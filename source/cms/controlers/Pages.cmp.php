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
* @package cms.controlers
* @version 7.0.4
*/


BASIC::init()->imported('SearchBar.cmp', 'cms/controlers/front');
/**
 * 
 * Menu target action plugin interface
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.controlers
 */
interface MenuTargetActionPlugin{
	/**
	 * 
	 * Get option name
	 * 
	 *  1. self
	 *  2. blank
	 *  3. top
	 *  4. popup
	 *  5. modal
	 */
	function getOptionName();
	/**
	 * 
	 * Get option value
	 * 
	 *  1. _self
	 *  2. _blank
	 *  3. _top
	 *  4. _popup
	 *  5. _modal 
	 */
	function getOptionValue();
	/**
	 * 
	 * 
	 * Get action for the html <a> tag
	 * 
	 * @param array [$attr]
	 */
	function getAction($attr = array());
	/**
	 * 
	 * Get attributes
	 * 
	 * @return array
	 */
	function getAttributes();
}
/**
 * 
 * Menu target self plugin class
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.controlers
 */
class MenuTargetActionPluginSelf implements MenuTargetActionPlugin{
	
	/**
	 * 
	 * Return option name
	 * 
	 * @return string
	 * @see MenuTargetActionPlugin::getOptionName()
	 */
	function getOptionName(){
		return 'self';
	}
	/**
	 * Return option value
	 * 
	 * @return string
	 * @see MenuTargetActionPlugin::getOptionValue()
	 */
	function getOptionValue(){
		return '_self';
	}
	/**
	 * 
	 * Get action
	 * 
	 * @param array [$attr]
	 * @return string
	 * @see MenuTargetActionPlugin::getAction()
	 */
	function getAction($attr = array()){
		return '';
	}
	/**
	 * 
	 * Get attributess
	 * 
	 * @return array
	 * @see MenuTargetActionPlugin::getAttributes()
	 */
	function getAttributes(){
		
		return array();
	}
}
/**
 * 
 * Menu target blank plugin class
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.controlers
 */
class MenuTargetActionPluginBlank implements MenuTargetActionPlugin{
	/**
	 * 
	 * Return option name
	 * 
	 * @return string
	 * @see MenuTargetActionPlugin::getOptionName()
	 */
	function getOptionName(){
		return 'blank';
	}
	/**
	 * 
	 * Return option value
	 * 
	 * @return string
	 * @see MenuTargetActionPlugin::getOptionValue()
	 */
	function getOptionValue(){
		return '_blank';
	}
	/**
	 * Get action
	 * @param array [$attr]
	 * @return string
	 * @see MenuTargetActionPlugin::getAction()
	 */
	function getAction($attr = array()){
		return 'target="_blank"';
	}
	/**
	 * Get attributess
	 * 
	 * @param array [$attr]
	 * @return array
	 * @see MenuTargetActionPlugin::getAttributes()
	 */
	function getAttributes($attr = array()){
		
		return array();
	}
}
/**
 * 
 * Menu target top plugin class
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.controlers
 */
class MenuTargetActionPluginTop implements MenuTargetActionPlugin{
	/**
	 * Return option name
	 * 
	 * @return string
	 * @see MenuTargetActionPlugin::getOptionName()
	 */
	function getOptionName(){
		return 'top';
	}
	/**
	 * Return option value
	 * 
	 * @return string
	 * @see MenuTargetActionPlugin::getOptionValue()
	 */
	function getOptionValue(){
		return '_top';
	}
	/**
	 * Get action
	 * 
	 * @param array [$attr]
	 * @return string 
	 * @see MenuTargetActionPlugin::getAction()
	 */
	function getAction($attr = array()){
		return 'onclick="(parent||window).location.href=this.href"';
	}
	/**
	 * 
	 * Get attributess
	 * 
	 * @param array [$attr]
	 * @return array 
	 * @see MenuTargetActionPlugin::getAttributes()
	 */
	function getAttributes($attr = array()){
		
		return array();
	}
}
/**
 * 
 * Menu target popup plugin class
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.controlers
 */
