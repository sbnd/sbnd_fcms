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

/**
 * Date (form element) ;). Object will be applaied in target element ('document.getElementById(name)').
 * Use jQuery (1.4.2+) lib. 
 * 
 * Ex: 
 * 		head.script
 * 			formDate('target_id','2011-07-02','%Y-%m-%d',' maxlength="255" class="formDate date_sent"');
 * 
 * 				... 
 * 
 * 			$('#target_id').get(0).showCalendar();
 * 
 * 		body.form
 * 			<input id="target_id" name="target_id"/>
 *
 * @author Evgeni Baldzhiyski
 * @version 0.6
 * @since 07.09.2007
 * @events
 * 		select
 * 		open
 * 		close
 * 		init
 * 		datechange
 * 
 * @param String name				- HTML Object id
 * @param String value				- valid date. ex: '0000-00-00 00:00'
 * @param String format 			- ex: '%Y-%m-%d %H:%M %p'
 * @param String htmlAttr 			- ex: 'style="display:blick" width="150" ... '
 * @param Object opt {
 * 		Boolean printMode	[false](deprecated)	- print the control when the browser parse html. In this case will make new form element with id and name == param name 
 * 		Boolean otherMonths [true]	- if true will show in grey days from other months
 * 		Integer firstDay    [1]		- number for first day in week row. 0 == snday, 1 == monday, ... 
 *  	Integer minYear		[1970]	-
 *		Integer maxYear		[2050]	-
 *		String related		['']	- related with date controls for minimum date time 
 *		Date minDate 		['']	- 2001-01-02
 *		Date maxDate		['']	- 2011-10-10
 *		Date dataformat		[int]|str	- if int will get and return timeStamp
 * }
 */
