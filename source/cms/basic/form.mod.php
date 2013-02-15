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
* @package basic.form
* @version 7.0.4  
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
	function SQL(){
		$table = new BasicSqlTable();
		
		$table->field($this->field_id, 'int', 11);
		$table->field($this->field_value, $this->field_el[0], $this->field_el[1]);
		$table->field($this->field_tag);
		
		$table->key($this->field_id, 'multiple_index');
		$table->key($this->field_tag, 'multiple_index');

		return BASIC_SQL::init()->createTable(null, $this->base, $table, 'InnoDB');
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
		}
		while ($rdr->read()){
			$dataBuffer[] = $rdr->field($this->field_value);
		}
		return $dataBuffer;
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
class BaseDisplayComponentClass extends BASIC_CLASS{
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
	 * @return boolean
	 */
	function SQL(){
		$data = '';
		foreach ($this->fields as $v){
			if($v[3] == 'none' && $v[0] == $this->field_id) continue;

			$data .= $this->columnProp($v).",";
		}
		return BASIC_SQL::init()->createTable($this->field_id, $this->base,$data);
	}
	/**
	 * Add column
	 * 
	 * @access public
	 * @param string $message
	 * @return boolean
	 */
	function addColumn($message){
		preg_match("/column( name)? '([^']+)'/", $message, $math);

		foreach ($this->fields as $v){
			if($v[0] == $math[2]){
				return BASIC_SQL::init()->createColumn($this->base,$this->columnProp($v));
			}
		}
	}
	/**
	 * Create the column properties in sql query for create table, add new column in table.
	 * 
	 * @access public
	 * @param array $v
	 * @return string
	 */
	function columnProp($v){
		$sql = '';
		$sql .= "	`".$v[0]."` ";
		if($v[3] == 'text' || $v[3] == 'longtext' || $v[3] == 'mediumtext'){
			$sql .= $v[3];//." NOT NULL DEFAULT '' ";
		}else if($v[3] == 'date' || $v[3] == 'datetime'){
			$v[3] = 'datetime';
			$sql .= $v[3]." NOT NULL DEFAULT '0000-00-00 00:00:00' ";
		//}else if($v[3] == 'int'){
			//$sql .= $v[3];
		}else{
			$sql .= $v[3]."(".$v[2].") NOT NULL DEFAULT ".($v[3] == 'int' || $v[3] == 'float' ? '0' : "''")." ";
		}
		
		return $sql;
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
					$this->fieldsForeign[$name] = new ForeignElements($this->base, $name, $context['dbtype'], $context['length']);
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
			$this->fieldsForeign[$name] = new ForeignElements($tbl, $name, $typedata, $lengthdata);
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
				$var_url = BASIC_URL::init()->request($this->prefix.$v[0], $this->cleanerDecision($v[3],true,$v[7]));
				
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
				$tmp = $this->addColumn($err['message']);
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
						$tmp = $this->addColumn($err['message']);
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
				$tmp = $this->addColumn($err['message']);
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
	    
		$column = '';
		foreach($this->fields as $v){
			if($v[3] == 'none') continue;

			$column .= '`'.$v[0].'`,';
		}
		$column .= '`'.$this->field_id."`";

		$rdr = BASIC_SQL::init()->read_exec(" SELECT ".$column." FROM `".$this->base."` WHERE `".$this->field_id."` = ".(int)$id." ");

			   BASIC_ERROR::init()->reset();
		$err = BASIC_ERROR::init()->error();
		if($err['code'] == 1146){
			$tmp = $this->SQL();
			if($tmp){
				BASIC_ERROR::init()->clean();
			}
		}else if($err['code'] == 1054){
			$tmp = $this->addColumn($err['message']);
			if($tmp){
				BASIC_ERROR::init()->clean();
				return $this->ActionLoad($id);
			}
		}

		$rdr->read();
		if($rdr->num_rows() > 0){
			$tmp = $rdr->getItems();
			foreach($this->fields as $k => $v){

				// test for special fields and load ower data

				if(!isset($tmp[$k]) || $v[3] == 'none'){
					$this->dataBuffer[$k] = ''; continue;
				}else{
					$this->dataBuffer[$k] = BASIC_URL::init()->other($tmp[$k],null,
						$this->cleanerDecision($v[3], false, $v[7])
					);
				}
			}
			$this->dataBuffer[$this->field_id] = $tmp[$this->field_id];
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
	function ActionRemove($id=0,$action = '',$rules = ''){
		if($id){
    		if(!is_array($id)) $id = array($id);
    		
    		if(count($id) > 0){
    			$file = array();
    			$criteria = " WHERE `".$this->field_id."` IN (".implode(",",$id).") ".$rules;
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
    				$this->updateForeignStructure($rdr->field($this->field_id),true);
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
	function updateForeignStructure($keyId,$onlyremove=false){
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
/**
 * @author Evgeni Baldziyski
 * @version 3.2
 * @since 02.02.2008
 * @package cms.form
 */
class DysplayComponent extends BaseDisplayComponentClass{
	/**
	 * Register default actions
	 * 
	 * ActionFormAdd 'Add new records'
	 * ActionFormEdit 'Edit/View'
	 * ActionRemove 'Remove selected elements'
	 * ActionSave 'Save'
	 * ActionBack 'Back'
	 * ActionLoad
	 * ActionError
	 * ActionFileRemove
	 * ActionList
	 *
	 * @access public
	 * @var array
	 */
	var $actions = array(
		'add'   	  => array('ActionFormAdd', 1, 'Add new records'),
		'edit'   	  => array('ActionFormEdit',2, 'Edit/View'),
		//'details'	  => array('ActionDetails', 2, 'Details'),
		'delete'	  => array('ActionRemove',  1, 'Remove checked'),
		'save'		  => array('ActionSave', 	3, 'Save'),
		'cancel'	  => array('ActionBack', 	3, 'Back'),
		// System Actions
		'load'		  => array('ActionLoad',    0),
		'error'		  => array('ActionError',   0),
		//'order_up'  => array('ActionOrder',   0),
		//'order_down'=> array('ActionOrder',   0),
		'filter' 	  => array('', 			    0, 'Filter'),
		'fileRemove'  => array('ActionFileRemove', 0),
		// Default Call Action
		'list' 		  => array('ActionList',    0)
	);
	/**
	 * Error Action Name.If exist error system will redirect to errorAction handler.
	 * 
	 * @access public
	 * @var string
	 */
	var $errorAction = 'edit';
	/**
	 * Used from test method.
	 * if this property have value != '' when createInterface execute errorAction.
	 *
	 * @access public
	 * @var array
	 */
	var $messages = array();
	/**
	 * 
	 * @access public
	 * @var string
	 */
	public $prefix = '';
	/**
	 * Container for component actions name
	 * 
	 * @access public
	 * @var string
	 */
	var $cmd = '';
	/**
	 * Url parameter for action name
	 * 
	 * @access public
	 * @var string
	 */
	var $urlCmdName = 'cmd';
	/**
	 * Key for field order_id on db table, if(no exist) auto created
	 * 
	 * @access private
	 * @var boolean
	 */
	private $_ordering = false;
	/**
	 * Container missing variables
	 * 
	 * @access public
	 * @var array
	 */
	var $miss = array(); 				
	/**
	 * Container hidden elements
	 * 
	 * @access public
	 * @var string
	 */
	var $hidden_el = ''; 					
	/**
	 * Container for lingual fields	
	 * 
	 * @access public
	 * @var array
	 */
	var $nasional = array(); 			   

	// List manager property //
	/**
	 * List manager property
	 * 
	 * @access public
	 * @var array
	 */
	var $system = array();
	/**
	 * Max number rows in list view
	 * 
	 * @access public
	 * @var integer
	 */
	var $maxrow = 20;
	/**
	 * Contain BasicSorting instance
	 * 
	 * @access public
	 * @var BasicSorting 
	 */
	var $sorting = null;
	/**
	 * Contain BasicComponentPaging instance
	 * 
	 * @access public
	 * @var BasicComponentPaging
	 */
	var $paging = null;
	/**
	 * Contain BasicFilterInterface instance
	 * 
	 * @access public
	 * @var BasicFilterInterface
	 */
	var $filter = null;
	/**
	 * hashmap with columns data for list UI.
	 * 
	 * @access public
	 * @var array
	 */
	var $map = array();
	/**
	 * show/hide language bar in formUI.
	 * 
	 * @access public
	 * @var boolean
	 */
	var $useJSLang = true;
	/**
	 * Flag for lock form save state
	 * 
	 * @access public
	 * @var boolean
	 */
	var $useSaveState = true;
	/**
	 * Pointer to method for additional form validation
	 * 
	 * Pointer to the method, that will be used for additional validation of the data.
	 * If the pointer is string will be call function else will be call array[0]->array[1]
	 * value = array(&$obj,'method') === $obj->method($this,$id,$action)
	 * value = 'function' === function($this,$id,$action)
	 *
	 * NEW :: value = array('this','method) === $this->method($id,$action)
	 *
	 * @access public
	 * @var string|array
	 */
	var $specialTest = '';
	/**
	 * indicates if the validators will be used
	 * 
	 * @access public
	 * @var boolean
	 */
	var $autoTest 	 = true;
	
	/**
	 * Template name for form view (ActionFormAdd, ActionFormEdit, ...)
	 * 
	 * @access public
	 * @var string
	 */
	var $template_form 	  	   = 'cmp-form.tpl';
	/**
	 * Default view template 'cmp-form.tpl
	 * 
	 * @access public
	 * @var string
	 */
	var $template_form_default = 'cmp-form.tpl';
	/**
	 * Template name for list view (ActionList, ...)
	 * 
	 * @access public
	 * @var string
	 */	
	var $template_list 	  	   = 'cmp-list.tpl';
	/**
	 * Default list template - 'cmp-list.tpl'
	 * 
	 * @access public
	 * @var string
	 */
	var $template_list_default = 'cmp-list.tpl';
	/**
	 * Template for details view (ActionDetails, ...)
	 * 
	 * @access public
	 * @var string
	 */	
	var $template_details 		  = 'cmp-details.tpl';
	/**
	 * Default details template - 'cmp-details.tpl'
	 * 
	 * @access public
	 * @var string
	 */
	var $template_details_default = 'cmp-details.tpl';
	/**
	 * Template  name for list filter (when this->filter != null)
	 * 
	 * @access public
	 * @var string
	 */	
	var $template_filter  		 = 'cmp-filter.tpl';
	/**
	 * Default list filter  template - 'cmp-filter.tpl'
	 * 
	 * @access public
	 * @var string
	 */
	var $template_filter_default = 'cmp-filter.tpl';
	/**
	 * Templates declarations
	 * 
	 * @access public
	 * @var array
	 */
	var $templates = array(
		// form template info
		'form-dynamic' => 'fields',
		'form-vars' => array(
			'prefix'  => 'prefix',
			'perm' 	  => 'perm',
			'label'   => 'label',
			'ctrl' 	  => 'ctrl',
			'message' => 'message',
			'buttons_bar' => 'buttons_bar',
			'value' 	=> 'value'
		),
		'list-vars' => array(
			'head-check' 		 => 'use_checkbox',
			'head-order' 		 => 'use_order',
			'head-dynamic' 		 => 'headers',
			'head-length' 		 => 'column_length',
			'head-dynamic-attr'  => 'attr',
			'head-dynamic-label' => 'label',
			'head-dynamic-selected' => 'selected',
			'head-dynamic-isdown' => 'isdown',
		
			'body-dynamic' 			 => 'rows',
			'body-dynamic-evenclass' => 'even_class',
			
			'body-dynamic-rownumber' 	=> 'row_number',
			'body-dynamic-rowlevel' 	=> 'row_level',
			'body-dynamic-columns' 		=> 'columns',
			'body-dynamic-columns-attr' => 'attr',
			'body-dynamic-columns-label'=> 'label',
			'body-dynamic-id' 			=> 'id',
			'body-dynamic-actionbar' 	=> 'action_bar',
		
			'action-bar' => 'action_bar',
			'paging-bar' => 'paging_bar',
			
			'prefix' => 'prefix',
			'cmd' 	 => 'cmd',
			'idcmd'  => 'idcmd'
		),
		'action-bar-vars' => array(
			'actions' 		    => 'actions',
			'actions-key'       => 'key',
			'actions-pkey'      => 'pkey',
			'actions-text' 	    => 'text',
			'actions-link' 	    => 'link',
			'actions-disable'   => 'disable',
			'actions-rule-type' => 'rule_type',
			'actions-rule-text' => 'rule_text',
			'is-ie7' 		    => 'is_ie7',
			'prefix' 		    => 'prefix',
			'cmd' 		  	    => 'cmd'
		),
		'row-action-bar-vars' => array(
			'function' 		  => 'function',
			'level' 		  => 'level',
			'id' 			  => 'id',
			'rownumber' 	  => 'row_number',
			'orderbar' 		  => 'order_bar',
			'orderbar-key' 	  => 'key',
			'orderbar-link'   => 'link',
			'actions' 		  => 'actions',
			'actions-key' 	  => 'key',
			'actions-pkey' 	  => 'pkey',
			'actions-text' 	  => 'text',
			'actions-link' 	  => 'link',
			'actions-disable' => 'disable',
			'actions-rule-type' => 'rule_type',
			'actions-rule-text' => 'rule_text',
			'prefix' 		  => 'prefix',
			'is-ie7' 		  => 'is_ie7',
			'idcmd' 		  => 'idcmd'
		),
		'form-action-bar-vars' => array(
			'rules' 		 => 'rules',
			'rules-type' 	 => 'type',
			'rules-key' 	 => 'key',
			'rules-text' 	 => 'text',
			
			'actions'	 	 => 'actions',
			'actions-key' 	 => 'key',
			'actions-pkey' 	 => 'pkey',
			'actions-text' 	 => 'text',
			'actions-disable'=> 'disable',
		
			'actions-rule-type' => 'rule_type',
			'actions-rule-text' => 'rule_text',
		
			'is-ie7' 		 => 'is_ie7',
			'prefix' 		 => 'prefix',
			'cmd' 		 	 => 'cmd',
			
			// this vars are supported in "form-vars" array also
			'linguals' 		 => 'linguals',
			'linguals-key' 	 => 'key',
			'linguals-text'  => 'text',
			'linguals-flag'  => 'flag',
			'lingual-current'=> 'current'
		)
	);
	/**
	 * Get system variables
	 * 
	 * @access public
	 * @param boolean $state
	 * @return arrray
	 */
	function getSystemVars($state = true){
		$tmp = $this->system;
		if($this->sorting && !$state){
			$tmp[] = $this->sorting->getPrefix().'dir';
			$tmp[] = $this->sorting->getPrefix().'column';
		}
		return $tmp;
	}
	/**
	 * Get field message
	 * 
	 * @access public
	 * @param string $name_field
	 * @return string|integer
	 */
	function getMessage($name_field){
		if(isset($this->messages[$name_field])){
			return $this->messages[$name_field];
		}
		return 0;
	}
	/**
	 * Set field message
	 *
	 * @param string $name_field
	 * @param integer|string $code
	 * @return boolen
	 */
	function setMessage($name_field,$code){
		$this->messages[$name_field] = $code;
		return true;
	}
	/**
	 * Remove message from message container by field name
	 * 
	 * @access public
	 * @param string $name_field
	 * @return void
	 */
	function unsetMessage($name_field){
		unset($this->messages[$name_field]);
	}
	/**
	 * Reset message container
	 * 
	 * @access public
	 * @return void
	 */
	function cleanMessages(){
		$this->messages = array();
	}
	/**
	 * Created SQL data base code
	 * @access public
	 * @return boolen
	 */
	function SQL(){
		$data = '';
		foreach ($this->fields as $key => $val){
			if($val[3] == 'none' || isset($this->fieldsForeign[$key])) continue;

			if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$key])){
				foreach(BASIC_LANGUAGE::init()->language as $k => $l){
					$multi = $val; $multi[0] = $multi[0]."_".$k;
					$data .= $this->columnProp($multi).",";
				}
			}else{
				$data .= $this->columnProp($val).",";
			}
		}
		return BASIC_SQL::init()->createTable($this->field_id, $this->base, $data);
	}
	/**
	 * Create db table column
	 * 
	 * @access public
	 * @param string $message
	 * @return boolean
	 * @see BaseDisplayComponentClass::addColumn()
	 */
	function addColumn($message){
		preg_match("/column( name)? '([^']+)'/",$message, $math);
		
		if(isset($math[2])){
			$math[2] = str_replace($this->base.".", "", $math[2]);
		}
		
		foreach ($this->fields as $fk => $v){
			if($v[3] == 'none' || isset($this->fieldsForeign[$fk])) continue;

			if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$fk])){
				foreach(BASIC_LANGUAGE::init()->language as $k => $l){
					if(preg_replace("/_".$k."$/", "", $math[2]) == $v[0]){
						$v[0] = $fk."_".$k;
						return BASIC_SQL::init()->createColumn($this->base, $this->columnProp($v));
					}
				}
			}
			if($fk == $math[2]){
				return BASIC_SQL::init()->createColumn($this->base, $this->columnProp($v));
			}
		}
	}
	/**
	 * Set component field
	 * 
	 * <code>
	 * 	; All fields options and not required.
	 *			$this->setField('my_field');
			
			---------------------------------------------------------------------------------------------
			text 			= (the fields id) 	/ the fields label. By default the vlaue is the field id.
			---------------------------------------------------------------------------------------------
				$this->setField('my_field', array(
					'text' => BASIC_LANGUAGE::init()->get('my_filed_label')
				));	
			
			---------------------------------------------------------------------------------------------
			formtype		= (text) 			/ the id of registrate control in BASIC_GENERATOR. For more information see the section "default-controls".
			---------------------------------------------------------------------------------------------
				$this->setField('my_field', array(
					'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'formtype' 	=> 'password'
			 	));
				
			---------------------------------------------------------------------------------------------
			dbtype			= (varchar) 		/ the database columns type.
			---------------------------------------------------------------------------------------------
				$this->setField('my_field', array(
					'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'dbtype' 	=> 'int'
			 	));
			 	
			---------------------------------------------------------------------------------------------	
			length			= (255) 			/ the max length in database column and in the HTML control.
			---------------------------------------------------------------------------------------------
				$this->setField('my_check_box', array(
					'text' 		=> BASIC_LANGUAGE::init()->get('my_check_box'),
					'formtype'	=> 'checkbox',
					'dbtype' 	=> 'int',
					'lenght'	=> 1
			 	));
			 	
			---------------------------------------------------------------------------------------------
			perm			= (empty string) 	/ symbol for the form UI and flag for the database show permition mode for this field.
			---------------------------------------------------------------------------------------------
				$this->setField('my_field', array(
					'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'formtype' 	=> 'password',
					'perm'		=> '*'
			 	));
			 
			---------------------------------------------------------------------------------------------	
			default 		= (empty string) 	/ default value for the form UI when is open for "insert" (ActionFormAdd).
			---------------------------------------------------------------------------------------------
				$this->setField('my_field', array(
					'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'default'	=> 'Fill you name please.'
			 	));
			
			---------------------------------------------------------------------------------------------
			lingual     	= (false) 			/ start multylingual support for this field.
			---------------------------------------------------------------------------------------------
				$this->setField('my_field', array(
					'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'lingual'	=> true
			 	));
			
			---------------------------------------------------------------------------------------------
			filter 			= (empty string) 	/ sql filter criteria for the filter in the list UI.
			---------------------------------------------------------------------------------------------
				$this->setField('my_field', array(
					'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'filter'	=> ' "nickname" LIKE "%{V}" '
			 	));
			 	
			---------------------------------------------------------------------------------------------
			filterFunction 	= (null) 			/ function or method for generate sql filter criteria for the filter in the list UI.
			---------------------------------------------------------------------------------------------
				class MyTools{
					/ **
					 * Handler for field's option "filterFunction".
					 * 
					 * @paranm String $filter_value - value for the sql criteria
					 * @paranm String $filed_name	- fields(option owner) name 
					 * /
					function mySqlBuilder($filter_value, $filed_name){
						if($field_name == 'my_field'){
							$currency = 'EU';
							if($filter_value == 1){
								$currency = 'US';
							}else if($filter_value == 2){
								$currency = 'BG';
							}
							return " AND `currency` = '".$currency."' "
						}
						return "";
					}
			 	}
			 	
			 	...	
			 		
			 	$this->setField('my_field', array(
					'text' 				=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'filterFunction'	=> array(new MyTools(), 'mySqlBuilder')
				));
			 	
			 	----------------------------------------
			 	
			 	function mySqlBuilderFunction($filter_value, $filed_name){
			 		$tools = new MyTools();
			 		
			 		return $tools->mySqlBuilder($filter_value, $filed_name);
			 	}
			 	
			 	...
			 	
			  	$this->setField('my_field', array(
					'text' 				=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'filterFunction'	=> 'mySqlBuilderFunction'
				));	
			
			---------------------------------------------------------------------------------------------
			attributes		= (empty array) 	/ mix of options (standart HTML tags options, specific for the control options)
			--------------------------------------------------------------------------------------------- 
			
			  	$this->setField('my_field', array(
					'text' 				=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'formtype'			=> 'moveselect',
					'attributes'		=> array(
						'data' => array(
							'en' => 'English',
							'bg' => 'Bulgarian',
							'fr' => 'France'
						),
						'id' => 'this_html_id',
						'class' => 'my_language_list'
					)
				));
			
			---------------------------------------------------------------------------------------------
			cleaners		= (empty array)		/ local/specific for filed fleaners
			---------------------------------------------------------------------------------------------
			
				====================================
							valid values
				====================================
				array('function for input and output')
				array('function for input', 'function for output')
				array('function for input', '#') - if this case will clean only input. For output will miss cleaner
				
				array(array(object, 'method for input and output'))
				array(
					array(object, 'method for input'), 
					array(object, 'method for input')
				)
				array(array(object, 'method for input'), '#')
			
				------------------ Examples ------------------
				
				/ **
				 * Handlers for field's option "cleaners" or global cleaners.
				 * 
				 * @paranm String $filter_value - value for the sql criteria
				 * @paranm String $filed_name	- fields(option owner) name 
				 * /	
				class MyTools{
					function cleanerForInput($value_for_cleaning){
						$value_for_cleaning = strip_tags($value_for_cleaning);
						$value_for_cleaning = addslashes($value_for_cleaning);
					
						return $value_for_cleaning;
					}
					function cleanerForOutput($value_for_cleaning){
						$value_for_cleaning = stripslashes($value_for_cleaning);
						
						return $value_for_cleaning;
					}
					function oneCleanerForOutputAndInput($value){
						return (int)$value
					}
			 	}
			 	
			 	...	
			 		
			 	$this->setField('my_field', array(
					'text' 				=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'filterFunction'	=> array(
						array(new MyTools(), 'oneCleanerForOutputAndInput')
					)
				));
			 	
			 	----------------------------------------
			 	
			 	function myCleanerForInputFunction($value_for_cleaning){
			 		$tools = new MyTools();
			 		
			 		return $tools->cleanerForInput($value_for_cleaning);
			 	}
			 	function myCleanerForOutoutFunction($value_for_cleaning){
			 		$tools = new MyTools();
			 		
			 		return $tools->cleanerForOutput($value_for_cleaning);
			 	}
			 	
			 	...
			 	
				$this->setField('my_field', array(
					'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
					'cleaners'	=> array('myCleanerForInputFunction', 'myCleanerForOutoutFunction')
				));	
			
			---------------------------------------------------------------------------------------------
			messages		= (empty array)		/ list with error and information mesages
			---------------------------------------------------------------------------------------------
				
				By default the system support two messages codes:
					0 = [empty string] 		/ if is not empty string will use this if not have message (error)
					1 = 'Mandatory Field!' 	/ will use from default validator when have value for the field option "perm" and the variable from request is empty.
					
					-----------------------------------------------------------------------
					For more auto return messages codes see cms/basic/upload.mod.php/test()
					-----------------------------------------------------------------------
				
				-------------------------------------------------------------
				
				function myValidatorMethod(){
					if(!BASIC::init()->validEmail($this->getDataBuffer('my_email_field'))){
						$this->setMessage('my_email_field', 2);
					}else if(!$this->read(" AND `my_email_field` = '".$this->getDataBuffer('my_email_field')."' ")->read()){
						$this->setMessage('my_email_field', 3);
					}else if(preg_match("/\.net$/")){
						$this->setMessage('my_email_field', 4);
					}
				}
				
				function main(){		
					$this->specialTest = 'myValidatorMethod';
					
				  	$this->setField('my_email_field', array(
						'text' 		=> BASIC_LANGUAGE::init()->get('my_filed_label'),
						'messages'	=> array(
							1 => basic_LANGUAGE::init()->get('missing_email'),
							2 => BASIC_LANGUAGE::init()->get('invalid_email'),
							3 => BASIC_LANGUAGE::init()->get('exist_email'),
							4 => BASIC_LANGUAGE::init()->get('insupported_email')
						)
					));
				}
				
			---------------------------------------------------------------------------------------------
			[your specific option]		= (*)		/ you can set your specific options with different from standart options names that you can get it later with method "getFirld".
			---------------------------------------------------------------------------------------------				
	 * </code>
	 * 
	 * @access public
	 * @param string $name
	 * @param array [$context]
	 * @param string $after
	 * @return void
	 */
	function setField($name, $context = array(), $after = ''){
		if(!isset($context['default'])) $context['default'] = '';
		if(!isset($context['lingual'])) $context['lingual'] = false;
		
		if($context['lingual'] == 'true' || $context['lingual'] == true){
			$this->nasional[$name] = 1;			
		}else if($context['lingual'] == 'false' || $context['lingual'] == false){
			unset($this->nasional[$name]);			
		}
		
		if(!isset($context['messages'])) $context['messages'] = array();
		
		if(!isset($context['messages'][0])) $context['messages'][0] = '';
		if(!isset($context['messages'][1])) $context['messages'][1] = 'Mandatory Field!';
		
		parent::setField($name, $context, $after);
		
		if(isset($this->fieldsForeign[$name])){
			$this->fields[$name]['lingual'] = false;
			unset($this->nasional[$name]);
		}
	}
	/**
	 * Remove field  $name from fileds container
	 * 
	 * @access public
	 * @param string $name
	 * @return void
	 * @see BaseDisplayComponentClass::unsetField()
	 */
	function unsetField($name){
		if(isset($this->fields[$name])){
			unset($this->nasional[$name]);
		}
		if(isset($this->fieldsForeign[$name])){
			unset($this->fieldsForeign[$name]);	
		}
		parent::unsetField($name);
	}
	/**
	 * Get uploaded file from request. Check for errors. Support multylanguage file upload.
	 * Inside use from method "test".
	 * 
	 * @access private
	 * @param field $v
	 * @param string [$lang]
	 * @return void
	 */
	protected function test_file($v, $lang = ''){
		BASIC::init()->imported('upload.mod');

		$_lang = '';
		if($lang) $_lang = "_".$lang;		
	
		$this->fieldsFile[$v[0].$_lang] = new BasicUpload($this->prefix.$v[0].$_lang);

//		if($lang && $lang == $GLOBALS['BASIC_LANG']->current()){
//			$this->setDataBuffer($v[0], $this->fieldsFile[$v[0].$_lang]);
//		}
		
		$att = BASIC_GENERATOR::init()->convertStringAtt($v[6]);
		if(isset($att['folders']) && $att['folders'] == 'true'){
			if(isset($att['dir'])){
				$path = BASIC_URL::init()->request($this->prefix.$v[0].$_lang."_path",
					$this->cleanerDecision($v[3], true, $v[7])
				);
				$multi = explode(";", $att['dir']);
				$test_path = true;
				foreach($multi as $f){
					if(preg_match("#".$path."#", $f)){
						$this->fieldsFile[$v[0].$_lang]->upDir = $path;
						$test_path = false;
						break;
					}
				}
			}
		}else{
			isset($att['dir']) ? $this->fieldsFile[$v[0].$_lang]->upDir = $att['dir'] : '';
		}
		if(isset($att['rand'])) $this->fieldsFile[$v[0].$_lang]->rand = $att['rand'];
		if(isset($att['max']))  $this->fieldsFile[$v[0].$_lang]->maxSize = $att['max'];
		if(isset($att['as']))   $this->fieldsFile[$v[0].$_lang]->AsFile = $att['as'];
		if(isset($att['perm'])) $this->fieldsFile[$v[0].$_lang]->setType(explode(",", $att['perm']));
		// Add Events
		if(isset($att['onComplete'])) $this->fieldsFile[$v[0].$_lang]->onComplete = $att['onComplete'];
		if(isset($att['onError'])) $this->fieldsFile[$v[0].$_lang]->onError = $att['onError'];
		if(isset($att['onDelete'])) $this->fieldsFile[$v[0].$_lang]->onDelete = $att['onDelete'];
		
		$this->fieldsFile[$v[0].$_lang]->test();
		if($ferr = $this->fieldsFile[$v[0].$_lang]->test()){
			if($ferr == 4 || $ferr == 5){
				if($v[1]){
					$use_err = true;
					if($this->id){
						$res = BASIC_SQL::init()->read_exec(" SELECT `".$v[0].$_lang."` FROM `".$this->base."` WHERE `".$this->field_id."` = ".$this->id." ", true);
						if($res[$v[0].$_lang]){
							$use_err = false;
						}
					}
					if($use_err) $this->setMessage($v[0].$_lang, 1);
				}
			}else{
				$this->setMessage($v[0].$_lang, $ferr);
			}
		}
		$this->setDataBuffer($v[0].$_lang, $this->fieldsFile[$v[0].$_lang]);		
	}
	/**
	 * Test for empty binding fields and load system var array $dataBuffer
	 * Last update is moving on the spesial test in the end and if($this->fields[][0] == '') miss
	 * Effect: create array $this->dataBuffer
	 * 
	 * @access public
	 * @version 0.3 
	 * @since 01-04-2007
	 * @return boolen
	 */
	 function test(){
		if(!$this->autoTest) return false;

		foreach($this->fields as $v){
			if(($this->_ordering && $v[0] == 'order_id') || $v[5] == 'none'){
				continue;	
			}
			
			$v[2] = (int)$v[2];
			
			$ctrl = BASIC_GENERATOR::init()->getControl($v[5]);
			if($ctrl !== null && $ctrl->isFileUpload()){				
				if(isset($GLOBALS['BASIC_LANG']) && $GLOBALS['BASIC_LANG']->language && isset($this->nasional[$v[0]])){
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){
						$this->test_file($v, $k);
					}
				} else {
					$this->test_file($v);
				}
			}else{
				if(isset($GLOBALS['BASIC_LANG']) && $GLOBALS['BASIC_LANG']->language && isset($this->nasional[$v[0]])){
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){
						if($ctrl !== null && $ctrl->isMultiple() && !BASIC_URL::init()->test($this->prefix.$v[0]."_".$k)){
							BASIC_URL::init()->set($this->prefix.$v[0]."_".$k, array());
						}						
						$var_url = BASIC_URL::init()->request($this->prefix.$v[0]."_".$k, $this->cleanerDecision($v[3],true,$v[7]));
				
//						if($k == $GLOBALS['BASIC_LANG']->current()){
//							$this->setDataBuffer($v[0],$var_url);
//						}
						$this->setDataBuffer($v[0]."_".$k, $var_url);
						if($v[1] && (
							(string)$this->dataBuffer[$v[0]."_".$k] == '' || (is_array($var_url) && !$var_url)
						)){
							$this->setMessage($v[0],1);
						}
					}
				}else{
					if($ctrl !== null && $ctrl->isMultiple() && !BASIC_URL::init()->test($this->prefix.$v[0])){
						BASIC_URL::init()->set($this->prefix.$v[0], array());
					}					
					$var_url = BASIC_URL::init()->request($this->prefix.$v[0], $this->cleanerDecision($v[3],true,$v[7]));
					
					$this->setDataBuffer($v[0], $var_url);
					if($v[1] && (
						(string)$this->dataBuffer[$v[0]] == '' || (is_array($var_url) && !$var_url)
					)){
						$this->setMessage($v[0],1);
					}
				}
			}
		}
		if($this->specialTest != ''){
			if(is_array($this->specialTest)){
				$obj = $this->specialTest[0];
				$method = $this->specialTest[1];
				$err = false;
				if($obj != null){
					$err = $obj->$method($this);
				}else{
					$err = $method($this);
				}
			}else{
				$special = $this->specialTest;
				$err = $this->$special();
			}
			if($err && !$this->messages){
			    $this->messages = array(-1);
			}
		}
		return ($this->messages ? true : false);
	}
	/**
	 * Call commponent actions. Support valid action, form type action (3). If called action is forbidden or not exist
	 * append to BASIC_ERROR service message and change errorAction value to 'list'. 
	 *
	 * @access public
	 * @param string $action
	 * @param array|int [$id]
	 * @param boolean [$useTest]
	 * @return mix
	 */
	function action($action, $id = null, $useTest = true){
		$tmp = '';
		try{
			if(isset($this->actions[$action])){
				
				if($this->actions[$action][1] >= 0){
					$caller = $this->actions[$action][0];
					
					//@FIX need think for cancel action and test!!!
					if($action != 'cancel' && $useTest && $this->actions[$action][1] == 3){
						if(!$this->test()){
							$tmp = $this->$caller($id, $action);
						}else{
							$tmp = false;
						}
					}else{
						$tmp = $this->$caller($id, $action);
					}
				}else{
					throw new Exception("Action '".$action."' is forbidden. ");	
				}
			}else{
				throw new Exception("Action '".$action."' is not supported. ");	
			}
		}catch (Exception $e){
			BASIC_ERROR::init()->setError($e->getMessage());	
			$this->errorAction = 'list';
			$tmp = '';
		}
		return $tmp;
	}
	/**
	 * Check for existing action in current componenet.
	 * 
	 * @access public
	 * @param integer $n
	 * @return boolean
	 */
	function checkForActions($n){
		$action = false;
		foreach ($this->actions as $v){
			if($v[1] == $n || $v[1] == ($n*(-1))) $action = true;
		}
		return $action;
	}
	/**
	 * Add new action. 
	 *	The param $activate is flag for button action's location. The type locations are: 
	 *		1 - action manager in list interface
	 *		2 - row action manager in list interface
	 *		3 - buttons bar in form interface
	 *
	 *	The param $rule is javascript rules. The type rules are:
	 *		javascript:(javascript code) - any javascript code
	 *		message:(text) - open alert dialog with content (text)
	 *		confirm:(text) - open confirm dialog with content text
	 * 
	 * @access public
	 * @param string $action
	 * @param string $method
	 * @param string [$text]
	 * @param integer [$activate] 
	 * @param string [$rule]
	 * @return void
	 */
	function addAction($action, $method, $text = '', $activate = 1, $rule = ''){
		$this->actions[$action] = array($method, $activate, $text, $rule);
	}
	/**
	 * Edit existing action
	 *
	 * @access public
	 * @param string $action
	 * @param string [$method]
	 * @param string [$text]
	 * @param integer [$activate]
	 * @param integer [$activate]
	 * @return void
	 */
	function updateAction($action, $method = null, $text = null, $activate = 0, $rule = ''){
		if(isset($this->actions[$action])){
			$this->actions[$action] = array(
				($method != null ? $method : $this->actions[$action][0]),
				($activate != null ? $activate : $this->actions[$action][1]),
				($text != null ? $text : (isset($this->actions[$action][2]) ? $this->actions[$action][2] : '') ),
				($rule != null ? $rule : (isset($this->actions[$action][3]) ? $this->actions[$action][3] : '') )
			);
		}
	}
	/**
	 * Delete existing action
	 *
	 * @access public
	 * @param string $action
	 * @return void
	 */
	function delAction($action){
		if($action != 'list' && isset($this->actions[$action])){
			unset($this->actions[$action]);
		}
	}
	/**
	 * Delete all action without 'list'
	 * 
	 * @access public
	 * @return void
	 */
	function delAllActions(){
		foreach ($this->actions as $k => $v){
			if($k != 'list') $this->delAction($k);
		}
	}	
	/**
	 * Get action list
	 * 
	 * @access public
	 * @return hashmap
	 */
	function getActions(){
		return $this->actions;
	}
	/**
	 * Get action name from url and set it in system container
	 * 
	 * @access public
	 * @return void
	 */
	function loadURLActions(){
		if(!$this->id){
			if($this->id = BASIC_URL::init()->request($this->prefix.'id', 'Int')){
				$this->system[] = $this->prefix.'id';
				if(is_array($this->id) && count($this->id) == 1){
					$this->id = $this->id[0];
					BASIC_URL::init()->set($this->prefix.'id', $this->id);
				}
			}
		}
		foreach($this->actions as $k => $v){
			if(BASIC_URL::init()->request($this->prefix.$this->urlCmdName.$k)){
				$this->cmd = $k; 
				$this->system[] = $this->miss[] = $this->prefix.$this->urlCmdName.$k;
				break;
			}
		}
		if(!$this->cmd){
			if($this->cmd = BASIC_URL::init()->request($this->prefix.$this->urlCmdName)){
				$this->system[] = $this->miss[] = $this->prefix.$this->urlCmdName;
			}
		}
	}
	/**
	 * If it's set action - run it
	 * 
	 * @access public
	 * @return mix
	 */
	function listenerActions(){
		if($this->cmd){
			if($tmp = $this->action($this->cmd, $this->id)){
				return $tmp;
			}
		}
		return '';
	}
	/**
	 * Return HTML with empty form
	 * 
	 * @access public
	 * @return string
	 */
	function ActionFormAdd(){
		return $this->FORM_MANAGER();
	}
	/**
	 * Return HTML with fill data for row with $id
	 * 
	 * @access public
	 * @param integer $id
	 * @return string
	 */
	function ActionFormEdit($id = 0){
		if($id && !$this->messages){
			$this->ActionLoad($id);
		}
		return $this->FORM_MANAGER();
	}
	/**
	 * Create fimple HTML with the info from specific components record.
	 *  
	 * @access public
	 * @param integer $id
	 * @return string
	 */
	function ActionDetails($id){
		$this->delAction('save');
		
		BASIC_TEMPLATE2::init()->set(array(
			'fields' => $this->getRecord($id),
			'buttons_bar' => $this->buttonActionsBar(3)
		), $this->template_details);
		
		$tpl = ''; try{
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_details);
		}catch(Exception $e){
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_details_default, $this->template_details);
		}
		
		return $this->formHtmlGenerator(array(
			'action' => BASIC_URL::init()->link(BASIC::init()->scriptName()),
			'method' => 'post',
			'name' => $this->prefix.'_'.get_class($this).'_details'
		), $tpl);
	}
	/**
	 * Insert or Update record from component
	 * 
	 * @access public
	 * @param integer $id
	 * @return integer|boolean
	 */
	function ActionSave($id = 0){
		if($id){
			return $this->ActionEdit($id);
		}else{
			return $this->ActionAdd();
		}
	}
	/**
	 * Add new record in component db table
	 * 
	 * @access public
	 * @return boolean
	 * @see BaseDisplayComponentClass::ActionAdd()
	 */
	function ActionAdd(){
		if($this->_ordering){
			$rdr = BASIC_SQL::init()->read_exec(" SELECT MAX(`order_id`)+1 AS `max` FROM `".$this->base."` "); 
			$rdr->read();
			$this->setDataBuffer("order_id", (int)$rdr->field('max'));
		}
		return parent::ActionAdd();
	}
	/**
	 * Load data row  from form in data buffer
	 * WARNING : this method is wanting optimization ...
	 *
	 * @access private
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
    	if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG'])){
    		foreach($this->nasional as $n => $v){
    
    			if(!isset($this->fields[$n])) continue;
    
    			foreach(BASIC_LANGUAGE::init()->language as $k => $l){
    				if(isset($row[$n.'_'.$k])){
	    				$this->setDataBuffer($n.'_'.$k, BASIC_URL::init()->other($row[$n.'_'.$k], null,
	    					$this->cleanerDecision($this->fields[$n][3],false,$this->fields[$n][7])
	    				));
    				}else{
    					$this->setDataBuffer($n.'_'.$k, '');
    				}
    				if($k == BASIC_LANGUAGE::init()->current()){
    					$this->setDataBuffer($n, $this->getDataBuffer($n.'_'.$k));
    				}
    			}
    		}
    	}
	}
	/**
	 * Call action error
	 * 
	 * @access public
	 * @param integer $id
	 * @return mix
	 */
	function ActionError($id){
	    foreach ($this->dataBuffer as $k => $v){
			$fname = $k;
	   		if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG'])){
	   			$tmp = '';
	   			foreach(BASIC_LANGUAGE::init()->language as $lk => $l){
	   				$tmp = str_replace("_".$lk, '', $k);
	   				if(isset($this->nasional[$tmp])){
	   					$fname = $tmp; break;
	   				}
	   			}
	   		}
	   		
        	if($v instanceof BasicUpload){
        		if($id && isset($this->fields[$fname])){
        			$res = BASIC_SQL::init()->read_exec(" SELECT `".$k."` FROM `".$this->base."` WHERE `".$this->field_id."` = ".$id." ", true);
        			
        			$this->dataBuffer[$k] = BASIC_URL::init()->other($res[$k], null,
	    	           $this->cleanerDecision($this->fields[$fname][3], false, $this->fields[$fname][7])
	    	        );
        		}else{
        			$this->dataBuffer[$k] = '';
        		}
        	}else{
        		if(isset($this->fields[$fname])){
					$this->dataBuffer[$k] = BASIC_URL::init()->other($v, null,
	    	           $this->cleanerDecision($this->fields[$fname][3], false, $this->fields[$fname][7])
	    	        );
        		}else{
        			$this->dataBuffer[$k] = $v;
        		}
        	}  
	    }
	    return $this->action($this->errorAction, $id, false);
	}
	/**
	 * Extra method for change boolen field
	 * Syntax action (Un)(Action)
	 * (Un) is key for off state
	 * strtolower(Action) is name changed field
	 *
	 * @access public
	 * @param integer $id
	 * @param string $action
	 * @version 0.3
	 * @return void
	 */
	function ActionBoolen($id, $action){
		$key = 1;
		preg_match("/^(Un)?(.+)$/", $action, $reg);

		if($reg[1]) $key = 0;

		if(!$id){
			$id = (int)BASIC_URL::init()->request($this->prefix.'id');
		}else{
			if(!is_array($id)) $id = array($id);
		}
		BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `".strtolower($reg[2])."` = ".$key." WHERE `".$this->field_id."` IN ".($id?"(".implode(",",$id).")":"(0)")." ");
	}
	/**
	 * Change order rows records
	 * WARNING :: This functionality no work on MsSql serrver !!!
	 *
	 * @access public
	 * @param integer $id
	 * @param string $action
	 * @return void
	 * @version 0.3 beta
	 */
	function ActionOrder($id,$action){
		if(!$id) return;
		
		BASIC_SQL::init()->exec(" SET @order_id = 0 , @new_id = 0 ,@new_order = 0 , @mx = 0 , @cnt = 0 , @id_num = ".$id."; ");
		$rdr = BASIC_SQL::init()->read_exec("
				SELECT @order_id:=c.`order_id` AS `ord`,
				       @mx:= max(d.`order_id`) AS `max`,
				       @cnt:= count(d.id)      AS `rows`
				FROM `".$this->base."` d LEFT JOIN `".$this->base."` c ON c.`".$this->field_id."` = @id_num
			    GROUP BY c.`order_id`;
		"); 
		$rdr->read();
		
		$err = BASIC_ERROR::init()->error();
		if($err['code'] == 1054){
			BASIC_SQL::init()->exec("ALTER TABLE `".$this->base."` ADD COLUMN `order_id` int(11) NOT NULL DEFAULT 0 ");
			BASIC_ERROR::init()->clean();
			$this->ActionOrder($id, $action);
			return ;
		}
		$flag = 1;
		if($action == 'order_up') $flag = -1;
		if($rdr->field('ord') > -1 && ($rdr->field('ord') < $rdr->field('max') || $flag < 0)){
			BASIC_SQL::init()->exec(" SET @i = @order_id; ");
			BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET order_id = @i:= @i + 1 WHERE `order_id` = @order_id AND `".$this->field_id."` != @id_num; ");
			if($flag > 0){
				BASIC_SQL::init()->exec(" SELECT @new_order:=order_id,@new_id:=`".$this->field_id."` FROM `".$this->base."`  WHERE `order_id` > @order_id ORDER BY `order_id` LIMIT 1; ");
			}else{
				BASIC_SQL::init()->exec(" SELECT @new_order:=order_id,@new_id:=`".$this->field_id."` FROM `".$this->base."`  WHERE `order_id` < @order_id ORDER BY `order_id` DESC LIMIT 1; ");
			}
			BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `order_id` = @order_id WHERE `".$this->field_id."` = @new_id; ");
			BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `order_id` = @new_order WHERE `".$this->field_id."` = @id_num; ");
		}
	}
	/**
	 * @todo NEED REVIEW THIS ACTIONS. IN NEXT VERSION THIS ACTION WILL BE HIDE BY DEFAULT.
	 *
	 * @access public
	 * @param integer $id
	 * @param string $action
	 * @param boolean|string $is_not_url_column_name
	 * @return integer
	 */
	function ActionFileRemove($id, $action, $is_not_url_column_name = ""){
		if(!$is_not_url_column_name){
			$column_name = BASIC_URL::init()->request($this->prefix.'fname', 'addslashes', 255);
		}else{
			$column_name = $is_not_url_column_name;
		}
		
		if(isset($this->fields[$column_name]) && $this->fields[$column_name][1]) return false;
		
		$file_name = BASIC_SQL::init()->read_exec(" SELECT `".$column_name."` as `file_name`FROM `".$this->base."` WHERE 1=1 AND `".$this->field_id."` = ".(int)$id." ",true);
		
		/**
		 * Find real field name.
		 */
		$field_column = $column_name;
		if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG'])){
			foreach($GLOBALS['BASIC_LANG']->language as $k => $l){
				$field_column = str_replace("_".$k,'',$field_column);
			}
		}
		
		$file_settings = $this->getField($field_column);
		$file_settings['attributes'] = BASIC_GENERATOR::init()->convertStringAtt($file_settings['attributes']);
		
		BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET `".$column_name."` = '' WHERE 1=1 AND `".$this->field_id."` = ".(int)$id." ");
		BASIC::init()->imported('upload.mod');
		$fl = new BasicUpload(null);
		$fl->upDir = $file_settings['attributes']['dir'];
		if(isset($file_settings['attributes']['onDelete'])){
			$fl->onDelete = $file_settings['attributes']['onDelete'];
		}
		$fl->delete($file_name['file_name']);

		$this->system[] = $this->prefix.'fname';
		$this->system[] = $this->prefix.'oldcmd';
		
		if(!$is_not_url_column_name){
			$old_cmd = BASIC_URL::init()->request($this->prefix.'oldcmd','addslashes',255);
			BASIC_URL::init()->redirect(BASIC::init()->scriptName(), BASIC_URL::init()->serialize($this->system).
				($old_cmd ? $this->prefix.$this->urlCmdName.'='.$old_cmd.'&' : '').
				$this->prefix.'id='.$id
			);
		}
		return $id;
	}
	/**
	 * Return HTML for list view
	 * 
	 * @access public 
	 * @return string
	 */
	function ActionList(){
		return $this->LIST_MANAGER();
	}
	/**
	 * Created HTML form manager
	 *
	 * @access public
	 * @version 1.4 
	 * @since 27.02.2007
	 * @param string [$form_attribute]
	 * @return string
	 */
	function FORM_MANAGER($form_attribute = array()){
		BASIC::init()->imported('template.mod');

		$att = BASIC_GENERATOR::init()->convertStringAtt($form_attribute);
		
		foreach($this->fields as $v){
			if($v[5] != 'none' && $v[5] != 'hidden' && BASIC_GENERATOR::init()->getControl($v[5])->isFileUpload()){
				$att["enctype"] = "multipart/form-data";
				break;
			} 
		}
	
		if(!isset($att['action'])) $att['action'] = BASIC_URL::init()->link(BASIC::init()->scriptName());
		if(!isset($att['method'])) $att['method'] = 'post';
		if(!isset($att['name']) && $this->prefix){
			$att['name'] = $this->prefix;
		}
		
		BASIC_TEMPLATE2::init()->set($this->dynamicLingualFormSupport(), $this->template_form);	
		BASIC_TEMPLATE2::init()->set(array(
			$this->templates['form-dynamic'] => $this->buildForm(),
			$this->templates['form-vars']['buttons_bar'] => $this->buttonActionsBar(),
			$this->templates['form-vars']['prefix'] => $this->prefix
		), $this->template_form);
		
		$tpl = ''; try{
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_form);
		}catch(Exception $e){
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_form_default, $this->template_form);
		}
		return $this->formHtmlGenerator($att, $tpl);
	}
	/**
	 * Add the fields that will miss into cross UI interfaces requests.
	 * 
	 * @param string $name
	 * @param boolean [$miss] - if null (default value) will add in both.
	 */
	function buildSpecialRequestFields($name, $miss = null, $lang = ''){
		$_name = $name;
		if($lang){
			$_name .= "_".$lang;
		}
		
		if(isset($this->fields[$name])){
			if($ctrl = BASIC_GENERATOR::init()->getControl($this->fields[$name][5])){
				foreach($ctrl->fieldNames() as $v){
					if($miss === null){
						$this->miss[] = $this->system[] = $v.$this->prefix.$_name;
					}else if($miss){
						$this->miss[] = $v.$this->prefix.$_name;
					}else{
						$this->system[] = $v.$this->prefix.$_name;
					}
				}
			}
		}
		
		if($miss === null){
			$this->miss[] = $this->system[] = $this->prefix.$_name;
		}else if($miss){
			$this->miss[] = $this->prefix.$_name;
		}else{
			$this->system[] = $this->prefix.$_name;
		}
	}	
	/**
	 * Get array with fields needed for parsing form
	 * 
	 * @access public
	 * @return array
	 */
	function buildForm(){
		$fields = array();
		foreach($this->fields as $v){
			$tag = $v[5];

			$attribute = array();
			if(isset($v[6])) $attribute = $v[6];
			

			if($v[1] && !isset($attribute['lang'])) $attribute['lang'] = 'on';
 			
			$length = (int)$v[2];
			if($length && !isset($attribute['maxlength'])){
			   $attribute['maxlength'] = $length;
			}
			
			$tagPHP = '';
			
			if($tag == 'none'){
				continue;
			}else if($tag == 'hidden'){
				if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$v[0]])){
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){
						if($v['default'] && !$this->id && !isset($this->dataBuffer[$v[0]."_".$k])){
							$this->setDataBuffer($v[0]."_".$k, $v['default']);
						}
						$this->hidden_el .= BASIC_GENERATOR::init()->controle('input', $this->prefix.$v[0]."_".$k, $this->getDataBuffer($v[0]."_".$k), array('type' => 'hidden'));
						
						//$this->miss[] = $this->prefix.$v[0]."_".$k;
						$this->buildSpecialRequestFields($v[0], true, $k);
					}
				}else{
					if($v['default'] && !$this->id && !isset($this->dataBuffer[$v[0]])){
						$this->setDataBuffer($v[0], $v['default']);
					}					
					$this->hidden_el .= BASIC_GENERATOR::init()->controle('input', $this->prefix.$v[0], $this->getDataBuffer($v[0]), array('type' => 'hidden'));
					
					//$this->miss[] = $this->prefix.$v[0];
					$this->buildSpecialRequestFields($v[0], true);
				}
			}else{
				if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$v[0]])){
					if(BASIC_LANGUAGE::init()->number() > 1){
						if(isset($attribute['class'])){
							$attribute['class'] .= ' form_lingual_field';
			 			}else{
			 				$attribute['class'] = 'form_lingual_field';
			 			}
					}
					foreach(BASIC_LANGUAGE::init()->language as $k => $l){
						
						if($k != BASIC_LANGUAGE::init()->current()){
							if(isset($attribute['style']))
								$attribute['style'] .= 'display:none;';
							else
								$attribute['style'] = 'display:none;';								
						}else{
							if(isset($attribute['style']))
								$attribute['style'] .= 'display:block;';
							else
								$attribute['style'] = 'display:block;';		
						}
						$attribute['lang'] = $k;
						if(BASIC_GENERATOR::init()->getControl($tag)->isFileUpload()){
							$attribute = $this->_createFileUploadButton($v[0].'_'.$k, $attribute);
							
							if($this->getDataBuffer($v[0].'_'.$k) instanceof BasicUpload){
								$this->setDataBuffer($v[0].'_'.$k, '');
							}
						}
						if($v['default'] && !$this->id && !isset($this->dataBuffer[$v[0].'_'.$k])){
						    $this->setDataBuffer($v[0].'_'.$k, $v['default']);
						}
						$tagPHP .= BASIC_GENERATOR::init()->controle($tag, $this->prefix.$v[0].'_'.$k, $this->getDataBuffer($v[0].'_'.$k), $attribute);
						
						//$this->miss[] = $this->prefix.$v[0].'_'.$k;
						$this->buildSpecialRequestFields($v[0], true, $k);
					}
				}else{
					if(BASIC_GENERATOR::init()->getControl($tag)->isFileUpload()){
						$attribute = $this->_createFileUploadButton($v[0], $attribute);
						
						if($this->getDataBuffer($v[0]) instanceof BasicUpload){
							$this->setDataBuffer($v[0], '');
						}
					}	
					if($v['default'] && !$this->id && !isset($this->dataBuffer[$v[0]])){
						$this->setDataBuffer($v[0], $v['default']);
					}
					if($v[1]) unset($attribute['delete_btn']);
					
					$tagPHP .= BASIC_GENERATOR::init()->controle($tag, $this->prefix.$v[0], $this->getDataBuffer($v[0]), $attribute);
					
					//$this->miss[] = $this->prefix.$v[0];
					$this->buildSpecialRequestFields($v[0], true);
				}
			}
			if(!$tagPHP) continue;

		    $message = (isset($v['messages'][(int)$this->getMessage($v[0])]) ? $v['messages'][(int)$this->getMessage($v[0])] : $v['messages'][0]);
			
			$fields[$v[0]] = array(
				$this->templates['form-vars']['perm'] 	 => ($v[1] ? $v[1] : ""),
				$this->templates['form-vars']['label']	 => $v[4],
				$this->templates['form-vars']['ctrl']	 => $tagPHP,
				$this->templates['form-vars']['message'] => $message,
				$this->templates['form-vars']['value'] 	 => $this->getDataBuffer($v[0])
			);
		}
		return $fields;
	}
	/**
	 * Generate form HTML ussing attributes
	 * 
	 * @access public
	 * @param array $attributes
	 * @param string $body
	 * @return string
	 */
	function formHtmlGenerator($attributes, $body){
		return BASIC_GENERATOR::init()->form($attributes, 
			$body.			 "\n<!-- hidden elements -->\n".
			$this->hidden_el."\n<!-- form state -->\n".
			($this->useSaveState ? BASIC_URL::init()->serialize($this->miss, 'post') : '')
		);
	}
	/**
	 * Help method for buildForm
	 * 
	 * @access private
	 * @param string $name
	 * @param string $attribute
	 * @return array
	 */
	protected function _createFileUploadButton($name, $attribute){
		$attribute = BASIC_GENERATOR::init()->convertStringAtt($attribute);
		
		if(!$this->getDataBuffer($name)){
			unset($attribute['delete_btn']);	
		}else{
			if(isset($attribute['delete_btn'])){
				$delete_btn = BASIC_GENERATOR::init()->convertStringAtt($attribute['delete_btn']);
				$delete_btn['href'] = BASIC_URL::init()->link(BASIC::init()->scriptName(), BASIC_URL::init()->serialize($this->system).$this->prefix.$this->urlCmdName.'=fileRemove&'.$this->prefix.'fname='.$name.'&'.$this->prefix.'id='.$this->id.($this->cmd ? '&'.$this->prefix.'oldcmd='.$this->cmd : ''));
				if(isset($delete_btn['class'])){
					$delete_btn['class'] .= ' FileRemove';
				}else{
					$delete_btn['class'] = 'FileRemove';
				}
				if(!isset($delete_btn['id'])){
					$delete_btn['id'] = 'cmdFileRemove';
				}
				$attribute['delete_btn'] = $delete_btn;
			}
		}
		return $attribute;	
	}
    /**
     * Create system variables
     * 
     * @access public
     * @return void
     */
	function startManager(){
		$this->loadURLActions();
		
		foreach($this->fields as $k => $v){
			if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$v[0]])){
				foreach(BASIC_LANGUAGE::init()->language as $lk => $l) $this->system[] = $this->prefix.$k.'_'.$lk;
			}
			if(isset($v[0])) $this->system[] = $this->prefix.$k;
		}
		if($this->_ordering && !$this->sorting){
			BASIC::init()->imported('bars.mod');
			$this->sorting = new BasicSorting($this->base.'.order_id', false, $this->prefix);
		}
		if(!$this->paging){
			$this->paging = new BasicComponentPaging($this->prefix);
		}
	}
	/**
	 * Create element for to map declaration
	 * if($field == '' && $colback != '') call user function with param table id key
	 *
	 * proto type user function
	 * 		function protoTypeUser($id){
	 * 			# definitions ...
	 * 		}
	 * 
	 * @access public
	 * @return string
	 */
	function _map(){
		$tmp = '#'.microtime();
		if(isset($this->map[$tmp])){
			$tmp = $this->_map();
		}
		return $tmp;
	}
	/**
	 * Ordering control
	 * 
	 * @access public
	 * @param boolean [$status]
	 * @return boolen _ordering
	 */
	 function ordering($status = null){
		if($status === false){
			$this->_ordering = false;
			$this->unsetField('order_id');
			
			$this->delAction('order_up');
			$this->delAction('order_down');
		}elseif($status === true){
			$this->_ordering = true;
			$this->setField("order_id",array(
				'dbtype' => 'int',
				'length' => 11,
				'formtype' => 'hidden'
			));
			$this->addAction('order_up', 'ActionOrder', '', 0);
			$this->addAction('order_down', 'ActionOrder', '', 0);
		}
		return $this->_ordering;
	}
	function useOrdering(){
		return !!$this->_ordering;
	}	
	/**
	 * Describing and setting of list columns information
	 *
	 * @access public
	 * @param string $field	db column name, if begins with '#' do not serch it in database, it's generated from code 
	 * @param string $header column header text
	 * @param string|array $colback  method or function formating content in the list view column
	 * @param string|array $attribute setting for the column, like width
	 * @param boolen $sort if it's allowed column ordering
	 * @return void
	 */
	function map($field, $header, $colback='', $attribute='', $sort=true){

		if(!is_numeric($field) && ($field == '' || $field == '#')){
			$field = $this->_map();
		}else if($this->sorting && $sort){
			$header = $this->sorting->sortlink($field, $header);
		}
		$this->map[$field] = array($header, $colback, $attribute, true);
	}
	/**
	 * Remove column from list view table
	 * 
	 * @access public
	 * @param $string $name
	 * @return void
	 */
	function unmap($name){
		if(is_array($name)){
			foreach($name as $v){
				unset($this->map[$v]);
			}
		}else{
			unset($this->map[$name]);
		}
	}
	/**
	 * Add column different of exist components fields in the List UI. 
	 * 
	 * @access public
	 * @param string/array $name
	 * @return void
	 */
	function addMapElement($name){
		if(is_array($name)){
			foreach ($name as $v){
				$this->map[$v] = array(null,null,null,false);
			}		
		}else{
			$this->map[$name] = array(null,null,null,false);
		}
	}
	/**
	 * Check for action and run it, used in start panel
	 * 
	 * @access public
	 * @return string
	 */
	function createInterface(){
		if($this->cmd){
					
			// Support use component exeptions 
			try{
				$t = $this->listenerActions();
			}catch(Exception $e){
				BASIC_ERROR::init()->append($e->getCode(), $e->getMessage());
			}
			
			if($t && is_string($t)){
				return $t;
			}else{
				if(!$this->messages && !BASIC_ERROR::init()->exist(array('fatal', 'warning'))){
					$this->ActionBack();
				}else{
					if($this->errorAction){
						return $this->action('error', $this->id);
					}
				}
			}
		}
		if(isset($this->actions['list'])){
			return $this->action("list", $this->id);
		}else{
			throw new Exception("Action 'list' is requare to exist!");
		}
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
				}else if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && isset($this->nasional[$k])){
					$tmp .= " `".$this->base."`.`".$k."_".BASIC_LANGUAGE::init()->current()."` AS `".$k."` ";
					$criteria = preg_replace("/[` ]".$k."[` ]/", "`".$k."_".BASIC_LANGUAGE::init()->current()."`", $criteria);
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
		
//		$tmp = str_replace("\n",'',$tmp);
		
		if(($this->_ordering || $this->sorting) && !preg_match("/[\n\t\r ]+order by/i", $query)){
			if($this->_ordering && !$this->sorting){
				BASIC::init()->imported('bars.mod');
				$this->sorting = new BasicSorting($this->base.'.order_id', false, $this->prefix);
			}
			if(preg_match("/[\n\t\r ]+limit /i", $query)){
				$query = preg_replace("/[\t\n\r ]+limit /i", $this->sorting->getsql()." LIMIT ", $query);
			}else{
				$query .= $this->sorting->getsql();
			}
		}
		return $query;
	}
	/**
	 * Container for foreign fields
	 * @var array
	 */
	var $_FOREING_LIST_CONTAINER = array();
	/**
	 * Fill container for foreign fields
	 * 
	 * @todo the name has to be change from load_foreing_list_firld to load_foreign_list_field 
	 * @param unknown_type $name
	 * @param unknown_type $id
	 */
	function load_foreing_list_firld($name,$id){
		if(isset($this->fieldsForeign[$name])){
			if(!isset($_FOREING_LIST_CONTAINER[$id])){
				$_FOREING_LIST_CONTAINER[$id] = $this->fieldsForeign[$name]->load($id);
			}
			return $_FOREING_LIST_CONTAINER[$id];
		}
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
			if($tmp = $this->addColumn($err['message'])){
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
	 * Redirect page and send existing variables
	 * 
	 * @access public
	 * @return void
	 */
	function managerRefresh(){
		$this->ActionBack();
	}
	/**
	 * Redirect from view mode to list mode
	 * 
	 * @access public
	 * @return  void
	 * 
	 */
	function ActionBack(){
		BASIC_URL::init()->redirect(BASIC::init()->scriptName(), BASIC_URL::init()->serialize($this->system));
	}
	/**
	 * Create log url request
	 *
	 * @access public
	 * @param string [$method]
	 * @return string
	 */
	function managerSaveVar($method = 'get'){
		return $this->useSaveState ? BASIC_URL::init()->serialize($this->system, $method) : '';
	}
	/**
	 * Create Header row
	 *
	 * @access public
	 * @param boolen [$manager]
	 * @return string
	 */
	function cmpHeaders($manager = true){
		$columns = array(); $len = 0;
		
		if(!$this->map){
			foreach($this->fields as $k => $v){
				if($v[3] != 'none') $this->map($k, $v[4]);
			}
		}
		foreach ($this->map as $k => $v){
			if($v[3]){ 
				$columns[$k] = array(
					$this->templates['list-vars']['head-dynamic-label'] => $v[0],
					$this->templates['list-vars']['head-dynamic-attr'] => ($v[2] ? BASIC_GENERATOR::init()->convertAtrribute($v[2]) : null),
					$this->templates['list-vars']['head-dynamic-selected'] => ($this->sorting && $this->sorting->selected($k)),
					$this->templates['list-vars']['head-dynamic-isdown'] => ($this->sorting && $this->sorting->isDown()) 
				);
				$len++;
			}
		}
		BASIC_TEMPLATE2::init()->set(array(
			$this->templates['list-vars']['head-check'] => ($manager && $this->checkForActions(1)),
			$this->templates['list-vars']['head-order'] => ($manager && $this->checkForActions(2) && $this->_ordering == true && $this->sorting ? $this->sorting->createUrlForLink('order_id') : ''),
			$this->templates['list-vars']['head-dynamic'] => $columns,
			$this->templates['list-vars']['head-length'] => $len
		), $this->template_list);
	}
	/**
	 * Create row in list view
	 * 
	 * @access public
	 * @param array $array
	 * @param string/array $attribute
	 * @return string
	 */
	function cmpRows($array, $attribute = ''){
		
		$rows = array();
		$rl = 0;
		$class = '';
		$attribute = BASIC_GENERATOR::init()->convertStringAtt($attribute);
		
		$action_bar_settings = array();
		if(isset($attribute['action_bar'])){
			$action_bar_settings = $attribute['action_bar'];
			unset($attribute['action_bar']);
		}
		
		foreach($array as $_key_ => $val) {
			$row_level = (isset($val['__level']))? $val['__level'] : 0;
			$even_class = false;
			if($rl == 0){
				$even_class = true;
			}

			$columns = array();
			foreach($this->map as $k => $v){
				if(!$v[3]) continue;

				if(is_array($v[1])){
					$class = &$v[1][0];
					$method = $v[1][1];
				}else{
					$method = $v[1];
				}

				// foreing extension 
				if(isset($this->fieldsForeign[$k])){
				    $val[$k] = $this->load_foreing_list_firld($k,$val[$this->field_id]);
				}
				
				$column_body = '';
				if($k[0] == '#' && $v[1] != ''){ // create specifick field
					if(is_array($v[1])){
						$class = &$v[1][0];$method = $v[1][1];
						
						$column_body = ($class != null ? $class->$method(null,$k,$val) : $method(null,$k,$val));
					}else{
						$column_body = $this->$v[1](null,$k,$val);
					}
				}else if($k != '' && $v[1] != ''){ // formated information field
					if(is_array($v[1])){
						$class = &$v[1][0];$method = $v[1][1];

						$column_body = ($class != null ? $class->$method((isset($val[$k]) ? $val[$k] : ''), $k, $val) : $method((isset($val[$k]) ? $val[$k] : ''), $k, $val));
					}else{
						$column_body = $this->$v[1]((isset($val[$k]) ? $val[$k] : ''), $k, $val);
					}
				}else{
					$column_body = (isset($val[$k]) ? $val[$k] : '');
				}
				
				$columns[$k] = array(
					$this->templates['list-vars']['body-dynamic-columns-label'] => $column_body,
					$this->templates['list-vars']['body-dynamic-columns-attr'] => ($v[2] ? BASIC_GENERATOR::init()->convertAtrribute($v[2]) : '')
				);
			}
			
			// start permissions test
			$mark = true;
			$_action_bar_settings = $action_bar_settings;

			foreach ($this->actions as $a_key => $a_val){
				if($a_val[1] == -2){
					$_action_bar_settings['actions'][$a_key] = 'disable';	
				}
				if(
					($a_val[1] == 1 || $a_val[1] == -1) && 
					!($a_key[0] == '_' && $a_key[1] == '_') && 
					$a_key != 'cancel' && 
					$a_key != 'add'
				){
					$mark = false;
				}
			}
			$_action_bar_settings['mark']['disabled'] = $mark;

			$val['row_number'] = $_key_;
			$rows[] = array(
				$this->templates['list-vars']['body-dynamic-rowlevel'] => $row_level,
				$this->templates['list-vars']['body-dynamic-columns'] => $columns,
				$this->templates['list-vars']['body-dynamic-evenclass'] => $even_class,
				$this->templates['list-vars']['body-dynamic-actionbar'] => $this->rowActionsBar($val, $_action_bar_settings),
				$this->templates['list-vars']['body-dynamic-rownumber'] => $_key_,
				$this->templates['list-vars']['body-dynamic-id'] => $val['id']
			);
			if($rl == 0){
				$rl = 1;
			}else{
				$rl = 0;
			}
		}
		BASIC_TEMPLATE2::init()->set(array(
			$this->templates['list-vars']['body-dynamic'] => $rows
		), $this->template_list);
	}
	/**
	 * Return action bar and paging bar
	 *
	 * @access public
 	 * @return string
	 */
	function footerBar(){
		$pbar = ($this->paging ? $this->paging->getBar() : '');
		
		BASIC_TEMPLATE2::init()->set(array(
			$this->templates['list-vars']['action-bar'] => $this->footerActionsBar(),
			$this->templates['list-vars']['paging-bar'] => $pbar
		), $this->template_list);
	}
	/**
	 * Create listing manager. Generate html (parsing list template) using array with data from components db table
	 *
	 * @access public
	 * @param string [$criteria]
	 * @return string
	 */
	function LIST_MANAGER($criteria = ''){
		$arr = array();
		if($this->base){
			$_map = true; if(!$this->map) $_map = false;
			
			foreach($this->fields as $k => $v){
				if(isset($v['filter']) || isset($v['filterFunction'])){
					if(!$this->filter){
						$this->filter = new BasicFilter();
						$this->filter->prefix($this->prefix.'f');
						$this->filter->template($this->template_filter, $this->template_filter_default);
						if(isset($this->actions['filter'])){
							$this->filter->button($this->actions['filter'][2]);
						}
					}
					if(isset($v['filter']) && $v['filter'] == 'auto'){
						if($v[2] == 'int'){
							$tmp = $this->getField($k);
							$tmp['filter'] = " AND (`{1}` >= {V1} OR `{2}` <= {V2}) ";
							$this->filter->rangeField($k, $tmp);
						}else{
							$tmp = $this->getField($k);
							$tmp['filter'] = " AND `".$k."` LIKE '%{V}%' ";
							$this->filter->field($k, $tmp);		
						}
					}else{
						$this->filter->field($k,$v);
					}
				}
				if(!$_map && $v[3] != 'none') $this->map($k, $v[4]); 
			}
			
			if($this->filter){
				$this->filter->init();
				$criteria .= $this->filter->sql();
			}
			if($this->sorting) $criteria .= $this->sorting->getsql();
		
			$rdr = $this->read($criteria);
			if($this->maxrow != 0 && $rdr->num_rows() > $this->maxrow){
				
	        	if(!$this->paging){
					BASIC::init()->imported('bars.mod');
					$this->paging = new BasicComponentPaging($this->prefix);
	        	}
				$this->paging->init($rdr->num_rows(), $this->maxrow);
				
				$rdr = $this->read($criteria.$this->paging->getSql());
			}
			while($rdr->read()){
				$arr[$rdr->item('id')] = $rdr->getItems();
			}
		}
		return $this->compile($arr);
	}
	/**
	 * Generate html (parsing list template) using array with data from components db table. Used in LIST_MANAGER
	 * 
	 * @access public
	 * @param array $arr
	 * @return string
	 */
	public function compile($arr){
		$this->cmpHeaders();
		$this->cmpRows($arr);
		$this->footerBar();
		
		BASIC_TEMPLATE2::init()->set(array(
			$this->templates['list-vars']['prefix'] => $this->prefix,
			$this->templates['list-vars']['cmd'] => $this->prefix.'id',
		), $this->template_list);
		
		$tpl = ''; try{
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_list);
		}catch(Exception $e){
			$tpl = BASIC_TEMPLATE2::init()->parse($this->template_list_default, $this->template_list);
		}
		return ($this->filter ? $this->filter->form() : '').BASIC_GENERATOR::init()->form(array(
				'enctype' => 'multipart/form-data',
				'method' => 'post',
				'name' => $this->prefix,
				'action' => BASIC_URL::init()->link(BASIC::init()->scriptName())
			), 
			$tpl.
			"\n<!-- list state -->\n".
			$this->managerSaveVar('post')
		);
	}
	/**
	 * Create action buttons bars.
	 *
	 * @access public
	 * @return array
	 */
	function buttonActionsBar($type = 3){
		$arr = $this->buttonActionsBarOnly($type)+$this->dynamicLingualFormSupport();
		return $arr;
	}
	/**
	 * Add existing languages in the template for the language bar in the form UI (ActionFromAdd, ActionFormEdin).
	 * 
	 * @access public
	 * @return array
	 */
	function dynamicLingualFormSupport(){
		$tpl_vars = array();
		if($this->nasional){
			$tpl_vars[$this->templates['form-action-bar-vars']['lingual-current']] = $GLOBALS['BASIC_LANG']->current();
		}
		if(isset($GLOBALS['BASIC_LANG']) && is_object($GLOBALS['BASIC_LANG']) && count($this->nasional) > 0 && $this->useJSLang){
			if(BASIC_LANGUAGE::init()->number() > 1){
				$linguals = array(); while($lang = BASIC_LANGUAGE::init()->listing()){
					$linguals[] = array(
						$this->templates['form-action-bar-vars']['linguals-text'] => $lang['text'],
						$this->templates['form-action-bar-vars']['linguals-key']  => $lang['code'],
						$this->templates['form-action-bar-vars']['linguals-flag'] => $lang['flag']
					);
				}
				$tpl_vars[$this->templates['form-action-bar-vars']['linguals']] = $linguals;
			}
		}
		return $tpl_vars;
	}
	/**
	 * Get array with data for each action button
	 * 
	 * @access public
	 * @param array $type
	 * @return array
	 */
	function buttonActionsBarOnly($type){
		$actions = array();
		$act = false;
		
		foreach ($this->actions as $k => $v){
			if($v[1] == $type){
				
				$rule_type = '';
				$rule_text = '';
				
				if(isset($v[3]) && $v[3]){
					if(preg_match("/^javascript:(.+)$/", $v[3],$ex)){
						$rule_type = 'rule';
						$rule_text = $ex[1];
					}else if(preg_match("/^message:(.+)$/", $v[3],$ex)){
						$rule_type = 'message';
						$rule_text = $ex[1];
					}else{
						$rule_type = 'confirm';
						$rule_text = $v[3];
					}
				}
		
				$actions[] = array(
					$this->templates['form-action-bar-vars']['actions-key'] => $k,
					$this->templates['form-action-bar-vars']['actions-pkey'] => $this->prefix.$k,
					$this->templates['form-action-bar-vars']['actions-text'] => $v[2],
					$this->templates['form-action-bar-vars']['actions-disable'] => false,
					$this->templates['form-action-bar-vars']['actions-rule-type'] => $rule_type,
					$this->templates['form-action-bar-vars']['actions-rule-text'] => $rule_text
				);
				$this->miss[] = $this->prefix.$this->urlCmdName.$k;
			}
			if($v[1] == ($type*-1)){
				$actions[] = array(
					$this->templates['form-action-bar-vars']['actions-key'] => $k,
					$this->templates['form-action-bar-vars']['actions-key'] => $k,
					$this->templates['form-action-bar-vars']['actions-pkey'] => $this->prefix.$k,
					$this->templates['form-action-bar-vars']['actions-text'] => $v[2],
					$this->templates['form-action-bar-vars']['actions-disable'] => true,
					$this->templates['form-action-bar-vars']['actions-rule-type'] => '',
					$this->templates['form-action-bar-vars']['actions-rule-text'] => ''
				);
			}
			if($v[1] == $type || $v[1] == ($type*-1)) $act = true;
		}
		return array(
			$this->templates['form-action-bar-vars']['is-ie7'] => (!(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') === false)),
			$this->templates['form-action-bar-vars']['actions'] => $actions,
			$this->templates['form-action-bar-vars']['prefix']  => $this->prefix,
			$this->templates['form-action-bar-vars']['cmd']  => $this->prefix.$this->urlCmdName
		);
	}
	/**
	 * Create manager bar for listing manager
	 *	<code>
	 * 		<b> $settings signature</b>
	 * 		array(
	 * 			'mark' => array(
	 * 				// style settings
	 * 			),
	 * 			'ordering' => true, //false // view order bar
	 * 			'actions' => array(
	 * 				'add' => 'disable', //hide|enable
 	 * 			)
	 *  	)
	 * 	</code>
	 * 
	 * @access public
	 * @param integer $row
	 * @param array $settings
	 * @return void
	 */
	function rowActionsBar($row, $settings = array()){
		
		if(!isset($settings['mark'])) 	  $settings['mark'] = array();
		if(!isset($settings['ordering'])) $settings['ordering'] = true;
		if(!isset($settings['actions']))  $settings['actions'] = array();
		
		$id = $row[$this->field_id];
		$level = (isset($row['__level']) ? $row['__level'] : 0);
		$actions = array();
		
		$act = false;
		
		foreach ($this->actions as $k => $v){
			$rule_type = '';
			$rule_text = '';
			
			if(isset($settings['actions'][$k])){
				if($settings['actions'][$k] == 'hide') continue;
				if($settings['actions'][$k] == 'disable'){
					$v[1] = ($v[1]*-1);	
				}else if($settings['actions'][$k] == 'enable'){
					if($v[1] < 0) $v[1] = ($v[1]*-1);	
				}
			}
			if($v[1] == 2){
				if(isset($v[3]) && $v[3]){
					if(preg_match("/^javascript:(.+)$/",$v[3],$ex)){
						$rule_type = 'rule';
						$rule_text = $ex[1];
					}else if(preg_match("/^message:(.+)$/",$v[3],$ex)){
						$rule_type = 'message';	
						$rule_text = $ex[1];	
					}else{
						$rule_type = 'confirm';
						$rule_text = $v[3];
					}
				}
				$actions[] = array(
					$this->templates['row-action-bar-vars']['actions-key'] => $k,
					$this->templates['row-action-bar-vars']['actions-pkey'] => $this->prefix.$k,
					$this->templates['row-action-bar-vars']['actions-text'] => $v[2],
					$this->templates['row-action-bar-vars']['actions-link'] => $this->createActionLink($k, $id),
					$this->templates['row-action-bar-vars']['actions-disable'] => false,
					$this->templates['row-action-bar-vars']['actions-rule-type'] => $rule_type,
					$this->templates['row-action-bar-vars']['actions-rule-text'] => $rule_text
				);
			}
			if($v[1] == -2){
				$actions[] = array(
					$this->templates['row-action-bar-vars']['actions-key'] => $k,
					$this->templates['row-action-bar-vars']['actions-text'] => $v[2],
					$this->templates['row-action-bar-vars']['actions-link'] => $this->createActionLink($k, $id),
					$this->templates['row-action-bar-vars']['actions-disable'] => true,
					$this->templates['row-action-bar-vars']['actions-rule-type'] => $rule_type,
					$this->templates['row-action-bar-vars']['actions-rule-text'] => $rule_text
				);
			}
			if($v[1] == 1 || $v[1] == -1) $act = true;
		}
		
		$order_bar = array();
		if($this->_ordering && $settings['ordering']){
			$order_bar = array(
				array(
					$this->templates['row-action-bar-vars']['orderbar-key'] => 'order_up',
					$this->templates['row-action-bar-vars']['orderbar-link'] => $this->createActionLink('order_up', $id)
				),
				array(
					$this->templates['row-action-bar-vars']['orderbar-key'] => 'order_down',
					$this->templates['row-action-bar-vars']['orderbar-link'] => $this->createActionLink('order_down', $id)
				)
			);
		}
	
		return array(
			$this->templates['row-action-bar-vars']['is-ie7'] => (!(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') === false)),
			$this->templates['row-action-bar-vars']['level'] => $level,
			$this->templates['row-action-bar-vars']['rownumber'] => (isset($row['row_number']) ? $row['row_number'] : 0),
			$this->templates['row-action-bar-vars']['id'] => str_replace('-','_',$id),
			$this->templates['row-action-bar-vars']['actions'] => $actions,
			$this->templates['row-action-bar-vars']['orderbar'] => $order_bar,
			$this->templates['row-action-bar-vars']['function'] => $act ? " ".BASIC_GENERATOR::init()->convertAtrribute($settings['mark']) : '',
			$this->templates['row-action-bar-vars']['prefix'] => $this->prefix,
			$this->templates['row-action-bar-vars']['idcmd'] => $this->prefix.'id'
		);
	}
	/**
	 * Create action bar for listing manager
	 * 
	 * @access public
	 * @return string
	 */
	function footerActionsBar(){
		$key = false;
		$actions = array();
			
		foreach ($this->actions as $k => $v){
			$rule_type = '';
			$rule_text = '';
			
			if($v[1] == 1){
				$key = true;
				if(isset($v[3]) && $v[3]){
					if(preg_match("/^javascript:(.+)$/",$v[3],$ex)){
						$rule_type = 'rule';
						$rule_text = $ex[1];
					}else if(preg_match("/^message:(.+)$/",$v[3],$ex)){
						$rule_type = 'message';
						$rule_text = $ex[1];
					}else{
						$rule_type = 'confirm';
						$rule_text = $v[3];
					}
				}
				$actions[] = array(
					$this->templates['action-bar-vars']['actions-key'] => $k,
					$this->templates['action-bar-vars']['actions-pkey'] => $this->prefix.$k,
					$this->templates['action-bar-vars']['actions-text'] => $v[2],							
					$this->templates['action-bar-vars']['actions-link'] => $this->createActionLink($k),							
					$this->templates['action-bar-vars']['actions-disable'] => false,
					$this->templates['action-bar-vars']['actions-rule-type'] => $rule_type,
					$this->templates['action-bar-vars']['actions-rule-text'] => $rule_text
				);
			}
			if($v[1] == -1){
				$key = true;
				$actions[] = array(
					$this->templates['action-bar-vars']['actions-key'] => '%'.$k,
					$this->templates['action-bar-vars']['actions-text'] => $v[2],
					$this->templates['action-bar-vars']['actions-link'] => $this->createActionLink($k),						
					$this->templates['action-bar-vars']['actions-disable'] => true,
					$this->templates['action-bar-vars']['actions-rule-type'] => $rule_type,
					$this->templates['action-bar-vars']['actions-rule-text'] => $rule_text
				);
			}
		}
		if(!$key) return array();
		
		return array(
			$this->templates['action-bar-vars']['is-ie7'] => (!(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') === false)),
			$this->templates['action-bar-vars']['actions'] => $actions,
			$this->templates['action-bar-vars']['prefix'] => $this->prefix,		
			$this->templates['action-bar-vars']['cmd'] => $this->prefix.$this->urlCmdName		
		);
	}
	/**
	 * Generate lang name of the field in fromat nameField_currentLanguageCode
	 * 
	 * @todo remove $as paramer, not used
	 * @param string $name
	 * @param string [$as]
	 * @return string
	 */
	function lang($name,$as=''){
	    return isset($this->nasional[$name]) ? $name."_".$GLOBALS['BASIC_LANG']->current() : $name;
	}
	/**
	 * Standart Action creator
	 * 
	 * @access public
	 * @param string $action
	 * @param string|integer [$id]
	 * @param array [$miss]
	 * @param string [$script]
	 * @return string
	 */
	function createActionLink($action, $id = '', $miss = array(), $script = ''){
		if(!$miss && $miss !== null) $miss = $this->system;
		
	    return BASIC_URL::init()->link(($script ? $script : BASIC::init()->scriptName()),
	    	($this->useSaveState ? BASIC_URL::init()->serialize($miss) : '').
	    	$this->prefix.$this->urlCmdName.'='.$action.
	    	($id ? "&".$this->prefix."id=".$id : '')
	    );
	}
	/**
	 * Clean working data buffer
	 * 
	 * @access public
	 * @return array
	 */
	function cleanBedVar(){
		$tmp = $this->dataBuffer;
		foreach($tmp as $k => $v){
			if(!isset($this->fields[$k])){ //maybe this is language field
				$_f = explode("_", $k);
				$_c = count($_f);
				
				if($_c == 1) continue; //Not lingual and doesn't exist in fields
				
				unset($_f[$_c-1]);
				
				if($_c > 2){
					$k = implode("_", $_f);
				}else{
					$k = $_f[0];
				}
				if(isset($this->fieldsForeign[$k]) || (isset($this->fields[$k]) && $this->fields[$k][3] == 'none')){
					unset($tmp[$k]);
				}
			}else{
				if($this->fields[$k][3] == 'none' || isset($this->fieldsForeign[$k])){
					unset($tmp[$k]);
				}
			}
		}
		return $tmp;
	}
	/**
	 * Add element to data buffer
	 * 
	 * @param string $name
	 * @param mix $value
	 * @return void
	 * @see BaseDisplayComponentClass::setDataBuffer()
	 */
	function setDataBuffer($name, $value){
		if(isset($this->fields[$name]) && isset($this->nasional[$name]) && isset($GLOBALS['BASIC_LANG'])){
			$name = $name."_".BASIC_LANGUAGE::init()->current();
		}
		$this->dataBuffer[$name] = $value;
	}
	/**
	 * Set prefix
	 * 
	 * @access public
	 * @param string $text
	 * @return void
	 */
	function prefix($text){
		if($this->sorting && $this->sorting->prefix() == $this->prefix){
			$this->sorting->prefix($text);
		}
		if($this->filter && $this->filter->prefix() == $this->prefix){
			$this->filter->prefix($text);
		}
		if($this->paging && $this->paging->prefix() == $this->prefix){
			$this->paging->prefix($text);
		}
		$this->prefix = $text;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @package basic.form
 */
interface BasicFilterInterface{
	function prefix($text = null);
	function template($name, $default_template = '');
	function button($text);
	function field($name, $context);
	function rangeField($name, $context);
	function init();
	function form($fprm_attr = '');
	function sql();
	/**
	 * @return hashmap[$field key ] => (
	 * 		string filter
	 * 		string data
	 * 		string type - valid values[match, start, middle, end] 
	 * )
	 */
	function buffer();
}
/**
 * Generate filter form and create sql criteria.
 * 
 * Usage : 
 * 		<code>
 * 			$filter = new BasicFilter('uid');
 * 
 * 				// set filter's fields
 * 				$filter->field('fname1', array(
 * 					'text' => 'filter text'
 * 					'formtype' => 'the valid componet support control type',
 * 					'filter' => ' AND `fname1` like "%{V}%" '
 * 					'attributes' => array(
 * 						...
 * 					)
 * 				));
 * 				$filter->field('fname2', array(
 * 					'text' => 'filter text'
 * 					'formtype' => 'the valid componet support control type',
 * 					'filter' => ' AND `fname2` in ({V}) '
 * 					'attributes' => array(
 * 						...
 * 					)
 * 				));
 * 				
 * 				// get values from the request
 * 				$filter->init()
 * 				
 * 				// get html code
 * 				$html = $filter->form();
 * 				// get sql code
 * 				$sql = $filter->sql();
 * 		</code>
 * 
 * @author Evgeni Baldziyski
 * @version 2.1.0 
 * @since 28.02.2007 update 15.12.2011
 * @package basic.form
 */
class BasicFilter implements BasicFilterInterface{
	/**
	 * @access private
	 * @var DysplayComponent
	 */
	protected $filter = null;
	/**
	 * Filter button name
	 * @access private
	 * @var string
	 */
	protected $button = 'Filter';
	/**
	 * Constructor
	 *
	 * @param string [$prefix]
	 * @param string [$button]
	 * @param [$template]
	 * @return void
	 */
	function __construct($prefix = '', $button = 'Filter', $template = ''){
		$this->filter = new DysplayComponent();
		$this->filter->prefix = $prefix;
		if($template) $this->filter->template_form = $template;
		
		$this->button = $button;
	}
	/**
	 * Set filter prefix
	 * @access public
	 * @param string [$text]
	 * @return void
	 * @see BasicFilterInterface::prefix()
	 */
	function prefix($text = null){
		if($text === null){
			return $this->filter->prefix;
		}
		$this->filter->prefix = $text;
	}
	/**
	 * Set template variables
	 * @access public
	 * @param string $name
	 * @param string [$default_template]
	 * @see BasicFilterInterface::template()
	 */
	function template($name, $default_template = ''){
		$this->filter->template_form = $name;
		$this->filter->template_form_default = $default_template;
	}
	/**
	 * Set text on filter submit button
	 * @see BasicFilterInterface::button()
	 */
	function button($text){
		$this->button = $text;
	}
	/**
	 * Set filter field
	 * 
	 * @access public
	 * @param string $name
	 * @param array $context
	 * @return array
	 * @see BasicFilterInterface::field()
	 */
	function field($name, $context){
		if(isset($context['lingual']) && $context['lingual']){
			if(class_exists('BASIC_LANGUAGE')){
				if(isset($context['filter'])) $context['filter'] = preg_replace('/[ ]`?'.$name.'`?[ ]/', ' `'.$name.'_'.BASIC_LANGUAGE::init()->current().'` ', $context['filter']);
			}
			unset($context['lingual']);
		}
		$context['real_name'] = $name;
		$this->filter->setField($name, $context);
	}
	/**
	 * One field create to input fields From and To
	 * 
	 * @access public
	 * @param string $name
	 * @param array $context
	 * @return void
	 * @see BasicFilterInterface::rangeField()
	 */
	function rangeField($name, $context){
		$this->field($name.'_from', $context);
		$this->field($name.'_to', $context);
	}
	/**
	 * Check request and set values to system buffer.
	 * 
	 * @access public
	 * @return boolen - if exist error retrn true
	 */
	public function init(){
		return $this->filter->test();
	}
	/**
	 * Create HTML filter form.
	 * 
	 * @access public
	 * @param array [$arr]
	 * @return string
	 */
	function form($arr = array()){
		BASIC_TEMPLATE2::init()->set('button', $this->button, $this->filter->template_form);
		return $this->filter->FORM_MANAGER($arr);
	}
	/**
	 * Create sql  filter criteria.
	 * Check for special field attributes
	 * 		"filter" - filter pattern in this format:
	 * 			for single url this sintax
	 * 				" (AND|OR) `name field` = '{V}'" result : " (AND|OR) `name field` = 'url el value'"
	 *
	 * 			for multiple url element use the follow syntax field1,field2,fieldN...
	 * 				' AND `code` in ({V})' rezultate : ' AND `code` in (5,43,20,...)'
	 * 					OR
	 * 				' AND `{V}` = 1' rezultate : 'AND `arr el 1` = 1 AND `arr el 2` = 1 .... AND `arr el N` = 1'
	 * 		
	 * 		"filterFunction" - fonction generating filter sql code. Use 2 case: 
	 * 			Array(class, 'the class's metthod'), Array('', 'function name') or String('the current's class method')
	 * 			
	 * 			filterFunction signature - function (String|Integer(request value), String(the filter field's name))
	 * 
	 * @access public
	 * @return string
	 */
	function sql(){
		$tmp = '';
		
		foreach ($this->filter->fields as $v){
			if($v[3] == 'none') continue;

//			$this->dataBuffer[$v[0]] = $GLOBALS['BASIC_URL']->request($this->prefix.$v[0],
//				$this->cleanerDecision($v[3],true,$v[7]),$v[2]
//			);
			if($this->filter->getDataBuffer($v[0]) !== ''){
			    if(isset($v['filterFunction'])){
			        if(is_array($v['filterFunction']) && count($v['filterFunction']) == 2){
			        	
			            if($v['filterFunction'][0] == ''){
			            	// object model
			               	$tmp .= $v['filterFunction']($this->filter->getDataBuffer($v[0]),$v[0]);
			            }else{
			            	// function model
			                $tmp .= $v['filterFunction'][0]->$v['filterFunction'][1]($this->filter->getDataBuffer($v[0]),$v[0]); 
			            }
			        }else{
			            $tmp .= $this->$v['filterFunction']($this->filter->getDataBuffer($v[0]), $v[0]);
			        }
			    }else if(isset($v['filter'])){
				    $tmp .= $this->_strategy($this->filter->getDataBuffer($v[0]), $v['filter']);
			    }else{
			        throw new Exception('Can not find filter or filterFunction catcher.');
			    }
			}
		}
		return $tmp;
	}
	/**
	 * Get data from Filter buffer
	 * 
	 * @access public
	 * @return array
	 * @see BasicFilterInterface::buffer()
	 */
	function buffer(){
		$tmp = array();
		foreach($this->filter->getBuffer() as $key => $val){
			if($val){
				$tmp[$this->filter->fields[$key]['real_name']] = array(
					'data' => $val,
					'type' => $this->typeMatch($key),
					'filter' => $this->filter->fields[$key]['filter']
				);
			}
		}
		return $tmp;
	}
	/**
	 * Generate type match string from the fields property "filter". This string can help if you need to make regexp check.
	 * 
	 * @access public
	 * @param string $name
	 * @return string
	 */
	function typeMatch($name){
		if(!isset($this->filter->fields[$name])) return null;
		
		if(strpos($this->filter->fields[$name]['filter'], '=') !== false) return 'match';
			
		$spl = preg_split("/like/i", $this->filter->fields[$name]['filter']);
		if(isset($spl[1])){
			$spl[1] = preg_replace("/['\" ]+/", "", $spl[1]);
			
			if(preg_match("/^%[^%]+%$/", $spl[1])) return 'middle';
			if(preg_match("/^%/", $spl[1])) return 'start';
			if(preg_match("/%$/", $spl[1])) return 'end';
			
			return 'match';
		}
	}
	/**
	 * Help method
	 * 
	 * @access private
	 * @param array  $post    request value
	 * @param string $filter  filter declaration
	 * @return string
	 */
	protected function _strategy($post, $filter){
		$tmp = '';
		if(is_array($post)){
			if(count($post) > 0){
				if(count($post) == 1 && $post[0] == '') return '';
				if(preg_match("/\{[^\}]+\}[ ]?=/", $filter)){
					foreach($post as $arr_v){
						if($arr_v != '') $tmp .= preg_replace("/(\{[^\}]+\})/", $arr_v, $filter);
					}
				}else{
					foreach($post as $arr_v){
						//if($arr_v != '') $filter = preg_replace("/(\{[^\}]+\})/",$arr_v.",$1",$filter);
						if($arr_v !== ''){
							$filter = preg_replace("/(['\"])?(\{[^\}]+\})(['\"])?/", "$1#_#_#$3,$1$2$3", $filter);
							$filter = preg_replace("/#_#_#/", $arr_v, $filter);
						}
					}
					$tmp .= preg_replace("/\,?['\"]?{[^\}]+\}['\"]?/", '', $filter);
				}
			}
		}else{
			if($post !== '') $tmp .= preg_replace("/\{[^\}]+\}/", $post, $filter);
		}
		return $tmp;
	}
}