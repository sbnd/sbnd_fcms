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
* @package basic.upload
* @version 7.0.4
*/


/**  
 * Basic uploader class
 *
 * @author Evgeni Baldzhiyski
 * @version 2.3
 * @since 22.01.2007
 * @package basic.upload
 */
class BasicUpload {
    /**
     * 
     * Permission type
     * @var array
     * @access private
     */
    protected $permType = array(); //if count == 0 every type is ok
	/**
	 * 
	 * File size
	 * @var int
	 * @access public
	 */
	var $size 			= 0;
	/**
	 * 
	 * Upload dir
	 * @var string
	 * @access public
	 */
	var $upDir 			= 'upload/';
	/**
	 * 
	 * Max filesize
	 * @var string
	 * @access public
	 */
	var $maxSize 		= '100K';	// Size is in K (1000 == 1M) (1000*1000 == 1G)
	/**
	 * 
	 * @todo description
	 * @var string
	 * @access public
	 */
	var $AsFile 		= '';
	/**
	 * 
	 * @todo description
	 * @var string
	 * @access public
	 */
	var $rand 			= 'true';
	/**
	 * 
	 * Auto create directory if the given not exists
	 * @var boolean
	 * @access public
	 */
	var $autoCreateDir 	= true;
	/**
	 * 
	 * Temporary file name
	 * @var string
	 * @access public
	 */
	var $tmpName 		= '';
	/**
	 * 
	 * The filename with the extension type
	 * @var string
	 * @access public
	 */
	var $fullName 		= '';
	/**
	 * 
	 * @todo description
	 * @var string
	 * @access public
	 */
	var $fileCtnType 	= '';
	/**
	 * 
	 * @todo description
	 * @var string
	 * @access public
	 */
	var $farr 			= '';
	/**
	 * 
	 * File name
	 * @var string
	 * @access public
	 */
	var $name 			= '';
	/**
	 * 
	 * File type
	 * @var string
	 * @access public
	 */
	var $type 			= '';
	/**
	 * 
	 * The name that will be returned after uploading ends
	 * @var string
	 * @access public
	 */
	var $returnName 	= '';
	/**
	 * 
	 * Errors
	 * @var int
	 * @access public
	 */
	var $error 			= 0;
	// Events
	/**
	 * 
	 * When uploading ends
	 * @var string
	 * @access public
	 */
	var $onComplete 	= '';
	/**
	 * 
	 * Error on uploading
	 * @var string
	 * @access public
	 */
	var $onError 		= '';
	/**
	 * 
	 * Delete event
	 * @var string
	 * @access public
	 */
	var $onDelete 		= '';
	
    /**
     * Load system variables for $file
     *
     * @param string $file
     */
    function __construct($file){
        if(isset($_FILES[$file])){
            $this->tmpName 		= $_FILES[$file]['tmp_name'];
            $this->fullName 	= $_FILES[$file]['name'];
            $this->size 		= $_FILES[$file]['size'];
            $this->fileCtnType 	= $_FILES[$file]['type'];
            $this->farr 		= $_FILES[$file]['error'];

           	preg_match("/(.+)\.([^\.]+)$/", $this->fullName, $exp);
			
            if(isset($exp[1])) $this->name = $exp[1];
			if(isset($exp[2])) $this->type = strtolower($exp[2]);

           // $this->test();
        }else{
        	if($file !== null) $this->error = 5; $this->onError();
        }
    }
    /**
     * Add perm type
     *
     * @param array $arr
     */
    function setType($arr){
    	if(is_array($arr)){
            foreach($arr as $v){
                $this->permType[$v] = true;
            }
            return;
    	}
    	$this->permType[$arr] = true;
    }
    /**
     * Delete perm type
     *
     * @param array $arr
     */
    function unsetType($arr){
    	if(is_array($arr)){
            foreach($arr as $v){
                if(isset($this->permType[$v])) $this->permType[$v] = false;
            }
            return ;
    	}
    	if(isset($this->permType[$arr])) $this->permType[$arr] = false;
    }
    
