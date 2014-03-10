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
* @package themes.responcive.js
* @version 7.0.6  
*/

var FormActionManager = (function (){
	return {
		go: function (obj, rule_type, rule_text){
			if(rule_type == 'confirm'){
				if(!confirm(rule_text, {
						yes: function(e){
							button[obj.tagName == 'A' ? 'link' : 'button']();
						}
					}
				)){return false;}
			}else{
				if(alert(rule_type, {
					yes: function(e){
						button[obj.tagName == 'A' ? 'link' : 'button']();
					}
				})){return false;}
			}
		},
		link: function (obj){
			locatio.href = obj.href;
		},
		button: function (obj){
			obj.click()
		}
	};
})();