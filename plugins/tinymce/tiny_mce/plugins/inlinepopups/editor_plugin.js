(function(){var d=tinymce.DOM,b=tinymce.dom.Element,a=tinymce.dom.Event,e=tinymce.each,c=tinymce.is;tinymce.create("tinymce.plugins.InlinePopups",{init:function(f,g){f.onBeforeRenderUI.add(function(){f.windowManager=new tinymce.InlineWindowManager(f);d.loadCSS(g+"/skins/"+(f.settings.inlinepopups_skin||"clearlooks2")+"/window.css")})},getInfo:function(){return{longname:"InlinePopups",author:"Moxiecode Systems AB",authorurl:"http://tinymce.moxiecode.com",infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/inlinepopups",version:tinymce.majorVersion+"."+tinymce.minorVersion}}});tinymce.create("tinymce.InlineWindowManager:tinymce.WindowManager",{InlineWindowManager:function(f){var g=this;g.parent(f);g.zIndex=300000;g.count=0;g.windows={}},
	open:function(s,j){
		var z=this,i,
		k="",
		r=z.editor,
		g=0,v=0,h,m,o,q,l,x,y,n;s=s||{};j=j||{};
		if(!s.inline){return z.parent(s,j)}
		n=z._frontWindow();
		if(n&&d.get(n.id+"_ifr")){
			n.focussedElement=d.get(n.id+"_ifr").contentWindow.document.activeElement
		}
		if(!s.type){z.bookmark=r.selection.getBookmark(1)}
		i=d.uniqueId();
		h=d.getViewPort();
		s.width=parseInt(s.width||320);
		s.height=parseInt(s.height||240)+(tinymce.isIE?8:0);
		s.min_width=parseInt(s.min_width||150);
		s.min_height=parseInt(s.min_height||100);
		s.max_width=parseInt(s.max_width||2000);
		s.max_height=parseInt(s.max_height||2000);
		s.left=s.left||Math.round(Math.max(h.x,h.x+(h.w/2)-(s.width/2)));
		s.top=s.top||Math.round(Math.max(h.y,h.y+(h.h/2)-(s.height/2)));
		s.movable=s.resizable=true;
		j.mce_width=s.width;
		j.mce_height=s.height;
		j.mce_inline=true;
		j.mce_window_id=i;
		j.mce_auto_focus=s.auto_focus;
		z.features=s;
		z.params=j;
		z.onOpen.dispatch(z,s,j);
		if(s.type){
			k+=" mceModal";
			if(s.type){
				k+=" mce"+s.type.substring(0,1).toUpperCase()+s.type.substring(1)
			}
			s.resizable=false
		}
		if(s.statusbar){k+=" mceStatusbar"}
		if(s.resizable){k+=" mceResizable"}
		if(s.minimizable){k+=" mceMinimizable"}
		if(s.maximizable){k+=" mceMaximizable"}
		if(s.movable){k+=" mceMovable"}
		z._addAll(d.doc.body,
			["div",{
				id:i,
				role:"dialog",
				"aria-labelledby":s.type?i+"_content":i+"_title",
				"class":(r.settings.inlinepopups_skin||"clearlooks2")+(tinymce.isIE&&window.getSelection?" ie9":""),
				style:"width:100px;height:100px"
			},
			["div",{
				id:i+"_wrapper",
				"class":"mceWrapper"+k
			},
			["div",{
				id:i+"_top",
				"class":"mceTop"
			},["div",{"class":"mceLeft"}],["div",{"class":"mceCenter"}],["div",{"class":"mceRight"}],["span",{id:i+"_title"},s.title||""]],["div",{id:i+"_middle","class":"mceMiddle"},["div",{id:i+"_left","class":"mceLeft",tabindex:"0"}],["span",{id:i+"_content"}],["div",{id:i+"_right","class":"mceRight",tabindex:"0"}]],["div",{id:i+"_bottom","class":"mceBottom"},["div",{"class":"mceLeft"}],["div",{"class":"mceCenter"}],["div",{"class":"mceRight"}],["span",{id:i+"_status"},"Content"]],["a",{"class":"mceMove",tabindex:"-1",href:"javascript:;"}],["a",{"class":"mceMin",tabindex:"-1",href:"javascript:;",onmousedown:"return false;"}],["a",{"class":"mceMax",tabindex:"-1",href:"javascript:;",onmousedown:"return false;"}],["a",{"class":"mceMed",tabindex:"-1",href:"javascript:;",onmousedown:"return false;"}],["a",{"class":"mceClose",tabindex:"-1",href:"javascript:;",onmousedown:"return false;"}],["a",{id:i+"_resize_n","class":"mceResize mceResizeN",tabindex:"-1",href:"javascript:;"}],["a",{id:i+"_resize_s","class":"mceResize mceResizeS",tabindex:"-1",href:"javascript:;"}],["a",{id:i+"_resize_w","class":"mceResize mceResizeW",tabindex:"-1",href:"javascript:;"}],["a",{id:i+"_resize_e","class":"mceResize mceResizeE",tabindex:"-1",href:"javascript:;"}],["a",{id:i+"_resize_nw","class":"mceResize mceResizeNW",tabindex:"-1",href:"javascript:;"}],["a",{id:i+"_resize_ne","class":"mceResize mceResizeNE",tabindex:"-1",href:"javascript:;"}],["a",{id:i+"_resize_sw","class":"mceResize mceResizeSW",tabindex:"-1",href:"javascript:;"}],["a",{id:i+"_resize_se","class":"mceResize mceResizeSE",tabindex:"-1",href:"javascript:;"}]]]);d.setStyles(i,{top:-10000,left:-10000});if(tinymce.isGecko){d.setStyle(i,"overflow","auto")}if(!s.type){g+=d.get(i+"_left").clientWidth;g+=d.get(i+"_right").clientWidth;v+=d.get(i+"_top").clientHeight;v+=d.get(i+"_bottom").clientHeight}d.setStyles(i,{top:s.top,left:s.left,width:s.width+g,height:s.height+v});
			
			y=s.url||s.file;
			if(y){
				if(tinymce.relaxedDomain){
					y+=(y.indexOf("?")==-1?"?":"&")+"mce_rdomain="+tinymce.relaxedDomain
				}
				y=tinymce._addVer(y)
			}
			//debugger;
			if(!s.type){
				d.add(i+"_content","iframe",{
					id:i+"_ifr",
					src:'javascript:""',
					frameBorder:0,
					style:"border:0;width:10px;height:10px"
				});
				d.setStyles(i+"_ifr",{width:s.width,height:s.height});
				d.setAttrib(i+"_ifr","src",y)
			}else{
				d.add(i+"_wrapper","a",{
					id:i+"_ok","class":"mceButton mceOk",
					href:"javascript:;",
					onmousedown:"return false;"
				},"Ok");
				if(s.type=="confirm"){
					d.add(i+"_wrapper","a",{"class":"mceButton mceCancel",href:"javascript:;",onmousedown:"return false;"},"Cancel")
				}
				d.add(i+"_middle","div",{"class":"mceIcon"});
				d.setHTML(i+"_content",s.content.replace("\n","<br />"));
				a.add(i,"keyup",function(f){var p=27;if(f.keyCode===p){s.button_func(false);return a.cancel(f)}});
				a.add(i,"keydown",function(f){
					var t,p=9;
					if(f.keyCode===p){
						t=d.select("a.mceCancel",i+"_wrapper")[0];
						if(t&&t!==f.target){
							t.focus()
						}else{
							d.get(i+"_ok").focus()
						}
						return a.cancel(f)
					}
				})
			}
			o=a.add(i,"mousedown",function(t){
				var u=t.target,f,p;f=z.windows[i];z.focus(i);if(u.nodeName=="A"||u.nodeName=="a"){
					if(u.className=="mceClose"){z.close(null,i);return a.cancel(t)}else{if(u.className=="mceMax"){f.oldPos=f.element.getXY();f.oldSize=f.element.getSize();p=d.getViewPort();p.w-=2;p.h-=2;f.element.moveTo(p.x,p.y);f.element.resizeTo(p.w,p.h);d.setStyles(i+"_ifr",{width:p.w-f.deltaWidth,height:p.h-f.deltaHeight});d.addClass(i+"_wrapper","mceMaximized")}else{if(u.className=="mceMed"){f.element.moveTo(f.oldPos.x,f.oldPos.y);f.element.resizeTo(f.oldSize.w,f.oldSize.h);f.iframeElement.resizeTo(f.oldSize.w-f.deltaWidth,f.oldSize.h-f.deltaHeight);d.removeClass(i+"_wrapper","mceMaximized")}else{if(u.className=="mceMove"){
						return z._startDrag(i,t,u.className)}else{if(d.hasClass(u,"mceResize")){return z._startDrag(i,t,u.className.substring(13))}}}}}}});
			q=a.add(i,"click",function(f){var p=f.target;z.focus(i);if(p.nodeName=="A"||p.nodeName=="a"){switch(p.className){case"mceClose":z.close(null,i);return a.cancel(f);case"mceButton mceOk":case"mceButton mceCancel":s.button_func(p.className=="mceButton mceOk");return a.cancel(f)}}});
			a.add([i+"_left",i+"_right"],"focus",function(p){
				var t=d.get(i+"_ifr");
			
				if(t){
					var f=t.contentWindow.document.body;
					var u=d.select(":input:enabled,*[tabindex=0]",f);
					if(p.target.id===(i+"_left")){
						u[u.length-1].focus()
					}else{
						u[0].focus()}
				}else{
					d.get(i+"_ok").focus()
				}
			});
			x=z.windows[i]={
					id:i,
					mousedown_func:o,
					click_func:q,
					element:new b(i,{blocker:1,container:r.getContainer()}),
					iframeElement:new b(i+"_ifr"),
					features:s,
					deltaWidth:g,
					deltaHeight:v
			};
			x.iframeElement.on("focus",function(){
				z.focus(i)
			});
			if(z.count==0&&z.editor.getParam("dialog_type","modal")=="modal"){
				d.add(d.doc.body,"div",{
					id:"mceModalBlocker","class":(z.editor.settings.inlinepopups_skin||"clearlooks2")+"_modalBlocker",style:{zIndex:z.zIndex-1}
				});
				d.show("mceModalBlocker");
				d.setAttrib(d.doc.body,"aria-hidden","true")
			}else{
				d.setStyle("mceModalBlocker","z-index",z.zIndex-1)
			}
			if(tinymce.isIE6||/Firefox\/2\./.test(navigator.userAgent)||(tinymce.isIE&&!d.boxModel)){
				d.setStyles("mceModalBlocker",{position:"absolute",left:h.x,top:h.y,width:h.w-2,height:h.h-2})
			}
			d.setAttrib(i,"aria-hidden","false");
			z.focus(i);
			z._fixIELayout(i,1);
			
			if(d.get(i+"_ok")){
				d.get(i+"_ok").focus()
			}
			z.count++;
			return x
		},
		focus:function(h){
			var g=this,f;
			if(f=g.windows[h]){
				f.zIndex=this.zIndex++;f.element.setStyle("zIndex",f.zIndex);
				f.element.update();h=h+"_wrapper";
				d.removeClass(g.lastId,"mceFocus");
				d.addClass(h,"mceFocus");
				g.lastId=h;
				if(f.focussedElement){f.focussedElement.focus()}else{if(d.get(h+"_ok")){d.get(f.id+"_ok").focus()}else{if(d.get(f.id+"_ifr")){d.get(f.id+"_ifr").focus()}}}}
		},
		_addAll:function(k,h){
			var g,l,f=this,j=tinymce.DOM;if(c(h,"string")){k.appendChild(j.doc.createTextNode(h))}else{if(h.length){k=k.appendChild(j.create(h[0],h[1]));for(g=2;g<h.length;g++){f._addAll(k,h[g])}}}
		},
		_startDrag:function(v,G,E){var o=this,u,z,C=d.doc,f,l=o.windows[v],h=l.element,y=h.getXY(),x,q,F,g,A,s,r,j,i,m,k,n,B;g={x:0,y:0};A=d.getViewPort();A.w-=2;A.h-=2;j=G.screenX;i=G.screenY;m=k=n=B=0;u=a.add(C,"mouseup",function(p){a.remove(C,"mouseup",u);a.remove(C,"mousemove",z);if(f){f.remove()}h.moveBy(m,k);h.resizeBy(n,B);q=h.getSize();d.setStyles(v+"_ifr",{width:q.w-l.deltaWidth,height:q.h-l.deltaHeight});o._fixIELayout(v,1);return a.cancel(p)});if(E!="Move"){D()}function D(){if(f){return}o._fixIELayout(v,0);d.add(C.body,"div",{id:"mceEventBlocker","class":"mceEventBlocker "+(o.editor.settings.inlinepopups_skin||"clearlooks2"),style:{zIndex:o.zIndex+1}});if(tinymce.isIE6||(tinymce.isIE&&!d.boxModel)){d.setStyles("mceEventBlocker",{position:"absolute",left:A.x,top:A.y,width:A.w-2,height:A.h-2})}f=new b("mceEventBlocker");f.update();x=h.getXY();q=h.getSize();s=g.x+x.x-A.x;r=g.y+x.y-A.y;d.add(f.get(),"div",{id:"mcePlaceHolder","class":"mcePlaceHolder",style:{left:s,top:r,width:q.w,height:q.h}});F=new b("mcePlaceHolder")}z=a.add(C,"mousemove",function(w){var p,H,t;D();p=w.screenX-j;H=w.screenY-i;switch(E){case"ResizeW":m=p;n=0-p;break;case"ResizeE":n=p;break;case"ResizeN":case"ResizeNW":case"ResizeNE":if(E=="ResizeNW"){m=p;n=0-p}else{if(E=="ResizeNE"){n=p}}k=H;B=0-H;break;case"ResizeS":case"ResizeSW":case"ResizeSE":if(E=="ResizeSW"){m=p;n=0-p}else{if(E=="ResizeSE"){n=p}}B=H;break;case"mceMove":m=p;k=H;break}if(n<(t=l.features.min_width-q.w)){if(m!==0){m+=n-t}n=t}if(B<(t=l.features.min_height-q.h)){if(k!==0){k+=B-t}B=t}n=Math.min(n,l.features.max_width-q.w);B=Math.min(B,l.features.max_height-q.h);m=Math.max(m,A.x-(s+A.x));k=Math.max(k,A.y-(r+A.y));m=Math.min(m,(A.w+A.x)-(s+q.w+A.x));k=Math.min(k,(A.h+A.y)-(r+q.h+A.y));if(m+k!==0){if(s+m<0){m=0}if(r+k<0){k=0}F.moveTo(s+m,r+k)}if(n+B!==0){F.resizeTo(q.w+n,q.h+B)}return a.cancel(w)});return a.cancel(G)},resizeBy:function(g,h,i){var f=this.windows[i];if(f){f.element.resizeBy(g,h);f.iframeElement.resizeBy(g,h)}},
		close:function(i,k){var g=this,f,j=d.doc,h,k;k=g._findId(k||i);if(!g.windows[k]){g.parent(i);return}g.count--;if(g.count==0){d.remove("mceModalBlocker");d.setAttrib(d.doc.body,"aria-hidden","false");g.editor.focus()}if(f=g.windows[k]){g.onClose.dispatch(g);a.remove(j,"mousedown",f.mousedownFunc);a.remove(j,"click",f.clickFunc);a.clear(k);a.clear(k+"_ifr");d.setAttrib(k+"_ifr","src",'javascript:""');f.element.remove();delete g.windows[k];h=g._frontWindow();if(h){g.focus(h.id)}}},
		_frontWindow:function(){var g,f=0;e(this.windows,function(h){if(h.zIndex>f){g=h;f=h.zIndex}});return g},setTitle:function(f,g){var h;f=this._findId(f);if(h=d.get(f+"_title")){h.innerHTML=d.encode(g)}},alert:function(g,f,j){var i=this,h;h=i.open({title:i,type:"alert",button_func:function(k){if(f){f.call(k||i,k)}i.close(null,h.id)},content:d.encode(i.editor.getLang(g,g)),inline:1,width:400,height:130})},confirm:function(g,f,j){var i=this,h;h=i.open({title:i,type:"confirm",button_func:function(k){if(f){f.call(k||i,k)}i.close(null,h.id)},content:d.encode(i.editor.getLang(g,g)),inline:1,width:400,height:130})},_findId:function(f){var g=this;if(typeof(f)=="string"){return f}e(g.windows,function(h){var i=d.get(h.id+"_ifr");if(i&&f==i.contentWindow){f=h.id;return false}});return f},
		_fixIELayout:function(i,h){var f,g;if(!tinymce.isIE6){return}e(["n","s","w","e","nw","ne","sw","se"],function(j){var k=d.get(i+"_resize_"+j);d.setStyles(k,{width:h?k.clientWidth:"",height:h?k.clientHeight:"",cursor:d.getStyle(k,"cursor",1)});d.setStyle(i+"_bottom","bottom","-1px");k=0});if(f=this.windows[i]){f.element.hide();f.element.show();e(d.select("div,a",i),function(k,j){if(k.currentStyle.backgroundImage!="none"){g=new Image();g.src=k.currentStyle.backgroundImage.replace(/url\(\"(.+)\"\)/,"$1")}});d.get(i).style.filter=""}}});tinymce.PluginManager.add("inlinepopups",tinymce.plugins.InlinePopups)
})();