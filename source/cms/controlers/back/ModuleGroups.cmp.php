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
* @package cms.controlers.back
* @version 7.0.6  
*/

/**
 * Manager for grouping in the admin menu
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.controlers.back
 */
class ModuleGroups extends Tree{
	/**
	 * component db table - 'module_groups'
	 * @access public
	 * @var string
	 */
	var $base = 'module_groups';
	/**
	 * Field for tree title
	 * By default in tree component is 'title', here it's 'name'
	 * @access public
	 * @var string
	 */
	var $treeTitleItem = 'name';
	/**
	 * 
	 * Main function - the constructor of the component
	 * @access public
	 * @see CmsComponent::main()
	 */
	public function main(){
		$this->setField('name', array(
			'text' => BASIC_LANGUAGE::init()->get('modul_group_name_label'),
			'perm' => '*',
			'lingual' => true
		));
		parent::main();
		
		$this->ordering(true);
		
		$this->addAction('modules', 'goToParent', BASIC_LANGUAGE::init()->get('back'));
	}
	/**
	 * Extends parent method with column mapping
	 * @access public
	 * @return string html for list view
	 */
	public function ActionList(){
		$this->map('name', BASIC_LANGUAGE::init()->get('modul_group_name_label'));
		
		return parent::ActionList();
	}
	/**
	 * Extend parent class with adding fedault element 'system'
	 * @access public
	 * @return boolean
	 */
	function SQL($message = ''){
		if(parent::SQL($message)){
			return $this->createDefaultGroup();
		}
		return false;
	}
	/**
	 * Add default element for menu group 'system'
	 * 
	 * @access public
	 * @return boolean
	 */
	function createDefaultGroup(){
		$names = '';
		$values = '';
		while($lang = BASIC_LANGUAGE::init()->listing()){
			$names .= "`name_".$lang['code']."`,";
			$values .= "'system',";
		}
		$names .= "`id`";
		$values .= "1";
		
		BASIC_SQL::init()->exec(" INSERT INTO `".$this->base."` (".$names.") VALUES (".$values.") ");
		return true;
	}
	/**
	 * Extends parent method like set checkbox in list view for first row 'system' - disabled, so it cannot be removed
	 * 
	 * @access public
	 * @return void
	 */
	function rowActionsBar($row, $settings = array()){

	   	if($row['id'] == 1){
			$settings = array(
	  			'mark' => array(
	  				'disabled' => 'disabled'
	  			)//,
	  			//'ordering' => false
		   	);
	   	}
		return parent::rowActionsBar($row, $settings);
	}
	/**
	 * Remove menu groups elements
	 * 
	 * @access public
	 * @param array $ids
	 * @return boolean
	 */
	function ActionRemove($ids){
		$target = Builder::init()->build('modules', false);
		$error_ids = array();
		$this->errorAction = 'list';
		
		foreach ($ids as $k => $v){
			if($v == 1) unset($ids[$k]);
			
			if($target->read(" AND `admin_group` = ".$v." ")->num_rows()){
				$error_ids[] = $v;
				unset($ids[$k]);
			}
		}
		if($error_ids){
			$error_message = '';
			$rdr = $this->getRecords($error_ids);
			
			while($rdr->read()){
				$error_message .= ' "'.$rdr->item('name').'",';
			}
			BASIC_ERROR::init()->setWarning(BASIC_LANGUAGE::init()->get('can_not_remove_used_module_groups').substr($error_message,0,-1));
			
			return false;
		}
		return parent::ActionRemove($ids);
	}
}