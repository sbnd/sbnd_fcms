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
* @package cms.controlers.front
* @version 7.0.6  
*/

BASIC::init()->imported('builder.mod', 'cms');
/**
 * User friendly URLs manager. 
 * 
 * The system use this active in front end when generate url links and variables. 
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.0.0
 * @since 03.09.2011
 */
class BuilderRewrite extends BasicRewrite implements BasicRewriteInterfase{
	
	/**
	 * Container for main page information
	 * @access private
	 * @var array
	 */
	protected $_pdata = array();
	/**
	 * Getter for $this->_pdata
	 * 
	 * @access public
	 * @return array
	 */
	public function pdata(){
		return $this->_pdata;
	}
	/**
	 * Processing url parameters, set script_name
	 * 
	 * @access public
	 * @return void
	 * @see BasicRewrite::decoder()
	 */
    function decoder(){
    	if(!$scr = BASIC_URL::init()->request('script_name', 'charAdd')){
    		BASIC_LANGUAGE::init()->start();
    		return;
    	} 
    	
    	BASIC_URL::init()->un('script_name');
    	
    	$url = BASIC_URL::init()->request($this->var_name, 'cleanURLInjection');
    	if($scr == BASIC_LANGUAGE::init()->varLog){
    		$l = explode("/", $scr."/".$url);
    		$_GET[$l[0]] = $l[1];
    		BASIC_LANGUAGE::init()->start();
    	}else{
    		if(preg_match("/(".BASIC_LANGUAGE::init()->varLog.")\/([^\/]+)\//", $url, $matches)){
    			$_GET[$matches[1]] = $matches[2];
    		}
    		BASIC_LANGUAGE::init()->start();
    		
	    	if(!$this->_pdata = Builder::init()->page($scr)){
	    		if($url){
	    			$url = $scr."/".$url;
	    		}else{
	    			$url = $scr; 
	    		}
	    		$scr = '';
	    	}else{
	    		if(!$this->_pdata['publish']){
	    			$scr = '';
	    			$this->_pdata = array();
	    		}else{
	    			Builder::init()->META_NAMES($this->_pdata['title']);
	    			Builder::init()->breadcrumb(Builder::init()->pagesControler->menuLinksBuilder($this->_pdata));
	    		}
	    	}
    	}
        if($url){
	        $tmp = ''; $incr = 0; $stop = false;
	        foreach (explode("/", $url) as $v){
	        	if($scr){
	        		$pdata = array();
	        		if(!$stop) $pdata = Builder::init()->page($v, $this->_pdata['id']);
	        		if($pdata){
	        			$this->_pdata = $pdata;
	        			$scr .= "/".$v;
	        			Builder::init()->META_NAMES($this->_pdata['title']);
	        			Builder::init()->breadcrumb(Builder::init()->pagesControler->menuLinksBuilder($this->_pdata));
	        			continue;
	        		}else{
	        			$stop = true;
	        		}
	        	}
	            
	            if(!($incr % 2)){
	            	$tmp = $v;
	                $_GET[$tmp] = '';
	            }else{
	                $_GET[$tmp] = $v;
	                $tmp = '';
	            }
	            $incr++;
	        }
        }
        unset($_GET[$this->var_name]);
        
        BASIC::init()->ini_set('script_name', $scr);
    }	
}
/**
 * Template plugin tag <!-- navigation(menu name,template name) -->
 * 
 * <code>
 * 		<!-- lingual(top,cms-top-menu.tpl) -->
 * </code>
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 */
class BasicTemplatePluginNavigation implements BasicTemplatePluginInterface{
	/**
	 * Change navigation template tag with php code
	 * 
	 * @access public
	 * @param string $source
	 * @return string
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- navigation\(([^\)]+),([^\)]+)\) -->/', array($this, '_parse'), $source);
	}
	/**
	 * Help method for parse()
	 * 
	 * @access private
	 * @param array $match
	 * @return string php code
	 */
	private function _parse($match){
		return "<?php print Builder::init()->menu('".preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[1])."', '".$match[2]."'); ?>";
	}
}
/**
 * Template plugin tag !-- sub-menu(menu name,template name) -->
 *
 * Collect all pages included in "menu name",
 * Check for page exist in navigation path,
 * Collect all child pages from detected page,
 * Create menu from "template name"
 *
 * <code>
 * 		<!-- sub-menu(catalog,my-artical-menu.tpl) -->
 * </code>
 *
 * @author Evgeni Baldzhiyski
 * @version 0.1
 */
