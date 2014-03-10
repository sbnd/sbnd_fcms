<?php
/**
* SBND F&CMS - Framework & CMS for PHP developers
*
* Copyright (C) 1999 - 2014, SBND Technologies Ltd, Sofia, info@sbnd.net, http://sbnd.net
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
* @package basic.filesystem
* @version 7.0.6  
*/

/**
 * 
 * @author Evgeni Baldziyski
 *
 */
interface BasicFileSystemInterface{
	/**
	 * @return array|string
	 */
	function read($returnArray = true);
	function close();
	function move($to);
	function copy($to);
	function remove();
	function isFile();
}
/**
 * @author Evgeni Baldziyski
 * @version 0.1 alpha
 * @since 01.10.2011
 */
class BASIC_FILE {
	/**
	 * @access private
	 * @var resource
	 */
	protected $handler = null;
	/**
	 * 
	 * Open file
	 * @param string $path
	 * @param string [$mode]
	 * @throws Exception
	 * @access public
	 */
	public function open($path, $mode = 'r'){
		if(is_dir(BASIC::init()->root().$path)){
			return ($this->handler = new BasicFolder(BASIC::init()->root().$path, $mode));
		}else if(is_file(BASIC::init()->root().$path)){
			return ($this->handler = new BasicFolder(BASIC::init()->root().$path, $mode));
		}else{
			throw new Exception("Can't find resource '".BASIC::init()->root().$path."' ");
		}
	}
	/**
	 * Close file
	 * 
	 * @access public
	 */
	public function close(){
		$this->handler->close();
	}
}
/**
 * @author Evgeni Baldziyski
 */
class BasicFile implements BasicFileSystemInterface{
	/**
	 * @access private
	 * @var string
	 */
	protected $type = '';
	/**
	 * @access private
	 * @var integer
	 */
	protected $size = 0;
	/**
	 * @access private
	 * @var resource
	 */
	protected $handler = null;
	/**
	 * @access private
	 * @var string
	 */
	protected $path = '';
	
	/**
	 * Constructor
	 *  
	 * @param string $path
	 * @param string [$mode]
	 */
	function __construct($path, $mode = 'r'){
		if(!$this->handler = @fopen($path, $mode)){
			throw new Exception("Cant't find file '".$path."' ");
		}
	}
	/**
	 * @access public
	 * @see BasicFileSystemInterface::close()
	 */
	function close(){
		@fclose($this->handler);
	}
	/**
	 * @access public
	 * @param string $to
	 * @see BasicFileSystemInterface::move()
	 */
	function move($to){

	}
	/**
	 * @access public
	 * @param string $to
	 * @see BasicFileSystemInterface::copy()
	 */
	function copy($to){

	}
	/**
	 * @access public
	 * @see BasicFileSystemInterface::remove()
	 */
	function remove(){
		@unlink($this->path);
	}
	/**
	 * @access public
	 * @param boolean [$returnArray]
	 * @return string|array
	 * @see BasicFileSystemInterface::read()
	 */
	function read($returnArray = true){
		if($returnArray){
			return @file($path);
		}else{
			$buffer = ''; while(!@feof($this->handler)){
				$buffer .= fread($this->handler, 1024);
			}
			return $buffer;
		}
	}
	/**
	 * @access public
	 * @return boolean
	 * @see BasicFileSystemInterface::isFile()
	 */
	function isFile(){
		return true;
	}	
}
/**
 * @TODO need to complete with error messengers.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1 ALPHA
 * @since 26.03.2012
 */
