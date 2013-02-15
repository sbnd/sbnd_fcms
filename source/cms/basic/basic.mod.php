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
* @package basic.basic
* @version 7.0.4  
*/

/**
 * @author Evgeni Baldzhiyski
 * @package basic
 */
class BASIC_CLASS {
	/**
	 * Set variable
	 * WARNING : if phpversion() < 5 don't used the method if add objects.
	 * 		if used method with value == is_object() ,refference isn't existed.
	 *
	 * @access public
	 * @param string $name
	 * @param mix $value
	 * @return void
	 */
	function set($name,$value){
		$this->$name = $value;
	}
	/**
	 * Set variables
	 * 
	 * @access public
	 * @param array $paramArray
	 * @return void
	 */
	function sets($paramArray){
		if($paramArray){
			foreach ($paramArray as $k => $v){
				$this->$k = $v;
			}
		}
	}
	/**
	 * Get variable
	 * 
	 * @access public
	 * @param string $name
	 * @param string [$retur_false]
	 * @return mix
	 */
	function get($name, $retur_false = 'undefined'){
		if(is_array($name)){
			$tmp = array();
			foreach ($name as $v){
				if(isset($this->$name)) $tmp[$name] = $this->$name;
				$tmp[$name] =  $retur_false;
			}
			return $tmp;
		}
		if(isset($this->$name)) return $this->$name;
		return $retur_false;
	}
	/**
	 * Unset variable
	 * 
	 * @accesspublic
	 * @param string $name variable name
	 * @return void
	 */
	function un($name){
		if(isset($this->$name)) unset($this->$name);
	}
	/**
	 * Check type
	 * 
	 * @param string $name class name
	 * @retrun boolean
	 */
	function getType($name){
		return $this instanceof $name;
	}
}
/**
 * Basic framework base class. This class create basic state variables and serve program tools.
 *
 * @author Evgeni Baldziyski
 * @version 1.5
 * @package basic
 */
