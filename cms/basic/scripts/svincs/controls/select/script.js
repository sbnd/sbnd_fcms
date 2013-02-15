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
* @package basic.scripts.svincs.select
* @version 7.0.4  
*/


/**
 * Object for work with select interfases
 */
Svincs.Select = {	
	/**
	 * Object for work with select interfases
	 * returns of object is metods 
	 * actionUse -> for click
	 * actionMove -> for transfer element 
	 * useAllButtons -> show or hide all buttons
	 * 
	 * events : 
	 * 		move 		(optionObject, to, from)
	 * 		select 		(optionObject, from)
	 * 
	 * @param string/DOM ELEMENT el
	 * @param array arr_all - list with all options
	 * @param array arr_chose - list with selected options
	 * @param object att - html dom element's parameters
	 * 
	 * @author Evgeni Baldzhiyski
	 * @version 0.7
	 * @since 20.03.2007
	 */
	MoveCombo: function (el, arr_all, arr_chose, att){
		if(!att) att = {}; 
		
		delete att.disabled;
		
		var cl_chose = [],
			cl_all = [],
			tmp = '',
			name = att.name||el;
		
		if(typeof arr_all == 'object'){	
			for(var p in arr_all){
				var ex = false;
				for(var pp in arr_chose){
					if(p == arr_chose[pp]){
						cl_chose += '<option value="'+p+'">'+arr_all[p]+'</option>'; 
						ex = true;
						break;
					}
				}
				if(!ex)
					cl_all += '<option value="'+p+'">'+arr_all[p]+'</option>';
			}
		}
	    $_attributes = '';
	    Svincs.forEach(att,function (v,i){
	    	if(i != 'name')
	    		$_attributes += i.replace('className','class')+'="'+v+'" ';
	    });
	    
		tmp += '<table id="'+el+'_mS" '+$_attributes+'>';
			tmp += '<tr>';
				tmp += '<td width="50%">';
					tmp += '<select multiple="multiple" id="'+el+'_all" style="width:100%;" class="select left_control">';
					tmp += cl_all;
					tmp += '</select>';
				tmp += '</td>';
				tmp += '<td align="center" class="button_bar">';
				tmp += '<div class="buttons">';
					tmp += '<button class="button left_all_button btn_left" type="button" id="'+el+'_left_all">&gt;&gt;&gt;</button><br/>';
					tmp += '<button class="button left_button btn_left" type="button" id="'+el+'_left">&gt;&gt;</button><br/>';
					tmp += '<button class="button right_button btn_right" type="button" id="'+el+'_right" class="btn_right">&lt;&lt;</button>';
					tmp += '<button class="button right_all_button btn_right" type="button" id="'+el+'_right_all">&lt;&lt;&lt;</button>';
				tmp += '</div>';
				tmp += '</td>';
				tmp += '<td width="50%">';
					tmp += '<select multiple="multiple" name="'+name+'[]" id="'+el+'"  style="width:100%;height" class="select right_contrl">';
					tmp += cl_chose;
					tmp += '</select>';
				tmp += '</td>';
			tmp += '</tr>';
		tmp += '</table>';
		
		$((typeof el == 'string' ? '#'+el : el)).replaceWith(tmp);
		
		//document.write(tmp);
		
		var form_control = document.getElementById(el+'');
		var self = form_control;
		
		form_control.l_obj = document.getElementById(el+'_all');
		//this.r_obj = document.getElementById(el);
		form_control.l_btn = document.getElementById(el+'_left');
		form_control.r_btn = document.getElementById(el+'_right');
	
		form_control.l_all_btn = document.getElementById(el+'_left_all');
		form_control.r_all_btn = document.getElementById(el+'_right_all');
		
		form_control.l_obj.ondblclick = function (){
			for(var i=0;i<this.options.length;i++)
				if(this.options[i].selected == true)
					self.change(this.options[i],self,self.l_obj);
		}
		form_control.ondblclick = function (){
			for(var i=0;i<this.options.length;i++)
				if(this.options[i].selected == true)
					self.change(this.options[i],self.l_obj,self);
		}
		form_control.l_obj.onchange = function(e){
			for(var i=0;i<this.options.length;i++)
				if(this.options[i].selected == true)
					if($(self).trigger('select', [this.options[i], this]) === false) return false;
		}
		form_control.onchange = function(e){
			for(var i=0;i<this.options.length;i++)
				if(this.options[i].selected == true)
					if($(self).trigger('select', [this.options[i], this]) === false) return false;
		}
		
		form_control.change = function ($obj,$location,$current){//debugger;
			if($(this).trigger('move', [$obj, $current, $location]) === false) return false;
			
			if($.browser.msie){
				var $newObj = document.createElement('option');
					$newObj.text = $obj.text;
					$newObj.value = $obj.value;
				
				$current.removeChild($obj);
				$location.add($newObj);
			}else{
				$location.appendChild($obj);
			}
		}
		
		form_control.buttonEvents = function ($to,$from){
			var tmp = new Array();
			for(var i=0;i<$to.options.length;i++){
				if($to.options[i].selected == true) tmp[tmp.length] = $to.options[i];
			}
			for(var i=0;i<tmp.length;i++){
				this.change(tmp[i],$from,$to);
			}
		}
		
		form_control.l_btn.onclick = function (){
			self.buttonEvents(self.l_obj,self);
		}
		form_control.r_btn.onclick = function (){
			self.buttonEvents(self,self.l_obj);
		}
		
		form_control.l_all_btn.onclick = function (){
			for(var i=0;i<form_control.l_obj.options.length;i++)
				form_control.l_obj.options[i].selected = true;		
			
			self.buttonEvents(self.l_obj, self);
		}
		form_control.r_all_btn.onclick = function (){
			for(var i=0;i<self.options.length;i++)
				self.options[i].selected = true;
			
			self.buttonEvents(self, self.l_obj);
		}
		
		form_control.disabledElement = function ($key){
			var $dsb = false;
			if($key) $dsb = true;
			
			this.l_obj.disabled = $dsb;
			
			this.l_btn.disabled = $dsb;
			this.r_btn.disabled = $dsb;
			this.l_all_btn.disabled = $dsb;
			this.r_all_btn.disabled = $dsb;
			
			this.disabled = $dsb;
		}
		form_control.reset = function (options){
			$(form_control).html('');
			
			var p, html = ''; for(p in options){
				html += '<option value="'+options[p].value+'">'+options[p].text+'</option>';
			}
			$(form_control.l_obj).html(html);
		}
		
		$(form_control.form).submit(function(){//debugger;
			for(var i=0;i<self.options.length;i++)
				self.options[i].selected = true;
		});
		
		return form_control;
	},
	/**
	 * Form field Change select 
	 *
	 * events onadd,onedit,ondelete
	 * 
	 * @param string or null $sysname -> name object variable 
	 * @param string el -> name vield form
	 * @param srray or null value  -> array options /['a,b,c','a,d,t','s,r,d']/
	 * @param array work  -> texts for work field /['Field 1','Field 2','Field 3']/
	 * @param array work  -> texts for buttons/['Add','Edit','Del']/
	 *
	 * @examples
	 *   $myfield = new Svincs.Select.changeSelect('$myfield','myvar',null,['Id','Text'],['Add','Del','Edit']);
	 * 
	 * @author Evgeni Baldzhiyski
	 * @version 0.7 
	 * @since 20.03.2007	 
	 */
	ManageCombo: function (el, value, work, btn, att){
		if(!att) att = {};
		
		delete att.disabled;
		
		var self = this;
		
		this.cntrId = el;
		this.cntrName = att.name||el;
		
		this.workEl = [];
		var workField = null;
		
		if(!btn) btn = ['+','-'];
		if(!work) work = [''];
		if(value == null || value == '') value = new Array();
		
		$_attributes = att;
		if(typeof att == 'object'){
		    if(att.onAdd){
		        var tmp = att.onAdd;
		        $(this).bind('Add',(typeof tmp == 'function' ? tmp : eval('tmp = '+tmp)));
		        delete att.onAdd;
		    }
		    if(att.onEdit){
		        var tmp = att.onEdit;
		        $(this).bind('Edit',(typeof tmp == 'function' ? tmp : eval('tmp = '+tmp)));
		        delete att.onEdit;
		    }
		    if(att.onDelete){
		        var tmp = att.onDelete;
		        $(this).bind('Delete',(typeof tmp == 'function' ? tmp : eval('tmp = '+tmp)));
		        delete att.onDelete;
		    }
		    if(att.buttons){
		        delete att.buttons;
		    }
		    
		    $_attributes = '';
		    Svincs.forEach(att,function (v,i){
		    	if(i != 'name')
		    		$_attributes += i.replace('className','class')+'="'+v+'" ';
		    });
		}
		
		var tmp = '';
		tmp += '<table id="'+this.cntrId+'_dS" '+$_attributes+'>';
			tmp += '<tr>';
				tmp += '<td align="left" style="padding:0;height:1">';
					tmp += '<table  cellpadding="0" cellspacing="0" border="0" width="100%" style="padding:0;margin:0;" id="'+this.cntrId+'_work">';
						tmp += '<tr>';
						for(var i=0;i<work.length;i++){
							tmp += '<td class="input_bar">';
								if(work[i] != null || work[i] != ''){
									tmp += '<span class="text_input">'+work[i]+'<\/span>';
								}
								tmp += '<input class="input" type="text" id="'+this.cntrId+'_txt_'+i+'"\/>';
							tmp += '<\/td>';
						}		
						tmp += '<\/tr>';
					tmp += '<\/table>';
				tmp += '<\/td>';
				tmp += '<td align="center" class="button_bar" valign="bottom">';
				
				if(btn[0]){
					tmp += '<div class="buttons">';
						tmp += '<button style="width:100%" class="button add" type="button" id="'+this.cntrId+'_add" onclick="document.getElementById(\''+this.cntrId+'\').addElement(1);">'+btn[0]+'<\/button>';
					tmp += '<\/div>';
				}
				
				tmp += '<\/td>';
			tmp += '<\/tr>';
			tmp += '<tr>';
				tmp += '<td align="left" valign="top" colspan="2">';
					tmp += '<select  style="width:100%;" class="select" name="'+this.cntrName+'[]" id="'+this.cntrId+'" multiple="multiple" ondblclick="document.getElementById(\''+this.cntrId+'\').editElement(1);">';
					for(var i=0;i<value.length;i++){
						tmp += '<option value="'+value[i]+'">'+value[i]+'<\/option>';
					}
					tmp += '<\/select>';
				tmp += '<\/td>';
			tmp += '<\/tr>';
			
			if(btn[1]){
				tmp += '<tr>';
				    tmp += '<td colspan="2" align="center" class="bottom_button_bar" valign="top">';
				          tmp += '<button style="width:100%" class="button delete" type="button" id="'+this.cntrId+'_del" onclick="document.getElementById(\''+this.cntrId+'\').delElement(1);">'+btn[1]+'<\/button>';
				    tmp += '<\/td>';
				tmp += '<\/tr>';
			}
		tmp += '<\/table>';
	
		$((typeof el == 'string' ? '#'+el : el)).replaceWith(tmp);
		//document.write(tmp);
		
		var workField = this.workField = document.getElementById(this.cntrId);
		
		this.txtField = new Array();
		for(var i=0;i<work.length;i++){
			this.txtField[i] = document.getElementById(this.cntrId+'_txt_'+i);
		}
		this.buttons = {
			'add' : document.getElementById(this.cntrId+'_add'),
			'edit': document.getElementById(this.cntrId+'_edit'),
			'del' : document.getElementById(this.cntrId+'_del')
		};
		
		this.disabledElement = function ($key){
			var dsb = false;
			if($key) dsb = true;
			
			for(p in this.txtField) this.txtField[p].disabled = dsb;
			
			for(p in this.buttons) try{this.buttons[p].disabled = dsb;}catch(e){}
			
			workField.disabled = dsb;
		}
		
		this.loadKey = null;
		this.load = function (){
			if(this.loadKey == null){
				
				var col = document.getElementById(this.cntrId+'_work').getElementsByTagName('input');
				for(var i=0;i<col.length;i++){
					this.workEl[i] = col.item(i);
				}
				this.loadKey = true;
			}
		};
		/**
		 * @param array arr
		 * @return array
		 */
		this.encode = function (arr){//debugger;
			var value = '';
			var text = '';
			var length = arr.length;
			for(var i=0;i<length;i++){
				value += arr[i] != '' ? arr[i].replace(/,/g,'&#58;') + (i < length-1 ? "," : '') : '';
				text += arr[i] != '' ? arr[i] + (i < length-1 ? "," : '') : '';
			}
			if(value){
				return [value, text];
			}
			return null;
		};
		/**
		 * @param string $str
		 * @return array
		 */
		this.decode = function (str){//debugger;
			var arr = str.split(",");
			for(var i=0;i<arr.length;i++){
				arr[i] = arr[i].replace(/&#58;/g, ',');
			}
			return arr;
		};
		this.addElement = function (evn){
		    if(evn && $(self).trigger('add') === false) return;
		    
			this.load();
			
			var arr = [], p, flag = false; 
			
			for(p in this.workEl){
				if(this.workEl[p].value){
					flag = true;
				}
				arr.push(this.workEl[p].value);
				
				this.workEl[p].value = '';
			}
			if(!flag) return;
			
	//		for(p in workField.options){
	//			workField.options[p].selected = false;
	//		}
			var enc = this.encode(arr);
			if(!enc) return;
			
			var el = document.createElement('option');
				el.value = enc[0]; 
				el.text = enc[1]; 
				//el.selected = true;
				
			if(document.all){
				workField.add(el);
			}else{
				workField.appendChild(el);
			}
		};
		this.editElement = function (evn){//debugger;
		    if(evn && $(self).trigger('edit') === false) return;
		     
			this.addElement();
			
			if(workField.options.length == 0) return;
			
			var arr = []; for(var i=0;i<workField.options.length;i++){
				if(workField.options[i].selected == true){
					arr = this.decode(workField.options[i].value);
					workField.removeChild(workField.options[i]);
					break;
				}
			}
			for(var i=0;i<this.workEl.length;i++){
				this.workEl[i].value = arr[i];
			}
		};
		this.delElement = function (evn){//debugger;
		    if(evn && $(self).trigger('delete') === false) return;
		    
	    		this.load();
	    
	    		var arr = new Array();
	    		var $length = workField.options.length;
	    		for(var i=0;i<$length;i++){
	    			if(workField.options[0].selected != true){
	    				arr[arr.length] = workField.options[0].cloneNode(true);
	    			}
	    			workField.removeChild(workField.options[0]);
	    		}
	    		
	    		for(var i=0;i<arr.length;i++){
	    			if(document.all){
	    				var op = document.createElement('option');
	    				op.value = arr[i].value; op.text = arr[i].value;
	    				workField.add(op);
	    			}else{
	    				workField.appendChild(arr[i]);
	    			}
	    		}
		};
		this.reset = function (options){
			var p, html = ''; for(p in options){
				html += '<option value="'+options.value+'">'+options.text+'</option>';
			}
			$(workField).html(options);
		}		
		for(p in this){
			workField[p] = this[p];
		}
		
		$(workField.form).submit(function(){
			if(self.workField == null){
				self.workField = document.getElementById(self.cntrId);
			}
			
			self.addElement();
			for(var i=0;i<self.workField.options.length;i++){
				self.workField.options[i].selected = true;
			}
		});
		
		return workField;
	}
}