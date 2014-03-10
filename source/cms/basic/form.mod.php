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
* @package basic.form
* @version 7.0.6  
*/

/**
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @since 12.03.2012
 * @package basic.form
 */
interface ForeignElementsInterface{
	/**
	 * Remove all foreign (id) rows
	 * 
	 * @param int $id
	 */
	function remove($id);
	/**
	 * Get rows for parent (id) 
	 * 
	 * @param int $id
	 */
	function load($id);
	/**
	 * Update or insert rows for parent (id)
	 * 
	 * @param int $id
	 */
	function update($id);
}
/**
 * @author Evgeni Baldzhiyski
 * @version 0.6
 * @since 23.02.2007
 * @package basic.form
 */
class ForeignElements implements ForeignElementsInterface{
	/**
	 * Parent table name
	 * 
	 * @var string
	 * @access private
	 */
	protected $tag_value = '';
	/**
	 * Field column configuration
	 * @var array
	 * @access private
	 */
	protected $field_el = array();
	
	/**
	 * Current table name: parent table name + element name
	 * 
	 * @var string
	 * @access public
	 */
	var $base = 'multiple';
	/**
	 * Foreign key column name
	 * 
	 * @var string
	 * @access public
	 */
	var $field_id = 'fkey';
	/**
	 * Foreign value column name
	 * 
	 * @access public
	 * @var string
	 */
	var $field_value = 'value';
	/**
	 * Foreign table name(owner) column name
	 * 
	 * @access public
	 * @var string
	 */
	var $field_tag = 'tag';
	/**
	 * Data buffer
	 * 
	 * @var hashmap
	 * @access public
	 */
	var $dataBuffer = array();	
	/**
	 * Constructor
	 *
	 * @param string $baseTableName
	 * @param string $foreignElementName
	 * @param string [$dataType]
	 * @param string [$dataLight]
	 */
	function __construct($baseTableName, $foreignElementName, $dataType = 'int', $dataLight = 11){
		$this->tag_value = $baseTableName.'_'.$foreignElementName;
		$this->base .= '_'.$dataType."_".str_replace(",", '', $dataLight);
		$this->field_el = array($dataType, $dataLight);
	}
	/**
	 * Table creator
	 * 
	 * @access public
	 * @return boolen
	 */
	function SQL($column_name = ''){
		$table = new BasicSqlTable();
		
		if($column_name){
			if($column_name == $this->field_value){
				$table->field($this->field_value, $this->field_el[0], $this->field_el[1]);
			}else{
				$table->field($this->field_tag);
			}
			return BASIC_SQL::init()->createColumn($this->base, $table);
		}else{
			$table->field($this->field_id, 'int', 11);
			$table->field($this->field_value, $this->field_el[0], $this->field_el[1]);
			$table->field($this->field_tag);
		
			$table->key($this->field_id, 'multiple_index');
			$table->key($this->field_tag, 'multiple_index');
	
			return BASIC_SQL::init()->createTable(null, $this->base, $table, 'InnoDB');
		}
	}
	/**
	 * Remove row
	 * 
	 * @access public
	 * @param integer $id
	 * @return void
	 * @see ForeignElementsInterface::remove()
	 */
	function remove($id){
		BASIC_SQL::init()->exec(" DELETE FROM `".$this->base."` WHERE 1=1 
			AND `".$this->field_id."` = ".(int)$id." 			
			AND `".$this->field_tag."` = '".$this->tag_value."' 			
		");
	}
	/**
	 * Update row
	 * 
	 * @access public
	 * @param integer $id 
	 * @return void
	 * @see ForeignElementsInterface::update()
	 */
	function update($id){
		$this->remove($id);
		if(is_array($this->dataBuffer)){
			foreach ($this->dataBuffer as $v){
				BASIC_SQL::init()->exec(" INSERT INTO `".$this->base."` (
					`".$this->field_id."`, `".$this->field_value."`, `".$this->field_tag."`
				)VALUES(
					'".$id."', '".$v."', '".$this->tag_value."'
				) ");
			}
		}
	}
	/**
	 * Load Data Buffer
	 * 
	 * @access public
	 * @param integer $id
	 * @return array
	 * @see ForeignElementsInterface::load()
	 */
	function load($id){
	    $dataBuffer = array();
		$rdr = BASIC_SQL::init()->read_exec(" SELECT `".$this->field_value."` FROM `".$this->base."` WHERE 1=1
			AND `".$this->field_id."` = ".(int)$id."  
			AND `".$this->field_tag."` = '".$this->tag_value."'  
			ORDER BY `".$this->field_value."` 
		");
			   
		       BASIC_ERROR::init()->reset();
		$err = BASIC_ERROR::init()->error();
		if($err['code'] == 1146){
			$tmp = $this->SQL();
			if($tmp){
				BASIC_ERROR::init()->clean();
				return $this->load($id);
			}
			return array();
		}else if($err['code'] == 1054){	
			preg_match("/column( name)? '([^']+)'/", $err['message'], $match);
			if(isset($match[2])){
				$spl = explode(".", $match[2]);
				$column_name = $spl[count($spl) - 1];
				
				$tmp = $this->SQL($column_name);
				if($tmp){
					BASIC_ERROR::init()->clean();
					return $this->load($id);
				}
			}
			return array();
		}
		while ($rdr->read()){
			$dataBuffer[] = $rdr->field($this->field_value);
		}
		return $dataBuffer;
	}
	
	/**
	 * Getter for tag value
	 * 
	 * @return string
	 */
	function getTagValue() {
		return $this->tag_value;
	}
	
	/**
	 * Get all ids for multiple records by given values
	 * 
	 * @param array/int $values
	 * @return object BASIC_SQL reader
	 */
	function getMultyRecords($values){
		$criteria = '';
	
		if(is_array($values)){
			$criteria .= "AND ".$this->field_value." IN (".implode(',', $values).") ";
		}else{
			$criteria .= "AND ".$this->field_value." = ".$values." ";
		}
		return BASIC_SQL::init()->read_exec(" SELECT `".$this->field_id."` FROM `".$this->base."` WHERE 1=1 ".
				"AND `".$this->field_tag."` = '".$this->tag_value."' ".
				$criteria.
				"ORDER BY `".$this->field_value."` "
		);
	}
}
/**
 * Class containing base methods and properties API for package BASIC.FORM
 *
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @since 23.02.2007]
 * @package basic.form
 */
class Component extends BASIC_CLASS{
	/**
	 * Fields register
	 * 
	 * @var array
	 * @access public
	 */
	var $fields  	= array();
	/**
	 * Fields File register
	 * 
	 * @var array
	 * @access public
	 */
	var $fieldsFile = array();
	/**
	 * Foreign field elements register
	 * 
	 * @var array
	 * @access public
	 */
	var $fieldsForeign = array();
	/**
	 * 
	 * 
	 * @var ForeignElementsInterface
	 * @access public
	 */
	var $fieldsForeignManager = 'ForeignElements';
	/**
	 * Data buffer
	 * @var array
	 * @access public
	 */
	var $dataBuffer  	= array();
	/**
	 * Cleaners register
	 * 
	 * @var multiArray
	 * @access public
	 */
	var $globalCleaner = array(
		'varchar' 	=> array('addslashes','stripcslashes'),
		'ini' 		=> array('Int'),
		'float' 	=> array('Float')
	);
	/**
	 * Working table name
	 * 
	 * @access public
	 * @var string
	 */
	var $base 		= '';
	/**
	 * Primary key name
	 * 
	 * @access public
	 * @var string
	 */
	var $field_id   = 'id';
	/**
	 * 
	 * ID
	 * 
	 * @access public
	 * @var integer
	 */
	var $id  = 0;

	# Cleaner API{
	/**
	 * Set the global cleaner for the current component.
	 * 
	 * @access public
	 * @param string $type
	 * @param string $add
	 * @param string $strip
	 * @return void
	 */
	function setCleaner($type,$add,$strip){
		$this->globalCleaner[$type] = array($add, $strip);
	}
	/**
	 * Remove global cleaner.
	 * 
	 * @access public
	 * @param string $type
	 * @return void
	 */
	function unsetCleaner($type){
		if(isset($this->globalCleaner[$type])) unset($this->globalCleaner[$type]);
	}
	/**
	 * Check for cleaner (local/global) and run it on the the value from the URL requests variable.
	 * 
	 * @access public
	 * @param string $type
	 * @param boolean [$is_in]
	 * @param array [$owner]
	 */
	function cleanerDecision($type, $is_in = true, $owner = null){
		if($owner){
			if(!$is_in && isset($owner[1]) && $owner[1]){
				if($owner[1] != '#') return $owner[1];
			}else{
				return $owner[0];
			}
		}else{
			if(isset($this->globalCleaner[$type])){
				if(!$is_in && isset($this->globalCleaner[$type][1]) && $this->globalCleaner[$type][1]){
					if($this->globalCleaner[$type][1] != '#') return $this->globalCleaner[$type][1];
				}else{
					return $this->globalCleaner[$type][0];
				}
			}
		}
		return null;
	}
	
	/**
	 * Clean Decision Array
	 *
	 * @access public
	 * @param array $array
	 * @param boolean [$direction] $direction == false is action load $direction == true is action save
	 * @return array
	 */
	function cleanerDecisionArray($array, $direction = false){
		foreach ($array as $k => $v){
			if(isset($this->fields[$k])){
				$array[$k] = BASIC_URL::init()->other($v, null,
					$this->cleanerDecision($this->fields[$k][3], $direction, $this->fields[$k][7]));
			}
		}
		return $array;
	}
	/**
	 * Create table
	 * 
	 * @access public
	 * @param string [$message]
	 * @return boolean
	 */
	function SQL($message = ''){
		$table = new BasicSqlTable();
		if($message){
			preg_match("/column( name)? '([^']+)'/", $message, $match);
			if(isset($match[2])){
				$spl = explode(".", $match[2]);
				$match[2] = $spl[count($spl) - 1];
				
				foreach ($this->fields as $v){
					if($v[0] == $match[2]){
						return BASIC_SQL::init()->createColumn($this->base, $this->columnProp($v, $table));
					}
				}
			}
			return false;
		}else{
			foreach ($this->fields as $v){
				if($v[3] == 'none' && $v[0] == $this->field_id) continue;
	
				$this->columnProp($v, $table);
			}
			return BASIC_SQL::init()->createTable($this->field_id, $this->base, $table);
		}
	}
	/**
	 * Create the column properties in sql query for create table, add new column in table.
	 * 
	 * @access protected
	 * @param array $field
	 * @param BasicSqlTable $table
	 * @return BasicSqlTable
	 */
	protected function columnProp($field, $table){
		if($field[3] == 'text' || $field[3] == 'longtext' || $field[3] == 'mediumtext'){
			$table->field($field[0], $field[3]);
		}else if($field[3] == 'date' || $field[3] == 'datetime'){
			$table->field($field[0], 'datetime', 0, false, '0000-00-00 00:00:00');
		}else{
			$table->field($field[0], $field[3], $field[2], false, ($field[3] == 'int' || $field[3] == 'float' ? '0' : "''"));
		}
		return $table;
	}
	/**
	 * Get value of $name from data buffer
	 * 
	 * @access public
	 * @param string $name
	 * @return string
	 */
	function getDataBuffer($name){
		if(isset($this->dataBuffer[$name])) return $this->dataBuffer[$name];
		return '';
	}
	/**
	 * 
	 * Set new element in Data Buffer
	 * 
	 * @access public
	 * @param string $name
	 * @param mix $value
	 */
	function setDataBuffer($name, $value){
		$this->dataBuffer[$name] = $value;
	}
	/**
	 * Unset Data Buffer element
	 * 
	 * @access public
	 * @param string $name
	 * @return void
	 */
	function unsetDataBuffer($name){
		if(isset($this->dataBuffer[$name])) unset($this->dataBuffer[$name]);
	}
	/**
	 * Get Data Buffer array
	 * 
	 * @access public
	 * @return array
	 */
	function getBuffer(){
		return $this->dataBuffer;
	}
	/**
	 * Set Data Buffer array
	 * 
	 * @access public
	 * @param array $array
	 * @return void
	 */
	function setBuffer($array){
		foreach ($array as $k => $v) $this->setDataBuffer($k,$v);
	}
	/**
	 * Clean Data Buffer array
	 * 
	 * @access public
	 * @return void
	 */
	function cleanBuffer(){
	    $this->dataBuffer = array();
	}
	/**
	 * Set field, which contain description about its form element, database field and so on
	 * if you want to set field to be first - use special value "first".
	 * 
	 * @param string $name
	 * @param array [$context]
	 * @param string [$after]
	 * @return void
	 */
	function setField($name, $context = array(), $after = ''){
		if($after == 'first' || ($after && isset($this->fields[$after]))){
			$tmp = $this->fields; $this->fields = array();
			
			if($after == 'first'){
				$this->fields[$name] = array();
			}
			foreach ($tmp as $k => $v){
				$this->fields[$k] = $v;
				
				if($k == $after){
					$this->fields[$name] = array();
				}
			}
		}

		if(!isset($context['text'])) 		$context['text'] 		= $name;
		if(!isset($context['dbtype'])) 		$context['dbtype'] 		= 'varchar';
		if(!isset($context['length'])) 		$context['length'] 		= 255;
		if(!isset($context['perm'])) 		$context['perm'] 		= '';
		if(!isset($context['attributes'])) 	$context['attributes'] 	= array();
		if(!isset($context['cleaners'])) 	$context['cleaners'] 	= array();
		if(!isset($context['formtype'])) 	$context['formtype'] 	= 'input';

		if($context['formtype'] != 'none'){
			$ctrl = BASIC_GENERATOR::init()->getControl($context['formtype']);
			if(!$ctrl && $context['formtype'] != 'hidden'){
				throw new Exception(" The type '".$context['formtype']."' is not supported.", 500);
			}
			
			if($context['dbtype'] != 'none' && $context['formtype'] != 'hidden' && (
				(isset($context['attributes']['multiple']) && $context['attributes']['multiple']) ||
				$ctrl->isMultiple() 
			)){
				if(!isset($this->fieldsForeign[$name])){
					$this->fieldsForeign[$name] = new $this->fieldsForeignManager($this->base, $name, $context['dbtype'], $context['length']);
				}
			}
		}
		$tmpArr = array();

		$tmpArr['perm'] 		= $context['perm']; 	  unset($context['perm']);
		$tmpArr['length'] 		= $context['length']; 	  unset($context['length']);
		$tmpArr['dbtype'] 		= $context['dbtype']; 	  unset($context['dbtype']);
		$tmpArr['text'] 		= $context['text']; 	  unset($context['text']);
		$tmpArr['formtype'] 	= $context['formtype'];   unset($context['formtype']);
		$tmpArr['attributes'] 	= $context['attributes']; unset($context['attributes']);
		$tmpArr['cleaners'] 	= $context['cleaners'];   unset($context['cleaners']);

		$this->fields[$name] = array($name,
			$tmpArr['perm'],
			$tmpArr['length'],
			$tmpArr['dbtype'],
			$tmpArr['text'],
			$tmpArr['formtype'],
			$tmpArr['attributes'],
			$tmpArr['cleaners']
		);
		foreach ($context as $k => $v){
			$this->fields[$name][$k] = $v;
		}
	}
	/**
	 * Get field element
	 * 
	 * @access public
	 * @param string $name
	 * @param boolean [$acs] access
	 * @return array|null
	 */
	function getField($name,$acs = true){
		if(isset($this->fields[$name])){
			$arr_tmp = $this->fields[$name];
			
			if($acs){
				$arr_tmp['perm'] 		= $arr_tmp[1]; unset($arr_tmp[1]);
				$arr_tmp['length'] 		= $arr_tmp[2]; unset($arr_tmp[2]);
				$arr_tmp['dbtype'] 		= $arr_tmp[3]; unset($arr_tmp[3]);
				$arr_tmp['text'] 		= $arr_tmp[4]; unset($arr_tmp[4]);
				$arr_tmp['formtype'] 	= $arr_tmp[5]; unset($arr_tmp[5]);
				$arr_tmp['attributes'] 	= $arr_tmp[6]; unset($arr_tmp[6]);
				$arr_tmp['cleaners'] 	= $arr_tmp[7]; unset($arr_tmp[7]);
				unset($arr_tmp[0]);
			}
			return $arr_tmp;
		}
		return null;
	}
	/**
	 * Remove field from fields array
	 * 
	 * @access public
	 * @param string $name field name
	 * @return void
	 */
	function unsetField($name){
		if(isset($this->fields[$name])){
			unset($this->fields[$name]);
		}
	}
	/**
	 * Update field setting, only these in $context parameter.
	 * Retrurn updated field array
	 * 
	 * @access public
	 * @param string $name
	 * @param array $context
	 * @return array|null
	 */
	function updateField($name,$context){
	    if($arrFil = $this->getField($name)){
				
		    foreach ($context as $k => $v){
		    	if(is_array($v)){
		    		if(!is_array($arrFil[$k])){
		    			$arrFil[$k] = array();
		    		}
		    		foreach($v as $kk => $vv){
		    			$arrFil[$k][$kk] = $vv;
		    		}
		    	}else{
		        	$arrFil[$k] = $v;
		    	}
		    }
		    $this->setField($name,$arrFil);
		    return $arrFil;
	    }
	    return null;
	}
	/**
	 * Set foreign fields
	 * 
	 * @param string $tbl
	 * @param string $name
	 * @param string [$typedata]
	 * @param integer [$lengthdata]
	 */
	function setFieldsForeign($tbl, $name, $typedata = 'int', $lengthdata = 11){
		if(!isset($this->fieldsForeign[$name])){
			$this->fieldsForeign[$name] = new $this->fieldsForeignManager($tbl, $name, $typedata, $lengthdata);
		}
	}
	/**
	 * Unset foreign fields
	 * 
	 * @access public
	 * @param string $name
	 * @return void
	 */
	function unsetFieldsForeign($name){
		if(isset($this->fieldsForeign[$name])) unset($this->fieldsForeign[$name]);
	}
	/**
	 * Check for errors before save data. Return true, if exist
	 * 
	 * @access public
	 * @return boolean
	 */
	function test(){
		$err = false;
		foreach ($this->fields as $k => $v){
			if(!$this->getDataBuffer($v[0])){
				$var_url = BASIC_URL::init()->request($this->prefix.$v[0], $this->cleanerDecision($v[3], true, $v[7]));
				
				$this->setDataBuffer($v[0],$var_url);
				if(($v[1] == 1 && (string)$this->dataBuffer[$v[0]] == '')){

					BASIC_ERROR::init()->append(500, $v[4]);
					$err = true;
				}
			}
		}
		return $err;
	}
	/**
	 * Sql query generator
	 *
	 * @version 0.3
	 * @access public
	 * @param string [$criteria]
	 * @param boolean [$include_all]
	 * @return string
	 */
	function select($criteria = '', $include_all = false){
		$tmp = "`".$this->base."`.`".$this->field_id."`";
		foreach ($this->fields as $k => $v){
			if($v[3] != 'none'){
				$tmp .= ",\n";
					
				if(isset($this->fieldsForeign[$k])){
					$tmp .= " '' AS `".$k."`";
				}else{
					if(strpos($k,' ') !== false){
						$tmp .= " ".$k." "; // is sub query
					}else{
						$tmp .= "`".$this->base."`.`".$k."` ";
					}
				}
			}
		}
		$query = " SELECT ".
				($include_all ? '`'.$this->base."`.*" : "").
				($include_all && $tmp ? ",\n" : '').$tmp.
				" FROM `".$this->base."` WHERE 1=1 ".$criteria;

		return $query;
	}
    /**
	 * Add new row in database table
	 * 
	 * @access public
	 * @return boolean
     */
	function ActionAdd(){
		$cleanedArray = $this->cleanBedVar();

		$fields = '';
		$values = '';
		foreach($cleanedArray as $k => $v){
			if(isset($this->fieldsFile[$k])){
				$value = $this->fieldsFile[$k]->add();
				if($value){
					$value = str_replace("//", "/", $this->fieldsFile[$k]->upDir."/".$value);
					$this->setDataBuffer($k, $value);
					if($values) $values .= ","; $values .= "'".$value."'";
					if($fields) $fields .= ","; $fields .= "`".$k."`";
				}else{
					$this->setDataBuffer($k, '');
				}
			}else{
				$value = (is_array($v)?serialize($v):$v);
				if($values) $values .= ","; $values .= "'".$value."'";
				if($fields) $fields .= ","; $fields .= "`".$k."`";
			}
		}

		if(!$this->messages && !BASIC_ERROR::init()->exist(array('warning', 'fatal'))){
			BASIC_SQL::init()->exec(" INSERT INTO `".$this->base."` (".$fields.") VALUES (".$values.")");
			
			   BASIC_ERROR::init()->reset();
			$err = BASIC_ERROR::init()->error();
			if($err['code'] == 1054){
				$tmp = $this->SQL($err['message']);
				if($tmp){
					BASIC_ERROR::init()->clean();
					return $this->ActionAdd();
				}
			}			
			
			$last = BASIC_SQL::init()->getLastId();
			$this->updateForeignStructure($last);
			
			return $last;
		}
		return false;
	}
    /**
	 * Update database row
	 * 
	 * @access public
	 * @param integer $id
	 * @return boolean|integer is it's success return row id, otherwise - false
     */
	function ActionEdit($id,$action = null,$rules = ''){
		$cleanedArray = $this->cleanBedVar();
		$query = "";
		foreach($cleanedArray as $k => $v){
			if(isset($this->fieldsFile[$k])){
				if(!$this->fieldsFile[$k]->test()){
					$res = BASIC_SQL::init()->read_exec(" SELECT `".$k."` FROM `".$this->base."` WHERE `".$this->field_id."` = ".$id." ", true);
				   		
							BASIC_ERROR::init()->reset();
					$err = BASIC_ERROR::init()->error();
					if($err['code'] == 1054){
						$tmp = $this->SQL($err['message']);
						if($tmp){
							BASIC_ERROR::init()->clean();
							return $this->ActionEdit($id);
						}
					}	
					$value = $this->fieldsFile[$k]->edit($res[$k]);
					if($value){
						$value = str_replace("//", "/", $this->fieldsFile[$k]->upDir."/".$value);
						$this->setDataBuffer($k, $value);
						if($query) $query .= ", \n"; $query .= "\t `" . $k . "` = '".$value."'";
					}else{
						$this->setDataBuffer($k, '');	
					}
				}
			}else{
				$value = (is_array($v)?serialize($v):$v);
				if($query) $query .= ", \n"; $query .= "\t `" . $k . "` = '".$value."'";
			}
		}
		if(!$this->messages && !BASIC_ERROR::init()->exist(array('warning', 'fatal'))){
			BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET \n".$query." WHERE `".$this->field_id . "` = ".$id." ".$rules);
				   
				   BASIC_ERROR::init()->reset();
			$err = BASIC_ERROR::init()->error();
			if($err['code'] == 1054){
				$tmp = $this->SQL($err['message']);
				if($tmp){
					BASIC_ERROR::init()->clean();
					return $this->ActionEdit($id);
				}
			}			
			$this->updateForeignStructure($id);

			return $id;
		}
		return false;
	}
	/**
	 * Set Data Buffer with data from database
	 * 
	 * @access public
	 * @param integer $id
	 * @return void
	 */
	function ActionLoad($id = 0){
		if(!$id) $id = (int)$this->id;

		$row = $this->getRecord($id, 'row', true);

		foreach($this->fields as $k => $v){
			if(isset($this->fieldsForeign[$k])){
				$this->dataBuffer[$k] = $this->fieldsForeign[$k]->load($id);
			}else if(!isset($row[$k]) || $v[3] == 'none'){
				//$this->dataBuffer[$k] = '';
			}else{
				$this->dataBuffer[$k] = BASIC_URL::init()->other($row[$k], null,
					$this->cleanerDecision($v[3], false, $v[7])
				);
			}
		}
		if($this->dataBuffer){
			$this->dataBuffer[$this->field_id] = $row[$this->field_id];
		}
	}
	/**
	 * Delete row from db table by id
	 * If there is field from type file, delete physical file too
	 * 
	 * @access public
	 * @param array|integer $id
	 * @param string [$action]
	 * @param string [$rules]
	 * @return boolen
	 */
	function ActionRemove($id = 0, $action = '', $rules = ''){
		if($id){
    		if(!is_array($id)) $id = array($id);
    		
    		if(count($id) > 0){
    			$file = array();
    			$criteria = " WHERE `".$this->field_id."` IN (".implode(",", $id).") ".$rules;
    			foreach ($this->fields as $v){
    				if($v[5] == "file"){
    					$file[$v[0]] = BASIC_GENERATOR::init()->convertStringAtt($v[6]);
    				}
    			}
    			BASIC::init()->imported('upload.mod');
    			$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->base."`".$criteria);
    			while($rdr->read()){
    				foreach ($file as $k => $v){
    					$fl = new BasicUpload(null);
    					$fl->upDir = $v['dir'];
    					if(isset($v['onDelete'])){
    						$fl->onDelete = $v['onDelete'];
    					}
    					$fl->delete($rdr->field($k));
    				}
    				$this->updateForeignStructure($rdr->field($this->field_id), true);
    			}
    			BASIC_SQL::init()->exec(" DELETE FROM `".$this->base."`".$criteria);
    		}
    		return true;
		}
		return false;
	}
	/**
	 * Updater Foreign Structure
	 *
	 * @access public
	 * @param integer $keyId
	 * @param boolen [$onlyremove]
	 * @return void
	 */
	function updateForeignStructure($keyId, $onlyremove=false){
		if(!BASIC_ERROR::init()->exist()){
			foreach ($this->fieldsForeign as $k => $v){
				if($onlyremove){
					$v->remove($keyId);
				}else{
					$v->dataBuffer = $this->getDataBuffer($k);
					$v->update($keyId);
				}
			}
		}
	}
	/**
	 * Clean working buffer
	 *
	 * @access public
	 * @return array
	 */
	function cleanBedVar(){
		$tmp = $this->dataBuffer;
		foreach($tmp as $k => $v){
			if(!isset($this->fields[$k]) ||
				$this->fields[$k][3] == 'none' || 
				isset($this->fieldsForeign[$k])
			){
				unset($tmp[$k]);
			}
		}
		return $tmp;
	}
	/**
	 * Get record from component db table
	 *
	 * @access public
	 * @param integer|array [$id]
	 * @param string[row|object] $type
	 * @param boolean [$include_all]
	 * @return array|ComponentReader
	 */
	function getRecord($id = 0, $type = 'row', $include_all = false){
		if(!$id) $id = $this->id;
		if(!is_array($id)) $id = array($id);
	
		$rdr = $this->getRecords($id, '', $include_all);
	
		if($type == 'row'){
			$rdr->read();
			return $rdr->getItems();
		}
		return $rdr;
	}
	/**
	 * Full record's data loader
	 *
	 * @access public
	 * @param array [$ids]
	 * @param string $criteria
	 * @param boolean $include_all
	 * @return ComponentReader
	 * @version 0.2
	 */
	function getRecords($ids = array(), $criteria = '', $include_all = false){
		if($ids){
			if(!is_array($ids)) $ids = array($ids);
			$criteria = " AND `".$this->base."`.`".$this->field_id."` IN (".implode(",",$ids).") ".$criteria;
		}
		$rdr = BASIC_SQL::init()->read_exec($this->select($criteria, $include_all));
	
		BASIC_ERROR::init()->reset();
		$err = 	BASIC_ERROR::init()->error();
		if($err['code'] == 1146){
			if($tmp = $this->SQL()){
				//FIX - run only if create parent table
				foreach ($this->fieldsForeign as $fkey => $fval){
					$fval->load(0);
				}
				BASIC_ERROR::init()->clean();
			}
		}else if($err['code'] == 1054){
			if($tmp = $this->SQL($err['message'])){
				//FIX - run only if create parent table
				foreach ($this->fieldsForeign as $fkey => $fval){
					$fval->load(0);
				}
				BASIC_ERROR::init()->clean();
				return $this->getRecords($ids, $criteria);
			}
		}
		return new ComponentReader($rdr, $this);
	}
	/**
	 * "getRecords"'s shorcut function. Miss useful first parametar $ids on original method.
	 *
	 * @access public
	 * @param string $criteria
	 * @param boolean $include_all
	 * @return ComponentReader
	 */
	function read($criteria = '', $include_all = false){
		return $this->getRecords(null, $criteria, $include_all);
	}
	/**
	 * Extension of "getRecords", return result in array
	 *
	 * @access public
	 * @param array [$ids]
	 * @param string $criteria
	 * @return array
	 */
	function getRecordsArray($ids = array(),$criteria = ''){
		$rdr = $this->getRecords($ids, $criteria);
		$tmp = array();
		while($rdr->read()){
			$tmp[] = $rdr->getItems();
		}
		return $tmp;
	}
	/**
	 * Create default settings -set base and prefix as class name
	 * 
	 * @access public
	 * @return void
	 */
	function createDefaultSettings(){
		$this->base = $this->prefix = get_class($this);
	}
}
/**
 * Object extends sqlReader functionality for reading components data
 * 
 * @author Evgeni Baldzisky
 * @version 0.1
 * @since 15.01.2009
 */
class ComponentReader {
	/**
	 * @access private
	 * @var sqlReader
	 */
	protected $rdr = null;
	/**
	 * @access private
	 * @var BaseDisplayComponentClass
	 */
	protected $target = null;
	/**
	 * @access private
	 * @var array
	 */
	protected $tmp_buffer = array();
	/**
	 * Result data from sql query
	 * 
	 * @access private
	 * @var array
	 */
	protected $buffer = array();
	/**
	 * @access private
	 * @var integer
	 */
	protected $index_position = -1;
	/**
	 * Constructor
	 * Create ComponentReader
	 *
	 * @access public
	 * @param sqlReader $rdr
	 * @param BaseDisplayComponentClass [target]
	 */
	function __construct($rdr, $target = null){
		$this->rdr = $rdr;

		while($this->rdr->read()){
			$perm = true;
			
			if($perm !== false){
	            foreach ($this->rdr->getItems() as $k => $v){
	                if($k == $target->field_id) continue;
	                
	              	if(isset($target->fieldsForeign[$k])){
	              	    $this->rdr->setItem($k, $target->fieldsForeign[$k]->load($this->rdr->item($target->field_id)));
					}else{
						if(!isset($target->fields[$k])){
							$this->rdr->setItem($k,$v);
						}else{
							$this->rdr->setItem($k, BASIC_URL::init()->other($v, null,
								$target->cleanerDecision($target->fields[$k][3], false, $target->fields[$k][7])
							));
						}
					}
	            }
	            $this->buffer[] = $this->rdr->getItems();
			}
		}
	}
	/**
	 * Add new element to object buffer data
	 *  
	 * @access public
	 * @param array $row
	 * @return void
	 */
	function addRow($row){
		$this->buffer[] = $row;
	}
	/**
	 * Add some new elements to object buffer data
	 * 
	 * @access public
	 * @param array $row
	 * @return void
	 */
	function addRows($rows){
		foreach($rows as $row) $this->buffer[] = $row;
	}
	/**
	 * Get current record from object buffer 
	 * 
	 * @access public
	 * @return array
	 */
	function getItems(){
		return $this->tmp_buffer;
	}
	/**
	 * Append|Edit elements of current record
	 *
	 * @access public
 	 * @param array $arr
 	 * @return void
	 */
	function setItems($arr){
		foreach ($arr as $k => $v){
			$this->setItem($k,$v);
		}
	}
	/**
	 * Append|Edit element of current record
	 *
	 * @access public
	 * @param string $name
	 * @param mix $value
	 * @return void
	 */
	function setItem($name,$value){
		$this->tmp_buffer[$name] = $value;
		//$this->buffer[$this->index_position+1][$name] = $value;
		$this->buffer[$this->index_position][$name] = $value;
	}
	/**
	 * Read and return next record
	 * 
	 * @access public
	 * @param mix [$cleanCall]
	 * @return array|null
	 */
	function read($cleanCall = null){
		$this->index_position++;
		if(isset($this->buffer[$this->index_position])){
			$this->tmp_buffer = $this->buffer[$this->index_position];
			
			return $this->tmp_buffer;
		}
		$this->index_position = -1;
		return null;
	}
	/**
	 * Reset index position
	 * 
	 * @access public
	 * @return void
	 */
	function reset(){
		$this->index_position = -1;
	}
	/**
	 * Get value from buffer by index
	 *
	 * @access public
	 * @param integer $index
	 * @return array
	 */
	function readIndex($index){
		return (isset($this->buffer[$index]) ? $this->buffer[$index] : '');
	}
	/**
	 * Get element of current record
	 * 
	 * @access public
	 * @param string $name
	 * @param string [$colback]
	 * @return mix
	 */
	function item($name,$colback = null){
		return (isset($this->tmp_buffer[$name]) ? $this->tmp_buffer[$name] : ''); 
	}
	/**
	 * Get number of records
	 * 
	 * @access public
	 * @return int
	 */
	function num_rows(){
		return count($this->buffer);
	}
	/**
	 * Return array in format for select controls (select, moveselect, ...)
	 * 
	 * @access public
	 * @param string [$id] default value 'id'
	 * @param string [$text] default value 'title' 
	 * @param array [$before]
	 * @return array
	 */
	function getSelectData($id = 'id', $text = 'title', $before = array()){
		if(!is_array($before)) $before = array();
		
		$this->reset();
		while($this->read()){
			$before[$this->item($id)] = $this->item($text); 
		}
		$this->reset();
		
		return $before;
	}
	/**
	 * Get whole data as array
	 * 
	 * @access public
	 * @return array
	 */
	function getArrayData(){
		return $this->buffer;
	}
	
	static public function getEmptyReader(){
		return new ComponentReader(BASIC_SQL::init()->getEmptyReader());
	}
}