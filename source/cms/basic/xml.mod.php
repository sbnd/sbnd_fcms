<?php
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
* @package basic.xml
* @version 7.0.6
*/


/**
 * 
 * XML Genrator class
 * 
 * @author Evgeni Baldziyski
 * @version 0.3 
 * @since 24.01.2007
 * @package basic.xml
 */
class BASIC_XMLGenerator{
	/**
	 * 
	 * xml flag
	 * @var boolean
	 */
	var $XML = false; 			// create true only call constructor
	/**
	 * 
	 * xml buffer
	 * @var string
	 */
	var $XMLbuffer = '';		// buffer generate XML
	/**
	 * 
	 * @todo write description
	 * @var boolean
	 */
	var $cleanAttribute = true;

	/**
	 * 
	 * XML generator
	 * @param boolean $createHeader
	 */
	function XMLGenerator($createHeader = true){
		$this->XML = true;
		if($createHeader){
			$this->createHeader();
		}
	}
	/**
	 * Convert $arrtibute array in valid HTML Syntax
	 *
	 * @param array $attribute
	 * @return string
	 */
	function convertAtrribute($attribute){

		$tmp = '';
		if(!is_array($attribute)){
			$attribute = $this->convertStringAtt($attribute);
		}
		
		foreach ($attribute as $k => $v){
			
			if($k == 'readonly' || $k == 'disabled' || $k == 'checked' || $k == 'selected'){
				if($v === false || $v === 'false') continue;
				if($v === true || $v === 'true'){
					$v = $k;
				}
			}
			
			if($this->cleanAttribute){
				//$v = preg_replace("/&(amp;)?/", "&amp;", $v);
			}
			
			$v = @str_replace("<", "&lt;", $v);
			$v = @str_replace(">", "&gt;", $v);
			$v = @str_replace('"', "&quot;", $v);
			
			if(is_array($v)){
				
			}

			$tmp .= ' '.strtolower($k).'="'.$this->convertToLiteral($v).'"';
		}
		
		return $tmp;
	}
	/**
	 * Confert recursive array data to string literal (string that will supported in JS)
	 * 
	 * @param string/array/hashmap $data
	 * @return string
	 */
	function convertToLiteral($data){
		if(is_array($data)){
			$tmp = '';
			foreach ($data as $k => $v){
				if($tmp) $tmp .= ',';
				
				$tmp .= "'".$k."':".(is_array($v) ? $this->convertToLiteral($v) : "'".$v."'")."";
			}
			return "{".$tmp."}";
		}else{
			return $data;
		}
	}
	/**
	 * Convert string attribute in array
	 * valid sintax 'name attribute 1=value attribute 1|name attribute 2=value attribute 2|...|name attribute N=value attribute N'
	 * if you nead special separators can add the second and the third function parametars with array declarations.
	 * array declaration must contain this elements (separator,alternativ symbol visioalisation).
	 * example : 
	 * 	$arr = BASIC.XML->convertStringAtt('att1=val&#62ue1|att2=val2|att3=v&#124al'); // default declaration
	 * 	result : 
	 * 		$arr = array(
	 * 			att1 => val=ue1
	 * 			att2 => val2
	 * 			att3 => v&|al
	 * 		)
	 * 
	 * 	$arr = BASIC.XML->convertStringAtt('att1:val=ue1;att2:va|l2;att3:v&#59a&#58l;',array(';',"&#59"),array(":","&#58")); // special symbol separators
	 * 	result : 
	 * 		$arr = array(
	 * 			att1 => val=ue1
	 * 			att2 => val2
	 * 			att3 => v;|a:l
	 * 		)
	 * 
	 * @param string $attribute
	 * @param array $separatopAttributes
	 * @param array $separatopValues

	 * @return array
	 */
	function convertStringAtt($attribute,$separatopAttributes=array('|','&#124;'),$separatopValues=array("=","&#62;")){
		if(!is_array($attribute) && $attribute != null && $attribute != ''){
			$arr = explode($separatopAttributes[0],$attribute);

			$attribute = array();
			foreach($arr as $v){
				$arr2 = explode($separatopValues[0],$v,2);
				if(isset($arr2[1])){
					
					$arr2[1] = str_replace($separatopAttributes[1],$separatopAttributes[0],$arr2[1]);
					$arr2[1] = str_replace($separatopValues[1],$separatopValues[0],$arr2[1]);
					
					$attribute[$arr2[0]] = $arr2[1];
				}
			}
		}
		return is_array($attribute) ? $attribute : array();
	}

