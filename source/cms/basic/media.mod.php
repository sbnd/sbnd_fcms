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
* @package basic.media
* @version 7.0.4  
*/

/**
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.media
 */
interface BasicMediaPluginInterface{
	/**
	 * HTML generator
	 * 
	 * @param hashmap $attributes
	 * @return string
	 */
	function generate($attributes, $info = null);
}
/**
 * options: 
 * 	pluginspage:(string|http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash)
 * 	version:(number|8)
 * 	config:(string in css style)
 * 	allowscriptaccess:(true|false)
 * 	allowfullscreen:(true|false)
 * 	quality:(string|high)
 * 	vmode:(string|Opaque)
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.media
 */
class FlashMediaPlugin implements BasicMediaPluginInterface{
	/**
	 * @access private
	 * @var array
	 */
	protected $attribute = array();
	/**
	 * @access private
	 * @var array
	 */
	protected $params = array();
	/**
	* @access private
	 * @var string|integer
	 */
	protected $id = '';
	/**
	 * @access private
	 * @var string
	 */
	protected $flashvars = '';
	/**
	 * @access private
	 * @var string
	 */
	protected $version = '';
	/**
	 * Configure parameters
	 * 
	 * @access private
	 * @return void
	 */
	protected function prepare(){
		if(!$this->attribute['width'])  unset($this->attribute['width']);
		if(!$this->attribute['height']) unset($this->attribute['height']);
		
		if(isset($this->attribute['name'])) unset($this->attribute['name']);
		
		$this->version = '8';
		if(isset($this->attribute['version'])){
			$this->version = $this->attribute['version'];
			unset($this->attribute['aversion']);
		}
		$this->version = 'version='.$this->version.',0,0,0';
		
		$this->params = array();
		
		$this->params['src'] = $this->attribute['src']; unset($this->attribute['src']);
 		
		if(isset($this->attribute['config'])){
			$this->params = BASIC_GENERATOR::init()->convertStringAtt($this->attribute['config'], array(';',"&#59"),array(":","&#58"));
			unset($this->attribute['config']);
		}
		
		if(!isset($this->params['pluginspage'])) $this->params['pluginspage'] = "http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash";	
		
		if(isset($this->attribute['allowscriptaccess'])){
			$this->params['allowscriptaccess'] = $this->attribute['allowscriptaccess'];
			unset($this->attribute['allowscriptaccess']);
		}
		if(isset($this->attribute['allowfullscreen'])){
			$this->params['allowfullscreen'] = $this->attribute['allowfullscreen'];
			unset($this->attribute['allowfullscreen']);
		}	
		$this->params['quality'] = 'high';
		if(isset($this->attribute['quality'])){
			$this->params['quality'] = $this->attribute['quality'];
			unset($this->attribute['quality']);
		}
		$this->params['vmode'] = 'Opaque';
		if(isset($this->attribute['vmode'])){
			$this->params['vmode'] = $this->attribute['vmode'];
			unset($this->attribute['vmode']);
		}
		
		$this->flashvars = '';
		if(isset($this->attribute['variables'])){
			$this->flashvars = $this->attribute['variables'];
			
			$this->flashvars = str_replace(":", "=", $this->flashvars);
			$this->flashvars = str_replace(";", "&", $this->flashvars);

			unset($this->attribute['variables']);
		}
		
		$this->id = '';
		if(isset($this->attribute['id'])){
			$this->id = $this->attribute['id'];
			unset($this->attribute['id']);
		}else{
			$this->id = uniqid("media_");
		}
	}
	/**
	 * Generate html for flash player
	 * 
	 * @access public
	 * @param array $attribute
	 * @param null $info not used
	 */
	function generate($attribute, $info = null){
		$this->attribute = $attribute;
		
		$this->prepare();
		
		$tmp = '';
		
		$tmp .= '<object codebase="http://active.macromedia.com/flash6/cabs/swflash.cab#'.$this->version.'" id="'.$this->id.'" '.
			'classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" '.BASIC_GENERATOR::init()->convertAtrribute($this->attribute).'>'."\n";
		
		foreach($this->params as $k => $v){
			if($k == 'src'){
				$k = 'movie';
				$v .=($this->flashvars ? '?'.$this->flashvars : $this->flashvars);
			}
			$tmp .= '<param name="'.$k.'" value="'.$v.'" />'."\n";
		}
		
		$this->attribute = $this->params+$this->attribute;
		
		if($this->flashvars) $this->attribute['flashvars'] = $this->flashvars;
		$this->attribute['name'] = $this->id;
		
		$tmp .= '<embed '.BASIC_GENERATOR::init()->convertAtrribute($this->attribute).' />'."\n";
		$tmp .= '</object>';

		return $tmp;
	}
}
/**
 * options: 
 * 	pluginspage:(string|http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash)
 * 	version:(number|8)
 * 	config:(string in css style)
 * 	allowscriptaccess:(true|false)
 * 	allowfullscreen:(true|false)
 * 	quality:(string|high)
 * 	vmode:(string|Opaque)
 *
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.media
 */
