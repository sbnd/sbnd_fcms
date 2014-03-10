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
* @package plugins.assetmanager
* @version 7.0.6  
*/

global $assetManager;
if(!$assetManager = Builder::init()->getDisplayComponentBySystemName('AssetManager')){
	die("Settings does'n exist. Go to Control panel/Components/ and check for component with class name 'AssetManager'.");
}
if(BASIC_USERS::init()->level() != -1 && !BASIC_USERS::init()->getPermission($assetManager->model->system_name, 'list')){
	die('Permition denied !');
}

global $bReturnAbsolute;	$bReturnAbsolute = false;

global $sMsg;				$sMsg = "";
global $ffilter;			$ffilter = "";
global $sUploadedFile;		$sUploadedFile = "";

//$size = BASIC::init()->stringToBite(ini_get('upload_max_filesize'));
global $MaxFileSize;		$MaxFileSize = $assetManager->maxFileSize;

global $AllowedTypes;		$AllowedTypes = $assetManager->allowedTypes;
global $defaultLanguage; 	$defaultLanguage = array(
	'asset_page_title' 					=> 'Asset Manager',
	'asset_upload_file' 				=> 'Upload File',
	'asset_upload_button' 				=> 'upload',
	'asset_ok_button' 					=> ' ok ',
	'asset_delete_button' 				=> 'Delete',
	'asset_empty_list' 					=> 'Empty ...',
	'asset_newfolder_button' 			=> 'New&nbsp;Folder',
	'asset_delfolder_button' 			=> 'Del&nbsp;Folder',
	'asset_all_type_files' 				=> 'All Files',
	'asset_image_type_files' 			=> 'Images',
	'asset_media_type_files' 			=> 'Media',
	'asset_flash_type_files' 			=> 'Flash',
	'asset_create_folder_label' 		=> 'New Folder Name',
	'asset_newfolder_ok_button' 		=> 'close & refresh',
	'asset_newfolder_create_button' 	=> 'create',
	'asset_permition_folderdel_message' => 'Are you sure you want to delete this folder?',
	'asset_root_folder_name' 			=> 'Resources',
	'asset_upload_progress_label' 		=> 'Uploading ...',
	'asset_confirm_filedel_label' 		=> 'Delete this file ?'
);
global $sBase0;			$sBase0 = BASIC::init()->ini_get('root_path').$assetManager->upload_path.$assetManager->storage;
global $sName0;			$sName0 = (class_exists('BASIC_LANGUAGE') ? BASIC_LANGUAGE::init()->get('asset_root_folder_name') : $defaultLanguage['asset_root_folder_name']);
global $sBaseRoot0;		$sBaseRoot0 = BASIC::init()->ini_get('root_path');
global $bReadOnly0;		$bReadOnly0 = false;
//$sBaseVirtual0=$GLOBALS['BASIC']->ini_get('root_virtual').$GLOBALS['BASIC']->ini_get('basic_path')."scripts/assetmanager/upload/";  //Assuming that the path is http://yourserver/Editor/assets/ ("Relative to Root" Path is required)

global $sBase1;			$sBase1 = "";
global $sName1;			$sName1 = "";
global $sBaseRoot1;		$sBaseRoot1 = "";
global $bReadOnly1;		$bReadOnly1 = true;
//$sBaseVirtual1="";

global $sBase2;			$sBase2 = "";
global $sName2;			$sName2 = "";
global $sBaseRoot2;		$sBaseRoot2 = "";
global $bReadOnly2;		$bReadOnly2 = false;
//$sBaseVirtual2="";

global $sBase3;			$sBase3 = "";
global $sName3;			$sName3 = "";
global $sBaseRoot3;		$sBaseRoot3 = "";
global $bReadOnly3;		$bReadOnly3 = false;
//$sBaseVirtual3="";

global $currFolder;		$currFolder = $sBase0;

global $root_manager_virtual;	$root_manager_virtual = BASIC::init()->ini_get('root_virtual').'plugins/assetmanager/';