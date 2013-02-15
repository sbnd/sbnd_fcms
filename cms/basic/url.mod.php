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
* @package basic.url
* @version 7.0.4
*/


/**
 * 
 * Class for managing the REQUEST variables
 * Autoinstall
 * 
 * @author Evgeni Baldziyski
 * @version 2.4
 * @since 26.08.2011
 * @package basic.url
 */
class BASIC_URL{
    /**
     * Class workspace
     *
     * @var array - collection of arrays
     */
	protected $arrays = array('_POST', '_GET', '_COOKIE');
	/**
	 * 
	 * 
	 * For configuration and global access 
	 * For instance container remains $GLOBALS['BASIC_URL']
	 * 
	 * @param array [$arr]
	 * @return BASIC_URL
	 * @static
	 * @access public
	 */
	static public function init($arr=array()){
		if(!isset($GLOBALS['BASIC_URL'])){
			$GLOBALS['BASIC_URL'] = new BASIC_URL();
		}
		foreach ($arr as $k => $v){
			$GLOBALS['BASIC_URL']->$k = $v;
		}		
		return $GLOBALS['BASIC_URL'];
	}
	/**
	 * Constructor
	 * 
	 * @return BASIC_URL
	 */
	function __construct(){
	    if(BASIC::init()->ini_get('rewrite')){
	        $tmp = BASIC::init()->ini_get('rewrite');
	        $tmp->decoder();	      
	    }
	}
	/**
	 * 
	 * Reload the service
	 * <code>
	 * 	 <example> 
	 * 		BASIC::init()->ini_get('rewrite','BasicRewrite');
	 * 		BASIC_URL::init()->restart();
	 * </code>
	 * @return BASIC_URL
	 */
	function restart(){
	    $this->__construct();
	    return $this;
	}
	/**
	 * 
	 * If there is given variable return the value
	 * 
	 * @param array $array
	 * @param string $name
	 * @param string [$hand_clean]
	 * @param int [$long]
	 * @access private
	 * @return string
	 */
	protected function _checked($array, $name, $hand_clean = '', $long = 0){
	    if(isset($array[$name.'_x'])){
	        $array[$name] = $array[$name.'_x'];
	    }
		if(isset($array[$name])){
			if($hand_clean){
				return $this->_clean($hand_clean, $array[$name], $long);
			}
			return $array[$name];
		}
		return null;
	}
	/**
	 * 
	 * Check in POST::REQUEST	 
	 *
	 * @param string $name
	 * @param string [$hand_clean]
	 * @param string [$type]
	 * @param int [$long]
	 * @access public
	 * @return string
	 */
	public function post($name,$hand_clean = '',$long = 0){
		$tmp = $this->_checked($_POST, $name, $hand_clean, $long);
		if($tmp === null && $hand_clean){
			return $this->_clean($hand_clean, null);
		}
		return $tmp;
	}
	/**
	 * Check in GET::REQUEST
	 *
	 * @param string $name
	 * @param string [$hand_clean]
	 * @param string [$type]
	 * @param int [$long]
	 * @access public
	 * @return string
	 */
	public function get($name, $hand_clean = '', $long = 0){
		$tmp = $this->_checked($_GET, $name, $hand_clean, $long);
		if($tmp === null && $hand_clean){
			return $this->_clean($hand_clean, null);
		}
		return $tmp;
	}
	/**
	 * Check in COOKIE::REQUEST
	 *
	 * @param string $name
	 * @param string [$hand_clean]
	 * @param string [$type]
	 * @param int [$long]
	 * @access public
	 * @return string
	 */
	public function cookie($name, $hand_clean = '', $long = 0){
		$tmp = $this->_checked($_COOKIE,$name,$hand_clean,$long);
		if($tmp === null && $hand_clean){
			return $this->_clean($hand_clean, null);
		}
		return $tmp;
	}
	/**
	 * 
	 * Set cookie
	 * @param string $name
	 * @param mixed $value
	 * @param string $expire
	 * @param string $path
	 * @param string $domain
	 * @param string $secure
	 * @param string $httponly
	 * @access public
	 */
	public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null){
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
		$_COOKIE[$name] = $value;
	}
	/**
	 * 
	 * Check request
	 * 
	 * <code>
	 * 	<example/> 
	 * 		BASIC::init()->ini_set('rewrite','BasicRewite');
	 * 
	 * 		http://localhost/script/var1/1/_var2/2/var13/1
	 * 			--> BASIC_URL::init()->request('var2') == '2' 
	 * 			--> BASIC_URL::init()->request('var8') == ''
	 * 
	 *  		NEW 
	 *  		--> BASIC_URL::init()->request('var%') == array(
	 *  			var1 => 1,
	 *  			var13 => 1
	 *  		)
	 *  		--> BASIC_URL::init()->request('%var') == array(
	 *  			_var2 => 2
	 *  		)
	 * 
	 * 		<form method="post" action="http://localhost/script">
	 * 			<input name="var1" value="1" />
	 * 			<input name="_var2" value="3" />
	 * 			<input name="var4" value="3" />
	 * 			<input type="submit" />
	 * 		</form>
	 * 			--> BASIC_URL::init()->request('_var2') == '3' 
	 * 			--> BASIC_URL::init()->request('var8') == ''
	 * 
	 *  		NEW 
	 *  		--> BASIC_URL::init()->request('var%') == array(
	 *  			var1 => 1,
	 *  			var4 => 3
	 *  		)
	 *  		--> BASIC_URL::init()->request('%var') == array(
	 *  			_var2 => 3
	 *  		)
	 * </code>
	 * 
	 * @param string $name
	 * @param string [$hand_clean]
	 * @param string [$type]
	 * @param int [$long]
	 * @access public
	 * @return string/array
	 */
	public function request($name, $hand_clean='', $long=0){
		if(strpos($name,'%') === false){
			$tmp = null;
			//check post
			foreach($this->arrays as $v){
				global $$v;

				$tmp = $this->_checked($$v, $name, $hand_clean, $long);
				if($tmp !== null) return $tmp;
			}
			if($hand_clean){
				return $this->_clean($hand_clean, null);
			}
			return $tmp;
		}else{
		    $pattern = '';
	    	if($name[0] == '%'){
	    		$pattern .= '.*';
	    	}
	    	$pattern .= str_replace("%",'',$name);
	    	if($name[strlen($name)-1] == '%'){
	    		$pattern .= '.*';
	    	}
	    	return $this->preg_request("/".$pattern."/i", $hand_clean, $long);
		}
	}
	public function getFirstRequest($range = 'get'){
		$arr = $_GET;
		
		if($range == 'post') $arr = $_POST;
		if($range == 'cookie') $arr = $_COOKIE;
		
		foreach($arr as $k => $v){
			return array(
				'key' => $k,
				'value' => $v
			);
		}
		return null;
	}
	/**
	 * 
	 * 
	 * Find elements in request by regex matches
	 * 
	 * <code>
	 * 		BASIC_URL->init()->set('test',1);
	 * 		BASIC_URL->init()->set('tova_e_test',1);
	 * 		BASIC_URL->init()->set('te__tova_st',1);
	 * 		BASIC_URL->init()->set('test_e_tova',1);
	 * 
	 * 		$arr = BASIC_URL->init()->preg_request("/[_]+(tova)/",array($cladd_cleaner,'method_name_cleaner'));
	 * 
	 * 		<result>
	 * 			array(
	 * 				'te__tova_st' => 1,
	 * 				'test_e_tova' => 1
	 * 			)
	 * 		</rewult>
	 * </code>
	 * 
	 * @param string $pattern
	 * @param array/string $hand_clean
	 * @param int $long
	 * @return array
	 */
	function preg_request($pattern, $hand_clean='', $long=0){
		$tmp = array();
		//check post
		foreach(array_reverse($this->arrays) as $v){
			global $$v;
			
		    foreach($$v as $ke => $el){
		    	$_tmp = '';
				if(preg_match($pattern,$ke)){				
					if($hand_clean){
						$_tmp = $this->_clean($hand_clean,$el,$long);
					}
					$_tmp = $el;
				}
				if($_tmp != 'undefined' && $_tmp != ''){
					$tmp[$ke] = $_tmp;
				}
		    }
		}
		return $tmp;
	}
	
	/**
	 * Check for existence of given variable by name
	 *
	 * @param string $name
	 * @param string $target - valid values: get, post, cookie. if not exist will find in all.
	 * @return boolean
	 * @access public
	 */
	public function test($name, $target = ''){
		if($target){
			$arr = $_GET;
			if($target == 'post'){
				$arr = $_POST;
			}else if($target == 'cookie'){
				$arr = $_COOKIE;
			}
			return isset($arr[$name]);
		}else{
			foreach($this->arrays as $v){
				global $$v;
					$arr = (array)$$v;
				if(isset($arr[$name])) return true;
			}
		}
		return false;
	}
	/**
	 * Clearing elements of specific hashmap.
	 * 
	 * @access public
	 * @param array $array
	 * @param string $name
	 * @param string $hand_clean
	 * @param string [$type]
	 * @param int [$long]
	 * @return data
	 */
	public function other($array, $name, $hand_clean, $long = 0){
		if($name){
			$tmp = $this->_checked($array, $name, $hand_clean, $long);
		}else{
			$tmp = $this->_clean($hand_clean, $array, $long);
		}
		return $tmp;
	}
	/**
	 * Set variable (POST, GET or Cookie)
	 *
	 * @param string $name
	 * @param string $value
	 * @param string [$array] - valid ranges: post, get and cookie
	 * @return void
	 */
	function set($name, $value, $array = 'post'){
		if($array == 'post') $_POST[$name] = $value;
		if($array == 'get') $_GET[$name] = $value;
		if($array == 'cookie'){
			setcookie($name, $value);
		}
	}
	/**
	 * Remove variable by name
	 *
	 * @param string $name
	 * @return void
	 */
	function un($name){
		if(isset($_POST[$name])) unset($_POST[$name]);
		if(isset($_GET[$name])) unset($_GET[$name]);
		if(isset($_COOKIE[$name])){
			setcookie($name, '', time()-(60*60*24*365));
		}
	}
	
	/**
	 * 
	 * Clean the Request by type
	 * @param string $type
	 */
	function cleanRequest($type=null){
		switch ($type){
			case 'post': $_POST = array(); break;
			case 'get': $_GET = array(); break;
			case 'cookie': $_COOKIE = array(); break;
			default:
				$_POST = array();
				$_GET =  array();
				$_COOKIE = array();
		}
	}

	// ############ link manager ########### //

	/**
	 * 
	 * Serialize the data in post or get arrays
	 *	<code>
	 * 		URL : http://localhost/site_root/script_name/var1/1/var2/12/var3/21
	 * 			
	 * 			BASIC_URL::init()->serialize() == 'script_name.php?var1=1&var2=12&var3=21
	 * 			BASIC_URL::init()->serialize(array('var1',var3')) == 'script_name.php?var2=12
	 * 			BASIC_URL::init()->serialize(array('var1'),'post') == 
	 * 															<input type="hidden" name="var2" value="12"/>
	 * 															<input type="hidden" name="var3" value="21"/>
	 * </code>
	 * @param array [$arrMiss] - array of skipped elements
	 * @param string [$metod]  - convert method [post|get]
	 * @return string	
	 */
	function serialize($arrMiss = array(), $metod = 'get', $only = ''){
		if(!is_array($arrMiss)) $arrMiss = array($arrMiss);

		$serialize = '';
		if($only){
			if($only == 'get') $serialize .= $this->_serialize($_GET, $arrMiss, $metod);
			if($only == 'post') $serialize .= $this->_serialize($_POST, $arrMiss, $metod);	
		}else{
			$serialize .= $this->_serialize($_GET, $arrMiss, $metod);
			$serialize .= $this->_serialize($_POST, $arrMiss, $metod);
		}
		return $serialize;
	}
	/**
	 * 
	 * Serialize the data in custom arrays
	 * 
	 * <code>
	 * 		$my_resource = array(
	 * 			'var1' => 12,
	 * 			'var2' => 5,
	 * 			'var5' => 'variable'
	 * 		);
	 * 
	 * 		BASIC_URL::init()->userSerialize($my_resource) == 'script_name.php?var1=12&var2=5&var5=variable'
	 * 		BASIC_URL::init()->userSerialize($my_resource,'post') == 
	 * 															<input type="hidden" name="var1" value="12" />
	 * 															<input type="hidden" name="var2" value="5" />
	 * 															<input type="hidden" name="var1" value="variable" />
	 * </code>
	 * @param array $arrRes
	 * @param string [$metod]
	 * @return string
	 */
	function userSerialize($arrRes,$metod = 'get'){
		return $this->_serialize($arrRes,array(),$metod);
	}
	/**
	 * Redirection
	 * 
	 * <code>
	 * 		BASIC::init()->ini_set('rewrite','BasicRewrite');
	 * 
	 * 		BASIC_URL::init()->redirect('http://localhost/other_script.php','var1=1&var2=2') 
	 * 				== URL : 'http://localhost/other_script.php?var1=1&var2=2';
	 * 		BASIC_URL::init()->redirect('other_script.php','var1=1&var2=2',true) 
	 * 				== URL : 'http://localhost/other_script/var1/1/var2/2';
	 * </code>
	 * @param string $url
	 * @param string [$addvars]
	 * @param array [$context]
	 */
	function redirect($url = '', $addvars = '', $context = array(
		'ignore_rewrite' => false,
		'target' => '_self' /* _self|_blank */
	)){
		$url = preg_replace("/[&]+$/", '', $this->convertURl($url));
		$addvars = preg_replace("/[&]+$/", '', $addvars);
		
		if(!is_array($context)){
			throw new Exception(' Parametar $context is must array type.');
		}
		if(!isset($context['ignore_rewrite'])) $context['ignore_rewrite'] = false;
		if(!isset($context['target'])) $context['target'] = '_self';

		$tmp = '';
		if(BASIC::init()->ini_get('rewrite') && !$context['ignore_rewrite']){
		    $tmp = $GLOBALS['BASIC']->ini_get('rewrite');
            $tmp = $tmp->encoder($url . ($addvars ? "?" . $addvars : ""));
		}else{
			$tmp = $url . ($addvars ? "?" . $addvars : "");
		}
		if($context['target'] != '_self'){
			print '
				<html>
					<body>
						<form method="get" action="'.$tmp.'" target="'.$context['target'].'" id="form"></form>
						<script type="text/javascript">document.getElementById("form").submit();window.history.go(-1);</script>
					</body>
				</html>
			';
		}else{
			header("Location: ".$tmp);
		}
		exit();
	}
	/**
	 * 
	 * When launched rewrite, converts the given url
	 * 
	 * <code>
	 * 		BASIC::init()->ini_set('rewrite','BasicRewrite');
	 * 
	 * 		$my_link_tag = '<a href="'.BASIC_URL::init()->link(script_name.php?var1=1&var2=2).'" title="_blank" >This is my html link</a>
	 * </code> 
	 *
	 * @param string $url
	 * @param string $addvars
	 * @return string
	 */
	function link($url = '', $addvars = ''){
		$url = preg_replace("/[&]+$/", '', $this->convertURl($url));
		$addvars = preg_replace("/[&]+$/", '', $addvars);		
		
		if(BASIC::init()->ini_get('rewrite')){
		    $tmp = BASIC::init()->ini_get('rewrite');
            return $tmp->encoder($url.($addvars ? "?".$addvars : ""));
		}else{
			return $url.($addvars ? "?".$addvars : "");
		}
	}
	/**
	 * 
	 * Secure the url
	 * 
	 * @param string $path
	 * @param mixed $retunString
	 * @access public
	 * @return mixed
	 */
	public function secureUrlPath($path, $retunString = false){
		$path = str_replace('#', '', $path);
		$path = str_replace('\\', '', $path);
		$path = str_replace('/','',$path);
		$path = str_replace('..', '', $path);
		
		return ($retunString ? $path : explode(".", $path));
	}
	/**
	 * 
	 * Convert URL
	 * @param string $url
	 * @access private
	 * @return string $url
	 */
	protected function convertURl($url){
		if($url == '') $url = './';
		
		if($url[0] == '/'){
			$url = BASIC::init()->ini_get('root_virtual').preg_replace("/^\//", "", $url);
		}else if($url[0] == "." && $url[1] == "/" && BASIC::init()->ini_get('rewrite')){
			$url = BASIC::init()->ini_get('root_virtual').BASIC::init()->scriptName().preg_replace("/^\.\//", "", $url);
		}
		return $url;
	}
	/**
	 * 
	 * 
	 * Help method for serialize and userSerialize to find the skipped variables
	 *
	 * @param array $arrMiss
	 * @param string $k
	 * @access private
	 * @return boolean
	 */
	protected function _l_miss($arrMiss,$k){
		$check = false;
		if(is_array($arrMiss)){
			foreach ($arrMiss as $miss){
				if($miss == $k) $check = true;
			}
		}
		return $check;
	}
	/**
	 * Help method for serializing 
	 *
	 * @param array $arrSearch
	 * @param array $arrMiss
	 * @param string $metod
	 * @access private
	 * @return string
	 */
	protected function _serialize($arrSearch, $arrMiss, $metod){
		$serialize = '';
		foreach ($arrSearch as $k => $v){
			if(!$k || $this->_l_miss($arrMiss,$k)) continue;
			
			$v = str_replace('<','&lt;'  ,$v);
			$v = str_replace('>','&gt;'  ,$v);
			$v = str_replace('"','&quot;',$v);
			
			if($metod == 'get'){
				$k = urlencode($k);
				if(is_array($v)){
					foreach ($v as $arr_v){
						//if($arr_v != '') 
							$serialize .= $k . "[]=" . $arr_v . "&";
					}
				}else{
					//if($v != '') 
						$serialize .= $k . "=" . $v . "&";
				}
			}else{
				if(is_array($v)){
					foreach ($v as $arr_v){
						//if($arr_v != '') 
							$serialize .= '<input type="hidden" name="'.$k.'[]" value="'.$arr_v.'" />'."\n";
					}
				}else{
					//if($v != '') 
						$serialize .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />'."\n";
				}
			}
		}
		return $serialize;
	}
	/**
	 * 
	 * Custom clean of data 
	 * <code>
	 * 	!) $hand_clean == array(object,'method')
	 * 	!) $hand_clean == 'function name'
	 *  !) if there is assigned length(long) the data will be cutted to the length
	 * </code>
	 * 
	 * @access private
	 * @param string $hand_clean - the name of the function
	 * @param array|string $post - the data that will be send to the function
	 * @param int $long 		 - The max length 
	 * @return array|string
	 */
	protected function _clean($hand_clean,$post,$long=0){
		//if($post != ''){
			if(is_array($post)){
				$arrtemp = array();
				foreach ($post as $k => $v){
					$arrtemp[$k] = $this->_clean($hand_clean,$v,$long);
				}
				return $arrtemp;
			}
			if($long != 0 && $long < strlen($post)){
				return substr($post,0,$long);
			}
			if($hand_clean){
 				if(is_array($hand_clean)){
					$class = $hand_clean[0]; 
					$method = $hand_clean[1];
					if($class === null){
						return $method($post);
					}
					return $class->$method($post);
				}else{
					return $hand_clean($post);
				}
			}
			return $post;
		//}
	  	//return '';
	}
}

