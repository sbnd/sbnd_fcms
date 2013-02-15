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
* @package basic.scripts.assetmanager
* @version 7.0.4  
*/

if(defined('SERVICE_OPEN') && SERVICE_OPEN == true && !$bWriteFolderAdmin){}else{die('No permitions! ');}

$sMsg = "";
$refresh = false;

if(isset($_POST["inpNewFolderName"])){	
	$sFolder = $_POST["inpCurrFolder"]."/".$_POST["inpNewFolderName"];

	if(is_dir($sFolder)==1){//folder already exist
		$sMsg = "<script>document.write(getText('Folder already exists.'))</script>";
	}else{
		if(mkdir($sFolder,0777)){ 
			chmod($sFolder,0777);
			$sMsg = "<script>document.write(getText('Folder created.'))</script>";
		}else{
			$sMsg = "<script>document.write(getText('Invalid input.'))</script>";
		}
	}
	$refresh = true;
}
?>
<base target="_self">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="<?php print $GLOBALS['ASSET_MANAGER_VIRTUAL']; ?>style.css" rel="stylesheet" type="text/css">

<script>
	if(navigator.appName.indexOf('Microsoft')!=-1){
		var sLang=dialogArguments.oUtil.langDir;
		
	}else{
		var sLang=(window.opener||parent).oUtil.langDir;
		
	}
	document.write("<scr"+"ipt src='<?php print $GLOBALS['ASSET_MANAGER_VIRTUAL']; ?>language/"+sLang+"/foldernew.js'></scr"+"ipt>");
</script>
<script>writeTitle()</script>
<script>
function doPreSubmit()
	{
	var Form1 = document.forms.Form1;
	if(navigator.appName.indexOf('Microsoft')!=-1)
		Form1.elements.inpCurrFolder.value=dialogArguments.selCurrFolder.value;
	else
		Form1.elements.inpCurrFolder.value=window.opener.document.getElementById("selCurrFolder").value;

	if(Form1.elements.inpNewFolderName.value=="")
		{
		alert(fgetText("Invalid input."));
		return false;
		}
	return true;
	}
function doSubmit(){
	if(doPreSubmit()){
		
		var opener = window.opener;
		if(navigator.appName.indexOf('Microsoft')!=-1){
			opener = dialogArguments;
		}
		
		document.forms.Form1.action = opener.getAction(false)+'&foldernew=1';
		document.forms.Form1.submit();
	}
}
function closeWindow(){
	if(navigator.appName.indexOf('Microsoft')!=-1){
		dialogArguments.changeFolder()
	}else{
		window.opener.changeFolder()
	};
	self.close();
}
</script>
</head>
<body onload="loadText()" style="overflow:hidden;margin:0;background: #f4f4f4">

<table width=100% height=100% align=center style="" cellpadding=0 cellspacing=0>
<tr>
<td valign=top style="padding-top:5px;padding-left:15px;padding-right:15px;padding-bottom:12px;height=100%">

<form method=post onsubmit="doPreSubmit()" name="Form1" id="Form1">
	<br>
	<input type="hidden" id="inpCurrFolder" name="inpCurrFolder">
	<span id="txtLang"><?php print AssetLanguage('asset_create_folder_label');?></span>: <br>
	<input type="text" id="inpNewFolderName" name="inpNewFolderName" class="inpTxt" size=38 onkeyup="if(this.value){document.getElementById('btnCreate').disabled = false;}else{document.getElementById('btnCreate').disabled = true;}">
	<div><b><? echo $sMsg ?>&nbsp;</b></div>
</form>

</td>
</tr>
<tr>
<td class="dialogFooter" style="height:40px;padding-right:10px;" align=right valign=middle>
	<input type="button" name="btnCloseAndRefresh" id="btnCloseAndRefresh" value="<?php print AssetLanguage('asset_newfolder_ok_button');?>" onclick="closeWindow()" class="inpBtn" onmouseover="this.className='inpBtnOver';" onmouseout="this.className='inpBtnOut'">&nbsp;
	<input name="btnCreate" id="btnCreate" disabled="disabled" type="button" onclick="doSubmit()" value="<?php print AssetLanguage('asset_newfolder_create_button');?>" class="inpBtn" onmouseover="this.className='inpBtnOver';" onmouseout="this.className='inpBtnOut'">
</td>
</tr>
</table>

</body>
</html>