class BasicTemplatePluginSubMenu implements BasicTemplatePluginInterface{
	/**
	 * Change navigation template tag with php code
	 *
	 * @access public
	 * @param string $source
	 * @return string
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- sub-menu\(([^\)]+),([^\)]+)\) -->/', array($this, '_parse'), $source);
	}
	/**
	 * Help method for parse()
	 *
	 * @access private
	 * @param array $match
	 * @return string php code
	 */
	private function _parse($match){
		return "<?php print BasicTemplatePluginSubMenu::parser(".
				"'".preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[1])."',".
				"'".$match[2]."'); ?>";
	}
	/**
	 * @todo description
	 *
	 * @static
	 * @access public
	 * @param string $menu_name
	 * @param string $template_name
	 * @return string
	 */
	static public function parser($menu_name, $template_name){
		$pages = array();
		foreach(Builder::init()->pagesControler->getMenuData($menu_name) as $v){
			$pages[$v['data']['name']] = $v['data'];
		}

		$current_path = array();
		foreach(explode("/", BASIC::init()->scriptName()) as $v){
			if(isset($pages[$v])){
				$manus = array_flip(Builder::init()->pagesControler->getMenuPositionsNames());

				return Builder::init()->pagesControler->getMenu($manus[$pages[$v]['position'][0]], $template_name, $pages[$v]['id']);
			}
		}
		return '';
	}
}
/**
 * Template plugin tag <!-- secondary-menu(menu name,sub name menu,template name) -->
 *
 * Collect all pages from "manu name",
 * Check for page exist in navigation path,
 * Collect all child pages for detected page that include in "sub name menu"
 * Create menu from "template name"
 * 
 * <code>
 * 		<!-- secondary-menu(top,secondary,my-secondary-menu.tpl) -->
 * </code>
 *
 * @author Evgeni Baldzhiyski
 * @version 0.1
 */
class BasicTemplatePluginSecondaryMenu implements BasicTemplatePluginInterface{
	/**
	 * Change navigation template tag with php code
	 *
	 * @access public
	 * @param string $source
	 * @return string
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- secondary-menu\(([^\)]+),([^\)]+),([^\)]+)\) -->/', array($this, '_parse'), $source);
	}
	/**
	 * Help method for parse()
	 *
	 * @access private
	 * @param array $match
	 * @return string php code
	 */
	private function _parse($match){
		return "<?php print BasicTemplatePluginSecondaryMenu::parser(".
				"'".preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[1])."',".
				"'".preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[2])."',".
				"'".$match[3]."'); ?>";
	}
	/**
	 * @todo description
	 *
	 * @static
	 * @access public
	 * @param string $menu_name
	 * @param string $secondary_menu_name
	 * @param string $template_name
	 * @return string
	 */
	static public function parser($menu_name, $secondary_menu_name, $template_name){
		if($id = self::checkForId(Builder::init()->pagesControler->getMenuData($menu_name))){
			return Builder::init()->pagesControler->getMenu($secondary_menu_name, $template_name, $id);
		}
		return '';
	}
	static private function checkForId($arr){
		$id = 0;
		foreach ($arr as $k => $v){
			if($v['data']['current']){
				if(!$id = self::checkForId($v['childs'])){
					return $v['data']['id'];
				}
				return $id;
			}
		}
		return $id;
	}
}
/**
 * Template plugin tag <!-- additional-menu(page name,template name) -->
 *
 * Collect all child page from "page name"
 * Create menu from "template name"
 * 
 * <code>
 * 		<!-- additional-menu(catalog,my-artical-menu.tpl) -->
 * </code>
 *
 * @author Evgeni Baldzhiyski
 * @version 0.1
 */