    /**
     * Check for file's error. If there is a error an error's code will be returned.
     * 
  	 * @error-codes
  	 * 	... object code errors ...
	 *	 (1) The uploaded file exceeds the upload_max_filesize directive in php.ini.
	 *   (2) The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
	 *   (3) The uploaded file was only partially uploaded.
	 *   (4) No file was uploaded.
	 *   (5) No exist file variable in request
	 *  ... PHP5 standart code errors ...
	 *   (10) No success reamed uploaded file.
	 *   (11) The uploaded file exceeds the max field directive
	 *   (12) The uploaded file type is no permited
	 *   (13) The uploaded file name is olready exist
	 *   (14) The uploaded file can't copied in destination directory
	 *   (15) Can't remuved file.
	 *   (16) Upload folder does't exist and can't create it.
	 *   (17) Can not create temporary file.
     *
     * @return int
     */
    function test(){
    	if($this->error) return $this->error;
    	
        if(!$this->tmpName){
        	$this->error = 4; $this->onError(); return $this->error;
        }
        $cSize = BASIC::init()->stringToBite($this->maxSize);
	    if($cSize != 0 && $this->size > $cSize){
           	$this->error = 11;  $this->onError(); return $this->error;
        }
    	if(count($this->permType) != 0){
        	if(!array_key_exists($this->type,$this->permType) || !$this->permType[$this->type]){
        		$this->error = 12;  $this->onError(); return $this->error;
        	}
        }
        if($this->farr && $this->fullName){
        	$this->error = $this->farr;  $this->onError(); return $this->error;
	    }
	    return 0;
    }
    /**
     * Write new file
     *
     * @return string
     */
    function add(){ 
        if($this->error) return '';
    	 
        $this->_add();
		$this->onComplete();
		
		return $this->returnName;
    }
	/**
     * Edit file
     *
     * @param string $oldFile
     * @return string
     */
    function edit($oldFile = ''){
    	if($this->error) return '';

    	$file_new_name = $this->_add();
        if($oldFile && !$this->error){
    		$this->_delete($oldFile);
    		
    		$this->returnName = preg_replace("#^".str_replace("#", "\\#", $this->upDir)."/?#", "", $oldFile);
	        
    		if(@rename($this->_path().$file_new_name, $this->_path().$this->returnName)){
	        	$this->onComplete();
	            return $this->returnName;
	        }
        }
        $this->onComplete();
	    return $this->returnName;
    }   
    /**
     * Delete file
     *
     * @param string $file
     * @return boolean
     */
    function delete($file){
    	if($this->error) return '';
    	
    	$ok = $this->_delete($file);
    	
    	$this->onDelete($file);
   
        return $ok;
    }
    /**
     * 
     * Move the uploaded file
     * @param string $folder
     * @param string $root
     */
    function move($folder, $root = ''){
    	if(!$root){
    		$root = $this->_path();
    	}else{
    		$root = BASIC::init()->validPath($root);
    	}
		$folder = BASIC::init()->validPath($folder);

    	if(file_exists($this->_path().$this->returnName)){
    		if($this->autoCreateDir) $this->createDir($folder,$root);

    		copy($this->_path().$this->returnName, $root.$folder.$this->returnName);
    		unlink($this->_path().$this->returnName);
    	}
    }
    /**
     * 
     * Get path
     */
    function getPath(){
    	return $this->_path();
    }
    /**
     * 
     * Events (errors and success) mapping
     * @param int $code
     * @return string
     */
    function getTextExections($code){
    	switch ($code){
    		case 1: return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
		    case 2: return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
		    case 3: return 'The uploaded file was only partially uploaded.';
		    case 4: return 'No file was uploaded.';
		    case 5: return 'No exist file varisble in request.';

		    case 10: return 'No success reamed uploaded file.';
		    case 11: return 'The uploaded file exceeds the max field directive';
		    case 12: return 'The uploaded file type is no permited';
		    case 13: return 'The uploaded file name is olready exist';
		    case 14: return "The uploaded file can't copied in destination directory";
		    case 15: return "Can't remuved file.";
		    case 16: return "Upload folder does't exist and can't create it.";

		     //... in PHP5 exist over error ...
		    default: return "Code exeption (".$code.")";
    	}
    }
    /**
     * 
     * On complete event handler
     * @access private
     */
    protected function onComplete(){
    	if($this->onComplete){
    		if(is_array($this->onComplete)){
    			$c = $this->onComplete[0];
    			$m = $this->onComplete[1];
    			$c->$m($this);
    		}else{
    			$c = $this->onComplete;
    			$c($this);
    		}
    	}
    }
    /**
     * 
     * On error event hadnler
     * @access private
     */
    protected function onError(){
    	if($this->onError){
    		if(is_array($this->onError)){
    			$c = $this->onError[0];
    			$m = $this->onError[1];
    			$c->$m($this->error, $this);
    		}else{
    			$c = $this->onError;
    			$c($this->error,$this);
    		}
    	}
    }
    /**
     * 
     * On delete event handler
     * @param string $file
     * @access private
     */
    protected function onDelete($file){
     	if($this->onDelete){
     		$this->fullName = $file;
    		if(is_array($this->onDelete)){
    			$c = $this->onDelete[0];
    			$m = $this->onDelete[1];

    			$c->$m($this);
    		}else{
    			$c = $this->onDelete;
    			$c($this);
    		}
    	}
    }
    /**
     * Create path
     *
     * @return $string
     * @access private
     */
    protected function _path(){
    	return BASIC::init()->ini_get('root_path').$this->upDir."/";
    }
    /**
     * 
     * Delete file
     * @param $file
     * @access private
     * @return boolean
     */
    protected function _delete($file){
    	$file = str_replace($this->upDir, "", $file);
    	
        if($file && file_exists($this->_path().$file)){
            if(unlink($this->_path().$file)){
                return true;
            }
        }
        $this->error = 15;
        $this->onError();
        return false;	
    }
	protected function nameGenerator($as){
       	$name = $as.round(rand(100000, 999999));
       	
        if(file_exists($this->_path().$name.".".$this->type)){
        	return $this->nameGenerator($as);
        }
    	return $name;
    }
	/**
     * Rename uploaded file
     *
     * @param string $NewName
     * @return string
     */
    protected function _rename($as){
        $name = $this->nameGenerator($as);
    	
        if(@rename($this->_path().$this->fullName, $this->_path().$name.".".$this->type)){
            return $name;
        }
        $this->error = 10;
        $this->onError();
        return false;
    }   
    /**
     * 
     * Method for adding the file  
     * 
     * @access private
     * @return mixed (string, int)
     */
    protected function _add(){
    	if($this->autoCreateDir) $this->createDir();

        if(file_exists($this->_path().$this->name.".".$this->type)){
        	if($this->rand == 'true' || $this->rand == true || $this->rand == 1){
        		$this->fullName = time().".tmp";
        	}else{
				$this->error = 13;  $this->onError(); return '';
        	}
        }
        $copyfile = $this->_path().$this->fullName;
        if(copy($this->tmpName, $copyfile)){
            if($this->rand == 'true' || $this->rand == true || $this->rand == 1){
            	$this->returnName = $this->_rename($this->AsFile).".".$this->type;
            	
                return $this->returnName;
            }
            $this->returnName = $this->name.".".$this->type;
          
            return $this->returnName;
        }else{
        	$this->error = 14;  $this->onError(); return '';
        }
    }
    /**
     * Create folder
     *
     * @param string $name
     * @param string $root
     * @param  $permission
     * @access private
     */
   protected function createDir($name = '', $root = '', $perm = 0777){
    	if(!$root){
    		$root = $this->_path();
    	}else{
    		$root = BASIC::init()->validPath($root);
    	}

    	if(!is_dir($root.$name)){
    		if(!@mkdir(BASIC::init()->ini_get('root_path').$this->upDir.$name."/", $perm)){
    			$this->error = 16;
    			$this->onError();
    		}
    	}
    }
}

