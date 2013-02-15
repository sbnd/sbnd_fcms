<?php
/**
* SBND F&CMS - Framework & CMS for PHP developers
*
* Copyright (C) 1999 - 2013, SBND Technologies Ltd, Sofia, info@sbnd.net, http://sbnd.net
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author SBND Techologies Ltd <info@sbnd.net>
* @package cms.backup
* @version 7.0.4
*/


/**
 * Standart cms backup manager. Backup sql database and some resourses(graphics, documents, ... )
 * By default this class will used from the system for BASIC_SQL.backupEngine.
 * 
 * All paths start from site root. Ex: "cms/controlers/".
 * 
 * Backup storege structure:
 * 		backup.php			- cookie file with name of last created backup
 *  
 * 		46464563767/ 		- time that backup is created
 * 			db.php			- sql database
 * 			... / 			- folders showd in the property "resources"
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.3
 * @since 27.03.2012
 * @package cms.backup
 */
class CmsBackup extends BasicDumpSql {
	/**
	 * Path to the folders that will be add to/revert from backup.
	 * 
	 * @var array
	 * @access public
	 */
	public $resources = array();
	/**
	 * Path to the backup folder.
	 * 
	 * @var string
	 * @access public
	 */
	public $storage = 'backups';
	/**
	 * Name for the cookie file thet show last stored backup.
	 * 
	 * @var string
	 * @access public
	 */
	public $last_backup = 'backup.php';
	/**
	 * Name for the file with sql dump
	 * 
	 * @var string
	 * @access public
	 */
	public $db_file_name = 'db.php';
	
	/**
	 * 
	 * Constructor
	 * @param array $options
	 */
	function __construct($options = array()){
		foreach ($options as $k => $v){
			$this->$k = $v;
		}
		
		if(!$this->resources){
			$this->resources[] = BASIC::init()->ini('upload_path');
		}
	}
	/**
	 * Function to make backup
	 * @see BasicDumpSql::backup()
	 * @param boolean $drop_if_exist
	 */
	function backup($drop_if_exist = true){
		$new = time();
		
		if(!is_dir(BASIC::init()->root().$this->storage)){
			@mkdir(BASIC::init()->root().$this->storage);
		}
		$target = BASIC::init()->root().$this->storage."/".$new;
		
		// make backup folder
		@mkdir($target);
		
		//show last backup folder for revert functionality
		$file = fopen(BASIC::init()->root().$this->storage."/.".$this->last_backup, 'a+');
		if(!fread($file, 1024)){
			fwrite($file, "<?php die('access denied'); /*");
		}
		fwrite($file, "\n".$new);
		fclose($file);
		
		// create db dump
		$file = fopen($target."/".$this->db_file_name, 'w');
		fwrite($file, "<?php die('access denied'); /*".parent::backup($drop_if_exist));
		fclose($file);
		
		BASIC::init()->import('filesystem.mod');
		
		// backup resources folders and files
		foreach ($this->resources as $res){
			$dir = new BasicFolder(BASIC::init()->root().$res);
			$dir->copy($target."/".$res);
		}
		
		return $new;
	}
	/**
	 * Function revert backup
	 * @see BasicDumpSql::revert()
	 * @param string $table
	 * @param string $source
	 */
	function revert($table = '', $source = ''){
		if(is_dir(BASIC::init()->root().$this->storage)){
			// read last backup folder name
			$file = file(BASIC::init()->root().$this->storage."/.".$this->last_backup);
			$file_count = count($file);
			
			if(!$source && $file_count > 1){
				$source = $this->storage."/".$file[$file_count - 1];
			}
			
			// if this is full revert replace all resources
			if(!$table && $source){
				BASIC::init()->import('filesystem.mod');
				
				foreach ($this->resources as $res){
					
					try{
						$dir = new BasicFolder(BASIC::init()->root().$res);
						$dir->remove();
					}catch (Exception $e){}
					
					$dir = new BasicFolder(BASIC::init()->root().$source.'/'.$res);
					$dir->copy(BASIC::init()->root().$res);
				}
			}
			if($source) $source .= '/'.$this->db_file_name;
			
			// revert sql
			return parent::revert($table, $source);
		}else if($this->default){
			return parent::revert($table);
		}
		return false;
	}
	function remove($source = ''){
		if($source){
			$path = $this->storage."/".$source;
			
			$last = ''; foreach(file(BASIC::init()->root().$this->storage."/.".$this->last_backup) as $row){
				$row = str_replace("\n", "", $row);
				if($row && $source != $row){
					$last .= ($last ? "\n" : "").$row;
				}
			}
			
			$file = fopen(BASIC::init()->root().$this->storage."/.".$this->last_backup, 'w');
			fwrite($file, $last);
			fclose($file);			
		}else{
			$path = $this->storage;
		}
		
		BASIC::init()->import('filesystem.mod');
		
		$dir = new BasicFolder(BASIC::init()->root().$path);
		$dir->remove();	
	}
}