class BasicTemplatePluginAdditionalMenu implements BasicTemplatePluginInterface{
	/**
	 * Change navigation template tag with php code
	 *
	 * @access public
	 * @param string $source
	 * @return string
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- additional-menu\(([^\)]+),([^\)]+)\) -->/', array($this, '_parse'), $source);
	}
	/**
	 * Help method for parse()
	 *
	 * @access private
	 * @param array $match
	 * @return string php code
	 */
	private function _parse($match){
		return "<?php print BasicTemplatePluginAdditionalMenu::parser(".
				"'".preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $match[1])."',".
				"'".$match[2]."'); ?>";
	}
	/**
	 * @todo description
	 *
	 * @static
	 * @access public
	 * @param string $page_name
	 * @param string $template_name
	 * @return string
	 */
	static public function parser($page_name, $template_name){
		$current_path = array();
		foreach(explode("/", BASIC::init()->scriptName()) as $v){
			$current_path[$v] = 1;
		}
		if(!isset($current_path[$page_name])) return;
		
		if($page = Builder::init()->pagesControler->getPage($page_name)){
			$manus = array_flip(Builder::init()->pagesControler->getMenuPositionsNames());
			
			if(isset($manus[$page['position'][0]])){
				return Builder::init()->pagesControler->getMenu($manus[$page['position'][0]], $template_name, $page['id']);
			}
		}
		return '';
	}
}
/**
 * Template plugin tag <!-- site-setting(lingual variable name) -->
 *
 * <code>
 * 		<!-- site-setting(SITE_THEME) -->
 * </code>
 *
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package basic.template
 */
class BasicTemplatePluginSiteSettings implements BasicTemplatePluginInterface{
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
		return preg_replace('/<!-- (site-setting)\(([^\)]+)\) -->/',"<?php print BasicTemplatePluginSiteSettings::parser('$2'); ?>",$source);
	}
	/**
	 *
	 *
	 * @todo description
	 *
	 * @static
	 * @access public
	 * @param string $name
	 * @return string
	 */
	static public function parser($name){
		return CMS_SETTINGS::init()->get($name);
	}
}
/**
 * Template plugin "component" for build component's boxes.
 * 
 * <!-- component(component name[,component variables]) -->
 *  
 * <!-- component(my_component) --> 
 * <!-- component(${my_component},var1=val 1|var2=val 2) --> 
 * <!-- component(${my_component},${my_component_variables}) --> 
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 */
class BasicTemplatePluginComponent implements BasicTemplatePluginInterface{
	/**
	 * Change component template tag with php code
	 * 
	 * @access public
	 * @param string $source
	 * @return string
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- component\(([^\)]+)\) -->/', array($this, '_parse'), $source);
	}
	/**
	 * Help method for parse()
	 * 
	 * @access private
	 * @param array $match
	 * @return string php code
	 */
	private function _parse($match){
		$spl = explode(',',$match[1]);
		
		$build = '';
		if(!preg_match('/\$\{[^\}]+\}/', $spl[0])){
			$build = "'".str_replace("'", "", $spl[0])."'";
		}else{
			$build = preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $spl[0]);
		}
		if(isset($spl[1])){
			if(!preg_match('/\$\{[^\}]+\}/', $spl[1])){
	    		$spl[1] = ", '".preg_replace('/[\'"]/','',$spl[1])."'";
	    		$build .= preg_replace_callback('/\$\{([^\}]+)\}/', array($this, 'translate_collback'), $spl[1]);
	   	  	}else{
	    		$build .=  ", ".preg_replace_callback('/\$\{([^\}]+)\}/', 'TemplateDriverBasic::translate_collback', $spl[1]);
	   		}
		}
		
		return "<?php print BasicTemplatePluginComponent::parser(".$build."); ?>";
	}
	/**
	 * The pluggin uses the function when it needs to parse and clean parameters. 
	 * 
	 * @static
	 * @param array $match
	 * @return string
	 */
	static public function translate_collback($match){
		
		return "'.".TemplateDriverBasic::translate_collback($match).".'";
	}
	/**
	 * Return html, generated from component startPanel()
	 * @static
	 * @access public
	 * @param string $cmp_name				 the registarated component
	 * @param string|array [$settings] any public component's variables
	 * @return string html
	 */
	static public function parser($cmp_name, $settings = ''){
		if($cmp = Builder::init()->build($cmp_name)){
			foreach(BASIC_GENERATOR::init()->convertStringAtt($settings) as $k => $v){
				$cmp->$k = $v;
			}
			return $cmp->startPanel();
		}else{
			//return 'Error load component';
			return '';
		}
	}
}
/**
 * Frontent client extends BuilderComponent.
 * 
 * variables in the base template which will be replaced:
 * 		VIRTUAL - site virtual path
 *		CONTENT - html returned from component startPanel()
 * 		LOGIN_BOX - html retruned from startPanel() of loginCmpq if in the setting is set login box for login method
 * 		MESSAGES - List with errors
 * 		META - s appended headers in BASIC_GENERATOR using head method
 * 		PAGE_DATA - page information
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.6
 * @since 03.09.2011
 */
