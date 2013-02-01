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
* @package basic.template
* @version 7.0.4
*/


/**
 * This interface will be used from tamplate engine for access to drivers.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
interface BasicTemplateDriverInterface {
	function set($variable_name,$variable_value = array());
	function un_set($variable_name);
	function createTemplate($template_name, $source, $usecache = true);
	function parse($template_name, $scope = '', $vars = array());
	
	function getTemplateSource($template_name);
	function getCashTime($template_name);
	function getTemplateTime($template_name);
	
	function clearCash($name = '');
}
/**
 * Service which implements the formatting of the displayed information.
 * The service uses BasicTemplateDriverInterface to access the methods
 * 
 * @author Evgeny Baldzisky
 * @version 0.1
 * @since 10.08.2009
 * @package basic.template
 */
class BASIC_TEMPLATE2 implements BasicTemplateDriverInterface{
	/**
	 * Default driver object
	 *
	 * @var TemplateDriverBasic
	 */
	public $driver = null;
	/**
	 * 
	 * 
	 * Access to the service panel and setup the driver.
	 * 	<code>
	 *		BASIC_TEMPLATE2::init(array(
	 *			'template_path' 	=> 'tpl/',
	 *			'prefix_ctemplate' 	=> 'cp_'
	 *		));
	 * 	</code>
	 * 
	 * @param array [$settings]
	 * @return BASIC_TEMPLATE2
	 * @static
	 */
	static function init($settings = array()){
		if(!isset($GLOBALS['BASIC_TPL2']) || !$GLOBALS['BASIC_TPL2']){
			$GLOBALS['BASIC_TPL2'] = new BASIC_TEMPLATE2();
		}
		foreach ($settings as $k => $v){
			if($k == 'driver'){
				$GLOBALS['BASIC_TPL2']->driver = $v;
			}else{
				$GLOBALS['BASIC_TPL2']->driver->$k = $v;
			}
		}
		return $GLOBALS['BASIC_TPL2'];
	}
	/**
	 * Constructor
	 * 
	 * @return BASIC_TEMPLATE2
	 */
	function __construct(){
		$this->driver = new TemplateDriverBasic();
	}
	/**
	 * Set the variables
	 *	<code>
	 * 		BASIC_TEMPLATE2->set('variable_name','variable_value');
	 * 			// OR
	 * 		BASIC_TEMPLATE2->set(array(
	 * 			'variable_name_1' => 'variable_value_1',
	 * 			'variable_name_2' => 'variable_value_2',
	 * 			'variable_name_3' => 'variable_value_3'
 	 * 		));
	 * 	</code>
	 *
	 * @param string $variable_name
	 * @param mix [$variable_value]
	 * @access public
	 * @return BASIC_TEMPLATE2
	 */
	public function set($variable_name, $variable_value = '', $scope = ''){
		$this->driver->set($variable_name, $variable_value, $scope);
		return $this;
	}
	/**
	 * Unset variables
	 * @see BasicTemplateDriverInterface::un_set()
	 * @param string $variable_name
	 * @param string $scope
	 * @access public
	 */
	public function un_set($variable_name, $scope = ''){
		$this->driver->un_set($variable_name, $scope);
	}
	/**
	 * 
	 * Parse method
	 * @param string $template_name
	 * @param string $scope
	 * @param array $vars
	 * @access public
	 * @see BasicTemplateDriverInterface::parse()
	 * @return string
	 */
	public function parse($template_name, $scope = '', $vars = array()){
		return $this->driver->parse($template_name, $scope, $vars);
	}
	/**
	 * 
	 * Create the template
	 * @see BasicTemplateDriverInterface::createTemplate()
	 * @param string $name
	 * @param string $source
	 * @param boolean $usecache
	 * @access public
	 */
	public function createTemplate($name, $source, $usecache = true){
		return $this->driver->createTemplate($name, $source, $usecache);
	}
	/**
	 * Get the generated template source
	 * @access public
	 * @param string $template_name
	 * @see BasicTemplateDriverInterface::getTemplateSource()
	 */
	public function getTemplateSource($template_name){
		return $this->driver->getTemplateSource($template_name);
	}
	/**
	 * Clear db's(if the driver->method is 'db') template's list or the name element from this list.
	 * 
	 * @see BasicTemplateDriverInterface::clearCash()
	 * @param string $name
	 * @access public
	 */
	public function clearCash($name = ''){
		return $this->driver->clearCash($name);
	}
	/**
	 * Get cache time
	 * @see BasicTemplateDriverInterface::getCashTime()
	 * @param string $template_name
	 */
	function getCashTime($template_name){
		return $this->driver->getCashTime($template_name);
	}
	/**
	 * Get template time
	 * @see BasicTemplateDriverInterface::getTemplateTime()
	 * @param string $template_name
	 */
	function getTemplateTime($template_name){
		return $this->driver->getTemplateTime($template_name);
	}	
}
/**
 * 
 * Basic template plugin interface
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
interface  BasicTemplatePluginInterface {
	/**
	 * convert source clauses
	 *
	 * @param string $source
	 * @return string
	 */
	function parse($source);	
}
/**
 * Basic template plugin for IF statements
 * 
 * <code>
 * 		<!-- if(${myvar} == 1) -->
 * 			<div>Show it only if you are logged</div>
 * 		<!-- end -->
 * 
 * 		*************************************************
 * 
 * 		<!-- if(${myvar} != 'myvalue') -->
 * 			<div>Show it if you are logged</div>
 * 		<!-- else -->
 * 			<div>Show it if you are not logged</div>
 * 		<!-- end -->
 * 
 * 		*************************************************
 * 
 * 		<!-- if(${myvar} != 'myvalue' || ${myvar2} > 5) -->
 * 
 * 		<!-- ifelse(${myvar2} > 7) -->
 * 
 * 		<!-- else -->
 * 
 * 		<!-- end -->
 * </code>
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
class BasicTemplatePluginIf implements BasicTemplatePluginInterface{
	/**
	 * 
	 * 
	 * Parse the template if/else tags
	 * @access public
	 * @param string $source
	 * @see BasicTemplatePluginInterface::parse()
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- (elseif)\(([^\)]+)\) -->/',array($this,'_parse_ifelse'),
			preg_replace_callback('/<!-- (if)\(([^\)]+)\) -->/',array($this,'_parse_if'),
				str_replace('<!-- else -->','<?php }else{?>',$source)
			)
		);
	}
	/**
	 * 
	 * Parse IF statement
	 * @param array $match
	 * @access private
	 */
	private function _parse_if($match){
		return "<?php ".$match[1]."(".preg_replace_callback('/\$\{([^\}]+)\}/','TemplateDriverBasic::translate_collback', $match[2])."){ ?>";
	}
	/**
	 * 
	 * Parse IF ELSE statement
	 * @param array $match
	 * @access private
	 */
	private function _parse_ifelse($match){
		return "<?php }".$match[1]."(".preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[2])."){ ?>";
	}
}
/**
 * Basic template plugin for FOR tags
 * 
 * <code>
 * 		<!-- for($i = 0,${myvar} > $i, $i++) -->
 * 			<div class="bg-${i}">Button ${i}</div>
 * 		<!-- end -->
 * </code>
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
class BasicTemplatePluginFor implements BasicTemplatePluginInterface{
	/**
	 * Parse the source
	 * @see BasicTemplatePluginInterface::parse()
	 * @param string $source
	 * @access public
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- (for)\(([^\)]+)\) -->/',array($this,'_parse'),$source);
	}
	/**
	 * 
	 * Parse the matched tags from parse function and set the FOR 
	 * @access private
	 * @param array $match
	 */
	private function _parse($match){
		$match[2] = preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[2]);

		preg_match('/^([^,]+)[,;]([^,]+)[,;](.+)$/', $match[2], $declarations);
		
		return "<?php for(".$declarations[1]."; ".$declarations[2]."; ".$declarations[3]."){ ?>";
	}
}
/**
 * 
 * Basic template plugin to parse FOREACH tags
 * 
 * 	<code>
 * 		<!-- foreach(${array}[,variable_key],variable_value) -->
 * 			<span class="span-index-${variable_key}">${variable_value}</span>
 * 		<!-- end -->
 * 
 * 	************************************************************		
 * 
 * 		<!-- foreach(${array} as $variable_key => $variable_value) -->
 * 			<span class="span-index-${variable_key}">${variable_value}</span>
 * 		<!-- end --> 
 * 
 * 	</code>
 * 
 * in version 0.3 is included counter:
 * <code>
 * 		<!-- foreach(${arr}[,k],v[,c]) -->
 *			Key: ${k}; Value: ${v}; Counter: ${c}<br/>
 *		<!-- end -->
 *	OR
 * 		<!-- foreach(${arr} as $k => $v,$c) -->
 *			Key: ${k}; Value: ${v}; Counter: ${c}<br/>
 *		<!-- end -->
 * </code>
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.3
 * @package basic.template
 */
