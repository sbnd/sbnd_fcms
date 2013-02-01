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
 * Administrative login system. When user is logged it will be added this variables to "template_list":
 * 		is_logged - exist user
 * 		logout_link 
 * 		user_data - all current user data for more info see cms.controlers.back.Profiles
 * 
 * By default "template_list" == Builder::init()->baseTemplate
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.4
 * @since 01.09.2011
 * @package cms.controlers.back
 */
class Access extends CmsComponent implements BuilderComponentLoginInterface{
	
	/**
	 * @access public
	 * @var string
	 */
	public $base_template = 'base-login.tpl';
	/**
	 * @access public
	 * @var string
	 */
	public $sess_var_for_last_path = 'last_address';
	
	/**
	 * Forgotten password mail subject
	 * @access public
	 * @var string
	 */
	public $subject_lang_var = 'module_forgot_pass_mail_subject';
	/**
	 * Forgotten password mail template name
	 * @access public
	 * @var string
	 */
	public $forgot_mail_template = 'cms-forgot-pass.tpl';
	/**
	 * Login form template name
	 * @access public
	 * @var string
	 */
	public $template_form = 'login.tpl';
	/**
	 * Component prefix
	 * @access public
	 * @var string
	 */
	public $prefix = 'access';
	/**
	 * Path to root
	 * @access public
	 * @var string "./"
	 */
	public $go_to_top = "./";
	/**
	 * Show user's data key for user contact.
	 * @access public
	 * @var string
	 */
	public $contact_space_name = 'email';
	/**
	 * Container for user data
	 * @access private
	 * @var array
	 */
	protected $userdata = array();
	/**
	 * Run mode 
	 * 	run_mode = 0 standart login form 
	 *  run_mode = 1 forgotten password form
	 *  run_mode = 2 activate account form
	 * @access private
	 * @var integer
	 */
	protected $run_mode = 0;
	/**
	 * Forgotten password url variable - 'forgot-password'
	 * @access private
	 * @var string
	 */
	protected $forgoten_var_name = 'forgot-password';
	/**
	 * Activate account url variable - 'active-password'
	 * @access private
	 * @var string
	 */
	protected $active_var_name = 'active-password';
	public $profile_page = '';
	/**
	 * 
	 * Main function - the constructor of the component
	 * @access public
	 * @see CmsComponent::main()
	 */
	function main(){
		if(BASIC_URL::init()->test('logout')){
			if(BASIC_USERS::init()->level() == -1){
				setcookie('page_max_rows', '', time() - 3600);
			}
			BASIC_USERS::init()->logout();
			BASIC_URL::init()->redirect($this->go_to_top);
		}
		
		$this->checkMode();
		
		if($this->run_mode == 0){ //standart login mode
			$this->setField('email', array(
				'text' => BASIC_LANGUAGE::init()->get(BASIC_USERS::init()->name_column),
				'perm' => '*',
				'messages' => array(
					2 => BASIC_LANGUAGE::init()->get('invalid_email_format'),
					3 => BASIC_LANGUAGE::init()->get('not_existing_email'),
					4 => BASIC_LANGUAGE::init()->get('fault_to_generate_new_pass'),
					5 => BASIC_LANGUAGE::init()->get('new_password_sent_success')
				)
			));
			$this->setField('pass', array(
				'text' => BASIC_LANGUAGE::init()->get(BASIC_USERS::init()->pass_column),
				'formtype' => 'password',
				'perm' => '*',
				'messages' => array(
					2 => BASIC_LANGUAGE::init()->get('not_valid_data'),
					3 => BASIC_LANGUAGE::init()->get('not_have_permitions'),
					4 => BASIC_LANGUAGE::init()->get('have_already_access')
				)
			));
			$this->setField('rememberme', array(
				'text' => BASIC_LANGUAGE::init()->get('rememberme'),
				'formtype' => 'checkbox'
			));
			$this->updateAction("save", null, BASIC_LANGUAGE::init()->get('login'));
			$this->setCmpPermition('admin-access', BASIC_LANGUAGE::init()->get('profil_admin_access_label'));
		}else if($this->run_mode == 1){ // forgotten password mode
			$this->setField('email', array(
				'text' => BASIC_LANGUAGE::init()->get('email'),
				'perm' => '*',
				'messages' => array(
					2 => BASIC_LANGUAGE::init()->get('invalid_email_format'),
					3 => BASIC_LANGUAGE::init()->get('not_existing_email'),
					4 => BASIC_LANGUAGE::init()->get('fault_to_generate_new_pass'),
					5 => BASIC_LANGUAGE::init()->get('new_password_sent_success')
				)
			));			
			$this->updateAction("save", null, BASIC_LANGUAGE::init()->get('get_new_password'));
		}else{ //activate password mode
			$this->setField('code', array(
			//	'formtype' => 'hidden',
				'default' => BASIC_URL::init()->request('code', 'charAdd')
			));
			$this->setField('pass', array(
				'text' => BASIC_LANGUAGE::init()->get(BASIC_USERS::init()->pass_column),
				'formtype' => 'password',
				'perm' => '*',
			    'messages' => array(
	        		2 => BASIC_LANGUAGE::init()->get('invalid_password')
	        	)
			));			
			$this->setField('confirm_pass', array(
				'text' => BASIC_LANGUAGE::init()->get('_'.BASIC_USERS::init()->pass_column),
				'formtype' => 'password',
				'perm' => '*',
	        	'messages' => array(
	        		2 => BASIC_LANGUAGE::init()->get('not_match_pass')
	         	)
			));			
			$this->updateAction("save", null, BASIC_LANGUAGE::init()->get('active_new_password'));
		}
		
		$this->unsetCmpPermition('add');
		$this->unsetCmpPermition('list');
		
		$this->updateAction("list", 'ActionFormAdd');
		
		$this->delAction("cancel");
		$this->delAction("delete");
		$this->delAction("edit");
		
		$this->template_list = Builder::init()->baseTemplate;
		
		$this->errorAction = 'add';
		$this->specialTest = 'validator';
		
	}
	/**
	 * Set run_mode depending on url variables
	 * @access private
	 * @return void
	 */
	protected function checkMode(){
		if(BASIC_USERS::init()->checked()){
			$this->run_mode = 0;	
		}else if(BASIC_URL::init()->test($this->forgoten_var_name)){
			$this->run_mode = 1;
		}else if(BASIC_URL::init()->test($this->active_var_name)){
			$this->run_mode = 2;
		}
	}
	/**
	 * Extends parent method ActionFormAdd() like set additional information in template depends on run mode
	 * @access public
	 * @see DysplayComponent::ActionFormAdd()
	 */
	function ActionFormAdd(){
		if($this->run_mode == 0){
			if($this->check()){
				$udata = BASIC_USERS::init()->data();
				$udata["profile_link"] = BASIC_URL::init()->link('./', 'cmp=profiles&cmd=edit&id='.BASIC_USERS::init()->getUserId());
				
				BASIC_TEMPLATE2::init()->set(array(
					'is_logged' => true,
					'logout_link' => BASIC_URL::init()->link($this->go_to_top, 'logout=1'),
					'user_data' => $udata
				), $this->template_list);
			}else{
				BASIC_GENERATOR::init()->script(
					'$(document).ready(function (){'.
						'$("#'.$this->prefix.'email").focus();'.
					'});', array('head' => true)
				);
				BASIC_TEMPLATE2::init()->set(array(
					'forgoten_pass_link' => BASIC_URL::init()->link($this->go_to_top, $this->forgoten_var_name),
					'login_mode' => true
				));
				
				if(!$this->messages && !BASIC_ERROR::init()->exist()){
					$last = $_SERVER['REQUEST_URI'];
					foreach (explode("/", BASIC::init()->ini_get('root_virtual')) as $val){
						if($val){
							$last = str_replace($val."/", "", $last);
						}
					}
					BASIC_SESSION::init()->set($this->sess_var_for_last_path, $last);
				}
				
				return parent::ActionFormAdd();
			}
		}else if($this->run_mode == 1){
			BASIC_TEMPLATE2::init()->set(array(
				'forgoten_pass_link' => BASIC_URL::init()->link($this->go_to_top)
			));
			return parent::ActionFormAdd();
		}else{
			$code = BASIC_URL::init()->request('code', 'charAdd');
			if($this->getMessage('code') || !$code){
				$this->updateField('code', array(
					'text' => BASIC_LANGUAGE::init()->get('active_code'),
					'formtype' => 'input',
					'perm' => '*',
					'messages' => array(
						2 => BASIC_LANGUAGE::init()->get('invalid_active_code')
					)
				));				
			}
			
			BASIC_TEMPLATE2::init()->set(array(
				'forgoten_pass_link' => BASIC_URL::init()->link($this->go_to_top)
			));
			return parent::ActionFormAdd();
		}
	}
	/**
	 * Extends parent method ActionSave() depending on run mode and different fields for each mode
	 * @see CmsComponent::ActionSave()
	 */ 
	function ActionSave(){
		if($this->run_mode == 0){
			if(BASIC_USERS::init()->login(
				$this->dataBuffer['email'], 
				$this->dataBuffer['pass'],
				$this->dataBuffer['rememberme']
			)){
			    BASIC_SESSION::init()->set('log_last_log', BASIC_USERS::init()->get('last_log'));
			    		    
				if(BASIC_USERS::init()->level() != -1 && !BASIC_USERS::init()->getPermission($this->model->system_name, 'admin-access')){
					$this->setMessage('pass', 3);
					BASIC_USERS::init()->logout();
					
					return false;
				}
			    //set user's language
				if(BASIC_USERS::init()->get('language') && BASIC_USERS::init()->get('language') != BASIC_LANGUAGE::init()->default_()){
			    	BASIC_LANGUAGE::init()->reloadLanguage(BASIC_USERS::init()->get('language'));
				}
				
				$tmp = BASIC_SESSION::init()->get($this->sess_var_for_last_path);
				BASIC_SESSION::init()->un($this->sess_var_for_last_path);
				BASIC_URL::init()->redirect($tmp);
			}
			$this->setMessage('pass', 2);
		}else if($this->run_mode == 1){
			BASIC::init()->imported('spam.mod');
			
			$active_code = BASIC_USERS::passwordGenerator(12, true);
			
			$this->forgotTemplateVars(array(
				'user_data' => $this->userdata,
				'active_code' => $active_code,
				'active_link' => $this->genLink($this->active_var_name),
				'active_code_link' => $this->genActiveCodeLink($active_code),
				'current_language' => BASIC_LANGUAGE::init()->current()
			));

			$mail = new BasicMail(CMS_SETTINGS::init()->get('SITE_EMAIL'), ("=?UTF-8?B?".base64_encode(CMS_SETTINGS::init()->get('SITE_NAME'))."?="));
						
			$mail->subject(BASIC_LANGUAGE::init()->get($this->subject_lang_var));
			$mail->body(BASIC_TEMPLATE2::init()->parse($this->forgot_mail_template));
				
			if($mail->send($this->getDataBuffer('email'))){
				BASIC_SQL::init()->exec(" UPDATE `".BASIC_USERS::init()->db_table."` SET 
						`".BASIC_USERS::init()->perm_column."` = 0,
						`".BASIC_USERS::init()->pass_column."` = '".BASIC_USERS::passwordCripter($active_code)."' 
					WHERE `id` = ".$this->userdata['id']." ");
				
			
				// fake message for stop refresh page 
				$this->setMessage('pass', 1);
				
				BASIC_ERROR::init()->setMessage(BASIC_LANGUAGE::init()->get('new_password_sent_success'));
				
			}else{
				$this->setMessage('email', 4);
			}
		}else{
			BASIC_SQL::init()->exec(" UPDATE `".BASIC_USERS::init()->db_table."` SET 
					`".BASIC_USERS::init()->perm_column."` = 1,
					`".BASIC_USERS::init()->pass_column."` = '".BASIC_USERS::passwordCripter($this->getDataBuffer('pass'))."' 
				WHERE `id` = ".$this->userdata['id']." ");
			
			BASIC_USERS::init()->autoLogin($this->userdata['id']);
			$link = ($this->profile_page)? $this->genLink($this->profile_page): BASIC_URL::init()->link($this->go_to_top);
			BASIC_URL::init()->redirect($link);
		}
		return false;
	}
	/**
	 * Set variables in forgotten mail template
	 * @access public
	 * @param array $vars
	 */
	function forgotTemplateVars($vars){
		BASIC_TEMPLATE2::init()->set($vars, $this->forgot_mail_template);
	}
	/**
	 * Check if the user is logged
	 * 
	 * @access public
	 * @return boolean
	 */
	function check(){
		return BASIC_USERS::init()->checked();
	}
	/**
	 * Change default base template with  login base template ('base-login.tpl')
	 * @access public
	 * @return void
	 */
	function runTotalMode(){
		Builder::init()->baseTemplate = $this->base_template;
	}
	/**
	 * Validation before saveq pointed in $this->specialTest
	 * 		false - if there are not errors
	 * 		true - if there are erros, after that will be invoke error action
	 * 
	 * @access public
	 * @return boolean
	 */
	function validator(){
		if($this->run_mode == 0){
			
		}else if($this->run_mode == 1){
			if(!$this->userdata = BASIC_SQL::init()->read_exec(" SELECT * FROM `".BASIC_USERS::init()->db_table."` WHERE 
				(`".$this->contact_space_name."` = '".$this->getDataBuffer('email')."' OR
				`".BASIC_USERS::init()->name_column."` = '".$this->getDataBuffer('email')."') AND
				`".BASIC_USERS::init()->perm_column."` = 1 
			", true)){
				return $this->setMessage('email', 3);
			}		
		}else{
			if(!$this->userdata = BASIC_SQL::init()->read_exec(" SELECT * FROM `".BASIC_USERS::init()->db_table."` WHERE `".BASIC_USERS::init()->pass_column."` = '".BASIC_USERS::passwordCripter($this->getDataBuffer('code'))."' ", true)){
				return $this->setMessage('code', 2);
			}
			if(BASIC_USERS::passwordValidator($this->getDataBuffer('pass'))){
				return $this->setMessage('pass', 2);
			}
			if($this->getDataBuffer('pass') != $this->getDataBuffer('confirm_pass')){
				return $this->setMessage('confirm_pass', 2);
			}
		}
	}	
	function genLink($page=''){
		return BASIC::init()->ini_get('root_virtual').'cp/?'.$page;
	}
	function genActiveCodeLink($active_code=''){
		return BASIC::init()->ini_get('root_virtual').'cp/?code='.$active_code.'&'.$this->active_var_name;
	}
	/**
	 * Define setting for component. Values will be overwrite values of these class properties
	 * 
	 * @access public
	 * @return array
	 */
	function settingsData(){
		return array(
			'template_form'    => $this->template_form,
			'base_template'    => $this->base_template,
			'prefix' 		   => $this->prefix,
			'subject_lang_var' => $this->subject_lang_var,
			'forgot_mail_template' => $this->forgot_mail_template,
		);
	}
	/**
	 * 
	 * Desciption of fields for component setting
	 * @access public
	 * @return array
	 */
	function settingsUI(){	
		return array(
			'template_form' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_form')
			),
			'base_template' => array(
				'text' => BASIC_LANGUAGE::init()->get('base_template')
			),
			'prefix' => array(
				'text' => BASIC_LANGUAGE::init()->get('prefix')
			),
			'subject_lang_var' => array(
				'text' => BASIC_LANGUAGE::init()->get('subject_lang_var')
			),
			'forgot_mail_template' => array(
				'text' => BASIC_LANGUAGE::init()->get('forgot_mail_template')
			)
		);
	}
}