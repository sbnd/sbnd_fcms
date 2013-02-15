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
* @package basic.language
* @version 7.0.4  
*/

/**
 * Class Multilanguage
 * This object is autoinstanced -  its object variable is $GLOBALS['BASIC_LANG']
 *
 * @author Evgeni Baldzisky
 * @version 0.5 [13-02-2007]
 * @package basic.language
 */
class BASIC_LANGUAGE extends BASIC_CLASS {
	/**
	 * @access private
	 * @var integer
	 */
	protected $i 		= -1;
	/**
	 * @access private
	 * @var string default language code
	 */
	protected $default 	= '';
	/**
	 * @access private
	 * @var string language code
	 */
	protected $current 	= '';
	/**
	 * @access private 
	 * @var array
	 */
	protected $data 	= array();
	/**
	 * @access private
	 * @var array
	 */
	public $language = array();
	/**
	 * Setting - from where to get language info
	 * $method   = 'db' - it will try to read from db table, if it doesn't exist - it will try to create it
	 * $method   = 'disk' - it havs to exist ini files for each language, where the file name is labnguage code, example en.ini. bg.ini
	 * 	<code>
	 * 		BASIC_LANGUAGE::init(array(
	 * 			'method' => 'disk'
 	 * 		));
 	 * 		// markup file system path
 	 * 			BASIC::init()->ini_get('root_path') -> BASIC_LANGUAGE::init()->container -> lang_code.ini
 	 * 
	 * 		BASIC_LANGUAGE::init(array(
	 * 			'method' => 'db'
 	 * 		));
 	 * 		// marcup data base
	 *			CREATE TABLE `".$this->container."` (
 	 *			  `id` int(11) NOT NULL auto_increment,
     *			  `code` varchar(2) NOT NULL,
 	 *			  `variable` varchar(255) NOT NULL,
 	 *			  `value` varchar(255) default NULL,
 	 *			   PRIMARY KEY  (`id`),
 	 *			   UNIQUE KEY `variable` (`variable`),
 	 *			   KEY `Code` (`code`)
 	 *			);
	 * </code>
	 *
	 * @var string [disk|db]
	 */
	public $method   = 'db'; //'disk'; //[db]
	/**
	 * Language container 
	 * 	if method -> 'db' then it will be name of the db table
	 * 	if method -> 'disk' it will be the folder where are the ini files for languages description 
	 * @access public
	 * @var string
	 */
	public $container  = 'lingual';
	/**
	 * Varaible name which will check for changing language
	 * @access public
	 * @var string
	 */
	public $varLog 	= 'LangCode';
	/**
	 * Set where will be store the session variable - in session or in cookie
	 * @access public
	 * @var string [cookie|session]
	 */
	public $logMethod   = 'cookie';
	/**
	 * Global access and setting of the service. For instance container is used $GLOBALS['BASIC_LANG'] 
	 * @staticvar
	 * @access public
	 * @param array [$arr]
	 * @return BASIC_LANGUAGE
	 */
	static public function init($arr = array()){
		if(!isset($GLOBALS['BASIC_LANG'])){
			$GLOBALS['BASIC_LANG'] = new BASIC_LANGUAGE();
		}
		foreach ($arr as $k => $v){
			if($k == 'language'){
				foreach ($v as $code => $val){
					if(!isset($val[$code]['text']))  $val[$code]['text'] = ''; 
					if(!isset($val[$code]['encode'])) $val[$code]['encode'] = 'utf8'; 
					if(!isset($val[$code]['flag']))   $val[$code]['flag'] = '';
					if(!isset($val[$code]['system'])) $val[$code]['system'] = false;
				}
				$GLOBALS['BASIC_LANG']->language = $v;
			}
			$GLOBALS['BASIC_LANG']->$k = $v;
		}
		return $GLOBALS['BASIC_LANG'];
	}
	/**
	 * Add some setting
	 * @access public
	 * @return BASIC_LANGUAGE
	 */
	function start(){
		if(!$this->language){
			return $this; 
		}
		
		$slCode = '';
		if($this->logMethod == 'session'){
			BASIC::init()->imported('session.mod');
			BASIC_SESSION::init()->start();
			
			$slCode = BASIC_SESSION::init()->get($this->varLog);
		}
		
		$code = '';
		if($lCode = BASIC_URL::init()->get($this->varLog)){
			$code = $lCode;
		}else if($lCode = BASIC_URL::init()->post($this->varLog)){
			$code = $lCode;
		}else if($this->logMethod == 'session' && $slCode){
			$code = $slCode;
		}else if($lCode = BASIC_URL::init()->cookie($this->varLog)){
			$code = $lCode;
		}
	
		foreach ($this->language as $k => $v){
			if(!$this->default)
				$this->default = $k;
			break;
		}
		if(!isset($this->language[$code])){
			$code = $this->default;
		}
		$this->reloadLanguage($code);
		
		return $this;
	}
	/**
	 * Restart set language
	 * @access public
	 * @return BASIC_LANGUAGE
	 */
	function restart(){
		$this->current = '';
		return $this->start();
	}
	/**
	 * Reload language
	 * @access public
	 * @param string $code
	 * @return BASIC_LANGUAGE
	 */
	function reloadLanguage($code){
		if($this->current != $code){ // optimization! 
		    $this->current = $code;
			if($this->logMethod == 'session'){
				BASIC_SESSION::init()->set($this->varLog, $this->current);
				setcookie($this->varLog, '', null, '/');
			}else{
				setcookie($this->varLog, $this->current, null, '/');
			}
			$this->_load();	
		}
		return $this; 
	}
	/**
	 * Get value of language variable
	 * @access public
	 * @param string $variable - language variable name
	 * @return string
	 */
	function get($variable){
		return stripslashes(isset($this->data[$variable]) ? $this->data[$variable] : '['.$variable.']');
	}
	/**
	 * Get info for language with code $code
	 * @access public
	 * @param string $code
	 * @return array
	 */
	function getList($code){
		$tmp = $this->data;
		$this->_load($code);
		$tmp2 = $this->data;
		$this->data = $tmp;
		return $tmp2;
	}
	/**
	 * Number of registered languages
	 * @access public
	 * @return integer
	 */
	function number(){
		return count($this->language);
	}
	/**
	 * Code of the current language
	 * @access public
	 * @return string
	 */
	function current(){
		return $this->current;
	}
	/**
	 * Code of the default language
	 * @access public
	 * @return string
	 */
	function default_(){
		return $this->default;
	}
	/**
	 * Info data for language with code $code
	 * @access public
	 * @param string [$name] - settings name 
	 * @param string [$code] - language code
	 * @return array|string
	 */
	function info($name = '',$code = ''){
		if(!$code) $code = $this->current();
		if(isset($this->language[$code])){
			if($name && isset($this->language[$code][$name])){
				return $this->language[$code][$name];
			}else{
				return $this->language[$code];
			}
		}
		return 'undefined';
	}
	/**
	 * Link or button for selected language
	 *
	 * @access public
	 * @param string $code - language code
	 * @param array [$miss]  - array with variable names which will be missed in url
	 * @return string
	 */
	function link($code,$miss = array()){
		$miss[] = $this->varLog;
		return ($GLOBALS['BASIC']->scriptName()).'?'.BASIC_URL::init()->serialize(array($this->varLog)).$this->varLog.'='.($code ? $code : $this->current());
	}
	/**
	 * While technique for getiing language setting data 
	 * 
	 * @access public
	 * @return boolen
	 */
	function listing(){
		$i = 0;
		foreach ($this->language as $k => $v){
			if($this->i < $i){
				$this->i = $i;
				$v['code'] = $k;
				return $v;
			}
			$i++;
		}
		$this->i = -1;
		return false;
	}
	/**
	 * Get hashmap with language codes
	 * 
	 * @access public
	 * @return hashmap;
	 */
	function getLanguageList(){
		return $this->language;
	}			
	/**
	 * Load language with code $code
	 * 
	 * If it's db method if there aren't column for this language it will be created
	 * @access private
	 * @param string [$code]
	 * @access Protected
	 */
	protected function _load($code = null){
		$lCode = ($code ? $code : $this->current);
		$err = false;
		
		if($this->method == 'disk'){
			$lFile = BASIC::init()->ini_get('root_path').$this->container."/".$lCode.".ini";
			if(file_exists($lFile)){
				$this->data = self::ini_parcer(file($lFile));
			}else{
				$err = true;
			}
		}else if($this->method == 'db'){
			$rdr = BASIC_SQL::init()->read_exec(" SELECT `variable`, `value_".$lCode."` AS `value` FROM `".$this->container."` WHERE 1=1 ORDER BY `variable` ");

			$err = BASIC_ERROR::init()->error();
			if($err['code'] == 1146){
				$data = " `variable` varchar(255) NOT NULL, ";
				foreach ($this->language as $k => $v){
					$data .= " `value_".$k."` VARCHAR(255) DEFAULT NULL, ";
				}
				$data .= " UNIQUE KEY `variable` (`variable`) ";
				BASIC_SQL::init()->createTable('id',$this->container, $data);
				
				$GLOBALS['BASIC_ERROR']->clean();
				$this->_load();
				return;
			}elseif($err['code'] == 1054){
				
				preg_match("/column( name)? '([^']+)'/",$err['message'],$math);
				BASIC_SQL::init()->createColumn($this->container," `".$math[2]."` VARCHAR(255) DEFAULT NULL ");
				
				BASIC_ERROR::init()->clean();
				$this->_load();
				return;
			}
			if($rdr->num_rows() != 0){
				while($rdr->read()){
					$this->data[$rdr->field('variable')] = $rdr->field('value');
				}
				unset($rdr->items);
			}
		}else{
			throw new Exception(" method <b>".$this->method."</b> load languages is not support!");
		}
	}
	/**
	 *  Process ini files
	 * @static
	 * @access public
	 * @param array $array
	 * @return array
	 */
	static public function ini_parcer($array){
		$data=array(); 			// Хеш мап (Асоциативен масив)
		foreach ($array as $v){
			// Check for coment //
			$v = trim($v);
			if(preg_match("/^ *([\n\r\t]*[#;]+.*)|(\[[^\]]+\][ \t\n\r]*)$/",$v)) continue;

			$tmp = explode("=",$v,2);
			if(!isset($tmp[1])){
				continue;
			}else{
				if((string)$tmp[1] != ''){		
					$tmp[1] = (isset($tmp[1]) ? preg_replace("/[\r\t\n]+/", "", $tmp[1]) : '');
					$data[$tmp[0]] = $tmp[1];
				}
			}
		}
		return $data;
	}
}
/*
utf8 1.0
Copyright: Left
---------------------------------------------------------------------------------
Version:        1.0
Date:           23 November 2004
---------------------------------------------------------------------------------
Author:         Alexander Minkovsky (a_minkovsky@hotmail.com)
---------------------------------------------------------------------------------
License:        Choose the more appropriated for You - I don't care.
---------------------------------------------------------------------------------
Description:
Class provides functionality to convert single byte strings, such as CP1251
ti UTF-8 multibyte format and vice versa.
Class loads a concrete charset map, for example CP1251.
(Refer to ftp://ftp.unicode.org/Public/MAPPINGS/ for map files)
Directory containing MAP files is predefined as constant.
Each charset is also predefined as constant pointing to the MAP file.
---------------------------------------------------------------------------------
Example usage:
Pass the desired charset in the class constructor:
$utfConverter = new utf8(CP1251); //defaults to CP1250.
or load the charset MAP using loadCharset method like this:
$utfConverter->loadCharset(CP1252);
Then call
$res = $utfConverter->strToUtf8($str);
or
$res = $utfConverter->utf8ToStr($utf);
to get the needed encoding.
---------------------------------------------------------------------------------
Note:
Rewrite or Override the onError method if needed. It's the error handler used from everywhere and takes 2 parameters:
err_code and err_text. By default it just prints out a message about the error.
*/