final class BASIC extends BASIC_CLASS{
	/**
	 * If the framework works in WEB mode it will find in cookie user time zone offset.
	 * 
	 * @access public
	 * @var string
	 */
	public $timeZoneCookieName = 'time_zone_offset';
	/**
	 * Settings collection of the environment
	 * 
	 * 	basic_path 	- core folder
	 *	root_path   - application folder
	 *	root_virtual- in WEB mode root path to the site/application
	 *	error_level - error level(recommended - 6143 in develope mode and  0 in live mode)
	 *  script_name - name of the current script when it's used rewrite engine
	 * 
	 * @access private
	 * @var array $ini
	 */
	private $ini = array(
		'version' => 1.4,
		
		// basic
		'basic_path' 	=> '',
		'root_path' 	=> '',
		'root_virtual' 	=> '',
		'error_level' 	=> 0,
		'script_name' 	=> ''
	);
	/**
	 * Constructor
	 *  
	 * Create environment paths
	 * @access public
	 * @example E:\projects\docs\info.php
	 * @return void
	 */
	function __construct(){
		$dir = strtolower(preg_replace("#[^/]+$#","",str_replace("\\","/",__FILE__)));

		$_SERVER['SCRIPT_NAME'] = strtolower(str_replace("\\","/",$_SERVER['SCRIPT_NAME']));
		$_SERVER['SCRIPT_FILENAME'] = strtolower(str_replace("\\","/",$_SERVER['SCRIPT_FILENAME']));

		$this->ini_set("root_path",preg_replace("#([^/]+/)$#","\\2",$dir));

		$this->ini_set('basic_path',str_replace($this->ini_get("root_path"),"",$dir));

		preg_match("#^/?[^/]+#",$this->ini_get("root_path"),$ex);
		preg_match("#^/?[^/]+#",$_SERVER['SCRIPT_FILENAME'],$ex1);

		if(isset($ex[0]) && isset($ex1[0])){
			if($ex[0] != $ex1[0]){
				$_SERVER['SCRIPT_FILENAME'] = preg_replace("#^.*".$ex[0]."#", $ex[0], $_SERVER['SCRIPT_FILENAME']);
			}
		}
		
		//die('test: '.(!(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') === false)));
		
		if(isset($_SERVER['HTTP_HOST'])){
			$doc_root = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);
			$dir = $this->validPath(str_replace($doc_root,'',$this->ini_get("root_path")));
			
			$this->ini_set('root_virtual', (isset($_SERVER['HTTPS']) ? 'https' : 'http'). "://".$_SERVER['HTTP_HOST'].$dir);
		
			$cookie = (isset($_COOKIE[$this->timeZoneCookieName]) ? $_COOKIE[$this->timeZoneCookieName] : '');
			
			if(!$cookie &&
				isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] &&
				isset($_SERVER['HTTP_COOKIE']) && $_SERVER['HTTP_COOKIE']
			){
				die('<!DOCTYPE html><html><head>
						<script>document.cookie = "'.$this->timeZoneCookieName.'="+(new Date()).getTimezoneOffset()/60*(-1)+"; path=/";
							location.reload();
						</script>
					</head><body></body></htnl>');
			}

			$this->ini_set('user_time_offset', $cookie);
			$this->ini_set('server_time_offset', @(int)date('O')/100);
		}
	}
	/**
	 * Global access to the service used singleton pattern.
	 * The container of instance is used $GLOBALS['BASIC']
	 * 
	 * @static
	 * @access public
	 * @return BASIC
	 */
	static public function init(){
	    if(!isset($GLOBALS['BASIC'])){
	        $GLOBALS['BASIC'] = new BASIC();
	    }
	    return $GLOBALS['BASIC'];
	}
	/**
	 * Add environment settings
	 *
	 * @access public
	 * @param string $name  name of settings
	 * @param mix $val all types
	 * @return void
	 */
	function ini_set($name,$val){
		if($name == 'error_level'){
			 error_reporting((int)$val);
		}
		$this->ini[$name] = $val;
	}
	/**
	 * Access to the environment settings
	 * 
	 * @access public
	 * @param string name
	 * @return mix if doesn't exist return null
	 */
	function ini_get($name){
		if(isset($this->ini[$name])){
			return $this->ini[$name];
		}
		return null;
	}
	/**
	 * Remove basic's variables.
	 * 
	 * You cant delete these variables.
	 *		version
	 *		basic_path
	 *		root_path
	 *		root_virtual
	 *		error_level
	 * 		script_name
	 * 
	 * @access public
	 * @param string $name
	 * @return void
	 */
	function ini_unset($name){
		switch ($name){
			case 'core-version' :
			case 'basic_path' :
			case 'root_path' :
			case 'root_virtual' :
			case 'error_level' :		
				break;
			default:
				unset($this->ini[$name]);
		}
	}
	/**
	 * Help property for imported method
	 * 
	 * @access private
	 * @var array
	 */
	protected $_imported = array();
	/**
	 * Including resources. 
	 * 
	 *  if you want to get current script's folder that want to include resources you have to use 
	 *		BASIC::init()->package(__FILE__).
	 *
	 *	if you want to include all files on package set "*" for $resource parameter.
	 *
	 * @param string $resource
	 * @param string [$package]
	 * @return boolean
	 */
	function imported($resource, $package = ''){
		if(isset($this->_imported[$package.$resource])){
			return true;
		}else{
			$this->_imported[$package.$resource] = 1;
		}
		
		$res_length = strlen($resource);
		
		$resource = str_replace('\\', '/', $resource);
		$package = str_replace('\\', '/', $package);
		
		if(!$package && substr($resource, -4, 4) == '.mod'){
			$package = $this->ini_get('basic_path');
		}else{
			$package = str_replace($this->ini_get('basic_path'), "", $package);
		}
		$package = $this->ini_get('root_path').$this->validPath($package);
		
		if($resource[$res_length-1] == '*'){
			$resource = preg_replace("/\*$/", "", $resource);

			if(!$dir = opendir($package.$resource)){
				throw new Exception('Can not open resource "'.$package.$resource.'".');
				return false;
			}
			
			$return  = true;
			while ($file = readdir($dir)){
				if(is_dir($package.$resource.$file)) continue;
				
				if(@(include_once $package.$resource.$file) === false){
					throw new Exception('Can not open resource "'.$package.$file.'".');
					$return = false;
				}
			}
			return $return;
		}else{
			if(@(include_once $package.$resource.'.php') === false){
				throw new Exception('Can not open resource "'.$package.$resource.'".');
				return false;
			}
		}
		return true;
	}
	/**
	 * Shorcut to method imported.
	 * 
	 * @access public
	 * @see BASIC::imported()
	 * @return void
	 */
	function import($resource){
		$this->imported($resource);
	}
	/**
	 * Change URL protocol ex: https,ftp
	 *
	 * @access public
	 * @param string $new
	 * @param boolen [$global]
	 * @return string
	 */
	function changeProtocol($new, $global = true){
		$protocol = explode('/', $_SERVER['SERVER_PROTOCOL']);
		$change = str_replace(strtolower($protocol[0]), $new, $this->ini_get('root_virtual'));
		if($global){
			$this->ini_set('root_virtual', $change);
		}
		return $change;
	}
	/**
	 * Change path to valid path
	 * 
	 * @access public
	 * @param string $path - file path or url address
	 * @return string
	 */
	function validPath($path){
		$path = str_replace("\\","/", $path);
		$path = str_replace("//","/", $path);

		if(!preg_match("#/$#",$path)) $path .= '/';

		return $path;
	}
	/**
	 * Get name of the current script
	 *
	 * <code>
	 * 		BASIC::init()->ini_get('root_virtual') -> 'http://localhost/mysite/'	
	 * 
	 * 		Зареден е адреса : http://localhost/mysite/info.php
	 * 			BASIC::init()->scriptName() == 'info.php'
	 * 
	 * 		при BASIC::init()->ini_set('rewrite','BasicRewrite');
	 * 
	 * 		Зареден е адреса : http://localhost/mysite/info
	 * 			BASIC::init()->scriptName() == 'info.php'
	 * </code>
	 * 
	 * @access public
	 * @return string
	 */
	function scriptName(){
	    if($this->ini_get('script_name')){
	        return $this->ini_get('script_name');
	    }
		return basename($_SERVER["PHP_SELF"]);
	}
	/**
	 * Get folder script name
	 * 
	 * @access public
	 * @param string [$path]
	 * @return string
	 */
	function dirName($path = ''){
		$ex = explode("/", str_replace("\\", "/", ($path ? $path : $_SERVER["PHP_SELF"])));
		if(count($ex) > 1){
			return $ex[count($ex) - 2].'/';
		}
		return '';
	}

	/**
	 * Convert bytes to string
	 *
	 * <code>
	 * 	BASIC::init()->biteToString(1024) == '1.00KB'
	 * </code>
	 * @version 0.2
	 * @access public
	 * @param string|integer $num
	 * @return string
	 */
	function biteToString($num){
		 $s = 1024;
		 $num = (int)$num;

		 $convert = $num . " Byte";

		 if($num >= pow($s,1))
		  	$convert = sprintf('%.2f',$num/pow($s,1))."KB";
		 if($num >= pow($s,2))
		  	$convert = sprintf('%.2f',$num/pow($s,2))."MB";
		 if($num >= pow($s,3))
		 	$convert = sprintf('%.2f',$num/pow($s,3))."GB";
		 if($num >= pow($s,4))
		 	$convert = sprintf('%.2f',$num/pow($s,3))."TB";

		 return $convert;
	}
	/**
	 * Convert text to bytes
	 *
	 * <code>
	 * 		BASIC::init()->stringToBite('1KB') == 1024
	 *</code>
	 * 
	 * @access public
	 * @param string $str
	 * @return integer
	 */
	function stringToBite($str){
		$tmp = str_replace("B","",$str);
		$tmp = substr($tmp,strlen($tmp)-1);

		if($tmp == 'T') return ((int)$str) * 1024*1024*1024*1024;
		if($tmp == 'G') return ((int)$str) * 1024*1024*1024;
		if($tmp == 'M') return ((int)$str) * 1024*1024;
		if($tmp == 'K') return ((int)$str) * 1024;

		return (int)$str;
	}
	/**
	 * Convert HTML colors (#FF0000) to RGB colors
	 * return array(
	 * 		'r' =>  int ,'g' => int, 'b' => int
	 * )
	 * 
	 * @access public
	 * @param string $color
	 * @return HashMap
	 */
    function convertHTMLtoRGB($color){
    	$color = str_replace("#", "", $color);
    	
        if (strlen($color) == 3){
            $red   = str_repeat(substr($color, 0, 1), 2);
            $green = str_repeat(substr($color, 1, 1), 2);
            $blue  = str_repeat(substr($color, 2, 1), 2);
        } else {
            $red   = substr($color, 0, 2);
            $green = substr($color, 2, 2);
            $blue  = substr($color, 4, 2); 
        }
        
        return array(
        	'r' => hexdec($red),
        	'g' => hexdec($green),
        	'b' => hexdec($blue)
        );
    }
	/**
	 * Check if email is valid
	 *
	 * <code>
	 *		BASIC::init()->validEmail('name@dom.ext') == 'name@dom.ext'
	 * 		BASIC::init()->validEmail('name.dom.ext') == ''
	 * </code>
	 * 
	 * @access public
	 * @param string $email
	 * @return string
	 */
	function validEmail($email){
		if(!preg_match('/^.+@.+\..+$/',$email)) return '';
		return $email;
	}
	/**
	 * Convert server time to client time. 
	 * The time is in format YYYY-MM-DD HH:ii
	 * 
	 * @access public
	 * @param string [$time] 
	 * @return string
	 */
	public function serverToClientTime($time = ''){
		$type = is_string($time);
		if($time && $type){
			$time = @(int)strtotime($time);
		}else if(!$time){
			$time = time();
		}
		$time = $time - (60*60*$this->ini('server_time_offset')) + (60*60*$this->ini('user_time_offset'));

		if(!$type){
			return $time;
		}
		return @date('Y-m-d H:i:s', $time);
	}
	/**
	 * Convert client time to server time. 
	 * The time is in format YYYY-MM-DD HH:ii
	 * 
	 * @param string [$time]
	 * @return string 
	 */
	public function clientToServerTime($time = ''){
		$type = is_string($time);
		if($time){
			if($type){
				$time = @(int)strtotime($time);
			}
			$time = $time - (60*60*$this->ini('user_time_offset')) + (60*60*$this->ini('server_time_offset'));
		}else{
			$time = time();
		}
		
		if(!$type){
			return $time;
		}
		return @date('Y-m-d H:i:s', $time);
	}
	/**
	 * Set collection of header for stoping caching
	 * 
	 * Set collection of header for stoping caching with option to set it to Content-Type:text/xml if $xml is true
	 *
	 * @param boolen [$xml]
	 */
	function SetXmlHeaders($xml = true){
		// Prevent the browser from caching the result.
		// Date in the past
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT') ;
		// always modified
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT') ;
		// HTTP/1.1
		header('Cache-Control: no-store, no-cache, must-revalidate') ;
		header('Cache-Control: post-check=0, pre-check=0', false) ;
		// HTTP/1.0
		header('Pragma: no-cache') ;

		// Set the response format.
		if($xml) header( 'Content-Type:text/xml; charset=utf-8' ) ;
	}
	/**
	 * Get Mime type
	 * 
	 * @access public
	 * @param string $type
	 * @return string
	 */
	public function getMimeType($type){
	   $mimes = array(
	      'hqx'   =>  'application/mac-binhex40',
	      'cpt'   =>  'application/mac-compactpro',
	      'doc'   =>  'application/msword',
	      'bin'   =>  'application/macbinary',
	      'dms'   =>  'application/octet-stream',
	      'lha'   =>  'application/octet-stream',
	      'lzh'   =>  'application/octet-stream',
	      'exe'   =>  'application/octet-stream',
	      'class' =>  'application/octet-stream',
	      'psd'   =>  'application/octet-stream',
	      'so'    =>  'application/octet-stream',
	      'sea'   =>  'application/octet-stream',
	      'dll'   =>  'application/octet-stream',
	      'oda'   =>  'application/oda',
	      'pdf'   =>  'application/pdf',
	      'ai'    =>  'application/postscript',
	      'eps'   =>  'application/postscript',
	      'ps'    =>  'application/postscript',
	      'smi'   =>  'application/smil',
	      'smil'  =>  'application/smil',
	      'mif'   =>  'application/vnd.mif',
	      'xls'   =>  'application/vnd.ms-excel',
	      'csv'   =>  'application/vnd.ms-excel',
	      'ppt'   =>  'application/vnd.ms-powerpoint',
	      'wbxml' =>  'application/vnd.wap.wbxml',
	      'wmlc'  =>  'application/vnd.wap.wmlc',
	      'dcr'   =>  'application/x-director',
	      'dir'   =>  'application/x-director',
	      'dxr'   =>  'application/x-director',
	      'dvi'   =>  'application/x-dvi',
	      'gtar'  =>  'application/x-gtar',
	      'php'   =>  'application/x-httpd-php',
	      'php4'  =>  'application/x-httpd-php',
	      'php3'  =>  'application/x-httpd-php',
	      'phtml' =>  'application/x-httpd-php',
	      'phps'  =>  'application/x-httpd-php-source',
	      'js'    =>  'application/x-javascript',
	      'swf'   =>  'application/x-shockwave-flash',
	      'sit'   =>  'application/x-stuffit',
	      'tar'   =>  'application/x-tar',
	      'tgz'   =>  'application/x-tar',
	      'xhtml' =>  'application/xhtml+xml',
	      'xht'   =>  'application/xhtml+xml',
	      'zip'   =>  'application/zip',
	      'mid'   =>  'audio/midi',
	      'midi'  =>  'audio/midi',
	      'mpga'  =>  'audio/mpeg',
	      'mp2'   =>  'audio/mpeg',
	      'mp3'   =>  'audio/mpeg',
	      'aif'   =>  'audio/x-aiff',
	      'aiff'  =>  'audio/x-aiff',
	      'aifc'  =>  'audio/x-aiff',
	      'ram'   =>  'audio/x-pn-realaudio',
	      'rm'    =>  'audio/x-pn-realaudio',
	      'rpm'   =>  'audio/x-pn-realaudio-plugin',
	      'ra'    =>  'audio/x-realaudio',
	      'rv'    =>  'video/vnd.rn-realvideo',
	      'wav'   =>  'audio/x-wav',
	      'bmp'   =>  'image/bmp',
	      'gif'   =>  'image/gif',
	      'jpeg'  =>  'image/jpeg',
	      'jpg'   =>  'image/jpeg',
	      'jpe'   =>  'image/jpeg',
	      'png'   =>  'image/png',
	      'tiff'  =>  'image/tiff',
	      'tif'   =>  'image/tiff',
	      'css'   =>  'text/css',
	      'html'  =>  'text/html',
	      'htm'   =>  'text/html',
	      'shtml' =>  'text/html',
	      'txt'   =>  'text/plain',
	      'text'  =>  'text/plain',
	      'log'   =>  'text/plain',
	      'rtx'   =>  'text/richtext',
	      'rtf'   =>  'text/rtf',
	      'xml'   =>  'text/xml',
	      'xsl'   =>  'text/xml',
	      'mpeg'  =>  'video/mpeg',
	      'mpg'   =>  'video/mpeg',
	      'mpe'   =>  'video/mpeg',
	      'qt'    =>  'video/quicktime',
	      'mov'   =>  'video/quicktime',
	      'avi'   =>  'video/x-msvideo',
	      'movie' =>  'video/x-sgi-movie',
	      'doc'   =>  'application/msword',
	      'word'  =>  'application/msword',
	      'xl'    =>  'application/excel',
	      'eml'   =>  'message/rfc822'
	    );
	    return isset($mimes[$type]) ? $mimes[$type] : '';
	}
	/**
	 * Mobile divice detector.
	 * 
	 * return divices values:
	 *		android
	 *		androidtablet
	 *		blackberry
	 *		blackberrytablet
	 *		iphone
	 *		ipad
	 *		palm
	 *		windows
	 *		windowsphone
	 *		generic
	 * 
	 * if doesn't match mobile divice will return empty string.
	 * 
	 * @access public
	 * @return string
	 */
	function mobileDetector(){
		if(!isset($_SERVER['HTTP_USER_AGENT'])) return ''; 
	
		$devices = array(
			"android" 			=> "android.*mobile",
			"androidtablet" 	=> "android(?!.*mobile)",
			"blackberry" 		=> "blackberry",
			"blackberrytablet" 	=> "rim tablet os",
			"iphone" 			=> "(iphone|ipod)",
			"ipad" 				=> "(ipad)",
			"palm" 				=> "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
			"windows" 			=> "windows ce; (iemobile|ppc|smartphone)",
			"windowsphone" 		=> "windows phone os",
			"generic" 			=> "(kindle|mobile|mmp|midp|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)"
		);
		foreach($devices as $device => $regexp){
			if((bool)preg_match("/".$devices[strtolower($device)]."/i", $_SERVER['HTTP_USER_AGENT'])){
				return $device;
			}
		}
		return '';
	}
	
	// #### Shorcuts #### //
	
	/**
	 * Convert and clean current file's path from magic variable __FILE__
	 * Example using: 
	 * 		if call file is in this location 
	 * 			'c:/webserver/www/site-root/folder-in-root/package-path-folder/package/current-file.php'	
	 * 		
	 * 		BASIC::init()->package(__FILE__) == folder-in-root/package-path-folder/package/
	 * 
	 * 	You can use this method ind with standart paths:
	 * 		BASIC::init()->package('folder-in-root/package-path-folder/package/folder/') == folder-in-root/package-path-folder/package/
	 *   
	 * @access public
	 * @param string $__FILE__
	 * @return string
	 */
	function package($__FILE__){
		$__FILE__ = str_replace("\\","/", $__FILE__);
		$__FILE__ = str_ireplace($this->ini_get('root_path'), "", $__FILE__);
		$__FILE__ = preg_replace("#/[^/]+/?$#", "/", $__FILE__);
		$__FILE__ = str_replace("//","/", $__FILE__);
		
		return $__FILE__;	
	}
	/**
	 * Get basic variables
	 * 
	 * @access public
	 * @param string $setting
	 * @param mix [$value]
	 * @return mix
	 */
	function ini($setting, $value = null){
		if($value === null){
			return $this->ini_get($setting);
		}
		$this->ini_set($setting, $value);
		
		return $this->ini_get($setting);
	}
	/**
	 * Get site root path (file system location).
	 * Ex: C:/webserver/www/root-site-directory/
	 * 
	 * @access public
	 * @return string
	 */
	function root(){
		return $this->ini_get('root_path');
	}
	/**
	 * Get site root url path (web server system location).
	 * Ex: http://my.sitedomain.com/root-site-directory/
	 * 
	 * @access public
	 * @return string
	 */
	function virtual(){
		return $this->ini_get('root_virtual');
	}
}

