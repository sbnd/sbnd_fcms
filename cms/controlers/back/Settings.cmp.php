<?php
/**
* BASIC - SBND F&CMS - Framework & CMS for PHP developers
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
 * SBND CMS7 Site settings UI
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.8
 * @since 10.10.2011
 * @package cms.controlers.back
 */
class Settings extends CmsComponent{
	/**
	 * 
	 * Bradcrumbs
	 * @var string
	 * @access public
	 */
	public $breadcrumps = 'variable';
	/**
	 * 
	 * Database table name
	 * @var string
	 * @access public
	 */
	public $base = 'settings';
	/**
	 * 
	 * Language reload flag
	 * @var boolean
	 * @access private
	 */
	protected $lingual_reload = false;
	
	/**
	 * 
	 * Main function - the constructor of the component
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();
		
		$this->setField('variable',array(
			'text' => BASIC_LANGUAGE::init()->get('settings_variable_label'),
			'perm' => '*',
			'filter' => 'auto'
		));
		$this->setField('value', array(
			'text' => BASIC_LANGUAGE::init()->get('settings_value_label'),
			'filter' => 'auto',
			'cleaners' => array('htmlSecurity', 'cleanHTMLT')
		));

		$this->setField("lingual", array(
			'text' => BASIC_LANGUAGE::init()->get('settings_lingual_label'),
			'formtype' => 'radio',
			'length' => 1,
			'dbtype' => 'int',
			'attributes' => array(
				'data' => array(
					BASIC_LANGUAGE::init()->get('no'),
					BASIC_LANGUAGE::init()->get('yes')					
				),
				'onclick' => '$(this.form).submit()'
			)
		));
		
		if(BASIC_USERS::init()->level() == -1){
			$this->setField('system', array(
				'text' => BASIC_LANGUAGE::init()->get('settings_system_label'),
				'formtype' => 'radio',
				'length' => 1,
				'dbtype' => 'int',
				'attributes' => array(
					'data' => array(
						BASIC_LANGUAGE::init()->get('no'),
						BASIC_LANGUAGE::init()->get('yes')					
					)
				)			
			));
		}else{
			$this->setField('system', array(
				'formtype' => 'hidden',
				'length' => 1,
				'dbtype' => 'int'			
			));
		}
		
		$this->specialTest = 'prepareToSave';
		$this->sorting = new BasicSorting('system,variable', true, $this->prefix);
		
		while($lang = BASIC_LANGUAGE::init()->listing()){
			$this->system[] = "value_".$lang['code'];
		}
		$this->system[] = 'value_radio';
	}
	/**
	 * 
	 * Create manager bar with actions
	 * @param array $row
	 * @param array [$settings]
	 * @see DysplayComponent::rowActionsBar()
	 */
	function rowActionsBar($row, $settings = array()){
	   	if(BASIC_USERS::init()->level() != -1 && $row['system']){
			$settings = array(
	  			'mark' => array(
	  				'disabled' => 'disabled'
	  			)
		   	);
	   	}
		return parent::rowActionsBar($row, $settings);
	}
	/**
	 * 
	 * Return the html of the listing view
	 * 
	 * @see DysplayComponent::ActionList()
	 */
	function ActionList(){
		$this->map('variable', BASIC_LANGUAGE::init()->get('settings_variable_label'));
		$this->map('value', BASIC_LANGUAGE::init()->get('settings_value_label'), 'mapFormater');
		
		return parent::ActionList();
	}
	/**
	 * 
	 * @todo description
	 * @param string $value
	 * @param string $name
	 * @param array $rowData
	 * @return mixed
	 */
	function mapFormater($value, $name, $rowData){
		if($name == 'value'){
			if($rowData['variable'] == 'SITE_LANGUAGE'){
				while($lang = BASIC_LANGUAGE::init()->listing()){
					if($lang['code'] == $value){
						$value = $lang['text'];
					}
				}
				return $value;
			}
			if($rowData['variable'] == 'SITE_OPEN'){
				return BASIC_LANGUAGE::init()->get(!$value ? 'yes' : 'no');
			}
			if(!$rowData['lingual']) return $value;
			
			$hash = array();
			if($value){
				$lex = explode("||", $value);
				foreach($lex as $ex){
					$spl = explode("=", $ex);
					
					$hash[$spl[0]] = $spl[1];
				}
			}
			
			while($lang = BASIC_LANGUAGE::init()->listing()){
				if(BASIC_LANGUAGE::init()->current() == $lang['code']){
					$value = $hash[$lang['code']];
				}
			}
			return $value;
		}
	}
	/**
	 * 
	 * Generate the form
	 * @param array [$form_attribute]
	 * @see DysplayComponent::FORM_MANAGER()
	 */
	function FORM_MANAGER($form_attribute = array()){
		if($this->lingual_reload){
			$this->test();
			$this->messages = array(-1);	
		}		
		if($this->getDataBuffer('lingual')){
			$this->buildFormLingualField("value");
		}
		switch ($this->getDataBuffer("variable")){
			case 'SITE_LANGUAGE':{
				$lang_data = array();
				while($lang = BASIC_LANGUAGE::init()->listing()){
					$lang_data[$lang['code']] = $lang['text'];
				}
				$this->updateField('value', array(
					'formtype' => 'select',
					'attributes' => array(
						'data' => $lang_data
					)
				));
				break;
			}
			case 'list_max_rows':{
				$this->updateField('value', array(
					'formtype' => 'radio',
					'attributes' => array(
						'data' => $this->getMaxRowsOptions()
					)
				));
				break;
			}
			case 'SITE_START_PAGE':{
				$cnt = Builder::init()->build('contents', false);
				$this->updateField('value', array(
					'formtype' => 'select',
					'attributes' => array(
						'data' => $cnt->getSelTree('',0, 'name', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', null)
					)
				));
				break;
			}
			case 'SITE_LOGIN_MODE':{
				$this->updateField('value', array(
					'formtype' => 'radio',
					'attributes' => array(
						'data' => array(
							'none' => BASIC_LANGUAGE::init()->get('site_login_none'),
							'box'  => BASIC_LANGUAGE::init()->get('site_login_box'),
							'total'=> BASIC_LANGUAGE::init()->get('site_login_total')
						)
					)
				));
				break;
			}
			case 'SITE_OPEN':{
				BASIC_GENERATOR::init()->script("$(document).ready(function (){
					$('#value_radio input:checked').click();
				});", array('head' => true));
				
				$this->setField('value_radio', array(
					'text' => BASIC_LANGUAGE::init()->get('settings_value_label'),
					'formtype' => 'radio',
					'attributes' => array(
						'data' => array(
							BASIC_LANGUAGE::init()->get('no'),
							BASIC_LANGUAGE::init()->get('yes')
						),
						'onclick' => "$('#value_radio').parent().parent().parent().next()[!parseInt(this.value)?'show':'hide']()"
					)
				), 'variable');
				
				$this->updateField("value", array(
					'text' => ' ',
					'formtype' => 'html',
					'attributes' => array(
						'height' => 200,
						'id' => 'value'
					)
				));
				
				if(!$this->getDataBuffer('value')){
					$this->setDataBuffer('value_radio', 1);
				}
				break;
			}
		}
		if($this->getDataBuffer('system') && BASIC_USERS::init()->level() > -1){
			$this->updateField('lingual', array(
				'formtype' => 'hidden'
			));
		}
		if(BASIC_USERS::init()->level() != -1 && $this->getDataBuffer('system')){
			$this->updateField('variable', array(
				'attributes' => array(
					'readonly' => true
				)
			));
		}
		return parent::FORM_MANAGER($form_attribute);
	}
	/**
	 * Method for validation before action save
	 * @see CmsComponent::test()
	 */
	function test(){
		if((int)BASIC_URL::init()->request($this->prefix.'lingual')){
			$this->buildFormLingualField("value", true);
		}	
		return parent::test();
	}
	/**
	 * Check request for action's variables.
	 * 
	 * @see CmsComponent::loadURLActions()
	 */
	function loadURLActions(){
		parent::loadURLActions();
		
		if(BASIC_URL::init()->test($this->prefix.'lingual') && !$this->cmd){
			$this->cmd = $this->id ? 'edit' : 'add';
			$this->lingual_reload = true;
		}
	}
	/**
	 * Action save - on form submit
	 * @param int $id
	 * @see CmsComponent::ActionSave()
	 */
	function ActionSave($id){
		if($this->getDataBuffer('variable') == 'SITE_START_PAGE'){
			$page = Builder::init()->build('pages', false);
			$pid = $page->read(" AND `name` = '".$this->getDataBuffer('value')."' ")->read();
			while ($lang = BASIC_LANGUAGE::init()->listing()){
				$page->setDataBuffer('publish_'.$lang['code'], 1);
			}
			$page->ActionSave($pid['id']);
		}
		if(BASIC_URL::init()->request('value_radio')){
			$this->setDataBuffer('value', '');
		}
		return parent::ActionSave($id);
	}
	/**
	 * 
	 * @todo description
	 * @param string $name
	 * @param boolean [$is_save]
	 */
	function buildFormLingualField($name, $is_save = false){
		$this->updateField($name, array(
			'lingual' => true
		));
		
		if($is_save){}else{
			$hash = array();
			if($this->getDataBuffer($name)){
				$lex = explode("||", $this->getDataBuffer($name));
				foreach($lex as $ex){
					$spl = explode("=", $ex, 2);
					
					$hash[$spl[0]] = $spl[1];
				}
			}
			while($lang = BASIC_LANGUAGE::init()->listing()){
				$this->setDataBuffer($name."_".$lang['code'], (isset($hash[$lang['code']]) ? $hash[$lang['code']] : ''));
				
				if(BASIC_LANGUAGE::init()->current() == $lang['code']){
					$this->setDataBuffer($name, (isset($hash[$lang['code']]) ? $hash[$lang['code']] : ''));
				}
			}
		}
	}
	/**
	 * 
	 * 
	 * Custom method for validation
	 */
	function prepareToSave(){
		if($this->getDataBuffer('lingual')){
			$value = '';
			while($lang = BASIC_LANGUAGE::init()->listing()){
				if($value) $value .= "||";
				
				$value .= $lang['code']."=".$this->getDataBuffer("value_".$lang['code']);
				$this->unsetDataBuffer("value_".$lang['code']);
			}
			$this->setField('value', array(
				'lingual' => false
			));
			$this->setDataBuffer('value', $value);
		}
		if(BASIC_USERS::init()->level() != -1){
			if($this->getDataBuffer('system')){
				$this->unsetDataBuffer('variable');
			}
			if($this->id){
				$this->unsetDataBuffer('system');
			}else{
				$this->setDataBuffer('system', 0);
			}
		}
	}
	/**
	 * get settings
	 * @param string [$criteria]
	 * @param boolean [$include_all]
	 * @see DysplayComponent::select()
	 */
	function select($criteria = '', $include_all = false){
		if(BASIC_USERS::init()->level() != -1){
			$criteria = " AND `system` != -1 ".$criteria;
		}
		return parent::select($criteria, $include_all);
	}
}