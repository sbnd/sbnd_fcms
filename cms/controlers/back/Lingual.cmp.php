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
 * Lingual manager
 * 
 * @author Evgeni Baldzhiyski
 * @since 05.05.2011
 * @version 0.2
 * @package cms.controlers.back
 */
class Lingual extends CmsComponent {
	/**
	 * Container with all language codes
	 * @access private
	 * @var array
	 */
	protected $langCode = array();
	/**
	 * 
	 * Main function - the constructor of the component
	 * @access public
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();
		
		$this->base = BASIC_LANGUAGE::init()->container;
		
		$this->setField('variable',array(
			'text' => BASIC_LANGUAGE::init()->get('langual_variable_labe'),
			'perm' => '*',
			'filter' => 'auto'
		));
		$this->setField('value', array(
			'text' => BASIC_LANGUAGE::init()->get('lingual_value_label'),
			'dbtype' => 'varchar',
			'length' => 500,
			'filter' => 'auto',
			'attributes'=>array(
				'maxlength' => '500'
			)
		));
		$this->sorting = new BasicSorting('variable', false, $this->prefix);
	}
	/**
	 * Getter for langCode property
	 * 
	 * @access public
	 * @return array
	 */
	function getLangCode(){
		if(!$this->langCode){
			$arr = Builder::init()->build('languages', false)->getRecord($this->parent_id);
			if($arr){
				$this->langCode = $arr['code'];
			}
		}
		return $this->langCode;
	}
	/**
	 * Setter for langCode property
	 * 
	 * @access public
	 * @param string $code
	 */
	function setLangCode($code){
		$this->langCode = $code;
	}
	/**
	 * Extends parent method with mapping columns in list view
	 * @access public
	 * @return string html for list view
	 */
	function ActionList(){
		$this->startManager();
		
		$this->map('variable', 	BASIC_LANGUAGE::init()->get('langual_variable_labe'));
		$this->map('value', 	BASIC_LANGUAGE::init()->get('lingual_value_label'));
		
		return parent::ActionList();
	}
	/**
	 * Save data in db
	 * @access public
	 * @return boolean|integer id of the record or false
	 */
	function ActionSave($id = 0){
		if(!$this->messages){
			if($id){
				BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET 
					".($this->getDataBuffer('variable') ? "`variable` = '".$this->getDataBuffer('variable')."'," : "")."
					`value_".$this->getLangCode()."` = '".$this->getDataBuffer('value')."'
				WHERE 1=1
					AND `id` = ".$id."
				");
				return $id;
			}else{
				BASIC_SQL::init()->exec(" INSERT INTO `".$this->base."` (
					`value_".$this->getLangCode()."`, `variable` 
				)VALUES(
					'".$this->getDataBuffer('value')."', '".$this->getDataBuffer('variable')."'
				)");
				return BASIC_SQL::init()->getLastId();
			}
		}
		return false;
	}
	/**
	 * Get data from component db table
	 * 
	 * @access public
	 * @return string sql query that get data from component table
	 */
	function select($criteria){
		return " SELECT `id`, `variable`, `value_".$this->getLangCode()."` AS `value` FROM `".$this->base."` WHERE 1=1 ".
			preg_replace("/AND `".$this->base."`.`_parent_id` = [^ ]+ /", "", str_replace("`value`", "`value_".$this->getLangCode()."`", $criteria));
	}
	/**
	 * @access public
	 * @return void
	 */
	function ActionBack(){
		$this->system[] = '_parent_id';
		parent::ActionBack();
	}
}