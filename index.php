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
* @version 7.0.4  
*/

include_once "cms/install.php";
if((@include_once "conf/site_config.php") === false){
	require "install.php";
}
BASIC::init()->imported('bars.mod');
BASIC::init()->imported('form.mod', 'cms');
BASIC::init()->imported('Builder.mod', 'cms/controlers/front');

Builder::init(array(
	'jQueryVersion' => '1.8.2',
	'jQueryUIVersion' => '1.7.3.custom',
	'useJSSvincs' => true,

	'loginMode' 	=> CMS_SETTINGS::init()->get('SITE_LOGIN_MODE'),
	'baseTemplate' 	=> CMS_SETTINGS::init()->get('SITE_TEMPLATE')
))->start();