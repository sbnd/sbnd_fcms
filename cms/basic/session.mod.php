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
* @package basic.session
* @version 7.0.4  
*/

/**
 * @author Evgeni Baldzhiyski
 * @version 0.8
 * @since 22.01.2007
 * @package basic.session
 */
class BASIC_SESSION{
	/**
	 * Session live time
	 * @access public
	 * @var integer
	 */
	public $liveTime = 1800;
	/**
	 * method [[cookie] | url]
	 * @access public
	 * @var string
	 */
	public $method = 'cookie';
	/**
	 * Session variable name
	 * @access public
	 * @var string
	 */
	public $nameID = 'PHPSESSID';
	/**
	 * Container for session variable [db | [disk]]
	 * @access public
	 * @var string
	 */
	public $container = "disk";
	/**
	 * @access public
	 * @var string
	 */
	public $passHesh = '';
	/**
	 * @access private
	 * @var boolen
	 */
	private $starting = false;
	/**
	 * @access private
	 * @var array
	 */
	private $arrVar = array();
	/**
	 * @access private
	 * @var SessionDriversDb|SessionDriversDisk
	 */
	private $modObj = null;
	/**
	 * Get Session object using sigleton pattern
	 * @param array [$config]
	 * @return BASIC_SESSION
	 */
	static public function init($config=array()){
		if(!isset($GLOBALS['BASIC_SESS'])){
			$GLOBALS['BASIC_SESS'] = new BASIC_SESSION();
		}
		foreach ($config as $k => $v){
		    if(isset($config['started'])){
		        $GLOBALS['BASIC_SESS']->start();
		    }else{
				$GLOBALS['BASIC_SESS']->$k = $v;
		    }
		}
		return $GLOBALS['BASIC_SESS'];
	}
	/**
	 * Start using session
	 *	<code>
	 * 		BASIC::init()->imported('session.mod');
	 *		BASIC_SESSION::init(array(
	 *			'liveTime' => 7200
	 *		))->start();
	 * 	</code>
	 * @access public
	 * @return boolen
	 */
	function start(){
		if($this->starting){
			return false;
		}else{
			$this->starting = true;
		}

		switch ($this->container){
			case 'db':
				$this->modObj = new SessionDriversDb($this);
				break;
			case 'disk':
				$this->modObj = new SessionDriversDisk($this);
				break;
		}
		$this->send();
		
		register_shutdown_function(array($this, "write"));
		
		return true;
	}
	/**
	 * Invoke write() method of SessionDriversDisk or SessionDriversDb
	 * 
	 * @access public
	 * @return void
	 */
	function write(){
		$this->modObj->write();
	}
	/**
	 * Invoke distroy() method of SessionDriversDisk or SessionDriversDb
	 * 
	 * @access public
	 * @return void
	 */
	function distroy(){
		$this->modObj->distroy();
	}
	/**
	 * Set session depends on method url or cookie
	 * @access public
	 * @return void
	 */
	function send(){
		if($this->method == 'url'){
			BASIC_URL::init()->set($this->nameID, $this->passHesh, 'get');
		}else{
			setcookie($this->nameID, $this->passHesh, time()+$this->liveTime, '/');
		}
	}
	/**
	 * Get session variable
	 * 
	 * @access public
	 * @param string $name
	 * @return string
	 */
	function get($name){
		if($name && isset($_SESSION[$name]))
			return $_SESSION[$name];
		return '';
	}
	/**
	 * Set session variable
	 * 
	 * @param string $name
	 * @param string|integer $value
	 * @return void
	 */
	function set($name,$value){
		if($name)  $_SESSION[$name] = $value;
	}
	/**
	 * Remove session variable
	 * @param string $name
	 * @return void
	 */
	function un($name){
		if($name && isset($_SESSION[$name]))
			unset($_SESSION[$name]);
	}
	/**
	 * Get all session
	 * 
	 * @access public
	 * @return array
	 */
	function all(){
		return $_SESSION;
	}
	/**
	 * Get session Id
	 * 
	 * @access public
	 * @return string
	 */
	function getID(){
		return $this->passHesh;
	}
	/**
	 * Get seesion variable name
	 * 
	 * @access public
	 * @return string
	 */
	function getName(){
		return $this->nameID;
	}
	/**
	 * Get session_name = seesion_id
	 * 
	 * @access public
	 * @return string
	 */
	function createUrl(){
		return $this->getName().'='.$this->getID();
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @package basic.session
 */
interface SessionDrivers {
	function write();
	function distroy();	
}
/**
 * Session module for disk session
 *
 * @author Evgeni Baldziyski
 * @version 0.3 
 * @since 03.05.2007
 * @package basic.session
 */
class SessionDriversDisk implements SessionDrivers{
	/**
	 * @access public
	 * @var object
	 */
	var $obj = null;
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param BASIC_SESSION $obj
	 * @return void
	 */
	function __construct($obj){
		$this->obj = $obj;

		ini_set("session.gc_maxlifetime", $obj->liveTime);
		
		//$obj->nameID = session_name($obj->nameID);
		$obj->nameID = session_name();
		
		if($obj->method == 'url'){
			$_COOKIE[$obj->nameID] = BASIC_URL::init()->request($obj->nameID);
		}
		//session_name($obj->nameID);
		@session_start();
		
		$obj->passHesh = session_id();
	}
	/**
	 * Write session data and end session
	 * 
	 * @access public
	 * @return void
	 */
	function write(){
		session_write_close();
	}
	/**
	 * Desroy all data registered to a session
	 * @access public
	 * @return void
	 */
	function distroy(){
		@session_destroy();
		$_SESSION = array();
	}
}
/**
 * Session module for database session
 *
 * @author Evgeni Baldzisky
 * @version 0.1 
 * @since 03-05-2007
 * @package BASIC.SESSION
 */
class SessionDriversDb implements SessionDrivers{
	/**
	 * @access public
	 * @var object
	 */
	var $obj = null;
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param BASIC_SESSION $obj
	 * @return void
	 */
	function __construct(&$obj){

		$this->obj = &$obj;

		BASIC_SQL::init()->exec(" DELETE FROM `session` WHERE `lastLog` < ".(time()-$obj->liveTime)." ");

			BASIC_ERROR::init()->reset();
		$res = BASIC_ERROR::init()->error();

		if($res['code'] == '1146'){
			$GLOBALS['BASIC_SQL']->exec("
				CREATE TABLE `session` (
				 	`passhesh` varchar(32) NOT NULL default '0',
					`variables` text,
					`lastLog` int(15) NOT NULL default '0',
					UNIQUE KEY `key` (`passhesh`)
				 );
			");
		}
		$obj->passHesh = BASIC_URL::init()->request($obj->nameID, 'cleanURLInjection');
		
		$rdr = BASIC_SQL::init()->read_exec(" SELECT `variables` FROM `session` WHERE `passhesh` = '".$obj->passHesh."' limit 1 ",true);
		if(count($rdr) > 0){
			$_SESSION =  unserialize($rdr['variables']);
			if(!is_array($_SESSION)) $_SESSION = array();

			BASIC_SQL::init()->exec(" UPDATE session SET lastLog = " . time() . " WHERE passhesh = '".$obj->passHesh."' ");
		}else{
			$seed = (float) microtime( ) * 100000000 ;
			srand($seed);
			$obj->passHesh = md5(rand());

			BASIC_SQL::init()->exec(" INSERT INTO `session` SET `passhesh` = '".$obj->passHesh."',`lastLog` = ".time()." ");
		}
	}
	/**
	 * write session data in db
	 * @access public
	 * @return void
	 */
	function write(){
		BASIC_SQL::init()->exec(" UPDATE `session` SET `variables` = '".serialize($_SESSION)."' WHERE `passhesh` = '".$obj->passHesh."' ");
	}
	/**
	 * Remove session data from db and url synchronize
	 * @access public
	 * @return void
	 */
	function distroy(){
		BASIC_SQL::init()->exec(" DELETE FROM `session` WHERE `passhesh` = '".$this->obj->passHesh."' ");
		BASIC_URL::init()->un($obj->nameID);
		$_SESSION = array();
	}
}