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
* @package basic.scripts.calendar
* @version 7.0.4  
*/

Calendar.setup = function(params){
 	
 	function param_default(pname,def){
 		if(typeof params[pname]=="undefined"){
 			params[pname]=def;
 		}
 	};
 	
 	param_default("inputField",null);
 	param_default("containerField",null);
 	param_default("displayArea",null);
 	param_default("button",null);
 	param_default("eventName","click");
 	param_default("ifFormat","%Y/%m/%d");
 	param_default("daFormat","%Y/%m/%d");
 	param_default("singleClick",true);
 	param_default("disableFunc",null);
 	param_default("dateStatusFunc",params["disableFunc"]);
 	param_default("dateText",null);
 	param_default("firstDay",null);
 	param_default("align","Br");
 	param_default("range",[1970,2050]);
 	param_default("weekNumbers",true);
 	param_default("flat",null);
 	param_default("flatCallback",null);
 	param_default("onSelect",null);
 	param_default("onClose",null);
 	param_default("onOpen",null);
 	param_default("onUpdate",null);
 	param_default("date",null);
 	param_default("showsTime",false);
 	param_default("timeFormat","24");
 	param_default("electric",true);
 	param_default("step",2);
 	param_default("position",null);
 	param_default("cache",true);
 	param_default("showOthers",false);
 	param_default("multiple",null);
 	
 	var tmp=["inputField","displayArea","button","targetPosition"];
 	
 	for(var i in tmp){
 		if(typeof params[tmp[i]]=="string"){
 			params[tmp[i]]=document.getElementById(params[tmp[i]]);
 		}
 	}
 	//debugger;
 	if(!(params.flat||params.multiple||params.inputField||params.displayArea||params.button)){
 		alert("Calendar.setup:\n  Nothing to setup (no fields found).  Please check your code");
 		return false;
 	}
 	
 	function onSelect(cal){
 		var p=cal.params;
 		var update=(cal.dateClicked||p.electric);
 		if(update&&p.inputField){
 			p.inputField.value=cal.date.print(p.ifFormat); 
 			if(typeof p.inputField.onchange=="function"){ 
 				p.inputField.onchange();
 			}
 		}
 		if(update&&p.displayArea)p.displayArea.innerHTML=cal.date.print(p.daFormat);
 		if(update&&typeof p.onUpdate=="function"){ 
 			p.onUpdate(cal);
 		}
 		if(update&&p.flat){
 			if(typeof p.flatCallback=="function")p.flatCallback(cal);
 		}
 		if(update&&p.singleClick&&cal.dateClicked)cal.callCloseHandler();
 	};
 	
 	if(params.flat!=null){
 		if(typeof params.flat=="string"){
 			params.flat=document.getElementById(params.flat);
 		}
 		if(!params.flat){
 			alert("Calendar.setup:\n  Flat specified but can't find parent.");
 			return false;
 		}
 		var cal=new Calendar(params.firstDay,params.date,params.onSelect||onSelect);
 		cal.showsOtherMonths=params.showOthers;
 		cal.showsTime=params.showsTime;
 		cal.time24=(params.timeFormat=="24");
 		cal.params=params;
 		cal.weekNumbers=params.weekNumbers;
 		cal.setRange(params.range[0],params.range[1]);
 		cal.setDateStatusHandler(params.dateStatusFunc);
 		cal.getDateText=params.dateText;
 		
 		if(params.ifFormat){
 			cal.setDateFormat(params.ifFormat);
 		}
 		if(params.inputField&&typeof params.inputField.value=="string"){
 			cal.parseDate(params.inputField.value);
 		}
 		cal.create(params.flat);
 		cal.show();
 		return false;
 	}
 	var triggerEl=params.button||params.displayArea||params.inputField;
 	
 	triggerEl["on"+params.eventName]=function(){//debugger;
 		var dateEl=params.inputField||params.displayArea;
 		var dateFmt=params.inputField?params.ifFormat:params.daFormat;
 		var mustCreate=false;
 		
 		var cal = window.calendar;
 		if(!params.cache){
 			cal = this.calendar;
 		}
 		if(dateEl){
 			params.date = Date.parseDate(dateEl.value||dateEl.innerHTML, dateFmt);
 			if(params.dataformat == 'int'){
 				params.date = new Date(parseInt(dateEl.value||dateEl.innerHTML)*1000);
 			}
 		}
 		
 		if(!cal){
 			cal = new Calendar(params.firstDay,params.date,params.onSelect||onSelect,params.onClose||function(cal){cal.hide();});
 			
 			cal.showsTime=params.showsTime;
 			cal.time24=(params.timeFormat=="24");
 			cal.weekNumbers=params.weekNumbers;
 			
 			mustCreate=true;
 			
 			if(params.cache){
 				window.calendar = cal;
 			}else{
 				window.calendar = this.calendar = cal;
 			}
 		}else{
 			cal.onSelected = params.onSelect||onSelect;
 			cal.onClose = params.onClose||function(cal){cal.hide();};
 			
 			if(params.date){
 				cal.setDate(params.date);
 			}
 			window.calendar.hide();
 			window.calendar = cal;
 		}
 		if(params.onOpen && params.onOpen(cal) === false){
 			cal.hide(); retrn;
 		}
 		
 		if(params.multiple){
 			cal.multiple={};
 			for(var i=params.multiple.length;--i>=0;){
 				var d=params.multiple[i];
 				var ds=d.print("%Y%m%d");
 				cal.multiple[ds]=d;
 			}
 		}
 		cal.showsOtherMonths=params.showOthers;
 		cal.yearStep=params.step;
 		cal.setRange(params.range[0],params.range[1]);
 		cal.params=params;
 		cal.setDateStatusHandler(params.dateStatusFunc);
 		cal.getDateText=params.dateText;
 		cal.setDateFormat(dateFmt);
 		
 		if(mustCreate){
 			cal.create(document.getElementById(params.containerField));
 		}else{
 			cal.move(document.getElementById(params.containerField));
 		}
 		
 		cal.refresh();
 		if(!params.position){
 			var pos = params.button||params.displayArea||params.inputField;
 			if(params.targetPosition) pos = params.targetPosition;
 			cal.showAtElement(pos,params.align);
 		}else{
 			cal.showAt(params.position[0],params.position[1]);
 		}
 		return false;
 	};
 	return cal;
 };