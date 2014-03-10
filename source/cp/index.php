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
* @package cp
* @version 7.0.6  
*/
include_once "../cms/install.php";
if((@include_once "../conf/site_config.php") === false){
	require "../install.php";
}
BASIC::init()->imported('bars.mod');
BASIC::init()->imported("Builder.mod", "cms/controlers/back");

BASIC_USERS::init(array(
	'userSysVar' => "admin_id",
	'userDomainVar' => "admin_domain"
));
BASIC_LANGUAGE::init(array(
	'varLog' => 'admin_lang',
	'isAdmin' => true
));
/**
 * Change theme for the administrative interface.
 */
CMS_SETTINGS::init()->set('FSITE_THEME', CMS_SETTINGS::init()->get('SITE_THEME'));
CMS_SETTINGS::init()->set('SITE_THEME', 'cp/themes/modern/');
BASIC_TEMPLATE2::init(array(
	'template_path' => CMS_SETTINGS::init()->get('SITE_THEME').'tpl',
	'prefix_ctemplate' => 'cp'
));

Builder::init(array(
	'cPanelPath' => str_replace(BASIC::init()->ini_get('root_path'), "", preg_replace("/\/[^\/]+$/", "", str_replace("\\", "/", __file__))).'/',
	'jQueryVersion' => '1.7.min',
	'jQueryUIVersion' => '1.8.16.custom.min',
	'jQueryUITheme' => 'cupertino',//'ui-lightness',
	'useJSSvincs' => true
));
Builder::init()->initComponent();

BASIC_GENERATOR::init()->head('SvincsRootVirtual', 'script', null, "Svincs.ROOT_VIRTUAL = '".BASIC::init()->ini_get("root_virtual")."cp/'");

BASIC_TEMPLATE2::init()->set(array(
//	'BREADCRUMBS' => BREADCRUMBS(),
	'LANG_INFO' => FORM_LANGUAGE_MESSAGES()
));
if(class_exists('BASIC_LANGUAGE') && BASIC_LANGUAGE::init()->number() > 1){
	$lang_menu_data = array();
	for($i=0;$arr = BASIC_LANGUAGE::init()->listing();$i++){
		$lang_menu_data[] = array(
			'text' => $arr['text'],
			'link' => BASIC_URL::init()->link(BASIC_LANGUAGE::init()->link($arr['code'])),
			'selected' => ($arr['code'] == BASIC_LANGUAGE::init()->current()),
			'path_flag' => array($arr['folder'],$arr['flag'])
		);
	}
	BASIC_TEMPLATE2::init()->set('lang_menu', $lang_menu_data);
}
print Builder::init()->start();

// ---------------------------------- TOOLS ----------------------------------------- //

function BREADCRUMBS_PARENT($display,$admin_object){
    $BREADCRUMBS = '';
    if(isset($display->parent) && $display->parent != 'undefined'){
        
    	try{
	        $tmp = $admin_object->getdisplayComponent(
	        	($display->parent->prefix != $display->parent->system_prefix ? $display->parent->prefix : '').
	            $display->parent->system_name
	        );
    	}catch (Exception $e){
    		die(BASIC_LANGUAGE::init()->get('you_have_not_access_to_this_modul'));
    	}
        if($display->parent->parent){
            $BREADCRUMBS .= BREADCRUMBS_PARENT($tmp,$admin_object);
        }
        //print(get_class($tmp));print $tmp->breadcrumps;
        $default_fielt_prev = 'title';
        if(isset($tmp->breadcrumps)){
            $default_fielt_prev = $tmp->breadcrumps;
        }        
        if(!$display->parent_id){
            $BREADCRUMBS .=  $display->parent->public_name.' / ';
        }else{
            $cmp = $display->buildParent();
            $res = $cmp->getRecord($cmp->id);
            $BREADCRUMBS .=  (isset($res[$default_fielt_prev]) ? $res[$default_fielt_prev] : '...').' / ';
        }         
    }
    return $BREADCRUMBS;
}
function BREADCRUMBS(){
    $display = Builder::init()->displayComponent;
    $action_cmd = (isset($display->cmd) ? $display->cmd : '');
    if(!$action_cmd) $action_cmd = 'List';
    
    $BREADCRUMBS = '';
    $BREADCRUMBS .= BREADCRUMBS_PARENT($display, Builder::init());
    if(isset($display->model->public_name)){
        
        $default_fielt_prev = 'title';
        if(isset($display->breadcrumps)){
            $default_fielt_prev = $display->breadcrumps;
        }
        
        if($display->id && $display->getDataBuffer($default_fielt_prev)){
            $BREADCRUMBS .= $display->model->public_name.' / '.$display->getDataBuffer($default_fielt_prev).' / ';
        }else{
            $BREADCRUMBS .= $display->model->public_name.' / ';
        }
    }
    if(isset($display->actions[$action_cmd][2])){
        $BREADCRUMBS .= $display->actions[$action_cmd][2];
    }
  
    return $BREADCRUMBS;
}
function FORM_LANGUAGE_MESSAGES(){
	$display = Builder::init()->displayComponent;
	$lang_info = array();
	//@FIXME deprecated or implement code !!! 
	return $lang_info;
}