/**
 * 
 * Basic rewrite interface
 * 
 * @author Evgeni Baldziyski
 * @version 1.0.0
 * @package basic.url
 */
interface BasicRewriteInterfase{
	function encoder($url, $save_state = null);
	function decoder();
}
/**
 * 
 * Basic Rewrite class
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.0.0
 */
class BasicRewrite implements BasicRewriteInterfase{
    /**
     * 
     * @todo description
     * @var string
     * @access private
     */
    protected $var_name = '_rewrite_';
    /**
     * 
     * @todo description
     * @var string
     * @access public
     */
    public $special_variable_name = 'url_var';
    /**
     * Encode the given url
     * @see BasicRewriteInterfase::encoder()
     * @param string $url
     * @param mixed $save_state (array or null)
     */
    function encoder($url, $save_state = null){
    	$url = str_replace(BASIC::init()->ini_get('root_virtual'), "", $url);
    	
	    if(is_array($save_state)){
	        $expl = explode("?", $url);
	        $ser = BASIC_URL::init()->serialize($save_state);
	        
	        if(isset($expl[1])){
	           $url =  $expl[0].'?'.$ser.$expl[1];
	        }else{
	           $url = $url.'?'.$ser;
	        }
	    }    
	    $expl = explode("?", $url);
	    $link = str_replace(".php", "", $expl[0]);
	    
	    $vars = ''; 
	    if(isset($expl[1])){
	        foreach (explode("&", $expl[1]) as $k => $v){
	            $vars .= str_replace("=", "/", $v)."/";
	        }
	    }
	    return BASIC::init()->ini_get('root_virtual').str_replace("//", "/", $link.($url && $vars ? '/' : '').($vars ? $vars : '')); 
    }
    /**
     * 
     * Url decoder. Splits the url with "/" separator
     * If the last part is the name of the variable, then will be putted a variable named "$special_variable_name" value
     * 
     * @see BasicRewriteInterfase::decoder()
     */
    function decoder(){
        $url = $GLOBALS['BASIC_URL']->request($this->var_name,'cleanURLInjection');
        
        $tmp = '';$incr = 0;
        foreach (explode("/",$url) as $v){
            if((string)$v=="") continue;
            
            if(!($incr % 2)){
                $tmp = $v;
            }else{
                $_GET[$tmp] = $v;
                $tmp = '';
            }
            $incr++;
        }
		if($tmp){
			$_GET[$this->special_variable_name] = $tmp;
		}
        unset($_GET[$this->var_name]); 
    }
}