class BasicTemplatePluginForeach implements BasicTemplatePluginInterface{
	
	/**
	 * Parse the forach tags method and call the replace tags function 
	 * @access public
	 * @param string $source
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- (foreach)\(([^\)]+)\) -->/',array($this,'_parse'),$source);
	}
	/**
	 * 
	 * The actual replace of the forach tags
	 * @access private
	 * @param array $match
	 * 
	 */
	private function _parse($match){
		$match[2] = preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[2]);
		
		$declarations = preg_split('/[ ]*( as |,|=>)[ ]*/',$match[2]);
		
		$counter = '';
		if(isset($declarations[3])){
			$counter = '$'.preg_replace('/[\$@]/', '', $declarations[3]);
		}
		
		$build = '';
		$build .= '(is_array('.$declarations[0].')?'.$declarations[0].':array()) as ';
		$build .= '$'.preg_replace('/[\$@]/', '', $declarations[1]).' ';
		if(isset($declarations[2])){
			$build .= '=> $'.preg_replace('/[\$@]/', '', $declarations[2]);
		}
		return '<?php '.($counter ? $counter.'=-1;' : $counter).'foreach('.$build.'){'.($counter ? $counter.'++;' : '').' ?>';
	}
}
/**
 * Foreach with incrementer. The incrementer can start only from 0. For more advansed incremented loops
 * see the template plug-in "BasicTemplatePluginIFor" or "BasicTemplatePluginForeach" v 0.3+.
 * 
 * 	<code>
 * 		<!-- iforeach(${array},incrementer_name[,variable_key],variable_value) -->
 * 			<span class="span-index-${variable_key}" tabindex="${incrementer_name}">${variable_value}</span>
 * 		<!-- end -->
 * 	</code>
 *
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
class BasicTemplatePluginIForeach implements BasicTemplatePluginInterface{
	/**
	 * Function to prase/match the tags
	 *  
	 * @access public
	 * @param string $source
	 * 
	 * */
	public function parse($source){
		return preg_replace_callback('/<!-- (iforeach)\(([^\)]+)\) -->/',array($this,'_parse'),$source);
	}
	/**
	 * 
	 * The actual replace of the tags
	 * 
	 * @access private
	 * @param array $match
	 * 
	 */
	private function _parse($match){
		$match[2] = preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[2]);
		
		$declarations = preg_split('/,[ ]*/', $match[2]);
		
		$build = '';
		$build .= '(is_array('.$declarations[0].')?'.$declarations[0].':array()) as ';
		$build .= '$'.preg_replace('/[\$@]/','',$declarations[2]).' ';
		if(isset($declarations[3])){
			$build .= '=> $'.preg_replace('/[\$@]/','',$declarations[3]).' ';
		}
		return '<?php $'.$declarations[1].'=-1;foreach('.$build.'){$'.$declarations[1].'++;?>';
	}
}
/**
 * 
 * Class plugin to parse the image tags
 * 	<code>
 * 		<!-- image(image path varianle[,image tag parameters]) -->
 * 
 * 		SET: $image_data = 'upload/mypictures_folder/mypicture.jpg'
 * 
 * 		<!-- image(${image_data},width=${imagewidth}|height=230|style=border:1px solid ${imagecolor};) -->
 * 	</code>
 *
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
class BasicTemplatePluginImage implements BasicTemplatePluginInterface{
	/**
	 * Parsing the source and match the tags
	 * 
	 * @access public
	 * @param string $source
	 * @see BasicTemplatePluginInterface::parse()
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- (image)\((.+)\) -->/', array($this, '_parse'), $source);
	}
	/**
	 * 
	 * The actual replace of the tags
	 * @param array $match
	 * @access private
	 */
	private function _parse($match){
		$spl = explode(',',$match[2]);
		
		$build = '';
		if(!preg_match('/\$\{[^\}]+\}/', $spl[0])){
			$build = "'".str_replace("'", "", $spl[0])."'";
		}else{
			$build = preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $spl[0]);
		}
		if(isset($spl[1])){
			$spl[1] = ", '".preg_replace('/[\'"]/', "\\'", $spl[1])."'";
			$build .= preg_replace_callback('/\$\{([^\}]+)\}/', array($this, 'translate_collback'), $spl[1]);
		}
		
		return "<?php print BasicTemplatePluginImage::parser(".$build."); ?>";
	}
	/**
	 * 
	 * @todo description
	 * @param array $match
	 * @static
	 * @access public
	 */
	static public function translate_collback($match){
		
		return "'.".TemplateDriverBasic::translate_collback($match).".'";
	}
	/**
	 * 
	 * The tag parser
	 * @static
	 * @access public
	 * @param array/string $namePath
	 * @param string $attributes
	 * @return string
	 */
	static public function parser($namePath,$attributes = ''){
		$attributes = BASIC_GENERATOR::init()->convertStringAtt($attributes);
		if(!is_array($namePath)){
			$namePath = array('',$namePath);
		}
		if(!isset($attributes['folder'])){
			$attributes['folder'] = (isset($namePath[0]) ? $namePath[0] : '');
		}
		return BASIC_GENERATOR::init()->image((isset($namePath[1]) ? $namePath[1] : ''),$attributes);
	}
}
/**
 * Class to parse the template tags in templates 
 * 
 * <code>
 * 		<!-- template(template name[,local variables for this template]) -->
 * 		<!-- template(my_template) -->
 * 
 * 		SET: $mytemplate_vars = array(
 * 			'var1' => 'val 1',
 * 			'var2' => 'val 2',
 * 			'var3' => 'val 3'
 * 		)
 * 		<!-- template(${my_template},${mytemplate_vars}) -->
 * 		
 * 		**********************************************************
 * 
 * 		<!-- template(${my_template},var1=val 1|var2=val 2|var3=val 3) -->
 * </code>
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
class BasicTemplatePluginTemplate implements BasicTemplatePluginInterface{
	/**
	 * 
	 * Parse the given source
	 * 
	 * @access public
	 * @param string $source
	 * @see BasicTemplatePluginInterface::parse()
	 */
	public function parse($source){
		//return preg_replace('/<!-- template\(([^\)]+)\) -->/', "<?php echo BASIC_TEMPLATE2::init()->parse('$1','',\$__VARS__); ? >", $source);
		return preg_replace_callback('/<!-- template\(([^\)]+)\) -->/', array($this, '_parse'), $source);
	}
	/**
	 * 
	 * The actual paring of the matched tags
	 * 
	 * @access private
	 * @param array $match
	 */
	protected function _parse($match){
		$spl = explode(',',$match[1]);
		
		$build = '';
		if(!preg_match('/\$\{[^\}]+\}/', $spl[0])){
			$spl[0] = "'".str_replace("'", "", $spl[0])."'";
		}else{
			$spl[0] = preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $spl[0]);
		}
		
		$rtn = '';
		if(isset($spl[1])){
			if(!preg_match('/\$\{[^\}]+\}/', $spl[1])){
				$tmp = '$_'.uniqid();
				$rtn .= $tmp.'='.$this->arrayToPhpString($spl[1]);
				
				$spl[1] = $tmp;
			}else{
				$spl[1] = preg_replace_callback('/\$\{([^\}]+)\}/', array($this, 'translate_collback'), $spl[1]);
			}
		}else{
			$spl[1] = '';
		}
		
		$rtn .= "<?php \$__local_vars__ = \$__VARS__; ".($spl[1] ? "foreach(is_array(".$spl[1].") ? ".$spl[1]." : array() as \$tk => \$tv) \$__local_vars__[\$tk] = \$tv; ":"")."echo BASIC_TEMPLATE2::init()->parse(".$spl[0].",'',\$__local_vars__); unset(\$__local_vars__); ?>";
		
		return $rtn; 
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
	/**
	 * 
	 * @todo description
	 * @param array $match
	 * @access public
	 * @static
	 */
	static public function translate_collback($match){
		
		return TemplateDriverBasic::translate_collback($match);
	}
}
/**
 * 
 * Class to parse the menu template tags 
 * For each input level the shown tags must be set with array param with name "nodes"
 * 
 * Mapping the "nodes" array
 * 	title 		- Title of the button
 * 	href 		- Link of the button
 * 	selected 	- Mark the button as selected element (current page)
 * 	target 		- <a> tag target attribute
 * 	onclick		- onclick event
 * 	childs		- HTML block generated from the childs
 * 	... 		- other params depends on exists "fields" in "navigationManager" component.
 * 
 * Usage example
 * 	!) Main template
 * 	<code>
 * 		<td valign="top">
 *			<!-- menu(${left_menu},regursion_menu.tpl) -->
 *		</td>
 *	</code>
 *
 *	!) Template used from the recusrion ( in this case generating the list )
 *	<code>
 *		<ul class="menu vertical">
 *			<!-- foreach(${nodes},v) -->
 *				<li><a href="${v['href']}" <!-- if(${v['target']}) -->target="${v['target']}"<!-- elseif(${v['onclick']}) -->onclick="${v['onclick']}"<!-- end --><!-- if(${v['selected']}) --> style="color:#FF0000;"<!-- end -->>${v['title']}</a></li>
 *				${v['childs']}
 *			<!-- end -->
 *		</ul>
 * </code>
 * 
 * @author Evgeni Baldzisky
 * @version 0.5
 * @since 10.12.2009
 * @package basic.template
 */
