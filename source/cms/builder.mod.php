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
* @package cms.builder
* @version 7.0.6
*/


/**
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.builder
 */
class ControlPanelException extends Exception{
	
	/*
	 * @access private 
	 */
	private $component = null;
	/**
	 * 
	 * Class constructor
	 * 
	 * @param $message
	 * @param $code
	 * @param $component
	 */
	function __construct($message, $code, $component){
		parent::__construct($message, $code);
		
		$this->component = $component;
	}
	
	/**
	 * 
	 * Getter for the component
	 */
	function getComponent(){
		return $this->component;	
	}
}
/**
 * Interface for access to system login obects.
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.0.0
 * @since 06.09.2011
 * @package cms
 */
interface BuilderComponentLoginInterface{
	function check();
	function runTotalMode();
}
/**
 * Class discribe register item
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms
 */
class RegisterObject extends BASIC_CLASS {
	/**
	 * Flag for register object's type. The type is:
	 * 	standart - for DysplayComponent objects
	 * 	system 	 - for not pages DysplayComponent or CmsComponent objects. For this type is need to put "#" before the register object's name. 
	 * 
	 * @var string
	 */
	var $type = 'standart';  // [system]
	var $system_name = 'NewComponent';
	var $class = null;
	/**
	 * special property for uniqe localisation on component
	 *
	 * @var string
	 */
	var $prefix = '';
	var $system_prefix = '';
	/**
	 * array with component information
	 *
	 * @var array
	 */
	var $cmp_settings = array();
	/**
	 * Flag for marked element for default open component
	 *
	 * @var boolean
	 */
	var $started = false;
    /**
     * component's folder where if contents
     *
     * @var string
     */
	var $folder = '';
	/**
	 * @var RegisterObject
	 */
	var $parent = null;
	/**
	 * @var array RegisterObject
	 */
	var $child = array();

	// Component's button settings
	
	/**
	 * @var string
	 */
	var $public_name = 'New Component';

	var $link = '';
	var $target = '_self';
	var $tooltip = '';
	var $param = '';
	
	var $system = 0;
	var $admin_support = 0;
	
	/**
	 * 
	 * Constructor of the class
	 * @param $name
	 * @param array $context
	 */
	function __construct($name, $context = array()){
		if(isset($context['class'])){
			$this->class = $context['class'];
		}
		
		if(!$this->class) $this->type = 'package';

		if($name[0] == '#'){
			$this->type = 'system';
			$name = preg_replace('/^[#]/', '', $name);
		}

		$this->system_name = $name;
		$this->link = BASIC::init()->scriptName();

		$this->sets($context);
	}
}

/**
 * Interface for BuilderComponent
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.0.0
 * @since 19.10.2012
 * @package cms
 */
interface BuilderComponentInterface{
	function start();
	function buildRegister();
	function compileTemplate();
	function build($callComponent, $secure = true);
	function META_KEYS($keys = null);
	function META_DESC($desk = null);
	function META_NAMES($title, $clean = false);
	function getRegisterComponent($name);
	function removeRegisterComponent($name, $prefix = '');
	function registerComponents($array, $target = null, $position = -1);
	function registerComponent($name, $context = array(), $target = null, $position = -1);
	function registerSystemComponents();
		
}
/**
 * Basic Component Controler
 * 
 * The system template's map: 
 * 		The class use BASIC_TEMPLATE2 engine and generate special variables
 * 			META -> headers added with basic_generator::head's method (meta tags, styles, ... all standart html headers tags)
 * 			JS -> javascripts
 * 			MESSAGES -> global (in standart case pop in modal) error/info messages
 * 			CONTENT -> component content.
 * 			VIRTUAL -> site base URL path
 *  		THEME -> site display theme
 *  
 * @author Evgeni Baldzhiyski
 * @version 0.5
 * @package cms
 */
class BuilderComponent implements BuilderComponentInterface {
	/**
	 * Set up login mode for administrative UI
	 * The valid values are: 
	 * 		total - see only login form if not logged
	 * 		box - see menu, start page and login box
	 * 		none - not support login system
	 * 
	 * @var string
	 */
	var $loginMode = 'total';
	/**
	 * 
	 * @var BuilderComponentLoginInterface
	 * @access private
	 */
	protected $_login = null;