class MenuTargetActionPluginPopup implements MenuTargetActionPlugin{
	/**
	 * Return option name 
	 * @return string 
	 * @see MenuTargetActionPlugin::getOptionName()
	 */
	function getOptionName(){
		return 'popup';
	}
	/**
	 * Return option value
	 * 
	 * @return string
	 * @see MenuTargetActionPlugin::getOptionValue()
	 */
	function getOptionValue(){
		return '_popup';
	}
	/**
	 * Get action
	 * 
	 * @param array [$attr]
	 * @return string 
	 * @see MenuTargetActionPlugin::getAction()
	 */
	function getAction($attr = array()){
		return 'onclick="window.open(\''.$attr['href'].'ajax\', '.
			'\'menu_target_action\', '.
			'\'width='.$attr['width'].'px,height='.$attr['height'].'px,left=\'+(screen.availWidth/2-'.$attr['width'].'/2)+\',top=\'+(screen.availHeight/2-'.$attr['height'].'/2)+\',location=0,status=0,resizable=0\'); '.
			'return false"';
	}
	/**
	 * Get attributess
	 * 
	 * @param array [$attr]
	 * @return array 
	 * @see MenuTargetActionPlugin::getAttributes()
	 */
	function getAttributes(){
		return array(
			'width' => '500',
			'height' => '400'
		);
	}
}
/**
 * 
 * Menu target modal plugin class
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.controlers
 */
class MenuTargetActionPluginModal implements MenuTargetActionPlugin{
	/**
	 * Return option name 
	 * @return string
	 * 
	 * @see MenuTargetActionPlugin::getOptionName()
	 */
	function getOptionName(){
		return 'modal';
	}
	/**
	 * Return option value
	 * 
	 * @return string
	 * @see MenuTargetActionPlugin::getOptionValue()
	 */
	function getOptionValue(){
		return '_modal';
	}
	/**
	 * Get action
	 * 
	 * @param array [$attr]
	 * @return string
	 * @see MenuTargetActionPlugin::getAction()
	 */
	function getAction($attr = array()){
		$params = ''; foreach($attr as $k => $v){
			if($k != 'cmp' && $k != 'width' && $k != 'height'){
				if($params) $params .= ",";
				
				$params .= $k.":".($v == 'false' || $v == 'true' ? $v : "'".$v."'");
			}
		}
		return 'onclick="Svincs.MenuTargetModal.open(\''.$attr['href'].'ajax\','.$attr['width'].','.$attr['height'].',window,{'.$params.'}); return false"';
	}
	/**
	 * Get attributess
	 * 
	 * @param array [$attr]
	 * @return array 
	 * @see MenuTargetActionPlugin::getAttributes()
	 */
	function getAttributes(){
		return array(
			'width' => '500',
			'height' => '400',
			'draggable' => 'true',
			'resizable' => 'true'
		);
	}
}
/**
 * Default site page controler.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @package cms.controlers
 */
