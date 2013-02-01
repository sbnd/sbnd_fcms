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
* @package cms.controlers.front
* @version 7.0.4
*/


/**
 * 
 * 
 * Login class
 * 
 * @author Evgeni Baldzhiyski
 * @package cms.controlers.front
 * 
 */
class Login extends CmsComponent implements BuilderComponentLoginInterface{
	
	/**
	 * 
	 * Template filename of the page
	 * @var string
	 * @access public
	 */
	public $template_total = 'base-login.tpl';
	/**
	 * 
	 * @todo description
	 * 
	 * @var string
	 * @access public
	 */
	public $sess_var_for_last_path = 'last_front_address';
	/**
	 * 
	 * Template filename for the form 
	 * @var string
	 * @access public
	 */
	public $template_form = 'login.tpl';
	/**
	 * 
	 * Template list
	 * @var string
	 * @access public
	 */
	public $template_list = '';
	/**
	 * 
	 * Forgot password page name
	 * @var string
	 * @access public
	 */
	public $forgotten_page = '';
	/**
	 * 
	 * Register page name
	 * @var string
	 * @access public
	 */
	public $register_page = '';
	/**
	 * 
	 * Profile page name
	 * @var string
	 * @access public
	 */
	public $profile_page = '';
	
	/**
	 * 
	 * Main function - the constructor of the component
	 * 
	 * @see CmsComponent::main()
	 */
	function main(){
		if(BASIC_URL::init()->test('logout')){
			
			BASIC_USERS::init()->logout();
			BASIC_URL::init()->redirect('/');
		}
		
		$this->prefix = 'login';

		$this->setField('email', array(
			'text' => BASIC_LANGUAGE::init()->get('email'),
			'perm' => true,
			'attributes' => array(
				'tabindex' => 1,
				'class'	=> 'input-small span3',
				'placeholder' => BASIC_LANGUAGE::init()->get('email')
			)
		));
		$this->setField('pass', array(
			'text' => BASIC_LANGUAGE::init()->get('password'),
			'formtype' => 'password',
			'perm' => true,
			'attributes' => array(
				'tabindex' => 2,
				'class'	=> 'input-small span3',
				'placeholder' => BASIC_LANGUAGE::init()->get('password')
			),
			'messages' => array(
				2 => BASIC_LANGUAGE::init()->get('not_valid_data'),
				3 => BASIC_LANGUAGE::init()->get('not_have_permitions')
			)
		));
		$this->setField('remember', array(
			'text' => BASIC_LANGUAGE::init()->get('rememberme'),
			'formtype' => 'checkbox',
			'dbtype' => 'int',
			'length' => 1,
			'attributes' =>  array('tabindex' => 3)
		));

		$this->updateAction("save", null, BASIC_LANGUAGE::init()->get('login'), 3);
		
		$this->updateAction("list", 'ActionFormAdd');
		
		$this->delAction("cancel");
		$this->delAction("delete");
		$this->delAction("edit");
		
		$this->errorAction = 'add';
		
		$this->unsetCmpPermition('list');
		$this->unsetCmpPermition('add');
		
		$this->setCmpPermition('user-access', BASIC_LANGUAGE::init()->get('profil_user_access_label'));
	}
	/**
	 * 
	 * Action save
	 * 
	 * @see CmsComponent::ActionSave()
	 */
	function ActionSave(){
		if(BASIC_USERS::init()->login($this->dataBuffer['email'], $this->dataBuffer['pass'], (int)$this->getDataBuffer('remember'))){
		    BASIC_SESSION::init()->set('log_last_log', BASIC_USERS::init()->get('last_log'));
		    
			if(BASIC_USERS::init()->level() != -1 && !BASIC_USERS::init()->getPermission($this->model->system_name, 'user-access')){
				$this->setMessage('pass', 3);
				BASIC_USERS::init()->logout();
				
				return false;
			}
		    
		//	$tmp = BASIC_SESSION::init()->get($this->sess_var_for_last_path);
				   BASIC_SESSION::init()->un($this->sess_var_for_last_path);
			
			if(BASIC_USERS::init()->get('language') && BASIC_USERS::init()->get('language') != BASIC_LANGUAGE::init()->default_()){
		    	BASIC_LANGUAGE::init()->reloadLanguage(BASIC_USERS::init()->get('language'));
			}
			
			BASIC_URL::init()->redirect();//$tmp - don't get url from session. because it contents old language
		}
		$this->setMessage('pass', 2);
		return false;
	}
	/**
	 * 
	 * 
	 * Return the html form
	 * 
	 * @see DysplayComponent::ActionFormAdd()
	 */
	function ActionFormAdd(){
		if($this->check()){
			BASIC_TEMPLATE2::init()->set(array(
				'is_logged' => true,
				'logout_link' => BASIC_URL::init()->link("./", 'logout'),
				'user_data' => BASIC_USERS::init()->data(),
				'profile_page' => $this->profile_page ? BASIC_URL::init()->link('/'.Builder::init()->pagesControler->getPageTreeByName($this->profile_page)) : ''
			), $this->template_list ? $this->template_list : Builder::init()->baseTemplate);
		}else{
			BASIC_GENERATOR::init()->script(
				'$(document).ready(function (){'.
					'$("#'.$this->prefix.'email").focus();'.
				'});', array('head' => true)
			);
			if(!$this->messages && !BASIC_ERROR::init()->exist()){
				BASIC_SESSION::init()->set($this->sess_var_for_last_path, 
					BASIC_URL::init()->link("./", BASIC_URL::init()->serialize(array(), 'get', 'get')));
			}
			BASIC_TEMPLATE2::init()->set(array(
				'register_page' => $this->register_page ? BASIC_URL::init()->link('/'.Builder::init()->pagesControler->getPageTreeByName($this->register_page)) : '',
				'forgotten_page' => $this->forgotten_page ? BASIC_URL::init()->link('/'.Builder::init()->pagesControler->getPageTreeByName($this->forgotten_page)) : ''
			), $this->template_form);
			
			return parent::ActionFormAdd();
		}
	}
	/**
	 * 
	 * Check of the user is logged in or not
	 * 
	 * @see BuilderComponentLoginInterface::check()
	 */
	function check(){
		return BASIC_USERS::init()->checked();
	}
	/**
	 * 
	 * @todo description
	 * 
	 * @see BuilderComponentLoginInterface::runTotalMode()
	 */
	function runTotalMode(){
		Builder::init()->baseTemplate = $this->template_total;
	}
	/**
	 * 
	 * Method for validation of the form data
	 * 
	 * @see CmsComponent::test()
	 */
	function test(){
		$ret = parent::test();
		
		foreach ($this->system as $k => $v){
			BASIC_URL::init()->un($v);
		}
		return $ret;
	}
	/**
	 * 
	 * Define the settings for the component. Values will be overwrite values of these class properties.
	 * @return array
	 */
	function settingsData(){
		return array(
			'template_form'  => $this->template_form,
			'template_list'  => $this->template_list,
			'template_total' => $this->template_total,
		
			'sess_var_for_last_path' => $this->sess_var_for_last_path,
		
			'forgotten_page' => $this->forgotten_page,
			'register_page' => $this->register_page,
			'profile_page' => $this->profile_page
		);
	}
	/**
	 * 
	 * Desciption of fields for component settings
	 * 
	 */
	function settingsUI(){
		$pages = Builder::init()->getdisplayComponent('pages')->getSelTree('', 0, 'name', "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		
		return array(
			'template_form' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_form')
			),
			'template_list' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_list')
			),
			'template_total' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_total')
			),
			'sess_var_for_last_path' => array(
				'text' => BASIC_LANGUAGE::init()->get('sess_var_for_last_path')
			),
			'forgotten_page' => array(
				'text' => BASIC_LANGUAGE::init()->get('forgotten_page'),
				'formtype' => 'select',
				'attributes' => array(
					'data' => $pages
				)
			),
			'register_page' => array(
				'text' => BASIC_LANGUAGE::init()->get('register_page'),
				'formtype' => 'select',
				'attributes' => array(
					'data' => $pages
				)
			),
			'profile_page' => array(
				'text' => BASIC_LANGUAGE::init()->get('profile_page'),
				'formtype' => 'select',
				'attributes' => array(
					'data' => $pages
				)
			)									
		);
	}
}