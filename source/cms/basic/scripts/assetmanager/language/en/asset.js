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
* @package basic.scripts.assetmanager.language.en
* @version 7.0.4  
*/

function getText(s)
	{
	switch(s)
		{
		case "Cannot delete Asset Base Folder.":return "Cannot delete Asset Base Folder.";
		case "Delete this file ?":return "Delete this file ?";
		case "Uploading...":return "Uploading...";
		case "File already exists. Do you want to replace it?":return "File already exists. Do you want to replace it?";
				
		case "Files": return "Files";
		case "del": return "del";
		case "Empty...": return "Empty...";
		}
	}
function loadText()
	{
	var txtLang = document.getElementsByName("txtLang");
	txtLang[0].innerHTML = "New&nbsp;Folder";
	txtLang[1].innerHTML = "Del&nbsp;Folder";
	txtLang[2].innerHTML = "Upload File";
	
	var optLang = document.getElementsByName("optLang");
    optLang[0].text = "All Files";
    optLang[1].text = "Media";
    optLang[2].text = "Images";
    optLang[3].text = "Flash";
	
    document.getElementById("btnOk").value = " ok ";
    document.getElementById("btnUpload").value = "upload";
	}
function writeTitle()
    {
    document.write("<title>Asset manager</title>")
    }