	var $nameUrlVar = 'cmp';
	
	var $model = array();
	var $child = array();

	var $cPanelPath = '';
	
	var $baseTemplate = 'base.tpl';
	var $jQueryVersion = ''; //1.3.2.min
	var $jQueryUIVersion = ''; //1.7.1.custom.min // id for theme "jQueryUI"
	var $jQueryUITheme = 'base'; //1.7.1.custom.min // id for theme "jQueryUI"
	var $useJSSvincs = false;

	var $startedCmp = 'dashboard';
	/**
	 * Flag to locate the components register. The values are:
	 * 		db - get register from database
	 * 		disk - get register from file system
	 * 
	 * @var string 
	 * @access public
	 */
	public $method = 'db'; //'disk';
	/**
	 * The components register's container. Only valid for db::tables or file names symbols.
	 * 
	 * @var string
	 * @access public
	 */
	public $container = 'modules';
	/**
	 * 
	 * Group container name
	 * @var string
	 * @access public
	 */
	public $group_container = 'module_groups';

	function start(){}
	/**
	 * Modules getter. If method is "disk" need to exist container structure:
	 * 		$this->container/
	 * 			register_front.php
	 * 			register_back.php
	 * 			
	 * 		every register's file need containt  BASIC_REGISTER()'s that retrn modules tree.
	 * 
	 * @param Boolean $for_front
	 * @param Int $parent_self
	 * @access public
	 * @return array
	 */
	public function buildRegister(){
		return $this->_buildRegister();
	}
	/**
	 * 
	 * Modules getter
	 * 
	 * @access private
	 * @param $parent_self
	 * @param $level
	 */
	protected function _buildRegister($parent_self = 0, $level = 0){
		if($this->method == 'disk'){
			$register = array(); BASIC::init()->imported('register.php', $this->container);
			
			return $register;
		}else{
			if(!$level){
				$cache = BASIC_CACHE::init()->open('register');
			}
			if($level || (!$level && !$reg = $cache->cacheArray('all_'.BASIC_LANGUAGE::init()->current()))){
				$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->container."` WHERE 1=1 AND `_parent_self` = ".(int)$parent_self." ");

				BASIC_ERROR::init()->reset();
				$err = BASIC_ERROR::init()->error();
				if($err['code'] == 1146){				
					BASIC_SQL::init()->createTable('id', $this->container, "
						`name` varchar(255) NOT NULL DEFAULT '',
						`class` varchar(255) NOT NULL DEFAULT '',
						`folder` varchar(255) NOT NULL DEFAULT '',
						`public_name` varchar(255) NOT NULL DEFAULT '',
						`cmp_settings` text NOT NULL DEFAULT '',
						`admin_support` int(1) NOT NULL DEFAULT '0',
						`admin_group` int(11) NOT NULL DEFAULT '0',
						`_parent_self` int(11) NOT NULL DEFAULT '0',
						`order_id` int(11) NOT NULL DEFAULT '0',
						 KEY `admin_group` (`admin_group`),
						 KEY `_parent_self` (`_parent_self`),
						 KEY `order_id` (`order_id`)
					");
					if(BASIC_SQL::init()->createTable('id', $this->group_container, "
						`name` varchar(255) NOT NULL DEFAULT '',
						`_parent_self` int(11) NOT NULL DEFAULT '0',
						`order_id` int(11) NOT NULL DEFAULT '0',
						 KEY `_parent_self` (`_parent_self`),
						 KEY `order_id` (`order_id`)
					")){
						BASIC_ERROR::init()->clean();
						return array();
					}
				}else if($err['code'] == 1054){
					preg_match("/column( name)? '([^']+)'/", $err['message'], $match);
					if(isset($match[2])){
						$spl = explode(".", $match[2]);
						$column_name = $spl[count($spl) - 1];
						
						if(BASIC_SQL::init()->createColumn($this->container, $this->_selectBuilder($column_name))){	
							BASIC_ERROR::init()->clean();
							return $this->_buildRegister($parent_self, $level);
						}
					}
					return null;
				}
				
				$reg = array();
				while ($rdr->read()){
					$prefix = $rdr->item('name');
					/**
					 * Convert commponent settings from URL to array format
					 */
					if($rdr->item('cmp_settings')){
						$settings = unserialize($rdr->item('cmp_settings'));
						
						$rdr->setItem('cmp_settings', $settings);
						
						if(isset($settings['prefix'])){
							$prefix = $settings['prefix'];
						}
					}else{
						$rdr->setItem('cmp_settings', array());
					}
					$reg[$rdr->item('name')] = array(
						'name' => $rdr->item('name'),
						'context' => array(
							'folder' => $rdr->item('folder'),
							'class' => $rdr->item('class'),
							'public_name' => $rdr->item('public_name') ? $rdr->item('public_name') : $rdr->item('public_name_'.BASIC_LANGUAGE::init()->current()),
							'cmp_settings' => $rdr->item('cmp_settings'),
							'system' => $rdr->item('admin_group'),
							'admin_support' => $rdr->item('admin_support'),
							'prefix' => $prefix
						),
						'child' => $this->_buildRegister($rdr->item('id'), $level + 1)
					);
				}
				if(!$level){
					$cache->cacheArray('all_'.BASIC_LANGUAGE::init()->current(), $reg);
				}
			}
			return $reg;
		}
	}
	/**
	 * @param string $column
	 * @return string
	 */	
	protected function _selectBuilder($column){
		$data = "`".$column."` ";

		switch($column){
			case 'name':
				$data .= "varchar(255) NOT NULL DEFAULT '' ";
				break;
			case 'class':
				$data .= "varchar(255) NOT NULL DEFAULT '' ";
				break;
			case 'folder':
				$data .= "varchar(255) NOT NULL DEFAULT '' ";
				break;
			case 'public_name':
				$data .= "varchar(255) NOT NULL DEFAULT '' ";
				break;
			case 'cmp_settings':
				$data .= "text NOT NULL DEFAULT '' ";
				break;
			case 'admin_support':
				$data .= "int(1) NOT NULL DEFAULT '0' ";
				break;
			case '_parent_self':
				$data .= "int(11) NOT NULL DEFAULT '0' ";
				break;
			case 'order_id':
				$data .= "int(11) NOT NULL DEFAULT '0' ";
				break;
		}
		return $data;
	}	
	/**
	 * @param string $name
	 * @param array [$context]
	 * @param RegisterObject [$target] 
	 * @param int [$position]
	 * @return RegisterObject
	 */
	function registerComponent($name, $context = array(), $target = null, $position = -1){
		if(!isset($context['prefix'])){
			$context['prefix'] = ltrim($name, '#');
		}
		
		$obj = new RegisterObject($name, $context);
		
		if($target == null){
			$target = $this;
		}else{
			$obj->parent = $target;
		}

		if($name == 'profiles'){
			$test = 0;
		}
		
		if(isset($this->model[$name])){
			$obj->child = $this->model[$name]->child;
			
			foreach($target->child as $k => $v){
				if($v->system_name == $name){
					$target->child[$k] = $obj; break;
				}
			}
		}else{
			if($position >= 0){
				$tmp = $target->child;
				
				$target->child[] = array();
				
				$i = 0; $match = false; foreach($tmp as $v){
					if($i == $position){
						$match = true;
						$target->child[$obj->system_name] = $obj;
					}
					$target->child[$v->system_name] = $v;
				}
				if(!$match){
					$target->child[$obj->system_name] = $obj;
				}
			}else{
				$target->child[$obj->system_name] = $obj;
			}
		    
			if($obj->started){
	        	$this->startedCmp = ($obj->prefix ? $obj->prefix : '').$obj->system_name;
			}
	        if($obj->parent){
	        	$obj->type = $obj->parent->type;
	        }			
		}
		 
		$this->model[$obj->system_name] = $obj;
		
		if(!$this->startedCmp){
			foreach ($this->model as $k => $v){
				if($v->type != 'system'){
					$this->startedCmp = $k; break;
				}
			}
		}
		return $obj;
	}
	/**
	 * Array structure :
	 * 	array(
	 * 		name => name component.it is used for idetity component
	 *		context => colection component variables
	 * 		child => colection of sub components
	 *  )
	 *
	 * @param array $array
	 * @param RegisterObject [$target]
	 * @param numeric [$position]
	 */
	function registerComponents($array, $target = null, $position = -1){
		foreach ($array as $v){
			$obj = $this->registerComponent($v['name'], isset($v['context']) ? $v['context'] : array(), $target, $position);
			
			if(isset($obj->cmp_settings['prepareCofiguration'])){
				BASIC::init()->imported($obj->class.'.cmp', $obj->folder);
				call_user_func(array($obj->class, 'prepareCofiguration'), false, $obj);
			}
			
			if(isset($v['child']) && is_array($v['child'])){
				$this->registerComponents($v['child'], $obj);
			}
		}
		return $this;
	}
	/**
	 * Remove component from the control panel's register
	 *
	 * @param string $name
	 * @param string $prefix
	 */
	function removeRegisterComponent($name, $prefix = ''){
		if(isset($this->model[$prefix.$name])){
			$cmp = $this->model[$prefix.$name];
			if($cmp->parent){
				unset($this->model[$cmp->parent]->child[$name]);
			}
			unset($this->model[$prefix.$name]);
		}
	}
	/**
	 * Check for and get registrate component.
	 * 
	 * @param string $name
	 * @return RegisterObject
	 */
	function getRegisterComponent($name){
		return isset($this->model[$name]) ? $this->model[$name] : null;
	}
	/**
	 * 
	 * Get path to the cpanel
	 */
	function getCPanelPath(){
		return BASIC::init()->ini_get('root_path');
	}
	/**
	 * 
	 * Regiter Component
	 */
	function registerSystemComponents(){
		$this->registerComponent('#pages', array(
			'public_name' => '', 
			'class' => 'Pages',
			'folder' => 'cms/controlers'
		));
		$this->registerComponent('#menu-positions', array(
			'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_menu_positions'), 
			'class' => 'Positions',
			'folder' => 'cms/controlers'
		));
		
		$register = array();
		
		$register[] = array(
			'name' => 'contents', 
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_pages'),
				'class' => 'Contents',
				'folder' => 'cms/controlers/back',
				'system' => 1
			)
		);
		$register[] = array(
			'name' => 'settings',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_settings'), 
				'class' => 'Settings',
				'folder' => 'cms/controlers/back',
				'system' => 1
			)
		);
		if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG'])){
			$register[] = array(
				'name' => 'languages',
				'context' => array(
					'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_languages'),
					'class' => 'Languages',
					'folder' => 'cms/controlers/back',
					'system' => 1
				),
				'child' => array(
					array(
					    'name' => '#lingual',
					    'context' => array(
							'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_lingual'),
						    'folder' => 'cms/controlers/back',
							'class' => 'Lingual'
				    	)
					)
				)
			);
		}
		$register[] = array(
			'name' => 'templates',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_templates'),
				'class' => 'Templates',
				'folder' => 'cms/controlers/back',
				'system' => 1
			)
		);
		$register[] = array(
			'name' => 'modules',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_modules'),
				'class' => 'Modules',
				'folder' => 'cms/controlers/back',
				'system' => 1
			),
			'child' => array(
				array(
					'name' => 'module-settings',
					'context' => array(
						'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_module_settings'),
						'class' => 'ModuleSettings',
						'folder' => 'cms/controlers/back'
					)
				)
			)
		);
			$register[] = array(
				'name' => '#module-groups',
				'context' => array(
					'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_module_groups'), 
					'class' => 'ModuleGroups',
					'folder' => 'cms/controlers/back'
				)
			);
		$register[] = array(
			'name' => 'profiles',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_profiles'), 
				'class' => 'Profiles',
				'folder' => 'cms/controlers/back',
				'system' => 1
			),
			'child' => array(
				array(
					'name' => '#profiles-types',
					'context' => array(
						'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_profiles_types'), 
						'class' => 'ProfilesTypes',
						'folder' => 'cms/controlers/back'
					),
					'child' => array(
						array(
							'name' => '#permitions',
							'context' => array(
								'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_permitions'), 
								'class' => 'Permitions',
								'folder' => 'cms/controlers/back'
							)
						)						
					)
				)
			)
		);
		$register[] = array(
			'name' => '#search-bar',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_search_bar'), 
				'class' => 'SearchBar',
				'folder' => 'cms/controlers/front',
				'public_component' => 1
			)
		);
		$register[] = array(
			'name' => '#language-bar',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_language_bar'), 
				'class' => 'LanguageBar',
				'folder' => 'cms/controlers/front',
				'public_component' => 1
			)
		);
		$register[] = array(
			'name' => '#login',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_login'),
				'class' => 'Login',
				'folder' => 'cms/controlers/front',
				'public_component' => 1
			)
		);

		$this->registerComponents($register);		
	}
	/**
	 * 
	 * @param unknown_type $name
	 */
	function getDisplayComponentBySystemName($name, $secure = true){
		foreach ($this->model as $k => $v){
			if($v->class == $name){
				return $this->getdisplayComponent($k, $secure);
			}
		}
		return null;
	}
	
	/**
	 * Create the componet's display object
	 *
	 * @return CmsComponent
	 */
	function getdisplayComponent($callComponent, $secure = true){
		$this->getCPanelPath();

		if($callComponent && isset($this->model[$callComponent])){
			$sysObj = $this->model[$callComponent];

			BASIC::init()->imported($sysObj->class.'.cmp', $sysObj->folder);
			
			$cmp = $sysObj->class;
			$cmp = new $cmp();
			
			if($sysObj->prefix) $cmp->prefix = $sysObj->prefix;
			$cmp->model = $sysObj;
			
 			$name = 'CmsComponent'; if($cmp instanceof $name){
 				//@TODO  Shorcuts - deprecated. Will remove in next version
 				$cmp->parent = $sysObj->parent;
 				$cmp->child = $sysObj->child;
				
 				$cmp->setChildActions();
 				$cmp->setParentAction();
			}
			foreach($sysObj->cmp_settings as $k => $v){
				$cmp->$k = $v;
			}
			
			$cmp->secure = $secure;
			$cmp->main();
						
			if($secure){	
				if($perm = BASIC_USERS::init()->getPermission($sysObj->system_name, 'list') || $callComponent == $this->startedCmp){
					$name = 'CmsActionDisplayComponent'; if($cmp instanceof $name){
						foreach($cmp->actions as $k => $v){
							if($cmp->getCmpPermition($k)){
								if(!BASIC_USERS::init()->getPermission($sysObj->system_name, $k) && $k != 'list'){
									unset($cmp->actions[$k]);
								}
							}
						}
					}
				}
				if($callComponent != $this->startedCmp && !$perm){
					return null;
					//throw new ControlPanelException(BASIC_LANGUAGE::init()->get('notaccess_module_position').' "'.$cmp->model->public_name.'".', 808, $cmp->model); return null;
				}
			}
			return $cmp;
		}
		return null;
	}
	/**
	 * Create the componet's display object
	 *
	 * @return CmsComponent
	 */
	function build($callComponent, $secure = true){
		return $this->getdisplayComponent($callComponent, $secure);
	}
	
	/**
	 * Compile the requested template
	 * 
	 * @see BuilderComponentInterface::compileTemplate()
	 */	
	function compileTemplate(){
		$meta = '';
		$js = '';
		
		if($this->jQueryVersion && !BASIC_GENERATOR::init()->getHead('jQuery')){
			$js .= BASIC_GENERATOR::init()->script(null, 'src='.BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'scripts/jquery/jquery-'.$this->jQueryVersion.'.js');
		}
		if($this->jQueryUIVersion){
			if(!BASIC_GENERATOR::init()->getHead('jQueryUI')){
				$js .= BASIC_GENERATOR::init()->script(null, 'src='.BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'scripts/jquery/jquery-ui-'.$this->jQueryUIVersion.'.js');
			}
			if(!BASIC_GENERATOR::init()->getHead('jQueryUITheme')){
				$meta .= "\n".BASIC_GENERATOR::init()->element('link', 'href='.BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'scripts/jquery/themes/'.$this->jQueryUITheme.'/ui.all.css|rel=stylesheet|type=text/css');
			}
		}
		if($this->useJSSvincs && !BASIC_GENERATOR::init()->getHead('Svincs')){
			$js .= BASIC_GENERATOR::init()->script(null, 'src='.BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'scripts/svincs/svincs.js');
		}
		if($this->useJSSvincs && !BASIC_GENERATOR::init()->getHead('SvincsRootVirtual')){
			$js .= BASIC_GENERATOR::init()->script("Svincs.ROOT_VIRTUAL = '".BASIC::init()->ini_get('root_virtual')."'")."\n";
		}
		$meta .= "\n".BASIC_GENERATOR::init()->element('meta', 'name=generator|value='.BASIC::init()->ini_get('cms_name').", www.cms".(int)BASIC::init()->ini_get('cms_version').".sbnd.net");
		$meta .= "\n".BASIC_GENERATOR::init()->element('meta', 'name=author|value=SBND Technologies, www.sbnd.net');
		
		foreach (BASIC_GENERATOR::init()->getHeadAll() as $k => $v){
			if($v && $v['ctrl']){
				if($v['tag'] == 'script'){
					$js .= $v['ctrl'];
				}else{
					$meta .= $v['ctrl'];
				}
			} 
		} 
		
		BASIC_TEMPLATE2::init()->set(array(
			'META' 		=> $meta,
			'JS'		=> $js,
			'MESSAGES' 	=> BASIC_ERROR::init()->getData()
		));
	    return BASIC_TEMPLATE2::init()->parse($this->baseTemplate);
	}

	/**
	 * 
	 * Meta name of the site
	 * @access private
	 * @var string
	 */
	protected $META_NAMES = '';
	protected $LAST_META_NAME = '';
	/**
	 * 
	 * Set site META names
	 * 
	 * @param string $title
	 * @param boolean $clean
	 * 
	 */
	public function META_NAMES($title, $clean = false){
		if($title){
			if($clean) $this->META_NAMES = '';
			
		    if(!is_array($title)) $title = array($title);
		    
		    $separator = " ● ";
		    
		    foreach ($title as $k => $v){
		        $this->META_NAMES .= $v.$separator;
		        
		        $this->LAST_META_NAME = $v;
		    }
		    BASIC_GENERATOR::init()->head('page_title', 'title', null, substr($this->META_NAMES, 0, strlen($separator)*-1));
		}
	}
	protected $META_DESC = '';
	/**
	 * Set site META description
	 * 
	 * @see BuilderComponentInterface::META_DESC()
	 */
	public function META_DESC($desk = null){
		if($desk === null){
			return $this->META_DESC;
		}
		$this->META_DESC = $desk;
	    BASIC_GENERATOR::init()->head('pdesk', 'meta', 'name=description|content='.$desk);
	}
	protected $META_KEYS = '';
	/**
	 * META KEYS
	 * @see BuilderComponentInterface::META_KEYS()
	 */
	public function META_KEYS($keys = null){
		if($keys === null){
			return $this->META_KEYS;
		}
		$this->META_KEYS = $keys;
	    BASIC_GENERATOR::init()->head('pkeys', 'meta', 'name=keywords|content='.(is_array($keys) ? implode(",",$keys) : $keys));
	}
}
BASIC_GENERATOR::init()->registerHead("jQuery");
BASIC_GENERATOR::init()->registerHead("jQueryUI");
BASIC_GENERATOR::init()->registerHead("jQueryUITheme");
BASIC_GENERATOR::init()->registerHead("Svincs");