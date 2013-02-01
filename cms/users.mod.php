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
* @package cms.users
* @version 7.0.4
*/

BASIC::init()->imported('users.mod');
BASIC::init()->imported('settings.mod', 'cms');

/**
 * Expand BASIC_USERS class with additional methods: refresh()  
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @since 26.10.2012
 * @package cms.users
 */
class CMS_USERS extends BASIC_USERS{
	/**
	 * 
	 * Initialisation
	 * @param array $config
	 * @static
	 */
	static function init($config = array()){
		if(!isset($GLOBALS['BASIC_USER'])){
			$GLOBALS['BASIC_USER'] = new CMS_USERS();
		}
		foreach ($config as $k => $v){
			$GLOBALS['BASIC_USER']->$k = $v;
		}
		return $GLOBALS['BASIC_USER'];
	}
	/**
	 * 
	 * Needed when changing the language to update userDomain session variable
	 * @access public
	 */
	public function refresh(){
		BASIC_SESSION::init()->set($this->userDomainVar, BASIC::init()->ini_get('root_virtual'));
	}
}
CMS_USERS::init();