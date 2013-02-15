<?php
/**
 * @author Evgeni Baldzhiyski
 * @version 1.3
 * @since 13.09.2012
 */
class TinyMCE extends BasicControl implements FormControlInterface{
	/**
	 * special parameters:
	 * 		boolen navigation_bar 
	 * 
	 * @see FormControlInterface::convertOut()
	 */
	public function convertOut($value) {
		$tmp = $value;
		$tmp = stripslashes($value);
		$tmp = str_replace("[HOST]", BASIC::init()->ini_get('root_virtual'), $tmp);

		return $tmp;
	}
	public function convertIn($value){
		return str_replace(BASIC::init()->virtual(), "[HOST]", $value);
	}

	public function generate($name, $value, $attributes = array()) {
		$this->init($name, $value, $attributes);
		
		BASIC_GENERATOR::init()->head('HTMLTextarea', 'script', array('src' => BASIC::init()->ini_get('root_virtual').'plugins/tinymce/tiny_mce/tiny_mce.js'), ' ');
		
		$css = ''; if(isset($this->attributes['css'])){ 
			$css = $GLOBALS['BASIC']->ini_get('root_virtual') . $this->attributes['css'];
			unset($this->attributes['css']);
		}

		$value = stripslashes($value);
		$value = preg_replace("/[\r\n\t]/", "", $value);
		//$value = str_replace(">", "&gt;", $value);
		//$value = str_replace("<", "&lt;", $value);
		$value = str_replace("'", "\\'", $value);

		if(isset($this->attributes['width'])){
			if(strpos($this->attributes['width'], '%') === false){
				$this->attributes['width'] = $this->attributes['width'].'px';
			}
		}else{
			$this->attributes['width'] = '100%';
		}
		if(isset($this->attributes['height'])){
			if(strpos($this->attributes['height'], '%') === false){
				$this->attributes['height'] = $this->attributes['height'].'px';
			}
		}else{
			$this->attributes['height'] = '250px';
		}
		if(!isset($this->attributes['manager'])){
			$this->attributes['manager'] = BASIC::init()->ini_get('root_virtual').BASIC::init()->dirName().BASIC::init()->scriptName().'?editor=assetmanager';	
		}else{
			$this->attributes['manager'] = BASIC::init()->ini_get('root_virtual').str_replace(BASIC::init()->ini_get('root_virtual'), '', $this->attributes['manager']);
		}
		
		$scr = 'tinyMCE.init({
			mode : "exact",
			elements : "'.$name.'",
			theme : "'.(isset($this->attributes['theme']) ? $this->attributes['theme'] : 'advanced').'",
			skin : "'.(isset($this->attributes['skin']) ? $this->attributes['skin'] : 'default').'",
			plugins : "'.(isset($this->attributes['plugins']) ? $this->attributes['plugins'] : 'lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave').'",
	
 			theme_advanced_buttons1 : "'.$this->convertButtons((isset($this->attributes['buttons']) ? $this->attributes['buttons'] : null), 'newdocument,pasteword,|,undo,redo,|,bold,italic,underline,strikethrough,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,outdent,indent,|,image,media,|,cleanup,code').'",
        	theme_advanced_buttons2 : "'.$this->convertButtons((isset($this->attributes['buttons2']) ? $this->attributes['buttons2'] : null), 'formatselect,fontselect,fontsizeselect,|,styleprops,|,tablecontrols').'",
        	theme_advanced_buttons3 : "'.$this->convertButtons((isset($this->attributes['buttons3']) && $this->attributes['buttons3'] != 'all' ? $this->attributes['buttons3'] : null), 
				(isset($this->attributes['buttons3']) && $this->attributes['buttons3'] == 'all' ? 'hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage' : '')).'",
        	theme_advanced_buttons4 : "'.$this->convertButtons((isset($this->attributes['buttons4']) ? $this->attributes['buttons4'] : null), '').'",
        
			theme_advanced_toolbar_location : "'.(isset($this->attributes['location']) ? $this->attributes['location'] : 'top').'",
			theme_advanced_toolbar_align : "'.(isset($this->attributes['align']) ? $this->attributes['align'] : 'left').'",
			theme_advanced_statusbar_location : "'.(isset($this->attributes['location']) ? $this->attributes['location'] : 'botton').'",
			theme_advanced_resizing : '.(isset($this->attributes['resizing']) ? $this->attributes['resizing'] : 'true').',
			
			document_base_url : "'.BASIC::init()->virtual().'",
			relative_urls : false, 
			remove_script_host : false,
			
			content_css : "'.$css.'",
			file_browser_callback: function (){
				var editor = tinyMCE.activeEditor;
			
				window.cms_tiny_popup = editor.windowManager.open({
					file:"'.$this->attributes['manager'].'",
					width:600,
					height:500,
					inline:1
				},{
					plugin_url:"'.$this->attributes['manager'].'"
				});
			},
	
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js",
	
			template_replace_values : {
				username : "Some User",
				staffid : "991234"
			},
			width: "'.$this->attributes['width'].'",
			height: "'.$this->attributes['height'].'"
		});';
		BASIC_GENERATOR::init()->head("HTMLTextarea_ctrl_".$name, 'script', null, $scr);
		
		$tmp = '';
		$tmp = '<div'.
			(isset($this->attributes['lang']) ? ' lang="'.$this->attributes['lang'].'"' : '').
			(isset($this->attributes['class']) ? ' class="'.$this->attributes['class'].'"' : ''). 
			(isset($this->attributes['style']) ? ' style="'.$this->attributes['style'].'"' : '').
		'>'.
			'<textarea class="tiny_mce" id="'.$this->attributes['id'].'" name="'.$name.'">'.$value.'</textarea>'.
		'</div>';
		
		return $tmp;
	}
	
	protected function convertButtons($btn, $default){
		if($btn){
			$btns = array();
			if($default){
				foreach (explode(";", $btn) as $v){
					$exp = explode(":", $v);
					
					$btns[$exp[0]] = $exp[1];
				}
			}
			$tmp = '';
			foreach (explode(",", $default) as $v){
				if($tmp) $tmp .= ',';
				
				if(($btns && isset($btns[$v]) && $btns[$v]) || (!$btns && $btns[$v])){
					$tmp .= $v;
				}
			}
			
			if($tmp){
				return $tmp;
			}
		}
		return $default;
	}
}