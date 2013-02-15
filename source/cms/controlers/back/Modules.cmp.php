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


/**
 * @new add new column "require_settings" for component that have method "isRequireSettings"
 * 		if this method return "true" and "cms_settings" are empty after save will be redirect to "modul-settings"
 * 		in list if user skip "modul-settings" will show warning message 
 *  
 * @author Evgeni Baldzhiyski
 * @since 10.11.2011
 * @version 1.2
 * @package cms.controlers.back
 */
class Modules extends Tree{
	public $breadcrumps = 'name';
	/**
	 * 
	 * Main function - the constructor of the component
	 * 
	 * @access public
	 * @see Tree::main()
	 */
	public function main(){
		parent::main();
		
		$this->unsetField('_parent_self');
		
		$this->base = Builder::init()->container;
	
		$this->setField('name', array(
			'text' => BASIC_LANGUAGE::init()->get('modul_name_label'),
			'perm' => '*',
			'default' => 'component',
			'filter' => 'auto'
		));
		$this->setField('class', array(
			'text' => BASIC_LANGUAGE::init()->get('modul_class_label'),
			'perm' => '*',
			'filter' => 'auto',
			'formtype' => 'browser',
			'attributes' => array(
				'resources' => array('cmp', 'cms/controlers/front', 'plugins'),
				'clean_types' => true,
				'file_types' => '.cmp.php',
				'type' => 'files'
			),
			'messages' => array(
				2 => BASIC_LANGUAGE::init()->get('module_class_not_exist')
			)
		));
		$this->setField('folder', array(
			'text' => BASIC_LANGUAGE::init()->get('modul_folder_label'),
			//'filter' => 'auto',
			'formtype' => 'hidden',
			'messages' => array(
				2 => BASIC_LANGUAGE::init()->get('module_folder_not_exist')
			)
		));
		$this->setField('public_name', array(
			'text' => BASIC_LANGUAGE::init()->get('modul_public_name_label'),
			'lingual' => true,
			'default' => 'New Component'
		));
		$this->setField('admin_support', array(
			'formtype' => 'checkbox',
			'dbtype' => 'int',
			'length' => 11,		
			'attributes' => array(
				
			)
		));
		$this->setField("admin_group", array(
			'text' => BASIC_LANGUAGE::init()->get('module_admin_group_field'),
			'formtype' => 'select',
			'dbtype' => 'int',
			'length' => 11,
			'attributes' => array(
				'data' => array()
			)
		));
		$this->setField("cmp_settings", array(
			'formtype' => 'hidden',
			'dbtype' => 'text'
		));
		$this->setField("require_settings", array(
			'formtype' => 'hidden',
			'dbtype' => 'int',
			'length' => '1',
		));
		
		$this->createSelfElement(BASIC_LANGUAGE::init()->get('parent'));
		$this->treeTitleItem = 'name';
		$this->template_form = 'module_form.tpl';
		
		$this->specialTest = 'fieldValidator';
		
		$this->addAction('module-groups', 'goToChild', BASIC_LANGUAGE::init()->get('cms_cmp_module_groups'));
	}
	/**
	 * 
	 * Return the html of the listing view
	 * 
	 * @access public
	 * @see Tree::ActionList()
	 */
	public function ActionList(){
		$this->map('name', 		    BASIC_LANGUAGE::init()->get('modul_name_label'), 'mapFormatter');
		$this->map('class', 	    BASIC_LANGUAGE::init()->get('modul_class_label'));
		$this->map('folder', 	    BASIC_LANGUAGE::init()->get('modul_folder_label'));
		$this->map('public_name',   BASIC_LANGUAGE::init()->get('modul_public_name_label'));
		$this->map('admin_support', BASIC_LANGUAGE::init()->get('module_admin_group_field'), 'mapFormatter', 'style=text-align:right');
		
		$this->filter = new BasicFilter($this->prefix, BASIC_LANGUAGE::init()->get('filter'), $this->template_filter);
		
		$this->filter->field('cmp_settings', array(
			'text' => BASIC_LANGUAGE::init()->get('module_settings'),
			'filter' => ' AND `cmp_settings` LIKE "%{v}%" '
		));
		return parent::ActionList();
	}
	/**
	 * 
	 * Action save
	 * 
	 * @see CmsComponent::ActionSave()
	 * @param int $id
	 */
	function ActionSave($id){
		$is_edit = $id ? true : false;
		
		if($this->getDataBuffer('name')){
			Builder::init()->registerComponent($this->getDataBuffer('name'), array(
				'class' => $this->getDataBuffer('class'),
				'folder' => $this->getDataBuffer('folder')
			));		
			$cmp = Builder::init()->build($this->getDataBuffer('name'));
			
			if(method_exists($cmp, 'prepareCofiguration')){
				$op = BASIC_URL::init()->other($this->dataBuffer['cmp_settings'], null, $this->cleanerDecision($this->fields['cmp_settings'][3], false, $this->fields['cmp_settings'][7]));
				$tmp = unserialize(BASIC_URL::init()->other($this->dataBuffer['cmp_settings'], null, $this->cleanerDecision($this->fields['cmp_settings'][3], $direction, $this->fields['cmp_settings'][7])));
				$tmp['prepareCofiguration'] = 1;
				
				$this->dataBuffer['cmp_settings'] = serialize(BASIC_URL::init()->other($tmp, null, $this->cleanerDecision($this->fields['cmp_settings'][3], true, $this->fields['cmp_settings'][7])));
			}
		}
		if($id = parent::ActionSave($id)){
			BASIC_CACHE::init()->open('register')->clear();
			
//			Builder::init()->registerComponent($this->getDataBuffer('name'), array(
//				'class' => $this->getDataBuffer('class'),
//				'folder' => $this->getDataBuffer('folder')
//			));
			//$cmp = Builder::init()->build($this->getDataBuffer('name'));
			if($this->getDataBuffer('name')){
				if(method_exists($cmp, 'isRequireSettings') && $cmp->isRequireSettings()){
					BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `require_settings` = 1 WHERE `id` = ".$id." ");
				}
				if(/*!$is_edit || */BASIC_SQL::init()->read_exec(" SELECT 1 FROM `".$this->base."` WHERE 1=1 AND `require_settings` = 1 AND(`cmp_settings` = '' OR `cmp_settings` = 'NILL') AND `id` = ".$id." ", true)){
					$this->goToChild($id, 'module-settings');
				}
			}
		}
		return $id;
	}
	/**
	 * 
	 * Action for ordering
	 * 
	 * @param int $id 
	 * @param string [$action] 
	 * @param string [$criteria]
	 *  
	 * @see CmsComponent::ActionOrder()
	 */
	function ActionOrder($id, $action = '', $criteria = ''){
		BASIC_CACHE::init()->open('register')->clear();
		
		return parent::ActionOrder($id, $action, $criteria);
	}
	/**
	 * 
	 * Action remove
	 * 
	 * @param int $id
	 * @param string [$rules]
	 * 
	 * @see CmsComponent::ActionRemove()
	 */
	function ActionRemove($id, $rules = ''){
		BASIC_CACHE::init()->open('register')->clear();
		
		return parent::ActionRemove($id, $rules);
	}
	/**
	 * 
	 * Created HTML form manager
	 * 
	 * @param array [$form_attribute]
	 * @access public
	 * @see DysplayComponent::FORM_MANAGER()
	 */
	public function FORM_MANAGER($form_attribute = array()){
		$this->setDataBuffer('class', $this->getDataBuffer('folder')."/".$this->getDataBuffer('class'));
				
		return parent::FORM_MANAGER($form_attribute);
	}
	/**
	 * Return HTML for the form
	 * @see Tree::ActionFormAdd()
	 * @access public
	 */
	public function ActionFormAdd(){
		$this->generateScript();
		return parent::ActionFormAdd();
	}
	/**
	 * Action form edit
	 * 
	 * @param int $id
	 * @see Tree::ActionFormEdit()
	 * @access public
	 */
	public function ActionFormEdit($id){
		$this->generateScript();
		return parent::ActionFormEdit($id);
	}
	/**
	 * 
	 * Format the names of the columns
	 * 
	 * @param string $value
	 * @param string $cname
	 * @param array [$row]
	 * @access private
	 */
	protected  function mapFormatter($value, $cname, $row = array()){
		if($cname == 'name'){
			if($row['require_settings'] && !$row['cmp_settings']){
				$value .= '<small> ('.BASIC_LANGUAGE::init()->get('need_set_required_settings').')</small>';
			}
		}
		if($cname == 'admin_support'){
			return BASIC_LANGUAGE::init()->get((int)$value ? 'yes' : 'no');
		}
		return $value;
	}
	/**
	 * 
	 * @todo description
	 * 
	 */
	function fieldValidator(){
//		if(!is_dir(BASIC::init()->ini_get('root_path').$this->getDataBuffer('folder'))){
//			$this->setMessage('folder', 2);
//		}
		if(!is_file(BASIC::init()->ini_get('root_path').$this->getDataBuffer('class').".cmp.php")){
			$this->setMessage('class', 2);
		}else{
			$path = explode("/", $this->getDataBuffer('class'));
			$count = count($path);
			
			$this->setDataBuffer("class", $path[$count-1]);
			
			unset($path[$count-1]);
			$this->setDataBuffer("folder", implode("/", $path));
		}
		//$this->unsetDataBuffer('cmp_settings');
	}
	/**
	 * Generate component assign list
	 * 
	 * @param array [$before] $miss
	 * @return hashmap
	 */
	function genesateAssignList($before = null){
		$res = $this->read(" AND `_parent_self` = 0")->getSelectData('name', 'public_name', $before);
		
		return $res;
	}
	
	/**
	 * 
	 * Add a default javascript code
	 * 
	 */
	function generateScript(){
		$this->updateField('admin_group', array(
			'attributes' => array(
				'data' => Builder::init()->build('module-groups', false)->getSelTree('',0, 'id', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
			)
		));
		
		BASIC_GENERATOR::init()->head(null, 'script', null, "
$(document).ready(function (){
	init = false;
	
	$('#admin_group').change(function(){
		$('#_parent_self')[this.value ? 'attr' : 'removeAttr']('disabled', 'true');
	}).change();
	
	$('#_parent_self').change(function(){
		var g = $('#admin_group'), s = $('#admin_support');
		if(this.value){
			g.attr('disabled', 'true');
		}else{
			if(s.get(0).checked) g.removeAttr('disabled');
		}
		s[this.value ? 'attr' : 'removeAttr']('disabled', 'true');
	}).change();
	
	$('#admin_support').click(function(){
		$('#admin_group')[!this.checked ? 'attr' : 'removeAttr']('disabled', 'true');
		return init;
	}).click();
	init = true;
});");
	}
}