class BasicTemplatePluginMenu implements BasicTemplatePluginInterface{
	/**
	 * 
	 * Parse the tags
	 * @param string $source
	 * @access public
	 * @return string
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- (menu)\(([^\)]+),([^\)]+)\) -->/',array($this,'_parse'),$source);
	}
	/**
	 * 
	 * Parser called from the public parse method
	 * 
	 * @access private
	 * @param array $match
	 */
	private function _parse($match){
		return "<?php print BasicTemplatePluginMenu::parser(".preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[2]).",'".$match[3]."'); ?>";
	}	
	/**
	 * 
	 * Recursion
	 * 
	 * @todo description
	 * 
	 * @static
	 * @access public
	 * @param int $name
	 * @return string
	 */
	static public function parser($rec, $template_name){
		return self::_parser(is_array($rec) ? $rec : array(), $template_name);
	}
	/**
	 * 
	 * The actual replace of the tags
	 * 
	 * 
	 * @static
	 * @access private
	 * @param array $rec ( - element's collection
	 * 		data array() 	- heshmap
	 * 		childs array() 	- next level element's collection
	 * )
	 * @param string $template_name
	 * @param array $parent_data
	 * @param int $level
	 */
	static protected function _parser($rec, $template_name, $parent_data = array(), $level = 0){
		$tmp = array();
		
		$parent_data['level'] = $level;
		
		foreach($rec as $v){
			$length = count($tmp);
			
			$tmp[$length] = $v['data'];
			$tmp[$length]['childs'] = array();
			
			if(isset($v['childs'])){
				$tmp[$length]['childs'] = self::_parser($v['childs'], $template_name, array('data' => $v['data']), ($level+1));
			}
		}
		if($tmp){
			BASIC_TEMPLATE2::init()->un_set(array('data', 'nodes', 'level'), $template_name);
			
			BASIC_TEMPLATE2::init()->set($parent_data, $template_name);
			BASIC_TEMPLATE2::init()->set('nodes', $tmp, $template_name);
			
			return BASIC_TEMPLATE2::init()->parse($template_name);
		}
		return '';
	}
}
/**
 * Convert tag lingual to basic lingual variable.
 * 
 * <code>
 * 		<!-- lingual(lingual variable name) -->
 * 
 * 		<!-- lingual(username) -->
 * </code>
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
class BasicTemplatePluginLingual implements BasicTemplatePluginInterface{
	/**
	 * 
	 *
	 * Parse function
	 * 
	 * @access public
	 * @param string $source
	 * @return string
	 */
	public function parse($source){
		return preg_replace('/<!-- (lingual)\(([^\)]+)\) -->/',"<?php print BasicTemplatePluginLingual::parser('$2'); ?>",$source);
	}
	/**
	 * 
	 * 
	 * @todo description
	 * 
	 * @static
	 * @access public
	 * @param int $name
	 * @return string
	 */
	static public function parser($name){
		if(class_exists('BASIC_LANGUAGE')){
			return BASIC_LANGUAGE::init()->get($name);
		}
		return '';
	}
}
/**
 * 
 * Template basic driver
 * 
 * @author Evgeni Baldzhiyski
 * @version 2.1
 * @package basic.template
 */