/**
 * Class for download files.
 *
 * @name BasicDownload
 * @author Evgeny Baldzhiyski
 * @version 1.0
 * @since 26.08.2011
 * @package basic.upload
 */
class BasicDownload{
	/**
	 * 
	 * Folder name
	 * @var string
	 * @access private
	 */
	protected $folder = '';
	/**
	 * 
	 * The file name with extension type
	 * @var string
	 * @access private
	 */
	protected $fullFileName = '';
	/**
	 * 
	 * File name
	 * @var string
	 * @access private
	 */
	protected $name = '';
	/**
	 * 
	 * File type
	 * @var string
	 * @access private
	 */
	protected $type = '';
	/**
	 * 
	 * File size
	 * @var int
	 * @access private
	 */
	protected $size = 0;
	
	/**
	 * 
	 * Constructor
	 * @param  $file
	 * @param string $folder
	 */
	function __construct($file, $folder = ''){
		if(!file_exists(BASIC::init()->ini_get('root_path').$folder.$file)){
			throw new Exception("Can't find file '".BASIC::init()->ini_get('root_path').$folder.$file."'.");
		}
		if(!$file || preg_match('/[:"\?\*\/\\]+/<>\|', $file)){
			throw new Exception("The file name is not falid.");
		}
		$this->fullFileName = $file;
		
		$ex = self::explodeFileName($file);
		$this->name = $ex[0];
		$this->type = $ex[1];
		
		$this->folder = $folder."/";
		$this->size = filesize(BASIC::init()->ini_get('root_path').$folder.$file);
	}
	/**
	 * 
	 * Get file type
	 */
	function getType(){
		return $this->type;
	}
	/**
	 * 
	 * Get file name
	 */
	function getName(){
		return $this->name;
	}
	/**
	 * 
	 * Get file size
	 */
	function getSize(){
		return $this->size;
	}
	/**
	 * The actual download method
	 * 
	 * @param string $public_name
	 */
    function download($public_name){
    	$ex = self::explodeFileName(str_replace(" ","_",$public_name));
    	
    	$name = $ex[0]; 
    	$type = $ex[1] ? $ex[1] : $this->type; 
    	
    	if(isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "MSIE")){
    		$name = rawurlencode($name);
    	}

