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
 * 
 * default component that is open after loggin. All roles have permissions to open it.
 * @author Evgeni Baldzhiyski
 * @package cms.controlers.back
 */
class Dashboard extends CmsComponent{

    public $base			   = '';
	function main(){
		parent::main();
	}
	/**
	 * change standart list view with text
	 * 
	 * @FIXME add template for this component
	 * @access public
	 * @return string
	 */
	function ActionList(){ 
		return "<h2>Welcome to SBND F&CMS - Framework & CMS for PHP developers</h2>";
	}
	
	
}