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
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @since 14.01.2014
 * @package cms.form
 */
class CmsForeignElements extends ForeignElements{
	/**
	 * Table creator
	 *
	 * @access public
	 * @return boolen
	 */
	function SQL($column_name){
		$table = new BasicSqlTable();
	
		if($column_name){
			if($column_name == $this->field_value){
				$table->field($this->field_value, $this->field_el[0], $this->field_el[1]);
			}else if($column_name == $this->field_tag){
				$table->field($this->field_tag);
			}else if($column_name == '_deleted'){
				$table->field('_deleted', 'int', 1, false, 0);
				$table->key('_deleted', 'multiple_index');
			}
			return BASIC_SQL::init()->createColumn($this->base, $table);
		}else{
			$table->field($this->field_id, 'int', 11);
			$table->field($this->field_value, $this->field_el[0], $this->field_el[1]);
			$table->field($this->field_tag);
		
			$table->key($this->field_id, 'multiple_index');
			$table->key($this->field_tag, 'multiple_index');
			
			if(CMS_SETTINGS::init()->get('SITE_DATA_DELETE') == 'archive'){
				$table->field('_deleted', 'int', 1, false, 0);
				$table->key('_deleted', 'multiple_index');
			}
			return BASIC_SQL::init()->createTable(null, $this->base, $table, 'InnoDB');
		}
	}
	function remove($id){
		if(CMS_SETTINGS::init()->get('SITE_DATA_DELETE') == 'archive'){
			BASIC_SQL::init()->exec2(" UPDATE `".$this->base."` SET `_deleted` = 1 WHERE 1=1
				AND `".$this->field_id."` = ".(int)$id."
				AND `".$this->field_tag."` = '".$this->tag_value."'
			");
		}else{
			parent::remove($id);
		}
	}
	function update($id){
		if(CMS_SETTINGS::init()->get('SITE_DATA_DELETE') == 'archive'){
			parent::remove($id);
			if(is_array($this->dataBuffer)){
				foreach ($this->dataBuffer as $v){
					BASIC_SQL::init()->exec2(" INSERT INTO `".$this->base."` (
						`".$this->field_id."`, `".$this->field_value."`, `".$this->field_tag."`, `_deleted`
					)VALUES(
						'".$id."', '".$v."', '".$this->tag_value."', 0
					) ");
				}
			}
		}else{
			parent::update($id);
		}
	}	
}
/**
 * @author Evgeni Baldziyski
 * @version 3.2
 * @since 02.02.2008
 * @package cms.form
 */
class DysplayComponent extends Component{
	/**
	 * Register default actions
	 *
	 * ActionFormAdd 'Add new records'
	 * ActionFormEdit 'Edit/View'
	 * ActionRemove 'Remove selected elements'
	 * ActionSave 'Save'
	 * ActionBack 'Back'
	 * ActionLoad
	 * ActionError
	 * ActionFileRemove
	 * ActionList
	 *
	 * @access public
	 * @var array
	 */
	var $actions = array(
			'add'   	  => array('ActionFormAdd', 1, 'Add new records'),
			'edit'   	  => array('ActionFormEdit',2, 'Edit/View'),
			//'details'	  => array('ActionDetails', 2, 'Details'),
			'delete'	  => array('ActionRemove',  1, 'Remove checked'),
			'save'		  => array('ActionSave', 	3, 'Save'),
			'cancel'	  => array('ActionBack', 	3, 'Back'),
			// System Actions
			'load'		  => array('ActionLoad',    0),
			'error'		  => array('ActionError',   0),
			//'order_up'  => array('ActionOrder',   0),
			//'order_down'=> array('ActionOrder',   0),
			'fileRemove'  => array('ActionFileRemove', 0),
			// Default Call Action
			'list' 		  => array('ActionList',    0)
	);
	/**
	 * @var ForeignElementsInterface
	 * @access public
	 */
	var $fieldsForeignManager = 'CmsForeignElements';
	/**
	 * Error Action Name.If exist error system will redirect to errorAction handler.
	 *
	 * @access public
	 * @var string
	*/
	var $errorAction = 'edit';
	/**
	 * Used from test method.
	 * if this property have value != '' when createInterface execute errorAction.
	 *
	 * @access public
	 * @var array
	 */
	var $messages = array();
	/**
	 *
	 * @access public
	 * @var string
	*/
	public $prefix = '';
	/**
	 * Container for component actions name
	 *
	 * @access public
	 * @var string
	 */
	var $cmd = '';
	/**
	 * Url parameter for action name
	 *
	 * @access public
	 * @var string
	 */
	var $urlCmdName = 'cmd';
	/**
	 * Key for field order_id on db table, if(no exist) auto created
	 *
	 * @access private
	 * @var boolean
	 */
	private $_ordering = false;
	/**
	 * Container missing variables
	 *
	 * @access public
	 * @var array
	 */
	var $miss = array();
	/**
	 * Container hidden elements
	 *
	 * @access public
	 * @var string
	*/
	var $hidden_el = '';
	/**
	 * Container for lingual fields
	 *
	 * @access public
	 * @var array
	 */
	var $nasional = array();

	// List manager property //
	/**
	 * List manager property
	 *
	 * @access public
	 * @var array
	*/
	var $system = array();
	/**
	 * Max number rows in list view
	 *
	 * @access public
	 * @var integer
	*/
	var $maxrow = 20;
	/**
	 * Contain BasicSorting instance
	 *
	 * @access public
	 * @var BasicSorting
	 */
	var $sorting = null;
	/**
	 * Contain BasicComponentPaging instance
	 *
	 * @access public
	 * @var BasicComponentPaging
	 */
	var $paging = null;
	/**
	 * Contain BasicFilterInterface instance
	 *
	 * @access public
	 * @var BasicFilterInterface
	 */
	var $filter = null;
	/**
	 * Contain BasicFilterInterface instance
	 *
	 * @access public
	 * @var hashmap
	 */
	var $filter_buttons = array(
			'clear'  => 'Clear',
			'action' => 'Filter'
	);
	/**
	 * hashmap with columns data for list UI.
	 *
	 * @access public
	 * @var array
	*/
	var $map = array();
	/**
	 * show/hide language bar in formUI.
	 *
	 * @access public
	 * @var boolean
	*/
	var $useJSLang = true;
	/**
	 * Flag for lock form save state
	 *
	 * @access public
	 * @var boolean
	 */
	var $useSaveState = true;
	/**
	 * Pointer to method for additional form validation
	 *
	 * Pointer to the method, that will be used for additional validation of the data.
	 * If the pointer is string will be call function else will be call array[0]->array[1]
	 * value = array(&$obj,'method') === $obj->method($this,$id,$action)
	 * value = 'function' === function($this,$id,$action)
	 *
	 * NEW :: value = array('this','method) === $this->method($id,$action)
	 *
	 * @access public
	 * @var string|array
	 */
	var $specialTest = '';
	/**
	 * indicates if the validators will be used
	 *
	 * @access public
	 * @var boolean
	 */
	var $autoTest 	 = true;

