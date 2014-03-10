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
 
?>
<base target="_self">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="<?php print $root_manager_virtual; ?>style.css" rel="stylesheet" type="text/css">
<script>
	if(navigator.appName.indexOf('Microsoft')!=-1)
		var sLang=dialogArguments.sLang;
	else
		var sLang=window.opener.sLang;
	document.write("<scr"+"ipt src='language/"+sLang+"/folderdel.js'></scr"+"ipt>");
</script>
<script>writeTitle()</script>
<script>
function del(){
	var Form1 = document.forms.Form1;
	
	if(navigator.appName.indexOf('Microsoft')!=-1){
		Form1.elements.inpCurrFolder.value=dialogArguments.selCurrFolder.value;
		Form1.action = dialogArguments.getAction(false)+'&_folderdel=1';
	}else{
		Form1.elements.inpCurrFolder.value=window.opener.document.getElementById("selCurrFolder").value;
		Form1.action = window.opener.getAction(false)+'&_folderdel=1';
	}
	Form1.submit();
}
</script>
</head>
<body onload="loadText()" style="overflow:hidden;margin:0px;background: #f4f4f4;">

<table width=100% height=100% align=center style="" cellpadding=0 cellspacing=0>
<tr>
<td valign=top style="padding-top:5px;padding-left:15px;padding-right:15px;padding-bottom:12px;height=100%">

<form method=post name="Form1" id="Form1">
	<br>
	<input type="hidden" ID="inpCurrFolder" NAME="inpCurrFolder">
	<div><b><span id=txtLang><?php print AssetLanguage('asset_permition_folderdel_message');?></span></b></div>
</form>

</td>
</tr>
<tr>
<td class="dialogFooter" style="height:45px;padding-right:10px;" align=right valign=middle>
	<input type="button" name="btnClose" id="btnClose" value="<?php print AssetLanguage('asset_ok_button');?>" onclick="self.close();" class="inpBtn" onmouseover="this.className='inpBtnOver';" onmouseout="this.className='inpBtnOut'">&nbsp;
	<input type="button" name="btnDelete" id="btnDelete" value="<?php print AssetLanguage('asset_delete_button');?>" onclick="del()" class="inpBtn" onmouseover="this.className='inpBtnOver';" onmouseout="this.className='inpBtnOut'">
</td>
</tr>
</table>

</body>
</html>