/**
 * Convert to integer
 * 
 * @package BASIC
 * @param mix $number
 * @return integer
 * @package basic
 */
function Int($number){
	return (int)$number;
}
/**
 * Convert to float
 * 
 * @package BASIC
 * @param mix $float
 * @return float
 * @package basic
 */
function Float($float){
	return (float)$float;
}
/**
 * 	HTML SECURITY.
 * 
 *  prohibit tag section "script","iframe" and "style"
 *  prohibit properties : every javascript event property
 *  prohibit use in style property url construction
 *  prohibit images src parameter, use url parameters 
 *  clean javascript executor "javascript[ ]*:"
 *
 *  @author Evgeni Baldzisky
 *  @version 0.5
 *  @package basic
 */
function htmlSecurity($str){
	$str = stripslashes($str);
	$str = preg_replace("/[\t]*<(script|style)[^>]*>[^<]*<\/(script|style)[^>]*>[\n\r]*/i","&nbsp;",$str);
	$str = preg_replace('/on[^= ]+[ ]*=[ ]*["\']?[^>]+/i','onerror="bed text"',$str);
	
	$str = preg_replace('/javascript[ ]*:/i', '#', $str);
	
	$str = preg_replace('/(style[ ]*=[ ]*["\']?.*)url[^;"]+;?(.*["\']?)/i', "$1$2", $str);

	$str = preg_replace_callback('/src=["\']?[^"\' ]*/i', "clearSrc", $str);
	//$str = preg_replace('/(<img.*src=["\']?[^\?]*)\?*[^"\' ]*(["\' ]?[^>]*>)/i',"$1$2",$str);
	$str = preg_replace("/[\t]*<(iframe|\.\.\.)[^>]*>[^<]*<\/(iframe|\.\.\.)[^>]*>[\n\r]*/i","&nbsp;",$str);
	
	$str = BASIC_GENERATOR::init()->getControl('html')->convertIn($str);
	
	return addslashes($str);
}
/**
 * Clean src string
 * 
 * @param array $match
 * @return string
 * @package basic
 */