	/**
	 * Generate closed tags
	 *
	 * @param string $tagname
	 * @param array/string $attribute
	 * @return string
	 */
	function createCloseTag($tagname,$attribute=''){
		if($this->XML) $attribute = $this->convertStringAtt($attribute);
		//print($tagname);
		$tmp  = '';
		$tmp .= '<'.strtolower($tagname);
		$tmp .= $this->convertAtrribute($attribute);
		$tmp .= ' />';
		if($this->XML) $this->XMLbuffer .= $tmp;
		return $tmp;
	}

	/**
	 * Generate open tags
	 *
	 * @param string $tagname
	 * @param array/string $attribute
	 * @return string
	 */
	function createOpen($tagname,$attribute=''){
		$tagname = strtolower($tagname);
		if($this->XML) $attribute = $this->convertStringAtt($attribute);
		$tmp  = "\n";
		$tmp .= '<'.$tagname;
		$tmp .= $this->convertAtrribute($attribute);
		$tmp .= '>';
		if($this->XML) $this->XMLbuffer .= $tmp;
		return $tmp;
	}

	/**
	 * Generate close tags
	 *
	 * @param string $tagname
	 * @param array/string $attribute
	 * @return string
	 */
	function createClose($tagname){
		$tmp = "</".strtolower($tagname).">";
		if($this->XML) $this->XMLbuffer .= $tmp;
		return $tmp;
	}

	/**
	 * Generate comment
	 *
	 * @param string $text
	 * @return string
	 */
	function createComment($text){
		$tmp = "\n<!--".$text."-->\n";
		if($this->XML) $this->XMLbuffer .= $tmp;
		return $tmp;
	}

	/**
	 * Generate valid tags.WARNING:No test for empty $inner
	 *
	 * @param string $tagname
	 * @param array/string $attribute
	 * @param string $inner
	 * @return string
	 */
	function createTag($tagname,$attribute='',$inner=''){
		$tmp  = '';
		$tmp .= $this->createOpen($tagname,$attribute);
		$tmp .= $inner;
		$tmp .= $this->createClose($tagname);

		return $tmp;
	}

	/**
	 * Generate valid tags.WARNING:Test for empty $inner end if true generate close tag
	 *
	 * @param string $tagname
	 * @param array/string $attribute
	 * @param string $inner
	 * @return string
	 */
	function create($tagname,$attribute=array(),$inner=''){
		$check = false;
		if(!$inner) $check = true;
		if($check){
			return $this->createCloseTag($tagname,$attribute);
		}
		return $this->createTag($tagname,$attribute,$inner);
	}
	/**
	 * 
	 * Set header
	 */
	function createHeader(){
		header('Content-Type: text/xml');
	}
	/**
	 * 
	 * Create xml version tag
	 * @param array $attribute
	 */
	function createVersion($attribute=array()){
		$attribute = $this->convertStringAtt($attribute);
		if(!isset($attribute['version'])){
			$attribute['version'] = '1.0';
		}
		return '<?xml '.$this->convertAtrribute($attribute).' ?>' . "\n";
	}
}
/**
 * 
 * XML Reader class
 * @author Evgeni Baldziyski
 * @version 0.3 
 * @since 02.09.2007
 * @package basic.xml
 */
class BASIC_XMLReader{

	/** @access protected */
	/**
	 * 
	 * Output array
	 * @var array
	 */
	var $arrOutput = array();
	/**
	 * 
	 * Document type
	 * @var string
	 */
	var $doctype = '';
	/**
	 * 
	 * $resParser
	 * @todo write description
	 * @var string
	 */
	var $resParser = '';
	/**
	 * 
	 * Xml string data
	 * @var string
	 */
	var $strXmlData = '';

	/**
	 * Constructor
	 *
	 * @return XMLReader
	 */
	function BASIC_XMLReader(){
		$this->resParser=xml_parser_create();
		xml_set_object($this->resParser,$this);
		xml_set_element_handler($this->resParser,"tagOpen","tagClosed");
		xml_set_character_data_handler($this->resParser,"tagData");
	}

