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
* @package cp.themes.modern.js
* @version 7.0.6  
*/

/**
 *	static object for manage checkbox list managers
 */
var managerList = (function (){
	
	return {
		numeredCheckbox : function (obj){
			if(!obj.form.is_numered){
				for(var i=0;i<obj.form[obj.name].length;i++){
					obj.form[obj.name][i].number = i;
				}
				obj.form.is_numered = true;
				obj.form.lastCheck = 0;
			}
		},
		treeChecker : function (obj, e){
			var form = obj.form;
			var _self = false;
			var collection = obj.form[obj.name];
			
			this.numeredCheckbox(obj);
			if(e.shiftKey){
				this.ctrlSupport(obj);
			}
			
			for(var i=0;i<collection.length;i++){
				if(collection[i] == obj){
					_self = true; continue;
				}
				if(_self){
					if(parseInt(collection[i].lang) > parseInt(obj.lang)){
						if(!collection[i].disabled) collection[i].checked = obj.checked;
					}else{
						break;
					}
				}
			}
			obj.form.lastCheck = obj.number;
			obj.state = obj.checked;
			// end
		}, 
		ctrlSupport : function (obj){
			var collection = obj.form[obj.name];
			
			var _start = parseInt(obj.form.lastCheck);
			var _end = parseInt(obj.number);
			if(_end < _start){
				var _start = parseInt(obj.number);
				var _end = parseInt(obj.form.lastCheck);
			}
			for(var i=_start; i<=_end ; i++){
				obj.form[obj.name][i].checked = obj.checked;
			}
		},
		changeAll : function (obj){//debugger;
			var coll = obj.form[obj.value+'[]'];	
			if(coll && !coll.length) coll = [coll];

			for(var i=0;i<coll.length;i++){
				if(!coll[i].disabled){
					coll[i].checked = obj.checked;
					$(coll[i]).trigger('modify');
				}
			}
		}
	}
})();