class Builder extends BuilderComponent{
	/**
	 * Instance of Pages
	 * @access public
	 * @var Pages
	 */
	public $pagesControler = null;
	/**
	 * @access public
	 * @var array
	 */
    public $currentPage = array();
    /**
     * @access private
     * @var array
     */
    protected $_breadcrumb = array();
    /**
     * @access private
     * @var integer
     */
    protected $_breadcrumb_length = 0;
	
    /**
     * Get Builder object using singleton pattern
     * 
     * @static
     * @access public
     * @return Builder object
     */
    static public function init($settings = array()){
    	if(!isset($GLOBALS['BASIC_FRONT_CLIENT'])){
    		$GLOBALS['BASIC_FRONT_CLIENT'] = new Builder();
    		
    		BASIC_LANGUAGE::init()->start();
    		$GLOBALS['BASIC_FRONT_CLIENT']->registerSystemComponents();
    		
    		BASIC_TEMPLATE2::init()->driver->addPlugin('BasicTemplatePluginNavigation', new BasicTemplatePluginNavigation());
    		BASIC_TEMPLATE2::init()->driver->addPlugin('BasicTemplatePluginSecondaryMeny', new BasicTemplatePluginSecondaryMenu());
    		BASIC_TEMPLATE2::init()->driver->addPlugin('BasicTemplatePluginComponent', new BasicTemplatePluginComponent());
    		BASIC_TEMPLATE2::init()->driver->addPlugin('BasicTemplatePluginSiteSettings', new BasicTemplatePluginSiteSettings());
    		BASIC_TEMPLATE2::init()->driver->addPlugin('BasicTemplatePluginAdditionalMenu', new BasicTemplatePluginAdditionalMenu());
    		BASIC_TEMPLATE2::init()->driver->addPlugin('BasicTemplatePluginSubMenu', new BasicTemplatePluginSubMenu());
    		
    		$GLOBALS['BASIC_FRONT_CLIENT']->pagesControler = $GLOBALS['BASIC_FRONT_CLIENT']->getdisplayComponent('pages', false);
    	}
		foreach ($settings as $k => $v){
			if($k == 'register'){
				$GLOBALS['BASIC_FRONT_CLIENT']->registerComponents($v);
			}else{
				$GLOBALS['BASIC_FRONT_CLIENT']->$k = $v;
			}
		}
    	return $GLOBALS['BASIC_FRONT_CLIENT'];
    }
    /**
     * Add system component in the register.
     * 
     * @access public
     * @return void
     */
    function registerSystemComponents(){
    	parent::registerSystemComponents();
		
    	$register = array();
    	
    	try{
    		$register = $this->buildRegister(false);	
    	}
    	catch (Exception $e){
    		
    	}

		$this->registerComponents($register);
    }
    /**
     * @access private
     * @var object
     */
    protected $rewrite = null;
    /**
     * The system starts. It's main function for the system.
     * 	!) Set default variable
     * 	!) Set the component register
     * 	!) Manage ans parse URL
     * 	!) Find, build, run component
     * 	!) Parse template
     * 	!) Print in the output compile HTML.
     * 
     * @@access public
     * @return string
     */
    function start(){
    	
    	$this->themesManager();
    	
    	$this->rewrite = new BuilderRewrite();
    	
		BASIC::init()->ini_set('rewrite', $this->rewrite);
		BASIC_URL::init()->restart();
		
		BASIC_TEMPLATE2::init()->set(array(
			'THEME' => BASIC::init()->ini_get('root_virtual').CMS_SETTINGS::init()->get('SITE_THEME'),
			'CURRENT_LANG' => BASIC_LANGUAGE::init()->current()
		));
		
		if(CMS_SETTINGS::init()->get('SITE_OPEN')){
			$this->baseTemplate = 'under-construction.tpl';
			BASIC_TEMPLATE2::init()->set('CONTENT', CMS_SETTINGS::init()->get('SITE_OPEN'));
		}else{
			if($this->loginMode != 'none'){
				$this->_login = $this->build('login', false);
			}
			//if($this->loginMode == 'none' || !$this->_login || $this->loginMode == 'total'){
				if(class_exists('BASIC_AJAX')){
					if(!$this->_login || ($this->_login && $this->loginMode == 'box') || ($this->_login && $this->_login->check())){
						if($this->_login) $this->_login->check();
						BASIC_AJAX::init()->listenerRemote();
					}else{
						BASIC_AJAX::init(array(
							'error' => new BASIC_AJAX_ERROR('801')
						));
					}
				}
			//}
			if($this->_login && !$this->_login->check() && $this->loginMode == 'total'){
				$this->_login->runTotalMode();
				$cmp = $this->_login;
			}else{
				$cmp = $this->compilePage();
			}
			
			if($this->_login && $this->loginMode == 'box'){
				BASIC_TEMPLATE2::init()->set('LOGIN_BOX', $this->_login->startPanel());
			}
			// if total just run the login's logged interface
			if($this->_login && $this->loginMode == 'total'){
				$this->_login->startPanel();
			}
			BASIC_TEMPLATE2::init()->set('CONTENT', $cmp->startPanel());
			BASIC_TEMPLATE2::init()->set('BREADCRUMBS', $this->breadcrumb());
		}
		print $this->compileTemplate();
    }
    protected function themesManager(){
		$except_theme = 'exception';
		$mobile_theme = 'mobile';
		
		$cache = BASIC_CACHE::init()->open('register');
	
		$theme = CMS_SETTINGS::init()->get('SITE_THEME_NAME');
		if($cache->check($theme.'browsers')){
			$browsers = $cache->cacheArray($theme.'browsers');
		}
		else{
			//create array from ini file
			$file = BASIC::init()->ini_get('root_path'). CMS_SETTINGS::init()->get('SITE_THEME')."support.ini";
			if (file_exists($file)){
				$browsers = BASIC_LANGUAGE::ini_parcer(file($file));
				//write in cache
				$cache->cacheArray($theme.'browsers',$browsers);
			}
		}
		if($browsers){
			$props = array(
				"Version" => "0.0.0",
				"Name"    => "unknown",
				"Agent" => "unknown"
			); 
			
			$props['Agent'] = strtolower($_SERVER['HTTP_USER_AGENT']);	
			foreach($browsers as $browser => $min_version){ 	
				//check browsers
				$check_browser = preg_match("#($browser)[/ ]?([0-9.]*)#", $props['Agent'], $match);	
				if($check_browser){		
						if($device = BASIC::init()->mobileDetector()){
						file_put_contents('upload/mobile.log',$device);
						$check_device = preg_match("#($browser)[/ ]?([0-9.]*)#", $device, $match);
						$mobile_dir = CMS_SETTINGS::init()->get('SITE_THEME').$except_theme.'/'.$mobile_theme.'/';
						if($check_device && is_dir($mobile_dir)){
								CMS_SETTINGS::init()->set('SITE_THEME',  CMS_SETTINGS::init()->get('SITE_THEME').$except_theme.'/'.$mobile_theme.'/');
					 	   	   	CMS_SETTINGS::init()->set('SITE_THEME_NAME',$mobile_theme);
					 	   	   	BASIC_TEMPLATE2::init(array( 
									'prefix_ctemplate' => $mobile_theme
								));
								break;
						}
					}
					
					$props['Name'] = $match[1] ;
			 	   	$props['Version'] = $match[2];
			 	 
			 	   	if((string)$props['Version'] <= (string)$min_version){
			 	   		CMS_SETTINGS::init()->set('SITE_THEME',  CMS_SETTINGS::init()->get('SITE_THEME').$except_theme.'/');
			 	   	   	CMS_SETTINGS::init()->set('SITE_THEME_NAME',$except_theme);
			 	   	   	BASIC_TEMPLATE2::init(array( 
							'prefix_ctemplate' => $except_theme
						));  
						break ;	 
			 	   	}   			
	   		 	}
			}
			BASIC_TEMPLATE2::init(array(
				'template_path' => CMS_SETTINGS::init()->get('SITE_THEME').'tpl'
			));
		}
	}    
    
