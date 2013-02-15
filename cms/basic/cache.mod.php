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
* @package basic.cache
* @version 7.0.4  
*/

/**
 * Global service for access to caches.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @since 10.03.2012
 * @package basic.cache
 */
class BASIC_CACHE {
	/**
	 * Path to temporary folder. The path start from site root.
	 * Ex: tmp/
	 * 
	 * @access public
	 * @var string
	 */
	public $tmp_path = '';
	/**
	 * Path to cache folder. By default the path start from self:tmp_path = BASIC::init()->ini('temporary_path'). 
	 * Ex: my_tmp/cache
	 * 
	 * @access public
	 * @var string
	 */
	public $storage = 'cache';
	/**
	 * Return object BASIC_CACHE using singleton pattern
	 * 
	 * @access public
	 * @static
	 * @param hashmap [$settings]
	 * @return BASIC_CACHE
	 */	
	public static function init($settings = array()){

		if(!isset($GLOBALS['BASIC_CACHE'])){
			$GLOBALS['BASIC_CACHE'] = new BASIC_CACHE();
		}
		foreach($settings as $k => $v){
			$GLOBALS['BASIC_CACHE']->$k = $v;
		}
		
		return $GLOBALS['BASIC_CACHE'];
	}
	/**
	 * Constructor
	 * 
	 * @access public
	 * @return void
	 */
	function __construct(){
		$this->tmp_path = BASIC::init()->ini('temporary_path');
	}
	/**
	 * Get object for subfolder in cache storage. 
	 * 
	 * @param string $name
	 * @access public
	 * @return BasicCache
	 */
	public function open($name){
		if(!is_dir(BASIC::init()->root().$this->tmp_path."/".$this->storage)){
			@mkdir(BASIC::init()->root().$this->tmp_path."/".$this->storage);
		}
		
		$name = $this->tmp_path."/".$this->storage."/".$name;
		
		return new BasicCache($name);
	}
	/**
	 * Clear cache
	 * 
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public function clear($path = ''){
		$root = BASIC::init()->root().$this->tmp_path."/".$this->storage;
		
		$dir = @opendir($root."/".$path);
		while ($f = @readdir($dir)){
			if($f[0] == '.' || $f == '..') continue;
			
			if(is_dir($root."/".$path."/".$f)){
				$this->clear($path."/".$f);
				@rmdir($root."/".$path."/".$f);
			}else{
				@unlink($root."/".$path."/".$f);
			}
		}
	}
}
/**
 * Interface for access to cache objects.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @since 10.03.2012
 * @package basic.cache
 */
interface BasicCacheInterface{
	/**
	 * Check if cache.mod exists.
	 *
	 * @param string $name
	 * @return boolean
	 */
	function check($name);
	/** 
	 * clear old cachees.
	 * 
	 * @param integer $time - in secunds
	 */
	function clear($time = '', $rate = 0);
	/**
	 * Store or get (if the params $array !== null) arrays.
 	 * 
	 * @param string $name
	 * @param array|hashmap [$array]
	 * @param integer $time - in seconds
	 * @return array
	 */
	function cacheArray($name, $array = null, $time = 0);
	/**
	 * Store or get (if the params $text !== null) text.
	 * 
	 * @param string $name
	 * @param string [$text]
	 * @param integer [$time]
	 * @retun text
	 */
	function cacheText($name, $text = null, $time = 0);
	/**
	 * Store or get (if the params $xml !== null) xml.
	 * 
	 * @param string $name
	 * @param string $xml
	 * @param int $time
	 * @retun SimpleXMLElement
	 */
	function cacheXML($name, $xml = null, $time = 0);
}
/**
 * @author Evgeni Baldzhiyski
 * @version 0.3
 * @since 10.03.2012
 * @package basic.cache
 * 
 * @example
 * 		$cache = new BasicCache('cache_store/my_cache');
 * 
 * 		if(!$txt = $cache->cacheText('cache_name_file')){
 * 			$getTextFromSql = $sql->read();
 * 
 * 			$txt = $cache->cacheText('cache_name_file', $getTextFromSql, (10*60));
 * 		}
 * 		die($txt);
 * 
 * 		---------------------
 * 
 * 		if(!$txt = $cache->cacheArray('cache_name_file')){
 * 			$hashMap = $sql->getSelectArray();
 * 
 * 			$hashMap = $cache->cacheArray('cache_name_file', $hashMap, (10*60));
 * 		}
 * 		die(print_r($hashMap));	
 */
class BasicCache implements BasicCacheInterface{
	/**
	 * Path to cache folder.
	 * 
	 * @access private
	 * @var string
	 */
	protected $storage_path = '';
	/**
	 * Hashmap with cache times. Determine how much seconds the cache has to exist.
	 * 
	 * @access private
	 * @var array
	 */
	protected $times = array();
	/**
	 * Name of cookie file with cache times.
	 * 
	 * @access public
	 * @var string
	 */
	public $timer_names = 'BASIC_CACHE_TIMES.php';
	/**
	 * Cache file extension
	 * 
	 * @var string
	 */
	public $cache_file_ext = "php";
	/**
	 * Constructor
	 * Set path to storage folder
	 * 
	 * @param string $storage
	 */
	function __construct($storage = ''){	
		$path = ''; foreach(explode("/", $storage) as $d){
			$path .= $d."/";
			
			if(!is_dir(BASIC::init()->root().$path)){
				@mkdir(BASIC::init()->root().$path);
			}
		}
		$this->storage_path = BASIC::init()->root().$path;
		
		$times = array(); @include $this->storage_path.$this->timer_names;
		$this->times = $times;
		
		register_shutdown_function(array($this, "write"));
	}
	/**
	 * Save caches times in the cookie file.
	 * 
	 * @access public 
	 * @return void
	 */
	function write(){
		$data = ''; foreach($this->times as $k => $v){
			if($data) $data .= ",";
			
			$data .= '"'.$k.'"=>"'.$v.'"';
		}
		if($data){
			$f = @fopen($this->storage_path.$this->timer_names, 'w');
			@fwrite($f, '<?php $times=array('.$data.');');
			@fclose($f);
		}
	}
	/**
	 * Check for available cache.
	 * 
	 * @access public
	 * @param string $name
	 * @return boolean
	 */
	function check($name){
		if($tmp = @file_exists($this->storage_path.$name.".".$this->cache_file_ext)){
			$time = 0; if(isset($this->times[$name])){
				$time = $this->times[$name];
			}
			
			if($time){
				$tmp = time() - @filemtime($this->storage_path.$name.'.'.$this->cache_file_ext) <= $time;
			}
		}
		return $tmp;
	}
	/**
	 * Remove caches in the folder.
	 * 
	 * @access public
     * @param string  [$time] - will remove only caches with higher lifetime.
	 * @param integer [$rate] - performance stuff. Rate for clearing running.
	 * @return void
	 */
	function clear($time = '', $rate = 0){
		if($rate){
			$rate = rand(0, $rate);
		}
		if(!$rate){
			$dir = @opendir($this->storage_path);
			while ($f = @readdir($dir)){
				if($f == '.' || $f == '..' ||
					($time && ($f == $this->timer_names || @filemtime($this->storage_path."/".$f) > time()-$time))
				) continue;
				
				unset($this->times[$f]);
				@unlink($this->storage_path."/".$f);
			}
			@closedir($dir);
		}
	}
	/**
	 * Store or get (if the params $array !== null) arrays.
	 * 
	 * @access public
	 * @return array
	 * @see BasicCacheerInterface::cacheArray
	 */
	function cacheArray($name, $array = null, $time = 0){
		if($array === null){
			$array = array();
			
			if($this->check($name)){
				include $this->storage_path.$name.".".$this->cache_file_ext;
			}
		}else{
			if($time) $this->times[$name] = $time;
			
			$f = @fopen($this->storage_path.$name.".".$this->cache_file_ext, 'w');
			@fwrite($f, '<?php $array='.$this->arrayToPhpString($array).';');
			@fclose($f);
		}
		return $array;
	}
	/**
	 * Store or get (if the params $text !== null) text.
	 * 
	 * @access public
	 * @param string $name
	 * @param string [$text] 
	 * @param integer [$time]
	 * @return string
	 * @see BasicCacheerInterface::cacheText
	 */
function cacheText($name, $text = null, $time = 0){
		if($text === null){
			if($this->check($name)){
//				//ob_start(); include $this->storage_path.$name.".".$this->cache_file_ext;
//			
//				//$text = ob_get_clean();
				
				$file = @fopen($this->storage_path.$name.".".$this->cache_file_ext, 'r');
				$text = ''; while(!@feof($file)){
					$text .= fread($file, 1024);
				}
			}else{
				$text = '';
			}
		}else{
			if($time) $this->times[$name] = $time;
			
			$f = @fopen($this->storage_path.$name.".".$this->cache_file_ext, 'w');
				 @fwrite($f, $text);
				 @fclose($f);
		}
		return $text;
	}
	/**
	 * @see BasicCacheerInterface::cacheXML
	 */
	function cacheXML($name, $xml = null, $time = 0){
		if($xml === null){
			if($this->check($name)){
				$xml = simplexml_load_file($this->storage_path.$name.".".$this->cache_file_ext);
			}else{
				$xml = '';
			}
		}else{
			if($time) $this->times[$name] = $time;
			
			$f = @fopen($this->storage_path.$name.".".$this->cache_file_ext, 'w');
				 @fwrite($f, $xml);
				 @fclose($f);
			
			$xml = simplexml_load_file($this->storage_path.$name.".".$this->cache_file_ext);
		}
		return $xml;
	}	

	
	/**
	 * @param array $data
	 * @return string
	 */
	protected function arrayToPhpString($data){
		$tmp = '';
		foreach ($data as $k => $v){
			if($tmp) $tmp .= ',';
			
			$tmp .= "'".$k."'=>".(is_array($v) ? $this->arrayToPhpString($v) : "'".$v."'")."";
		}
		return "array(".$tmp.")";
	}	
}