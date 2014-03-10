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
* @package basic.ajax
* @version 7.0.6  
*/

/**
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.ajax
 */
class BASIC_AJAX_ERROR{
	public $code = "";
	public $message = "";
	
	function __construct($code, $message = ''){
		$this->code = $code;
		$this->message = $message;
	}
}
/**
 * Ajax requests generator and listener. With this basic service you can create session remote list and remote links.
 * 
 * @author Evgeni Baldzisky
 * @version 2.0
 * @since 14.11.2009 
 * @package basic.ajax
 */
class BASIC_AJAX {
	/**
	 * Container of functions and classes for asynchronous invocation.
	 * @var string
	 * @access public
	 */
	public $bin_folder = 'services';
	/**
	 * Name of allocated part of session for registered resources 
	 * @var string
	 * @access public
	 */
	public $register_name = 'ajax_register';
	/**
	 * Name of allocated part of session for removed registered resources, 
	 * but saved for history functionality in frontend. 
	 * @var string
	 * @access public
	 */
	public $register_history_name = 'ajax_history_register';
	/**
	 * The maximum number of saved steps back.
	 * @var int
	 * @access public
	 */
	public $max_history = 30;
	/**
	 * Permitted types of resources for requests of type "service".
	 * @var array
	 * @access public
	 */
	public $service_permited_extensions = array(
//		'php' => true, 
		'html' => true, 
		'hml' => true, 
		'css' => true,
		'js' => true
	);
	/**
	 * 
	 * Is allowed ajax support
	 * @var boolean
	 * @access public
	 */
	public $ajax_support = false;
	/**
	 * @var BASIC_AJAX_ERROR
	 * @access public
	 */
	public $error = null;
	/**
	 * 
	 * @var boolean
	 * @access public
	 * @staticvar
	 */
	static public $USE_SESSION = false;
	/**
	 * Constructor
	 * 
	 * @return BASIC_AJAX
	 * @access public
	 */
	function __construct(){
		
		if(self::$USE_SESSION){
			BASIC::init()->imported('session.mod');
			
			BASIC_SESSION::init()->set($this->register_name, array());
			BASIC_SESSION::init()->set($this->register_history_name, array());
		}
		$this->ajax_support = true;
	}
	
