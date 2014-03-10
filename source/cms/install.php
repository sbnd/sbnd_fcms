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
* @package cms.install
* @version 7.0.6
*/
try {
	/**
	 * SBND CMS
	 * 
	 * @author Evgeni Baldziyski
	 * @version 7.0.6
	 */
	include_once(preg_replace("/\/[^\/]+$/","",str_replace("\\","/",__file__))."/basic/basic.mod.php");
	BASIC::init();
	
	BASIC::init()->ini_set('root_path', str_replace('/cms/',"/",BASIC::init()->ini_get('root_path')));
	BASIC::init()->ini_set('root_virtual', str_replace('/cms/',"/",BASIC::init()->ini_get('root_virtual')));
	
	BASIC::init()->ini_set('basic_path', 'cms/'.BASIC::init()->ini_get('basic_path'));
	
	// ######## Load modules ######### //
	
	BASIC::init()->imported('error.mod');
	BASIC::init()->imported('url.mod');
	BASIC::init()->imported('cache.mod');
	
	BASIC::init()->imported('sql.mod', 'cms');
	BASIC::init()->imported('generator.mod');
	BASIC::init()->imported('template.mod');
	BASIC::init()->imported('session.mod');
	BASIC::init()->imported('template.mod');
	
	// ######## Registrate Global Settings ######## //
	
	BASIC::init()->ini_set('core_version', 	'3.1');
	BASIC::init()->ini_set('cms_version', 	'7.0.6');
	
	BASIC::init()->ini_set('cms_name', 		'SBND F&CMS');
	BASIC::init()->ini_set('upload_path', 	'upload/');
	BASIC::init()->ini_set('image_path', 	'img/');
	BASIC::init()->ini_set('rewrite', 		false);
	BASIC::init()->ini_set('error_level', 	6143);

	BASIC::init()->ini_set('template_path', 'tpl/');
	BASIC::init()->ini_set('temporary_path','tmp/');
	
	BASIC::init()->imported('ajax.mod', 	'cms');
	BASIC::init()->imported('form.mod', 	'cms');
	BASIC::init()->imported('language.mod', 'cms');
	BASIC::init()->imported('settings.mod', 'cms');
	BASIC::init()->imported('backup.mod',	'cms');
	BASIC::init()->imported('users.mod',	'cms');
	
	BASIC::init()->imported('permitions', 	'cms');
}catch (Exception $e){
	BASIC_ERROR::init()->setError($e->getMessage()." :: [File : ".$e->getFile()." / Line : ".$e->getLine()."]");
}