class FlashVideoMediaPlugin extends FlashMediaPlugin{
	/**
	 * Add additional settings
	 * @access public
	 * @return void
	 */
	protected function prepare(){
		parent::prepare();
		
		$this->flashvars = 'file='.$this->params['src'].
			(isset($this->attribute['width']) && $this->attribute['width'] ? '&width='.$this->attribute['width'] : '').
			(isset($this->attribute['height']) && $this->attribute['height'] ? '&height='.$this->attribute['height'] : '').
			($this->flashvars ? '&'.$this->flashvars : '');
	
		$this->params['src'] = BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'scripts/flash/mediaplayer.swf';
	}
}
/**
 * options: 
 * 	play:(true|false)
 * 	loop:(true|false)
 * 	controler:(true|false)
 * 	target:(string|QuickTimePlayer)
 * 	pluginspage:(string|http://www.apple.com/quicktime/download/indext.html)
 * 	targetcache:(true|false)
 * 	cache:(true|false)
 * 	bgcolor:(string)
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.media
 */
class QuickTimeVideoMediaPlugin implements BasicMediaPluginInterface{
	/**
	 * Generate html for quick time video
	 * 
	 * @access public
	 * @param array $attribute
	 * @param null $info not used
	 */
	function generate($attribute, $info = null){
		if(!$attribute['width'])  unset($attribute['width']);
		if(!$attribute['height']) unset($attribute['height']);
		
		$attribute['autoplay'] 	 =(isset($attribute['play']) ? true : false);
		$attribute['loop'] 		 =(isset($attribute['loop']) ? true : false);
		$attribute['controller'] =(isset($attribute['controller']) ? true : false);
		
		if(!isset($attribute['target'])) 		$attribute['target'] = 'QuickTimePlayer';
		if(!isset($attribute['pluginspage'])) 	$attribute['pluginspage'] = 'http://www.apple.com/quicktime/download/indext.html';
		if(!isset($attribute['targetcache'])) 	$attribute['targetcache'] = 'true';
		if(!isset($attribute['cache'])) 		$attribute['cache'] = 'true';
		
		return('<embed '.BASIC_GENERATOR::init()->convertAtrribute($attribute).'/>');
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.media
 */
class ImageMediaPlugin implements BasicMediaPluginInterface{
	/**
	 * Generate html for image
	 * 
	 * @access public
	 * @param array $attribute
	 * @param null $info not used
	 */
	function generate($attribute, $info = null){
		if(!isset($attribute['alt'])){
			$attribute['alt'] = $attribute['name'];
		}
		return '<img '.BASIC_GENERATOR::init()->convertAtrribute($attribute)."/>";
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.media
 */
class MediaPlugin implements BasicMediaPluginInterface{
	/**
	 * Generate html for media, dependens on media type
	 * 
	 * @access public
	 * @param array $attribute
	 * @param null $info not used
	 */
	function generate($attribute, $info = null){
		if($info['type'] == 13 || $info['type'] == 4 || $info['type'] == -4){
			$o = new FlashMediaPlugin();
		}else if($info['type'] == -1){
			$o = new FlashVideoMediaPlugin();
		}else if($info['type'] == -2){
			$o = new QuickTimeVideoMediaPlugin();
		}else if(!isset($info['mime'])){
			return '<a href="'.$attribute['src'].'">'.$attribute['name'].'</a>';
		}else{
			$o = new ImageMediaPlugin();
		}
		return $o->generate($attribute);
	}
}
/**
 * Media generator. Images, Videos, Other
 * 
 * @author Evgeni Baldzhiyski
 * @version 2.1
 * @since 24.01.2007
 * @package basic.media
 */
class BASIC_MEDIA extends BASIC_CLASS{
	
	/**
	 * ifexist : alternative picture
	 * @access public
	 * @var string
	 */
	var $defpic = ''; 
	/**
	 * ifexist : base picture
	 * @access public
	 * @var string
	 */
	var $img = ''; // 
	/**
	 * ifexist : runtime get file url
	 * @access public
	 * @var string
	 */
	var $var = ''; 
	/**
	 * ifexist : file folder
	 * @access public
	 * @var string
	 */
	var $folder = ''; 
	/**
	 * picture type
	 * @access public
	 * @var integer
	 */
	var $type = 0;
	/**
	 * media type
	 * @access public
	 * @var string
	 */
	var $ext = ''; 
	/**
	 * @access public
	 * @var boolean
	 */
	var $fixed = false; 
	/**
	 * @access public
	 * @var boolean
	 */
	var $absolute = false;
	/**
	 * picture width
	 * @access public
	 * @var integer
	 */
	var $width = 0;
	/**
	 * picture height
	 * @access public
	 * @var integer
	 */
	var $height = 0;
	
	/**
	 * graphic path
	 * @access public
	 * @var integer
	 */
	var $src = '';
	
	/**
	 * @access public
	 * @var array
	 */
	var $info = array();
	/**
	 * system properties
	 * @access public
	 * @var boolean
	 */
	var $is_full = false;
	/**
	 * @access public
	 * @var string
	 */
	var $fullPath = '';
	/**
	 * @access public
	 * @var string
	 */
	var $virtualPath = '';
	/**
	 * @access private
	 * @staticvar
	 * @var hashmap
	 */
	protected static $controlers = array();
	/**
	 * Registrate plugins
	 * @static
	 * @access public
	 * @param string $key
	 * @param BasicMediaPluginInterface $plugin
	 * @return void
	 */
	static public function addPlugin($key, $plugin){
		self::$controlers[$key] = $plugin;
	}
	/**
	 * Delete plugins. Save the default plugin with empty key.
	 * @static
	 * @access public
	 * @param string $key
	 * @return void
	 */
	static public function deletePlugin($key){
		if($key){
			unset(self::$controlers[$key]);
		}
	}
	
	/**
	 * Constructor
	 * 
	 *  array signature:
	 * [folder] folder path
	 * [fullpath] display full virtual path
	 * [default] path to default picture
	 * 
	 * @access public
	 * @param string $img
	 * @param array [att]
	 * @return void
	 */
	function __construct($img, $att = array()){
		if(isset($att['folder']) && $att['folder']){
			$this->folder = $att['folder'].(!preg_match("/\/$/", $att['folder']) ? "/" : "");
		}else{
			$img = str_replace(BASIC::init()->ini_get('root_virtual'), "", $img);
			$tmp = explode("/", $img);
			$num = count($tmp);
			if($num > 1){
				for($i = 0; $i < $num - 1; $i ++){
					$this->folder .= $tmp[$i]."/";
				}
				$img = $tmp[$num - 1];
			}else{
				$this->folder = BASIC::init()->ini_get('image_path');
			}
		}
		$img = str_replace($this->folder, "", $img);
		
		if($tmp = explode('.', $img)){
			$this->ext = strtolower($tmp[count($tmp) -1]);
		}		
		if(isset($att['default'])){
			$this->defpic = str_replace(BASIC::init()->virtual(), "", $att['default']);
		}
		if(isset($att['fixed']) &&($att['fixed'] == 'true' || $att['fixed'] == '1')){
			$this->fixed = true;
		}
		if(isset($att['absolute']) &&($att['absolute'] == 'true' || $att['absolute'] == '1')){
			$this->absolute = true;
		}
		
		$this->is_full =(isset($att['fullpath']) && $att['fullpath'] != 'false');
		
		$this->getPaths();
		
		$link = explode("?", $img);
		$this->img = $link[0];
		if(isset($link[1])){
			$this->var = $link[1];
		}
		$this->info['type'] = 0;
		if($this->src = $this->src()){
			//die($this->src);
			if($this->var){
				$this->info = @getimagesize($this->src);
			}else{
				$this->info = @getimagesize($this->fullPath.$this->img);
			}
			if($this->info){
				$this->info['type'] = $this->info[2];
				
				$this->width = $this->info[0];
				$this->height = $this->info[1];
				
				$ex = explode("/", $this->info['mime']);
				$this->info['extent'] = $ex[1];
				
				$this->info['width'] = $this->info[0];
				unset($this->info[0]);
				$this->info['height'] = $this->info[1];
				unset($this->info[1]);
			}else{
				$this->info['type'] = 0;
				preg_match('/[^.]+$/', $this->img, $ex);
				if($ex[0] == 'flv' || $ex[0] == 'mp3'){
					$this->info['type'] = - 1;
				}else if($ex[0] == 'mov'){
					$this->info['type'] = - 2;
				}else if($ex[0] == 'swf'){
					$this->info['type'] = - 4;
				}
				$this->info['width'] = 0;
				$this->info['height'] = 0;
				$this->info['extent'] = $ex[0];
			}
		}
	}
	/**
	 * Set root and virtual root path
	 * 
	 * @access public
	 * @return void
	 */
	function getPaths(){
		$this->fullPath = BASIC::init()->ini_get('root_path').$this->folder;
		$this->virtualPath = BASIC::init()->ini_get('root_virtual').$this->folder;
	}
	/**
	 * Return full path to media
	 * 
	 * @access public
	 * @return string
	 */
	function src(){
		if(!file_exists($this->fullPath.$this->img) || !$this->img){
			if(!$this->defpic)
				return '';
			
			$this->img = '';
			$this->var = '';
			
			$link = explode("?", $this->defpic);
			$this->folder = $link[0];
			if(isset($link[1])){
				$this->var = $link[1];
			}
			$this->getPaths();
		}
		return $this->virtualPath.$this->img .($this->var ? '?'.$this->var : '');
	}
	/**
	 * Resize file
	 * Warning:0 for wight and height is flag for no resized.
	 * Return array('width'=>int,'height'=>int);
	 *
	 * @access public
	 * @param integer $width
	 * @param integer $height
	 * @return hashmap array
	 */
	function size($width = 0, $height = 0){
		$width =(int) $width;
		$height =(int) $height;
		
		if(!$this->src){
			return array('width' => 0, 'height' => 0);
		}
		if($this->info['type'] == - 1 || $this->info['type'] == - 2 || $this->info['type'] == - 4){
			return array('width' => $this->width = $width, 'height' => $this->height = $height);
		}
		if(!$width && !$height){
			return array('width' => $this->width, 'height' => $this->height);
		}
		
		if($this->absolute){
			$this->width = $width;
			$this->height = $height;
		}else{
			if($width > $this->width && $height > $this->height && !$this->fixed){
			}else{
				$width_gen =($this->width * $height) / $this->height;
				$height_get =($this->height * $width) / $this->width;
				if($width && $height &&(($height < $this->height && $width < $this->width) || $this->fixed)){
					if($width_gen > $width){
						$this->height = $height_get;
						$this->width = $width;
					
					}else if($height_get > $height){
						$this->width = $width_gen;
						$this->height = $height;
					
					}else if($width < $height){
						$this->height = $height_get;
						$this->width = $width;
					
					}else{
						$this->width = $width_gen;
						$this->height = $height;
					}
				
				}else if($width &&($this->width > $width || $this->fixed)){
					$this->height = $height_get;
					$this->width = $width;
				
				}else if($height &&($this->height > $height || $this->fixed)){
					$this->width = $width_gen;
					$this->height = $height;
				
				}
			}
		}
		$this->width =(int) $this->width;
		$this->height =(int) $this->height;
		
		return array('width' => $this->width, 'height' => $this->height);
	}
	/**
	 * Generate HTML code
	 * 
	 * Sintax string attribute
	 * 'name att 1=val att 1|name att 2=val att 2|...|name att N=val att N'
	 * settings flash attribute:
	 * align
	 * bgcolor
	 * variables
	 * allowScriptAccess
	 * version
	 * loop
	 * autoplay
	 *
	 * settings QickTime attributes:
	 * controller
	 * loop
	 * play
	 * bgcolor
	 *
	 * @param integer [width]
	 * @param integer [height]
	 * @param array|string [attribute]
	 * @return string
	 */
	function view($width = 0, $height = 0, $attribute = array()){
		if(!$this->src)
			return '';
		
		$return = '';
		$size = $this->size($width, $height);
		
		if(!isset($attribute['absolute'])){
			$attribute['width'] = $width = $this->width;
			$attribute['height'] = $height = $this->height;
		}else{
			$attribute['width'] = $width;
			$attribute['height'] = $height;
			
			unset($attribute['absolute']);
		}
		
		$src = $this->src();
		if(isset($attribute['uid']) && $attribute['uid']){
			unset($attribute['uid']);
			$uid = uniqid('media=');
			
			if(!preg_match("/^.+\?.*$/", $src)){
				$src .= "?";
			}else{
				$src .= "&";
			}
			$src .= $uid;
		}
		$attribute['src'] = $src;
		$attribute['name'] = $this->img;
		
		if(isset(self::$controlers[$this->ext])){
			return self::$controlers[$this->ext]->generate($attribute);
		}else{
			return self::$controlers['']->generate($attribute, $this->info);
		}
	}
	/**
	 * Help property for getUid()
	 * @access private
	 * @staticvar
	 * @var integer
	 */
	private static $uid = 0;
	/**
	 * Generate unique id
	 * @static
	 * @access private
	 * @return integer
	 */
	static private function getUid(){
		return(self::$uid ++);
	}
}
BASIC_MEDIA::addPlugin('',  new MediaPlugin());

/**
 * Work with images. Support only jpeg, png and gif image types.
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.0
 * @package basic.media
 */
class BasicMediaImage extends BASIC_MEDIA{
	/**
	 * access public
	 * @var boolean
	 */
	public $printMode = false;
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param string $image
	 * @param string $package
	 * @return void
	 */
	function __construct($image, $package = ''){
		parent::__construct($image, array('folder' => $package));
		
		if(!$this->src()){
			throw new Exception(" File '".$this->img."' not exist.");
		}
		switch($this->info['extent']){
			case 'jpg' :
			case 'jpeg' :
			case 'png' :
			case 'gif' :
				break;
			default :
				throw new Exception(" Type image '".$this->info['extent']."' not support.");
		}
	}
	/**
	 * Resize image
	 * 
	 * @access public
	 * @param integer $width
	 * @param integer $height
	 * @param string $type_resize flag values: 
	 * 		  scalare - resize by save resolution
	 * 		  absolute - resize by width and height
	 * 		  sizer - stop check for max width and height
	 * @return void
	 */
	public function resize($width, $height = -1, $type_resize = 'scalare'){
		$fix = $this->fixed;
		$abs = $this->absolute;
		
		if($type_resize == 'sizer'){
			$this->fixed = true;
			$this->absolute = false;
		}
		if($type_resize == 'absolute'){
			$this->fixed = false;
			$this->absolute = true;
		}
		
		if($height == - 1)
			$height = $this->height;
		
		// ifimage is more big from $width and $height miss resize
		if($type_resize != 'sizer' && $type_resize != 'absolute' && $width > $this->width && $height > $this->height)
			return;
		
		if($this->info['extent'] == 'jpeg'){
			/**
			 * @todo too much resources need for some files
			 */
			$in = imagecreatefromjpeg($this->fullPath.$this->img);
		}else if($this->info['extent'] == 'gif'){
			$in = imagecreatefromgif($this->fullPath.$this->img);
		}else if($this->info['extent'] == 'png'){
			$in = imagecreatefrompng($this->fullPath.$this->img);
		}
		imageinterlace($in, true);
		imagealphablending($in, true);
		imagesavealpha($in, true);
		
		//if($type_resize == 'scalare' || $type_resize == 'sizer'){
		$this->size($width, $height);
		//}

		$out = imagecreatetruecolor($this->width, $this->height);
		if($this->info['extent'] == "png" || $this->info['extent'] == "gif"){
			imagecolortransparent($out, imagecolorallocate($out, 0, 0, 0));
		}
		imagecopyresized($out, $in, 0, 0, 0, 0, $this->width, $this->height, imagesx($in), imagesy($in));
		
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			imagejpeg($out, $this->printMode ? null : $this->fullPath.$this->img, 100);
		}
		if($this->info['extent'] == "gif"){
			imagegif($out, $this->printMode ? null : $this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			imagepng($out, $this->printMode ? null : $this->fullPath.$this->img);
		}
		imagedestroy($out);
		imagedestroy($in);
		
		$this->fixed = $fix;
		$this->absolute = $fix;
	}
	/**
	 * Crop image
	 * 
	 * @access public
	 * @param integer $x
	 * @param integer $y
	 * @param float $width
	 * @param float $height
	 * @param array $bg - integer collection(array(red, green, blue))
	 * @return void
	 */
	public function crop($x, $y, $width, $height, $bg = array()){
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			$in = imagecreatefromjpeg($this->fullPath.$this->img);
		}
		if($this->info['extent'] == "gif"){
			$in = imagecreatefromgif($this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			$in = imagecreatefrompng($this->fullPath.$this->img);
		}
		imageinterlace($in, true);
		imagealphablending($in, true);
		imagesavealpha($in, true);
		
		$out = imagecreatetruecolor($width, $height);
		if($bg){
			$rbg = imagecolorallocate($out, $bg[0], $bg[1], $bg[2]);
			imagefill($out, 0, 0, $rbg);
		}
		
		if($this->info['extent'] == "png" || $this->info['extent'] == "gif"){
			imagecolortransparent($out, imagecolorallocate($out, 0, 0, 0));
		}
		imagecopyresized($out, $in, 0, 0, $x, $y, $width, $height, $width, $height);
		
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			imagejpeg($out, $this->printMode ? null : $this->fullPath.$this->img, 100);
		}
		if($this->info['extent'] == "gif"){
			imagegif($out, $this->printMode ? null : $this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			imagepng($out, $this->printMode ? null : $this->fullPath.$this->img);
		}
		imagedestroy($in);
		imagedestroy($out);
		
		$this->width = $width;
		$this->height = $height;
	}
	/**
	 * Rotate image
	 * 
	 * @access public
	 * @param integer $gradus
	 * @return void
	 */
	public function rotate($gradus){
		if($gradus == 360)
			return;
		
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			$in = imagecreatefromjpeg($this->fullPath.$this->img);
		}
		if($this->info['extent'] == "gif"){
			$in = imagecreatefromgif($this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			$in = imagecreatefrompng($this->fullPath.$this->img);
		}
		
		$out = imagerotate($in, $gradus * - 1, - 1);
		imagealphablending($out, true);
		imagesavealpha($out, true);
		
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			imagejpeg($out, $this->printMode ? null : $this->fullPath.$this->img, 100);
		}
		if($this->info['extent'] == "gif"){
			imagegif($out, $this->printMode ? null : $this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			imagepng($out, $this->printMode ? null : $this->fullPath.$this->img);
		}
		
		$this->width = imagesx($out);
		;
		$this->height = imagesy($out);
		
		@imagedestroy($in);
		@imagedestroy($out);
	}
	/**
	 * Add filter image (grayscale, sepia, ...)
	 * 
	 * @access public
	 * @param string $filter
	 * @return void
	 */
	public function filter($filter){
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			$in = imagecreatefromjpeg($this->fullPath.$this->img);
		}
		if($this->info['extent'] == "gif"){
			$in = imagecreatefromgif($this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			$in = imagecreatefrompng($this->fullPath.$this->img);
		}
		
		switch($filter){
			case 'grayscale' :
				imagefilter($in, IMG_FILTER_GRAYSCALE);
				break;
			case 'sepia' :
				imagefilter($in, IMG_FILTER_GRAYSCALE);
				imagefilter($in, IMG_FILTER_COLORIZE, 100, 50, 0);
				break;
			case 'pencil' :
				imagefilter($in, IMG_FILTER_EDGEDETECT);
				break;
			case 'emboss' :
				imagefilter($in, IMG_FILTER_EMBOSS);
				break;
			case 'blur' :
				imagefilter($in, IMG_FILTER_GAUSSIAN_BLUR);
				break;
			case 'smooth' :
				imagefilter($in, IMG_FILTER_SMOOTH, 5);
				break;
			case 'invert' :
				imagefilter($in, IMG_FILTER_NEGATE);
				break;
			case 'brighten' :
				imagefilter($in, IMG_FILTER_BRIGHTNESS, 1);
				break;
		}
		
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			imagejpeg($in, $this->printMode ? null : $this->fullPath.$this->img, 100);
		}
		if($this->info['extent'] == "gif"){
			imagegif(in, $this->printMode ? null : $this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			imagepng(in, $this->printMode ? null : $this->fullPath.$this->img);
		}
		imagedestroy(in);
	}
	/**
	 * 
	 * @version 0.2
	 * @access public
	 * @param BasicMediaImage $img
	 * @param integer $x
	 * @param integer $y
	 * @return void
	 */
	public function setLayer($img, $x, $y){
		if($img->info['extent'] == "jpg" || $img->info['extent'] == "jpeg"){
			$in = imagecreatefromjpeg($img->fullPath.$img->img);
		}
		if($img->info['extent'] == "gif"){
			$in = imagecreatefromgif($img->fullPath.$img->img);
		}
		if($img->info['extent'] == "png"){
			$in = imagecreatefrompng($img->fullPath.$img->img);
		}
		imagealphablending($in, true);
		imagesavealpha($in, true);
		
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			$out = imagecreatefromjpeg($this->fullPath.$this->img);
		}
		if($this->info['extent'] == "gif"){
			$out = imagecreatefromgif($this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			$out = imagecreatefrompng($this->fullPath.$this->img);
		}
		imagealphablending($out, true);
		imagesavealpha($out, true);
		
		$width = imagesx($in);
		$height = imagesy($in);
		
		imagecopyresized($out, $in, $x, $y, 0, 0, $width, $height, $width, $height);
		
		if($this->info['extent'] == "jpg" || $this->info['extent'] == "jpeg"){
			imagejpeg($out, $this->fullPath.$this->img, 100);
		}
		if($this->info['extent'] == "gif"){
			imagegif($out, $this->fullPath.$this->img);
		}
		if($this->info['extent'] == "png"){
			imagepng($out, $this->fullPath.$this->img);
		}
		
		imagedestroy($in);
		imagedestroy($out);
	}
	/**
	 * Copy image
	 * 
	 * @access public
	 * @param string [$copy_name]
	 * @param string [$folder]
	 * @return BasicMediaImage
	 */
	public function copy($copy_name = '', $folder = ''){
		if(!$copy_name)
			$copy_name = 'Copy_'.$this->img;
		if(!$folder)
			$folder = $this->folder;
		
		$full_path = BASIC::init()->ini_get('root_path').$folder."/";
		
		if(!is_dir($full_path)){
			throw new Exception("Folder '".$full_path."' miss!");
			return;
		}
		@unlink($full_path.$copy_name);
		if(!copy($this->fullPath.$this->img, $full_path.$copy_name)){
			throw new Exception("Can't copy image in folder '".$this->fullPath."'!");
			return;
		}
		
		return new BasicMediaImage($copy_name, $folder);
	}
	/**
	 * Create image
	 * 
	 * @static
	 * @access public
	 * @param string $name
	 * @param integer $width
	 * @param integer $height
	 * @param string $folder
	 * @param string $bg
	 * @return BasicMediaImage
	 */
	static public function make($name, $width, $height, $folder, $bg){
		$fullPath = BASIC::init()->ini_get("root_path").$folder."/";
		
		$out = imagecreatetruecolor($width, $height);
		if($bg){
			$rbg = imagecolorallocate($out, $bg[0], $bg[1], $bg[2]);
			imagefill($out, 0, 0, $rbg);
		}
		$ext = 'png';
		foreach(explode(".", $name) as $v)
			$ext = $v;
		
		if($ext == "jpg" || $ext == "jpeg"){
			imagejpeg($out, $fullPath.$name, 100);
		}else if($ext == "gif"){
			imagegif($out, $fullPath.$name);
		}else{
			imagepng($out, $fullPath.$name);
		}
		
		return new BasicMediaImage($name, $folder);
	}
}