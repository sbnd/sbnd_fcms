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
* @package cms.controlers.back
* @version 7.0.4  
*/

BASIC::init()->imported('builder.mod','cms');
/**
 * 
 * Build components for admin panel
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package cms.controlers.back
 */
class Builder extends BuilderComponent {
	/**
	 * Base template for the cms
	 * @access public
	 * @var string
	 */
	var $baseTemplate = 'base.tpl';
	/**
	 * Builded component
	 * @access public
	 * @var object
	 */
	var $displayComponent = null;
	/**
	 * Db table for gropes in cms menu, 'module_groups'
	 * 
	 * Default record is 'system' (menu)
	 * 
	 * @var unknown_type
	 */
	var $group_container = 'module_groups';
	/**
	 * Create Builder object using singleton pattern
	 * 
	 * @static
	 * @access public
	 * @param array $array
	 * @return Builder $GLOBALS['BASIC_CPANEL']
	 */
	static public function init($array = array()){
		if(!isset($GLOBALS['BASIC_CPANEL']) || $GLOBALS['BASIC_CPANEL'] == null){
			$GLOBALS['BASIC_CPANEL'] = new Builder();
			$GLOBALS['BASIC_CPANEL']->registerSystemComponents();
		}
		foreach ($array as $k => $v){
			if($k == 'model'){
				$GLOBALS['BASIC_CPANEL']->registerComponents($v);
			}else{
				$GLOBALS['BASIC_CPANEL']->$k = $v;
			}
		}
		return $GLOBALS['BASIC_CPANEL'];
	}
	/**
	 * Extends parent method like adding two new system componets to register array.
	 * Admin Login
	 * Dashboard
	 * 
	 * @access private
	 * @return void
	 */
	function registerSystemComponents(){
		BASIC_LANGUAGE::init()->start();
		
		parent::registerSystemComponents();
		
		$register = array();
		
		$register[] = array(
			'name' => '#admin-login',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('cms_cmp_access'),
				'class' => 'Access',
				'folder' => 'cms/controlers/back'
			)
		);
		$register[] = array(
			'name' => 'dashboard',
			'context' => array(
				'public_name' => BASIC_LANGUAGE::init()->get('dashboard'),
				'class' => 'Dashboard',
				'folder' => 'cms/controlers/back'
			)
		);

