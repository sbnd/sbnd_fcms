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
 * 
 * Module settings interface
 * 
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @since 01.01.2012
 * @package cms.controlers.back
 */
interface ModuleSettingsInterface{
	/**
	 * Use from ActionLoad for load default settings data. If "module_settings" column is not empty will ignore this method.
	 * 
	 *  @return hashMap
	 */
	function settingsData();
	/**
	 * Use from action add on "ModuleSettings" for make settings form.
	 * 
	 * @return nashMap
	 */
	function settingsUI();
	/**
	 * For last formating before saveing on the db's table.
	 * 
	 * @param hashmap $data
	 * @return hashmap
	 */
	function settingsFormat($data);
//	/**
//	 * if need to use require settings UI 
//	 * 
//	 * @return boolean
//	 */
//	function isRequireSettings();
}
/**
 * Component's settings manager. 
 *  
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @since 01.01.2012
 * @package cms.controlers.back
 */
class ModuleSettings extends CmsComponent {
	/**
	 * @var CmsComponent
	 * @access private
	 */
	private $parent_build = null;
	/**
	 * @var CmsComponent
	 * @access private
	 */
	private $module_build = null;
	/**
	 * @var unknown_type
	 * @access private
	 */
	private $module_settings = array();
	
	/**
	 * Main function - the constructor of the component
	 * 
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();
	
		$this->updateAction('list', 'ActionFormEdit');
		$this->updateAction('cancel', 'goToParent');
	}
	/**
	 * Check request for action's variables.
	 *  
	 * @see CmsComponent::loadURLActions()
	 */
	function loadURLActions(){
		parent::loadURLActions();
		
		$this->parent_build = $this->buildParent();
		
		$res = $this->parent_build->getRecord($this->parent_id);
		
		if(!isset(Builder::init()->model[$res['name']])){
			Builder::init()->registerComponent('#'.$res['name'], array(
				'class' => $res['class'],
				'folder' => $res['folder']
			));
		}
		$this->module_build = Builder::init()->build($res['name'], false);
		$this->module_settings = $res['cmp_settings'];
		
		$fields = array();
		if(method_exists($this->module_build, 'settingsUI')){
			$fields = $this->module_build->settingsUI();
		}else{
			$fields['standart'] = array(
				'text' => BASIC_LANGUAGE::init()->get('modul_cmp_settings_label'),
				'formtype' => 'selectmanage',
				'dbtype' => 'text',
				'attributes' => array(
					//'add' => BASIC_LANGUAGE::init()->get('modul_add_label'),
					"del" => BASIC_LANGUAGE::init()->get('modul_del_label'),
					'data' => array(BASIC_LANGUAGE::init()->get('modul_aname_label'), BASIC_LANGUAGE::init()->get('modul_avalue_label'))
				)
			);
		}
		if(!$fields){
			$this->delAction('save');
		}else{
			foreach($fields as $k => $v){
				$this->setField($k, $v);
			}
		}
		$this->id = 1;
	}
	/**
	 * Action Save
	 * @see CmsComponent::ActionSave()
	 */
	function ActionSave(){
		unset($this->dataBuffer['_parent_id']);
		if(isset($this->dataBuffer['standart'])){
			$str = $this->dataBuffer['standart']; unset($this->dataBuffer['standart']);
			
			foreach($str as $v){
				$spl = explode(",", $v);
				
				$spl[1] = (isset($spl[1]) ? $spl[1] : '');
				
//				if($this->module_build->$spl[0] != $spl[1]){
					$this->dataBuffer[$spl[0]] = $spl[1];
//				}
			}
		}
		if(method_exists($this->module_build, 'settingsFormat')){
			$this->dataBuffer = $this->module_build->settingsFormat($this->dataBuffer);
		}
		if(method_exists($this->module_build, 'prepareCofiguration')){
			$this->dataBuffer['prepareCofiguration'] = 1;
		}
//		foreach($this->dataBuffer as $k => $v){
//			if($this->module_build->$k == $v){
//				unset($this->dataBuffer[$k]);
//			}
//		}
		
		$this->parent_build->autoTest = false;
		$this->parent_build->setDataBuffer('cmp_settings', serialize($this->dataBuffer));
		$this->parent_build->ActionSave($this->parent_id);
		
		$this->goToParent();
	}
	/**
	 * 
	 * Load data in edit form
	 * 
	 * @param int $id
	 * @see CmsComponent::ActionLoad()
	 */
	function ActionLoad(){
		if($this->module_settings){
			if(isset($this->fields['standart'])){
				$data = array();
				foreach(unserialize($this->module_settings) as $k => $v){
					$data[] = $k.','.$v;
				}
				$this->dataBuffer['standart'] = $data;
			}else{
				$this->dataBuffer = unserialize($this->module_settings);
			}
		}else{
			if(method_exists($this->module_build, 'settingsData')){
				$this->dataBuffer = $this->module_build->settingsData();
			}
			if(!$this->dataBuffer){
				
				$sett = array('prefix,'.$this->module_build->prefix);
				if(isset($this->module_build->template_form)){
					$sett[] = 'template_form,'.$this->module_build->template_form;
				}
				if(isset($this->module_build->template_list)){
					$sett[] = 'template_list,'.$this->module_build->template_list;
				}
				if(isset($this->module_build->useSaveState)){
					$sett[] = 'useSaveState,'.$this->module_build->useSaveState;
				}
				if(isset($this->module_build->errorAction)){
					$sett[] = 'errorAction,'.$this->module_build->errorAction;
				}
				
				$this->dataBuffer['standart'] = $sett;
			}
		}
	}
}