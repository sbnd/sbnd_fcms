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
* @package basic.scripts.assetmanager
* @version 7.0.6  
*/

if(defined('SERVICE_OPEN') && SERVICE_OPEN == true && !$bWriteFolderAdmin){}else{die('No permitions! ');}

$sMsg = "";
function recDelete($name){
	$noerror = ''; 
	if(is_file($name)){
		if(!@unlink($name)) return 'File '.$name."/".$file." can't deleted !";
	}else{
		if(!$dir = @opendir($name)) return "Folder '" . $name . "' not exist!";
		
		while ($file = @readdir($dir)) {
			if($file == '.' || $file == '..') continue;
			
			if(is_file($name."/".$file)){
				if(!@unlink($name."/".$file)) return 'File '.$file." can't deleted !";
			}else{
				$noerror = recDelete($name."/".$file);
				if($noerror != '') return $noerror;
			}
		}
	
		@closedir($dir);
		@rmdir($name."/");
	}
}
if(isset($_POST["inpCurrFolder"])){
	$_POST["inpCurrFolder"] = str_replace("//", "/", $_POST["inpCurrFolder"]);
	
	if(!preg_match("/\/$/", $_POST["inpCurrFolder"])) $_POST["inpCurrFolder"] .= "/";
	
	$sDestination = pathinfo($_POST["inpCurrFolder"]);
	
	//DELETE ALL FILES IF FOLDER NOT EMPTY
    $dir = $_POST["inpCurrFolder"];
	recDelete($dir);
    
	
//	if(rmdir($_POST["inpCurrFolder"])==0)
//		$sMsg = "";
//	else
		$sMsg = "<script>document.write(getText('Folder deleted.'))</script>";
}
?>
<base target="_self">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="<?php print $GLOBALS['ASSET_MANAGER_VIRTUAL']; ?>style.css" rel="stylesheet" type="text/css">
<script>
	if(navigator.appName.indexOf('Microsoft')!=-1)
		var sLang=dialogArguments.sLang;
	else
		var sLang=window.opener.sLang;
	document.write("<scr"+"ipt src='language/"+sLang+"/folderdel_.js'></scr"+"ipt>");
</script>
<script>writeTitle()</script>
<script>
function refresh()
	{
	if(navigator.appName.indexOf('Microsoft')!=-1)
		dialogArguments.refreshAfterDelete(inpDest.value);
	else
		window.opener.refreshAfterDelete(document.getElementById("inpDest").value);
	}
</script>
</head>
<body onload="loadText()" style="overflow:hidden;margin:0px;background: #f4f4f4;">

<table width=100% height=100% align=center style="" cellpadding=0 cellspacing=0 ID="Table1">
<tr>
<td valign=top style="padding-top:5px;padding-left:15px;padding-right:15px;padding-bottom:12px;height=100%">

	<br>
	<input type="hidden" ID="inpDest" NAME="inpDest" value="<? echo $sDestination['dirname']; ?>">
	<div><b><? echo $sMsg; ?>&nbsp;</b></div>

</td>
</tr>
<tr>
<td class="dialogFooter" style="height:45px;padding-right:10px;" align=right valign=middle>
	<input type="button" name="btnCloseAndRefresh" id="btnCloseAndRefresh" value="<?php print AssetLanguage('asset_newfolder_ok_button');?>" onclick="refresh();self.close();" class="inpBtn" onmouseover="this.className='inpBtnOver';" onmouseout="this.className='inpBtnOut'">
</td>
</tr>
</table>


</body>
</html>