		$f = @fopen(BASIC::init()->ini_get('root_path').$this->folder.$this->fullFileName, "rb");

		header("Content-Type: ".$type);
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=".$name.".".$type);	    
		
		header('Expires: 0');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Pragma: public');		
		
		header("Content-Length: ".$this->size);

		while (!feof($f)) {
		   print fread($f, (1024*1024));
		}
		@fclose($f); die();
    }
	/**
	 * Browser download data like file.
	 * 
	 * @param string $public_name
	 * @param string $data
	 * @param string [$encoding]
	 * @static
	 * @access public
	 */
    static public function downloadSource($public_name, $data, $encoding = 'utf-8'){
    	$ex = self::explodeFileName(str_replace(" ","_",$public_name));
    	$name = $ex[0]; $type = $ex[1];     	
    	
    	if(isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){
    		$name = rawurlencode($name);
    	}
    	$contentType = BASIC::init()->getMimeType($type);
    	if(!$contentType){
    		$contentType = "application/force-download";
    	}
    	if($encoding){
    		$contentType .= '; charset:'.$encoding;
    	}
    	
		header('Expires: 0');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Pragma: public');
	        	
    	header('Content-Description: File Transfer');
		header("Content-Type: ".$contentType.";");
		header("Content-Disposition: attachment; filename=".$name.($type ? ".".$type : ''));
		header("Content-Length: ".strlen($data));

		die((strtolower($encoding) == 'utf-8' ? "\xEF\xBB\xBF" : '').$data);
    }
	/**
	 * 
	 * Explode the file data (name and type)
	 * 
	 * @param string $name
	 * @static
	 * @access public
	 * @return array(name, type)
	 */
	static public function explodeFileName($fullname){
    	$tmp = ''; $name = '';
		foreach (explode(".", $fullname) as $v){
			if($tmp){
				if($name) $name .= "."; $name .= $tmp;
			}
			$tmp = $v;
		}
		if(!$name){
			return array($tmp, '');
		}
		return array($name, $tmp);
	}
}