    /**
     * Compile template
     * 
     * @access public
     * @return string
     */
    function compileTemplate(){
    	//$this->META_KEYS(str_replace(" ", ",", $this->LAST_META_NAME).",".$this->META_KEYS());
    	
    	return parent::compileTemplate();
    }
    /**
     * Get page info
     * 
     * @access public
     * @return array with page data
     */
    function compilePage(){
    	$cmp_name = 'pages';
		$this->currentPage = $this->rewrite->pdata();
		if(!$this->currentPage){
			$this->currentPage = $this->page(CMS_SETTINGS::init()->get('SITE_START_PAGE'));
			$this->META_NAMES($this->currentPage['title']);
			$this->breadcrumb($this->currentPage);
			BASIC::init()->ini_set('script_name', $this->currentPage['name']);
		}
		if($this->currentPage['component_name'] && $this->getRegisterComponent($this->currentPage['component_name']) && BASIC_USERS::init()->getPermission($this->currentPage['component_name'], 'list')){
			$cmp_name = $this->currentPage['component_name'];
		}
		$this->META_DESC($this->currentPage['meta_description'] ? $this->currentPage['meta_description'] : CMS_SETTINGS::init()->getLangSettings('SITE_DESK'));
		$this->META_KEYS($this->currentPage['meta_key'] ? $this->currentPage['meta_key'] : CMS_SETTINGS::init()->getLangSettings('SITE_KEYS'));
		
		$pcmp = BASIC_URL::init()->request($this->nameUrlVar);
		
		if(!$pcmp || $pcmp == $cmp_name){
			BASIC_URL::init()->un($this->nameUrlVar);
		}else{
			if(isset($this->model[$cmp_name]) && $this->model[$cmp_name]->child){
				$tmp = $this->getChildComponent($this->model[$cmp_name]->child, $pcmp);
				if($tmp) $cmp_name = $tmp;
	    	}
		}
    	$cmp = $this->getdisplayComponent($cmp_name);
    	
    	if(!$cmp || ($cmp_name != 'pages' && $cmp->model->type == 'system')){
	        if($this->startedCmp){
	            $cmp = $this->getdisplayComponent($this->startedCmp);
	        }
	        if(!$cmp){
			    foreach ($this->model as $k => $v){
			        $cmp = $this->getdisplayComponent($k); break;
			    }
	        }
	    }
	    if($this->currentPage && $cmp){
	    	$cmp->pdata = $this->currentPage;
	    }
	    
	    BASIC_TEMPLATE2::init()->set('PAGE_DATA', $cmp->pdata);
	    return $cmp;
    }
    /**
     * Set information to breadcrumb array
     * 
     * @access public
     * @param array $data
     * @return array/void
     */
    function breadcrumb($data = null){
    	if($data === null){
    		return $this->_breadcrumb;
    	}
    	if($this->_breadcrumb){
    		$this->_breadcrumb[$this->_breadcrumb_length - 1]['current'] = false;
    	}
    	$data['current'] = true;
        	
    	$this->_breadcrumb[] = $data;
    	$this->_breadcrumb_length++;
    }
    /**
     * Secure page component tree
     * 
     * @access public
     * @param array $rource
     * @param string $name
     * @return string
     */
    protected function getChildComponent($rource, $name){
    	foreach ($rource as $obj){
			if($obj->system_name == $name){
				return $name;
			}
			$tmp = $this->getChildComponent($obj->child, $name);
			if($tmp){
				return $tmp;
			}
		}
		return '';
    }
	/**
	 * Get row|page data
	 * 
	 * @access public
	 * @param string $name
	 * @param integer [$parent_id]
	 * @return array
	 */
	public function page($name, $parent_id = -1){
		return $this->pagesControler->getPage($name, $parent_id);
	}
	/**
	 * Get BasicTemplatePluginMenu valid tree array
	 * 
	 * @access public
	 * @param string $name
	 * @param string [$template]
	 * @return string
	 */
	public function menu($name, $template = ''){
		return $this->pagesControler->getMenu($name, $template);
	}
	/**
	 * @todo empty method
	 * @access public
	 * @param string $from_page
	 */
	public function pathLine($from_page){
		
	}
}