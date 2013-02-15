<?php
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
* @package basic.generator
* @version 7.0.4  
*/

BASIC::init()->imported('xml.mod');
/**
 * @version 0.1
 * @author Evgeni Baldzhiyski
 * @package basic.generator
 */
interface FormControlInterface{
	/**
	 * @param string|integer $value
	 * @return string|integer
	 */
	function convertIn($value);
	/**
	 * @param string|integer $value
	 * @return string|integer
	 */
	function convertOut($value);
	/**
	 * @param string $name
	 * @param string|integger $value
	 * @param hashmap $attributes
	 * @return string
	 */
	function generate($name, $value, $attributes = array());
	/**
	 * @return boolean
	 */
	function isMultiple();
	/**
	 * @return boolean
	 */	
	function isFileUpload();
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class BasicControl implements FormControlInterface{
	/**
	 * @var hashmap
	 */
	protected $attributes = array();
	/**
	 * @var hashmap
	 */
	protected $data = array();
	/**
	 * Initializing of $attribute and $data
	 * 
	 * @param string $name
	 * @param mix $value
	 * @param array $attributes
	 * @return void
	 */
	protected function init($name, $value, $attributes = array()){
		$this->attributes = BASIC_GENERATOR::init()->convertStringAtt($attributes);
		$name = BasicControl::uId($name);

		if($name){
			$this->attributes['name'] = $name;
		}
		if(!isset($this->attributes['id']) && $name){
			$this->attributes['id'] = $name;	
		}
		$this->attributes['value'] = $value;
		
		if(isset($this->attributes['data'])){
			$this->data = $this->attributes['data'];
			unset($this->attributes['data']);
		}
	}
	/**
	 * Generate html for input element
	 * 
	 *  @access public
	 *  @param string $name
	 *  @param string $value
	 *  @param array $attributes
	 *  @return string
	 * @see FormControlInterface::generate()
	 */
	function generate($name, $value, $attributes = array()){
		$this->init($name, $value, $attributes);

		if(!isset($this->attributes['type'])){
			$this->attributes['type'] = 'text';
		}
		
		return BASIC_GENERATOR::init()->createCloseTag('input', $this->attributes)."\n";
	}
	/**
	 * @see FormControlInterface::convertIn()
	 */
	function convertIn($value){
		return $value;
	}
	/**
	 * @see FormControlInterface::convertOut()
	 */
	function convertOut($value){
		return $value;
	}
	/**
	 * @see FormControlInterface::isMultiple()
	 */
	function isMultiple(){
		return false;
	}
	/**
	 * @see FormControlInterface::isFileUpload()
	 */
	function isFileUpload(){
		return false;
	}
	function fieldNames(){
		return array();
	}
	
	/**
	 * Help property
	 * 
	 * @staticvar
	 * @access private
	 * @var integer
	 */
	static private $_uid = 0;
	/**
	 * Generate unique control id
	 * 
	 * @access public
	 * @static
	 * @param string [$name]
	 * @return string
	 */
	static public function uId($name = ''){
		if($name == '' || $name == null){
			$name .= "basecontrol_".(self::$_uid++);
		}
		return $name;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class PasswordControl extends BasicControl implements FormControlInterface{
	/**
	 * Generate password control html
	 * @access public
	 * @param string $name
	 * @param string [$value] not used
	 * @param array $attributes
	 * @return string
	 * @package basic.generator.passwordcontrol
	 */
	public function generate($name, $value, $attributes = array()) {
		$this->init($name, $value, $attributes);
		
		$this->attributes['type'] = 'password';
		$this->attributes['value'] = '';
		
		return BASIC_GENERATOR::init()->createCloseTag('input', $this->attributes)."\n";
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class CheckBoxControl extends BasicControl implements FormControlInterface{
	/**
	 * Generate checkbox control html
	 * @access public
	 * @param string $name
	 * @param string [$value] not used
	 * @param array $attributes
	 * @return string
	 * @package basic.generator.checkboxcontrol
	 */
	public function generate($name, $value, $attributes = array()) {
		$this->init($name, $value, $attributes);
		
		$this->attributes['type'] = 'checkbox';
		$this->attributes['value'] = 1;
		
		if(isset($this->attributes['class'])){
			$this->attributes['class'] = 'checkbox '.$attributes['class'];
		}else{
			$this->attributes['class'] = 'checkbox';
		}
		
		if($value) $this->attributes['checked'] = 'checked';
		
		return BASIC_GENERATOR::init()->createCloseTag('input', $this->attributes)."\n";
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class TextareaControl extends BasicControl implements FormControlInterface{
	/**
	 * Generate textarea control html
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array $attributes boolean allow_html - show html editor
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()) {
		if(isset($attributes['allow_html']) && $attributes['allow_html']){
			unset($attributes['allow_html']);
			return BASIC_GENERATOR::init()->getControl('html')->generate($name, $value, $attributes);
		}
		
		$this->init($name, $value, $attributes);

		if(isset($this->attributes['maxlength']) && (int)$this->attributes['maxlength']){
		    $length = (int)$this->attributes['maxlength'];
		    if(!isset($this->attributes['id'])){
		        $name = $this->attributes['id'] = uniqid();
		    }
		}

		return BASIC_GENERATOR::init()->createTag('textarea', $this->attributes, $value);
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class HtmlControl extends TextareaControl implements FormControlInterface{}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class DateControl extends BasicControl implements FormControlInterface{
	/**
	 *Generate date control html.Used ISO time standart {yyyy-mm-dd hh:mm:ss}
	 * 
	 * new attribute
	 * 	disabled = [true|1]
	 *  dkey = true[false] view key checkbox
	 * 	format - [%Y-%m-%d %H:%M %p]
	 *
	 *	 ("inputField",null);
	 *	 ("displayArea",null);
	 *	 ("button",null);
	 *	 ("eventName","click");
	 *	 ("ifFormat","%Y/%m/%d");
	 *	 ("daFormat","%Y/%m/%d");
	 *	 ("singleClick",true);
	 *	 ("disableFunc",null);
	 *	 ("dateStatusFunc",params["disableFunc"]);
	 *	 ("dateText",null);
	 *	 ("firstDay",null);
	 *	 ("align","Br");
	 *	 ("range",[1900,2999]);
	 *	 ("weekNumbers",true);
	 *	 ("flat",null);
	 *	 ("flatCallback",null);
	 *	 ("onSelect",null);
	 *	 ("onClose",null);
	 *	 ("onUpdate",null);
	 *	 ("date",null);
	 *	 ("showsTime",false);
	 *	 ("timeFormat","24");
	 *	 ("electric",true);
	 *	 ("step",2);
	 *	 ("position",null);
	 *	 ("cache",false);
	 *	 ("showOthers",false);
	 *	 ("multiple",null);
	 *
	 * 	skin [default is basic_path/scripts/calendar/skins/]
	 * 
	 * @access public
	 * @param string $name
	 * @param string $value 
	 * @param array [$attributes]
	 * @return string
	 * @see FormControlInterface::generate()
	 */
	public function generate($name, $value, $attributes = array()) {
		$this->init($name, $value, $attributes);
		
		$tmp = '';

		$this->attributes['type'] = 'text';
		
		$oName = $name;
		$oName = str_replace("#", '', $oName);
		$oName = str_replace("[]", self::uId(), $oName);
		
		$specialAttr = array();
		
		if(!isset($this->attributes['format'])){
			$format = '%Y-%m-%d %H:%M %p';
		}else{
			$format = $this->attributes['format'];
			unset($this->attributes['format']);
		}
		$dformat = 'int';
		if(isset($this->attributes['dataformat']) && $this->attributes['dataformat'] == 'str'){	
			$dformat = 'str';
			unset($this->attributes['dataformat']);
		}
		$specialAttr[] = 'dataformat:"'.$dformat.'"';
		
		if($dformat == 'str'){
			$value = (!$value || $value == '0000-00-00 00:00:00' || $value == '0000-00-00' || !(int)preg_replace('/[^0-9]*/', '', $value) ? time() : strtotime($value));
			$value = date('Y-m-d H:i:s', $value);
		}else{
			if(!$value) $value = time();
		}
		
		if(!isset($this->attributes['class'])){
			$this->attributes['class'] = 'formDate '.$oName;
		}else{
			$this->attributes['class'] = $this->attributes['class'].' formDate '.$oName;
		}
		if(isset($this->attributes['skin'])){
			$this->head('scin','link',"media=all|href=".$this->attributes['skin']);
			unset($this->attributes['skin']);
		}else{
			BASIC_GENERATOR::init()->head('scin', 'link', "media=all|href=".BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path')."scripts/calendar/skins/theme.css");
		}		
		if(isset($this->attributes['disabled'])){
		    if($this->attributes['disabled'] && !BASIC_URL::init()->request($name)){
			     $specialAttr[] = 'disabled:true';
		    }
			unset($this->attributes['disabled']);
		}
		if(isset($this->attributes['dkey'])){
			$specialAttr[] = 'dkey:'.($this->attributes['dkey'] ? 'true' : 'false');
			unset($this->attributes['dkey']);
		}
		if(isset($this->attributes['minDate'])){
			$specialAttr[] = 'minDate:"'.$this->attributes['minDate'].'"';
			unset($this->attributes['minDate']);
		}
		if(isset($this->attributes['maxDate'])){
			$specialAttr[] = 'maxDate:"'.$this->attributes['maxDate'].'"';
			unset($this->attributes['maxDate']);
		}
		if(isset($this->attributes['related'])){
			$specialAttr[] = 'related:"'.$this->attributes['related'].'"';
			unset($this->attributes['related']);
		}

		if(!isset($this->loadscripts['formDate'])){
			BASIC_GENERATOR::init()->head('calendar', 'script',array("type"=>"text/javascript", "src"=>BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path')."scripts/calendar/calendar.js")," ");
			BASIC_GENERATOR::init()->head('calendar_d', 'script',array("type"=>"text/javascript", "src"=>BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path')."scripts/calendar/setup.js")," ");

			BASIC_GENERATOR::init()->head('calendar_l','script',array("type"=>"text/javascript", "src"=>BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path')."scripts/calendar/formdate.js")," ");
			$this->loadscripts['formDate'] = true;
		}
		if(isset($this->attributes['id'])){
			unset($this->attributes['id']);
		}		
		BASIC_GENERATOR::init()->head('calendar_c_'.$name, 'script', array("type"=>"text/javascript"),"
			$(document).ready(function (){
				var d = formDate('".$name."','".$value."','".$format."','".BASIC_GENERATOR::init()->convertAtrribute($this->attributes)."',{".implode(",", $specialAttr)."});
				".$tmp."
			});	
		");
			
		return BASIC_GENERATOR::init()->createCloseTag('input', $this->attributes+array('value' => $value, 'id' => $name))."\n";
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.3
 * @package basic.generator
 */
class SelectControl extends BasicControl implements FormControlInterface{
	/**
	 * Generate select control html
	 * 
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()) {
		$this->init($name, $value, $attributes);
		
		if($name){
			$this->attributes['name'] = (isset($this->attributes['multiple']) ? $name.'[]' : $name);
			if(!isset($this->attributes['id'])) $this->attributes['id'] = $name;
		}
		
		if(isset($this->attributes['multiple'])){
			if(isset($this->attributes['class'])){
				$this->attributes['class'] .= " multiple";
			}else{
				$this->attributes['class'] = "multiple";
			}
		}
		unset($this->attributes['value']);
		unset($this->attributes['maxlength']);
		
		$is_open_group = false;
		$tmp = '';
		$tmp .= BASIC_GENERATOR::init()->createOpen('select', $this->attributes);
		foreach ($this->data as $key => $txt){
			
			$att = array();
			if(is_array($txt)){
				$att = $txt;
				
				if(!isset($att['label'])){
					foreach ($txt as $k => $v){
						$txt = $txt[$k]; break;
					}
				}else{
					$txt = $att['label'];
					unset($att['label']);
				}
			}			
			
			$att['value'] = $key;
			if(preg_match("/^%/", $key)){
				$att['style'] = 'color:#C6C5C4;';
			}else if(preg_match("/^GROUP::(.+)$/", $txt, $ext)){
				if($is_open_group){
					$tmp .= BASIC_GENERATOR::init()->createClose('optgroup');
				}
				$tmp .= BASIC_GENERATOR::init()->createOpen('optgroup', 'label='.$ext[1]);
				$is_open_group = true;
				continue;
			}else if($txt == "ENDGROUP"){
				$tmp .= BASIC_GENERATOR::init()->createClose('optgroup');
				$is_open_group = false;
				continue;
			}else{
				if(isset($att['style'])) unset($att['style']);
			}
			if(isset($this->attributes['multiple'])){
				if(!is_array($value)){
					$value = array($value);
				}
				foreach($value as $v){
					if(
    				    ($key && $key == $v) || 
    				    (!$key && !$v && is_numeric($key) && is_numeric($v)) || 
    				    (!$key && !$v && is_string($key) && is_string($v))
					){
						$att['selected'] = 'selected';
						break;
					}
				}
			}else{
				if(
				    ($key && $key == $value) || 
				    (!$key && !$value && is_numeric($key) && is_numeric($value)) || 
				    (!$key && !$value && is_string($key) && is_string($value))
				){
				    $att['selected'] = 'selected';
				}
			}
			$tmp .= BASIC_GENERATOR::init()->createTag('option', $att, $txt);
			unset($att['selected']);
		}
		$tmp .= BASIC_GENERATOR::init()->createClose('select');

		return $tmp."\n";
	}
	/**
	 * Check if attribute multiple is set
	 * 
	 * @access public
	 * @return boolean
	 */
	function isMultiple(){
		if(isset($this->attributes['multiple']) && $this->attributes['multiple']){
			return true;
		}
		return false;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class MultySelectControl extends SelectControl implements FormControlInterface{
	/**
	 * Generate multyselect control html
	 * 
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()) {
		$attributes['multiple'] = 'multiple';
		if(isset($this->attributes['class'])){
			$this->attributes['class'] = 'multiple '.$this->attributes['class'];
		}else{
			$this->attributes['class'] = 'multiple';
		}
		
		return parent::generate($name, $value, $attributes);
	}
	function isMultiple(){
		return true;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class RadioBoxGroupControl extends SelectControl implements FormControlInterface{
	/**
	 * Generate radiobox group control html
	 * 
	 * 	special attributes
	 * 	vmode => true|false [false]
	 * 	onclick - will append this attribute for every element
	 * 	onmouseover - will append this attribute for every element 
	 *  onmouseout - will append this attribute for every element
	 *  
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()) {
		$this->init($name, $value, $attributes);
		
		$vmode = false;
		if(isset($this->attributes['vmode'])){
			$vmode = $this->attributes['vmode'];
			unset($this->attributes['vmode']);
		}

		$attTmp = array();
		$attTmp['name'] = $name;
		$attTmp['type'] = 'radio';
		
		if(isset($this->attributes['onclick'])){
			$attTmp['onclick'] = $this->attributes['onclick'];
			unset($this->attributes['onclick']);
		}
		if(isset($this->attributes['onmouseover'])){
			$attTmp['onmouseover'] = $this->attributes['onmouseover'];
			unset($this->attributes['onmouseover']);
		}
		if(isset($this->attributes['onmouseout'])){
			$attTmp['onmouseout'] = $this->attributes['onmouseout'];
			unset($this->attributes['onmouseout']);
		}
		
		if(isset($this->attributes['disabled'])){
			$attTmp['disabled'] = $this->attributes['disabled'];	
		}
		// @TODO this not work corectlly in IE
		if(isset($this->attributes['readonly']) && $this->attributes['readonly']){
			$attTmp['onclick'] = 'return false';
		}

		$tmp = '';
		$check = false;
		foreach ($this->data as $val => $txt){
		    $attTmp['id'] = uniqid();
			$attTmp['value'] = $val;
			
			if($value == $val || (!$value && !$check)){
				$attTmp['checked'] = 'checked';
				$check = true;
			}
			$tmp .= BASIC_GENERATOR::init()->element('div', 'class='.($vmode ? 'radiobox_item_vmode ' : '').'radiobox_item',//float:left|
				BASIC_GENERATOR::init()->createCloseTag('input', $attTmp).' <label for="'.$attTmp['id'].'" class="radioBox_label">'.$txt.'</label>'
			);
			unset($attTmp['checked']);
		}
		$this->attributes = BASIC_GENERATOR::init()->convertStringAtt($this->attributes);
		if(isset($this->attributes['id'])){
			$name = $this->attributes['id'];
		}
		if(isset($this->attributes['class'])){
			$this->attributes['class'] .= ' radioBox '.$name;
		}else{
			$this->attributes['class'] = 'radioBox '.$name;
		}
		return BASIC_GENERATOR::init()->element('div', $this->attributes, $tmp);
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class CheckBoxGroupControl extends SelectControl implements FormControlInterface{
	/**
	 * Generate checkbox group control html
	 * 
	 * 	onclick - will append this attribute for every element
	 * 	onmouseover - will append this attribute for every element 
	 *  onmouseout - will append this attribute for every element
	 *  
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()){
		$this->init($name, $value, $attributes);
		
		if(!is_array($value)){
			$value = array($value);
		}
		$value = array_flip($value);
		
		$attTmp = array();
		$attTmp['name'] = $name.'[]';
		$attTmp['type'] = 'checkbox';
		
		if(isset($this->attributes['onclick'])){
			$attTmp['onclick'] = $this->attributes['onclick'];
			unset($this->attributes['onclick']);
		}
		if(isset($this->attributes['onmouseover'])){
			$attTmp['onmouseover'] = $this->attributes['onmouseover'];
			unset($this->attributes['onmouseover']);
		}
		if(isset($this->attributes['onmouseout'])){
			$attTmp['onmouseout'] = $this->attributes['onmouseout'];
			unset($this->attributes['onmouseout']);
		}
			
		if(isset($this->attributes['disabled'])){
			$attTmp['disabled'] = $this->attributes['disabled'];
		}

		$tmp = '';
		foreach ($this->data as $val => $txt){
			$attTmp['id'] = 'gen_'.uniqid();
			$attTmp['value'] = $val;
			
			if(isset($value[$val])) $attTmp['checked'] = 'checked';

			$tmp .= BASIC_GENERATOR::init()->element('div','style=float:left|class=box', 
				BASIC_GENERATOR::init()->createCloseTag('input', $attTmp).' <label for="'.$attTmp['id'].'" class="multyBox_label">'.$txt.'</label>'
			);
			unset($attTmp['checked']);
		}
		if(isset($this->attributes['id'])){
			$name = $this->attributes['id'];
		}
		if(isset($this->attributes['class'])){
			$this->attributes['class'] .= ' multyBox '.$name;
		}else{
			$this->attributes['class'] = 'multyBox '.$name;
		}
		
		unset($this->attributes['value']);
		unset($this->attributes['name']);
		unset($this->attributes['maxlength']);
		
		return BASIC_GENERATOR::init()->element('div', $this->attributes, $tmp);
	}
	/**
	 * @access public
	 * @return boolean alway true
	 */
	function isMultiple(){
		return true;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @since 12.10.2012
 * @package basic.generator
 */
class ManageComboControl extends SelectControl implements FormControlInterface{
	/**
	 * Generate managecombobox control html
	 * 
	 * special attribute
 	 * 		data - array with texts for input fields. If text element contains '' will generate only text control without label.
 	 * 		buttons - style string (add:Add;del:Delete) for set buttons texts. If add:; will remove button "add".
	 *  
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()) {
 		$this->init($name, $value, $attributes);

 		$value = (!is_array($value) ? unserialize($value) : $value);
 		$tmp = '[';
 		$options = '';
	 	$i = 0; foreach ($value as $v){
 			$tmp .= ($i ? ',' : '')."'".$v."'";
 			$options .= '<option value="'.$v.'">'.$v.'</option>'."\n";
 		$i++;}
 		$tmp .= "]";
 		$value = $tmp;

 		$id = $name;
 		if(isset($this->attributes['id'])){
 			$id = $this->attributes['id'];
 		}
 		
 		$arrBtnText = array();
 		if(isset($this->attributes['buttons'])){
 		    $buttons = explode(";", $this->attributes['buttons']);
 		    foreach ($buttons as $v){
 		        $ex = explode(":", $v);
 		        $this->attributes[$ex[0]] = $ex[1];
 		    }
 		}
 		if(isset($this->attributes['add'])){
 			$arrBtnText[0] = $this->attributes['add'];
 			unset($this->attributes['add']);
 		}else{
 		    $arrBtnText[0] = '+';
 		}
 		if(isset($this->attributes['del'])){
 			$arrBtnText[1] = $this->attributes['del'];
 			unset($this->attributes['del']);
 		}else{
 		    $arrBtnText[1] = '-';
 		}
 		$arrBtnText = (count($arrBtnText) > 0 ? "['".implode("','", $arrBtnText)."']" : "null" );

  		if(!isset($this->attributes['cellspacing'])) $this->attributes['cellspacing'] = 0;
 		if(!isset($this->attributes['cellpadding'])) $this->attributes['cellpadding'] = 0;
 		if(!isset($this->attributes['style'])) $this->attributes['width'] = '100%';

 		if(!isset($this->attributes['class'])){
 			$this->attributes['class'] = 'changeSelect '.$name;
 		}else{
 			$this->attributes['class'] .= ' changeSelect '.$name;
 		}
 		if(isset($this->attributes['skin'])){
 			BASIC_GENERATOR::init()->head('select_skin', 'link', 'href='.$this->attributes['skin']);
 			unset($this->attributes['skin']);
 		}else{
			BASIC_GENERATOR::init()->head('select_skin', 'link', "href=".BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'/scripts/svincs/controls/select/style.css');
 		}
		BASIC_GENERATOR::init()->head('Svincs', 'script', array('type'=>'text/javascript', 'src'=>BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'scripts/svincs/svincs.js'), ' ');
		BASIC_GENERATOR::init()->head('select_scr', 'script', array('type'=>'text/javascript'), 'Svincs.include("controls/select/script")');
        

        unset($this->attributes['value']);
        unset($this->attributes['id']);
		unset($this->attributes['maxlength']);
        
		$add_att = '';
        foreach ($this->attributes as $k => $v){
            if($add_att) $add_att .= ",";
            
            $add_att .= "'".$k."':'".str_replace("'", "\\'", $v)."'";
        }		
		
		BASIC_GENERATOR::init()->script("$(document).ready(function (){
			(new Svincs.Select.ManageCombo('".$id."',".$value.",".(count($this->data) > 0 ? "['".implode("','", $this->data)."']" : "null" ).",".$arrBtnText.",{".$add_att."}))"
			.(isset($this->attributes['disabled']) && $this->attributes['disabled'] ? ".disabledElement(true)" : "").";
		});", array(
			'type'=>'text/javascript',
			'head' => true
		));
 	
		$this->attributes['multiple'] = 'multiple';
		$this->attributes['id'] = $id;
		
		return BASIC_GENERATOR::init()->element('select', $this->attributes, $options);	
	}
	/**
	 * Convert out in array
	 * 
	 * @access public
	 * @param string $value
	 * @return array
	 */
	public function convertOut($value) {
 		foreach (explode(':', $value) as $k => $v){
 			$arr[$k] = str_replace('&#58;', ':', $v);
 		}
 		return $arr;
	}
	/**
	 * @access public
	 * @return boolean alway true
	 */
	function isMultiple(){
		return true;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @since 12.10.2012
 * @package basic.generator
 */
class MoveComboControl extends SelectControl implements FormControlInterface{
	/**
	 * Generate movecombobox control html
	 * 
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()) {
 		$this->init($name, $value, $attributes);

 		if(!isset($this->attributes['cellspacing'])) $this->attributes['cellspacing'] = 0;
 		if(!isset($this->attributes['cellpadding'])) $this->attributes['cellpadding'] = 0;
 		if(!isset($this->attributes['style'])) $this->attributes['width'] = '100%';
	
 		$id = $name;
 		if(isset($this->attributes['id'])){
 			$id = $this->attributes['id'];
 		}
 		if(!isset($this->attributes['class'])){
 			$this->attributes['class'] = 'moveSelect '.$name;
 		}else{
 			$this->attributes['class'] .= ' moveSelect '.$name;
 		}
 		if(isset($this->attributes['skin'])){
 			BASIC_GENERATOR::init()->head('select_skin', 'link', 'href='.$this->attributes['skin']);
 			unset($this->attributes['skin']);
 		}else{
			BASIC_GENERATOR::init()->head('select_skin', 'link', "href=".BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'/scripts/svincs/controls/select/style.css');
 		}
 		
 
 		$value = (!is_array($value) ? array($value) : $value);
 		$tmp = '[';
 		$selected = array();
	 	$i = 0; foreach ($value as $k => $v){
 			$tmp .= ($i ? ',' : '')."'".$v."'";
 			$selected[$v] = 1;
 		$i++;}
 		$tmp .= "]";
 		$value = $tmp;
		
 		$all_cl = "{";
 		$options = '';
 		$i = 0; foreach ($this->data as $k => $v){
 			$all_cl .= ($i ? ',' : '')."'".$k."':'".$v."'";
 			
 			$options .= '<option value="'.$k.'" '.(isset($selected[$k]) ? 'selected="selected"' : '').'>'.$v.'</option>'."\n";
 		$i++;}
 		$all_cl .= "}";

		BASIC_GENERATOR::init()->head('Svincs','script','src='.BASIC::init()->ini_get('root_virtual').BASIC::init()->ini_get('basic_path').'/scripts/svincs/svincs.js',' ');
		BASIC_GENERATOR::init()->head('select_scr', 'script', array('type'=>'text/javascript'), 'Svincs.include("controls/select/script")');
		
		$disabled = false;
		if(isset($this->attributes['disabled'])){
			$disabled = $this->attributes['disabled'];
		}
		
		unset($this->attributes['value']);
		unset($this->attributes['id']);
		unset($this->attributes['maxlength']);
//		unset($this->attributes['name']);
//		unset($this->attributes['id']);
//		unset($this->attributes['disabled']);
		
		$add_att = '';
        foreach ($this->attributes as $k => $v){
            if($add_att) $add_att .= ",";
            
            $add_att .= "'".$k."':'".str_replace("'", "\\'", $v)."'";
        }
		
		BASIC_GENERATOR::init()->script("$(document).ready(function (){
			(new Svincs.Select.MoveCombo('".$id."',".$all_cl.",".$value.",{".$add_att."}))".
			(isset($this->attributes['disabled']) && $this->attributes['disabled'] ? ".disabledElement(1)" : '').
			";
		});", array(
			'type'=>'text/javascript', 
			'head' => true)
		);
			
		$this->attributes['multiple'] = 'multiple';
		$this->attributes['id'] = $id;
		
		return BASIC_GENERATOR::init()->element('select', $this->attributes, $options);
	}
	/**
	 * @access public
	 * @return boolean alway true
	 */
	function isMultiple(){
		return true;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class UploadControl extends BasicControl implements FormControlInterface{
	/**
	 * Generate upload control html
	 * 
	 * special attributes
 	 * 	dir - uploaded file folder
 	 * 	max - max file size
 	 * 	perm - [type1,type2,...,typeN] permissible file types
 	 * 	preview - [width,height[,subfix]] activate preview window. You can use subfix if have thumbneils.
 	 * 	error - text view for runtime error
 	 * 	delete_btn - basic style or array [ex. text=this is text on link|class=link_class|id=link_id|...]
 	 * 	upload_btn - basic style or array [ex. text=this is text on link|class=link_class|id=link_id|...]
 	 * 
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
 	function generate($name, $value, $attributes = array()){
 		$this->init($name, $value, $attributes);

 		if(!isset($this->attributes['class'])){
			$this->attributes['class'] = 'file_upload '.$name;
		}else{
			$this->attributes['class'] .= ' file_upload '.$name;
		}
		$perm = '';
		if(isset($this->attributes['perm'])){
			$perm = $this->attributes['perm']; unset($this->attributes['perm']);
		}
		$max = '';
		if(isset($this->attributes['max'])){
			$max = $this->attributes['max']; unset($this->attributes['max']);
		}
		$dir = '';
		if(isset($this->attributes['dir'])){
			$dir = $this->attributes['dir']; unset($this->attributes['dir']);
 		}
		$size = '';
		if(isset($this->attributes['size'])){
			$size = $this->attributes['size'];
			unset($this->attributes['size']);
		}
 		$disabled = false;
		if(isset($this->attributes['disabled'])){
			$disabled = $this->attributes['disabled'];
			unset($this->attributes['disabled']);
		}
		$readonly = false;
		if(isset($this->attributes['readonly'])){
			$disabled = $this->attributes['readonly'];
			unset($this->attributes['readonly']);
		}

		if(isset($this->attributes['rand'])) unset($this->attributes['rand']);
		if(isset($this->attributes['as'])) unset($this->attributes['as']);

		if(isset($this->attributes['onComplete'])) unset($this->attributes['onComplete']);
		if(isset($this->attributes['onDelete'])) unset($this->attributes['onDelete']);
		if(isset($this->attributes['onError'])) unset($this->attributes['onError']);

 		$tmp = BASIC_GENERATOR::init()->createCloseTag('input', 'type=file|class=control|name='.$name.'|id='.$name.'|'.($disabled ? 'disabled=disabled|' : '').($readonly ? 'readonly=readonly|' : '').'title=types:'.$perm.' max:'.$max.($size ? '|size='.$size : ''));
		
 		if(!$disabled && !$readonly){
			if(isset($this->attributes['delete_btn']) && isset($this->attributes['delete_btn']['text'])  && $value){
			    $delete_btn = BASIC_GENERATOR::init()->convertStringAtt($this->attributes['delete_btn']);
			    $text = $delete_btn['text']; 
			    
			    unset($delete_btn['text']);
			    unset($this->attributes['delete_btn']);
			    
			    $tmp .= BASIC_GENERATOR::init()->createTag('a', $delete_btn, $text);
			}
	 	 	if(isset($this->attributes['upload_btn']) && $value){
			    $btn = BASIC_GENERATOR::init()->convertStringAtt($this->attributes['upload_btn']);
			    $text = $btn['text']; 
			    
			    unset($btn['text']);
			    unset($this->attributes['upload_btn']);
			    
			   
			    $btn['href'] = BASIC::init()->ini_get('root_virtual').$dir.$value;
			    
			    $tmp .= BASIC_GENERATOR::init()->createTag('a', $btn, $text);
			}
 		}
		if(isset($this->attributes['preview']) && $this->attributes['preview'] && $value){
			$ex = explode(",",$this->attributes['preview']);
			$vals = explode(".", $value);
			if(isset($ex[2])){
				$ex[2] = '_'.$ex[2];
			}else{
				$ex[2] = '';
			}
			
			$cnt = count($vals);
			$vals[$cnt > 1 ? $cnt-2 : 0] .= $ex[2];
			
			$t = BASIC_GENERATOR::init()->image(implode(".", $vals), ($dir ? '|folder='.$dir : '').
				(isset($ex[0]) ? '|width='.$ex[0] : '').
				(isset($ex[1]) ? '|height='.$ex[1] : ''));
				
			if($t) $tmp .= BASIC_GENERATOR::init()->create('div', 'class=window', $t);
			unset($this->attributes['preview']);
		}
		
		return BASIC_GENERATOR::init()->create('div', $this->attributes, $tmp);
 	}
 	function isFileUpload(){
 		return true;
 	}
 	function fieldNames(){
 		return array(
 			'desc_'
 		);
 	}
}
/**
 * @todo the name has to be capTcha
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.2
 * @package basic.generator
 */
class CapchaControl extends BasicControl implements FormControlInterface{
	/**
	 * Captcha url variable
	 * 
	 * @staticvar
	 * @access public
	 * @var string
	 */
	static public $url_var = '_capcha_';
	/**
	 * Constructor
	 * 
	 * @access public
	 * @return void
	 */
	function __construct(){
		if($id = BASIC_URL::init()->request(self::$url_var)){
			BASIC::init()->imported('spam.mod');
			BASIC::init()->imported('session.mod');
			
			BASIC_SESSION::init()->start();
			
			$settings = BASIC_SESSION::init()->get(self::$url_var.$id);
			$captcha = new BasicAntiSpam($settings ? $settings : array());
			
			BASIC_SESSION::init()->set(self::$url_var.$id."_code", $captcha->getCode());
			
			$captcha->getImage();
			die();
		}
	}
	/**
	 * Generate captcha control html
	 * 
	 * special parameters:
	 *	ttf [alger.ttf] - text font
	 *	ttf_path [BASIC::init()->ini_get(root_virtual).BASIC::init()->ini_get(basic_path)];
	 *	width [110] - captcha graphic width
	 *	height [30] - captcha graphic height
	 *	lenght [6] - text length
	 *	mode [2] - captcha type. 0(int), 1(string), 2(int+string), 3(mathematic.ex:7+2=)
	 *	mime [png] - graphic type. Valid values are: jpg, png, gif
	 *	text_size [17]
	 *	bg_color [#F1F1F1] - captcha background color
	 *	text_color [#6F6F6F]
	 *	line_color [#D7D7D7]
	 *	noise_color [#D7D7D7]
	 *	num_lines [5]
	 *	noise_level [3]
	 *
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()) {
 		$this->init($name, $value, $attributes);
 		
 		BASIC::init()->imported('spam.mod');
		BASIC::init()->imported('session.mod');
		BASIC_SESSION::init()->start();
			
 		$title = '';
 		if(isset($this->attributes['title'])){
 			$title = $this->attributes['title'];
 			unset($this->attributes['title']);
 		}
 		
 		$ctr_attrs = array();
 		foreach($this->attributes as $k => $v){
 			if(
 				$k == 'ttf'||$k == 'ttf_path'||$k == 'iwidth'||$k == 'iheight'||$k == 'lenght'||$k == 'mod'||$k == 'mime'||$k == 'transparency'||
 				$k == 'text_size'||$k == 'bg_color'||$k == 'text_color'||$k == 'line_color'||$k == 'noise_color'||$k == 'num_lines'||$k == 'noise_level'
 			){
 				if($k == 'iwidth'){
 					$this->attributes['width'] = $v; unset($this->attributes[$k]);
 				}	
 				if($k == 'iheight'){
 					$this->attributes['height'] = $v; unset($this->attributes[$k]);
 				}	
 			}else{
 				$ctr_attrs[$k] = $v;
 				unset($this->attributes[$k]);
 			}
 		}
 		BASIC_SESSION::init()->set(self::$url_var.$name, $this->attributes);
 		
 		if(!isset($ctr_attrs['class'])){
 			$ctr_attrs['class'] = 'basic_captcha';
 		}else{
 			$ctr_attrs['class'] = 'basic_captcha '.$ctr_attrs['class'];	
 		}
 		$id = $name;
 		if(isset($ctr_attrs['id'])){
 			$id = $ctr_attrs['id'];
 			unset($ctr_attrs['id']);
 		}
 		unset($ctr_attrs['name']);
 		unset($ctr_attrs['value']);
	
 		$captcha = new BasicAntiSpam(BASIC_SESSION::init()->get(self::$url_var.$name));
 		
 		$src = BASIC::init()->ini_get('root_virtual').'index.php?'.self::$url_var."=".$name;
		return BASIC_GENERATOR::init()->element('div', $ctr_attrs, 
		
			BASIC_GENERATOR::init()->element('img', array(
				'src' => $src,
				'title' => $title,
				'alt' => $title,
				'style' => 'cursor:pointer;',
				'onclick' => 'this.src=\''.$src.'&\'+(new Date()).getTime();'
			)).
			BASIC_GENERATOR::init()->createCloseTag('input', array('name' => $name, 'id' => $id))
		);
	}
	/**
	 * Get seesion variable for captcha
	 * 
	 * @access public
	 * @param integer $id
	 * @return string
	 */
	function code($id){
		BASIC::init()->imported('session.mod');
		BASIC_SESSION::init()->start();
		
		return BASIC_SESSION::init()->get(self::$url_var.$id."_code");
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 12.12.2011
 * @version 0.1
 * @package basic.generator
 */
class BrowserControl extends BasicControl implements FormControlInterface{
	/**
	 * Browser control utl variable
	 * 
	 * @staticvar
	 * @access public
	 * @var string
	 */
	static public $url_var = '_browser_';
	/**
	 * Constructor
	 * 
	 * @access public
	 * @return string
	 */
	function __construct(){
		if($id = BASIC_URL::init()->request(self::$url_var)){
			BASIC::init()->imported('session.mod');
			BASIC_SESSION::init()->start();
			
			if(!$attrs = BASIC_SESSION::init()->get(self::$url_var.$id)){
				die('wrong browser name');
			}
			
			$url = clearUpInjection(BASIC_URL::init()->request('browse_url'));
			
			$html = '
<!DOCTYPE html>
<html>
	<head>
		<title>Test iframe reload</title>
		<link type="text/css" rel="stylesheet" href="'.BASIC::init()->ini_get('root_virtual').'cms/scripts/browser/browser.css">
		<script>
			function openFolder(url){
				location.href = "'.BASIC::init()->ini_get('root_virtual').'index.php?'.self::$url_var.'='.$id.'&browse_url="+url;
			}
			function useResource(name){
				_opener_.setResource(name);
			}
		</script>
	</head>
	
	<body>
		<div class="browserBox">
			';
				$url = preg_replace("/\/$/", "", $url);
				$up = preg_replace("/[^\/]+$/", "", $url);
				$match = false;
				
				foreach ($attrs['resources'] as $path){
					if($path == $url){
						$match = true;
						break;
					}
				}
				
				if($url && !$match){
					$attrs['resources'] = array($url);
				}else{
					$url = '';
				}
			
				foreach ($attrs['resources'] as $path){
					$html .= '
					
			<div class="sep"> -- '.$path.' -- </div>
			<ul>';
					
					if($url){
						$html .= '
				<li class="folder"><a href="javascript:openFolder(\''.$up.'\')">..</a></li>';
					}
					$folder = opendir(BASIC::init()->ini_get('root_path').$path);
					while (($file = readdir($folder)) !== false) {
						if($file == '.' || $file == '..' || $file == '.svn') continue; 
						
						
						if(is_file(BASIC::init()->ini_get('root_path').$path."/".$file)){				
							if($attrs['type'] == 'files' || $attrs['type'] == 'all'){
								$ufile = preg_replace("/(".str_replace(",", "|", str_replace(".", '\.', $attrs['file_types'])).")/", "", $file);
								if($ufile != $file){
									$html .= '
				<li class="file"><a href="javascript:useResource(\''.$path."/".($attrs['clear_types'] ? $ufile : $file).'\')">'.$file.'</a></li>';
								}
							}
						}else{
							$event =  'onclick="openFolder(\''.$path."/".$file.'\')"';
							if($attrs['type'] == 'folder' || $attrs['type'] == 'all'){
								$event =  'onclick="$path."/".$file(\''.$path."/".$file.'\')" dblclick="openFolder(\''.$path."/".$file.'\')"';
							}
							$html .= '
				<li class="folder"><a href="#" '.$event.'>'.$file.'</a></li>';
						}
					}
					
					$html .= '
			</ul>';
				}
				$html .= '
		</div>
	</body>
</html>
			';
			die($html);
		}
	}
	/**
	 * Generate browse control html
	 * 
	 *  Special parameters:
	 * 	string type - folder|files|all - by default is "all"
	 * 	array resources - folder root paths 
	 * 	array file_types - ex: .com.php,.txt,.pdf, ... 
	 *  boolean clear_types - 
	 * 
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param array [$attributes] 
	 * @return string
	 */
	public function generate($name, $value, $attributes = array()) {
 		$this->init($name, $value, $attributes);
 		
 		BASIC::init()->imported('session.mod');
		BASIC_SESSION::init()->start();
		
		if(!isset($this->attributes['type'])) $this->attributes['type'] = 'files';
		if(!isset($this->attributes['file_types'])) $this->attributes['file_types'] = '.cmp.php';
		if(!isset($this->attributes['resources'])) $this->attributes['resources'] = array();
		if(!isset($this->attributes['clear_types'])) $this->attributes['clear_types'] = true;
		
		if(!is_array($this->attributes['resources'])) $this->attributes['resources'] = array($this->attributes['resources']);
 		
		BASIC_SESSION::init()->set(self::$url_var.$name, $this->attributes);
 		
 		return '<table width="100%" cellpadding="0" cellspacing="0" class="ctrl_browser"><tr>
			<td width="100%">'.BASIC_GENERATOR::init()->createCloseTag('input', $this->attributes).'</td>
			<td><button type="button" class="btn" onclick="Svincs.MenuTargetModal.open(\''.BASIC::init()->ini_get('root_virtual').'index.php?'.self::$url_var.'='.$name.'\', 500, 400, {setResource: function (name){ $(\'#'.$name.'\').get(0).value=name; Svincs.MenuTargetModal.close();}}, {draggable:false,resizable:false})">'.BASIC_LANGUAGE::init()->get('browse').'</button></td>
		</tr></table>';
	}
	protected function htmlGenerator(){
	
	}
}
/**
 * @name BASIC_PAGEGenerator
 * @author Evgeni Baldzisky
 * @version 1.1 
 * @since 24.01.2007
 * @package basic.generator
 */
class BASIC_GENERATOR extends BASIC_XMLGenerator {
	/**
	 * Help variable used in uId()
	 * 
	 * @staticvar
	 * @access private
	 * @var integer
	 */
	static private $_uid = 0;
	/**
	 * Generate unique name
	 * 
	 * @static
	 * @access public
	 * @param string $name
	 * @return string
	 */
	static public function uId($name = ''){
		if($name == '' || $name == null){
			$name .= "autogen".(self::$_uid++);
		}
		return $name;
	}
	/**
	 * Help property used in head()
	 * 
	 * @access private
	 * @var array
	 */
	protected $Head 	= array();
	/**
	 * @access private
	 * @var array
	 */
	protected $ctrls = array();
	
	/**
	 * Get BASIC_GENERATOR using sigleton pattern
	 * 
	 * @param array [$arr]
	 * @return BASIC_GENERATOR
	 */
	static public function init($arr = array()){
		if(!isset($GLOBALS['BASIC_PAGE'])){
			$GLOBALS['BASIC_PAGE'] = new BASIC_GENERATOR();
		}
		foreach ($arr as $k => $v){
			$GLOBALS['BASIC_PAGE']->$k = $v;
		}
		return $GLOBALS['BASIC_PAGE'];
	}
	
	/********* HEAD SECTION **********/
	
	/**
	 * Buffering tags for HEAD section on the page.
	 * 
	 * WARNING:Test for empty $inner and if true create close tag
	 * 
	 * @access public
	 * @param string $coment - if == null system will put "sys_"+next head's number
	 * @param string $tag
	 * @param string|array $attribute
	 * @param string $inner
	 * @return void
	 */
	function head($coment, $tag, $attribute, $inner=''){
		if(!$tag){
			$this->Head[$coment] = '';
			return;
		}
		$n = '';
		$attribute = $this->convertStringAtt($attribute);
		
		if($coment === null || $coment === '') $coment = 'sys_'.count($this->Head);
		
		if($tag == 'title' && !$inner){
			$inner = ' ';
		}
		if($tag == 'script'){
			$attribute['type'] = 'text/javascript';
			if($inner == '') $inner = ' ';
			
			if(isset($attribute['src'])){
			    $attribute['src'] = str_replace('ROOT_VIRTUAL',$GLOBALS['BASIC']->ini_get('root_virtual'),$attribute['src']);
			}
			if($inner && $inner != ' ' && isset($attribute['src'])){
				$att = $attribute; unset($att['src']);
				$this->Head[$coment.'_inner'] = $n.$this->create($tag,$att,$inner);
				$inner = '';
			}
		}
		if($tag == 'style'){
			if(isset($attribute['href'])) $tag = 'link';
		}
		if($tag == 'link'){
			$attribute['rel'] = 'stylesheet';
			$attribute['type'] = 'text/css';
			
			if(isset($attribute['href'])){
			    $attribute['href'] = str_replace('ROOT_VIRTUAL',$GLOBALS['BASIC']->ini_get('root_virtual'),$attribute['href']);
			}
			$n = "\n";
		}
		
		$this->Head[$coment] = array(
			'tag' => $tag,
			'ctrl' => $n.$this->create($tag,$attribute,$inner)
		);
	}
	/**
	 * Add element in Head property
	 * 
	 * @access public
	 * @param integer $id
	 * @return void
	 */
	function registerHead($id){
		$this->Head[$id] = array(
			'tag' => '',
			'ctrl' => ''
		);
	}
	/**
	 * Shortcut for head function.
	 *
	 * @access public
	 * @param string $tagName
	 * @param array|string $attributes
	 * @param string [$inner]
	 * @param string [$index]
	 * @return void
	 */
	function setHead($tagName,$attributes,$inner = '',$index = ''){
		$this->head($index,$tagName,$attributes,$inner);
	}
	/**
	 * Get 1 head tag from buffer
	 * 
	 * @access public
	 * @param string $coment
	 * @return string
	 */
	function getHead($coment){
		if(isset($this->Head[$coment]) && $this->Head[$coment]['ctrl']) return $this->Head[$coment];
		return '';
	}
	/**
	 * Set header of special type
	 *
	 * @access public
	 * @example <!--[if IE]--><!--[end]-->
	 * @param string $body
	 * @param string $coment
	 * @return void
	 */
	function headSpecial($body,$coment){
		$this->Head[$coment] = $body;
	}
	/**
	 * Test for existing tag in buffer
	 *
	 * @access public
	 * @param string $coment
	 * @return boolen
	 */
	function existHead($coment){
		return isset($this->Head[$coment]);
	}
	/**
	 * Get all headers.
	 * 
	 * Style string - set flag true
	 * Style array  - set flag false.It is default value
	 *
	 * @access public
	 * @param boolen [$string]
	 * @return string|array
	 */
	function getHeadAll($string = false){
		if($string){
			$string = '';
			foreach ($this->Head as $v){
				$string .= $v;
			}
			return $string;
		}
		return $this->Head;
	}
	/**
	 * Delete head tag
	 *
	 * @access public
	 * @param string $coment
	 * @return void
	 */
	function delHead($coment){
		if(isset($this->Head[$coment])) unset($this->Head[$coment]);
	}
	
	/********* END HEAD SECTION **********/
	
	
	/**
	 * Add control to ctrl container
	 * 
	 * @access public
	 * @param string $name
	 * @param FormControlInterface $ctrl
	 * @return void
	 */
	public function registrateControle($name, $ctrl){
		$this->ctrls[$name] = $ctrl;
	}
	/**
	 * Remove element from ctrl container
	 * 
	 * @access public
	 * @param string $name
	 * @return void
	 */
	public function removeControle($name){
		unset($this->ctrls[$name]);
	}
	/**
	 * Execute generate method of control $ctrlName 
	 * 
	 * @access public
	 * @param string $name
	 * @param string $ctrlName
	 * @param object $ctrlValue
	 * @param hashmap [$ctrlAttributes]
	 * @return string
	 */
	public function controle($name, $ctrlName, $ctrlValue, $ctrlAttributes = array()){
		if(isset($this->ctrls[$name])){
			return $this->ctrls[$name]->generate($ctrlName, $ctrlValue, $ctrlAttributes);
		}
		throw new Exception("Can't find controle '".$name."'.");
		return null;
	}
	/**
	 * Get control from ctrl container
	 * 
	 * @access public
	 * @param string $name
	 * @return FormControlInterface|null
	 */
	public function getControl($name){
		if(isset($this->ctrls[$name])) return $this->ctrls[$name];
		
		return null;
	}
	/**
	 * Get ctrl container data
	 * 
	 * @access public
	 * @return hashmap
	 */
	public function getControls(){
		return $this->ctrls;
	}
	/**
	 * clear the value with the controls convertor.
	 * 
	 * @param  mix $value
	 */
	public function convertControl($name, $value){
		if(isset($this->ctrls[$name])){
			return $this->ctrls[$name]->convertOut($value);
		}
		return $value;
	}
	/**
	 * Create XHTML tag script.
	 * 
	 *	valid attributes: 
	 *		All standart HTML attributes
	 *		head - put script in html head
	 *
	 * @version 0.3
	 * @since 12.07.2007
	 * @access public
	 * @param string $body
	 * @param array [$attribute]
	 * @return string
	 */
	function script($body, $attribute = array()){
		$attribute = $this->convertStringAtt($attribute);
		$attribute['type'] = "text/javascript";
		
		$head = false;
		if(isset($attribute['head']) && $attribute['head']){
			$head = true;
			unset($attribute['head']);
		}
		if(isset($attribute['src'])){
            $attribute['src'] = str_replace('{ROOT_VIRTUAL}', BASIC::init()->ini_get('root_virtual'), $attribute['src']);
		}
		if($head){
			$this->head(null, 'script', $attribute, $body); return '';
		}
		$tmp = '';
		if($body && $body != ' ' && isset($attribute['src'])){
			$att = $attribute; unset($att['src']);
			$tmp .= $this->createTag('script',$att,$body);
			$body = ' ';
		}
		$tmp .= $this->createTag('script', $attribute, $body);
		
		return $tmp;
	}
	/**
	 * Create XTHML tag style and link.
	 * 
	 * if isset(attribute['href']) tag == 'link' else tag == 'style'
	 *
	 * @version 0.2 update [12-09-2007] add new attribute "path" for add current domain path
	 * @access public
	 * @param string $name
	 * @param string $body
	 * @param array [$attribute]
	 * @param void
	 */
	function style($name,$body,$attribute = array()){
		$attribute = $this->convertStringAtt($attribute);

		$attribute['type'] = "text/css";

		if(isset($attribute['href'])){
			if(isset($attribute['path'])){
				$tmp = $GLOBALS['BASIC']->pathFile(
					array(
						$GLOBALS['BASIC']->ini_get('root_virtual'),
						$attribute['href']
					)
				);
				$attribute['href'] = $tmp[0].$tmp[1];
				unset($attribute['path']);
			}
			$attribute['rel'] = 'stylesheet';
			$this->head($name,'link',$attribute);
		}else{
			$this->head($name,'style',$attribute,$body);
		}
	}
	/**
	 * Create image tag or flash
	 * 
	 * Special attribute
	 * 		['default'] -> default image 	 ex: images\def.jpg 	def[]
	 * 		['folder'] -> container images 	 ex: images 			def[upload]
	 * 		['absolute'] -> kill w\h size    ex: true 				def[false]
	 * 		['fullpath'] -> activate display full path   ex: true   def[true]
 	 *
 	 * @version 0.5 update [09-03-2007]
	 * @param string $img
	 * @param array [$attribute]
	 * @return string
	 */
	function image($img,$attribute = array()){
		$tmp = '';
		$attribute = $this->convertStringAtt($attribute);

		$default = '';  $width = 0;
		$folder  = '';  $height = 0;

		$att = array();
		if(isset($attribute['default'])){
			$att['default'] = $attribute['default'];
			unset($attribute['default']);
		}
		if(isset($attribute['folder'])){
			$att['folder'] = $attribute['folder'];
			unset($attribute['folder']);
		}
		if(isset($attribute['fixed'])){
			$att['fixed'] = $attribute['fixed'];
			unset($attribute['fixed']);
		}
		if(isset($attribute['absolute'])){
			$att['absolute'] = $attribute['absolute'];
			unset($attribute['absolute']);
		}
		if(isset($attribute['fullpath'])){
			$att['fullpath'] = $attribute['fullpath'];
			unset($attribute['fullpath']);
		}else{
			$att['fullpath'] = 'true';
		}

		BASIC::init()->imported('media.mod');
		$media = new BASIC_MEDIA($img, $att);

		if(isset($attribute['width'])) $width = $attribute['width'];
		if(isset($attribute['height'])) $height = $attribute['height'];

		return $media->view($width,$height,$attribute) . $tmp;
	}
	/**
	 * Add 5*number spaces 
	 *
	 * @param integer [$number]
	 * @return string
	 */
	function createTab($number = 1){
		$tmp = '';
		for($i=0;$i<$number+1;$i++){
			$tmp .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		return $tmp;
	}
	/**
	 * Create link html
	 * 
	 * Warning:
	 * 		http;\\myhost.com?var=val&var2=va&lue  -> bad
	 * 		http;\\myhost.com?var=val&var2=value   -> good
	 *
	 * 		update [21-07-2007] add new attribute "state" autocreate state
	 * 		update [12-09-2007] add new attribute "path" autocreate current domain path #start from root site folder
	 * 		fix    [20-04-2008] fix regular expression if doesn't exist file name
	 * @version 0.5 [08-03-2007]
	 *
	 * @access public
	 * @param sting $text
	 * @param string [$url]
	 * @param array|string $attribute
	 * @return string
	 */
	function link($text,$url = '#',$attribute = array()){ 
		$attribute = $this->convertStringAtt($attribute);
		if($url){
			preg_match("/([^\?]+)?(\?)?(.+)?/", $url, $reg);
			if(isset($reg[3])){
				preg_match_all("/&(amp;)?([^&]+)/","&".$reg[3], $exp);
	
				for ($i=0;$i<count($exp[2]);$i++){
					$var = explode("=",$exp[2][$i]);
					$exp[2][$i] = $var[0]."=".urlencode(isset($var[1]) ? $var[1] : '');
				}
	
				$reg[3] = implode("&", $exp[2]);
			}else{
				$reg[3] = '';
			}
			$stat = '';
			if(isset($attribute['state'])){
				if($attribute['state'] == "*"){
					$ex = array();
				}else{
					$ex = explode(",", $attribute['state']);
				}
				$stat = BASIC_URL::init()->serialize($ex);
				unset($attribute['state']);
			}
			$tmp = $stat.$reg[3];
			$attribute['href'] = $reg[1].($tmp ? '?' : '').$tmp;
			if(isset($attribute['path'])){
				$tmp = BASIC::init()->pathFile(array(BASIC::init()->ini_get('root_virtual'), $attribute['href']));

				$attribute['href'] = $tmp[0].$tmp[1];
				unset($attribute['path']);
			}
			$attribute['href'] = BASIC_URL::init()->link($attribute['href']);
		}else{
			$attribute['href'] = '#';
		}
		return $this->createTag('a', $attribute, $text);
	}
	/**
	 * Create html element
	 * 
	 * @param string $tag
	 * @param string|array [$attribute]
	 * @param string [$inner]
	 * @param boolean [$flag]
	 * @return string
	 */
	function element($tag, $attribute = array(), $inner = '', $flag = false){
		$tag = strtolower($tag);

		$attribute = $this->convertStringAtt($attribute);

		if($flag && $inner == '') return '';

		if($tag == 'br' || $tag == 'hr' || $tag == 'img' || $tag == 'input' || $tag == 'meta' || $tag == 'link'){
			if($tag == 'img' && !isset($attribute['alt'])) $attribute['alt'] = '';
			$tmp = $this->createCloseTag($tag, $attribute);
		}else{
			$tmp = $this->createTag($tag, $attribute, ($inner ? $inner : '&nbsp;'));
		}
		return $tmp;
	}
	/**
	 * Create form
	 * 
	 * @version 0.2 [19-10-2007]
	 * Special parameters
	 * 	state -> save state program
	 *
	 * @access public
	 * @param array|string [$attribute]
	 * @param string [$inner]
	 * @return string
	 */
	function form($attribute = array(), $inner = ''){
		$attribute = $this->convertStringAtt($attribute);
		if(!isset($attribute['action'])){
			$attribute['action'] = BASIC::init()->scriptName();
		}
		
		$name = '';
		if(isset($attribute['name'])) $name = $attribute['name'];
		if(isset($attribute['id'])) $name = $attribute['id'];
		
		$name = self::uId($name);

		$attribute['name'] = $name;
		$attribute['id'] = $name;

		if(isset($attribute['state'])){
			if($attribute['state'] == "*"){
				$ex = array();
			}else{
				$ex = explode(",", $attribute['state']);
			}
			$inner .= BASIC_URL::init()->serialize($ex, 'post');
			unset($attribute['state']);
		}
		$tmp = BASIC_GENERATOR::init()->create('form', $attribute, "\n".$inner);
//		$tmp .= $GLOBALS['BASIC_PAGE']->script("
//			var frm".$name." = document.getElementById('".$name."');
//			var col".$name." = frm".$name.".getElementsByTagName('a');
//			for(var i=0;i<col".$name.".length;i++) col".$name.".item(i).form = frm".$name.";
//		");
		return $tmp;
	}
	/**
	 * Dynamic form element
	 * 
	 * 	support tags
	 *		select
	 *		radioBox
	 *		multyBox
	 *		moveSelect
	 *		changeSelect
	 *
	 * 	special attribute
	 *		data || base%name table;name value column;name text column;sql criteria;default value[,:]default text
	 * 			 || static%value1:text1;value2:text2;...;valueN:textN
	 * 			 || query%select [key column],[text column] from ... [;key column][;text column]
	 *           || PHP hash array
	 *
	 *  NEW : 
	 *     base and query data options support lingual functionality
	 * @access public
	 * @param string $tag
	 * @param string $name
	 * @param string $value
	 * @param arrau $attribute
	 * @return string
	 */
 	function dynamic($tag, $name, $value, $attribute){
 		$attribute = $this->convertStringAtt($attribute);
 		$tmp = '';
		$optionArray = array();

		if(
			$tag != "select" &&
			$tag != "multySelect" &&
			$tag != "radioBox" &&
			$tag != "multyBox" &&
			$tag != "moveSelect" &&
			$tag != "changeSelect"
		) return '';
		
		if($tag == "multySelect"){
			$tag = 'select';
			$attribute['multiple'] = 'multiple';
		}

 		if(isset($attribute['data'])){

			if(is_array($attribute['data'])){
				foreach($attribute['data'] as $k => $v){
					$optionArray[$k] = (is_array($v) ? $v[0] : $v);
				}
			}else{
				$tmp_arr = explode("%",$attribute['data']);
				
				if($tmp_arr[0] == 'base'){
					
					$infoArray = explode(";", $tmp_arr[1]);
					$optionArray = array();
					if(isset($infoArray[4]) && ($tag == 'select' || $tag == 'radioBox')){
						for($i=4;isset($infoArray[$i]);$i++){
							$ex_def = split("[,:]", $infoArray[$i]); // save old functionality
							if(isset($ex_def[1])){
								$optionArray[$ex_def[0]] = $ex_def[1];
							}else{
								$optionArray[] = $infoArray[$i];
							}
						}
					}
					$preg_ex = "/^((.+) as )?[^a-zA-Z_]*([a-zA-Z_]+)[^a-zA-Z_]*$/i";
					$var_1 = $infoArray[1];
					preg_match($preg_ex,$infoArray[1],$ex);
					if($ex[1]){
						$var_1 = $ex[3];
					}else{
						$infoArray[1] = "`".$infoArray[1]."`";
					}
					$var_2 = $infoArray[2];
					preg_match($preg_ex, $infoArray[2], $ex);
					if($ex[1]){
						$var_2 = $ex[3];
					}else{
						$infoArray[2] = "`".$infoArray[2]."`";
					}
					$criteria = '';//(isset($infoArray[3]) ? $infoArray[3] : ' order by `'.$var_2.'` ');
					$rdr = BASIC_SQL::init()->read_exec("select * from `".$infoArray[0]."` where 1=1 ".$criteria." ");

					for($i=0;$rdr->read();$i++){
					    if(!$rdr->test($var_2)){
					        if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && $rdr->test($var_2.'_'.$GLOBALS['BASIC_LANG']->current())){
					            $optionArray[$rdr->field($var_1)] = $rdr->field($var_2.'_'.BASIC_LANGUAGE::init()->current());
					        }else{
					            throw new Exception('Column '.$infoArray[0].'.'.$var_2.' no exist.');
					        }
					    }else{
						    $optionArray[$rdr->field($var_1)] = $rdr->field($var_2);
					    }
					}
				}else if($tmp_arr[0] == 'query'){ // NEW
				    
					$infoArray = explode(";", $tmp_arr[1]);
					
					if(!isset($infoArray[1])){
					    $query = $infoArray[0];
                        $query = preg_replace("[\n\t\r]","",$query);
                         
                        preg_match('/select[ ]+(.+)[ ]+from/i',$query,$ex);
                        if(isset($ex[1])){
                            $ex[1] = preg_replace("/[` ]+/","",$ex[1]);
                            
                            $tmp = explode(',',$ex[1]);
                            if(isset($tmp[1])){
                                $infoArray[2] = $tmp[1];
                            }else{
                                $infoArray[2] = $tmp[0];
                            }
                            $infoArray[1] = $tmp[0];
                            
                            $infoArray[1] = preg_replace("/^[^\.]+\./", "", $infoArray[1]);
                            $infoArray[2] = preg_replace("/^[^\.]+\./", "", $infoArray[2]);
                            
                            $infoArray[0] = preg_replace('/select[ ]+(.+)[ ]+from/i','select * from',$query);
                        }else{
                            throw new Exception('In query ['.$query.'] no declare id and text columns.');
                        }				    
					}
					BASIC_SQL::init()->read_exec($infoArray[0]);
					while($rdr->read()){
				        if(!$rdr->test($infoArray[2])){
					        if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && $rdr->test($infoArray[2].'_'.$GLOBALS['BASIC_LANG']->current())){
					            $optionArray[$rdr->field($infoArray[1])] = $rdr->field($infoArray[2].'_'.BASIC_LANGUAGE::init()->current());
					        }else{
					             throw new Exception(500,'Column '.$infoArray[0].'.'.$infoArray[2].' no exist.');
					        }
					    }else{
						    $optionArray[$rdr->field($infoArray[1])] = $rdr->field($infoArray[2]);
					    }
					}
				}else{
					$ex = explode(";", $tmp_arr[1]);
					foreach($ex as $V){
						$spl = explode(":", $V);
						if(isset($spl[1])){
							$optionArray[$spl[0]] = $spl[1];
						}
					}
				}
			}
			$attribute['data'] = $optionArray;
		}
 		return $this->controle($tag, $name, $value, $attribute);
 	}
}
BASIC_GENERATOR::init()->registrateControle('input', 		new BasicControl());
BASIC_GENERATOR::init()->registrateControle('password', 	new PasswordControl());
BASIC_GENERATOR::init()->registrateControle('textarea', 	new TextareaControl());
BASIC_GENERATOR::init()->registrateControle('date', 		new DateControl());
BASIC_GENERATOR::init()->registrateControle('html', 		new HtmlControl());
BASIC_GENERATOR::init()->registrateControle('file', 		new UploadControl());
BASIC_GENERATOR::init()->registrateControle('radio', 		new RadioBoxGroupControl());
BASIC_GENERATOR::init()->registrateControle('check', 		new CheckBoxGroupControl());
BASIC_GENERATOR::init()->registrateControle('checkbox', 	new CheckBoxControl());
BASIC_GENERATOR::init()->registrateControle('select', 		new SelectControl());
BASIC_GENERATOR::init()->registrateControle('multiple', 	new MultySelectControl());
BASIC_GENERATOR::init()->registrateControle('selectmove', 	new MoveComboControl());
BASIC_GENERATOR::init()->registrateControle('selectmanage', new ManageComboControl());
BASIC_GENERATOR::init()->registrateControle('capcha', 		new CapchaControl());
BASIC_GENERATOR::init()->registrateControle('browser', 		new BrowserControl());