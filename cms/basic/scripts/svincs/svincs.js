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
* @package basic.scripts.svincs
* @version 7.0.4  
*/

/**
 * @author Evgeni Baldziyski
 * @version 3.0 
 * @since 06.03.2007
 */
var Svincs = (function (){
	
	return {
		version : '3.0',

		ROOT_VIRTUAL : '',
		
		path : '',
		getPath : function (find){
			
			var oScripts=document.getElementsByTagName("script");
			var findDef = (find ? find : "svincs")+'.js';
			
			for(var i=0;i<oScripts.length;i++){
				var sSrc=oScripts[i].src;//.toLowerCase();
				if(sSrc.indexOf(findDef) != -1){
					if(!find){
						this.path =oScripts[i].src.replace(findDef,"");
						return this.path; 
					}else{
						return oScripts[i].src.replace(findDef,"");
					}
				}
			}
		},
		moduls : [],
		include : function (obj,type){
			if(!type) type = 'js';

			if(!this.path) this.getPath();
			if(typeof obj == 'string') obj = [obj];

			for(var i=0;i<obj.length;i++){
				if(!this.moduls[obj[i]+'.'+type]){
					document.write("<scr"+"ipt src='"+this.path+obj[i]+'.'+type+"'></scr"+"ipt>");
					this.moduls[obj[i]+'.'+type] = true;
				}
			}
		},
		forEach : function (obj,fun,scope){
		    
		    if(!obj) obj = {};
		    if(!scope) scope = obj;
		    
		    function parser(index){
			    if(obj.pop) return parseInt(index);
			    return index;
			}
	        for(var p in obj){
	            if(fun.call(scope, obj[p], parser.call(this,p), obj) === false) break;
	        }
		},
		defaultInclude : []
	};
})();

Svincs.include(Svincs.defaultInclude);

Svincs.Window = {
	opener: function (){
		if($.browser.msie){
			if(dialogArguments) return dialogArguments;
		}else{
			if(window.opener) return window.opener;
		}
		return null;
	},
	bookmark: function(title, url){
        if (window.sidebar){ //FF
            window.sidebar.addPanel(title, url, '');
        }else if(window.opera && window.print){ // opera
            var elem = document.createElement('a');
                elem.setAttribute('href',url);
                elem.setAttribute('title',title);
                elem.setAttribute('rel','sidebar');
                elem.click();
        }else if($.browser.msie){// ie
            window.external.AddFavorite(url, title);
        }
     }
};
/**
 * Language manager system.
 * @author Evgeni Baldziyski
 * @version 0.3 
 * @since 19.03.2007
 * @event Svincs.LangManager.CHANGE(current lang, before change lang)
 * @example
 * 	<script>
 * 		var form = Svincs.LangManager({
 *			current: "en",
 *			target : $('#lang_sys').get(0)
 *		});
 *		alert(form.langcurrent); // en
 *
 *		form.langchange('fr');
 *		alert(form.langcurrent); // fr
 *	</script>
 *	<form>
 *		<input id="lang_sys" type="hidden"/>
 *
 *		<input name="title" value="this is en field" lang="en" class="lingual"/>
 *		<input name="title" value="this is fr field" lang="fr" class="lingual" style="display:none;"/>
 *	</form>
 *
 *	important html's properties are: 
 *		class="lingual" - show this field in language list
 *		lang="..." - show field's language
 */
Svincs.LangManager = function(options){	
	var prefix = '',
		current = '',
		target = null,
		form = null;
	
	if(options){
		if(options.current) current = options.current;
		if(options.prefix)  prefix  = options.prefix;
		if(options.target)  target  = options.target;
	}
	if(!target){
		document.write('<input type="hidden" id="'+prefix+'lang_sys"\/>');
		form = $('$'.prefix+'lang_sys').get(0).form;
	}else{
		form = target.form;
	}
	
	form.langcurrent = current;
	form.langchange = function (lang){
		if(this.current == lang || !lang) return;
		
		$.each($(this).find('.form_lingual_field'), function (i, el){
			$(el)[$(el).attr('lang') == lang ? 'show' : 'hide']();
		});
		
		var old_lang = this.current;
		this.current = lang;
		
		$(this).trigger(Svincs.LangManager.CHANGE, [lang, old_lang]);
	};
	return form;
};
Svincs.LangManager.CHANGE = 'langchange';
/**
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @since 30.01.2012
 */
