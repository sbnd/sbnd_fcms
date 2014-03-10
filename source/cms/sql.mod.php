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
 * @package cms.sql
 * @version 7.0.6
 */

BASIC::init()->imported('sql.mod');
BASIC::init()->imported('settings.mod', 'cms');

class CMS_SQL extends BASIC_SQL{
	protected $supportArchiveMode = true;
	
	static public function init($config = array()){
		if(!isset($GLOBALS['BASIC_SQL'])){
			$GLOBALS['BASIC_SQL'] = new CMS_SQL();
			$GLOBALS['BASIC_SQL']->backupEngine = new BasicDumpSql();
		}
		foreach ($config as $k => $v){
			if($k == 'connect'){
				$GLOBALS['BASIC_SQL']->connect($v); continue;
			}
			if($k == 'backup'){
				$GLOBALS['BASIC_SQL']->backupEngine->option('default', $v);
			}
			$GLOBALS['BASIC_SQL']->$k = $v;
		}
		return $GLOBALS['BASIC_SQL'];
	}
	function append($sql, $clean = false){
		if($clean) $this->clean();
	
		if($this->supportArchiveMode && CMS_SETTINGS::init()->get('SITE_DATA_DELETE') == 'archive'){
			preg_match("/^[ ]*(select|update) ([^, ]+)/i", $sql, $act);
			
			if(isset($act[1])){
				$action = strtolower($act[1]);
				$tbl_name = $act[2];
				if($action != 'update'){
					preg_match("/from ([^, ]+)/i", $sql, $tmp);
					$tbl_name = isset($tmp[1]) ? $tmp[1] : '';
				}
				if($tbl_name){
					$spl = preg_split("/where /i", $sql);
					$count = count($spl);
					
					if($count > 1){
						$spl[$count - 1] = $tbl_name.".`_deleted` = 0 AND ".$spl[$count - 1]."";
					}else{
						$spl[1] = " ".$tbl_name.".`_deleted` = 0 ";
					}
					$sql = implode('WHERE ', $spl);			
				}
			}
		}
		$this->buffer .= $sql;
	}
	function read2($query, $returnArray = false){
		return $this->read_exec2($query, $returnArray);
	}
	function read_exec2($query, $returnArray = false){
		$this->supportArchiveMode = false;
		
		$data = $this->read_exec($query, $returnArray);
		$this->supportArchiveMode = true;
		
		return $data;
	}
	function exec2($query){
		$this->supportArchiveMode = false;
		
		$data = $this->exec($query);
		$this->supportArchiveMode = true;
		
		return $data;
	}
	/**
	 * Support _delete repear
	 * 
	 * @param string $query
	 * @return int
	 */
	function exec($query){
		$rdr = parent::exec($query);
		
		if($this->errorManage()){
			return $this->exec($query);
		}
		
		return $rdr;
	}
	/**
	 * Support _delete repear
	 * 
	 * @param string $query
	 * @param boolean $returnArray
	 * @return SqlReader
	 */
	function read_exec($query, $returnArray = false){
		$rdr = parent::read_exec($query, $returnArray);
		
		if($this->errorManage()){
			return $this->read_exec($query, $returnArray);
		}
		
		return $rdr;
	}
	/**
	 * @return boolean
	 */
	protected function errorManage(){
		BASIC_ERROR::init()->reset();
		$err = BASIC_ERROR::init()->error();
		
		if($err['code'] == 1054){
			preg_match("/column( name)? '([^']+)'/", $err['message'], $match);
			if(isset($match[2])){
				$spl = explode(".", $match[2]);
				
				$table_name = $spl[count($spl) - 2];
				$column_name = $spl[count($spl) - 1];
		
				if($column_name == '_deleted'){
					$table = new BasicSqlTable();
					
					$table->field('_deleted', 'int', 0, false);
					$table->key('_deleted');
					
					if($this->createColumn($table_name, $table)){
						BASIC_ERROR::init()->clean();
						return true;
					}
				}
			}
		}
		return false;
	}
}
CMS_SQL::init();