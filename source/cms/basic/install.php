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
* @package basic.install
* @version 7.0.4  
*/

/**
 * BASIC Framework - WEB
 * 
 * @author Evgeni Baldzhiyski
 * @version 3.0
 */

include_once(preg_replace("#/[^/]+$#", "", str_replace("\\", "/", __file__))."/basic.mod.php");

BASIC::init();

// ######## Load modules ######### //

/** Start Compulsory Servises **/
BASIC::init()->imported('error.mod');
BASIC::init()->imported('url.mod');
BASIC::init()->imported('cache.mod');

BASIC::init()->imported('sql.mod');
//BASIC::init()->imported('xml.mod');
BASIC::init()->imported('generator.mod');

//BASIC::init()->imported('ajax.mod');

BASIC::init()->imported('template.mod');
//BASIC::init()->imported('bars.mod');
//BASIC::init()->imported('upload.mod');
//BASIC::init()->imported('session.mod');
//BASIC::init()->imported('users.mod');
//BASIC::init()->imported('span.mod');
//BASIC::init()->imported('form.mod');
//BASIC::init()->imported('language.mod');

//BASIC::init()->imported('media.mod');

// ######## Registrate Global Settings ######## //

BASIC::init()->ini_set('core_version', 	'3.0');

BASIC::init()->ini_set('upload_path', 	'upload/');
BASIC::init()->ini_set('image_path', 	'img/');
BASIC::init()->ini_set('ttf_path', 		'ttf/');
BASIC::init()->ini_set('rewrite', 		false);
BASIC::init()->ini_set('error_level', 	6143);

BASIC::init()->ini_set('template_path',	'tpl/');
BASIC::init()->ini_set('temporary_path','tmp/');