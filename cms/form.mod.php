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
* @package cms.form
* @version 7.0.4
*/

BASIC::init()->imported('form.mod');
/**
 * Public interface for display componet that not show actions UI.
 * Used for make front end component pages.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.form
 */
interface CmsDysplayComponent{
	/**
	 * Builder will run this method when build the component.
	 * 
	 * @return void
	 */
	function main();
	/**
	 * The returned data will put in the template.
	 * 
	 * @return string
	 */
	function startPanel();
}
/**
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.form
 */
interface CmsActionDisplayComponent{
	/**
	 * Get component's actions list. Will get and action list in list.
	 * 
	 * @return hashmap
	 */
	function getActions();
	/**
	 * Check request for action's variables.
	 */
	function loadURLActions();
	/**
	 * Add child's actions.
	 */
	function setChildActions();
	/**
	 * Add parent action
	 */
	function setParentAction();
}
/**
 * 
 * Set component display data 
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @since 29.10.2011
 * @package cms.form
 */
class CmsBox implements CmsDysplayComponent{
	/**
	 * Add cms permission support.
	 * 
	 * @var hashmap
	 * @access public
	 */
	public $actions = array(
		// Default Call Action
		'list' => array('ActionList', 0, 'List')
	);
	/**
	 * Component permissions
	 * 
	 * @var hashmap
	 * @access private
	 */
	protected $cmp_perms = array(
		'list' => 'List'
	);
	/**
	 * @var String
	 * @access public
	 */
	public $prefix = '';
	/**
	 * @var String
	 * @access public
	 */
	public $public_name = '';
	/**
	 * @var RegisterObject
	 * @access public
	 */
	public $model = null;
	/**
	 * @var Boolean
	 * @access public
	 */
	public $secure = true;
	/**
	 * Main function
	 * 
	 * @return void
	 */
	public function main(){
		$actions["list"][2] = $this->cmp_perms["list"] = BASIC_LANGUAGE::init()->get('list');
	}
	/**
	 * 
	 * @return String
	 */
	public function startPanel(){
		
		return 'Cms Box';
	}
	/**
	 * 
	 * Get component permissions
	 * 
	 */
	function getCmpPermitions(){
		return $this->cmp_perms;
	}	
}
/**
 * @author Evgeni Baldziyski
 * @version 1.0
 * @since 14.03.2008
 * @package cms.form
 * 
 * optional methods:
 * 	
 * 	isRequireSettings() 	- if have to after registrate to open settings
 * 	settingsUI() 			- configure settings form interface
 * 	settingsData() 			- default values for components settings
 * 	settingsFormat(hashmap) - before save convert values
 * 	
 * 	static prepareCofiguration() - will exec this method after add to register 
 */
class CmsComponent extends DysplayComponent implements CmsDysplayComponent, CmsActionDisplayComponent{
	/**
	 * 
	 * @var hashmap
	 * @access public
	 */
	public $globalCleaner = array(
		'int' 		=> array('Int'),
		'text'		=> array('htmlSecurity', 'cleanHTMLT'),	
		'char'	 	=> array('charAdd', 'charStrip'),
		'float' 	=> array('Float'),
		'varchar' 	=> array('charAdd', 'charStrip'),
		'longtext'	=> array('htmlSecurity', 'cleanHTMLT')
	);
	/**
	 * Collection from component's specific permissions
	 * 
	 * @var $cmp_perms HashMap(
	 * 		name - string : system permition's name
	 * 		text - string : public text for permitions interface
	 * )
	 * @access private
	 */
	protected $cmp_perms = array(
		'list' 	 => 'List',
		'add'  	 => 'Add',
		'edit' 	 => 'Edit',
		'delete' => 'Delete'
	);
	/**
	 * @var Boolean
	 */
	var $secure = true;
	/**
	 * @var RegisterObject
	 */
	var $model = null;
	/**
	 * Register parent component
	 *
	 * @var RegisterObject
	 */
	var $parent = null;
	/**
	 * Reference to parent compile object
	 *
	 * @var DysplayComponent
	 */
	var $parent_obj = null;
	/**
	 * Collection register child components
	 * @TODO deprecated / LAST CMS VERSION FOR THIS IS 7.1.0
	 *
	 * @var array
	 */
	var $child = array();
	/**
	 * url variable name for get component name
	 *
	 * @var string
	 */
	var $nameUrlVar = 'cmp';
	/**
	 * Url variable name for get parent id
	 *
	 * @var string
	 */
	var $nameUrlVarId = 'parent_id';
	/**
	 * log parent component's id
	 *
	 * @var unknown_type
	 */
	var $parent_id = 0;
	/**
	 * Component builder.
	 *
	 * @var BuilderComponent
	 */
	var $componentBuilder = null;