class TemplateDriverBasic implements BasicTemplateDriverInterface{
	/**
	 * Flag that say where is the template's list.
	 * The options are:
	 * 		disk - on file system
	 * 		db - on data base system
	 * 
	 * @access public
	 * @var string
	 */
	public $method = 'disk'; /*'db';*/
	/**
	 * 
	 * Tempate list array
	 * 
	 * @var array
	 * @access private
	 */
	protected $templatez_list_db_cache = array();
	/**
	 * Templates storage
	 *
	 * @var string
	 * @access public
	 */
	public $template_path = '';
	/**
	 * compiled templates storage
	 *
	 * @var string
	 * @access public
	 */
	public $template_cpath = '';
	/**
	 * Prefix string
	 * @var string
	 * @access public
	 */
	public $prefix_ctemplate = '';
	/**
	 * Compilator's leveling. 
	 * 	0 - allow all
	 * 	1 - deny php sections
	 * 
	 * @var int
	 * @access public
	 */
	public $level_strict = 1;
	/**
	 * 
	 * Compress flag
	 * @var boolean
	 * @access public
	 */
	public $compress = false;
	/**
	 * 
	 * Templates array
	 * @var array
	 * @access private
	 */
	private $template = array();
	/**
	 * 
	 * Template variables
	 * @var array
	 * @access private
	 */
	private $variables = array(
		'' => array()
	);
	/**
	 * 
	 * Templates plugins
	 * @var array
	 * @access private
	 */
	private $plugins = array();
	/**
	 * 
	 * Temporary template name
	 * @var string
	 * @access private
	 */
	private $_tmp_tpl_name = '';
	/**
	 * 
	 * Dynamic templates array
	 * @var array
	 * @access private
	 */
	private $dynamic_templates = array();
	/**
	 * Cionstructor
	 * @return TemplateDriverBasic
	 */
	function __construct(){
		$this->plugins = array(
			'BasicTemplatePluginIf' 	  => new BasicTemplatePluginIf(),
			'BasicTemplatePluginFor' 	  => new BasicTemplatePluginFor(),
			'BasicTemplatePluginForeach'  => new BasicTemplatePluginForeach(),
			'BasicTemplatePluginIForeach' => new BasicTemplatePluginIForeach(),
			'BasicTemplatePluginImage' 	  => new BasicTemplatePluginImage(),
			'BasicTemplatePluginMenu' 	  => new BasicTemplatePluginMenu(),
			'BasicTemplatePluginLingual'  => new BasicTemplatePluginLingual(),
			'BasicTemplatePluginTemplate' => new BasicTemplatePluginTemplate()
		);
		$this->template_path = BASIC::init()->ini_get('template_path');
		$this->template_cpath = BASIC::init()->ini_get('temporary_path');
		
		$this->template_cpath .= 'templates/';
		
		if(!is_dir(BASIC::init()->ini_get('root_path').$this->template_cpath)){
			mkdir(BASIC::init()->ini_get('root_path').$this->template_cpath);
		}
	}
	/**
	 * Add plugins.
	 *
	 * @param string $name
	 * @param object $object
	 * @access public
	 */
	public function addPlugin($name, $object){
		$this->plugins[$name.(isset($this->plugins[$name]) ? '_2' : '')] = $object;
	}	
	/**
	 * Remove registered plugins
	 *
	 * @param string $name
	 * @access public
	 */
	public function delPlugins($name){
		if(isset($this->plugins[$name])) unset($this->plugins[$name]);
	}
	/**
	 * clear different caches: used db templates, ...
	 * 
	 * @param string $name
	 */
	function clearCash($name = ''){
		if(!$name){
			$this->templatez_list_db_cache = array();
		}else{
			unset($this->templatez_list_db_cache[$name]);
		}
	}
	/**
	 * 
	 * 
	 * Set variables. 
	 * If there is a scope param, the given variables will be with higher priority than the already registered variables 
	 *
	 * @param mix $variable_name
	 * @param mix [$variable_value]
	 * @param string [$scope]
	 * @access public
	 */
	public function set($variable_name,$variable_value = '',$scope = ''){
	
		if(is_array($variable_name)){
			$scope = (string)$variable_value;
			foreach ($variable_name as $k => $v){
				$this->_set($k,$v,$scope);	
			}
		}else{
			$this->_set($variable_name,$variable_value,$scope);
		}	
	}
	/**
	 * Help method for "set".
	 *
	 * @param string $variable_name
	 * @param mix $variable_value
	 * @param string $scope
	 * @access private
	 */
	protected function _set($variable_name, $variable_value, $scope){
		if(!isset($this->variables[$scope])){
			$this->variables[$scope] = array();
		}
		$this->variables[$scope][$variable_name] = $variable_value;
	}
	/**
	 * Remove a registered variable.
	 *
	 * @param string $variable_name
	 * @param string $scope
	 * @access public
	 */
	public function un_set($variable_name, $scope = ''){
		if(is_array($variable_name)){
			foreach($variable_name as $v){
				$this->_un_set($v, $scope);
			}
		}else{
			$this->_un_set($variable_name, $scope);
		}
	}
	/**
	 * Help method for "un_set".
	 *
	 * @param string $variable_name
	 * @param string $scope
	 * @access private
	 */
	protected function _un_set($variable_name, $scope){
		if(isset($this->variables[$scope][$variable_name])){
			unset($this->variables[$scope][$variable_name]);
		}
	}
	/**
	 * Create the template
	 * @see BasicTemplateDriverInterface::createTemplate()
	 * @access public
	 * @param string $template_name
	 * @param string $source
	 * @param boolean $usecache use cache ot not
	 */
	public function createTemplate($template_name, $source, $usecache = true){
		$this->dynamic_templates[$template_name] = 1;
		
		if(!$usecache || ($usecache && !$this->checker($template_name))){
			$file = fopen(BASIC::init()->ini_get('root_path').$this->template_cpath.'/'.$this->prefix_ctemplate.$template_name.'.php', 'w');
			fwrite($file, $this->copiler($source));
			fclose($file);
		}
	}
	/**
	 * 
	 * Get the template source code by template name
	 * 
	 * @access public
	 * @param string $template_name
	 * @return string $buffer
	 * @see BasicTemplateDriverInterface::getTemplateSource()
	 */
	public function getTemplateSource($template_name){
		$buffer = '';		
		if($this->method == 'db'){
			if($res = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->template_path."` WHERE `name` = '".$template_name."' ", true)){
				$buffer = $res['body'];
			}
		}else{
			if($file = @fopen(BASIC::init()->ini_get('root_path').$this->template_path.'/'.$template_name,'r')){
				while (!@feof($file)) {
					$buffer .= fread($file, 1024);
				}
				@fclose($file);
			}
		}
		if(!$buffer){
			throw new Exception("File ".$template_name."(".BASIC::init()->ini_get('root_path').$this->template_path.'/'.") no exist!"); return '';
		}
		return $buffer;
	}
	/**
	 * 
	 * Parse the template
	 * @access public
	 * @param string $template_name
	 * @param string $scope
	 * @param array $vars
	 * @return string
	 */
	public function parse($template_name, $scope = '', $vars = array()){
		if(!$this->checker($template_name)){
			$buffer = '';
			
			if($this->method == 'db'){
				$buffer = $this->templatez_list_db_cache[$template_name]['body'];
			}else{
				if(!$file = @fopen(BASIC::init()->ini_get('root_path').$this->template_path.'/'.$template_name,'r')){
					throw new Exception("File ".$template_name."(".BASIC::init()->ini_get('root_path').$this->template_path.'/'.") no exist!"); return '';
				}
				while (!@feof($file)) {
					$buffer .= fread($file, 1024);
				}
				@fclose($file);
			}
			$file = fopen(BASIC::init()->ini_get('root_path').$this->template_cpath.'/'.$this->prefix_ctemplate.$template_name.'.php','w');
			fwrite($file, $this->copiler($buffer));
			fclose($file);
			
			unset($buffer);
			unset($file);
		}
		if(!$scope) $scope = $template_name;
		if(!isset($this->variables[$scope])){
			$this->variables[$scope] = array();
		}
	
		foreach ($this->variables[''] as $k => $v){
			$$k = $v;
		}
		foreach ($vars as $k => $v){
			$$k = $v;
		}
		foreach ($this->variables[$scope] as $k => $v){
			$$k = $v;
		}
		$this->_tmp_tpl_name = $template_name;
		
		// system variable for template's extend support
		$__VARS__ = $vars + $this->variables[$scope];		
		
		unset($scope);
		unset($template_name);
		
		$VIRTUAL = BASIC::init()->ini_get('root_virtual');
		$ROOT	 = BASIC::init()->ini_get('root_path');
		
		ob_start();
		
		require(BASIC::init()->ini_get('root_path').$this->template_cpath.'/'.$this->prefix_ctemplate.$this->_tmp_tpl_name.'.php');
		
		return ob_get_clean();
	}
	/**
	 * if exist template's cache return last modified tile or -1
	 * 
	 * @param string $template_name
	 * @return integer
	 */
	function getCashTime($template_name){
		if(file_exists(BASIC::init()->ini_get('root_path').$this->template_cpath.'/'.$this->prefix_ctemplate.$template_name.'.php')){
			return @filemtime(BASIC::init()->ini_get('root_path').$this->template_cpath.'/'.$this->prefix_ctemplate.$template_name.'.php');
		}else{
			return -1;
		}
	}
	/**
	 * if exist temnplate return last modified tile or -1
	 * @param string $template_name
	 * @see BasicTemplateDriverInterface::getTemplateTime()
	 * @return integer
	 */
	function getTemplateTime($template_name){
		if($this->method == 'db'){
			$this->checker($template_name);
			if(isset($this->templatez_list_db_cache[$template_name]['mdate'])) return $this->templatez_list_db_cache[$template_name]['mdate'];
		}else{
			if(file_exists(BASIC::init()->ini_get('root_path').$this->template_path.'/'.$template_name)){
				return @filemtime(BASIC::init()->ini_get('root_path').$this->template_path.'/'.$template_name);
			}
		}
		return -1;
	}
	/**
	 * 
	 * Checker for template existence
	 * 
	 * @access private
	 * @param string $template_name
	 * @return boolean
	 */
	private function checker($template_name){
		if($this->method == 'db'){
			if(!isset($this->templatez_list_db_cache[$template_name])){
				$this->template_path = str_replace("/", "_", $this->template_path);
				
				if($res = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->template_path."` WHERE `name` = '".$template_name."' ", true)){
					$name = $res['name']; unset($res['name']);
					$this->templatez_list_db_cache[$template_name] = $res;
					unset($res);
					
					return false;
				}
				
				$err = BASIC_ERROR::init()->error();
				if($err['code'] == 1146){
					BASIC_SQL::init()->createTable('id', $this->container, "
						  `name` varchar(255) NOT NULL default '',
						  `body` longtext,
						  `mdate` int(15)',
						  UNIQUE KEY `name` (`name`)
					");
					BASIC_ERROR::init()->clean();
					return $this->checker($template_name);
				}else{
					if(!isset($this->dynamic_templates[$template_name])){
						throw new Exception("File ".$template_name."(".BASIC::init()->ini_get('root_path').$this->template_path.'/'.") no exist!");
						return false;
					}
				}
			}
			$mdate = 0;
			if(isset($this->templatez_list_db_cache[$template_name]['mdate'])){
				$mdate = $this->templatez_list_db_cache[$template_name]['mdate'];
			}
			if(@filemtime(BASIC::init()->ini_get('root_path').$this->template_cpath.'/'.$this->prefix_ctemplate.$template_name.'.php') <= $mdate) return false;
		}else{
			$ttime = @filemtime(BASIC::init()->ini_get('root_path').$this->template_path.'/'.$template_name);
			if(!isset($this->dynamic_templates[$template_name]) && $ttime === false){
				throw new Exception("File ".$template_name."(".BASIC::init()->ini_get('root_path').$this->template_path.'/'.") no exist!");
				return false;
			}
			if(@filemtime(BASIC::init()->ini_get('root_path').$this->template_cpath.'/'.$this->prefix_ctemplate.$template_name.'.php') <= $ttime) return false;
		}
		return true;
	}
	/**
	 * Templater compilator. The compile process are:
	 * 		check for xml heared tag and save it 
	 * 		secure and comment php sections
	 * 		convert xml head tag
	 * 		convert plug-ins
	 * 		convert end tags
	 * 		convert variables
	 * 
	 * @access private
	 * @param string $source
	 * @return string
	 */
	private function copiler($source){
		// code xml head
		$source = preg_replace('/<\?xml([^\?]+)\?>/i', '<#?xml$1?#>', $source);
		
		if($this->level_strict == 1){
			$source = preg_replace('/<\?(php)?/i', '<?php /*', $source);
			$source = preg_replace('/\?>/', '*/ ?>', $source);
		}
		// uncode xml head
		$source = str_replace("<#?", "<?php print '<?'; ?>", $source);
		$source = str_replace("?#>", "<?php print \"?>\n\"; ?>", $source);
		
		foreach ($this->plugins as $plugin){
			$source = $plugin->parse($source);
		}
		$source = str_replace('<!-- end -->','<?php }?>',$source);
		$source = preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::php_translate_collback', $source);
		
		if($this->compress){
			$source = preg_replace("/[\t\r\n]+/", "", $source);
			//$source = preg_replace("/[  ]+/", " ", $source);
		}
		return $source;
	}
	/**
	 * Generate php section for variable's output with array support
	 * 
	 * @static
	 * @access public
	 * @param array $match
	 * @return string
	 */
	static public function php_translate_collback($match){
		return "<?php echo @$".self::varArraySupport($match[1]).";?>";
	}
	/**
	 * Generate variable only with array support
	 * 
	 * @param array $match
	 * @static
	 * @access public
	 */
	static public function translate_collback($match){
		return "@$".self::varArraySupport($match[1]);
	}
	/**
	 * Generate valid for php array syntaxis
	 * 
	 * @param string $var
	 * @static
	 * @access public
	 */
	static public function varArraySupport($var){
		$var_array_check = explode(".", $var);
		if(count($var_array_check) > 1){
			$var = '';
			foreach($var_array_check as $v){
				if(!$var){
					$var = $v; continue;
				}
				$var .= "['".$v."']";
			}
		}
		return $var;
	}
}