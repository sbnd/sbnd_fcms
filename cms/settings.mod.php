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
* @package cms.settings
* @version 7.0.4
*/


/**
 * Class-module to make CMS settings. Support multiple languages settings if use "db" method version. 
 * 
 * @version 1.2
 * @author Evgeni Baldzhiyski
 * @since 10.08.2011
 * @package cms.settings
 */
class CMS_SETTINGS {
	/**
	 * 
	 * Container
	 * @var string
	 * @access public
	 */
	public $container = 'settings';
	/**
	 * 
	 * Method
	 * @var string
	 * @access public
	 */
	public $method = 'db'; // 'disk'
	/**
	 * 
	 * Data
	 * @var array
	 * @access private
	 */
	protected $data = array();
	/**
	 * 
	 * Are there changes param
	 * @var boolean
	 * @access private
	 */
	protected $have_changes = false;
	/**
	 * Initialisation
	 * @param array $settings
	 * @return CMS_SETTINGS
	 * @static
	 */
	static public function init($settings = array()){
		if(!isset($GLOBALS['CMS_SETTINGS'])){
			$GLOBALS['CMS_SETTINGS'] = new CMS_SETTINGS();
			
			//register_shutdown_function(array($GLOBALS['CMS_SETTINGS'], "write"));
		}
		foreach ($settings as $k => $v){
			$GLOBALS['CMS_SETTINGS']->$k = $v;
		}
		return $GLOBALS['CMS_SETTINGS'];
	}
	/**
	 * 
	 * Get settings data by name from 
	 * @param string $name
	 * @return string
	 * @access public
	 */
	public function get($name){
		if(!$this->data){
			$this->load();
		}
		return $this->getLangSettings($name);
	}
	/**
	 * 
	 * Set setting name => value data
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value){
		$this->have_changes = true;
		$this->data[$name] = $value;
	}
	/**
	 * 
	 * Set setting data and save to database
	 * @param string $name
	 * @param mixed $value
	 * @param boolean $lingual
	 * @param boolean $hidden
	 */
	public function setAndSave($name, $value, $lingual = false, $hidden = false){
		$this->data[$name] = $value;
		
		if($this->method == 'disk'){
			$cnt = ''; foreach ($this->data as $k => $v){
				$cnt .= $k.'='.$v."\r\n";
			}			
			if(!$file = fopen(BASIC::init()->ini_get('root_path').$this->container."/settings.ini", 'w')){
				fwrite($file, $cnt);
				fclose($file);
			}
		}else{
			$val = $value;
			if($lingual){
				$value = ''; while ($lang = BASIC_LANGUAGE::init()->listing()){
					if($value) $value .= "||";
					
					$value .= $lang['code']."=".$val[$lang['code']];
				}
			}
			if(!BASIC_SQL::init()->exec(" INSERT INTO `".$this->container."` (`variable`, `value`, `lingual`) VALUES ('".$name."', '".$value."', ".(int)$lingual.") ")){
				BASIC_ERROR::init()->clean();
				
				BASIC_SQL::init()->exec(" UPDATE `".$this->container."` SET 
					`value` = '".$value."',
					`lingual` = ".(int)$lingual.",
					`system` = ".($hidden ? -1 : 0)."
				WHERE `variable` = '".$name."' ");	
			}
		}
	}
	/**
	 * 
	 * Reload the settings
	 */
	public function reload(){
		$this->load();
	}
	/**
	 * 
	 * Get language settings
	 * @param string $name
	 */
	public function getLangSettings($name){
		if(isset($this->data[$name."_".BASIC_LANGUAGE::init()->current()])){
			return $this->data[$name."_".BASIC_LANGUAGE::init()->current()];
		}else if(isset($this->data[$name])){
			return $this->data[$name];
		}
		return null;
	}
	/**
	 * 
	 * Load the settings
	 */
	protected function load(){
		$err = false;
		
		if($this->method == 'disk'){
			$lFile = BASIC::init()->ini_get('root_path').$this->container."/settings.ini";
			if(file_exists($lFile)){
				$this->data = $this->ini_parcer(file($lFile));
			}else{
				throw new Exception(" Can't find file <b>".$lFile."</b>.");
			}
		}else if($this->method == 'db'){
			$rdr = BASIC_SQL::init()->read_exec(" SELECT `variable`, `value`, `lingual` FROM `".$this->container."` WHERE 1=1 ORDER BY `variable` ");
			
			$err = BASIC_ERROR::init()->error();
			if($err['code'] == 1146){				
				BASIC_SQL::init()->createTable('id', $this->container, "
				  `variable` varchar(255) NOT NULL DEFAULT '',
				  `value` varchar(500) NOT NULL DEFAULT '',
				  `lingual` int(1) NOT NULL DEFAULT '0',
				  `system` int(1) NOT NULL DEFAULT '-1'
				  UNIQUE KEY `variable` (`variable`)
				");
				BASIC_ERROR::init()->clean();
				$this->load();
				return;
			}
			while($rdr->read()){
				if($rdr->item('lingual')){
					$hash = array();
					$lex = explode("||", $rdr->item('value'));
					foreach($lex as $ex){
						$spl = explode("=", $ex, 2);
						
						if(!isset($spl[1])){
							$spl[1] = '';
						}
						
						$hash[$spl[0]] = $spl[1];
						
						// @TODO must still thinking about it
						if(!isset($this->data[$rdr->item('variable')])){
							$this->data[$rdr->item('variable')] = $spl[1];
						}
						$this->data[$rdr->item('variable')."_".$spl[0]] = $spl[1];
					}
				}else{
					$this->data[$rdr->item('variable')] = $rdr->item('value');
				}
			}
		}else{
			throw new Exception(" method <b>".$this->method."</b> for load settings is not supported!");
		}
	}
	/**
	 * Ini file data parser function
	 * @param array $array
	 * @return array
	 */
	protected function ini_parcer($array){
		$data = array();
		foreach ($array as $v){
			// Check for coment //
			$v = trim($v);
			if(preg_match("/^ *([\n\r\t]*[#;]+.*)|(\[[^\]]+\][ \t\n\r]*)$/", $v)) continue;

			$tmp = explode("=",$v,2);
			if(!isset($tmp[1])){
				continue;
			}else{
				if($tmp[1]){		
					$tmp[1] = (isset($tmp[1]) ? preg_replace("/[\r\t\n]+/", "", $tmp[1]) : '');
					$data[$tmp[0]] = $tmp[1];
				}
			}
		}
		return $data;
	}
	/**
	 * @TODO Not complete. Will complete in feature
	 */
	function write(){
		if($this->have_changes){
			
		}
	}
	/**
	 * @TODO Not complete. Will complete in reature
	 */
	public function save(){
		if($this->method == 'disk'){
			
		}else{
			
		}
	}
}