function clearSrc($match){
	$tmp = explode("?",$match[0],2);
	return $tmp[0];
}
/**
 * Clean HTML 
 * 
 * @param string $longtext
 * @return string
 * @package basic
 */
function cleanHTMLT($longtext){
    return BASIC_GENERATOR::init()->getControl('html')->convertOut($longtext);
}
/**
 * Clean POST
 * 
 * @package basic
 * @param string $post
 * @return string
 */
function cleanURLInjection($post){
	$post = trim(addslashes($post));
	$post = str_replace("#", "", $post);
	
	return $post;
}
/**
 * Clear Up Injection
 * 
 * @param string $post
 * @return string
 * @package basic
 */
function clearUpInjection($post){
	//$post = str_replace('/',  '', $post);
	$post = str_replace('\\', '', $post);
	$post = str_replace('..', '', $post);
	
	return cleanURLInjection($post);
}
/**
 * Add slashes, remove \n and \r and strip_tags if it set
 * 
 * @param string $post
 * @param boolean [$strip_tags]
 * @return string
 * @package basic
 */
function charAdd($post, $strip_tags = true){
	$post = str_replace("\n", "", $post);
	$post = str_replace("\r", "", $post);
	$post = charStrip($post);
	
	if($strip_tags){ 
		$post = strip_tags($post);	
	}
	$post = addslashes($post);

	return $post;
}
/**
 * Remove \\
 * @package basic
 * @param string $post
 * @return string
 */
function charStrip($post){
	$post = str_replace('\\', '', $post);
	
	return $post;
}
/**
 * Returns a string with backslashes and change and set <br /> before all newlines
 * 
 * @param string $post
 * @return string
 * @package basic
 */
function formatText($post){
	$post = stripcslashes($post);
	$post = nl2br($post);
	
	return $post;
}