	var $system_prefix = '';
	
	var $treeTitleItem = 'title';
	/**
	 * Third party data. Ex: the pages will set this own data here when this component is assigned to its.
	 * 
	 * @var Array
	 * @access public
	 */
	public $pdata = array();
	/**
	 * Flag for run component in special mode. The valid values are:
	 * 	none - build standart html
	 *  simple - build standart html witout buttons bars
	 *  json - build json object 
	 * 
	 * @var Boolean
	 * @todo in v.7.0.0 support only value "none"
	 * @access public
	 */
	public $ajaxMode = 'none';
	/**
	 * override
	 * @access public
	 */
	public $maxrow = -1;
	/**
	 * Start method. use CMS_SETTINGS's 'list_max_rows'
	 *
	 * @return void
	 */
	function main(){
		$this->updateAction("add", 		null, BASIC_LANGUAGE::init()->get('add'));
		$this->updateAction("edit",		null, BASIC_LANGUAGE::init()->get('edit'));
		$this->updateAction("delete",	null, BASIC_LANGUAGE::init()->get('delete'), 1, BASIC_LANGUAGE::init()->get('are_you_sure'));
		$this->updateAction("list",		null, BASIC_LANGUAGE::init()->get('list'));
		
		$this->updateAction("cancel",	null, BASIC_LANGUAGE::init()->get('back'));	
		$this->updateAction("save",		null, BASIC_LANGUAGE::init()->get('save'));
		$this->updateAction("filter",	null, BASIC_LANGUAGE::init()->get('filter'));
		
		if($this->maxrow == -1) $this->maxrow = (int)CMS_SETTINGS::init()->get('list_max_rows');
		
		if(isset($this->cmp_perms['list'])) 	$this->cmp_perms['list']   = BASIC_LANGUAGE::init()->get('list');
		if(isset($this->cmp_perms['add'])) 		$this->cmp_perms['add']    = BASIC_LANGUAGE::init()->get('add');
		if(isset($this->cmp_perms['edit'])) 	$this->cmp_perms['edit']   = BASIC_LANGUAGE::init()->get('edit');
		if(isset($this->cmp_perms['delete'])) 	$this->cmp_perms['delete'] = BASIC_LANGUAGE::init()->get('delete');
	}
	/**
	 * Registrate local permission for control show component's contents and elements
	 * 
	 * @param string $name
	 * @param string $text
	 */
	function setCmpPermition($name, $text){
		$this->cmp_perms[$name] = $text;
	}
	/**
	 * Unset permission for component
	 * @param string $name
	 */
	function unsetCmpPermition($name){
		unset($this->cmp_perms[$name]);
	}
	/**
	 * Get permission by name
	 *  
	 * @param string $name
	 * @return string
	 */
	function getCmpPermition($name){
		return (isset($this->cmp_perms[$name]) ? $this->cmp_perms[$name] : null);
	}
	/**
	 * 
	 * Get all permissions for the component
	 * @return array
	 */
	function getCmpPermitions(){
		return $this->cmp_perms;
	}
	/**
	 * 
	 * Method for delete action
	 * 
	 * @see DysplayComponent
	 * @access public
	 */
	public function delAction($action){
		if($action != 'list' && isset($this->cmp_perms[$action])){
			unset($this->cmp_perms[$action]);
		}
		parent::delAction($action);
	}
	/**
	 * Get list with options for control max rows per page in list component's interfaces.
	 * 
	 * @return array
	 */
	function getMaxRowsOptions(){
		return array(
			'10' => '10',
			'20' => '20',
			'50' => '50',
			'100' => '100',
			'-1' => BASIC_LANGUAGE::init()->get('all')
		);
	}
	/**
	 * 
	 * @see CmsDysplayComponent::startPanel()
	 * @return string
	 */
	function startPanel(){
		$this->startManager();
		
		return $this->createInterface();
	}
	/**
	 * Function to set field in component
	 * @param string $name field system name
	 * @param array $context field type and data (int, dbtype)
	 * @param string $after for first set 'first' otherwise set the name of the field after which this field must be set 
	 * @see DysplayComponent::setField()
	 */
	function setField($name, $context = array(), $after = ''){
		if(!isset($context['messages'])) $context['messages'] = array();

		if(!isset($context['messages'][0])) $context['messages'][0] = '';
		if(!isset($context['messages'][1])){
			$context['messages'][1] = BASIC_LANGUAGE::init()->get(isset($context['lingual']) && BASIC_LANGUAGE::init()->number() > 1 ? 'is_required_multy_lingual' : 'is_required');
		}
		
		parent::setField($name, $context, $after);
	}
	/**
	 * Set child actions
	 * @see CmsActionDisplayComponent::setChildActions()
	 */
	function setChildActions(){
		foreach ($this->model->child as $obj){
			if($obj->type == 'system') continue;
			
			$this->addAction($obj->prefix.$obj->system_name, 'goToChild', $obj->public_name, 2);
		}
	}
	/**
	 * Set parent action
	 * @see CmsActionDisplayComponent::setParentAction()
	 */
	function setParentAction(){
		if($this->model->parent){
			$this->addAction($this->model->parent->system_name, 'goToParent', $this->model->parent->public_name, 1);
		}
	}
	/**
	 * 
	 * Get parent object
	 * 
	 * @param object $obj
	 * @access private
	 */
	protected function goToParentTop($obj){
		if($obj->parent){
			return $this->goToParentTop($obj->parent);
		}
		return $obj;
	}
	/**
	 * 
	 * Go to child
	 * 
	 * @version 0.1
	 * @param int $id
	 * @param string $action
	 */
	function goToChild($id, $action){
		$top = $this->goToParentTop($this->model);
		
		$add = array(
			$this->nameUrlVar => $action.":".$top->system_name
		);
		if($id){
			$add[$this->prefix.$this->nameUrlVarId] = $id;
		}
		
		foreach ($this->fields as $k => $v){
			if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$v[0]])){
				foreach(BASIC_LANGUAGE::init()->language as $lk => $l) $this->miss[] = $this->prefix.$k.'_'.$lk;
			}
			if(isset($v[0])) $this->miss[] = $this->prefix.$k;
		}
		$this->miss[] = $this->prefix.$this->urlCmdName;
		$this->miss[] = $this->prefix.$this->urlCmdName.$action;
		$this->miss[] = $this->prefix.'id';
		$this->miss[] = $this->nameUrlVar;
		
		BASIC_URL::init()->redirect(BASIC::init()->scriptName(), BASIC_URL::init()->serialize($this->miss).BASIC_URL::init()->userSerialize($add));
	}

	/**
	 * Redirection to the parent component.
	 * 
	 * @version 0.3
	 * @param int $id
	 * @param string $action
	 */
	function goToParent($id = 0, $action = ''){
		if($this->sorting){
			$this->system[] = $this->sorting->prefix.BasicSorting::$desk_var_name;
			$this->system[] = $this->sorting->prefix.BasicSorting::$column_var_name;
		}
		if($this->paging){
			$this->system[] = $this->paging->prefix.$this->paging->page_url_var;
		}
		$this->system[] = $this->nameUrlVar;
		
		$top = '';
		if($this->model->parent){
			$this->system[] = $this->model->parent->prefix.$this->nameUrlVarId;
			
			if($this->model->parent->parent){
				$top  = $this->goToParentTop($this->model->parent);
			}
		}
		
		$url_vars = BASIC_URL::init()->serialize($this->system);
		if(!$this->pdata || ($this->pdata && $this->model->parent && $this->model->parent->parent)){
			
			$url_vars .= BASIC_URL::init()->userSerialize(array(
				$this->nameUrlVar => ($this->model->parent ? 
					($this->model->parent->prefix != $this->model->parent->system_prefix ? $this->model->parent->prefix : '').$this->model->parent->system_name.($top ? ":".$top->system_name : '') :
				$action.($top ? ":".$top->system_name : '')
			)));
		}
		BASIC_URL::init()->redirect(BASIC::init()->scriptName(), $url_vars);
	}
	/**
	 * Redirection to the specific component.
	 * 
	 * @param string $name
	 */
	function goToComponent($name){
		$this->goToParent(0, $name);
	}
	/**
	 * This function set the buffer from submitted form use this when you must do some validation
	 * @see DysplayComponent::test()
	 */
	function test(){
		if(!$this->autoTest) return false;
		
		$tmp = $this->specialTest; $this->specialTest = '';
		
		parent::test();
		$this->specialTest = $tmp;
		
		if($this->model->parent){
			$this->setDataBuffer('_parent_id', $this->parent_id);
		}
		if($this->specialTest != ''){
			if(is_array($this->specialTest)){
				$obj = &$this->specialTest[0];
				$method = $this->specialTest[1];
				$err = false;
				if($obj != null){
					$err = $obj->$method();
				}else{
					$err = $method();
				}
			}else{
				$special = $this->specialTest;
				$err = $this->$special();
			}
			if($err && !$this->messages){
			    $this->messages = array(-1);
			}
		}
		return ($this->messages ? true : false);
	}
	
	// ***
	
	/**
	 * Check request for action's variables.
	 * @see DysplayComponent::loadURLActions()
	 */
	function loadURLActions(){
		if(!$this->parent_id && $this->model->parent){
			$this->parent_id = (int)BASIC_URL::init()->request($this->model->parent->prefix.$this->nameUrlVarId);
		}
		parent::loadURLActions();
	}
	/**
	 * Function to load the form
	 * @see DysplayComponent::ActionLoad()
	 * @param int $id
	 */
	function ActionLoad($id = 0){
		parent::ActionLoad($id);
		
		if($this->model->parent){
			$this->setDataBuffer('_parent_id', $this->parent_id);
			$this->unsetField('_parent_id');
		}
	}
	/**
	 * 
	 * Form edit action
	 * @see DysplayComponent::ActionFormEdit()
	 * @param int $id
	 */
	function ActionFormEdit($id){
		//if(!$this->messages)
			$this->updateAction("save",	null, BASIC_LANGUAGE::init()->get('update'));
		
		return parent::ActionFormEdit($id);
	}
	/**
	 * On submitting the form
	 * @see DysplayComponent::ActionSave()
	 * @param int $id
	 */
	function ActionSave($id = 0){
		if($this->model->parent){
			if(
				(!$id && $this->parent_id) || 
				($id && BASIC_SQL::init()->read_exec(" SELECT 1 FROM `".$this->base."` WHERE `_parent_id` = ".$this->parent_id." AND `".$this->field_id."` = ".$id." ", true))
			){
				$this->setDataBuffer('_parent_id', $this->parent_id);
				return parent::ActionSave($id);
			}else{
				return false;
			}
		}
		return parent::ActionSave($id);
	}
	/**
	 * Function to delete action handler
	 * @see BaseDisplayComponentClass::ActionRemove()
	 * @param int $id
	 * @param string $rules
	 */
	function ActionRemove($id, $rules = ''){
		if($id){
			$this->secure = false;
			if(!is_array($id)) $id = array($id);
			
			/**
			 * parent_id secure
			 */
			if($this->model->parent){
				$rdr = BASIC_SQL::init()->read_exec(" SELECT `id` FROM `".$this->base."` WHERE 1=1
					AND `_parent_id` ".(is_array($this->parent_id) ? " IN (".implode(",", $this->parent_id).",0)" : " = ".$this->parent_id." ")." 
					AND `id` IN (".implode(",", $id).",0) ");
				$id = array(); while ($rdr->read()){
					$id[] = $rdr->item('id');
				}
			}
			
			if($id){
				if($this->getField('_parent_self')){
					$tmp_arr = array();
					$rdr = BASIC_SQL::init()->read_exec(" SELECT `id` FROM `".$this->base."` WHERE `_parent_self` IN (".implode(",", $id).") ");
					while($rdr->read()){
						$tmp_arr[] = $rdr->item('id');
					}
					if($tmp_arr){
						$this->ActionRemove($tmp_arr, $rules);
					}
				}
				$this->id = $id;
				foreach($this->model->child as $obj){
					$cmp = $this->buildChild($obj->system_name);
			
					if($cmp->base && $cmp->parent){
						$tmp_arr = array();
						
						$rdr = $cmp->read();
						
						while($rdr->read()){
							$tmp_arr[] = $rdr->item('id');	
						}
						$cmp->ActionRemove($tmp_arr);
					}
				}
				parent::ActionRemove($id, $rules);
			}
		}
	}
	/**
	 * 
	 * Funtion to get ultimate parent recursively
	 * @param int $id
	 * @param int $check_id
	 * @access private
	 */
	private function checkTreeActionOrder($id, $check_id){
		if($id == $check_id) return true;
		
		$res = BASIC_SQL::init()->read_exec(" SELECT `id`, `_parent_self` FROM `".$this->base."` WHERE `id` = ".$id." ", true);
		
		if($res && $res['_parent_self'] != 0){
			return $this->checkTreeActionOrder($res['_parent_self'], $check_id);	
		}
		return false;
	}
	/**
	 * 
	 * Function for ordering the elements
	 * 
	 * @see DysplayComponent::ActionOrder()
	 * @param int $id
	 * @param string $action
	 * @param string $criteria
	 */
	function ActionOrder($id, $action = '', $criteria = ''){
		$sql = BASIC_SQL::init();
		if(!$id) return;
			
		if(is_array($id)){
			$prev = -1;
			
			$allow_tree = isset($this->fields['_parent_self']) ? true : false;
			$parent_id = -1;
			$update = '';
			
			$move_res = BASIC_SQL::init()->read_exec(" SELECT `order_id` FROM `".$this->base."` WHERE `id` = ".(int)$id[1]." ", true);
			
			if($id[0]){ // child
				if($allow_tree){
					$rdr = BASIC_SQL::init()->read_exec(" SELECT `_parent_self`, `order_id` FROM `".$this->base."` WHERE `_parent_self` = ".(int)$id[0]." ORDER BY `order_id` DESC ");
					while($rdr->read()){
						$prev = $rdr->item('order_id');
						$parent_id = $rdr->item('_parent_self');
						
						$update = " UPDATE `".$this->base."` SET `_parent_self` = ".$rdr->item('_parent_self')." WHERE `id` = ".(int)$id[1]." ";
						break;
					}
				}else{
					return;
				}
			}else{ // just order
				if($id[1] == $id[2]) return;
				
				$res = array();
				if((int)$id[2]){
					if($res = BASIC_SQL::init()->read_exec(" SELECT ".($allow_tree ? '`_parent_self`' : '-1' )." as `_parent_self`, `order_id` FROM `".$this->base."` WHERE `id` = ".(int)$id[2]." ", true)){
						if($allow_tree){
							$parent_id = $res['_parent_self'];
							$update = " UPDATE `".$this->base."` SET `_parent_self` = ".$parent_id." WHERE `id` = ".(int)$id[1]." ";
						}
					}
				}else{
					$res = BASIC_SQL::init()->read_exec(" SELECT max(`order_id`)+1 as `order_id` FROM `".$this->base."` ", true);
				}
				$prev = $res['order_id'];
			}
			if($update){
				if(!$this->checkTreeActionOrder($parent_id, (int)$id[1])){
					BASIC_SQL::init()->exec($update);
				}else{
					//@TODO need ass same error here
					return;
				}
			}
			if($prev > -1){
				$query = '';
				if($move_res['order_id'] > $prev){
					BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `order_id` = (`order_id` + 1) WHERE 1=1
						AND `order_id` < ".$move_res['order_id']." 
						AND `order_id` >= ".$prev." "
					);
					BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `order_id` = ".$prev." WHERE `id` = ".(int)$id[1]." ");
				}else{
					BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `order_id` = (`order_id` - 1) WHERE 1=1
						AND `order_id` > ".$move_res['order_id']." 
						AND `order_id` < ".$prev." "
					);
					BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `order_id` = ".($prev-1)." WHERE `id` = ".(int)$id[1]." ");
				}
			}else{
				BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `_parent_self` = ".(int)$id[0]." WHERE `id` = ".(int)$id[1]." ");
			}
		}else{
			// FIX for tree structure
			if(isset($this->fields['_parent_self'])){
				$self_id = $sql->read_exec(" SELECT `_parent_self` FROM `".$this->base."` WHERE 1=1 AND `".$this->field_id."` = ".(int)$id." ",true);
				$sql->exec(" SET @parent_self = ".(int)$self_id['_parent_self']." ");
			}
			
			$sql->exec(" SET 
				@order_id = 0 , 
				@new_id = 0 ,
				@new_order = 0 , 
				@mx = 0 , 
				@cnt = 0 , 
				@id_num = ".$id." , 
				@parent_id = ".$this->parent_id."; 
			");
			$rdr = $sql->read_exec("
				SELECT @order_id:=c.`order_id` AS `ord`,
					@mx:= max(`".$this->base."`.`order_id`) AS `max`,
				    @cnt:= count(`".$this->base."`.id)      AS `rows`
				FROM `".$this->base."` `".$this->base."` 
				    LEFT JOIN `".$this->base."` c ON c.`".$this->field_id."` = @id_num
				WHERE 1=1
					".($this->model->parent ? " AND `".$this->base."`.`_parent_id`   = @parent_id " 	: "")."
				    ".(isset($this->fields['_parent_self']) ? " AND `".$this->base."`.`_parent_self` = @parent_self " 	: "")."
				    ".$criteria."
			    GROUP BY c.`order_id`;
			");
			$rdr->read();
			//die(print_r($rdr->getItems()));
			$err = $GLOBALS['BASIC_ERROR']->error();
			if($err['code'] == 1054){
				$sql->append("ALTER TABLE `".$this->base."` ADD COLUMN `order_id` int(11) NOT NULL DEFAULT 0 ",true);
				$sql->exec();
				$GLOBALS['BASIC_ERROR']->clean();
				$this->ActionOrder($id,$action);
				return ;
			}
			$flag = 1;
			if($action == 'order_up') $flag = -1;
			if($rdr->field('ord') > -1 && ($rdr->field('ord') < $rdr->field('max') || $flag < 0)){
				
				// if exist equal order_id
				$sql->exec(" SET @i = @order_id; ");
				$sql->exec(" UPDATE `".$this->base."` SET order_id = @i:= @i + 1 WHERE 1=1
				     AND `order_id` = @order_id 
				     AND `".$this->field_id."` != @id_num;
				");
				
				if($flag > 0){
					$sql->exec(" SELECT 
					        @new_order:=order_id,
					        @new_id:=`".$this->field_id."` 
					    FROM `".$this->base."`  
					    WHERE 1=1
					        AND `order_id` > @order_id
					        ".($this->model->parent ? " AND `_parent_id` = @parent_id " 		: "")."
					    	".(isset($this->fields['_parent_self']) ? " AND `_parent_self` = @parent_self " 	: "")."
					        ".$criteria."
					    ORDER BY `order_id` LIMIT 1;
					");
				}else{
					$sql->exec(" SELECT 
					        @new_order:=order_id,
					        @new_id:=`".$this->field_id."` 
					    FROM `".$this->base."`  
					    WHERE 1=1
					        AND `order_id` < @order_id
					        ".($this->model->parent ? " AND `_parent_id` = @parent_id " 		: "")."
					        ".(isset($this->fields['_parent_self']) ? " AND `_parent_self` = @parent_self " 	: "")."
					        ".$criteria."
					    ORDER BY `order_id` DESC LIMIT 1; ");
				}
				$sql->exec(" UPDATE `".$this->base."` SET `order_id` = @order_id  WHERE `".$this->field_id."` = @new_id; ");
				$sql->exec(" UPDATE `".$this->base."` SET `order_id` = @new_order WHERE `".$this->field_id."` = @id_num; ");
			}
		}
	}
	/**
	 * Creator of cms objects
	 *
	 * @access Public
	 * @param ControlPanel_Components $obj
	 * @param int [$id]
	 * @return CmsComponent
	 */
	function createCmsObj($obj){
		return Builder::init()->getdisplayComponent(($obj->prefix != $obj->system_prefix ? $obj->prefix : '').$obj->system_name, $this->secure);
	}
	/**
	 * THe new are cache created objects.If there is created object, override RegisterObject object
	 * 
	 * @param string $child_name
	 * @return CmsComponent
	 */
	function buildChild($child_name){
		foreach ($this->model->child as $k => $obj){
			if(get_class($obj) == 'RegisterObject'){
				$checker = $obj->system_name;
			}else{
				$checker = get_class($this->model->child[$k]);
			}
			if($checker == $child_name){
				$obj = $this->createCmsObj($obj, $this->id);
				
				$obj->parent_obj = $this;
				$obj->parent_id = $this->id;
				
				return $obj;
			}
		}
		throw new Exception('Child component "'.$child_name.'" not exist.');
		return null;
	}
	/**
	 * Get parent cms object
	 *
	 * @return object CmsComponent
	 */
	function buildParent(){
		if($this->model->parent){
			$cmp = $this->createCmsObj($this->model->parent);
			$cmp->id = $this->parent_id;
			
			return $cmp;
		}
		return null;
	}
	/**
	 * 
	 * Get the tree by params
	 * 
	 * @param int $id
	 * @param string $criteria
	 * @param array $tree
	 * @param int $level
	 * @param string $separator
	 * 
	 * @return array $tree
	 */
	function getTree($id = 0, $criteria = '', $tree = array(), $level = 0, $separator = ""){
		$tab = '';
		
		for($i = 0; $i < $level; $i++){
			$tab .= $separator;
		}
		$rdr = $this->read(" AND `".$this->base."`.`_parent_self` = ".$id." "./*$this->_cms_criteria().*/$criteria." ");
		
		while($res = $rdr->read()){
			if(isset($res[$this->treeTitleItem])){
				$res[$this->treeTitleItem] = $tab.$res[$this->treeTitleItem];
			}
			$res['__level'] = $level;

			$tree[] = $res;
			$tree = $this->getTree($res['id'], $criteria, $tree, $level+1, $separator);
		}
		return $tree;
	}
	/**
	 * 
	 * @todo write description
	 * @param string $criteria
	 * @param int $id
	 * @param string $id_key_name
	 * @param string $separator
	 * @param array $first
	 * 
	 * @return array $tmp
	 */
	function getSelTree($criteria = '',$id = 0, $id_key_name = 'id', $separator = "", $first = array('' => '&nbsp;')){
		$tmp = $first ? $first : array();
		
		foreach ($this->getTree($id, $criteria, array(), 0, $separator) as $v){
			$tmp[$v[$id_key_name]] = $v[$this->treeTitleItem];
		}
		return $tmp;
	}
	/**
	 * 
	 * @todo write description
	 * @param string $text
	 * @param string $attributes
	 * @param string $criteria
	 */
	function getParentSelfElement($text = '', $attributes = '', $criteria = ''){
		$arr = $this->getSelTree($criteria, 0, 'id', "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		
		if(!$this->fields['_parent_self']){
			$this->createSelfElement($text, $attributes + array('data' => $arr));
		}else{
			$this->updateField('_parent_self', array(
				'attributes' => array('data' => $arr)
			));
		}
	}
	/**
	 * 
	 * @todo write description
	 * @param string $text
	 * @param string $attributes
	 */
	function createSelfElement($text = '', $attributes = ''){
		$attributes = BASIC_GENERATOR::init()->convertStringAtt($attributes);
		$this->setField('_parent_self', array(
			'text' => $text,
			'length' => 11,
			'dbtype' => 'int',
			'formtype' => 'select',
			'attributes' => $attributes
		));
		BASIC_TEMPLATE2::init()->set("parent_self", true, $this->template_list);
	}
	/**
	 * 
	 * Get Records data from db
	 * @param array $ids
	 * @param string $criteria
	 * @return ComponentReader
	 */
	function getRecords($ids = array(), $criteria = '', $include_all = false){	
		if($this->model->parent){
			$this->setField('_parent_id', array(
				'formtype' => 'hidden',
				'length' => 11,
				'dbtype' => 'int'
			));
			$this->system[] = $this->miss[] = '_parent_id';
			
			$criteria = ' AND `'.$this->base.'`.`_parent_id` '.(is_array($this->parent_id) ? "IN (".implode(",",$this->parent_id).")" : "= ".$this->parent_id)." ".$criteria;
		}
		return parent::getRecords($ids, $criteria, $include_all);
	}
	/**
	 * @todo write description
	 * @see DysplayComponent::footerBar()
	 */
	function footerBar(){
		$pbar = ($this->paging ? $this->paging->getBar() : array());
		$pbar['max_page_rows'] = array();
		
		foreach($this->getMaxRowsOptions() as $key => $val){
			$pbar['max_page_rows'][] = array(
				'link' => BASIC_URL::init()->link(BASIC::init()->scriptName(), BASIC_URL::init()->serialize(array('page_rows')).'page_rows='.$key),
				'label' => $val,
				'current' => ($this->maxrow == 0 && $key == -1)||($this->maxrow == $key)
			);	
		}
		BASIC_TEMPLATE2::init()->set(array(
			$this->templates['list-vars']['action-bar'] => $this->footerActionsBar(),
			$this->templates['list-vars']['paging-bar'] => $pbar
		), $this->template_list);
	}
}
/**
 * @todo write description
 * @author Evgeni Baldzhiyski
 * @version 1.0
 * @since 25.07.2011
 * @package cms.form
 */
class Tree extends CmsComponent{
	
	function main(){
		parent::main();
		
		$this->createSelfElement(BASIC_LANGUAGE::init()->get('parent'));
		
		$this->ordering(true);
	}
	function ActionFormAdd(){
		$this->getParentSelfElement();		
		
		return parent::ActionFormAdd();
	}
	function ActionFormEdit($id){
		$this->getParentSelfElement('', '', ' AND `id` != '.(int)$id);		
		
		return parent::ActionFormEdit($id);
	}
	/**
	 * @todo need fix paging problem
	 */
	function ActionList(){
		$_map = true;
		$criteria = '';
		
		if(!$this->map) $_map = false;
		
		foreach($this->fields as $k => $v){
			if(isset($v['filter']) || isset($v['filterFunction'])){
				if(!$this->filter){
					$this->filter = new BasicFilter();
					$this->filter->prefix($this->prefix.'f');
					$this->filter->template($this->template_filter);
					if(isset($this->actions['Filter'])){
						$this->filter->button($this->actions['Filter'][2]);
					}
				}
				if(isset($v['filter']) && $v['filter'] == 'auto'){
					if($v[2] == 'int'){
						$tmp = $this->getField($k);
						$tmp['filter'] = " AND (`{1}` >= {V1} OR `{2}` <= {V2}) ";
						$this->filter->rangeField($k, $tmp);
					}else{
						$tmp = $this->getField($k);
						$tmp['filter'] = " AND `".$k."` LIKE '%{V}%' ";
						$this->filter->field($k, $tmp);		
					}
				}else{
					$this->filter->field($k,$v);
				}
			}
			if(!$_map) $this->map($k, $v[4]); 
		}
		if($this->filter){
			$this->filter->init();
			$criteria .= $this->filter->sql();
		}
		if($this->sorting) $criteria .= $this->sorting->getsql();		
		
		$arr = $this->getTree(0, $criteria);
		$count = count($arr);
		
		$this->paging = new BasicComponentPaging($this->prefix);
		$this->paging->init($count, $this->maxrow);
		
		if($this->maxrow != 0 && $count > $this->maxrow){
			$space = $this->paging->getSpace();
			
			$tmp = array();
			for($i = $space['from'] ; $i < (($count) < $space['to'] ? $count : $space['to']); $i++){
				$tmp[] = $arr[$i];
			}
			$arr = $tmp;
		}
		return $this->compile($arr);
	}
}