	/**
	 * Method for load XML of file
	 *
	 * @param string $tfile
	 * @param boolean $doc doctype param tag
	 * @return array
	 */
	function loadFile($tfile,$doc=false){
		$this->thefile = $tfile;
		if(!file_exists($tfile)){
			die(" File ".$tfile." no exist.");
		}
		$th = file($tfile);
		$tdata = implode("\n",$th);
		return $this->loadData($tdata,$doc);
	}

	/**
	 * Method to load custom XML
	 *
	 * @param string $data
	 * @param boolean $doc doctype param tag
	 * @return array
	 */
	function loadData($data,$doc=false){	
		if($doc){
			preg_match("/^[ \n\t\r]*(<!DOCTYPE[^>]+>)/", $data, $r);
			$this->doctype = (isset($r[1]) ? $r[1] : '');
		}
		return $this->parse($data);
	}

	/**
	 * Create XML array
	 *
	 * @param string $strInputXML
	 * @return array
	 */
	function parse($strInputXML){
		$this->strXmlData = xml_parse($this->resParser,$strInputXML);
		if(!$this->strXmlData){
			die(
				sprintf("XMLerror: %sat line %d",
					xml_error_string(xml_get_error_code($this->resParser)),
					xml_get_current_line_number($this->resParser)
				)
			);
		}
		xml_parser_free($this->resParser);
		return $this->arrOutput;
	}

	/** XML HANDLERS **/
	
	/**
	 * Open tag
	 * 
	 * @param object $parser
	 * @param string $name
	 * @param array $attrs
	 */
	function tagOpen($parser,$name,$attrs){
		$tmp = array();
		foreach ($attrs as $k => $v){
			$tmp[strtolower($k)] = $v;
		}
		$tag = array(
			"nodeType" => 1,
			"nodeName"=>strtolower($name),
			"attributes"=>$tmp
		);
		array_push($this->arrOutput,$tag);
	}
	/**
	 * Set tag attributes data 
	 * 
	 * @param object $parser
	 * @param string $tagData
	 */
	function tagData($parser,$tagData){
		if(trim($tagData)){
			if(!isset($this->arrOutput[count($this->arrOutput)-1]['childNodes'])){
				$this->arrOutput[count($this->arrOutput)-1]['childNodes'] = array();
			}
			$this->arrOutput[count($this->arrOutput)-1]['childNodes'][] = array(
				"nodeType" => 3,
				"nodeName"=>'#text#',
				'nodeValue'=>$this->_parseXMLValue($tagData)
			);
		}
	}
	/**
	 * 
	 * Parse XML value
	 * @param string $tvalue
	 * @return string $tvalue
	 */
	function _parseXMLValue($tvalue){
		$tvalue=htmlentities($tvalue);
		return $tvalue;
	}
	/**
	 * Tag closed
	 * 
	 * @param object $parser
	 * @param string $name
	 */
	function tagClosed($parser,$name){
		$this->arrOutput[count($this->arrOutput)-2]['childNodes'][] = $this->arrOutput[count($this->arrOutput)-1];
		array_pop($this->arrOutput);
	}
	/**
	 * 
	 * Add or create xml structure
	 * @param array $array
	 * @param object $xhtml
	 * @return object $data xmlobject
	 */
	function _toXML($array,$xhtml){
			$data = '';
			foreach($array as $k => $v){
				if(isset($v['childNodes'])){
					$data .= $this->_toXML($v['childNodes'],$xhtml);
				}else{
					$data .= $xhtml->create($v['nodeName'],$v['attributes'],$v['nodeValue']);
				}
			}
			return $data;
	}
	/**
	 * Method XHTML generator.$array use currene sintax.
	 * 
	 * <code>
	 * array(
	 * 		[0] => array(
	 * 			[nodeName] => '...',
	 * 			[attribute] => array(
	 * 				[att1] => '...',
	 * 				[attN] => '...'
	 * 			)
	 * 			[childNodes] => array(
	 * 
	 * 			)
	 * 			[nodeValue] => ''
	 * 		)
	 * } 
	 * </code>
	 * @param array $array
	 * @return string
	 */
	function toXML($array){
		$xhtml = new BASIC_XMLGenerator();
		return $this->_toXML($array,$xhtml);
	}
}