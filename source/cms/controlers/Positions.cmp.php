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
* @package cms.controlers
* @version 7.0.6
*/


/**
 * @author Evgeni Baldzhiyski
 * @version 0.3
 * @since 01.08.2012
 * @package cms.controlers
 */
class Positions extends CmsComponent {
	/**
	 * 
	 * DB table name
	 * @var string
	 * @access public
	 */
	public $base = 'positions';
	/**
	 * Main function - the constructor of the component
	 * 
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();
		
		$this->setField('name', array(
			'text' => BASIC_LANGUAGE::init()->get('menu_positions_label'),
			'perm' => '*',
			'length' => 50,
			'messages' => array(
				2 => BASIC_LANGUAGE::init()->get('unique_position')
			)
		));
		$this->setField('tag', array(
			'length' => 50,
			'formtype' => 'none'
		));
		
		$this->specialTest = 'beforeSave';
		$this->model->parent = null;
		
		$this->sorting = new BasicSorting($this->field_id, false, $this->prefix);
	}
	/**
	 * Set parent action
	 * @see CmsComponent::setParentAction()
	 * 
	 */
	function setParentAction(){
		$this->addAction(($this->model->parent ? $this->model->parent->system_name : 'contents'), 'goToParent', BASIC_LANGUAGE::init()->get('back'));
	}
	
	/**
	 * Create system variables
	 * @see DysplayComponent::startManager()
	 */
	function startManager(){
		$this->setParentAction();
		
		return parent::startManager();
	}
	/**
	 * 
	 * Return the html of the listing view
	 * 
	 * @see DysplayComponent::ActionList()
	 */
	function ActionList(){
		$this->map('', ' ', null, 'width=40');
		$this->map('name', BASIC_LANGUAGE::init()->get('menu_positions_label'));
		
		return parent::ActionList();
	}
	/**
	 * 
	 * Validation method
	 * 
	 */
	function beforeSave(){
		if(!$this->id && BASIC_SQL::init()->read_exec(" SELECT 1 FROM `".$this->base."` WHERE `name` = '".$this->getDataBuffer('name')."' AND `tag` = '".$this->model->system_name."'  ", true)){
			return $this->setMessage('name', 2);
		}
		$this->setDataBuffer('tag', $this->model->system_name);
	}
	/**
	 * 
	 * Select data
	 * 
	 * @see DysplayComponent::select()
	 */
	function select($criteria = '', $include_all = false){
		$criteria = " AND `tag` = '".$this->model->system_name."' ".$criteria;
		
		return parent::select($criteria, $include_all);
	}
}