	/**
	 * Get BASIC_AJAX object using Singleton pattern
	 * 
	 * @param array $arr
	 * @return BASIC_AJAX
	 * @access public
	 * @static
	 */
	static public function init($arr = array()){
		if(!isset($GLOBALS['BASIC_AJAX'])){
			$GLOBALS['BASIC_AJAX'] = new BASIC_AJAX();
		}
		foreach ($arr as $k => $v){
			$GLOBALS['BASIC_AJAX']->$k = $v;
		}
		return $GLOBALS['BASIC_AJAX'];
	}
	/**
	 * Check if exists request to resource
	 * 
	 * Check if exists request to resource and if it exists stop execution with output printing of requested result.
	 * When matching "group" and "clean" move query from register_name list to register_history_name.
	 * 
	 * Типове ресурси:
	 * 	ajax  -  Java Script requests (mostly inside library "Svincs.Ajax")
	 * 	url   -  Requests for opening pages in the context of current address (most of the pop-ups or "iframe")
	 * 
	 * @return void
	 * @access public
	 */
	function listenerRemote(){
		if($id = BASIC_URL::init()->request('ajax')){
			if($this->error){
				die($this->errorSender($this->error->code, $this->error->message));
			}
			
			if(self::$USE_SESSION){
				$register = BASIC_SESSION::init()->get($this->register_name);
				$history = BASIC_SESSION::init()->get($this->register_history_name);
				
				$return  = '';
				if(isset($register[$id])){
					$tmp = $register[$id];
					if($tmp['group'] == $tmp['clean']){
						$this->removeRemote($id);
					}
					$return = $this->binSintax($tmp['bin']);
				}else if(isset($history[$id])){
					$return = $this->binSintax($history[$id]['bin']);
				}
				die($return);
			}else{
				$path = BASIC_URL::init()->secureUrlPath($id);
				$count = count($path);
				$script_name = $path[$count-1];
				
				if(@(include_once BASIC::init()->ini_get('root_path').$this->bin_folder.'/'.implode("/", $path).'.php') === false){
					die($this->errorSender('802', "Service '".$id."' not exist"));
				}else{
					if(function_exists($script_name)){
						die($script_name());
					}
				}
				die();
			}
		}
		if($path = BASIC_URL::init()->request('url')){
			define('SERVICE_OPEN',true);
			
			$path = BASIC_URL::init()->secureUrlPath($path);
			$count = count($path);
			$ext = $path[$count-1]; 
			unset($path[$count-1]);
					
			$split = explode(".",$path); $count = count($split);
			if($count > 1 && (!isset($this->service_permited_extensions[$split[$count-1]]) || !$this->service_permited_extensions[$split[$count-1]])){
				die(' Access Forbidden !');
			}
			require_once BASIC::init()->ini_get('root_path').implode("/",$path).".".$ext; 
			die();
		}
	}
	/**
	 * Generate JSON for specific error.
	 * 
	 * @param integer $code
	 * @param string [$message]
	 * @access public
	 */
	public function errorSender($code, $message = ''){
		return("[{errCode:'".$code."',errMessage:'".str_replace("'", "\\'",$message)."'}]");
	}
	/**
	 * Add remote handler.
	 * 
	 * @version 0.1 beta
	 * 
	 * @param string $id 	[string that will be used later in request parameter "ajax"]
	 * @param string $bin	[string with special syntax with which resources are requested
	 * @param string [$group] [indicates belonging to a group.]
	 * @param string [$clean] [when handle request all from this group are moved to register_history_name]
	 * @access public
	 * @return void
	 */
	function addRemote($id, $bin, $group = '', $clean = ''){
		if(self::$USE_SESSION){
			$tmp = BASIC_SESSION::init()->get($this->register_name);
			$tmp[$id] = array(
				'bin' => $bin, 
				'group' => $group, 
				'clean' => $clean
			);
			BASIC_SESSION::init()->set($this->register_name,$tmp);
		}
	}
	/**
	 * Remove remote handler
	 * 
	 * @version 0.1 beta
	 * 
	 * @param string $id
	 * @param boolen [$move_to_history]
	 * @access public
	 * @return void
	 */
	function removeRemote($id, $move_to_history = true){
		if(self::$USE_SESSION){
			$tmp = BASIC_SESSION::init()->get($this->register_name);
			
			if(isset($tmp[$id])){
				if($move_to_history){
					$h_tmp = BASIC_SESSION::init()->get($this->register_history_name);
					$h_tmp[$id] = $tmp[$id];
					if(count($h_tmp) > $this->max_history){
						foreach($h_tmp as $k => $v){
							unset($h_tmp[$k]); break;
						}
					}
					BASIC_SESSION::init()->set($this->register_history_name, $h_tmp);
				}
				unset($tmp[$id]);
			
				BASIC_SESSION::init()->set($this->register_name, $tmp);
			}else{
				throw new Exception('Can not find id "'.$id.'" in register.');
			}
		}
	}
	/**
	 * Create event for asynchronous request supporting exclusion of the object until request is completed.
	 * 
	 * @version 0.1 beta
	 * 
	 * @param string $id
	 * @param string $bin
	 * @param string $group
	 * @param array [$context]
	 * @access public
	 * @return string
	 */
	function ajaxRemote($id, $bin, $group, $context = array(
		'group_cleaner' => '',
		'js_calback' => '',
		'url' => ''
	)){
		if(!isset($context['url']) || $context['url'] == '#'){
			$url = BASIC::init()->ini_get('root_virtual').BASIC::init()->scriptName();
			$data = BASIC_URL::init()->serialize();
		}else{
			$exp = explode("?",$context['url']);
			$url = $exp[0];
			$data = isset($exp[1]) ? $exp[1] : '';
		}
		if(!isset($context['group_cleaner'])){
			$context['group_cleaner'] = null;
		}
		if(!isset($context['js_calback'])){
			$context['js_calback'] = 'Svincs.Ajax.Parser';
		}else{
			if(is_array($context['js_calback'])){
				$context['js_calback'] = '['.implode(",",$context['js_calback']).']';
			}
		}
			$parser = 'Svincs.Ajax.Parser';
			if(isset($$context['js_calback'])){
				$target = $context['js_calback'];
				
				if(is_array($target)){
					$parser = $target[0]; unset($target[0]);
				}else{
					$target = array($target);
				}	
			}else{
				$target = array($id);
			}
		
		$this->addRemote($id, $bin, $group, $context['group_cleaner']);
		BASIC_GENERATOR::init()->head('initialize','script',null,"$.ajaxHistory.initialize();");
		
		return "Svincs.Ajax.Remote($('#".$id."').get(0),'".$url."','".$data."',function(res){".$parser.'(res'.($target ? ',"'.implode('","',$target).'"' : '').');'."})";
	}
	/**
	 * Generate HTML link tag supporting special properties for using ajax module
	 * 
	 * $attribute['ajax'] 	 - request id
	 * $attribute['bin']  	 - invoke resource
	 * $attribute['group']	 - belonging to a group
	 * $attribute['clean']	 - group name for moving it to history. (most often used when the returned HTML will be stored in a container which has links)
	 * $attribute['parser']	 - js function for processing the returned HTML. The default is "Svincs.Ajax.Parser"
	 * 
	 * @param string $text
	 * @param string [$url]
	 * @param array [$attribute]
	 * @access public
	 * @version 0.1 (beta)
	 * @return string
	 */
	function link($text,$url = '#',$attribute = array()){
		$attribute = BASIC_GENERATOR::init()->convertStringAtt($attribute);
		
		if(isset($attribute['ajax'])){
			$attribute['id'] = $attribute['ajax'];
			
			$this->ajaxRemote($attribute['ajax'],$attribute['bin'],$attribute['group'],array(
				'group_cleaner' => (isset($attribute['clean']) ? $attribute['clean'] : ''),
				'url' => $url
			));
		
			$parser = 'Svincs.Ajax.Parser';
			if(isset($attribute['parser'])){
				$target = $attribute['parser'];
				
				if(is_array($target)){
					$parser = $target[0]; unset($target[0]);
				}else{
					$target = array($target);
				}
			}else{
				$target = array($attribute['ajax']);
			}
			
			$inner = "Svincs.Ajax.Register('".$attribute['ajax']."',function(res){".$parser.'(res'.($target ? ',"'.implode('","',$target).'"' : '').');'."});";
			$head = BASIC_GENERATOR::init()->getHead('ajax register');
			if(isset($head['inner'])){
				$inner = $head['inner']."\n".$inner;
			}
			BASIC_GENERATOR::init()->head('ajax register','script',null,$inner);
			
			unset($attribute['ajax']);
			unset($attribute['bin']);
			unset($attribute['group']);
			unset($attribute['clean']);
			unset($attribute['parser']);
		}
		return BASIC_GENERATOR::init()->link($text, $url, $attribute);
	}
	/**
	 * Create ajax box
	 * 
	 * @todo: change description
	 * @param string $bin
	 * @param string/array [$attributes]
	 * @param string [$body]
	 * @access public
	 * @version 1.0.1 (beta)
	 * @return string
	 */
	public function ajaxbox($bin, $attributes = '', $body = ''){
		$attributes = BASIC_GENERATOR::init()->convertStringAtt($attributes);
		
		if(!isset($attributes['id'])) $attributes['id'] = 'ajaxbox_'.self::getId();
		
		$this->addRemote($attributes['id'], $bin, 'ajaxbox');
		
		$jsparams = '';
		
		if(isset($attributes['params'])){
			$params = BASIC_GENERATOR::init()->convertStringAtt($attributes['params'], array(';',';'), array(':',':'));
		
			foreach($params as $k => $v){
				$jsparams .= ($jsparams ? ',' : '').$k.':"'.$v.'"';
			}
			unset($attributes['params']);	
		}
		
		BASIC_GENERATOR::init()->head('ajaxbox', 'script', null, '
			Svincs.Ajaxbox = function (id, serv, params){
					
					var thebox = $("#"+id).get(0);
					if(thebox){
						thebox.params = params;
						thebox.serv = serv;
						thebox.url = "'.BASIC::init()->ini_get('root_virtual').'";
						
						$(thebox).bind("loadData", function (){//debugger;
							if(thebox.not_access_click) return false;
							
							Svincs.Ajax.Remote(this, thebox.url, this.params, function(data){
								Svincs.Ajax.Parser(data, id);
								
								$("#"+id+" a[name=ajaxbox]").each(function (a, b, c){//debugger;
									$(this).click(function(){//debugger;
										var href = this.href;
										var href_split = href.split("?");
										var url = href_split[0];
										
										if(href_split[1]){
											var data = href_split[1].split("&");
											thebox.params = {};
											for(p in data){
												var spl = data[p].split("=");
												thebox.params[spl[0]] = spl[1];
											}												
										}
										thebox.url = url;
										
										$.ajaxHistory.update("#"+this.id);
										$(thebox).trigger("loadData"); return false;
									});
								});
							},{service: this.serv});
						});
						$(thebox).trigger("loadData");
					}
			}
		');
		
		//BASIC_GENERATOR::init()->head('ajaxbox_'.$attributes['id'], 'script', null, 'Svincs.Ajaxbox("'.$attributes['id'].'", {'.$jsparams.'});', null, true);
		
		$tmp = '';
		$tmp .= BASIC_GENERATOR::init()->element('div', $attributes, $body);
		$tmp .= BASIC_GENERATOR::init()->script('Svincs.Ajaxbox("'.$attributes['id'].'", "'.$bin.'", {'.$jsparams.'});');
		
		return $tmp;
	}
	/**
	 * IDs
	 * 
	 * @var int
	 * @staticvar
	 * @access private
	 */
	static protected $ids = 0;
	/**
	 * Generate unique id.
	 * 
	 * @static
	 * @access private
	 * @return integer
	 */
	static protected function getId(){
		return (self::$ids++);
	}	
	
	
	/**
	 * System for invoking resources using parsing string parameter.
	 * 
	 * <code>
	 * ROOLES :-->:
	 * WARNING:Start folder is root/component_path/
	 * # run the bins :
	 *  Technick for load in root project folder on bin elements
	 *  	$bin = "../bin/test.method
	 * 			===>
	 * 				include: root/bin/test.bin.php;
	 * 				run: test->method();
	 *	run if-else expressions :
	 * 		(...?...:...) bin declare are in this sintax : bin(path bin/fun)
	 * 		$bin = 'bin(../bin/bin1[3,2,1])?bin(../bin/bin1.good):"no"';
	 *
	 * $GLOBALS['BASIC']->binSintax($bin);
	 *
	 * examples ::
	 *  	$ARR = array(
	 *			"ARR1" => array(
	 *				"ARR2" => "test is ok"
	 *			)
	 *		);
	 * 		resource_folder ->
	 * 			class links{
	 * 				function withparam($p1,$p2){
	 * 					die($p1,$p2);
	 * 				}
	 * 				function noparam(){
	 * 					die(" ..... ");
	 * 				}
	 * 			}
	 *
	 * 		print $GLOBALS['BASIC']->binSintax('ARR.ARR1.ARR2'); 						:: call array resource
	 * 		print $GLOBALS['BASIC']->binSintax('links.noparam'); 						:: call object resource with no parameters
	 * 		print $GLOBALS['BASIC']->binSintax('links.withparam["test 1"," test 2"]'); 	:: call object resource with parameters
	 * 		print $GLOBALS['BASIC']->binSintax('GLOBALS.BASIC.ini_get["root_path"]');	:: call servise resourse
	 * 		WARNING:
	 * 			no support call object array property
	 * 			wrong : print $GLOBALS['BASIC']->binSintax('GLOBALS.BASIC.ini.root_path');
	 *	</code>
	 *
	 * @param string $bin
	 * @version 0.6 beta
	 * @since 19.09.2007
	 * @return *
	 */
	function binSintax($bin){

		if(!$bin) return '';
//
//		$ret = '';
//		$arg = (func_get_args());
//		unset($arg[0]);

		// execute if-else case. is not recommended(to slow execute)
		if(preg_match('/([^\?]+)\?([^:]+):(.+)/', $bin, $ex)){
			$bin = preg_replace_callback("/".$this->bin_folder."\(([^\)]+)\)/",array($this,'_binSintax'),$bin);
			eval('$ret = '.$bin.";");
			return $ret;
		}

		// split of element "/"
		preg_match('#(.+/)?([^/]+)$#',$bin,$reg);

		// split of element "."
		$e = explode(".",$reg[2]);
		$count = count($e); //

		//$var = null;
		$param = '';
    
		// search for exist paramenters
		preg_match("/([^\[]+)(\[([^\]]+)\])?/",$e[$count-1],$ex);

		// create "nameelement(parametters)" the  part
		$param = $ex[1]."(".(isset($ex[3]) ? $ex[3] : '').")";

		// clean parametters the part on last elemen
		if($count == 1){
			$e[$count-1] = preg_replace("/\[([^\]]+)?\]/","",$e[$count-1]);
		}

		// if bin is the constant (sintax is __[my_Constant]__)
		if(preg_match("/^__[a-zA-Z_]+__$/", $e[0])){
			$var = @constant($e[0]);
		}else{ // check for exist element(object or variable)
			global $$e[0];
			if(isset($$e[0])){ // load only existing element
				if(is_object($$e[0])){ // if element is object
					$var = &$$e[0];
				}else{ // if element is variable
					$var = $$e[0];
				}
			}
		}
		if(!isset($var)){
			if($count == 1){ // function
				if(!function_exists($e[0])){
					BASIC::init()->imported($reg[1].$e[0], $this->bin_folder);
				}
				if(function_exists($e[0])){
					eval('$ret = '.$param.';');  return $ret;
				}else{
					return '';
				}
			}else{ // if dos't exist variable this element is object
				BASIC::init()->imported($reg[1].$e[0].'.bin', $this->bin_folder);
				global $$e[0];  $$e[0] = new $e[0]();  $var = &$$e[0];
			}
		}
		if($count == 1){ // variable
			return $var;
		}else{ // object or array
			unset($e[0]);
			foreach($e as $pk => $pv){
				// if current element is object run it
				if(is_object($var)){
					$e[$count-1] = $param;
					eval('$ret = $var->'.implode('->',$e).';');
					return $ret;
				}
				// load array's element
				$var = $var[$e[$pk]];
				unset($e[$pk]);
			}
			// return array's element
			return $var;
		}
	}
	/**
	 * Help method of binSintax
	 * 
	 * @param string|array $bin
	 * @access private
	 * @return string
	 */
	private function _binSintax($bin){
		if(is_array($bin)){
			$bin = $bin[1];
		}

		$bin = $this->binSintax($bin);
		if(is_string($bin)){
			$bin = '"'.$bin.'"';
		}
		return $bin;
	}
}