//Charset maps
//define("MAP_DIR",$GLOBALS['BASIC']->ini_get('root_path') . "basic/map/");
//
//define("CP1250",MAP_DIR . "CP1250.MAP");
//define("CP1251",MAP_DIR . "CP1251.MAP");
//define("CP1252",MAP_DIR . "CP1252.MAP");
//define("CP1253",MAP_DIR . "CP1253.MAP");
//define("CP1254",MAP_DIR . "CP1254.MAP");
//define("CP1255",MAP_DIR . "CP1255.MAP");
//define("CP1256",MAP_DIR . "CP1256.MAP");
//define("CP1257",MAP_DIR . "CP1257.MAP");
//define("CP1258",MAP_DIR . "CP1258.MAP");
//define("CP874", MAP_DIR . "CP874.MAP");
//define("CP932", MAP_DIR . "CP932.MAP");
//define("CP936", MAP_DIR . "CP936.MAP");
//define("CP949", MAP_DIR . "CP949.MAP");
//define("CP950", MAP_DIR . "CP950.MAP");

define("ERR_OPEN_MAP_FILE","ERR_OPEN_MAP_FILE");

/**
 * Class provides functionality to convert single byte strings, such as CP1251
 * ti UTF-8 multibyte format and vice versa.
 * @author Alexander Minkovsky
 */