class Pages extends Tree implements SearchBarInterface{
	/**
	 * 
	 * Upload folder
	 * @var string
	 * @access public
	 */
	public $upload_folder 	   = 'upload/pages';
	/**
	 * 
	 * Supported filetypes (comma-separated)
	 * @var string
	 * @access public
	 */
    public $support_file_types = 'jpg,jpeg,gif,png';
    /**
     * 
     * Max filesize of uploaded file
     * @var string
     * @access public
     */
   	public $max_file_size      = '5M';
   	/**
   	 * 
   	 * Save actions array
   	 * @var array
   	 * @access private
   	 */
	protected $save_actions = array();
	/**************** SETTINGS **************/
	/**
	 * Work template 
	 * @var string
	 * @access public
	 */
	public $template_page  = 'cms-page.tpl';
	/**
	 * 
	 * Popup template
	 * @var string
	 * @access public
	 */
	public $template_popup = 'cms-popup.tpl';
	/**
	 * 
	 * Db table name
	 * @var string
	 * @access public
	 */
	public $base = 'pages';
	/**
	 * 
	 * Target actions array
	 * @var array
	 * @access private
	 */
	protected $targetActions = array();
	/**	
	 * 
	 * Object with all pages 
	 * @var object
	 * @access private
	 */
	protected $URLS = null;
	/**
	 * 
	 * @todo description
	 * @var array
	 * @access private
	 */
	protected $PAGES = array();
	/**
	 * 
	 * @todo description
	 * @var array
	 * @access private
	 */
	protected $page_path_hash = array();
	/**
	 * 
	 * Positions collections
	 * @var array
	 * @access private
	 */
	protected $positions_collection = array();
	/**
	 * 
	 * Register target actions
	 * @param object
	 * @param MenuTargetActionPlugin $object
	 */
	function registerTargetAction($object){
		$this->targetActions[$object->getOptionValue()] = $object;
	}
	/**
	 * 
	 * Uregister target action
	 * @param mixed $key
	 */
	function unregisterTargetAction($key){
		if(isset($this->targetActions[$key])) unset($this->targetActions[$key]);
	}
	/**
	 * 
	 * Get all target actions
	 * @return array
	 */
	function getTargetActions(){
		return $this->targetActions;
	}
	/**
	 * 
	 * Main function - the constructor of the component
	 * 
	 * @see Tree::main()
	 */
	function main(){
		parent::main();
		
		$this->registerTargetAction(new MenuTargetActionPluginSelf());
		$this->registerTargetAction(new MenuTargetActionPluginBlank());
		$this->registerTargetAction(new MenuTargetActionPluginTop());
		$this->registerTargetAction(new MenuTargetActionPluginPopup());
		$this->registerTargetAction(new MenuTargetActionPluginModal());
	
		$this->unsetField('_parent_self');
		
		$this->setField("name", array(
			'text' => BASIC_LANGUAGE::init()->get('content_name_label'),
			'perm' => '*'
		));
		$this->createSelfElement(BASIC_LANGUAGE::init()->get('parent'));
		$this->setField('title',array(
			'text' => BASIC_LANGUAGE::init()->get('content_public_name_label'),
			'lingual' => true,
		));
		$this->setField('sub_title',array(
			'text' => BASIC_LANGUAGE::init()->get('subtitle'),
			'lingual' => true,
		));
		$this->setField('position', array(
			'text' => BASIC_LANGUAGE::init()->get('content_menu_position_label'),
			'dbtype' => 'int',
			'length' => 11,
			'formtype' => 'selectmove'	
		));
		
		$this->setField("body", array(
			'text' => BASIC_LANGUAGE::init()->get('content_body_label'),
			'formtype' => 'html',
			'dbtype' => 'longtext',
			'lingual' => true,
			'attributes' => array(
				'height' => 400//,
				//'css' => 'themes/modern/css/test.css'
			)
		));
		$this->setField('location',array(
			'text' => BASIC_LANGUAGE::init()->get('permalink'),
			'perm' => '*'
		));
//		$this->setField("urlvars", array(
//			'text' => BASIC_LANGUAGE::init()->get('content_urlvars_label')
//		));		
		$this->setField('target',array(
			'text' => BASIC_LANGUAGE::init()->get('content_target_label'),
			'length' => 10,
			'formtype' => 'select'	
		));
		$this->setField('target_params',array(
			'text' => BASIC_LANGUAGE::init()->get('target_parameters'),
			'length' => 500,
			'dbtype' => 'varchar'
		));
		$this->setField('meta_key',array(
			'text' => BASIC_LANGUAGE::init()->get('meta_key'),
			'lingual' => true,
		));
		$this->setField('meta_description',array(
			'text' => BASIC_LANGUAGE::init()->get('meta_description'),
			'lingual' => true,
		));
		$this->setField('component_name',array(
			'text' => BASIC_LANGUAGE::init()->get('component_name'),
			'formtype' => 'select'
		));		
		$this->setField('publish', array(
			'text' => BASIC_LANGUAGE::init()->get('content_pblish_label'),
			'formtype' => 'radio',
			'dbtype' => 'int',
			'length' => 1,
			'default' => 1,
			'lingual' => true,
			'attributes' => array(
				'data' => array(
					BASIC_LANGUAGE::init()->get('no'), 
					BASIC_LANGUAGE::init()->get('yes')
				)
			)
		));
		
		$this->createSelfElement(BASIC_LANGUAGE::init()->get('parent'));
		
		$this->ordering(true);
		
		$this->save_actions = $this->getActions();
		
		$this->delAction('save');
		$this->delAction('add');
		$this->delAction('edit');
		$this->delAction('delete');
		$this->delAction('fileRemove');
		
		$this->prefix = 'page';
	}
	/**
	 * 
	 * This function will return the actual html of the component
	 * 
	 * @see CmsComponent::startPanel()
	 */
	function startPanel(){
		return BASIC_TEMPLATE2::init()->set($this->pdata, $this->template_page)->parse($this->template_page);
	}
	/**
	 * Build page link by component name. Will return the first page that is assigned to this component.
	 * 
	 * @param string $cmp_name
	 * @return string
	 */
	function getPageTreeByComponent($cmp_name){
		$this->getCashURLs();
		
		if(!isset($this->URLS['cmp'.$cmp_name])){
			if($res = $this->read(" AND `component_name` = '".$cmp_name."' ")->read()){
				$res = $this->menuLinksBuilder($res);
				$this->URLS['cmp'.$cmp_name] = $res['href']."/";
				//$this->URLS['cmp'.$cmp_name] = $this->getPageTreeById((int)$res['_parent_self'], false).$res['name']."/";
			}else{
				$this->URLS['cmp'.$cmp_name] = '';
			}
			$this->setCashURL('cmp'.$cmp_name, $this->URLS['cmp'.$cmp_name]);
		}
		return $this->URLS['cmp'.$cmp_name];
	}
	/**
	 * 
	 * Get save actions
	 * 
	 * @return array
	 */
	function getSaveActions(){
		return $this->save_actions;
	}	
	/**
	 * Get page link by page name.
	 * 
	 * @param string $page_name
	 * @return string
	 */
	function getPageTreeByName($page_name){
		$this->getCashURLs();
		
		if(!isset($this->URLS['name'.$page_name])){
			if($res = $this->read(" AND `name` = '".$page_name."' ORDER BY `_parent_self` ")->read()){
				$res = $this->menuLinksBuilder($res);
				$this->URLS['name'.$page_name] =  $res['href']."/";
				//$this->URLS['name'.$page_name] =  $this->getPageTreeById((int)$res['_parent_self']).$page_name."/";
			}else{
				$this->URLS['name'.$page_name] = $page_name."/";
			}
			$this->setCashURL('name'.$page_name, $this->URLS['name'.$page_name]);
		}
		return $this->URLS['name'.$page_name];
	}
	/**
	 * Get page link by page id.
	 * 
	 * @param int $id
	 * @param boolean $cached
	 * @return string
	 */
	function getPageTreeById($id){
		$this->getCashURLs();
		
		if(!isset($this->URLS['id'.$id])){
			if($res = $this->read(" AND `id` = ".$id." ")->read()){
				$res = $this->menuLinksBuilder($res);				
				$this->URLS['id'.$id] =  $res['href']."/";
			}else{
				$this->URLS['id'.$id] = $page_name."/";
			}
			$this->setCashURL('id'.$id, $this->URLS['id'.$id]);
		}
		return $this->URLS['id'.$id];
	}
	/**
	 * 
	 * Get page
	 * 
	 * @param string $name
	 * @param integer $parent_id
	 * @return array
	 */
	function getPage($name, $parent_id){
		$res = array();
		
		if(!$this->PAGES){
			$PAGES = array();
			
			if((@include BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations/pages_".BASIC_LANGUAGE::init()->current().".php") === false){
				$tmp_dir = BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations";
				if(!is_dir($tmp_dir)){
					if(!@mkdir($tmp_dir)){
						throw new Exception("Dont have permitions for create folders in temporary site container."); return;
					}
				}
				$f = fopen(BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations/pages_".BASIC_LANGUAGE::init()->current().".php", "w");
				fwrite($f, '<?php ');
				fclose($f);
			}	
			$this->PAGES = $PAGES; unset($PAGES);
		}
		if(!isset($this->PAGES[$name])){
			
			if($res = $this->read(" AND `name` = '".$name."' ".($parent_id ? " AND `_parent_self` = ".(int)$parent_id." " : ""))->read()){
				$this->PAGES[$name] = $res;
				
				$cache = '';
				foreach($res as $k => $v){
					if($cache) $cache .= ",";
					if(is_array($v)){
						$tmp = ''; foreach($v as $pv){
							if($tmp) $tmp .= ",";
							$tmp .= $pv;
						}
						$cache .= "'".$k."'=>array(".$tmp.")";
					}else{
						$cache .= "'".$k."'=>'".str_replace("'", "\\'", $v)."'";
					}
				}
				
				$f = fopen(BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations/pages_".BASIC_LANGUAGE::init()->current().".php", "a");
				fwrite($f, "\n".'$PAGES["'.$name.'"] = array('.$cache.');');
				fclose($f);
			}
		}else{
			$res = $this->PAGES[$name];
		}
		return $res;
	}
	/**
	 * 
	 *
	 * Menu link builder
	 * 
	 * @version 0.2
	 * @param hashmap $data
	 * @return array
	 */
	function menuLinksBuilder($data){
					
		if(!$data['location']){
			$data['href'] = BASIC_URL::init()->link($this->pagePathBuilder($data['id']), isset($data['urlvars']) ? $data['urlvars'] : '');
		}else{
			$data['href'] = str_replace('${INSIDE}', BASIC::init()->virtual(), $data['location']);
		}
		
		if ($data['location'] == '${INSIDE}'){
			$data['href'] = BASIC_URL::init()->link($this->pagePathBuilder($data['id']), isset($data['urlvars']) ? $data['urlvars'] : '');
		}
		
				
		$params = array('cmp' => $data['component_name']);
		if($data['target_params']){
			foreach(explode("&", $data['target_params']) as $v){
				$spl = explode("=", $v);
				
				$params[$spl[0]] = $spl[1];
			}
		}

		$match = false;
		foreach ($this->targetActions as $k => $v){
			if($data['target'] == $v->getOptionValue()){
				$params['href'] = $data['href'];
				$data['target'] = $v->getAction($params); $match = true; break;
			}
		}
		if(!$match) $data['target'] = '';
		return $data;
	}
	/**
	 * 
	 * Get menu data
	 * @param string $name
	 * @param int [$position_id]
	 * @param int [$parent_self]
	 * @return array
	 */
	function getMenuData($name, $position_id = 0, $parent_self = 0){
		$arr = array();
		$max_depth = int(CMS_SETTINGS::init()->get('NAVIGATION_MAX_DEPTH'));
		
		if(!$position_id){
			if(!$position_id = $this->buildMenuColection($name)) return $arr;
		}
		
		$rdr = $this->read(" AND `_parent_self` = ".$parent_self." ");
		while($rdr->read()){
			
			$continue = true;
			if($rdr->item('publish')){
				foreach($rdr->item('position') as $v){
					if($v == $position_id){
						$continue = false; break;
					}
				}
			}
			
			if($continue){
				foreach($this->getPageRelations($name, $rdr->item('id'), 0, $max_depth +1 ) as $page){
					$arr[] = $page;
				}
			}else{
				$arr[] = array(
					'data' => $this->menuLinksBuilder($rdr->getItems(), true),
					'childs' => $this->getPageRelations($name, $rdr->item('id'), 0, $max_depth) 
				);					
			}
		}
		return $arr;
	}
	/**
	 * The get menu support only simple if-else-end construction for current notes
	 * in version 2.0 neet to use node variable in loop
	 * Ex: <!-- foreach(${nodes},note) -->
	 * 
	 * @param string $name
	 * @param string $template
	 * @param int $parent_self
	 * @return string
	 * @version 2.0
	 */
	function getMenu($name, $template, $parent_self = 0){
		if(!$this->page_path_hash){
			foreach(explode("/", BASIC::init()->scriptName()) as $v){
				$this->page_path_hash[$v] = 1;
			}
		}
		if(!$position_id = $this->buildMenuColection($name)) return '';
			
		$tmp_dir = BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations";
		$theme = CMS_SETTINGS::init()->get('SITE_THEME_NAME').'_';
		if(!$this->checkForMenuCash($theme.$name.'_'.BASIC_LANGUAGE::init()->current(), $template)){
			
			$f = fopen($tmp_dir."/".$theme.$name.'_'.BASIC_LANGUAGE::init()->current().".php", 'w');
			
			$arr = $this->getMenuData($name, $position_id, $parent_self);
			
			$template_source = BASIC_TEMPLATE2::init()->getTemplateSource($template);
			
			$template_source = preg_replace('/<!-- if\([^\)]*\$\{note\.current\}[^\)]*\) -->/', 'CASH_CURRENT', $template_source);
		
			$tmp_spl = explode('CASH_CURRENT', $template_source);
			if(count($tmp_spl) > 1){
				
				$template_source = '';//$tmp_spl[0].'CASH_CURRENT#if(isset($RELATIONS[$note["name"]))#';
				
				for($i = 1; isset($tmp_spl[$i]); $i++){
					$flag = '';
					$first = '';
					foreach (explode("<!-- end -->", $tmp_spl[$i]) as $end){
						if(!$first){
							$first = str_replace('<!-- else -->', 'CASH_CURRENT#}else{#', $end); continue;
						}
						if(!$flag){
							$flag = 'CASH_CURRENT#}#'.$end;
						}else{
							$flag .= '<!-- end -->'.$end;
						}
					}
					if($template_source) $template_source.= 'CASH_CURRENT';
					
					$template_source .= $first.$flag;
				}
				
				$template_source = $tmp_spl[0].'CASH_CURRENT#if(isset($RELATIONS["${note.name}"])){#'.$template_source;
			}
			
			BASIC_TEMPLATE2::init()->createTemplate("cache-".$template, $template_source, false);
			
			$save = BasicTemplatePluginMenu::parser($arr, "cache-".$template);
			$save = preg_replace("/CASH_CURRENT#([^#]+)#/", "<?php $1 ?>", $save);
			$save = str_replace(BASIC::init()->virtual(), '<?php print BASIC::init()->virtual(); ?>', $save);
			
			fwrite($f, $save);
			fclose($f);
		}
		$RELATIONS = $this->page_path_hash;
		
		ob_start();
		
		require($tmp_dir."/".$theme.$name.'_'.BASIC_LANGUAGE::init()->current().".php");
		
		return ob_get_clean();
	}
	/**
	 * 
	 * Clear cache of the menu
	 * 
	 */
	function clearMenuCash(){
		$path = BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations";
		if(!is_dir($path)){
			if(!@mkdir($path)){
				throw new Exception("Dont have permitions for create folders in temporary site container."); return;
			}
		}
		$dir = opendir($path);
		while ($f = readdir($dir)){
			if($f == '.' || $f == '..') continue;
			
			unlink($path."/".$f);
		}
	}
	/**
	 * 
	 * Get page relations
	 * 
	 * @param string $menu_name
	 * @param int [$parent_self]
	 * @param int [$level]
	 * @param int [$max_depth]
	 * @return array
	 */
	function getPageRelations($menu_name, $parent_self = 0, $level = 0, $max_depth = 0){
		
		$results = array();
		$rdr = $this->read(" AND `_parent_self` = ".$parent_self." ");
		if((!$max_depth || ($max_depth && $level < $max_depth)) && $position_id = $this->buildMenuColection($menu_name)){
			if($menu_name == 'particular'){
				$a = 1;
			}
			while($rdr->read()){
				if(!$rdr->item('publish')) continue;
				
				$continue = true;
				foreach($rdr->item('position') as $v){
					if($v == $position_id){
						$continue = false;
							break;
					}
				}
				if($continue) continue;
				
				$rdr->setItems(array(
					'current' => isset($this->page_path_hash[$rdr->item('name')]),
					//'level' => ($level+1)
				));
				$results[] = array(
					'data' => $this->menuLinksBuilder($rdr->getItems()),
					'childs' => $this->getPageRelations($menu_name, $rdr->item('id'), $level+1, $max_depth)
				);
			}
		}
		return $results;
	}
	/**
	 * 
	 * Page path builder
	 * 
	 * @param int $id
	 * @param boolean [$cached]
	 * @access private
	 */
	protected function pagePathBuilder($id, $cached = true){
		if($cached){
			$this->getCashURLs();
			
			if(!isset($this->URLS[$id])){
				if($res = $this->read(" AND `id` = ".$id." ")->read()){
					$this->URLS[$id] = $this->pagePathBuilder((int)$res['_parent_self'], false).$res['name']."/";
				}else{
					$this->URLS[$id] = '';
				}
				$this->setCashURL($id, $this->URLS[$id]);
			}
			return $this->URLS[$id];
		}else{
			if($res = $this->read(" AND `id` = ".$id." ")->read()){
				return $this->pagePathBuilder((int)$res['_parent_self'], false).$res['name']."/";
			}
			return '';
		}
	}	
	/**
	 * 
	 * Get cache urls
	 * @access private
	 */
	protected function getCashURLs(){
		if($this->URLS === null){
			$URLS = array(); if(!@include_once(BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations/urls_".BASIC_LANGUAGE::init()->current().".php")){
				$f = fopen(BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations/urls_".BASIC_LANGUAGE::init()->current().".php", "w");
				fwrite($f, '<?php ');
				fclose($f);
			}
			$this->URLS = $URLS; unset($URLS);
		}
	}
	/**
	 * 
	 * Save urls to cache
	 * @param mixed $key
	 * @param string $url
	 * @access private
	 */
	protected function setCashURL($key, $url){
		$f = fopen(BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations/urls_".BASIC_LANGUAGE::init()->current().".php", 'a');
		fwrite($f, "\n".'$URLS["'.$key.'"] = "'.$url.'";');
		fclose($f);
	}	
	/**
	 * 
	 * Build positions list
	 * 
	 * @access private
	 * @param string $name
	 * @return int
	 */
	protected function buildMenuColection($name){
		if(!$this->positions_collection){
			$rdr = Builder::init()->getdisplayComponent("menu-positions", false)->read();
			while($rdr->read()){
				$this->positions_collection[$rdr->item('name')] = $rdr->item('id');
			}
		}
		if(!isset($this->positions_collection[$name])) return 0;
		
		return $this->positions_collection[$name];
	}
	/**
	 * 
	 * Check for cache of the menu
	 * 
	 * @param string $name
	 * @param string $template
	 * @throws Exception
	 * @return boolean
	 */
	public function checkForMenuCash($name, $template){
		$tmp_dir = BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('temporary_path')."navigations";
		if(!is_dir($tmp_dir)){
			if(!@mkdir($tmp_dir)){
				throw new Exception("Dont have permitions for create folders in temporary site container."); return;
			}
		}
		if(file_exists($tmp_dir."/".$name.".php") && (@filemtime($tmp_dir."/".$name.".php") > BASIC_TEMPLATE2::init()->getTemplateTime($template))){
			return true;
		}
		return false;
	}
	/**
	 * 
	 * Move element up or down
	 * 
	 * @param int $start
	 * @param string $code
	 * @param string $status
	 */
	function changePublishTree($start, $code, $status){
		if($status){ // up
			$res = BASIC_SQL::init()->read_exec(" SELECT `_parent_self` FROM `".$this->base."` WHERE `".$this->field_id."` = ".$start." ", true);
			if($res && $res['_parent_self']){
				BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `publish_".$code."` = 1 WHERE `".$this->field_id."` = ".$res['_parent_self']);
				$this->changePublishTree($res['_parent_self'], $code, $status);
			}
		}else{ // down
			$rdr = BASIC_SQL::init()->read_exec(" SELECT `".$this->field_id."` FROM `".$this->base."` WHERE `_parent_self` = ".$start." ");
			while($rdr->read()){
				BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `publish_".$code."` = 0 WHERE `".$this->field_id."` = ".$rdr->item('id'));
				$this->changePublishTree($rdr->item($this->field_id), $code, $status);
			}
		}
	}
	
	/**
	 * This method will be called only from cms.controlers.front.Builder.mod
	 * 
	 * @param array $criteria
	 * @see SearchBarInterface::getMatchData()
	 * @access public
	 */
	public function getMatchData($criteria){
		$res = array();
		$rdr = $this->read(" AND (".SearchBar::buildSqlCriteria(array('name', 'title', 'body'), array($criteria[0])).") ");
		if(!$rdr->num_rows()){
			unset($criteria[0]);
			$rdr = $this->read(" AND (".SearchBar::buildSqlCriteria(array('name', 'title', 'body'), $criteria).") ");
		}
		while($rdr->read()){
			$rdr->setItem('href', BASIC_URL::init()->link(BASIC::init()->ini_get('root_virtual').
				Builder::init()->pagesControler->getPageTreeById($rdr->item('id'))));
			
			$res[] = $rdr->getItems();
		}
		return $res;
	}
}