Svincs.MenuTargetModal = (function (){
	var div = null;
	var iframe = null;
	function buildIframe(){
		if(!iframe){
			div = $('<div/>');
			iframe = $('<iframe src="about:blank" frameborder="0" style="width:100%"/>').appendTo(div);
		}
		return div;
	}

	return {
		open: function (url, width, height, opener, options){//debugger;
				if(!options) options = {};
				
				options.modal 	   = true,
				options.width  	   = width+20,
				options.height 	   = height+20+(jQuery.browser.msie ? 0 : 20),
				options.minWidth   = width+20,
				options.minHeight  = height+20+(jQuery.browser.msie ? 0 : 20),
				options.dialogClass= options.dialogClass||"menuTargetModal",
				options.autoOpen   = false;
				options.resize 	   = function(e, ui) {
					iframe.height(div.height()-5);
				}
				var d = buildIframe();
				
				iframe.attr("src", url)				
				.bind("load.system", function(e){
					(this.contentWindow||this)["_opener_"] = opener;
				});
				
				d.dialog(options).dialog('open');
				
				iframe.height(div.height()-5);
		},
		close: function (){
			buildIframe().dialog('close');
		}
	};
})();

//ajax.js

/**
 * History/Remote - jQuery plugin for enabling history support and bookmarking
 * @requires jQuery v1.0.3+
 *
 * http://stilbuero.de/jquery/history/
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Version: 0.2.3
 */