	/**
	 * Template name for form view (ActionFormAdd, ActionFormEdit, ...)
	 *
	 * @access public
	 * @var string
	 */
	var $template_form 	  	   = 'cmp-form.tpl';
	/**
	 * Default view template 'cmp-form.tpl
	 *
	 * @access public
	 * @var string
	 */
	var $template_form_default = 'cmp-form.tpl';
	/**
	 * Template name for list view (ActionList, ...)
	 *
	 * @access public
	 * @var string
	 */
	var $template_list 	  	   = 'cmp-list.tpl';
	/**
	 * Default list template - 'cmp-list.tpl'
	 *
	 * @access public
	 * @var string
	 */
	var $template_list_default = 'cmp-list.tpl';
	/**
	 * Template for details view (ActionDetails, ...)
	 *
	 * @access public
	 * @var string
	 */
	var $template_details 		  = 'cmp-details.tpl';
	/**
	 * Default details template - 'cmp-details.tpl'
	 *
	 * @access public
	 * @var string
	 */
	var $template_details_default = 'cmp-details.tpl';
	/**
	 * Template  name for list filter (when this->filter != null)
	 *
	 * @access public
	 * @var string
	 */
	var $template_filter  		 = 'cmp-filter.tpl';
	/**
	 * Default list filter  template - 'cmp-filter.tpl'
	 *
	 * @access public
	 * @var string
	 */
	var $template_filter_default = 'cmp-filter.tpl';
	/**
	 * Templates declarations
	 *
	 * @access public
	 * @var array
	 */
	var $templates = array(
			// form template info
			'form-dynamic' => 'fields',
			'form-vars' => array(
					'prefix'  => 'prefix',
					'perm' 	  => 'perm',
					'label'   => 'label',
					'ctrl' 	  => 'ctrl',
					'message' => 'message',
					'buttons_bar' => 'buttons_bar',
					'value' 	=> 'value'
			),
			'list-vars' => array(
					'head-check' 		 => 'use_checkbox',
					'head-order' 		 => 'use_order',
					'head-dynamic' 		 => 'headers',
					'head-length' 		 => 'column_length',
					'head-dynamic-attr'  => 'attr',
					'head-dynamic-label' => 'label',
					'head-dynamic-selected' => 'selected',
					'head-dynamic-isdown' => 'isdown',

					'body-dynamic' 			 => 'rows',
					'body-dynamic-evenclass' => 'even_class',
						
					'body-dynamic-rownumber' 	=> 'row_number',
					'body-dynamic-rowlevel' 	=> 'row_level',
					'body-dynamic-columns' 		=> 'columns',
					'body-dynamic-columns-attr' => 'attr',
					'body-dynamic-columns-label'=> 'label',
					'body-dynamic-id' 			=> 'id',
					'body-dynamic-actionbar' 	=> 'action_bar',

					'action-bar' => 'action_bar',
					'paging-bar' => 'paging_bar',
						
					'prefix' => 'prefix',
					'cmd' 	 => 'cmd',
					'idcmd'  => 'idcmd'
							),
							'action-bar-vars' => array(
							'actions' 		    => 'actions',
							'actions-key'       => 'key',
							'actions-pkey'      => 'pkey',
							'actions-text' 	    => 'text',
							'actions-link' 	    => 'link',
							'actions-disable'   => 'disable',
							'actions-rule-type' => 'rule_type',
							'actions-rule-text' => 'rule_text',
							'is-ie7' 		    => 'is_ie7',
							'prefix' 		    => 'prefix',
							'cmd' 		  	    => 'cmd'
									),
									'row-action-bar-vars' => array(
									'function' 		  => 'function',
									'level' 		  => 'level',
									'id' 			  => 'id',
									'rownumber' 	  => 'row_number',
									'orderbar' 		  => 'order_bar',
									'orderbar-key' 	  => 'key',
									'orderbar-link'   => 'link',
									'actions' 		  => 'actions',
									'actions-key' 	  => 'key',
									'actions-pkey' 	  => 'pkey',
									'actions-text' 	  => 'text',
									'actions-link' 	  => 'link',
									'actions-disable' => 'disable',
									'actions-rule-type' => 'rule_type',
									'actions-rule-text' => 'rule_text',
									'prefix' 		  => 'prefix',
									'is-ie7' 		  => 'is_ie7',
									'idcmd' 		  => 'idcmd'
											),
											'form-action-bar-vars' => array(
											'rules' 		 => 'rules',
											'rules-type' 	 => 'type',
											'rules-key' 	 => 'key',
											'rules-text' 	 => 'text',
												
											'actions'	 	 => 'actions',
											'actions-key' 	 => 'key',
											'actions-pkey' 	 => 'pkey',
											'actions-text' 	 => 'text',
											'actions-disable'=> 'disable',

											'actions-rule-type' => 'rule_type',
											'actions-rule-text' => 'rule_text',

											'is-ie7' 		 => 'is_ie7',
											'prefix' 		 => 'prefix',
											'cmd' 		 	 => 'cmd',
												
											// this vars are supported in "form-vars" array also
											'linguals' 		 => 'linguals',
											'linguals-key' 	 => 'key',
											'linguals-text'  => 'text',
											'linguals-flag'  => 'flag',
											'lingual-current'=> 'current'
													)
	);
	/**
	 * Get system variables
	 *
	 * @access public
	 * @param boolean $state
	 * @return arrray
	*/
	function getSystemVars($state = true){
		$tmp = $this->system;
		if($this->sorting && !$state){
			$tmp[] = $this->sorting->getPrefix().'dir';
			$tmp[] = $this->sorting->getPrefix().'column';
		}
		return $tmp;
	}
	/**
	 * Get field message
	 *
	 * @access public
	 * @param string $name_field
	 * @return string|integer
	 */
	function getMessage($name_field){
		if(isset($this->messages[$name_field])){
			return $this->messages[$name_field];
		}
		return 0;
	}
	/**
	 * Set field message
	 *
	 * @param string $name_field
	 * @param integer|string $code
	 * @return boolen
	 */
	function setMessage($name_field, $code){
		if($this->fields[$name_field]){
			$this->messages[$name_field] = $code;
		}
		return true;
	}
	/**
	 * Remove message from message container by field name
	 *
	 * @access public
	 * @param string $name_field
	 * @return void
	 */
	function unsetMessage($name_field){
		unset($this->messages[$name_field]);
	}
	/**
	 * Reset message container
	 *
	 * @access public
	 * @return void
	 */
	function cleanMessages(){
		$this->messages = array();
	}
	/**
	 * Created SQL data base code
	 * 
	 * @access public
	 * @param string [$message]
	 * @return boolen
	 */
	function SQL($message = ''){
		$table = new BasicSqlTable();
		
		if($message){
			preg_match("/column( name)? '([^']+)'/",$message, $match);
			if(isset($match[2])){
				$spl = explode(".", $match[2]);
				$match[2] = $spl[count($spl) - 1];
				
				foreach ($this->fields as $fk => $v){
					if($v[3] == 'none' || isset($this->fieldsForeign[$fk])) continue;
				
					if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$fk])){
						foreach(BASIC_LANGUAGE::init()->language as $k => $l){
							if(preg_replace("/_".$k."$/", "", $match[2]) == $v[0]){
								$v[0] = $fk."_".$k;
								return BASIC_SQL::init()->createColumn($this->base, $this->columnProp($v, $table));
							}
						}
					}
					if($fk == $match[2]){
						return BASIC_SQL::init()->createColumn($this->base, $this->columnProp($v, $table));
					}
				}				
			}
			return false;
		}else{
			foreach ($this->fields as $key => $val){
				if($val[3] == 'none' || isset($this->fieldsForeign[$key])) continue;
	
				if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$key])){
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){
						$multi = $val; $multi[0] = $multi[0]."_".$k;
						$this->columnProp($multi, $table);
					}
				}else{
					$this->columnProp($val, $table);
				}
			}
			return BASIC_SQL::init()->createTable($this->field_id, $this->base, $table);
		}
	}
	/**
	 * Get value of $name from data buffer
	 *
	 * @access public
	 * @param string $name
	 * @return string
	 */
	function getDataBuffer($name){
		if(isset($this->dataBuffer[$name])){
			return $this->dataBuffer[$name];
		}else if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$name]) && isset($this->dataBuffer[$name."_".BASIC_LANGUAGE::init()->current()])){
			return $this->dataBuffer[$name."_".BASIC_LANGUAGE::init()->current()];
		}
		return '';
	}
	/**
	 *
	 * Set new element in Data Buffer
	 *
	 * @access public
	 * @param string $name
	 * @param mix $value
	 */
	function setDataBuffer($name, $value){
		if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$name])){
			$this->dataBuffer[$name."_".BASIC_LANGUAGE::init()->current()] = $value;
		}else{
			$this->dataBuffer[$name] = $value;
		}
	}
	/**
	 * Unset Data Buffer element
	 *
	 * @access public
	 * @param string $name
	 * @return void
	 */
	function unsetDataBuffer($name){
		if(isset($this->dataBuffer[$name])){
			unset($this->dataBuffer[$name]);
		}else if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$name]) && isset($this->dataBuffer[$name."_".BASIC_LANGUAGE::init()->current()])){
			unset($this->dataBuffer[$name."_".BASIC_LANGUAGE::init()->current()]);
		}
	}
	/**
	 * Set component field
	 *
	 * <code>
	 * 	; All fields options and not required.
	 *			$this->setField('my_field');
	 	
	 ---------------------------------------------------------------------------------------------
	 text 			= (the fields id) 	/ the fields label. By default the vlaue is the field id.
	 ---------------------------------------------------------------------------------------------
	 $this->setField('my_field', array(
	 'text' => BASIC_LANGUAGE::init()->get('my_filed_label')
	 ));
	 	
	 ---------------------------------------------------------------------------------------------
	 formtype		= (text) 			/ the id of registrate control in BASIC_GENERATOR. For more information see the section "default-controls".
	 ---------------------------------------------------------------------------------------------
	 $this->setField('my_field', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'formtype' 	=> 'password'
	 ));

	 ---------------------------------------------------------------------------------------------
	 dbtype			= (varchar) 		/ the database columns type.
	 ---------------------------------------------------------------------------------------------
	 $this->setField('my_field', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'dbtype' 	=> 'int'
	 ));
			
	 ---------------------------------------------------------------------------------------------
	 length			= (255) 			/ the max length in database column and in the HTML control.
	 ---------------------------------------------------------------------------------------------
	 $this->setField('my_check_box', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_check_box'),
	 'formtype'	=> 'checkbox',
	 'dbtype' 	=> 'int',
	 'lenght'	=> 1
	 ));
			
	 ---------------------------------------------------------------------------------------------
	 perm			= (empty string) 	/ symbol for the form UI and flag for the database show permition mode for this field.
	 ---------------------------------------------------------------------------------------------
	 $this->setField('my_field', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'formtype' 	=> 'password',
	 'perm'		=> '*'
	 ));

	 ---------------------------------------------------------------------------------------------
	 default 		= (empty string) 	/ default value for the form UI when is open for "insert" (ActionFormAdd).
	 ---------------------------------------------------------------------------------------------
	 $this->setField('my_field', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'default'	=> 'Fill you name please.'
	 ));
	 	
	 ---------------------------------------------------------------------------------------------
	 lingual     	= (false) 			/ start multylingual support for this field.
	 ---------------------------------------------------------------------------------------------
	 $this->setField('my_field', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'lingual'	=> true
	 ));
	 	
	 ---------------------------------------------------------------------------------------------
	 filter 			= (empty string) 	/ sql filter criteria for the filter in the list UI.
	 ---------------------------------------------------------------------------------------------
	 $this->setField('my_field', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'filter'	=> ' "nickname" LIKE "%{V}" '
	 ));
			
	 ---------------------------------------------------------------------------------------------
	 filterFunction 	= (null) 			/ function or method for generate sql filter criteria for the filter in the list UI.
	 ---------------------------------------------------------------------------------------------
	 class MyTools{
	 / **
	 * Handler for field's option "filterFunction".
	 *
	 * @paranm String $filter_value - value for the sql criteria
	 * @paranm String $filed_name	- fields(option owner) name
	 * /
	 function mySqlBuilder($filter_value, $filed_name){
	 if($field_name == 'my_field'){
	 $currency = 'EU';
	 if($filter_value == 1){
	 $currency = 'US';
	 }else if($filter_value == 2){
	 $currency = 'BG';
	 }
	 return " AND `currency` = '".$currency."' "
	 }
	 return "";
	 }
	 }
			
	 ...

	 $this->setField('my_field', array(
	 'text' 				=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'filterFunction'	=> array(new MyTools(), 'mySqlBuilder')
	 ));
			
	 ----------------------------------------
			
	 function mySqlBuilderFunction($filter_value, $filed_name){
	 $tools = new MyTools();

	 return $tools->mySqlBuilder($filter_value, $filed_name);
	 }
			
	 ...
			
	 $this->setField('my_field', array(
	 'text' 				=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'filterFunction'	=> 'mySqlBuilderFunction'
	 ));
	 	
	 ---------------------------------------------------------------------------------------------
	 attributes		= (empty array) 	/ mix of options (standart HTML tags options, specific for the control options)
	 ---------------------------------------------------------------------------------------------
	 	
	 $this->setField('my_field', array(
	 'text' 				=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'formtype'			=> 'moveselect',
	 'attributes'		=> array(
	 'data' => array(
	 'en' => 'English',
	 'bg' => 'Bulgarian',
	 'fr' => 'France'
	 ),
	 'id' => 'this_html_id',
	 'class' => 'my_language_list'
	 )
	 ));
	 	
	 ---------------------------------------------------------------------------------------------
	 cleaners		= (empty array)		/ local/specific for filed fleaners
	 ---------------------------------------------------------------------------------------------
	 	
	 ====================================
	 valid values
	 ====================================
	 array('function for input and output')
	 array('function for input', 'function for output')
	 array('function for input', '#') - if this case will clean only input. For output will miss cleaner

	 array(array(object, 'method for input and output'))
	 array(
	 array(object, 'method for input'),
	 array(object, 'method for input')
	 )
	 array(array(object, 'method for input'), '#')
	 	
	 ------------------ Examples ------------------

	 / **
	 * Handlers for field's option "cleaners" or global cleaners.
	 *
	 * @paranm String $filter_value - value for the sql criteria
	 * @paranm String $filed_name	- fields(option owner) name
	 * /
	 class MyTools{
	 function cleanerForInput($value_for_cleaning){
	 $value_for_cleaning = strip_tags($value_for_cleaning);
	 $value_for_cleaning = addslashes($value_for_cleaning);
			
	 return $value_for_cleaning;
	 }
	 function cleanerForOutput($value_for_cleaning){
	 $value_for_cleaning = stripslashes($value_for_cleaning);

	 return $value_for_cleaning;
	 }
	 function oneCleanerForOutputAndInput($value){
	 return (int)$value
	 }
	 }
			
	 ...

	 $this->setField('my_field', array(
	 'text' 				=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'filterFunction'	=> array(
	 array(new MyTools(), 'oneCleanerForOutputAndInput')
	 )
	 ));
			
	 ----------------------------------------
			
	 function myCleanerForInputFunction($value_for_cleaning){
	 $tools = new MyTools();

	 return $tools->cleanerForInput($value_for_cleaning);
	 }
	 function myCleanerForOutoutFunction($value_for_cleaning){
	 $tools = new MyTools();

	 return $tools->cleanerForOutput($value_for_cleaning);
	 }
			
	 ...
			
	 $this->setField('my_field', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'cleaners'	=> array('myCleanerForInputFunction', 'myCleanerForOutoutFunction')
	 ));
	 	
	 ---------------------------------------------------------------------------------------------
	 messages		= (empty array)		/ list with error and information mesages
	 ---------------------------------------------------------------------------------------------

	 By default the system support two messages codes:
	 0 = [empty string] 		/ if is not empty string will use this if not have message (error)
	 1 = 'Mandatory Field!' 	/ will use from default validator when have value for the field option "perm" and the variable from request is empty.
			
	 -----------------------------------------------------------------------
	 For more auto return messages codes see cms/basic/upload.mod.php/test()
	 -----------------------------------------------------------------------

	 -------------------------------------------------------------

	 function myValidatorMethod(){
	 if(!BASIC::init()->validEmail($this->getDataBuffer('my_email_field'))){
	 $this->setMessage('my_email_field', 2);
	 }else if(!$this->read(" AND `my_email_field` = '".$this->getDataBuffer('my_email_field')."' ")->read()){
	 $this->setMessage('my_email_field', 3);
	 }else if(preg_match("/\.net$/")){
	 $this->setMessage('my_email_field', 4);
	 }
	 }

	 function main(){
	 $this->specialTest = 'myValidatorMethod';
			
	 $this->setField('my_email_field', array(
	 'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
	 'messages'	=> array(
	 1 => basic_LANGUAGE::init()->get('missing_email'),
	 2 => BASIC_LANGUAGE::init()->get('invalid_email'),
	 3 => BASIC_LANGUAGE::init()->get('exist_email'),
	 4 => BASIC_LANGUAGE::init()->get('insupported_email')
	 )
	 ));
	 }

	 ---------------------------------------------------------------------------------------------
	 [your specific option]		= (*)		/ you can set your specific options with different from standart options names that you can get it later with method "getFirld".
	 ---------------------------------------------------------------------------------------------
	 * </code>
	 *
	 * @access public
	 * @param string $name
	 * @param array [$context]
	 * @param string $after
	 * @return void
	 */
	function setField($name, $context = array(), $after = ''){
		if(!isset($context['default'])) $context['default'] = '';
		if(!isset($context['lingual'])) $context['lingual'] = false;

		if($context['lingual'] == 'true' || $context['lingual'] == true){
			$this->nasional[$name] = 1;
		}else if($context['lingual'] == 'false' || $context['lingual'] == false){
			unset($this->nasional[$name]);
		}

		if(!isset($context['messages'])) $context['messages'] = array();

		if(!isset($context['messages'][0])) $context['messages'][0] = '';
		if(!isset($context['messages'][1])) $context['messages'][1] = 'Mandatory Field!';

		parent::setField($name, $context, $after);

		if(isset($this->fieldsForeign[$name])){
			$this->fields[$name]['lingual'] = false;
			unset($this->nasional[$name]);
		}
	}
	/**
	 * Remove field  $name from fileds container
	 *
	 * @access public
	 * @param string $name
	 * @return void
	 * @see Component::unsetField()
	 */
	function unsetField($name){
		if(isset($this->fields[$name])){
			unset($this->nasional[$name]);
		}
		if(isset($this->fieldsForeign[$name])){
			unset($this->fieldsForeign[$name]);
		}
		parent::unsetField($name);
	}
	/**
	 * Get uploaded file from request. Check for errors. Support multylanguage file upload.
	 * Inside use from method "test".
	 *
	 * @access private
	 * @param field $v
	 * @param string [$lang]
	 * @return void
	 */
	protected function test_file($v, $lang = ''){
		BASIC::init()->imported('upload.mod');

		$_lang = '';
		if($lang) $_lang = "_".$lang;

		$this->fieldsFile[$v[0].$_lang] = new BasicUpload($this->prefix.$v[0].$_lang);

		//		if($lang && $lang == $GLOBALS['BASIC_LANG']->current()){
		//			$this->setDataBuffer($v[0], $this->fieldsFile[$v[0].$_lang]);
		//		}

		$att = BASIC_GENERATOR::init()->convertStringAtt($v[6]);
		if(isset($att['folders']) && $att['folders'] == 'true'){
			if(isset($att['dir'])){
				$path = BASIC_URL::init()->request($this->prefix.$v[0].$_lang."_path",
						$this->cleanerDecision($v[3], true, $v[7])
				);
				$multi = explode(";", $att['dir']);
				$test_path = true;
				foreach($multi as $f){
					if(preg_match("#".$path."#", $f)){
						$this->fieldsFile[$v[0].$_lang]->upDir = $path;
						$test_path = false;
						break;
					}
				}
			}
		}else{
			isset($att['dir']) ? $this->fieldsFile[$v[0].$_lang]->upDir = $att['dir'] : '';
		}
		if(isset($att['rand'])) $this->fieldsFile[$v[0].$_lang]->rand = $att['rand'];
		if(isset($att['max']))  $this->fieldsFile[$v[0].$_lang]->maxSize = $att['max'];
		if(isset($att['as']))   $this->fieldsFile[$v[0].$_lang]->AsFile = $att['as'];
		if(isset($att['perm'])) $this->fieldsFile[$v[0].$_lang]->setType(explode(",", $att['perm']));
		// Add Events
		if(isset($att['onComplete'])) $this->fieldsFile[$v[0].$_lang]->onComplete = $att['onComplete'];
		if(isset($att['onError'])) $this->fieldsFile[$v[0].$_lang]->onError = $att['onError'];
		if(isset($att['onDelete'])) $this->fieldsFile[$v[0].$_lang]->onDelete = $att['onDelete'];

		$this->fieldsFile[$v[0].$_lang]->test();
		if($ferr = $this->fieldsFile[$v[0].$_lang]->test()){
			if($ferr == 4 || $ferr == 5){
				if($v[1]){
					$use_err = true;
					if($this->id){
						$res = BASIC_SQL::init()->read_exec(" SELECT `".$v[0].$_lang."` FROM `".$this->base."` WHERE `".$this->field_id."` = ".$this->id." ", true);
						if($res[$v[0].$_lang]){
							$use_err = false;
						}
					}
					if($use_err) $this->setMessage($v[0].$_lang, 1);
				}
			}else{
				$this->setMessage($v[0].$_lang, $ferr);
			}
		}
		$this->setDataBuffer($v[0].$_lang, $this->fieldsFile[$v[0].$_lang]);
	}
	/**
	 * Test for empty binding fields and load system var array $dataBuffer
	 * Last update is moving on the spesial test in the end and if($this->fields[][0] == '') miss
	 * Effect: create array $this->dataBuffer
	 *
	 * @access public
	 * @version 0.3
	 * @since 01-04-2007
	 * @return boolen
	 */
	function test(){
		if(!$this->autoTest) return false;

		foreach($this->fields as $v){
			if(($this->_ordering && $v[0] == 'order_id') || $v[5] == 'none'){
				continue;
			}
				
			$v[2] = (int)$v[2];
				
			$ctrl = BASIC_GENERATOR::init()->getControl($v[5]);
			if($ctrl !== null && $ctrl->isFileUpload()){
				if(isset($GLOBALS['BASIC_LANG']) && $GLOBALS['BASIC_LANG']->language && isset($this->nasional[$v[0]])){
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){
						$this->test_file($v, $k);
					}
				} else {
					$this->test_file($v);
				}
			}else{
				if(isset($GLOBALS['BASIC_LANG']) && $GLOBALS['BASIC_LANG']->language && isset($this->nasional[$v[0]])){
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){
						if($ctrl !== null && $ctrl->isMultiple() && !BASIC_URL::init()->test($this->prefix.$v[0]."_".$k)){
							BASIC_URL::init()->set($this->prefix.$v[0]."_".$k, array());
						}
						$var_url = BASIC_URL::init()->request($this->prefix.$v[0]."_".$k, $this->cleanerDecision($v[3],true,$v[7]));

						//						if($k == $GLOBALS['BASIC_LANG']->current()){
						//							$this->setDataBuffer($v[0],$var_url);
						//						}
						$this->setDataBuffer($v[0]."_".$k, $var_url);
						if($v[1] && (
								(string)$this->dataBuffer[$v[0]."_".$k] == '' || (is_array($var_url) && !$var_url)
						)){
							$this->setMessage($v[0],1);
						}
					}
				}else{
					if($ctrl !== null && $ctrl->isMultiple() && !BASIC_URL::init()->test($this->prefix.$v[0])){
						BASIC_URL::init()->set($this->prefix.$v[0], array());
					}
					$var_url = BASIC_URL::init()->request($this->prefix.$v[0], $this->cleanerDecision($v[3],true,$v[7]));
						
					$this->setDataBuffer($v[0], $var_url);
					if($v[1] && (
							(string)$this->dataBuffer[$v[0]] == '' || (is_array($var_url) && !$var_url)
					)){
						$this->setMessage($v[0],1);
					}
				}
			}
		}
		if($this->specialTest != ''){
			if(is_array($this->specialTest)){
				$obj = $this->specialTest[0];
				$method = $this->specialTest[1];
				$err = false;
				if($obj != null){
					$err = $obj->$method($this);
				}else{
					$err = $method($this);
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
	/**
	 * Call commponent actions. Support valid action, form type action (3). If called action is forbidden or not exist
	 * append to BASIC_ERROR service message and change errorAction value to 'list'.
	 *
	 * @access public
	 * @param string $action
	 * @param array|int [$id]
	 * @param boolean [$useTest]
	 * @return mix
	 */
	function action($action, $id = null, $useTest = true){
		$tmp = '';
		try{
			if(isset($this->actions[$action])){

				if($this->actions[$action][1] >= 0){
					$caller = $this->actions[$action][0];
						
					//@FIX need think for cancel action and test!!!
					if($action != 'cancel' && $useTest && $this->actions[$action][1] == 3){
						if(!$this->test()){
							$tmp = $this->$caller($id, $action);
						}else{
							$tmp = false;
						}
					}else{
						$tmp = $this->$caller($id, $action);
					}
				}else{
					throw new Exception("Action '".$action."' is forbidden. ");
				}
			}else{
				throw new Exception("Action '".$action."' is not supported. ");
			}
		}catch (Exception $e){
			BASIC_ERROR::init()->setError($e->getMessage());
			$this->errorAction = 'list';
			$tmp = '';
		}
		return $tmp;
	}
	/**
	 * Check for existing action in current componenet.
	 *
	 * @access public
	 * @param integer $n
	 * @return boolean
	 */
	function checkForActions($n){
		$action = false;
		foreach ($this->actions as $v){
			if($v[1] == $n || $v[1] == ($n*(-1))) $action = true;
		}
		return $action;
	}
	/**
	 * Add new action.
	 *	The param $activate is flag for button action's location. The type locations are:
	 *		1 - action manager in list interface
	 *		2 - row action manager in list interface
	 *		3 - buttons bar in form interface
	 *
	 *	The param $rule is javascript rules. The type rules are:
	 *		javascript:(javascript code) - any javascript code
	 *		message:(text) - open alert dialog with content (text)
	 *		confirm:(text) - open confirm dialog with content text
	 *
	 * @access public
	 * @param string $action
	 * @param string $method
	 * @param string [$text]
	 * @param integer [$activate]
	 * @param string [$rule]
	 * @return void
	 */
	function addAction($action, $method, $text = '', $activate = 1, $rule = ''){
		$this->actions[$action] = array($method, $activate, $text, $rule);
	}
	/**
	 * Edit existing action
	 *
	 * @access public
	 * @param string $action
	 * @param string [$method]
	 * @param string [$text]
	 * @param integer [$activate]
	 * @param integer [$activate]
	 * @return void
	 */
	function updateAction($action, $method = null, $text = null, $activate = null, $rule = null){
		if(isset($this->actions[$action])){
			$this->actions[$action] = array(
					($method 	!== null ? $method 	 : $this->actions[$action][0]),
					($activate 	!== null ? $activate : $this->actions[$action][1]),
					($text 		!== null ? $text 	 : (isset($this->actions[$action][2]) ? $this->actions[$action][2] : '')),
					($rule 		!== null ? $rule 	 : (isset($this->actions[$action][3]) ? $this->actions[$action][3] : ''))
			);
		}
	}
	/**
	 * Delete existing action
	 *
	 * @access public
	 * @param string $action
	 * @return void
	 */
	function delAction($action){
		if($action != 'list' && isset($this->actions[$action])){
			unset($this->actions[$action]);
		}
	}
	/**
	 * Delete all action without 'list'
	 *
	 * @access public
	 * @return void
	 */
	function delAllActions(){
		foreach ($this->actions as $k => $v){
			if($k != 'list') $this->delAction($k);
		}
	}
	/**
	 * Get action list
	 *
	 * @access public
	 * @return hashmap
	 */
	function getActions(){
		return $this->actions;
	}
	/**
	 * Get action name from url and set it in system container
	 *
	 * @access public
	 * @return void
	 */
	function loadURLActions(){
		if(!$this->id){
			if($this->id = BASIC_URL::init()->request($this->prefix.'id', 'Int')){
				$this->system[] = $this->prefix.'id';
				if(is_array($this->id) && count($this->id) == 1){
					$this->id = $this->id[0];
					BASIC_URL::init()->set($this->prefix.'id', $this->id);
				}
			}
		}
		foreach($this->actions as $k => $v){
			if(BASIC_URL::init()->request($this->prefix.$this->urlCmdName.$k)){
				$this->cmd = $k;
				$this->system[] = $this->miss[] = $this->prefix.$this->urlCmdName.$k;
				break;
			}
		}
		if(!$this->cmd){
			if($this->cmd = BASIC_URL::init()->request($this->prefix.$this->urlCmdName)){
				$this->system[] = $this->miss[] = $this->prefix.$this->urlCmdName;
			}
		}
	}
	/**
	 * If it's set action - run it
	 *
	 * @access public
	 * @return mix
	 */
	function listenerActions(){
		if($this->cmd){
			if($tmp = $this->action($this->cmd, $this->id)){
				return $tmp;
			}
		}
		return '';
	}
	/**
	 * Return HTML with empty form
	 *
	 * @access public
	 * @return string
	 */
	function ActionFormAdd(){
		return $this->FORM_MANAGER();
	}
	/**
	 * Return HTML with fill data for row with $id
	 *
	 * @access public
	 * @param integer $id
	 * @return string
	 */
	function ActionFormEdit($id = 0){
		if($id && !$this->messages){
			$this->ActionLoad($id);
		}
		return $this->FORM_MANAGER();
	}
	/**
	 * Create fimple HTML with the info from specific components record.
	 *
	 * @access public
	 * @param integer $id
	 * @return string
	 */
	function ActionDetails($id){
		$this->delAction('save');

		BASIC_TEMPLATE2::init()->set(array(
		'fields' => $this->getRecord($id),
		'buttons_bar' => $this->buttonActionsBar(3)
		), $this->template_details);

		$tpl = ''; try{
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_details);
		}catch(Exception $e){
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_details_default, $this->template_details);
		}

		return $this->formHtmlGenerator(array(
				'action' => BASIC_URL::init()->link(BASIC::init()->scriptName()),
				'method' => 'post',
				'name' => $this->prefix.'_'.get_class($this).'_details'
		), $tpl);
	}
	/**
	 * Insert or Update record from component
	 *
	 * @access public
	 * @param integer $id
	 * @return integer|boolean
	 */
	function ActionSave($id = 0){
		if($id){
			return $this->ActionEdit($id);
		}else{
			return $this->ActionAdd();
		}
	}
	/**
	 * Add new record in component db table
	 *
	 * @access public
	 * @return boolean
	 * @see Component::ActionAdd()
	 */
	function ActionAdd(){
		if($this->_ordering){
			$rdr = BASIC_SQL::init()->read_exec(" SELECT MAX(`order_id`)+1 AS `max` FROM `".$this->base."` ");
			$rdr->read();
			$this->setDataBuffer("order_id", (int)$rdr->field('max'));
		}
		return parent::ActionAdd();
	}
	/**
	 * Load data row  from form in data buffer
	 * WARNING : this method is wanting optimization ...
	 *
	 * @access private
	 * @param integer $id
	 * @return void
	 */
	function ActionLoad($id = 0){
		if(!$id) $id = (int)$this->id;

		$row = $this->getRecord($id, 'row', true);

		foreach($this->fields as $k => $v){
			if(isset($this->fieldsForeign[$k])){
				$this->dataBuffer[$k] = $this->fieldsForeign[$k]->load($id);
			}else if(!isset($row[$k]) || $v[3] == 'none'){
				//$this->dataBuffer[$k] = '';
			}else{
				$this->dataBuffer[$k] = BASIC_URL::init()->other($row[$k], null,
					$this->cleanerDecision($v[3], false, $v[7])
				);
			}
		}
		if($this->dataBuffer){
			$this->dataBuffer[$this->field_id] = $row[$this->field_id];
		}
		
		if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG'])){
			foreach($this->nasional as $n => $v){
				if(!isset($this->fields[$n])) continue;

				foreach(BASIC_LANGUAGE::init()->language as $k => $l){
					if(isset($row[$n.'_'.$k])){
						$this->setDataBuffer($n.'_'.$k, BASIC_URL::init()->other($row[$n.'_'.$k], null,
								$this->cleanerDecision($this->fields[$n][3],false,$this->fields[$n][7])
						));
					}else{
						$this->setDataBuffer($n.'_'.$k, '');
					}
					if($k == BASIC_LANGUAGE::init()->current()){
						$this->setDataBuffer($n, $this->getDataBuffer($n.'_'.$k));
					}
				}
			}
		}
	}
	/**
	 * Call action error
	 *
	 * @access public
	 * @param integer $id
	 * @return mix
	 */
	function ActionError($id){
		foreach ($this->dataBuffer as $k => $v){
			$fname = $k;
			if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG'])){
				$tmp = '';
				foreach(BASIC_LANGUAGE::init()->language as $lk => $l){
					$tmp = str_replace("_".$lk, '', $k);
					if(isset($this->nasional[$tmp])){
						$fname = $tmp; break;
					}
				}
			}

			if($v instanceof BasicUpload){
				if($id && isset($this->fields[$fname])){
					$res = BASIC_SQL::init()->read_exec(" SELECT `".$k."` FROM `".$this->base."` WHERE `".$this->field_id."` = ".$id." ", true);
					 
					$this->dataBuffer[$k] = BASIC_URL::init()->other($res[$k], null,
							$this->cleanerDecision($this->fields[$fname][3], false, $this->fields[$fname][7])
					);
				}else{
					$this->dataBuffer[$k] = '';
				}
			}else{
				if(isset($this->fields[$fname])){
					$this->dataBuffer[$k] = BASIC_URL::init()->other($v, null,
							$this->cleanerDecision($this->fields[$fname][3], false, $this->fields[$fname][7])
					);
				}else{
					$this->dataBuffer[$k] = $v;
				}
			}
		}
		return $this->action($this->errorAction, $id, false);
	}
	/**
	 * Extra method for change boolen field
	 * Syntax action (Un)(Action)
	 * (Un) is key for off state
	 * strtolower(Action) is name changed field
	 *
	 * @access public
	 * @param integer $id
	 * @param string $action
	 * @version 0.3
	 * @return void
	 */
	function ActionBoolen($id, $action){
		$key = 1;
		preg_match("/^(Un)?(.+)$/", $action, $reg);

		if($reg[1]) $key = 0;

		if(!$id){
			$id = (int)BASIC_URL::init()->request($this->prefix.'id');
		}else{
			if(!is_array($id)) $id = array($id);
		}
		BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `".strtolower($reg[2])."` = ".$key." WHERE `".$this->field_id."` IN ".($id?"(".implode(",",$id).")":"(0)")." ");
	}
	/**
	 * Change order rows records
	 * WARNING :: This functionality no work on MsSql serrver !!!
	 *
	 * @access public
	 * @param integer $id
	 * @param string $action
	 * @return void
	 * @version 0.3 beta
	 */
	function ActionOrder($id,$action){
		if(!$id) return;

		BASIC_SQL::init()->exec(" SET @order_id = 0 , @new_id = 0 ,@new_order = 0 , @mx = 0 , @cnt = 0 , @id_num = ".$id."; ");
		$rdr = BASIC_SQL::init()->read_exec("
				SELECT @order_id:=c.`order_id` AS `ord`,
				       @mx:= max(d.`order_id`) AS `max`,
				       @cnt:= count(d.id)      AS `rows`
				FROM `".$this->base."` d LEFT JOIN `".$this->base."` c ON c.`".$this->field_id."` = @id_num
			    GROUP BY c.`order_id`;
		");
		$rdr->read();

		$err = BASIC_ERROR::init()->error();
		if($err['code'] == 1054){
			BASIC_SQL::init()->exec("ALTER TABLE `".$this->base."` ADD COLUMN `order_id` int(11) NOT NULL DEFAULT 0 ");
			BASIC_ERROR::init()->clean();
			$this->ActionOrder($id, $action);
			return ;
		}
		$flag = 1;
		if($action == 'order_up') $flag = -1;
		if($rdr->field('ord') > -1 && ($rdr->field('ord') < $rdr->field('max') || $flag < 0)){
			BASIC_SQL::init()->exec(" SET @i = @order_id; ");
			BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET order_id = @i:= @i + 1 WHERE `order_id` = @order_id AND `".$this->field_id."` != @id_num; ");
			if($flag > 0){
				BASIC_SQL::init()->exec(" SELECT @new_order:=order_id,@new_id:=`".$this->field_id."` FROM `".$this->base."`  WHERE `order_id` > @order_id ORDER BY `order_id` LIMIT 1; ");
			}else{
				BASIC_SQL::init()->exec(" SELECT @new_order:=order_id,@new_id:=`".$this->field_id."` FROM `".$this->base."`  WHERE `order_id` < @order_id ORDER BY `order_id` DESC LIMIT 1; ");
			}
			BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `order_id` = @order_id WHERE `".$this->field_id."` = @new_id; ");
			BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `order_id` = @new_order WHERE `".$this->field_id."` = @id_num; ");
		}
	}
	/**
	 * @todo NEED REVIEW THIS ACTIONS. IN NEXT VERSION THIS ACTION WILL BE HIDE BY DEFAULT.
	 *
	 * @access public
	 * @param integer $id
	 * @param string $action
	 * @param boolean|string $is_not_url_column_name
	 * @return integer
	 */
	function ActionFileRemove($id, $action, $is_not_url_column_name = ""){
		if(!$is_not_url_column_name){
			$column_name = BASIC_URL::init()->request($this->prefix.'fname', 'addslashes', 255);
		}else{
			$column_name = $is_not_url_column_name;
		}

		if(isset($this->fields[$column_name]) && $this->fields[$column_name][1]) return false;

		$file_name = BASIC_SQL::init()->read_exec(" SELECT `".$column_name."` as `file_name`FROM `".$this->base."` WHERE 1=1 AND `".$this->field_id."` = ".(int)$id." ",true);

		/**
		 * Find real field name.
		*/
		$field_column = $column_name;
		if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG'])){
			foreach($GLOBALS['BASIC_LANG']->language as $k => $l){
				$field_column = str_replace("_".$k,'',$field_column);
			}
		}

		$file_settings = $this->getField($field_column);
		$file_settings['attributes'] = BASIC_GENERATOR::init()->convertStringAtt($file_settings['attributes']);

		BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `".$column_name."` = '' WHERE 1=1 AND `".$this->field_id."` = ".(int)$id." ");
		BASIC::init()->imported('upload.mod');
		$fl = new BasicUpload(null);
		$fl->upDir = $file_settings['attributes']['dir'];
		if(isset($file_settings['attributes']['onDelete'])){
			$fl->onDelete = $file_settings['attributes']['onDelete'];
		}
		$fl->delete($file_name['file_name']);

		$this->system[] = $this->prefix.'fname';
		$this->system[] = $this->prefix.'oldcmd';

		if(!$is_not_url_column_name){
			$old_cmd = BASIC_URL::init()->request($this->prefix.'oldcmd','addslashes',255);
			BASIC_URL::init()->redirect(BASIC::init()->scriptName(), BASIC_URL::init()->serialize($this->system).
			($old_cmd ? $this->prefix.$this->urlCmdName.'='.$old_cmd.'&' : '').
			$this->prefix.'id='.$id
			);
		}
		return $id;
	}
	/**
	 * Return HTML for list view
	 *
	 * @access public
	 * @return string
	 */
	function ActionList(){
		return $this->LIST_MANAGER();
	}
	/**
	 * Created HTML form manager
	 *
	 * @access public
	 * @version 1.4
	 * @since 27.02.2007
	 * @param string [$form_attribute]
	 * @return string
	 */
	function FORM_MANAGER($form_attribute = array()){
		BASIC::init()->imported('template.mod');

		$att = BASIC_GENERATOR::init()->convertStringAtt($form_attribute);

		foreach($this->fields as $v){
			if($v[5] != 'none' && $v[5] != 'hidden' && BASIC_GENERATOR::init()->getControl($v[5])->isFileUpload()){
				$att["enctype"] = "multipart/form-data";
				break;
			}
		}

		if(!isset($att['action'])) $att['action'] = BASIC_URL::init()->link(BASIC::init()->scriptName());
		if(!isset($att['method'])) $att['method'] = 'post';
		if(!isset($att['name']) && $this->prefix){
			$att['name'] = $this->prefix;
		}

		BASIC_TEMPLATE2::init()->set($this->dynamicLingualFormSupport(), $this->template_form);
		BASIC_TEMPLATE2::init()->set(array(
		$this->templates['form-dynamic'] => $this->buildForm(),
		$this->templates['form-vars']['buttons_bar'] => $this->buttonActionsBar(),
		$this->templates['form-vars']['prefix'] => $this->prefix
		), $this->template_form);

		$tpl = ''; try{
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_form);
		}catch(Exception $e){
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_form_default, $this->template_form);
		}
		return $this->formHtmlGenerator($att, $tpl);
	}
	/**
	 * Add the fields that will miss into cross UI interfaces requests.
	 *
	 * @param string $name
	 * @param boolean [$miss] - if null (default value) will add in both.
	 */
	function buildSpecialRequestFields($name, $miss = null, $lang = ''){
		$_name = $name;
		if($lang){
			$_name .= "_".$lang;
		}

		if(isset($this->fields[$name])){
			if($ctrl = BASIC_GENERATOR::init()->getControl($this->fields[$name][5])){
				foreach($ctrl->fieldNames() as $v){
					if($miss === null){
						$this->miss[] = $this->system[] = $v.$this->prefix.$_name;
					}else if($miss){
						$this->miss[] = $v.$this->prefix.$_name;
					}else{
						$this->system[] = $v.$this->prefix.$_name;
					}
				}
			}
		}

		if($miss === null){
			$this->miss[] = $this->system[] = $this->prefix.$_name;
		}else if($miss){
			$this->miss[] = $this->prefix.$_name;
		}else{
			$this->system[] = $this->prefix.$_name;
		}
	}
	/**
	 * Get array with fields needed for parsing form
	 *
	 * @access public
	 * @return array
	 */
	function buildForm(){
		$fields = array();
		foreach($this->fields as $v){
			$tag = $v[5];

			$attribute = array();
			if(isset($v[6])) $attribute = $v[6];
				

			if($v[1] && !isset($attribute['lang'])) $attribute['lang'] = 'on';

			$length = (int)$v[2];
			if($length && !isset($attribute['maxlength'])){
				$attribute['maxlength'] = $length;
			}
				
			$tagPHP = '';
				
			if($tag == 'none'){
				continue;
			}else if($tag == 'hidden'){
				if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$v[0]])){
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){
						if($v['default'] && !$this->id && !isset($this->dataBuffer[$v[0]."_".$k])){
							$this->setDataBuffer($v[0]."_".$k, $v['default']);
						}
						$this->hidden_el .= BASIC_GENERATOR::init()->controle('input', $this->prefix.$v[0]."_".$k, $this->getDataBuffer($v[0]."_".$k), array('type' => 'hidden'));

						//$this->miss[] = $this->prefix.$v[0]."_".$k;
						$this->buildSpecialRequestFields($v[0], true, $k);
					}
				}else{
					if($v['default'] && !$this->id && !isset($this->dataBuffer[$v[0]])){
						$this->setDataBuffer($v[0], $v['default']);
					}
					$this->hidden_el .= BASIC_GENERATOR::init()->controle('input', $this->prefix.$v[0], $this->getDataBuffer($v[0]), array('type' => 'hidden'));
						
					//$this->miss[] = $this->prefix.$v[0];
					$this->buildSpecialRequestFields($v[0], true);
				}
			}else{
				if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$v[0]])){
					if(BASIC_LANGUAGE::init()->number() > 1){
						if(isset($attribute['class'])){
							$attribute['class'] .= ' form_lingual_field';
						}else{
							$attribute['class'] = 'form_lingual_field';
						}
					}
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){

						if($k != BASIC_LANGUAGE::init()->current()){
							if(isset($attribute['style']))
								$attribute['style'] .= 'display:none;';
							else
								$attribute['style'] = 'display:none;';
						}else{
							if(isset($attribute['style']))
								$attribute['style'] .= 'display:block;';
							else
								$attribute['style'] = 'display:block;';
						}
						$attribute['lang'] = $k;
						if(BASIC_GENERATOR::init()->getControl($tag)->isFileUpload()){
							$attribute = $this->_createFileUploadButton($v[0].'_'.$k, $attribute);
								
							if($this->getDataBuffer($v[0].'_'.$k) instanceof BasicUpload){
								$this->setDataBuffer($v[0].'_'.$k, '');
							}
						}
						if($v['default'] && !$this->id && !isset($this->dataBuffer[$v[0].'_'.$k])){
							$this->setDataBuffer($v[0].'_'.$k, $v['default']);
						}
						$tagPHP .= BASIC_GENERATOR::init()->controle($tag, $this->prefix.$v[0].'_'.$k, $this->getDataBuffer($v[0].'_'.$k), $attribute);

						//$this->miss[] = $this->prefix.$v[0].'_'.$k;
						$this->buildSpecialRequestFields($v[0], true, $k);
					}
				}else{
					if(BASIC_GENERATOR::init()->getControl($tag)->isFileUpload()){
						$attribute = $this->_createFileUploadButton($v[0], $attribute);

						if($this->getDataBuffer($v[0]) instanceof BasicUpload){
							$this->setDataBuffer($v[0], '');
						}
					}
					if($v['default'] && !$this->id && !isset($this->dataBuffer[$v[0]])){
						$this->setDataBuffer($v[0], $v['default']);
					}
					if($v[1]) unset($attribute['delete_btn']);
						
					$tagPHP .= BASIC_GENERATOR::init()->controle($tag, $this->prefix.$v[0], $this->getDataBuffer($v[0]), $attribute);
						
					//$this->miss[] = $this->prefix.$v[0];
					$this->buildSpecialRequestFields($v[0], true);
				}
			}
			if(!$tagPHP) continue;

			$message = (isset($v['messages'][(int)$this->getMessage($v[0])]) ? $v['messages'][(int)$this->getMessage($v[0])] : $v['messages'][0]);
				
			$fields[$v[0]] = array(
					$this->templates['form-vars']['perm'] 	 => ($v[1] ? $v[1] : ""),
					$this->templates['form-vars']['label']	 => $v[4],
					$this->templates['form-vars']['ctrl']	 => $tagPHP,
					$this->templates['form-vars']['message'] => $message,
					$this->templates['form-vars']['value'] 	 => $this->getDataBuffer($v[0])
			);
		}
		return $fields;
	}
	/**
	 * Generate form HTML ussing attributes
	 *
	 * @access public
	 * @param array $attributes
	 * @param string $body
	 * @return string
	 */
	function formHtmlGenerator($attributes, $body){
		return BASIC_GENERATOR::init()->form($attributes,
				$body.			 "\n<!-- hidden elements -->\n".
				$this->hidden_el."\n<!-- form state -->\n".
				($this->useSaveState ? BASIC_URL::init()->serialize($this->miss, 'post') : '')
		);
	}
	/**
	 * Help method for buildForm
	 *
	 * @access private
	 * @param string $name
	 * @param string $attribute
	 * @return array
	 */
	protected function _createFileUploadButton($name, $attribute){
		$attribute = BASIC_GENERATOR::init()->convertStringAtt($attribute);

		if(!$this->getDataBuffer($name)){
			unset($attribute['delete_btn']);
		}else{
			if(isset($attribute['delete_btn'])){
				$delete_btn = BASIC_GENERATOR::init()->convertStringAtt($attribute['delete_btn']);
				$delete_btn['href'] = BASIC_URL::init()->link(BASIC::init()->scriptName(), BASIC_URL::init()->serialize($this->system).$this->prefix.$this->urlCmdName.'=fileRemove&'.$this->prefix.'fname='.$name.'&'.$this->prefix.'id='.$this->id.($this->cmd ? '&'.$this->prefix.'oldcmd='.$this->cmd : ''));
				if(isset($delete_btn['class'])){
					$delete_btn['class'] .= ' FileRemove';
				}else{
					$delete_btn['class'] = 'FileRemove';
				}
				if(!isset($delete_btn['id'])){
					$delete_btn['id'] = 'cmdFileRemove';
				}
				$attribute['delete_btn'] = $delete_btn;
			}
		}
		return $attribute;
	}
	/**
	 * Create system variables
	 *
	 * @access public
	 * @return void
	 */
	function startManager(){
		$this->loadURLActions();

		foreach($this->fields as $k => $v){
			if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$v[0]])){
				foreach(BASIC_LANGUAGE::init()->language as $lk => $l) $this->system[] = $this->prefix.$k.'_'.$lk;
			}
			if(isset($v[0])) $this->system[] = $this->prefix.$k;
		}
		if($this->_ordering && !$this->sorting){
			BASIC::init()->imported('bars.mod');
			$this->sorting = new BasicSorting($this->base.'.order_id', false, $this->prefix);
		}
		if(!$this->paging){
			$this->paging = new BasicComponentPaging($this->prefix);
		}
	}
	/**
	 * Create element for to map declaration
	 * if($field == '' && $colback != '') call user function with param table id key
	 *
	 * proto type user function
	 * 		function protoTypeUser($id){
	 * 			# definitions ...
	 * 		}
	 *
	 * @access public
	 * @return string
	 */
	function _map(){
		$tmp = '#'.microtime();
		if(isset($this->map[$tmp])){
			$tmp = $this->_map();
		}
		return $tmp;
	}
	/**
	 * Ordering control
	 *
	 * @access public
	 * @param boolean [$status]
	 * @return boolen _ordering
	 */
	function ordering($status = null){
		if($status === false){
			$this->_ordering = false;
			$this->unsetField('order_id');
				
			$this->delAction('order_up');
			$this->delAction('order_down');
		}elseif($status === true){
			$this->_ordering = true;
			$this->setField("order_id",array(
					'dbtype' => 'int',
					'length' => 11,
					'formtype' => 'hidden'
			));
			$this->addAction('order_up', 'ActionOrder', '', 0);
			$this->addAction('order_down', 'ActionOrder', '', 0);
		}
		return $this->_ordering;
	}
	function useOrdering(){
		return !!$this->_ordering;
	}
	/**
	 * Describing and setting of list columns information
	 *
	 * @access public
	 * @param string $field	db column name, if begins with '#' do not serch it in database, it's generated from code
	 * @param string $header column header text
	 * @param string|array $colback  method or function formating content in the list view column
	 * @param string|array $attribute setting for the column, like width
	 * @param boolen $sort if it's allowed column ordering
	 * @return void
	 */
	function map($field, $header, $colback='', $attribute='', $sort=true){

		if(!is_numeric($field) && ($field == '' || $field == '#')){
			$field = $this->_map();
		}else if($this->sorting && $sort){
			$header = $this->sorting->sortlink($field, $header);
		}
		$this->map[$field] = array($header, $colback, $attribute, true);
	}
	/**
	 * Remove column from list view table
	 *
	 * @access public
	 * @param $string $name
	 * @return void
	 */
	function unmap($name){
		if(is_array($name)){
			foreach($name as $v){
				unset($this->map[$v]);
			}
		}else{
			unset($this->map[$name]);
		}
	}
	/**
	 * Add column different of exist components fields in the List UI.
	 *
	 * @access public
	 * @param string/array $name
	 * @return void
	 */
	function addMapElement($name){
		if(is_array($name)){
			foreach ($name as $v){
				$this->map[$v] = array(null,null,null,false);
			}
		}else{
			$this->map[$name] = array(null,null,null,false);
		}
	}
	/**
	 * Check for action and run it, used in start panel
	 *
	 * @access public
	 * @return string
	 */
	function createInterface(){
		if($this->cmd){
				
			// Support use component exeptions
			try{
				$t = $this->listenerActions();
			}catch(Exception $e){
				BASIC_ERROR::init()->append($e->getCode(), $e->getMessage());
			}
				
			if($t && is_string($t)){
				return $t;
			}else{
				if(!$this->messages && !BASIC_ERROR::init()->exist(array('fatal', 'warning'))){
					$this->ActionBack();
				}else{
					if($this->errorAction){
						return $this->action('error', $this->id);
					}
				}
			}
		}
		if(isset($this->actions['list'])){
			return $this->action("list", $this->id);
		}else{
			throw new Exception("Action 'list' is requare to exist!");
		}
	}
	/**
	 * Sql query generator
	 *
	 * @version 0.3
	 * @access public
	 * @param string [$criteria]
	 * @param boolean [$include_all]
	 * @return string
	 */
	function select($criteria = '', $include_all = false){
		$tmp = "`".$this->base."`.`".$this->field_id."`";
		foreach ($this->fields as $k => $v){
			if($v[3] != 'none'){
				$tmp .= ",\n";
					
				if(isset($this->fieldsForeign[$k])){
					$tmp .= " '' AS `".$k."`";
				}else if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$k])){
					$tmp .= " `".$this->base."`.`".$k."_".BASIC_LANGUAGE::init()->current()."` AS `".$k."` ";
					$criteria = preg_replace("/[` ]".$k."[` ]/", "`".$k."_".BASIC_LANGUAGE::init()->current()."`", $criteria);
				}else{
					if(strpos($k,' ') !== false){
						$tmp .= " ".$k." "; // is sub query
					}else{
						$tmp .= "`".$this->base."`.`".$k."` ";
					}
				}
			}
		}
		$query = " SELECT ".
				($include_all ? '`'.$this->base."`.*" : "").
				($include_all && $tmp ? ",\n" : '').$tmp.
				" FROM `".$this->base."` WHERE 1=1 ".$criteria;

		//		$tmp = str_replace("\n",'',$tmp);

		if(($this->_ordering || $this->sorting) && !preg_match("/[\n\t\r ]+order by/i", $query)){
			if($this->_ordering && !$this->sorting){
				BASIC::init()->imported('bars.mod');
				$this->sorting = new BasicSorting($this->base.'.order_id', false, $this->prefix);
			}
			if(preg_match("/[\n\t\r ]+limit /i", $query)){
				$query = preg_replace("/[\t\n\r ]+limit /i", $this->sorting->getsql()." LIMIT ", $query);
			}else{
				$query .= $this->sorting->getsql();
			}
		}
		return $query;
	}
	/**
	 * Container for foreign fields
	 * @var array
	 */
	var $_FOREING_LIST_CONTAINER = array();
	/**
	 * Fill container for foreign fields
	 *
	 * @todo the name has to be change from load_foreing_list_firld to load_foreign_list_field
	 * @param unknown_type $name
	 * @param unknown_type $id
	*/
	function load_foreing_list_firld($name,$id){
		if(isset($this->fieldsForeign[$name])){
			if(!isset($_FOREING_LIST_CONTAINER[$id])){
				$_FOREING_LIST_CONTAINER[$id] = $this->fieldsForeign[$name]->load($id);
			}
			return $_FOREING_LIST_CONTAINER[$id];
		}
	}
	/**
	 * Redirect page and send existing variables
	 *
	 * @access public
	 * @return void
	 */
	function managerRefresh(){
		$this->ActionBack();
	}
	/**
	 * Redirect from view mode to list mode
	 *
	 * @access public
	 * @return  void
	 *
	 */
	function ActionBack(){
		BASIC_URL::init()->redirect(BASIC::init()->scriptName(), BASIC_URL::init()->serialize($this->system));
	}
	/**
	 * Create log url request
	 *
	 * @access public
	 * @param string [$method]
	 * @return string
	 */
	function managerSaveVar($method = 'get'){
		return $this->useSaveState ? BASIC_URL::init()->serialize($this->system, $method) : '';
	}
	/**
	 * Create Header row
	 *
	 * @access public
	 * @param boolen [$manager]
	 * @return string
	 */
	function cmpHeaders($manager = true){
		$columns = array(); $len = 0;

		if(!$this->map){
			foreach($this->fields as $k => $v){
				if($v[3] != 'none') $this->map($k, $v[4]);
			}
		}
		foreach ($this->map as $k => $v){
			if($v[3]){
				$columns[$k] = array(
						$this->templates['list-vars']['head-dynamic-label'] => $v[0],
						$this->templates['list-vars']['head-dynamic-attr'] => ($v[2] ? BASIC_GENERATOR::init()->convertAtrribute($v[2]) : null),
						$this->templates['list-vars']['head-dynamic-selected'] => ($this->sorting && $this->sorting->selected($k)),
						$this->templates['list-vars']['head-dynamic-isdown'] => ($this->sorting && $this->sorting->isDown())
				);
				$len++;
			}
		}
		BASIC_TEMPLATE2::init()->set(array(
		$this->templates['list-vars']['head-check'] => ($manager && $this->checkForActions(1)),
		$this->templates['list-vars']['head-order'] => ($manager && $this->checkForActions(2) && $this->_ordering == true && $this->sorting ? $this->sorting->createUrlForLink('order_id') : ''),
		$this->templates['list-vars']['head-dynamic'] => $columns,
		$this->templates['list-vars']['head-length'] => $len
		), $this->template_list);
	}
	/**
	 * Create row in list view
	 *
	 * @access public
	 * @param array $array
	 * @param string/array $attribute
	 * @return string
	 */
	function cmpRows($array, $attribute = ''){

		$rows = array();
		$rl = 0;
		$class = '';
		$attribute = BASIC_GENERATOR::init()->convertStringAtt($attribute);

		$action_bar_settings = array();
		if(isset($attribute['action_bar'])){
			$action_bar_settings = $attribute['action_bar'];
			unset($attribute['action_bar']);
		}

		foreach($array as $_key_ => $val) {
			$row_level = (isset($val['__level']))? $val['__level'] : 0;
			$even_class = false;
			if($rl == 0){
				$even_class = true;
			}

			$columns = array();
			foreach($this->map as $k => $v){
				if(!$v[3]) continue;

				if(is_array($v[1])){
					$class = &$v[1][0];
					$method = $v[1][1];
				}else{
					$method = $v[1];
				}

				// foreing extension
				if(isset($this->fieldsForeign[$k])){
					$val[$k] = $this->load_foreing_list_firld($k,$val[$this->field_id]);
				}

				$column_body = '';
				if($k[0] == '#' && $v[1] != ''){ // create specifick field
					if(is_array($v[1])){
						$class = &$v[1][0];$method = $v[1][1];

						$column_body = ($class != null ? $class->$method(null,$k,$val) : $method(null,$k,$val));
					}else{
						$column_body = $this->$v[1](null,$k,$val);
					}
				}else if($k != '' && $v[1] != ''){ // formated information field
					if(is_array($v[1])){
						$class = &$v[1][0];$method = $v[1][1];

						$column_body = ($class != null ? $class->$method((isset($val[$k]) ? $val[$k] : ''), $k, $val) : $method((isset($val[$k]) ? $val[$k] : ''), $k, $val));
					}else{
						$column_body = $this->$v[1]((isset($val[$k]) ? $val[$k] : ''), $k, $val);
					}
				}else{
					$column_body = (isset($val[$k]) ? $val[$k] : '');
				}

				$columns[$k] = array(
						$this->templates['list-vars']['body-dynamic-columns-label'] => $column_body,
						$this->templates['list-vars']['body-dynamic-columns-attr'] => ($v[2] ? BASIC_GENERATOR::init()->convertAtrribute($v[2]) : '')
				);
			}
				
			// start permissions test
			$mark = true;
			$_action_bar_settings = $action_bar_settings;

			foreach ($this->actions as $a_key => $a_val){
				if($a_val[1] == -2){
					$_action_bar_settings['actions'][$a_key] = 'disable';
				}
				if(
				($a_val[1] == 1 || $a_val[1] == -1) &&
				!($a_key[0] == '_' && $a_key[1] == '_') &&
				$a_key != 'cancel' &&
				$a_key != 'add'
						){
					$mark = false;
				}
			}
			$_action_bar_settings['mark']['disabled'] = $mark;

			$val['row_number'] = $_key_;
			$rows[] = array(
					$this->templates['list-vars']['body-dynamic-rowlevel'] => $row_level,
					$this->templates['list-vars']['body-dynamic-columns'] => $columns,
					$this->templates['list-vars']['body-dynamic-evenclass'] => $even_class,
					$this->templates['list-vars']['body-dynamic-actionbar'] => $this->rowActionsBar($val, $_action_bar_settings),
					$this->templates['list-vars']['body-dynamic-rownumber'] => $_key_,
					$this->templates['list-vars']['body-dynamic-id'] => $val['id']
			);
			if($rl == 0){
				$rl = 1;
			}else{
				$rl = 0;
			}
		}
		BASIC_TEMPLATE2::init()->set(array(
		$this->templates['list-vars']['body-dynamic'] => $rows
		), $this->template_list);
	}
	/**
	 * Return action bar and paging bar
	 *
	 * @access public
	 * @return string
	 */
	function footerBar(){
		$pbar = ($this->paging ? $this->paging->getBar() : '');

		BASIC_TEMPLATE2::init()->set(array(
		$this->templates['list-vars']['action-bar'] => $this->footerActionsBar(),
		$this->templates['list-vars']['paging-bar'] => $pbar
		), $this->template_list);
	}
	/**
	 * Create listing manager. Generate html (parsing list template) using array with data from components db table
	 *
	 * @access public
	 * @param string [$criteria]
	 * @return string
	 */
	function LIST_MANAGER($criteria = ''){
		$arr = array();
		if($this->base){
			$_map = true; if(!$this->map) $_map = false;
				
			foreach($this->fields as $k => $v){
				if(isset($v['filter']) || isset($v['filterFunction'])){
					if(!$this->filter){
						$this->filter = new BasicFilter($this->prefix, $this->filter_buttons);
						$this->filter->template($this->template_filter, $this->template_filter_default);
					}
					if(isset($v['filter']) && $v['filter'] == 'auto'){
						if($v[2] == 'int'){
							$tmp = $this->getField($k);
							$tmp['filter'] = " AND (`".$this->base."`.`{1}` >= {V1} OR `".$this->base."`.`{2}` <= {V2}) ";
							$this->filter->rangeField($k, $tmp);
						}else{
							$tmp = $this->getField($k);
							$tmp['filter'] = " AND `".$this->base."`.`".$k."` LIKE '%{V}%' ";
							$this->filter->field($k, $tmp);
						}
					}else{
						$this->filter->field($k,$v);
					}
				}
				if(!$_map && $v[3] != 'none') $this->map($k, $v[4]);
			}
				
			if($this->filter){
				$this->filter->init();
				$criteria .= $this->filter->sql();
			}
			if($this->sorting) $criteria .= $this->sorting->getsql();

			$rdr = $this->read($criteria);
			if($this->maxrow != 0 && $rdr->num_rows() > $this->maxrow){

				if(!$this->paging){
					BASIC::init()->imported('bars.mod');
					$this->paging = new BasicComponentPaging($this->prefix);
				}
				$this->paging->init($rdr->num_rows(), $this->maxrow);

				$rdr = $this->read($criteria.$this->paging->getSql());
			}
			while($rdr->read()){
				$arr[$rdr->item('id')] = $rdr->getItems();
			}
		}
		return $this->compile($arr);
	}
	/**
	 * Generate html (parsing list template) using array with data from components db table. Used in LIST_MANAGER
	 *
	 * @access public
	 * @param array $arr
	 * @return string
	 */
	public function compile($arr){
		$this->cmpHeaders();
		$this->cmpRows($arr);
		$this->footerBar();

		BASIC_TEMPLATE2::init()->set(array(
		$this->templates['list-vars']['prefix'] => $this->prefix,
		$this->templates['list-vars']['cmd'] => $this->prefix.'id',
		), $this->template_list);

		$tpl = ''; try{
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_list);
		}catch(Exception $e){
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_list_default, $this->template_list);
		}
		return ($this->filter ? $this->filter->form() : '').BASIC_GENERATOR::init()->form(array(
				'enctype' => 'multipart/form-data',
				'method' => 'post',
				'name' => $this->prefix,
				'action' => BASIC_URL::init()->link(BASIC::init()->scriptName())
		),
				$tpl.
				"\n<!-- list state -->\n".
				$this->managerSaveVar('post')
		);
	}
	/**
	 * Create action buttons bars.
	 *
	 * @access public
	 * @return array
	 */
	function buttonActionsBar($type = 3){
		$arr = $this->buttonActionsBarOnly($type)+$this->dynamicLingualFormSupport();
		return $arr;
	}
	/**
	 * Add existing languages in the template for the language bar in the form UI (ActionFromAdd, ActionFormEdin).
	 *
	 * @access public
	 * @return array
	 */
	function dynamicLingualFormSupport(){
		$tpl_vars = array();
		if($this->nasional){
			$tpl_vars[$this->templates['form-action-bar-vars']['lingual-current']] = $GLOBALS['BASIC_LANG']->current();
		}
		if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && count($this->nasional) > 0 && $this->useJSLang){
			if(BASIC_LANGUAGE::init()->number() > 1){
				$linguals = array(); while($lang = BASIC_LANGUAGE::init()->listing()){
					$linguals[] = array(
							$this->templates['form-action-bar-vars']['linguals-text'] => $lang['text'],
							$this->templates['form-action-bar-vars']['linguals-key']  => $lang['code'],
							$this->templates['form-action-bar-vars']['linguals-flag'] => $lang['flag']
					);
				}
				$tpl_vars[$this->templates['form-action-bar-vars']['linguals']] = $linguals;
			}
		}
		return $tpl_vars;
	}
	/**
	 * Get array with data for each action button
	 *
	 * @access public
	 * @param array $type
	 * @return array
	 */
	function buttonActionsBarOnly($type){
		$actions = array();
		$act = false;

		foreach ($this->actions as $k => $v){
			if($v[1] == $type){

				$rule_type = '';
				$rule_text = '';

				if(isset($v[3]) && $v[3]){
					if(preg_match("/^javascript:(.+)$/", $v[3],$ex)){
						$rule_type = 'rule';
						$rule_text = $ex[1];
					}else if(preg_match("/^message:(.+)$/", $v[3],$ex)){
						$rule_type = 'message';
						$rule_text = $ex[1];
					}else{
						$rule_type = 'confirm';
						$rule_text = $v[3];
					}
				}

				$actions[] = array(
						$this->templates['form-action-bar-vars']['actions-key'] => $k,
						$this->templates['form-action-bar-vars']['actions-pkey'] => $this->prefix.$k,
						$this->templates['form-action-bar-vars']['actions-text'] => $v[2],
						$this->templates['form-action-bar-vars']['actions-disable'] => false,
						$this->templates['form-action-bar-vars']['actions-rule-type'] => $rule_type,
						$this->templates['form-action-bar-vars']['actions-rule-text'] => $rule_text
				);
				$this->miss[] = $this->prefix.$this->urlCmdName.$k;
			}
			if($v[1] == ($type*-1)){
				$actions[] = array(
						$this->templates['form-action-bar-vars']['actions-key'] => $k,
						$this->templates['form-action-bar-vars']['actions-key'] => $k,
						$this->templates['form-action-bar-vars']['actions-pkey'] => $this->prefix.$k,
						$this->templates['form-action-bar-vars']['actions-text'] => $v[2],
						$this->templates['form-action-bar-vars']['actions-disable'] => true,
						$this->templates['form-action-bar-vars']['actions-rule-type'] => '',
						$this->templates['form-action-bar-vars']['actions-rule-text'] => ''
				);
			}
			if($v[1] == $type || $v[1] == ($type*-1)) $act = true;
		}
		return array(
				$this->templates['form-action-bar-vars']['is-ie7'] => (!(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') === false)),
				$this->templates['form-action-bar-vars']['actions'] => $actions,
				$this->templates['form-action-bar-vars']['prefix']  => $this->prefix,
				$this->templates['form-action-bar-vars']['cmd']  => $this->prefix.$this->urlCmdName
		);
	}
	/**
	 * Create manager bar for listing manager
	 *	<code>
	 * 		<b> $settings signature</b>
	 * 		array(
	 * 			'mark' => array(
	 * 				// style settings
	 * 			),
	 * 			'ordering' => true, //false // view order bar
	 * 			'actions' => array(
	 * 				'add' => 'disable', //hide|enable
	 * 			)
	 *  	)
	 * 	</code>
	 *
	 * @access public
	 * @param integer $row
	 * @param array $settings
	 * @return void
	 */
	function rowActionsBar($row, $settings = array()){

		if(!isset($settings['mark'])) 	  $settings['mark'] = array();
		if(!isset($settings['ordering'])) $settings['ordering'] = true;
		if(!isset($settings['actions']))  $settings['actions'] = array();

		$id = $row[$this->field_id];
		$level = (isset($row['__level']) ? $row['__level'] : 0);
		$actions = array();

		$act = false;

		foreach ($this->actions as $k => $v){
			$rule_type = '';
			$rule_text = '';
				
			if(isset($settings['actions'][$k])){
				if($settings['actions'][$k] == 'hide') continue;
				if($settings['actions'][$k] == 'disable'){
					$v[1] = ($v[1]*-1);
				}else if($settings['actions'][$k] == 'enable'){
					if($v[1] < 0) $v[1] = ($v[1]*-1);
				}
			}
			if($v[1] == 2){
				if(isset($v[3]) && $v[3]){
					if(preg_match("/^javascript:(.+)$/",$v[3],$ex)){
						$rule_type = 'rule';
						$rule_text = $ex[1];
					}else if(preg_match("/^message:(.+)$/",$v[3],$ex)){
						$rule_type = 'message';
						$rule_text = $ex[1];
					}else{
						$rule_type = 'confirm';
						$rule_text = $v[3];
					}
				}
				$actions[] = array(
						$this->templates['row-action-bar-vars']['actions-key'] => $k,
						$this->templates['row-action-bar-vars']['actions-pkey'] => $this->prefix.$k,
						$this->templates['row-action-bar-vars']['actions-text'] => $v[2],
						$this->templates['row-action-bar-vars']['actions-link'] => $this->createActionLink($k, $id),
						$this->templates['row-action-bar-vars']['actions-disable'] => false,
						$this->templates['row-action-bar-vars']['actions-rule-type'] => $rule_type,
						$this->templates['row-action-bar-vars']['actions-rule-text'] => $rule_text
				);
			}
			if($v[1] == -2){
				$actions[] = array(
						$this->templates['row-action-bar-vars']['actions-key'] => $k,
						$this->templates['row-action-bar-vars']['actions-text'] => $v[2],
						$this->templates['row-action-bar-vars']['actions-link'] => $this->createActionLink($k, $id),
						$this->templates['row-action-bar-vars']['actions-disable'] => true,
						$this->templates['row-action-bar-vars']['actions-rule-type'] => $rule_type,
						$this->templates['row-action-bar-vars']['actions-rule-text'] => $rule_text
				);
			}
			if($v[1] == 1 || $v[1] == -1) $act = true;
		}

		$order_bar = array();
		if($this->_ordering && $settings['ordering']){
			$order_bar = array(
					array(
							$this->templates['row-action-bar-vars']['orderbar-key'] => 'order_up',
							$this->templates['row-action-bar-vars']['orderbar-link'] => $this->createActionLink('order_up', $id)
					),
					array(
							$this->templates['row-action-bar-vars']['orderbar-key'] => 'order_down',
							$this->templates['row-action-bar-vars']['orderbar-link'] => $this->createActionLink('order_down', $id)
					)
			);
		}

		return array(
				$this->templates['row-action-bar-vars']['is-ie7'] => (!(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') === false)),
				$this->templates['row-action-bar-vars']['level'] => $level,
				$this->templates['row-action-bar-vars']['rownumber'] => (isset($row['row_number']) ? $row['row_number'] : 0),
				$this->templates['row-action-bar-vars']['id'] => str_replace('-','_',$id),
				$this->templates['row-action-bar-vars']['actions'] => $actions,
				$this->templates['row-action-bar-vars']['orderbar'] => $order_bar,
				$this->templates['row-action-bar-vars']['function'] => $act ? " ".BASIC_GENERATOR::init()->convertAtrribute($settings['mark']) : '',
				$this->templates['row-action-bar-vars']['prefix'] => $this->prefix,
				$this->templates['row-action-bar-vars']['idcmd'] => $this->prefix.'id'
		);
	}
	/**
	 * Create action bar for listing manager
	 *
	 * @access public
	 * @return string
	 */
	function footerActionsBar(){
		$key = false;
		$actions = array();
			
		foreach ($this->actions as $k => $v){
			$rule_type = '';
			$rule_text = '';
				
			if($v[1] == 1){
				$key = true;
				if(isset($v[3]) && $v[3]){
					if(preg_match("/^javascript:(.+)$/",$v[3],$ex)){
						$rule_type = 'rule';
						$rule_text = $ex[1];
					}else if(preg_match("/^message:(.+)$/",$v[3],$ex)){
						$rule_type = 'message';
						$rule_text = $ex[1];
					}else{
						$rule_type = 'confirm';
						$rule_text = $v[3];
					}
				}
				$actions[] = array(
						$this->templates['action-bar-vars']['actions-key'] => $k,
						$this->templates['action-bar-vars']['actions-pkey'] => $this->prefix.$k,
						$this->templates['action-bar-vars']['actions-text'] => $v[2],
						$this->templates['action-bar-vars']['actions-link'] => $this->createActionLink($k),
						$this->templates['action-bar-vars']['actions-disable'] => false,
						$this->templates['action-bar-vars']['actions-rule-type'] => $rule_type,
						$this->templates['action-bar-vars']['actions-rule-text'] => $rule_text
				);
			}
			if($v[1] == -1){
				$key = true;
				$actions[] = array(
						$this->templates['action-bar-vars']['actions-key'] => '%'.$k,
						$this->templates['action-bar-vars']['actions-text'] => $v[2],
						$this->templates['action-bar-vars']['actions-link'] => $this->createActionLink($k),
						$this->templates['action-bar-vars']['actions-disable'] => true,
						$this->templates['action-bar-vars']['actions-rule-type'] => $rule_type,
						$this->templates['action-bar-vars']['actions-rule-text'] => $rule_text
				);
			}
		}
		if(!$key) return array();

		return array(
				$this->templates['action-bar-vars']['is-ie7'] => (!(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') === false)),
				$this->templates['action-bar-vars']['actions'] => $actions,
				$this->templates['action-bar-vars']['prefix'] => $this->prefix,
				$this->templates['action-bar-vars']['cmd'] => $this->prefix.$this->urlCmdName
		);
	}
	/**
	 * Generate lang name of the field in fromat nameField_currentLanguageCode
	 *
	 * @todo remove $as paramer, not used
	 * @param string $name
	 * @param string [$as]
	 * @return string
	 */
	function lang($name,$as=''){
		return isset($this->nasional[$name]) ? $name."_".$GLOBALS['BASIC_LANG']->current() : $name;
	}
	/**
	 * Standart Action creator
	 *
	 * @access public
	 * @param string $action
	 * @param string|integer [$id]
	 * @param array [$miss]
	 * @param string [$script]
	 * @return string
	 */
	function createActionLink($action, $id = '', $miss = array(), $script = ''){
		if(!$miss && $miss !== null) $miss = $this->system;

		return BASIC_URL::init()->link(($script ? $script : BASIC::init()->scriptName()),
				($this->useSaveState ? BASIC_URL::init()->serialize($miss) : '').
				$this->prefix.$this->urlCmdName.'='.$action.
				($id ? "&".$this->prefix."id=".$id : '')
		);
	}
	/**
	 * Clean working data buffer
	 *
	 * @access public
	 * @return array
	 */
	function cleanBedVar(){
		$tmp = $this->dataBuffer;
		foreach($tmp as $k => $v){
			if(!isset($this->fields[$k])){ //maybe this is language field
				$_f = explode("_", $k);
				$_c = count($_f);

				if($_c == 1) continue; //Not lingual and doesn't exist in fields

				unset($_f[$_c-1]);

				if($_c > 2){
					$k = implode("_", $_f);
				}else{
					$k = $_f[0];
				}
				if(isset($this->fieldsForeign[$k]) || (isset($this->fields[$k]) && $this->fields[$k][3] == 'none')){
					unset($tmp[$k]);
				}
			}else{
				if($this->fields[$k][3] == 'none' || isset($this->fieldsForeign[$k])){
					unset($tmp[$k]);
				}
			}
		}
		return $tmp;
	}
	/**
	 * Set prefix
	 *
	 * @access public
	 * @param string $text
	 * @return void
	 */
	function prefix($text){
		if($this->sorting && $this->sorting->prefix() == $this->prefix){
			$this->sorting->prefix($text);
		}
		if($this->filter && $this->filter->prefix() == $this->prefix){
			$this->filter->prefix($text);
		}
		if($this->paging && $this->paging->prefix() == $this->prefix){
			$this->paging->prefix($text);
		}
		$this->prefix = $text;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @package basic.form
 */
interface BasicFilterInterface{
	function prefix($text = null);
	function template($name, $default_template = '');
	/**
	 * Add filter buttons
	 *   action
	 *   clear
	 *
	 *  Ex: buttons(array(
	 *  	'action' => 'Filter',
	 *  	'clear'  => 'Clear filter'
	 *  ));
	 * @param array $array
	*/
	function buttons($array);
	function field($name, $context);
	function rangeField($name, $context);
	function init();
	function form($fprm_attr = '');
	function sql();
	/**
	 * @return hashmap[$field key ] => (
	 * 		string filter
	 * 		string data
	 * 		string type - valid values[match, start, middle, end]
	 * )
	*/
	function buffer();
}
class FilterDysplayComponent extends DysplayComponent{
	var $actions = array(
			'filter_action' => array('ActionEmpty', 3, 'Filter'),
			'filter_clear'  => array('ActionEmpty', 3, 'Clear'),
	);
	function ActionEmpty(){}
	function test(){
		$this->loadURLActions();
		if($this->cmd && $this->cmd != 'filter_clear'){
			parent::test();
		}
	}
}

/**
 * Generate filter form and create sql criteria.
 *
 * Usage :
 * 		<code>
 * 			$filter = new BasicFilter('uid');
 *
 * 				// set filter's fields
 * 				$filter->field('fname1', array(
 * 					'text' => 'filter text'
 * 					'formtype' => 'the valid componet support control type',
 * 					'filter' => ' AND `fname1` like "%{V}%" '
 * 					'attributes' => array(
 * 						...
 * 					)
 * 				));
 * 				$filter->field('fname2', array(
 * 					'text' => 'filter text'
 * 					'formtype' => 'the valid componet support control type',
 * 					'filter' => ' AND `fname2` in ({V}) '
 * 					'attributes' => array(
 * 						...
 * 					)
 * 				));
 *
 * 				// get values from the request
 * 				$filter->init()
 *
 * 				// get html code
 * 				$html = $filter->form();
 * 				// get sql code
 * 				$sql = $filter->sql();
 * 		</code>
 *
 * @author Evgeni Baldziyski
 * @version 2.1.0
 * @since 28.02.2007 update 15.12.2011
 * @package basic.form
 */
class BasicFilter implements BasicFilterInterface{
	public $prefix = 'filter';
	/**
	 * @access private
	 * @var FilterDysplayComponent
	 */
	protected $filter = null;
	/**
	 * Filter button name
	 * @access private
	 * @var string
	 */
	protected $button = 'Filter';
	/**
	 * Constructor
	 *
	 * @param string [$prefix]
	 * @param string [$button]
	 * @param [$template]
	 * @return void
	 */
	function __construct($prefix = '', $buttons = null, $template = ''){
		$this->filter = new FilterDysplayComponent();

		if($template) $this->filter->template_form = $template;

		$this->prefix($prefix);
		$this->buttons($buttons);
	}
	/**
	 * Set filter prefix
	 * @access public
	 * @param string [$text]
	 * @return void
	 * @see BasicFilterInterface::prefix()
	 */
	function prefix($text = null){
		if($text === null){
			return str_replace($this->prefix, '', $this->filter->prefix);
		}
		$this->filter->prefix = $this->prefix.$text;
	}
	/**
	 * Set template variables
	 * @access public
	 * @param string $name
	 * @param string [$default_template]
	 * @see BasicFilterInterface::template()
	 */
	function template($name, $default_template = ''){
		$this->filter->template_form = $name;
		$this->filter->template_form_default = $default_template;
	}
	/**
	 * Set text on filter submit button
	 * @see BasicFilterInterface::button()
	 */
	function buttons($array){
		if($array){
			$this->filter->delAllActions();
			foreach($array as $k => $v){
				$this->filter->addAction('filter_'.$k, 'ActionEmpty', $v, 3);
			}
		}
	}
	/**
	 * Set filter field
	 *
	 * @access public
	 * @param string $name
	 * @param array $context
	 * @return array
	 * @see BasicFilterInterface::field()
	 */
	function field($name, $context){
		if(isset($context['lingual']) && $context['lingual']){
			if(class_exists('BASIC_LANGUAGE')){
				if(isset($context['filter'])) $context['filter'] = preg_replace('/[ ]`?'.$name.'`?[ ]/', ' `'.$name.'_'.BASIC_LANGUAGE::init()->current().'` ', $context['filter']);
			}
			unset($context['lingual']);
		}
		$context['real_name'] = $name;
		$this->filter->setField($name, $context);
	}
	/**
	 * One field create to input fields From and To
	 *
	 * @access public
	 * @param string $name
	 * @param array $context
	 * @return void
	 * @see BasicFilterInterface::rangeField()
	 */
	function rangeField($name, $context){
		$this->field($name.'_from', $context);
		$this->field($name.'_to', $context);
	}
	/**
	 * Check request and set values to system buffer.
	 *
	 * @access public
	 * @return boolen - if exist error retrn true
	 */
	public function init(){
		return $this->filter->test();
	}
	/**
	 * Create HTML filter form.
	 *
	 * @access public
	 * @param array [$arr]
	 * @return string
	 */
	function form($arr = array()){
		return $this->filter->FORM_MANAGER($arr);
	}
	/**
	 * Create sql  filter criteria.
	 * Check for special field attributes
	 * 		"filter" - filter pattern in this format:
	 * 			for single url this sintax
	 * 				" (AND|OR) `name field` = '{V}'" result : " (AND|OR) `name field` = 'url el value'"
	 *
	 * 			for multiple url element use the follow syntax field1,field2,fieldN...
	 * 				' AND `code` in ({V})' rezultate : ' AND `code` in (5,43,20,...)'
	 * 					OR
	 * 				' AND `{V}` = 1' rezultate : 'AND `arr el 1` = 1 AND `arr el 2` = 1 .... AND `arr el N` = 1'
	 *
	 * 		"filterFunction" - fonction generating filter sql code. Use 2 case:
	 * 			Array(class, 'the class's metthod'), Array('', 'function name') or String('the current's class method')
	 *
	 * 			filterFunction signature - function (String|Integer(request value), String(the filter field's name))
	 *
	 * @access public
	 * @return string
	 */
	function sql(){
		$tmp = '';

		foreach ($this->filter->fields as $v){
			if($v[3] == 'none') continue;

			//			$this->dataBuffer[$v[0]] = $GLOBALS['BASIC_URL']->request($this->prefix.$v[0],
			//				$this->cleanerDecision($v[3],true,$v[7]),$v[2]
			//			);
			if($this->filter->getDataBuffer($v[0]) !== ''){
				if(isset($v['filterFunction'])){
					if(is_array($v['filterFunction']) && count($v['filterFunction']) == 2){

						if($v['filterFunction'][0] == ''){
							// object model
							$tmp .= $v['filterFunction']($this->filter->getDataBuffer($v[0]),$v[0]);
						}else{
							// function model
							$tmp .= $v['filterFunction'][0]->$v['filterFunction'][1]($this->filter->getDataBuffer($v[0]),$v[0]);
						}
					}else{
						$tmp .= $this->$v['filterFunction']($this->filter->getDataBuffer($v[0]), $v[0]);
					}
				}else if(isset($v['filter'])){
					$tmp .= $this->_strategy($this->filter->getDataBuffer($v[0]), $v['filter']);
				}else{
					throw new Exception('Can not find filter or filterFunction catcher.');
				}
			}
		}
		return $tmp;
	}
	/**
	 * Get data from Filter buffer
	 *
	 * @access public
	 * @return array
	 * @see BasicFilterInterface::buffer()
	 */
	function buffer(){
		$tmp = array();
		foreach($this->filter->getBuffer() as $key => $val){
			if($val){
				$tmp[$this->filter->fields[$key]['real_name']] = array(
						'data' => $val,
						'type' => $this->typeMatch($key),
						'filter' => $this->filter->fields[$key]['filter']
				);
			}
		}
		return $tmp;
	}
	/**
	 * Generate type match string from the fields property "filter". This string can help if you need to make regexp check.
	 *
	 * @access public
	 * @param string $name
	 * @return string
	 */
	function typeMatch($name){
		if(!isset($this->filter->fields[$name])) return null;

		if(strpos($this->filter->fields[$name]['filter'], '=') !== false) return 'match';
			
		$spl = preg_split("/like/i", $this->filter->fields[$name]['filter']);
		if(isset($spl[1])){
			$spl[1] = preg_replace("/['\" ]+/", "", $spl[1]);
				
			if(preg_match("/^%[^%]+%$/", $spl[1])) return 'middle';
			if(preg_match("/^%/", $spl[1])) return 'start';
			if(preg_match("/%$/", $spl[1])) return 'end';
				
			return 'match';
		}
	}
	/**
	 * Help method
	 *
	 * @access private
	 * @param array  $post    request value
	 * @param string $filter  filter declaration
	 * @return string
	 */
	protected function _strategy($post, $filter){
		$tmp = '';
		if(is_array($post)){
			if(count($post) > 0){
				if(count($post) == 1 && $post[0] == '') return '';
				if(preg_match("/\{[^\}]+\}[ ]?=/", $filter)){
					foreach($post as $arr_v){
						if($arr_v != '') $tmp .= preg_replace("/(\{[^\}]+\})/", $arr_v, $filter);
					}
				}else{
					foreach($post as $arr_v){
						//if($arr_v != '') $filter = preg_replace("/(\{[^\}]+\})/",$arr_v.",$1",$filter);
						if($arr_v !== ''){
							$filter = preg_replace("/(['\"])?(\{[^\}]+\})(['\"])?/", "$1#_#_#$3,$1$2$3", $filter);
							$filter = preg_replace("/#_#_#/", $arr_v, $filter);
						}
					}
					$tmp .= preg_replace("/\,?['\"]?{[^\}]+\}['\"]?/", '', $filter);
				}
			}
		}else{
			if($post !== '') $tmp .= preg_replace("/\{[^\}]+\}/", $post, $filter);
		}
		return $tmp;
	}
}
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
 * @version 0.3
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
}
/**
 * @author Evgeni Baldziyski
 * @version 1.1
 * @since 14.03.2008
 * @package cms.form
 * 
 * optional methods: see ModuleSettingsInterface
 * 
 * system fields:
 * 	 _created - time (in timestumb) when the row is added in database
 * 	 _author  - the logged user that is added this row
 * 	 _updated - time (in timestumb) when the row is updated
 * 	 _updater - the logged user that is updated this row
 * 	 
 * 	 _deleted - if cms setting 'SITE_DATA_DELETE' have value 'archive' this column mark the status of the row
 * 
 * 	 _parent_id   - support the tree of components. See RegisterObject::parent for more information.
 *   _parent_self - the compoent use this to make rows tree when select from the table. See CmsComponent::checkTreeActionOrder for more information. 
 *   
 *   order_id 	  - the component use this for ordering when select rows from the table. See CmsComponent::ActionOrder for more information
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
	 * Components tree register. The Builder set the components tree register here when build the current component.
	 * 
	 * Ex: if($this->model->parent){
	 * 		die("this component is child of component '".$this->model->parent->public_name."'.");
	 * }
	 * 
	 * @var RegisterObject
	 */
	var $model = null;
	/**
	 * Register parent component
	 *
	 * @deprecated 
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
	 * @deprecated
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
		$this->updateAction("add", 			null, BASIC_LANGUAGE::init()->get('add'));
		$this->updateAction("edit",			null, BASIC_LANGUAGE::init()->get('edit'));
		$this->updateAction("delete",		null, BASIC_LANGUAGE::init()->get('delete'), 1, BASIC_LANGUAGE::init()->get('are_you_sure'));
		$this->updateAction("list",			null, BASIC_LANGUAGE::init()->get('list'));
		
		$this->updateAction("cancel",		null, BASIC_LANGUAGE::init()->get('back'));	
		$this->updateAction("save",			null, BASIC_LANGUAGE::init()->get('save'));
		$this->updateAction("filter",		null, BASIC_LANGUAGE::init()->get('filter'));
		$this->updateAction("filter_clear",	null, BASIC_LANGUAGE::init()->get('filter_clear'));
		
		if($this->maxrow == -1) $this->maxrow = (int)CMS_SETTINGS::init()->get('list_max_rows');
		
		if(isset($this->cmp_perms['list'])) 	$this->cmp_perms['list']   = BASIC_LANGUAGE::init()->get('list');
		if(isset($this->cmp_perms['add'])) 		$this->cmp_perms['add']    = BASIC_LANGUAGE::init()->get('add');
		if(isset($this->cmp_perms['edit'])) 	$this->cmp_perms['edit']   = BASIC_LANGUAGE::init()->get('edit');
		if(isset($this->cmp_perms['delete'])) 	$this->cmp_perms['delete'] = BASIC_LANGUAGE::init()->get('delete');
		
			/**
		 * System fields
		 */
		$this->setField('_author', array(
			'formtype' => 'none',
			'dbtype' => 'int',
			'length' => 11
		));
		$this->setField('_updater', array(
			'formtype' => 'none',
			'dbtype' => 'int',
			'length' => 11
		));
		$this->setField('_created', array(
			'formtype' => 'none',
			'dbtype' => 'int',
			'length' => 15
		));
		$this->setField('_updated', array(
			'formtype' => 'none',
			'dbtype' => 'int',
			'length' => 15
		));
		if(CMS_SETTINGS::init()->get('SITE_DATA_DELETE') == 'archive'){
			$this->setField('_deleted', array(
				'formtype' => 'none',
				'dbtype' => 'int',
				'length' => 1
			));
		}
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
			'10'  => '10',
			'20'  => '20',
			'50'  => '50',
			'100' => '100',
			'-1'  => BASIC_LANGUAGE::init()->get('all')
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
			
			$this->addAction($obj->system_name, 'goToChild', $obj->public_name, 2);
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
		if(!isset($this->model->child[$action])){
			$msg = 'The child component "'.$action.'" does not exist!';
			if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG'])){
				$msg = BASIC_LANGUAGE::init()->get('child_component_missing');
			}
			BASIC_ERROR::init()->setError($msg);
			return;
		}
		
		$top = $this->goToParentTop($this->model);
		
		$add = array(
			$this->nameUrlVar => $action.":".$top->system_name
		);
		if($id){
			$add[$this->model->child[$action]->prefix.$this->nameUrlVarId] = $id;
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
			$this->system[] = $this->model->prefix.$this->nameUrlVarId;
			
			if($this->model->parent->parent){
				$top  = $this->goToParentTop($this->model->parent);
			}
		}
		
		$url_vars = BASIC_URL::init()->serialize($this->system);
		if(!$this->pdata || ($this->pdata && $this->model->parent && $this->model->parent->parent)){
			$url_vars .= BASIC_URL::init()->userSerialize(array(
				$this->nameUrlVar => ($this->model->parent ? $this->model->parent->system_name.($top ? ":".$top->system_name : '') : $action.($top ? ":".$top->system_name : ''))
			));
		}
		BASIC_URL::init()->redirect(BASIC::init()->scriptName(), $url_vars);
	}
	/**
	 * Redirection to the specific component.
	 * 
	 * @param string $name
	 */
	function goToComponent($id, $name){
		if(is_numeric($id)) $id = $name;
		
		$this->goToParent(0, $id);
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
			$this->parent_id = (int)BASIC_URL::init()->request($this->prefix.$this->nameUrlVarId);
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
			if((!$id && $this->parent_id) || ($id && BASIC_SQL::init()->read_exec(" SELECT 1 FROM `".$this->base."` WHERE `_parent_id` = ".$this->parent_id." AND `".$this->field_id."` = ".$id." ", true))){
				$this->setDataBuffer('_parent_id', $this->parent_id);
				
				return parent::ActionSave($id);
			}else{
				return false;
			}
		}
		
		if($id){
			$this->setDataBuffer('_updater', BASIC_USERS::init()->getUserId());
			$this->setDataBuffer('_updated', time());
		}else{
			$this->setDataBuffer('_author', BASIC_USERS::init()->getUserId());
			$this->setDataBuffer('_created', time());
		}
		
		return parent::ActionSave($id);
	}
	/**
	 * Function to delete action handler
	 * @see Component::ActionRemove()
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
				if(CMS_SETTINGS::init()->get('SITE_DATA_DELETE') == 'archive'){
					$this->_ActionRemove($id, $rules);
				}else{
					parent::ActionRemove($id, $rules);
				}
			}
		}
	}
	/**
	 * Use for the marking rows in the database table when the cms settings 'SITE_DATA_DELETE' have value 'archive'.
	 *
	 * @param int/array $id
	 * @param string $action
	 * @param string $rules
	 * @return boolean
	 */
	protected function _ActionRemove($id = 0, $action = '', $rules = ''){
		if($id){
			if(!is_array($id)) $id = array($id);
	
			if(count($id) > 0){
				foreach ($id as $v){
					$this->updateForeignStructure($v, true);
				}
				BASIC_SQL::init()->exec2(" UPDATE `".$this->base."` SET `_deleted` = 1 WHERE `".$this->field_id."` IN (".implode(",",$id).") ".$rules);
			}
			return true;
		}
		return false;
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
		return Builder::init()->getdisplayComponent($obj->system_name, $this->secure);
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
					$this->filter = new BasicFilter($this->prefix, $this->filter_buttons, $this->template_filter);
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