		$this->registerComponents($register);
	}
	/**
	 * @access private
	 * @var array
	 */
	private $_cmp_map = array();
	/**
	 * @access private
	 * @var boolean
	 */
	private $_tmp_current = false;
	/**
	 * Get array needed for cms menu
	 * 
	 * @access public
	 * @param integer $id
	 * @param integer $level
	 * @return array
	 */
	function getMenu($id = 0, $level = 0){
		
		$menu = array();
		
		$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->group_container."` WHERE 1=1 AND `_parent_self` = ".$id." ");
		
		while($rdr->read()){
			if(!$level) $this->_tmp_current = false;
			
			$child = $this->getMenu($rdr->item('id'), $level+1);
			if(isset($this->_cmp_map[$rdr->item('id')])){
				foreach ($this->_cmp_map[$rdr->item('id')] as $obj){
					$item_current = false;
					if(
						$this->displayComponent->prefix.$obj->system_name == $this->displayComponent->prefix.$this->displayComponent->model->system_name ||
						$obj->system_name == $this->parseUrlVar(true)
					){
						$this->_tmp_current = true;
						$item_current = true;
					}
					
					$child[] = array(
						'data'    => array(
							'level'   => $level+1,
							'title'   => $obj->public_name,
							'href'    => $obj->link."?".$this->nameUrlVar.'='.$obj->prefix.$obj->system_name,
							'tooltip' => $obj->tooltip,
							'target'  => $obj->target,
							'current' => $item_current,
							'uid'	  => $obj->system_name
						),
						'childs'  => array()
					);
				}
			}
			
			$menu[] = array(
				'data'    => array(
					'level'   => $level,
					'title'   => $rdr->item('name_'.BASIC_LANGUAGE::init()->current()) ? $rdr->item('name_'.BASIC_LANGUAGE::init()->current()) : $rdr->item('name'),
					'href'    => "#",
					'tooltip' => "",
					'target'  => "",
					'current' => $this->_tmp_current,
					'uid'	  => 'cat_'.$rdr->item('id')
				),
				'childs'  => $child
			);
		}
		if(!$level && isset($this->_cmp_map[0])){
			foreach($this->_cmp_map[0] as  $obj){
				if(!$obj->admin_support) continue; 
				
				
				$item_current = false;
				if($this->displayComponent->prefix.$obj->system_name 
						== 
					$this->displayComponent->prefix.$this->displayComponent->model->system_name
				){
					$this->_tmp_current = true;
					$item_current = true;
				}
				
				$menu[] = array(
					'data'    => array(
						'level'   => $level,
						'title'   => $obj->public_name,
						'href'    => $obj->link."?".$this->nameUrlVar.'='.$obj->prefix.$obj->system_name,
						'tooltip' => $obj->tooltip,
						'target'  => $obj->target,
						'current' => $item_current,
						'uid'	  => $obj->system_name
					),
					'childs'  => array()
				);
			}
		}
		return $menu;
	}
	/**
	 * Generate html for cms menu
	 * 
	 * @access public
	 * @param array $array
	 * @param string $template
	 * @return string
	 */	
	function createMenu($array, $template){
		BASIC_TEMPLATE2::init()->set('notes', $array, $template);
		
		return BASIC_TEMPLATE2::init()->parse($template);
	}
	/**
	 * @access private
	 * @var array
	 */
	protected $url_parsed = null;
	/**
	 * @access public
	 * @param boolean $giveMeParent
	 */
	function parseUrlVar($giveMeParent = false){
		if($this->url_parsed === null){
			$this->url_parsed = array();
			
			if($url = BASIC_URL::init()->request($this->nameUrlVar)){
				$this->url_parsed = explode(":", $url);
			}
		}
		if($this->url_parsed){
			if($giveMeParent){
				return (isset($this->url_parsed[1]) ? $this->url_parsed[1] : '');
			}else{
				return $this->url_parsed[0];
			}
		}
		return '';
	}
	/**
	 * Create component object
	 * 
	 * @access public
	 * @return CmsComponent
	 */
	function initComponent(){
		//BASIC_LANGUAGE::init()->start();
			
		$this->registerComponents($this->buildRegister(false));
		
		$nameComponent = $this->parseUrlVar();
		
		$this->getCPanelPath();

		if($this->loginMode != 'none'){
			$this->_login = $this->getdisplayComponent('admin-login', false);
		}
		
		if($this->_login && !$this->_login->check() && $this->loginMode == 'total'){
			$this->_login->runTotalMode();
			$this->displayComponent = $this->_login;
		}else{
			//try {
				$this->displayComponent = parent::getdisplayComponent($nameComponent);
			//}catch (Exception $e){
			//	BASIC_ERROR::init()->append($e->getCode(), $e->getMessage());
			//}
			if(!$this->displayComponent/* || $this->displayComponent->model->type != 'standart'*/){
		        if($this->startedCmp){
		            $this->displayComponent = parent::getdisplayComponent($this->startedCmp, false);
		        }
		        if(!$this->displayComponent){
				    foreach ($this->model as $k => $v){
				    	if($v->type != 'system'){
				        	$this->displayComponent = parent::getdisplayComponent($k, false); break;
				    	}
				    }
		        }
			}		
		}
		if($this->_login && $this->loginMode == 'box'){
			BASIC_TEMPLATE2::init()->set('LOGIN_BOX', $this->_login->startPanel());
		}
		// if total just run the login's logged interface
		if($this->_login && $this->loginMode == 'total' && $this->_login->check()){
			$this->_login->startPanel();
		}
		
		if($this->displayComponent && $this->displayComponent->model){
			$this->META_NAMES($this->displayComponent->model->public_name, false);
		}
		$this->pageMaxRowsManager();
		return $this->displayComponent;
	}
	/**
	 * Set max row per page for component list
	 * 
	 * @access private
	 * @return void
	 */
	protected function pageMaxRowsManager(){
		$max_rows = BASIC_URL::init()->request('page_rows');
		if($max_rows !== null){
			// FIX for special user
			if(BASIC_USERS::init()->level() == -1){
				setcookie('page_max_rows', $max_rows);
			}
			BASIC_USERS::init()->set('page_max_rows', $max_rows);
			BASIC_USERS::init()->saveData();
			
			BASIC_URL::init()->un('page_rows');
		}
		if(BASIC_USERS::init()->level() == -1 && BASIC_URL::init()->cookie('page_max_rows') !== null){
			$this->displayComponent->maxrow = (int)BASIC_URL::init()->cookie('page_max_rows');
			if($this->displayComponent->maxrow == -1) $this->displayComponent->maxrow = 0;
		}
		if(BASIC_USERS::init()->getUserId() && (int)BASIC_USERS::init()->get('page_max_rows')){
			$this->displayComponent->maxrow = (int)BASIC_USERS::init()->get('page_max_rows');
			if($this->displayComponent->maxrow == -1) $this->displayComponent->maxrow = 0;
		}
	}
	/**
	 * Returned html = breadcrumb + html, returned from component startPanel()
	 * @access public
	 * @return string
	 */
	function start(){
		parent::start();
		
		BASIC_TEMPLATE2::init()->set(array(
			'BASE' => BASIC::init()->ini_get('root_virtual').$this->cPanelPath,
			'THEME'=> BASIC::init()->ini_get('root_virtual').CMS_SETTINGS::init()->get('SITE_THEME')
		));
		if($this->loginMode == 'none' || !$this->_login || $this->loginMode == 'total'){
			if(!$this->_login || ($this->_login && $this->_login->check())){
				if(class_exists('BASIC_AJAX')){
					BASIC_AJAX::init()->listenerRemote();
				}
				
				$this->_cmp_map = array();
				foreach($this->child as $obj){
					
					if($obj->type == 'system' || (
					    BASIC_USERS::init()->level() != -1 &&
					    ($obj->prefix.$obj->system_name) != $this->startedCmp && 
					    BASIC_USERS::init()->getPermission(($obj->prefix.$obj->system_name),'list', -1) === false &&
					    BASIC_USERS::init()->getPermission(($obj->prefix.$obj->system_name),'__child'.($obj->prefix.$obj->system_name), -1) === false
					)) continue;
					
					if(!isset($this->_cmp_map[$obj->system])){
						$this->_cmp_map[$obj->system] = array();
					}
					$this->_cmp_map[$obj->system][] = $obj;
				}
				$menu_data = $this->getMenu();
				$this->_cmp_map = null;
				
				BASIC_TEMPLATE2::init()->set('menu', $menu_data);
			}else{
				BASIC_AJAX::init(array(
					'error' => new BASIC_AJAX_ERROR('801')
				));
			}
		}
		
		$content = $this->displayComponent->startPanel();
		BASIC_TEMPLATE2::init()->set(array(
			'BREADCRUMBS' => BREADCRUMBS(),
			'CONTENT' 	  => $content
		));
				
		return $this->compileTemplate();
	}
	/**
	 * Getter for cPanelPath property
	 * 
	 * @access public
	 * @return string
	 */
	function getCPanelPath(){
		return $this->cPanelPath;
	}
	/**
	 * Redirect to component
	 * 
	 * @access public
	 * @param string $name
	 * @param array [$param]
	 * @param array [$arrmiss]
	 * @return void
	 */
	function goToComponent($name, $param=array(), $arrmiss=array()){
		$arrmiss[] = $this->nameUrlVar;
		
		BASIC_URL::init()->redirect(BASIC::init()->scriptName(), 
			BASIC_URL::init()->serialize($arrmiss).BASIC_URL::init()->userSerialize($param).$this->nameUrlVar.'='.$name);
	}
}