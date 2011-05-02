<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Mig_Sequencer
{
	/**
	* The migrations directory
	*
	* @var String
	*/
	protected $_dir = null;

	/**
	* The files in the correct order
	*
	* @var Array
	*/
	protected $_sequence = array();

	/**
	* Class constructor
	*
	* @param String $dir
	* @return Mig_Sequencer
	*/
	public function __construct($dir)
	{
		$this->_dir = $dir;
		$this->loadSequence();
	}

	/**
	* Load the list of files to be commited
	*/
	public function loadSequence()
	{
		if(!$this->_dir) throw new Mig_Exception('No directory specified for migrations');
		if(!is_readable($this->_dir)) throw new Mig_Exception('The directory '.$this->_dir.' is not readable');

		$list = $this->getFiles($this->_dir);

		$sequence = array();
		foreach($list as $li){
			if(preg_match('/^([0-9]+)/i', $li, $matches)){
				$sequence[] = array(
					'file' => $li,
					'class' => 'Migration_'.$matches[1],
					'version' => $matches[1]
				);
			}
		}
		$this->_sequence = $sequence;
		usort($this->_sequence, array($this, 'compare'));
	}

	/**
	* Execute the corresponding sequence for committing changes to database
	*
	* @param mixed $currentVersion
	* @param mixed $version
	*/
	public function commit($currentVersion, $version = null)
	{
		if($version && $version < $currentVersion) throw new Mig_Exception("You can't commit to a previous version, use rollback instead");
		$sequence = $this->createSequence($currentVersion, $version);

		$this->execute($sequence, 'up');
	}

	/**
	* Execute the corresponding sequence for rolling back changes to database
	*
	* @param mixed $currentVersion
	* @param mixed $version
	*/
	public function rollback($currentVersion, $version = null)
	{
		if($version > $currentVersion) throw new Mig_Exception("You can't rollback to a newer version");
		$sequence = $this->createSequence($version, $currentVersion, true);

		$this->execute($sequence, 'down');
	}

	/**
	* Create the sequence of objects used as a list
	*
	* @param mixed $first
	* @param mixed $last
	* @param mixed $revert
	*/
	protected function createSequence($first = null, $last = null, $revert = false)
	{
		if($first === null) $first = 0;
		if($last === null) $last = $this->_sequence[count($this->_sequence)-1]['version'];

		$seq = array();
		foreach($this->_sequence as $s){
			if($s['version'] > $first && $s['version'] <= $last){
				$seq[] = $s;
			}
		}
		if($revert) $seq = array_reverse($seq);
		//print_r($seq);die;

		$sequence = array();
		foreach($seq as $s){
			include_once(Mig_Manager::getMigrationStorage() . DS . $s['file']);
			$sequence[$s['version']] = new $s['class'];
		}

		return $sequence;
	}

	/**
	* Executes a whole sequence of objects
	*
	* @param mixed $sequence
	* @param mixed $method
	*/
	protected function execute($sequence, $method)
	{
		Mig_Printer::pr("\n\n=== STARTING MIGRATION SEQUENCE ==============================\n");
		$achievedVersion = Mig_Manager::getCurrentVersion();
		if(!count($sequence)){
			Mig_Printer::pr("Already there. Current version: {$achievedVersion}");
			return;
		}
		$adapter = Mig_Manager::getAdapter();
		$adapter->beginTransaction();
		try{
			foreach($sequence as $version => $s){
				call_user_func_array(array($s, $method), array());
				$achievedVersion = $method == 'down' ? $this->lookupPrevVersion($version) : $version;
			}
			$adapter->commit();
		}catch(Exception $e){
			$adapter->rollback();
			Mig_Printer::pr('Error encountered (current version '.$achievedVersion.'): '.$e->getMessage()."\n");
		}

		Mig_Manager::setCurrentVersion($achievedVersion);
		Mig_Printer::pr("\n=== MIGRATION ENDED ============================================");
	}

	/**
	* Gets the list of files in a directory
	*
	* @param String $dir
	* @return Array
	*/
	public function getFiles($dir)
	{
		$files = array();
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
			    while (($file = readdir($dh)) !== false) {
			        if($file != '.' && $file != '..'){
						$files[] = $file;
			        }
			    }
			    closedir($dh);
			}
		}else{
			throw new Mig_Exception('The specified resource is not a directory: '.$dir);
		}
		return $files;
	}

	/**
	* Used with usort to sort the items in sequence
	*
	* @param Array $a
	* @param Array $b
	*/
	public function compare($a, $b)
	{
		return $a['version'] < $b['version'] ? -1 : 1;
	}

	/**
	* Find the version before a given one
	*
	* @param mixed $version
	*/
	protected function lookupPrevVersion($version)
	{
		foreach($this->_sequence as $index => $s){
			if($s['version'] == $version){
				if($index > 0) return $this->_sequence[$index-1]['version'];
				else return 0;
			}
		}
	}
}
