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
* @package basic.users
* @version 7.0.4
*/


/**
 * 
 * 
 * API to work with peremissions. Used from BASIC_USESR. 
 * 
 * @author Evgeni Baldzisky
 * @version 0.1 
 * @since 01-10-2009
 * @package basic.users
 */
interface PermitionInterface {
	/**
	 * 
	 * Get permissions for given user
	 * 
	 * @param int $user_id
	 * @param string/DysplayComponent $cmp_owner
	 * @param string $perm_name
	 * @return Boolean
	 */
	function getPermission($cmp_owner, $perm_name, $user_id = 0);
	/**
	 *
	 * Set permission to given user
	 * 
	 * @param int $user_id
	 * @param string/DysplayComponent $cmp_owner
	 * @param string $perm_name
	 * @param boolean $status
	 * @param int $row_id
	 */
	function setPermission($cmp_owner, $perm_name, $status, $user_id = 0);
	/**
	 * Get the name of the work table.
	 * 
	 * return string
	 */
	function getBase();
}
/**
 * @author Evgeni Baldziyski
 * @version 1.2
 * @since 27.01.2007
 * @package basic.users
 */
class BASIC_USERS{
	/**
	 * 
	 * 
	 * The profiles database name.
	 *
	 * @var string
	 */
	public $db_table     = 'profiles';	
	/**
	 * Column name for user's id (PRIMARY KEY)
	 *
	 * @var string
	 * @access public
	 */
	public $key_column   = 'id';
	/**
	 * 
	 * 
	 * Column name for user login name
	 *
	 * @var string
	 * @access public
	 */
	public $name_column  = 'email';
	/**
	 * 
	 * 
	 * Column name for user login password
	 *
	 * @var string
	 * @access public
	 */
	public $pass_column  = 'password';
	/**
	 * Column name for user's account activation. 
	 * If is empty will be ignore when loged user and make chacking.
	 * 
	 * @var string
	 * @access public
	 */
	public $perm_column  = 'active';
	/**
	 * 
	 * Column name for user's level. 
	 *
	 * @var string
	 * @access public
	 */
	public $level_column = 'level';
	/**
	 * Column name for user's last logged time. 
	 * If not exist in the table will be created automatically.
	 *
	 * @var string
	 * @access public
	 */
	public $last_log_column = 'last_log';
	/**
	 * Column name for user's session.
	 * If is not exist in the table will create column with this name. 
	 * If is empty when update the table this column will be ignored.
	 * 
	 * @var string
	 * @access public
	 */
	public $session_id_column = 'session_id';
	/**
	 * 
	 * The name of the system variable, needed for the system to check is the user is already logged in
	 *
	 * @var string
	 * @access public
	 */
	public $userSysVar = 'userid';
	/**
	 * 
	 * User domain
	 * @var string
	 * @access public
	 */
	public $userDomainVar = 'userDomain';
	/**
	 * The name of session variable stored login users time. 
	 * 
	 * @var string
	 */
	public $logTime = 'logTime';
	/**
	 * Validator/convertor for the login data(name_column && pass_column).
	 * 
	 * @var array/string
	 * @access public
	 */
	public $cleanVars = array('this', '_cleanVars');
	/**
	 * 
	 * Remember me variable - name
	 * @var string
	 * @access public
	 */
	public $rememberCookieName = 'remember_me';
	/**
	 * 
	 * Remember me password variable - passord
	 * @var string
	 * @access public
	 */
	public $rememberCookiePass = 'remember_my_data';
	/**
	 * 
	 * Developer user
	 * @var boolen
	 * @access public
	 */
	public $devUse   = false;
	/**
	 * 
	 * developer username
	 * @var string
	 * @access public
	 */
	public $devName  = "";
	/**
	 * developer password
	 * @var string
	 * @access public
	 */
	public $devPass  = "";
	/**
	 * Formatting of output data. 
	 * 
	 * @var array/string
	 * @access public
	 */
	public $outCleaner = '';
	/**
	 * @var PermitionInterface
	 * @access public
	 */
	public $permition_manager = null;
	/**
	 * 
	 * User data
	 * @var array
	 * @access private
	 */
	protected $userdata = array();
	/**
	 * 
	 * Buffer data (the submited form data)
	 * @var array
	 * @access private
	 */
	protected $bufferData = array();
	