(function($) { // block scope

/**
 * Initialize the history manager. Subsequent calls will not result in additional history state change 
 * listeners. Should be called soonest when the DOM is ready, because in IE an iframe needs to be added
 * to the body to enable history support.
 *
 * @example $.ajaxHistory.initialize();
 *
 * @param Function callback A single function that will be executed in case there is no fragment
 *                          identifier in the URL, for example after navigating back to the initial
 *                          state. Use to restore such an initial application state.
 *                          Optional. If specified it will overwrite the default action of 
 *                          emptying all containers that are used to load content into.
 * @type undefined
 *
 * @name $.ajaxHistory.initialize()
 * @cat Plugins/History
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
$.ajaxHistory = new function() {

    var RESET_EVENT = 'historyReset';

    var _currentHash = location.hash;
    var _intervalId = null;
    var _observeHistory; // define outside if/else required by Opera

    this.update = function() { }; // empty function body for graceful degradation

    // create custom event for state reset
    var _defaultReset = function() {
        $('.remote-output').empty();
    };
    $(document).bind(RESET_EVENT, _defaultReset);
    
    // TODO fix for Safari 3
    // if ($.browser.msie)
    // else if hash != _currentHash
    // else check history length

    if ($.browser.msie) {return;

        var _historyIframe, initialized = false; // for IE

        // add hidden iframe
        $(function() {
            _historyIframe = $('<iframe src="about:blank" style="display:none;"/>').appendTo(document.body).get(0);
            var iframe = _historyIframe.contentWindow.document;
            // create initial history entry
            iframe.open();
            iframe.close();
            if (_currentHash && _currentHash != '#') {
                iframe.location.hash = _currentHash.replace('#', '');
            }
        });

        this.update = function(hash) {
            _currentHash = hash;
            var iframe = _historyIframe.contentWindow.document;
            iframe.open();
            iframe.close();
            iframe.location.hash = hash.replace('#', '');
        };

        _observeHistory = function() {
            var iframe = _historyIframe.contentWindow.document;
            var iframeHash = iframe.location.hash;
            if (iframeHash != _currentHash) {
                _currentHash = iframeHash;
                if (iframeHash && iframeHash != '#') {//debugger;
                    // order does matter, set location.hash after triggering the click...
                    $('a[href$="' + iframeHash + '"]').click();
                    location.hash = iframeHash;
                } else if (initialized) {
                    location.hash = '';
                    $(document).trigger(RESET_EVENT);
                }
            }
            initialized = true;
        };

    } else if ($.browser.mozilla || $.browser.opera) {

        this.update = function(hash) {
            _currentHash = hash;
        };

        _observeHistory = function() {
            if (location.hash) {
                if (_currentHash != location.hash) {
                    _currentHash = location.hash;
                    $('a[href$="' + _currentHash + '"]').click();
                }
            } else if (_currentHash) {
                _currentHash = '';
                $(document).trigger(RESET_EVENT);
            }
        };

    } else if ($.browser.safari) {

        var _backStack, _forwardStack, _addHistory; // for Safari

        // etablish back/forward stacks
        $(function() {
            _backStack = [];
            _backStack.length = history.length;
            _forwardStack = [];

        });
        var isFirst = false, initialized = false;
        _addHistory = function(hash) {
            _backStack.push(hash);
            _forwardStack.length = 0; // clear forwardStack (true click occured)
            isFirst = false;
        };

        this.update = function(hash) {
            _currentHash = hash;
            _addHistory(_currentHash);
        };

        _observeHistory = function() {
            var historyDelta = history.length - _backStack.length;
            if (historyDelta) { // back or forward button has been pushed
                isFirst = false;
                if (historyDelta < 0) { // back button has been pushed
                    // move items to forward stack
                    for (var i = 0; i < Math.abs(historyDelta); i++) _forwardStack.unshift(_backStack.pop());
                } else { // forward button has been pushed
                    // move items to back stack
                    for (var i = 0; i < historyDelta; i++) _backStack.push(_forwardStack.shift());
                }
                var cachedHash = _backStack[_backStack.length - 1];
                $('a[href$="' + cachedHash + '"]').click();
                _currentHash = location.hash;
            } else if (_backStack[_backStack.length - 1] == undefined && !isFirst) {
                // back button has been pushed to beginning and URL already pointed to hash (e.g. a bookmark)
                // document.URL doesn't change in Safari
                if (document.URL.indexOf('#') >= 0) {
                    $('a[href$="' + '#' + document.URL.split('#')[1] + '"]').click();
                } else if (initialized) {
                    $(document).trigger(RESET_EVENT);
                }
                isFirst = true;
            }
            initialized = true;
        };

    }
    
    this.is_initialize = false;
    this.initialize = function(callback) {
        // look for hash in current URL (not Safari)
//        if (location.hash && typeof _addHistory == 'undefined') {
//            $('a[href$="' + location.hash + '"]').trigger('click');
//        }
    	if(!this.is_initialize){
	        // custom callback to reset app state (no hash in url)
	        if (typeof callback == 'function') {
	            $(document).unbind(RESET_EVENT, _defaultReset).bind(RESET_EVENT, callback);
	        }
	        // start observer
	        if (_observeHistory && _intervalId == null) {
	            _intervalId = setInterval(_observeHistory, 200); // Safari needs at least 200 ms
	        }
	        this.is_initialize = true;
    	}
    };

};

/**
 * jQuery tag for support a ajax request with history support
 * 
 * @param function callback
 */
$.fn.history = function(callback) {
	
	return this.each(function(i) {
		Svincs.Ajax(this,callback);
	});
};
})(jQuery);
/**
 * Ajax navigations with support browser history paging.
 * Work only with a tags :
 * 		<a href="server resource" id="id for ajax variable">Click for load remote resource</a>
 *  
 *  Call method get on Svincs.Ajax.Tags and use return obj.
 *  
 *  @param DOMObject obj
 *  @param function callback
 */
Svincs.Ajax = function (obj, callback){
	if(!obj) return false;
	
	obj = Svincs.Ajax.Tags.get(obj);
	
	if(obj.nodeName != 'A') return false;
	
	var href = obj.href;
	var href_split = href.split('?');
	var url = href_split[0];
	var data = href_split[1];
		
	obj.href = '#_'+obj.id;
	
    $(obj).click(function(e) {//debugger;
    	if(this.not_access_click) return false;
        if (e.clientX) {
			if (this.hash == location.hash) {
				//return false;
			}else{
				$.ajaxHistory.update(this.hash);
			}
        }
        
    	this.not_access_click = true;
    	$(this).addClass('disabled');
    	
    	var self = this;
        Svincs.Ajax.Remote.call(this, this, url, data, callback, {
        	complete : function (XMLHttpRequest, textStatus){
        		self.not_access_click = false;
        		$(self).removeClass('disabled');
        	}
        });
    });
    if (location.hash == '#_'+obj.id) {
    	$(obj).trigger('click');
    }
};
/**
 * Help Ajax object. Registered tag for ajax tags support.
 */