function formDate(name, value, format, htmlAttr, opt){
	
    return new function (){
    	
		var options = {
			printMode	: false,
			otherMonths	: true,
			firstDay   	: 1,
			minYear		: 1970,
			maxYear		: 2050,
			disabled	: false,
			dkey 		: false,
			minDate		: '',
			maxDate		: '',
			related		: '',
			dataformat  : 'int'
		}
		if(opt){
			for(var p in opt){
				options[p] = opt[p];
			}
		}
		var self = this;
		var hdob = null;
		var relations = {};
		
		function parseMyDate(date){
			var f = [];
			if(options.dataformat == 'int'){
				var d = new Date(parseInt(date)*1000);
				f = [
				    d.getFullYear(),
				    d.getMonth()+1,
				    d.getDate(),
				    d.getHours(),
				    d.getMinutes(),
				    0
				];
			}else{
				f = date.split(/[^0-9AMPM]/ig);
			}
			return f;
		}
		function parseMyValue(date){
			var v = date;
			if(options.dataformat == 'int'){
				var f = date.split(/[^0-9AMPM]/ig);
				var d = new Date(f[0], parseInt(f[1])-1, f[2], f[3], f[4], 0);
				
				v = d.getTime()/1000;
			}
			return v;
		}
    	this.doptions = options;
		this.format = (format ? format : '%Y-%m-%d %H:%M %p');
		
		var f = parseMyDate(value);

		var spl = this.format.split(/%\D/ig);
		var frm = this.format.match(/%\D/ig);
		
		var h = 0;
		if(typeof f[3] != 'undefined'){
			h =  parseInt(f[3]);
			if(h >= 12 && /%p/i.test(this.format)){
				h -= 12;
			}
		}
		
		function convertIntDate(int){
			int = parseInt(int);
			if(int < 10){
				int = '0'+int;
			}
			return int;
		}
		function _v(name, length){
			var value = '', i;
			if(!length) length = 0;
			
			if(document.getElementById(name)){
				value = document.getElementById(name).value+'';
				
				length -= value.length;
			}
			if(length > 0){
				for(i = 0; i < length; i++){
					value = '0'+value;
				}
			}
			return value;
		}
		
		this.field = {
			'%Y': ["yyyy", 35, 4, parseInt(f[0])],
			'%y': ["yy"	 , 20, 2, parseInt((f[0]+'').substr(2, 2))],
			'%m': ["mm"	 , 20, 2, parseInt(f[1])],
			'%d': ["dd"	 , 20, 2, parseInt(f[2])],
			
			'%H': ["hor" , 20, 2, h],
			'%M': ["min" , 20, 2, (typeof f[4] != 'undefined' ?  parseInt(f[4]) : 0)],
			'%p': ["st"	 , 20, 2, (h && f[3] > 12 ? 1 : 0)]
		}

		this.arrFormat = frm;
	
		var useClock = false;
		var useClockFormat = false;
		
		this.markup = '';
		this.markup += '<table id="el_table_'+name+'" '+htmlAttr+' cellspacing="0" cellpadding="0">';
		this.markup += '	<tr>';
		this.markup += '	<td class="ctrl"><input type="checkbox" id="el_dKay_'+name+'" style="display:none;margin-right:6px;" onclick="document.getElementById(\''+name+'\').lock(!this.checked);" value="1" checked="checked"/></td>'
		
		for(var i=0;i<frm.length;i++){
			if($.browser.msie && $.browser.version < 9){
				mjd = (spl[i] && spl[i] != ' '? spl[i] : '&nbsp;');
			}else{
				mjd = (spl[i+1] && spl[i+1] != ' ' ? spl[i+1] : '&nbsp;');
			}
			
			var key = frm[i];
			var val = (typeof this.field[key] != 'undefined' ? convertIntDate(this.field[key][3]) : 0);
			if(key == '%p' || key == '%P'){
				val = 'am';
				if(this.field['%p'][3]){
					val = 'pm';
				}
				if(key == '%P'){
					val = val.toUpperCase();
				}
				key = '%p';
			}
			this.markup += '		<td class="ctrl"><input type="text" id="'+this.field[key][0]+'_'+name+'" value="'+val+'" maxlength="'+this.field[key][2]+'" style="width:'+this.field[key][1]+'px" onkeyup="document.getElementById(\''+name+'\').validate(this,0);" onclick="this.select();" onblur="document.getElementById(\''+name+'\').validate(this,1);" \/><\/td>';
			this.markup += '		<td class="ctrl">'+mjd+'<\/td>';
			
			if(key == '%H' || key == '%M'){
				useClock = true;
			}
			if(key == '%p'){
				useClockFormat = true;
			}
		}
		this.markup += '		<td  class="ctrl"style="cursor:pointer;" ><div onclick="return document.getElementById(\''+name+'\').showCalendar(this);" id="el_btn_'+name+'">&nbsp;&raquo;&nbsp;</div><\/td>';
		this.markup += '	<\/tr>';
		this.markup += '	<tr><td class="ctrl" colspan="'+((frm.length*2)+2)+'"><div id="calendar_cont_'+name+'" style="position:relative"></div></td><\/tr>';
		this.markup += '<\/table>';
		
		function changeDate(date){
			var f = parseMyDate(date);
			var h = 0;
			if(typeof f[3] != 'undefined'){
				h =  parseInt(f[3]);
				if(h >= 12 && /%p/i.test(self.format)){
					h -= 12;
				}
			}
			self.field['%Y'][3] = parseInt(f[0]);
			self.field['%y'][3] = parseInt((f[0]+'').substr(2, 2));
			self.field['%m'][3] = parseInt(f[1]);
			self.field['%d'][3] = parseInt(f[2]);
					
			self.field['%H'][3] = h;
			self.field['%M'][3] = (typeof f[4] != 'undefined' ?  parseInt(f[4]) : 0);
			self.field['%p'][3] = (h && f[3] > 12 ? 1 : 0);

			for(var i=0;i<frm.length;i++){
				var k = frm[i], val = 0;
				
				if(k == '%p' || k == '%P'){
					val = 'am';
					if(self.field['%p'][3]){
						val = 'pm';
					}
					if(k == '%P'){
						val = val.toUpperCase();
					}
					k = '%p';
				}else{
					val = convertIntDate(self.field[k][3]);
				}
				document.getElementById(self.field[k][0]+'_'+name).value = val;
			}
		  	self.validate(null);
		}
		
		// METHODS //
		this.option = function (name, value){
			if(typeof value == 'undefined'){
				return options[name];
			}
			switch (name) {
				case 'disabled':
					this.lock(value);
				break;
				case 'dKey':
					this.openKeyBtn(value);
					break;
				case 'value':
					changeDate(value);
					break;
				case 'related':
					if(value.pop){
						for(p in value){
							this.related(value[p]);
						}
					}else{
						this.related(value[p]);
					}
					break;

			default:
				options[name] = value;
			}
		}
		this.lock = function(key){
			var btn = document.getElementById('el_btn_'+name);
			var dKay = document.getElementById('el_dKay_'+name);
			
			btn.innerHTML = '&nbsp;&raquo;&nbsp;';
			dKay.checked = true;
			if(key){
				btn.innerHTML = '&nbsp;&curren;&nbsp;';
				dKay.checked = false;
			}
			var frm = this.format.match(/%\D/ig);
			for(var i=0;i<frm.length;i++){
				var k = frm[i];
				if(k == '%P') k = '%p';
				
				document.getElementById(this.field[k][0]+'_'+name).disabled = key;
			}
			document.getElementById(name).disabled = key;
			options.disabled = key;
		}
		this.openKeyBtn = function (key){
			options.dKey = key;
			document.getElementById('el_dKay_'+name).style.display = (key ? 'block' : 'none');
		}
		this.related = function (id){
			if(id && !relations[id]){
				relations[id] = 1;
				$('#'+id).bind('datechange', function (){//debugger;
					var date = this.value,
						f = [], d = null, v = null;
					
					if(this.doptions.dataformat == 'int'){
						d = new Date(parseInt(date)*1000);
						f = [
						    d.getFullYear(),
						    d.getMonth()+1,
						    d.getDate(),
						    d.getHours(),
						    d.getMinutes(),
						    0
						];
					}else{
						f = date.split(/[^0-9AMPM]/ig);
						d = new Date(f[0], f[1]-1, f[2], f[3], f[4], 0);
					}
					
					self.option('minDate', f[0]+'-'+convertIntDate(f[1])+'-'+convertIntDate(f[2]));
					
					if(options.dataformat == 'int'){
						v = new Date(parseInt(document.getElementById(name).value)*1000);
						f = [
						    d.getFullYear(),
						    d.getMonth()+1,
						    d.getDate(),
						    d.getHours(),
						    d.getMinutes(),
						    0
						];
					}else{
						f = document.getElementById(name).value.split(/[^0-9AMPM]/ig);
						v = new Date(f[0], f[1]-1, f[2], f[3], f[4], 0);
					}
					
					if(v < d) self.option('value', date);
				});
				$('#'+id).trigger('datechange');
			}
		}
		this.showCalendar = function(obj){
			if(options.disabled) return false;
			obj.onopen();
	
			return false;
		}
		this.closeHandler = function (cal) {
			$(hdob).trigger('close');
			cal.hide(); 
		}
		this.validate = function($obj,min){
			if($obj){
				if($obj.value.match(/[^0-9]+/)){
					$obj.value = 0;
				}
				var value = parseInt();
				if($obj.value.length > 1){
					value = parseInt($obj.value.replace(/^0/,''));
				}
				switch ($obj.id.replace('_'+name,"")){
					case 'yyyy':
							if($obj.value.length == 4){
								if(parseInt($obj.value) < options.minYear) $obj.value = options.minYear;
								if(parseInt($obj.value) > options.maxYear) $obj.value = options.maxYear;
							}
						break;
					case 'yy':
							if(value < 0) $obj.value = '00';
							if(parseInt(value) > 70) $obj.value = '70';
						break;
					case 'mm':
							if(parseInt(value) < min) $obj.value = '01';
							if(parseInt(value) > 12) $obj.value = '12';
						break;
					case 'dd':
							if(parseInt(value) < min) $obj.value = '01';
							if(parseInt(value) > 31) $obj.value = '31';
						break;
					case 'hor':
							if(parseInt(value) < 0) $obj.value = '00';
							if(parseInt(value) > 23) $obj.value = '23';
						break;
					case 'min':
							if(parseInt(value) < 0) $obj.value = '00';
							if(parseInt($obj.value) > 59) $obj.value = '59';
						break;
					case 'st':
							if($obj.value.length == 2){
								if($obj.value != 'PM' && $obj.value != 'AM' && $obj.value != 'pm' && $obj.value != 'am'){
									$obj.value = 'AM';
								}
							}
						break;
				}
			}
			var tmp = '';
			if(_v(this.field['%Y'][0]+'_'+name)){
				tmp += _v(this.field['%Y'][0]+'_'+name, 4);
			}else{
				tmp += (this.field['%Y'][3]+'').substr(0,2)+''+_v(this.field['%y'][0]+'_'+name, 2);
			}
			tmp += '-';
			tmp += _v(this.field['%m'][0]+'_'+name, 2);   
			tmp += '-';
			tmp += _v(this.field['%d'][0]+'_'+name, 2);
			tmp += ' ';
			if(_v(this.field['%H'][0]+'_'+name)){
				var val = parseInt(_v(this.field['%H'][0]+'_'+name));
				var pval = _v(this.field['%p'][0]+'_'+name);
				if(useClockFormat && pval.toLowerCase() == 'pm'){
					val += 12;
				}
				tmp += convertIntDate(val);
			}else{
				tmp += '00';
			}
			tmp += ':';
			tmp += _v(this.field['%M'][0]+'_'+name, 2);
			tmp += ':00';
			tmp = tmp.replace(/([ ]+)$/ig,'');
			
			document.getElementById(name).value = parseMyValue(tmp);

			$(hdob).trigger('datechange', tmp);
		} 
		
		// SETUP //
		
		if(options.printMode){
			document.write(this.markup+'<input type="hidden" name="'+name+'" id="'+name+'" value="'+value+'" />');
		}else{
			$(self.markup).insertBefore('#'+name);
		}
		hdob = document.getElementById(name);
	
		var params = {};
			params.inputField = name;
			params.containerField = 'calendar_cont_'+name;
			params.firstDay = options.firstDay;
			params.dataformat = options.dataformat;
			
			params.ifFormat = '%Y-%m-%d';//this.format;
			
			if(useClock){
				params.ifFormat += ' %H:%M';
			}
			if(useClockFormat){
				params.timeFormat = '12';
				params.showsTime = true;
				//params.ifFormat += ' %p';
			}else if(/%H/.test(this.format) || /%M/.test(this.format)){
				params.timeFormat = '24';
				params.showsTime = true;
			}else{
				params.showsTime = null;
			}
			params.onSelect = function (cal,date,target){
				if(options.dataformat == 'int'){
					var f = date.split(/[^0-9AMPM]/ig);
					var d = new Date(f[0], f[1]-1, f[2], f[3], f[4], 0);
					
					date = d.getTime()/1000;
				}
				changeDate(date);
			  	
			  	var vv = document.getElementById(name).value;
			  	
			  	$(hdob).trigger('select', vv);
			}
			params.onOpen = function (cal){
				$(hdob).trigger('open');
			}
			params.onClose = function (cal){
				$(hdob).trigger('close');
				cal.hide();
			}
			params.range = [options.minYear, options.maxYear];
			
			params.button   = "el_btn_"+name;
			params.eventName   = "open";
			params.targetPosition = "el_table_"+name;

			if(options.otherMonths) params.showOthers = true;
			
			params.field = self.field;
			
			params.dateStatusFunc = function (date, year, month, iday){
				var status = false;
				
				var dmin = null; if(options.minDate){
					dmin = new Date(options.minDate);
				}
				var dmax = null; if(options.maxDate){
					dmax = new Date(options.maxDate)
				}				
				
				if(dmin && date < dmin) status = true;
				if(dmax && date > dmax) status = true;
				
				return status;
			}
				
			var cal = Calendar.setup(params);
		
			hdob.style.display = 'none';
			for(p in self){
				hdob[p] = self[p];
			}
			
			self.validate(null);
			self.lock(options.disabled);
			self.openKeyBtn(options.dkey);
			self.related(options.related);
			
			$(hdob).trigger('init');
	}
}