class BasicFolder implements BasicFileSystemInterface{
	/**
	 * access private
	 * @var string
	 */
	protected $path = '';
	/**
	 * @access private
	 * @var resource
	 */
	protected $handler = null;
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param string $path
	 * @return void
	 */
	function __construct($path){
		$this->path = $path;
		
		if(!is_dir($path)){
			throw new Exception("Cant't find file '".$path."' ");
		}
	}
	/**
	 * Open folder
	 * 
	 * @access public
	 * @param string $mode
	 * @return resource
	 */
	function open($mode){
		if($this->handler){
			$this->handler = @opendir($this->path, $mode);
		}
		return $this->handler;
	}
	/**
	 * @access public
	 * @see BasicFileSystemInterface::close()
	 */
	function close(){
		if($this->handler){
			@closedir($this->handler);
		}
	}
	/**
	 * Create folder
	 * 
	 * @access public
	 * @param string $to
	 */
	function create($to){
		if(!is_dir($to)){
			if(!@mkdir($to)){
				throw new Exception("The folder do not exist and can't create in '".$to."' "); return false;
			}
		}	
	}
	/**
	 * @access public
	 * @param string $to
	 * @see BasicFileSystemInterface::move()
	 */	
	function move($to){
		//$this->create($to);
		$this->_move($this->path, $to);
	}
	/**
 	 * @access public
	 * @param string $to
	 * @see BasicFileSystemInterface::copy()
	 */
	function copy($to){
		//$this->create($to);
		$this->_move($this->path, $to, false);
	}
	/**
	 * Help method of method move
	 * 
	 * @access private
	 * @param string $path
	 * @param string $to
	 * @param boolean [$is_move
	 * @return void
	 */
	protected function _move($path, $to, $is_move = true){
		$error = false; 
		if(is_file($path)){
			if(!@move($path, $to)){
				//throw new Exception("Can't copy file to ".$to."' "); return true;
			}
			if($is_move){
				if(!@unlink($path)){
					 //throw new Exception("Can't delete file ".$path."' "); return true;
				}
			}
		}else{
			if(!@mkdir($to) && $this->path != $path){
				//throw new Exception("Can't make folder '". $path."'"); return true;
			}
			if($dir = @opendir($path)){
				//throw new Exception("Can't list folder '". $path."'"); return true;
				
				while ($file = @readdir($dir)) {
					if($file == '.' || $file == '..') continue;
					
					if(is_file($path."/".$file)){
						if(!@copy($path."/".$file, $to."/".$file)){
							//throw new Exception("Can't copy file to ".$to."/".$file."' "); return true;
						}
						if($is_move){				
							if(!@unlink($path."/".$file)) {
								//throw new Exception("Can't delete file ".$path."/".$file."' "); return true;
							}
						}
					}else{
						$error = $this->_move($path."/".$file, $to."/".$file, $is_move);
						if($error) return $error;
					}
				}
				
				@closedir($dir);
				
				if($is_move){
					if(!@rmdir($path."/") && $this->path != $path){
						//throw new Exception("Can't delete folder '". $path."'"); return true;
					}
				}				
			}
		}
	}
	/**
	 * Remove folder
	 * 
	 * @access public
	 * @return void
	 * @see BasicFileSystemInterface::remove()
	 */
	function remove(){
		$this->_remove($this->path);
	}
	/**
	 * Help method of method remove
	 * 
	 * @access private
	 * @param string $path
	 * @return void
	 */
	protected function _remove($path){
		$error = false; 
		if(is_file($path)){
			if(!@unlink($path)){
				 //throw new Exception("Can't delete file ".$path."' "); return true;
			}
		}else{
			if($dir = @opendir($path)){
				//throw new Exception("Can't list folder '". $path."'"); return true;
				
				while ($file = @readdir($dir)) {
					if($file == '.' || $file == '..') continue;
					
					if(is_file($path."/".$file)){
						if(!@unlink($path."/".$file)) {
							//throw new Exception("Can't delete file ".$path."/".$file."' "); return true;
						}
					}else{
						$error = $this->_remove($path."/".$file);
						if($error) return $error;
					}
				}				
			}
			@closedir($dir);
			@rmdir($path."/");
		}
	}
	/**
	 * @access public
	 * @param boolean [$returnArray]
	 * @return string|array
	 * @see BasicFileSystemInterface::read()
	 */
	function read($returnArray = true){
		if($returnArray){
			$files = array();
		}else{
			$files = '';
		}
		if($dir = $this->open(null)){
			while ($file = readdir($dir)){
				if($file == '.' || $file == '..') continue;
				
				if($returnArray){
					$files[] = $file;
				}else{
					if($files) $str .= ',';
					
					$files .= $file;
				}
			}
		}
		return $files;
	}
	/**
	 * @access public
	 * @return boolean
	 * @see BasicFileSystemInterface::isFile()
	 */
	function isFile(){
		return true;
	}	
}