Svincs.Ajax.Tags = (function(){
	/**
	 * @var object
	 * @access private
	 */
	var tags = {};
	
	return {
		/**
		 * Registered tag type
		 * 
		 * @param string tag
		 * @param function func
		 * @return void
		 */
		add : function (tag, func){
			tags[tag] = func;
		},
		/**
		 * Remove tag from register
		 * 
		 * @param string tag
		 * @return void
		 */
		remove : function (tag){
			delete tags[tag];
		},
		/**
		 * Run tags ajax support 
		 * 
		 * @param DOMObject obj
		 * @return DOMObject
		 */
		get : function (obj){
			$(tags).each(function (key, func){
				if(obj.nodeName == key){
					var tmp = func.call(obj);
					if(typeof tmp == 'object') obj == tmp;
				}
			});
			return obj;
		}
	};
})();
/**
 * Ajax remote access manager.
 * Send variable "ajax" to server with value object's id. 
 * 
 * @options
 * 	type 		[GET]
 * 	global 		[true]
 * 	dataType 	[string]
 * 	async 		[false]
 * 	complete 	[function]
 * 	error 		[function]
 * 
 * 	// new 
 * 	service
 * 	loader(HTML Object)
 */
Svincs.Ajax.Remote = function (obj, url, data, callback, options){//debugger;
	if(typeof options == 'undefined'){
		options = {};
	}
	if(!obj) obj = {};
	
	if(typeof options.complete == 'undefined'){
    	if(obj.not_access_event) return false;
    	   obj.not_access_event = true;
    	   
		$(obj).attr('disabled','disabled').addClass('disabled');
		if(options.loader) $(options.loader).show();
		
		options.complete = function (callbackContext, xhr, status){//debugger;
			try{
				var err = eval(callbackContext.responseText)[0];
				if(err.errCode){
					if(err.errCode == '801'){ //session timeout
						location.href = location.href;
					}else{
						if(options.error){
							options.error.apply(this, [err.errCode, err.errMessage]);
							return;
						}
					}
				}
			}catch(e){}

			obj.not_access_event = false;
			$(obj).removeAttr("disabled").removeClass('disabled');
			if(options.loader) $(options.loader).hide();
		};
	}
	
	var __id = '';
	if(options.service){
		__id = options.service;
	}else{
		__id = obj.id.replace(/^_/,'');
	}
	if(typeof data == 'object'){
		data['ajax'] = __id;
	}else{
		data += '&ajax='+__id;
	}
	
	if(typeof callback == 'object' && $.isArray(callback)){
		var fnc = callback;
		callback = function(data, textStatus){			
			var params = [data, textStatus];
			
			func = eval(fnc.shift());
			
			for(var i=0;i<fnc.length;i++){
				params.push(fnc[i]);
			}
			func.apply(this, params);
		};
	}
	
	$.ajax({
		type 		: (options.type === 'GET' ? 'GET' : 'POST'),
		url 		: (url),
		global 		: (options.global === true ? true : false),
		dataType 	: (options.dataType ? options.dataType : 'text'),
		data 		: (data),
		async 		: (options.async === false ? false : true),
		
		complete 	: options.complete,
		success 	: callback,
		error 		: options.error
	});
};
Svincs.Ajax.Register = (function(){
	
	var register = [];
	var first = false;
	
	return function (id, collback){//debugger;
		if(!first){
			$(document).ready(function (){
				for(p in register){//debugger;
					Svincs.Ajax(document.getElementById(register[p][0]),register[p][1]);
				};
			});
			first = true;
		}
		register.push([id, collback]);
	};
})();
Svincs.Ajax.Parser = function (html, obj){//debugger;
	$('#'+obj).html(html);
};