Class utf8{

	var $charset = '';
	var $ascMap = array();
	var $utfMap = array();

	/**
	 * Constructor
	 * @access public
	 * @param string [$charset]
	 */
	function utf8($charset="cp1251"){
		$charset = $GLOBALS['BASIC']->ini_get('root_path').$GLOBALS['BASIC']->ini_get('basic_path')."map/".strtoupper($charset).".MAP";

		$this->loadCharset($charset);
	}

	/**
	 * Load charset
	 * @access public
	 * @param unknown_type $charset
	 */
	function loadCharset($charset){
		$lines = @file_get_contents($charset)
		or exit($this->onError(ERR_OPEN_MAP_FILE,"Error openning file: " . $charset));
		$this->charset = $charset;
		$lines = preg_replace("/#.*$/m","",$lines);
		$lines = preg_replace("/\n\n/","",$lines);
		$lines = explode("\n",$lines);
		foreach($lines as $line){
			$parts = explode('0x',$line);
			if(count($parts)==3){
				$asc=hexdec(substr($parts[1],0,2));
				$utf=hexdec(substr($parts[2],0,4));
				$this->ascMap[$charset][$asc]=$utf;
			}
		}
		$this->utfMap = array_flip($this->ascMap[$charset]);
	}
	/**
	 * Error handler
	 * @access public
	 * @param string $err_code
	 * @param string $err_text
	 */
	function onError($err_code,$err_text){
		print($err_code . " : " . $err_text . "<hr>\n");
	}
	/**
	 * Translate string ($str) to UTF-8 from given charset
	 * @access public
	 * @param string $str
	 */
	function strToUtf8($str){
		$chars = unpack('C*', $str);
		$cnt = count($chars);
		for($i=1;$i<=$cnt;$i++) $this->_charToUtf8($chars[$i]);
		return implode("",$chars);
	}

	/**
	 * Translate UTF-8 string to single byte string in the given charset
	 * @access public
	 * @param string $utf
	 */
	function utf8ToStr($utf){
		$chars = unpack('C*', $utf);
		$cnt = count($chars);
		$res = ""; //No simple way to do it in place... concatenate char by char
		for ($i=1;$i<=$cnt;$i++){
			$res .= $this->_utf8ToChar($chars, $i);
		}
		return $res;
	}

	/**
	 * Char to UTF-8 sequence
	 * @access public
	 * @param unknown_type $char
	 */
	function _charToUtf8(&$char){
		$c = (int)$this->ascMap[$this->charset][$char];
		if ($c < 0x80){
			$char = chr($c);
		}
		else if($c<0x800) // 2 bytes
		$char = (chr(0xC0 | $c>>6) . chr(0x80 | $c & 0x3F));
		else if($c<0x10000) // 3 bytes
		$char = (chr(0xE0 | $c>>12) . chr(0x80 | $c>>6 & 0x3F) . chr(0x80 | $c & 0x3F));
		else if($c<0x200000) // 4 bytes
		$char = (chr(0xF0 | $c>>18) . chr(0x80 | $c>>12 & 0x3F) . chr(0x80 | $c>>6 & 0x3F) . chr(0x80 | $c & 0x3F));
	}

	//
	/**
	 * UTF-8 sequence to single byte character
	 * @access public
	 * @param  $chars
	 * @param unknown_type $idx
	 */
	function _utf8ToChar(&$chars, &$idx){
		if(($chars[$idx] >= 240) && ($chars[$idx] <= 255)){ // 4 bytes
			$utf =    (intval($chars[$idx]-240)   << 18) +
			(intval($chars[++$idx]-128) << 12) +
			(intval($chars[++$idx]-128) << 6) +
			(intval($chars[++$idx]-128) << 0);
		}
		else if (($chars[$idx] >= 224) && ($chars[$idx] <= 239)){ // 3 bytes
			$utf =    (intval($chars[$idx]-224)   << 12) +
			(intval($chars[++$idx]-128) << 6) +
			(intval($chars[++$idx]-128) << 0);
		}
		else if (($chars[$idx] >= 192) && ($chars[$idx] <= 223)){ // 2 bytes
			$utf =    (intval($chars[$idx]-192)   << 6) +
			(intval($chars[++$idx]-128) << 0);
		}
		else{ // 1 byte
			$utf = $chars[$idx];
		}
		if(array_key_exists($utf,$this->utfMap))
		return chr($this->utfMap[$utf]);
		else
		return "?";
	}
	// End class
}