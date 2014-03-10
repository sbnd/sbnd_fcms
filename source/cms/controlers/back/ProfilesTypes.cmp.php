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
 * User access level support.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.3
 * @package cms.controlers.back
 */
class ProfilesTypes extends CmsComponent {
	
	/**
	 * 
	 * Database table name
	 * @var string
	 * @access public
	 */
	public $base = 'profiles_types';
	/**
	 * 
	 * Current data level
	 * @var array
	 * @access private
	 */
	protected $current_data = array();
	
	/**
	 * Main function - the constructor of the component
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();
		
		$this->setField('title', array(
			'text' => BASIC_LANGUAGE::init()->get('profiles_role'),
			'lingual' => true,
			'perm' => '*'
		));
		$this->ordering(BASIC_USERS::init()->level() == 1 || BASIC_USERS::init()->level() == -1 ? true : false);
		$this->sorting = new BasicSorting('order_id', false, $this->prefix);
		
		$this->addAction('profiles', 'goToParent', BASIC_LANGUAGE::init()->get('back'));
		
		$this->model->parent = null;
		$this->system[] = '_parent_id';
	}
	/**
	 * 
	 * This function will return the actual html of the component
	 * 
	 * @see CmsComponent::startPanel()
	 */
	function startPanel(){
		$this->startManager();
		
		$this->map('', '', null, 'width=10');
		$this->map('title', BASIC_LANGUAGE::init()->get('profiles_role'));
		
		if($this->id){
			$this->current_data = $this->getRecord($this->id);
			
			if($this->id != -2 && $this->current_data['order_id']+1 <= BASIC_USERS::init()->level()){
				$this->delAction('save');
			}
		}
		return $this->createInterface();
	}
	/**
	 * 
	 * Select data
	 * 
	 * @param string [$criteria]
	 * @param boolean [$include_all]
	 * @see DysplayComponent::select()
	 */
	function select($criteria = '', $include_all = false){
		return parent::select(" AND `id` <> '-1' ".$criteria, $include_all);
	}
	/**
	 * 
	 * Insert the user level data to database  
	 * 
	 * @see DysplayComponent::SQL()
	 * @return boolean
	 */
	function SQL($message = ''){
		if(parent::SQL($message)){
			$names = '';
			
			$values_gest = '';
			$values_admin = '';
			while($lang = BASIC_LANGUAGE::init()->listing()){
				$names .= "`title_".$lang['code']."`,";
				
				$values_gest .= "'Gest',";
				$values_admin .= "'Admin',";
			}
			$names .= "`id`";
			
			$values_gest .= "-2";
			$values_admin .= "1";
			
			BASIC_SQL::init()->exec(" INSERT INTO `".$this->base."` (".$names.") VALUES (".$values_gest.") ");
			BASIC_SQL::init()->exec(" INSERT INTO `".$this->base."` (".$names.") VALUES (".$values_admin.") ");
			return true;
		}
		return false;
	}
	/**
	 * 
	 * Set row actions bar
	 * 
	 * @param array $row
	 * @param array [$settings]
	 * @see DysplayComponent::rowActionsBar()
	 */
	function rowActionsBar($row, $settings = array()){
	   	if($row['id'] == 1 || $row['id'] == -2 || $row['id'] <= BASIC_USERS::init()->level()){
			$settings = array(
	  			'mark' => array(
	  				'disabled' => 'disabled'
	  			),
	  			'ordering' => false
		   	);
	   	}
		return parent::rowActionsBar($row, $settings);
	}
	/**
	 * 
	 * Form edit action
	 * 
	 * @param int $id
	 * @see CmsComponent::ActionFormEdit()
	 */
	function ActionFormEdit($id){
		if($this->id && $this->id != -2 && $this->current_data['order_id']+1 <= BASIC_USERS::init()->level()){
			$this->updateField('title', array(
				'attributes' => array(
					'disabled' => true
				)
			));
		}
		return parent::ActionFormEdit($id);
	}
	/**
	 * Action remove
	 * 
	 * @param array $ids
	 * @see CmsComponent::ActionRemove()
	 */
	function ActionRemove($ids){
		$accounts = Builder::init()->build('profiles', false);
		$error_ids = array();
		$this->errorAction = 'list';
		
		foreach ($ids as $k => $v){
			if($v == 1) unset($ids[$k]);
			
			if($accounts->read(" AND `level` = ".$v." ")->num_rows()){
				$error_ids[] = $v;
				unset($ids[$k]);
			}
		}
		if($error_ids){
			$error_message = '';
			$rdr = $this->getRecords($error_ids);
			
			while($rdr->read()){
				$error_message .= ' "'.$rdr->item('title').'",';
			}
			BASIC_ERROR::init()->setWarning(BASIC_LANGUAGE::init()->get('can_not_remove_used_account_types').substr($error_message,0,-1));
			
			return false;
		}
		return parent::ActionRemove($ids);
	}
}