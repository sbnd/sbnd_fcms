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
* @package default_site_config
* @version 7.0.4  
*/

/**
 * SBND CMS7 configuration.
 * 
 * @varsion 3.1
 */
try{
	BASIC_SQL::init(array(
		'backupEngine' => new CmsBackup(array(
			'default' => 'conf/sbnd_cms7.php'
		))
	))->connect('${dbdriver}://${dbuser}:${dbpass}@${dbhost}/${dbname}', 'utf8');

	BASIC_LANGUAGE::init(array(
		'varLog' => 'language'
	));
	BASIC_SESSION::init(array(
		'liveTime' => CMS_SETTINGS::init()->get('session_time')
	))->start();
	
	$prfix = ''; foreach(explode("/", CMS_SETTINGS::init()->get('SITE_THEME')) as $v) if($v) $prfix = $v;
	BASIC_TEMPLATE2::init(array(
		'template_path' => CMS_SETTINGS::init()->get('SITE_THEME').'tpl',
		'prefix_ctemplate' => $prfix,
		'compress' => false
	));
	
	// if have server's alias with different names
	//BASIC::init()->ini_set('root_virtual', 'http://... root site url .../');
	
	BASIC::init()->imported('TinyMCE', 'plugins/tinymce');
	BASIC_GENERATOR::init()->registrateControle('html', new TinyMCE());
}catch (Exception $e){
	die(BASIC_GENERATOR::init()->element('div', 'style=color:#FF0000;font-size:12pt;', $e->getMessage()));
}