	/**
	 * 
	 * Constructor
	 */
	function __construct(){
		BASIC::init()->imported('session.mod');
	}
	/**
	 * 
	 * Set user data
	 * @param array $config
	 * @return BASIC_USERS
	 * @static
	 */
	static function init($config=array()){
		if(!isset($GLOBALS['BASIC_USER'])){
			$GLOBALS['BASIC_USER'] = new BASIC_USERS();
		}
		foreach ($config as $k => $v){
			$GLOBALS['BASIC_USER']->$k = $v;
		}
		return $GLOBALS['BASIC_USER'];
	}
	/**
	 * @todo description
	 * @return boolen
	 * @access private
	 */
	protected $_checked= false; // cache for performance
	/**
	 * 
	 * Method for check is the user is logged or not
	 * @return mixed false for not logged or userid for logged user 
	 */
	function checked(){
		BASIC_SESSION::init()->start();
		if($this->_checked){
			return $this->userdata ? $this->getUserId() : false;
		}	
		$this->_checked = true;

		if($this->userdata) return true;
		
		if($this->devUse){
			if($this->getUserId() == -1){
				$this->_developer();
				return true;
			}
		}
		if(BASIC_URL::init()->cookie($this->rememberCookieName) && BASIC_URL::init()->cookie($this->rememberCookiePass)){
			$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->db_table."` WHERE `".$this->key_column."` = " . (int)BASIC_URL::init()->cookie($this->rememberCookieName) ." ");
			$rdr->read();
			
			if(md5($rdr->field($this->key_column).$rdr->field($this->pass_column)) != BASIC_URL::init()->cookie($this->rememberCookiePass)){
				return false;
			}else{
				BASIC_SESSION::init()->set($this->userSysVar, $rdr->item($this->key_column));
				BASIC_SESSION::init()->set($this->userDomainVar, BASIC::init()->ini_get('root_virtual'));				
			}
		}else{
			$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->db_table."` WHERE `".$this->key_column."` = " . $this->getUserId() ." ");
			$rdr->read();
		}
        $this->bufferData = $rdr->getItems();
			
		if($rdr->num_rows() != 0){
			$this->userdata = $this->cleanData($rdr->getItems());
			$this->saveLastLog();
			return $this->getUserId();
		}
		return false;
	}
	/**
	 * 
	 * Method for logging
	 * 
	 * @param string $user
	 * @param string(MD5) $pass
	 * @param boolen $remember
	 * @return boolen
	 */
	function login($user, $pass, $remember = false){
		BASIC_SESSION::init()->start();
		
		if((isset($this->bufferData[$this->name_column]) && $this->bufferData[$this->name_column] == $user) && 
			(isset($this->bufferData[$this->pass_column]) && $this->bufferData[$this->pass_column] == $pass)){
			
			return true;
		}
		// if user is developer
		if($this->devUse){
			if(($user==$this->devName) && (md5($pass)==$this->devPass)){
				$this->_developer();
				return true;
			}
		}
		if(is_array($this->cleanVars)){
			if($this->cleanVars[0] == 'this'){
				$this->cleanVars[0] = $this;
			}
			$class = $this->cleanVars[0];
			$method = $this->cleanVars[1];
			
			$user = $class->$method($user,'user');
			$pass = $class->$method($pass,'password');
		}else{
			$function = $this->cleanVars;
			$user = $function($user,'user');
			$pass = $function($pass,'password');
		}
		$query = " SELECT * FROM `".$this->db_table."` WHERE `".$this->name_column."` = '".$user."' AND `".$this->pass_column."` = '".$pass."' ";
		if($this->perm_column){
			$query .= " AND `".$this->perm_column."` = 1 ";
		}
		$rdr = BASIC_SQL::init()->read_exec($query);
		
		$rdr->read();
        $this->bufferData = $rdr->getItems();
        
		if($rdr->num_rows() != 0){

			$this->userdata = $this->cleanData($rdr->getItems());
			$this->saveLastLog();

			BASIC_SESSION::init()->set($this->userSysVar, $this->field($this->key_column));
			BASIC_SESSION::init()->set($this->userDomainVar, BASIC::init()->ini_get('root_virtual'));
			
			if($remember){
				setcookie($this->rememberCookieName, $this->field($this->key_column), time()+(60*60*24*365),'/');
				setcookie($this->rememberCookiePass, md5($this->field($this->key_column).$this->field($this->pass_column)), time()+(60*60*24*365), '/');
			}else{
				setcookie($this->rememberCookieName, '', time()-(60*60*24*365),'/');
				setcookie($this->rememberCookiePass, '', time()-(60*60*24*365),'/');
			}
		}
		return $this->getUserId();
	}
	/**
	 * 
	 * Logout method
	 * 
	 */
	function logout(){
		$this->userdata = array();
		BASIC_SESSION::init()->distroy();
		
		setcookie($this->rememberCookieName, '', time()-(60*60*24*365),'/');
		setcookie($this->rememberCookiePass, '', time()-(60*60*24*365),'/');
	}
	/**
	 * 
	 * Save last login data
	 * @access private
	 */
	protected function saveLastLog(){
		BASIC_SESSION::init()->start();
		
		$time = @date('Y-m-d H:i:s', time());
		
		BASIC_SQL::init()->exec(" UPDATE `".$this->db_table."` SET 
			`".$this->last_log_column."`= '".$time."'".
			($this->session_id_column ? ",`".$this->session_id_column."` = '".BASIC_SESSION::init()->getID()."' " : "").
		 " WHERE `".$this->key_column."` = ".$this->getUserId()." ");

			BASIC_ERROR::init()->reset();
		$res = BASIC_ERROR::init()->error();

		if($res['code'] == 1054){
			$query = BASIC_SQL::init()->getSql();
			
			BASIC_SQL::init()->createColumn($this->db_table,' `'.$this->last_log_column.'` datetime');
			
			if($this->session_id_column){
				BASIC_SQL::init()->createColumn($this->db_table,' `'.$this->session_id_column.'` varchar(32)');
			}
			
			BASIC_SQL::init()->exec($query);
			
			BASIC_ERROR::init()->clean();
		}
		$this->userdata[$this->last_log_column] = $time;
		
		BASIC_SESSION::init()->set($this->logTime, @strtotime(($this->get($this->last_log_column) ? $this->get($this->last_log_column) : time())));
	}
	/**
	 * Update db's data. If any data dosn't exist in db system - return false.
	 * 
	 * @return boolean
	 */
	function saveData(){
		$sql = ''; foreach($this->userdata as $key => $data){
			if($sql) $sql .= ','; $sql .= "`".$key."` = '".$data."'";
		}
		BASIC_SQL::init()->exec(" UPDATE `".$this->db_table."` SET ".$sql." WHERE `".$this->key_column."` = ".$this->getUserId()." ");
		
				BASIC_ERROR::init()->reset();
		return !BASIC_ERROR::init()->error();
	}
	/**
	 * 
	 * Autologin method
	 * 
	 * @param int $id
	 * @return boolean
	 */
	function autoLogin($id){
		BASIC_SESSION::init()->start();
		$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->db_table."` WHERE `".$this->key_column."` = ".(int)$id." ");
		$rdr->read();

		if($rdr->num_rows() != 0){
			$this->userdata = $this->cleanData($rdr->getItems());
			
			BASIC_SESSION::init()->set($this->userSysVar, $this->field($this->key_column));
			BASIC_SESSION::init()->set($this->userDomainVar, BASIC::init()->ini_get('root_virtual'));
			
			$this->saveLastLog();
			return true;
		}
		return false;
	}
	/**
	 * 
	 * Clean data
	 * 
	 * @todo description
	 * @param array $arr
	 * @return array
	 */
	function cleanData($arr){
		if($out = $this->outCleaner){
			if(is_array($this->outCleaner)){
				$class = $this->outCleaner[0];
				$method = $this->outCleaner[1];
				
				$arr = $class->$method($arr);
			}else{
				$method = $this->outCleaner;
				
				$arr = $method($arr);
			}
		}else{
			foreach ($arr as $k => $v){
				$arr[$k] = stripslashes($v);	
			}
		}
		return $arr;
	}
	/**
	 * Get the user id
	 *
	 * @return int
	 */
	function getUserId(){
		BASIC_SESSION::init()->start();
		
		if(BASIC_SESSION::init()->get($this->userDomainVar) == BASIC::init()->ini_get('root_virtual')){
			return (int)BASIC_SESSION::init()->get($this->userSysVar);
		}
		return 0;
	}
	/**
	 * 
	 * Get user level. If the 'level_column' is empty or not exist logged user will return -2
	 *
	 * @return int
	 */
	function level(){
		return isset($this->userdata[$this->level_column]) ? (int)$this->userdata[$this->level_column] : -2;
	}
	/**
	 * 
	 * Get user data from the existing array data
	 *
	 * @param string $name
	 * @return string/mix
	 */
	function get($name){
		return isset($this->userdata[$name]) ? $this->userdata[$name] : '';
	}
	/**
	 * 
	 * Like get() function
	 *
	 * @todo depricated
	 * @param string $name
	 * @return string/mix
	 */
	function field($name){
		return isset($this->userdata[$name]) ? $this->userdata[$name] : '';
	}
	/**
	 * 
	 * Check for existing user data object
	 * @return boolean true/false
	 */
	function exist(){
		return ($this->userdata ? true : false);
	}
	/**
	 * 
	 * Function for adding additional data to user data object
	 *
	 * @param sttring $name
	 * @param string/mix $value
	 */
	function set($name,$value){
		$this->userdata[$name] = $value;
	}
	/**
	 * 
	 * Removing data form user data object by name
	 * 
	 * @param string $name
	 */
	function un($name){
		if(isset($this->userdata[$name])){
			unset($this->userdata[$name]);
		}
	}
	/**
	 * 
	 *
	 * Get user permission by permission name
	 *
	 * @param string/DysplayComponent $cmp_owner
	 * @param string $perm_name
 	 * @return boolean
	 */
	function getPermission($cmp_owner, $perm_name){
		if($this->permition_manager){
			return $this->permition_manager->getPermission($cmp_owner, $perm_name, $this->level());
		}
		return true;
	}
	/**
	 * 
	 * 
	 * Set permission 
	 * @param string $cmp_owner
	 * @param string $perm_name
	 * @param boolean $status
 	 * @return void
	 */
	function setPermission($cmp_owner, $perm_name, $status){
		if($this->permition_manager){
			$this->permition_manager->setPermission($cmp_owner, $perm_name, $status, $this->level());
		}
	}
	/**
	 * Get data for logged user
	 * 
	 * @param int $id
	 * @return array
	 */
	function data($id = null){
		if($id !== null && $id != $this->getUserId()){
			$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->db_table."` WHERE 1=1 AND `".$this->key_column."` = " . $id ." ");
				$rdr->read();
			return $rdr->getItems();
		}else{
			return $this->userdata;
		}	
	}
	
	/**
	 * 
	 * 
	 * Add additional data to user data object for developer account
	 * @access private
	 */
	protected function _developer(){
		$this->userdata[$this->name_column] = $this->devName;
		$this->userdata[$this->level_column] = -1;
		$this->userdata[$this->key_column] = -1;
		
		BASIC_SESSION::init()->set($this->userSysVar, -1);
		BASIC_SESSION::init()->set($this->userDomainVar, BASIC::init()->ini_get('root_virtual'));
	}
	/**
	 * 
	 * 
	 * Validation of username and password
	 * Password crypt method is md5 
	 * @param string $var
	 * @return string
	 */
	protected function _cleanVars($value,$type){
		if($type == 'user'){
			$symbols = 'a-zA-Z0-9_\.@ -';

			if(preg_match("/[^".$symbols."]/",$value)){
				return "";
			}
			return $value;
		}
		if($type == 'password') return md5($value);
		
		return '';
	}
	/**
	 * 
	 * 
	 * The actual md5 encrypting
	 *
	 * @param string $pass
	 * @static
	 * @return string
	 */
	static public function passwordCripter($pass){
		if(is_array(self::init()->cleanVars)){
			if(self::init()->cleanVars[0] == 'this'){
				self::init()->cleanVars[0] = self::init();
			}
			$class = self::init()->cleanVars[0];
			$method = self::init()->cleanVars[1];
			
			return $class->$method($pass, 'password');
		}
		$function = self::init()->cleanVars;
		
		return $function($pass, 'password');
	}
  	/**
  	 * 
  	 * 
  	 * Password validator
  	 * 
  	 * Error codes{
  	 * 		0 - all ok
  	 * 		1 - empty pass
  	 * 		2 - < 8 symbols
  	 * 		3 - dont exist number
  	 * 		4 - dont exist upper letter
  	 * 		5 - dont exist upper letter
  	 * }
  	 * 
  	 * @param String $pass
  	 * @return int
  	 * @static
  	 */
  	 static public function passwordValidator($pass){//die($pass);
  		$err = 0;
  		if(!$pass){
            $err = 1;
        }else{
        	if(!preg_match("/^.{8}.?/", $pass)){
        		$err = 2;
        	}else if(!preg_match("/[0-9]+/", $pass)){
        		$err = 3;
        	}else if(!preg_match("/[A-Z]+/", $pass)){
        		$err = 4;
        	}else if(!preg_match("/[a-z]+/", $pass)){
        		$err = 5;
        	}
        }
        return $err;
  	}
  	/**
  	 * Hight security password generator.
  	 * 
  	 * @param int $length - the password length
  	 * @return string
  	 * @static
  	 */
  	static public function passwordGenerator($length = 8, $skipSpecialChars = false){
  		if($skipSpecialChars){
			$data = array('a','b','c','d','e','f','j','i','g','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
				'A','B','C','D','E','F','J','I','G','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
				'1','2','3','4','5','6','7','8','9','0'
			);	
  		}else{
			$data = array('_','-','#','@','%','~','/','^','&','*','(',')','[',']','+','|',',','.','?','!','`',';',':',
				'a','b','c','d','e','f','j','i','g','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
				'A','B','C','D','E','F','J','I','G','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
				'1','2','3','4','5','6','7','8','9','0'
			);
  		}
		$pass = ''; $l = count($data);
		for($i = 0; $i < $length; $i++){
			$pass .= $data[rand(0, $l-1)];
		}
		return $pass;
  	}
}