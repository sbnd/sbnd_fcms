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
* @package basic.sql
* @version 7.0.4
*/


/**
 * 
 * SQL driver interface
 * 
 * @author Evgeni Baldzhiyski
 * @version 2.0
 * @since 06.03.2012
 * @package basic.sql
 */
interface SqlDriverInterface{
	/**
	 * Test for exist PHP sql support. Test for exist php extension.
	 * 
	 * @return boolean
	 */
	function support();
	/**
	 * 
	 * DB connection
	 * 
	 * @param string $host
	 * @param string $user
	 * @param string $pwd
	 * @param string $db
	 */
	function connect($host, $user, $pwd, $db = '');
	/**
	 * 
	 * Query function
	 * @param string $sql
	 */
	function query($sql);
	/**
	 * 
	 * get data from DB in array
	 * @param object $res
	 * @param string $type
	 */
	function fetch_array($res, $type = 'BOTH');
	/**
	 * 
	 * Number of rows from the query
	 * @param object $res
	 */
	function num_rows($res);
	/**
	 * 
	 * Get the last inserted ID
	 */
	function lastId();
	/**
	 * 
	 * error handler
	 */
	function error();
	/**
	 * 
	 * Returns the error number from the last MySQL function. 
	 * @deprecated
	 */
	function errno();
	/**
	 * 
	 * Close mysql instance
	 */
	function close();
	/**
	 * return field' s names
	 *   'Tables_in_{db name}' || 0
	 * 
	 * @param string $base
	 */
	function showTables($base = '');
	/**
	 * return field' s names
	 *    'Table'
	 *    'Create Table'
	 * 
	 * @param string $base
	 */	
	function showCreateTables($table = '');
	/**
	 * 
	 * Show table fields
	 * @param string $table
	 * @param string $base
	 */
	function showFields($table, $base = '');
	/**
	 * 
	 * Set name
	 * @param  $charset
	 */
	function setName($charset);
	/**
	 * 
	 * Create table
	 * 
	 * @param string $idname
	 * @param string $name
	 * @param string / BasicSqlTable $data
	 * @param string $type
	 */
	function createTable($idname, $name, $data, $type = '');
	/**
	 * 
	 * Add column to table
	 * @param string $tbl
	 * @param string $data
	 */
	function createColumn($tbl, $data);
	/**
	 * 
	 * Remove a column from table
	 * @param string $tbl
	 * @param string $name
	 */
	function drobColumn($tbl, $name);
	/**
	 * 
	 * Create database
	 * @param string $name
	 * @param string $charset
	 */
	function createDatabase($name, $charset);
}
/**
 * SQL result reader 
 * 
 * @author Evgeni Baldziyski
 * @version 1.2
 * @package basic.sql
 */
class SqlReader{
    /**
     * 
     * Resourse
     * @var string
     */
	var $resource = '';
	/**
	 * 
	 * 
	 * @var int
	 */
	var $obj = 0;
	/**
	 * 
	 * Sql reader items
	 * @var array
	 */
	var $SQLR_items = array();
	/**
	 * 
	 * Result type
	 * @var string
	 */
	var $type_res = '';

	/**
	 * 
	 * 
	 *  Constructor metod
	 *
	 * @param sesource $res - result handler
	 * @param BASIC_SQL $sqlobj
	 * @param string $type_res - type of the returned array
	 */
	function SqlReader($res, $sqlobj, $type_res = 'BOTH'){
		$this->resource = $res;
		$this->obj = $sqlobj;
		$this->type_res = $type_res;
	}
	/**
	 * Get the current row of the result
	 *
	 * @return array
	 */
	function getItems(){
		return $this->SQLR_items;
	}
	/**
	 * 
	 * Set the current row from the result
	 * @param array $arr
	 */
	function setItems($arr){
		$this->SQLR_items = $arr;
	}
	/**
	 * Set the current row from the result like setItems but for single data
	 *
	 * @param string $name
	 * @param mix $value
	 */
	function setItem($name, $value){
		$this->SQLR_items[$name] = $value;
	}

	/**
	 * 
	 * Read and buffer the next row of the result
	 *	<code>
	 * 		function formater($array_data){
	 * 			foreach($array_data as $k => $v){
	 * 				if($k == 8){
	 * 					unset($array_data[$k]);
	 * 				}else{
	 * 					$array_data[$k] = '<div>'.$v.'</div>';
	 *				}
	 * 			}
	 * 			return $array_data;
	 * 		}
	 * 		$rdr = BASIC_SQL::init()->read_exec("
	 * 			SELECT * FROM `table_name` WHERE 1=1
	 * 		");
	 * 		while($rdr->read('formater')){
	 * 			print $rdr->item('column_1');
	 * 		}
	 * 	</code>
	 * @param array [$cleanCall] - clean functionality
	 * @return array
	 */
	function read($cleanCall = null){
		$tmp = $this->resource;
		if ($tmp){
		    $this->SQLR_items = @$this->obj->fetch_array($this->resource, $this->type_res);
		    if($cleanCall != null && $this->SQLR_items){
		    	if(is_array($cleanCall)){
		    		$class = &$cleanCall[0];
		    		$metod = $cleanCall[1];
		    		$this->SQLR_items = $class->$metod($this->SQLR_items);
		    	}else{
		    		$this->SQLR_items = $cleanCall($this->SQLR_items);
		    	}
		    }
		    $tmp = $this->SQLR_items;
		}
		return $tmp;
	}
	/**
	 * 
	 * Get an element from the current row from the result
	 *	<code>
	 * 		function formater($string){
	 * 			if($string == 'test 1'){
	 * 				$string = 'test';
	 * 			}
	 * 			return $string;
	 * 		}
	 * 		$rdr = BASIC_SQL::init()->read_exec("
	 * 			SELECT * FROM `table_name` WHERE 1=1
	 * 		");
	 * 		while($rdr->read('formater')){
	 * 			print $rdr->item('column_1','formater');
	 * 		}
	 * 	</code>
	 * @param string $name
	 * @param string/array [$callback]
	 * @return string
	 */
	function field($name,$callback = null){
		$tmp = '';
		if(isset($this->SQLR_items[$name])){
			$tmp = $this->SQLR_items[$name];
		}else{
			$tmp = '';
		}
		if($callback != null){
			if(is_array($callback)){
				$class = &$callback[0];
				$metod = $callback[1];
				return $class->$metod($tmp);
			}else{
				return $callback($tmp);
			}
		}else{
			return $tmp;
		}
	}
	/**
	 * Shortcut to "field"
	 *
	 * @param string $name
	 * @param string/array [$callback]
	 * @return string
	 */
	function item($name,$colback = null){
		return $this->field($name,$colback);
	}
	/**
	 *
	 * Check for element existence in current row from the result
	 * 
	 * @param string $name
	 * @return boolean
	 */
	function test($name){
		return isset($this->SQLR_items[$name]);
	}
	/**
	 * Get the number of rows from the result.
	 *
	 * @return int
	 */
	function num_rows(){
		if($this->resource){
			return @$this->obj->num_rows($this->resource);
		}
		return 0;
	}
	/**
	 * 
	 * Get array which is suitable for select usage
	 * 
	 * @param string $id
	 * @param string $text
	 * @param array $before
	 * @return array
	 */
	function getSelectData($id = 'id', $text = 'title', $before = array()){
		if(!is_array($before)) $before = array();
		
		while($this->read()){
			$before[$this->item($id)] = $this->item($text); 
		}
		return $before;
	}
	/**
	 * Get all result data in array
	 * 
	 * @return array
	 */
	function getArrayData(){
		$tmp = array();
		while($this->read()){
			$tmp[] = $this->getItems();
		}
		return $tmp;
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @since 26.03.2012
 * @package basic.sql
 */
interface SqlDumpInterface{
	/**
	 * Make backups
	 * return path to backup
	 * 
	 * @param boolean $drop_if_exist
	 * @return string
	 */
	function backup($drop_if_exist = true);
	/**
	 * Revert to backup points
	 * 
	 * @param string $source
	 * @param string $table
	 */
	function revert($table = '', $source = '');
	/**
	 * Remove backup points
	 * 
	 * @param string $source
	 */
	function remove($source = '');
	/**
	 * @param string/hashmap $options
	 * @return string
	 */
	function option($name, $value = null);
}
/**
 * Help module class for create dump data of DB Tables 
 *
 * @author Evgeni Baldzhiyski
 * @version 0.4
 * @package basic.sql
 * @since 27.09.2008
 */
class BasicDumpSql implements SqlDumpInterface{
	public $default = '';
	/**
	 * Create structore dump
	 *
	 * @param string $table
	 * @param boolean [$drop_if_exist]
	 * @return string
	 */
	function getStructore($table, $drop_if_exist = true){
        $tmp = '';
		if($res = BASIC_SQL::init()->showCreateTables($table)->read()){
	    	$tmp = "#\n".
               "# Dumping structure for table ".$table."\n".
               "#\n";
	    	if($drop_if_exist){
	    		$tmp .= "DROP TABLE IF EXISTS `".$table."`;\n";
	    	}	
        	$tmp .= $res[1].";";
        }
        return $tmp;
	}
	/**
	 * Create data dump
	 * 
	 * @param string $table
	 * @return string
	 */
	function getData($table){
	   	$tmp = '';
	    
	   	$rdr = BASIC_SQL::init()->read(" SELECT * FROM `".$table."` ");
	   	while($rdr->read()){
			$tmp_s = "";
	    	foreach ($rdr->getItems() as $k => $v){
	        	if($tmp_s) $tmp_s .= ",";	
	            $tmp_s .= "'".str_replace("'", "\\'", $v)."'";
	        }
	        
	        if($tmp) $tmp .= "\n";
	        $tmp .= "INSERT INTO `".$table."` VALUES (".$tmp_s.");";
	    }
	    if($tmp){
 			return "#\n".
               "# Dumping data for table ".$table."\n".
               "#\n".
 			$tmp;
	    }
        return $tmp;
	}
	/**
	 * Create structure and data dump
	 *
	 * @param boolean [$drop_if_exist]
	 * @return string
	 */
	function backup($drop_if_exist = true){
	    $sql = '';
		
	    $rdr = BASIC_SQL::init()->showTables();
	    while ($rdr->read()){
	    	if($sql) $sql .= "\n";
	    	
	    	$sql .= $this->getStructore($rdr->item(0), $drop_if_exist)."\n".
	    			$this->getData($rdr->item(0));
	    }
	    return $sql;
	}
	/**
	 * 
	 * Revert -> use generated dump file and make sql queryies
	 * 
	 * @param string $table
	 * @param string $source
	 * 
	 * @see SqlDumpInterface::revert()
	 */
	function revert($table = '', $source = ''){
		$success = false;
		$buffer = '';
		
		if(!$source && !$this->default){
			return false;
		}
		
		if(!$source) $source = $this->default;

		if($file = @file(BASIC::init()->ini_get('root_path').$source)){
			$file[0] = str_replace("<?php", "#", $file[0]);
			
			foreach($file as $row){
				if($row[0] == "#") continue;
				
				$buffer .= preg_replace("//", "", $row);
			}
			if(!$table){
				$spl = preg_split("/;[ ]*[\t\r\n]+/", $buffer);
				foreach($spl as $v){
					BASIC_SQL::init()->exec($v);
					$success = true;
				}
			}else{
				$buffer = str_replace("\'", "%ESC%", $buffer);
				$buffer = preg_replace_callback("/'[^']*'/", array($this, '_backupParser'), $buffer);
				
				$buffer = str_replace('\"', "%DESC%", $buffer);
				$buffer = preg_replace_callback('/"[^"]*"/', array($this,'_backupParser'), $buffer);
				
				preg_match_all('/(INSERT INTO|CREATE TABLE) [`\[]?'.$table.'[`\]]? [^;]+;/i', $buffer, $matches);
				if(isset($matches[0])){
					foreach($matches[0] as $v){
						if(!BASIC_SQL::init()->exec(preg_replace("/;*[\n\t\r]*$/", "", str_replace('%END%', ';', $v)))){
							return false;
						}
						$success = true;
					}
				}
			}
		}else{
			throw new Exception('Can not find bachup file ('.BASIC::init()->ini_get('root_path').$source.').');
		}
		return $success;
	}
	/**
	 * 
	 * Backup parser
	 * 
	 * @access private
	 * @param array $matches
	 */
	protected function _backupParser($matches){
		$matches[0] = str_replace(";", "%END%",		$matches[0]);
		$matches[0] = str_replace("%ESC%", "\\'",	$matches[0]);
		$matches[0] = str_replace("%DESC%", '\\"',	$matches[0]);
		
		return $matches[0];
	}
	/**
	 * Options manager
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @see SqlDumpInterface::option()
	 */
	function option($name, $value = null){
		if($value !== null){
			return $this->$name = $value;
		}
		return isset($this->$name) ? $this->$name : '';
	}
	function remove($source = ''){
		if($source){
			 @unlink(BASIC::init()->ini_get('root_path').$source);
		}
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 01.01.2008
 * @version 0.1
 * @package basic.sql
 */
class BasicSqlTable{
	/**
	 * 
	 * Table fields
	 * @var array
	 * @access private
	 */
	protected $fields = array();
	/**
	 * 
	 * Table keys
	 * @var array
	 * @access private
	 */
	protected $keys = array();
	/**
	 * 
	 * set table field
	 * @param string $name
	 * @param string $type
	 * @param int $lenght
	 * @param boolean $null
	 * @param string $default
	 */
	function field($name, $type = 'varchar', $lenght = 255, $null = false, $default = ''){
		$this->fields[$name] = array(
			'type' => $type, 
			'length' => $lenght, 
			'null' => $null, 
			'default' => $default
		);
	}
	/**
	 * 
	 * Set table keys
	 * @param string $name
	 * @param string $group
	 * @param boolean $is_unique
	 */
	function key($name, $group = '', $is_unique = false){
		if(!$group) $group = $name;
		
		if(isset($this->keys[$group])){
			$this->keys[$group]['fields'][] = $name; 
		}else{
			$this->keys[$group] = array(
				'unique' => $is_unique, 
				'fields' => array($name)
			);
		}
	}
	/**
	 * 
	 * Return table data (keys and fields)
	 * 
	 * @return array
	 */
	function data(){
		foreach ($this->keys as $k => $v){
			foreach ($v['fields'] as $kk => $vv){
				if(!isset($this->fields[$vv])){
					unset($v['fields'][$kk]);
				}
			}
			$this->keys[$k] = $v;
			
			if(!$v['fields']){
				unset($this->keys[$k]);
			}
		}
		return array(
			'fields'=> $this->fields,
			'keys' 	=> $this->keys
		);
	}
}
/**
 * @author Evgeni Baldzhiyski
 * @since 01.01.2008
 * @version 2.3
 * @package basic.sql
 */
class BASIC_SQL{
    
	/**
	 * 
	 * SQL object
	 * @var object
	 * @access private
	 */
	protected $obj 		= null;
	/**
	 * 
	 * Connect
	 * @var int
	 * @access private
	 */
	protected $connect 	= 0;
	/**
	 * 
	 * Biffer
	 * @var string
	 * @access private
	 */
	protected $buffer 	= '';
	/**
	 * 
	 * Runtime
	 * @var int
	 * @access private
	 */
	protected $runtime 	= 0;
	
	/**
	 * 
	 * Reults type
	 * @var string
	 * @access public
	 */
	public $type_res 	= 'ASSOC';//'BOTH';
	/**
	 * 
	 * Url
	 * @var string
	 * @access public
	 */
	public $url 		= '';
	/**
	 * 
	 * DB host
	 * @var string
	 * @access public
	 */
	public $host 		= '';
	/**
	 * 
	 * DB user
	 * @var string
	 * @access public
	 */
	public $user 		= '';
	/**
	 * 
	 * Db password
	 * @var string
	 * @access public
	 */
	public $password 	= '';
	/**
	 * 
	 * Database name
	 * @var string
	 * @access public
	 */
	public $database 	= '';
	/**
	 * 
	 * Collation
	 * @var string
	 * @access public
	 */
	public $collation 	= '';
	/**
	 * 
	 * Server name
	 * @var string
	 * @access public
	 */
	public $server 		= '';
	/**
	 * 
	 * Log flag
	 * @var boolean
	 * @access public
	 */
	public $log 	  	= false;
	/**
	 * 
	 * Result type
	 * @var string
	 * @access public
	 */
	public $result 		= 'array';//'object';
	/**
	 * 
	 * Hostory data
	 * @var array
	 * @access public
	 */
	public $history 	= array();
	/**
	 * Път до sql файл които ще се парсне при липса на база или таблица.
	 * 
	 * Path ti sql dump file. Will be used when there is no db or db table
	 * @var string
	 * @access public
	 */
	public $backup = '';
	/**
	 * @var SqlDumpInterface
	 * @access public
	 */
	public $backupEngine = null;
	/**
	 * 
	 * Constructor
	 * 
	 * @param array [$config]
	 * @return BASIC_SQL
	 * @static
	 * @access public
	 */
	static public function init($config = array()){
		if(!isset($GLOBALS['BASIC_SQL'])){
		    $GLOBALS['BASIC_SQL'] = new BASIC_SQL();
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
	/**
	 * 
	 *  Connection 
	 *  
	 * 	type_server://db_user:db_pass@db_server_host/db_table_name
	 * 	Ex: mysql://user_name:password@host_name/table_name
	 * 
	 * 	<code>
	 * 		try{
	 *			BASIC_SQL::init()->connect("mysql://user_name:password@host_name/table_name",'utf8');
	 *		}catch (Exception $e){
	 *			die(BASIC_GENERATOR::init()->element('div',
	 * 				'style=color:#FF0000;font-size:12pt;',$e->getMessage()
	 * 			));
	 *		}
	 *	</code>
	 * 
	 * @param string [$url]
	 * @param string [$collation]
	 * @return BASIC_SQL
	 */
	function connect($url, $collation = ''){
		$this->runtime = microtime(true);
		if(!$url && !$this->url){
			throw new Exception("Invalide connection's url.");
		}else if($url){
			$this->url = $url;
		}
		preg_match("/^(.+):\/\/([^:]+):?(.+)?@(.+)\/(.+)$/", $this->url, $arr_data);
		
		if(!isset($arr_data[1])){
			throw new Exception("The url '".$url."' is invalid."); return false;
		}
		$err = false;
		switch ($arr_data[1]){
			case "mysql":
				$this->obj = new _MySql(); break;
			case "mysqli":
				$this->obj = new _MySqli(); break;
			case "mssql":
				$this->obj = new _MsSql(); break;
			default:
				$err = true;
		}
		if($err || !$this->obj->support()){
			throw new Exception("The server's type (".$arr_data[1].") isn't suported !"); return false;
		}
				
		$this->server   = $arr_data[1];
		$this->user 	= $arr_data[2];
		$this->password = $arr_data[3];
		$this->host 	= $arr_data[4];
		$this->database = $arr_data[5];
		$this->collation = $collation;
		
		$this->connect = $this->obj->connect($this->host, $this->user, $this->password, $this->database);
		if($this->connect || (!$this->connect && $this->obj->errno() == 1049)){
			$erno = $this->obj->errno();
			if(!$this->connect){
				$this->connect = $this->obj->connect($this->host, $this->user, $this->password);
			}
			if($erno == 1049 && $this->createDatabase($this->database, $this->collation)){
				$this->exec(" USE `".$this->database."` ");
				$this->backupEngine->revert();
			}else if($this->obj->errno()){
				throw new Exception($this->obj->error()); return false;
			}
		}else{
			throw new Exception($this->obj->error()); return false;
		}
		if($this->collation != ''){
			$this->setName($this->collation);
		}
		return $this->collation;
	}
	/**
	 * 
	 *  Append queries
	 *  
	 *	<code>
	 * 		BASIC_SQL::init()->append('SELECT * FROM `table_name` WHERE 1=1');
	 * 			// or
	 * 		BASIC_SQL::init()->append('SELECT * FROM `table_name` WHERE 1=1',true);
	 * 		BASIC_SQL::init()->append(' AND `test_2` = 1 ');
	 * 		BASIC_SQL::init()->append(' AND `test_3` = 231 ');
	 * 	</code>
	 * @param string $sql
	 * @param boolean [$clean]
	 */
	function append($sql,$clean=false){
		if($clean) $this->clean();

		$this->buffer .= $sql;
	}
	/**
	 * 
	 * Clean the buffer
	 */
	function clean(){
		$this->buffer = '';
	}
	/**
	 * 
	 * Execute queries function
	 * 
	 * @param string [$query]
	 * @return int
	 */
	function exec($query){
		if(!$this->obj){ throw new Exception('No connection!'); return false; }
		
		if($query) $this->append($query,true);
		if(!$this->buffer || !$query) return false;

		$runtime = $this->runTimeQuery();
		$exectime_start = microtime(true);
		
        if(trim($this->buffer)){
			if(!$tmp = @$this->obj->query($this->buffer)){
			    $add_err = true;
				if($this->obj->errno() == 1146){
					$add_err = false;
					
					preg_match("/Table '[^\.]+\.([^']+)'/", $this->obj->error(), $match);
					if(!isset($match[1]) || !$this->backupEngine->revert($match[1])){
						$add_err = true;
					}
				}
				if($add_err){
					BASIC_ERROR::init()->append($this->obj->errno(), $this->obj->error());
				}else{
					return $this->exec($query);
				}
		    }
			 
			if($this->log) $this->setHistory($runtime,(microtime(true)-$exectime_start),$this->buffer);
			return $tmp;
        }
		return null;
	}
	/**
	 * 
	 * Execute and read the results
	 *	<code>
	 * 		$rdr = BASIC_SQL::init()->read_exec(" 
	 * 			SELECT 
	 * 				`column_1` as `column_11`,
	 * 				`column_2
	 * 			FROM 
	 * 				`table_name`
	 * 			WHERE 1=1
	 * 				AND `column_1` IN ('12','14','70')
	 * 		");
	 * 		while($rdr->read()){
	 * 			BASIC_ERROR->append(1,$rdr->item('column_11'));
	 * 			BASIC_ERROR->append(1001,$rdr->item('column_2'));
	 * 		}
	 * 	</code>
	 * @param string [$query]
	 * @param boolean [$returnArray]
	 * @return SqlReader
	 */
	function read_exec($query, $returnArray = false){
		if(!$this->obj){ throw new Exception('No connection!'); return false; }
		
		if($query){
			$this->append($query,true);
		}else{
			return false;
		}
		$runtime = $this->runTimeQuery();
		$exectime_start = microtime(true);
        
		$read = $this->obj->query($this->buffer);
		if(!$read){
			$add_err = true;
			if($this->obj->errno() == 1146){
				$add_err = false;
				
				preg_match("/Table '[^\.]+\.([^']+)'/", $this->obj->error(), $match);
				if(!isset($match[1]) || !$this->backupEngine->revert($match[1])){
					$add_err = true;
				}
			}
			if($add_err){
				BASIC_ERROR::init()->append($this->obj->errno(), $this->obj->error());
			}else{
				return $this->read_exec($query, $returnArray);
			}
		}
		if($this->log){
			$this->setHistory($runtime,(microtime(true)-$exectime_start),$this->buffer);
		}
		if($returnArray){
			return @$this->obj->fetch_array($read, $this->type_res);
		}
		return (new SqlReader($read, $this->obj, $this->type_res));
	}
	/**
	 * Shorcut on method read_exec.
	 * 
	 * @param string $query
	 * @param array $returnArray
	 * @see BASIC_SQL::read_exec()
	 */
	function read($query, $returnArray = false){
		return $this->read_exec($query, $returnArray);
	}
	/**
	 * 
	 * Get the db query error
	 */
	function lastError(){
		return $this->obj->error();
	}
	/**
	 * 
	 * Get db query errno
	 * @deprecated
	 */
	function lastErrorCode(){
		return $this->obj->errno();
	}
	/**
	 * 
	 *  Show tables in database
	 * @param string [$base]
	 * @return SqlReader
	 */
	function showTables($base = ''){
		if(!$this->obj){ throw new Exception('No connection!'); return false; }
		
		return new SqlReader($this->obj->showTables($base), $this->obj, MYSQL_NUM);
	}
	/** 
	 * 
	 * Show create table code
	 * @param string [$base]
	 * @return SqlReader
	 */
	function showCreateTables($table){
		if(!$this->obj){ throw new Exception('No connection!'); return false; }
		
		return new SqlReader($this->obj->showCreateTables($table), $this->obj, MYSQL_NUM);
	}
	/**
	 * 
	 * 
	 * Show fields
	 * 
	 * @param string $table
	 * @param string [$base]
	 * @return SqlReader
	 */
	function showFields($table, $base = ''){
		if(!$this->obj){ throw new Exception('No connection!'); return false; }
	
		return new SqlReader($this->obj->showFields($table, $base), $this->obj, $this->type_res);
	}
	/**
	 * 
	 * @deprecated
	 * @return string
	 */
	function getSql(){
		return $this->buffer;
	}
	/**
	 * 
	 * Return the last insert id
	 * @return int
	 */
	function getLastId(){
		return $this->obj->lastId();
	}
	/**
	 * Set name
	 * 
	 * @param string $charset
	 * @return boolean
	 */
	function setName($charset){
		return $this->obj->setName($charset);
	}
	/**
	 * 
	 * Create database
	 * 
	 * @param string $name
	 * @param string $charset
	 * @return boolean
	 */
	function createDatabase($name, $charset){
		return $this->obj->createDatabase($name, $charset);
	}
	/**
	 * 
	 * Create Table
	 * 
	 * @param string $idname [name of the PRIMARY KEY column]
	 * @param string $name [name of the table]
	 * @param steing || BasicSqlTable $data
	 * @return boolean
	 */
	function createTable($idname, $name, $data, $type = ''){
		return $this->obj->createTable($idname, $name, $data, $type);
	}
	/**
	 * 
	 * Create column
	 * @param string $tbl
	 * @param string $data
	 */
	function createColumn($tbl, $data){
		return $this->obj->createColumn($tbl, $data);
	}
	/**
	 * 
	 * Drop column
	 * @param string $tbl
	 * @param string $name
	 */
	function drobColumn($tbl, $name){
		return $this->obj->drobColumn($tbl, $name);
	}
	/**
	 * 
	 * Create Foreign key
	 * @param string $tblChild
	 * @param string $fieldChild
	 * @param string $tblParent
	 * @param string $fieldParent
	 */
	function createForeignKey($tblChild, $fieldChild, $tblParent, $fieldParent){
		return $this->exec($this->obj->createForeignKey($tblChild, $fieldChild, $tblParent, $fieldParent));
	}
	/**
	 * 
	 * Limit query
	 * 
	 * @param string $query
	 * @param string $from
	 * @param string $to
	 * @param string $SortField
	 * @param string $SortDirection
	 */
	function getLimit($query, $from, $to, $SortField, $SortDirection='ASC'){
		return $this->obj->limit($query,$from,$to,$SortField,$SortDirection);
	}
	/**
	 * 
	 * Get current mysql time = now()
	 * 
	 */
	function getCurDate(){
		return $this->obj->getCurDate();
	}
	/**
	 * 
	 * Runtime query
	 * 
	 * @access private
	 * @return  int
	 */
	protected function runTimeQuery(){
		return (microtime(true)-$this->runtime);
	}
	/**
	 * 
	 * 
	 * Set history
	 * 
	 * @access private
	 * @param int $runtime
	 * @param int $exectime
	 * @param string $query
	 */
	protected function setHistory($runtime,$exectime,$query){
		$this->history[] = array(
			"runtime"=>$runtime,
			"execution"=>$exectime,
			"query"=>$query
		);
	}
	/**
	 * Read info query for specific format dispaly
	 *
	 * @return hash array
	 */
	function getArrHistory(){
		return $this->history;
	}
	/** 
	 * 
	 * get empty reader
	 * 
	 * @return SqlReader
	 */
	function getEmptyReader(){
	    return new SqlReader(false, $this->obj);
	}
}
/**
 * MYSQL server driver
 *
 * @author Evgeni Baldzhiyski
 * @version 0.3 
 * @since 02.12.2006
 * @package sql.mod
 */
class _MySql implements SqlDriverInterface{
	/**
	 * 
	 * Connection flag
	 * @var int
	 */
	var $connect = 0;
	
	/**
	 * 
	 * Mysql support checker
	 * 
	 * @see SqlDriverInterface::support()
	 */
	function support(){
		return function_exists('mysql_connect');
	}
	/**
	 * Mysql connect
	 * 
	 * @param string $host
	 * @param string $user
	 * @param string $pwd
	 * @param string [$db]
	 * 
	 * @see SqlDriverInterface::connect()
	 */
	function connect($host, $user, $pwd, $db = ''){
		$this->connect = mysql_connect($host, $user, $pwd);
		if($this->connect){
			 mysql_select_db($db, $this->connect);
		}
		return $this->connect;
	}
	/**
	 * Query function
	 * @var string $sql
	 * @see SqlDriverInterface::query()
	 */
	function query($sql){
		return mysql_query($sql, $this->connect);
	}
	/**
	 * Fetch array
	 * @param object $res
	 * @param string $type 
	 *
	 * @see SqlDriverInterface::fetch_array()
	 */
	function fetch_array($res, $type = 'BOTH'){
		$type_res = array(
			'BOTH' => MYSQL_BOTH,
			'ASSOC' => MYSQL_ASSOC,
			'NUM' => MYSQL_NUM
		);
		return mysql_fetch_array($res, isset($type_res[$type]) ? $type_res[$type] : $type_res['BOTH']);
	}
	/**
	 * PHP mysql_num_rows()
	 * 
	 * @deprecated
	 * @see SqlDriverInterface::num_rows()
	 */
	function num_rows($res){
		return mysql_num_rows($res);
	}
	/**
	 * PHP mysql_insert_id()
	 * @deprecated
	 * @see SqlDriverInterface::lastId()
	 */
	function lastId(){
		return mysql_insert_id($this->connect);
	}
	/**
	 * PHP mysql_error()
	 * 
	 * @deprecated
	 * @see SqlDriverInterface::error()
	 */
	function error(){
		return mysql_error();
	}
	/**
	 * PHP mysql_errno()
	 * 
	 * @deprecated
	 * @see SqlDriverInterface::errno()
	 */
	function errno(){
		return mysql_errno();
	}
	/**
	 * PHP mysql_close()
	 * 
	 * @deprecated
	 * @see SqlDriverInterface::close()
	 */
	function close(){
		return mysql_close($this->connect);
	}
	/**
	 * Show tables queyr
	 * @param string [$base]
	 * @see SqlDriverInterface::showTables()
	 */
	function showTables($base = ''){
		return mysql_query(" SHOW TABLES ".($base ? " FROM `".$base."` " : ""), $this->connect);
	}
	/**
	 * Show create table
	 * @param string [$table]
	 * @see SqlDriverInterface::showCreateTables()
	 */
	function showCreateTables($table = ''){
		return mysql_query(" SHOW CREATE TABLE `".$table."`", $this->connect);
	}
	/**
	 * Show columns from table
	 * @param string $table
	 * @param string [$base]
	 * @see SqlDriverInterface::showFields()
	 */
	function showFields($table, $base = ''){
		return mysql_query(" SHOW COLUMNS FROM ".($base ? "`".$base."`." : "")."`".$table."` ", $this->connect);
	}
	/**
	 * Set names charset
	 * @param string $charset
	 * @see SqlDriverInterface::setName()
	 */
	function setName($charset){
		return mysql_query(" SET NAMES ".$charset, $this->connect);
	}
	/**
	 * Create database query
	 * @param string $name
	 * @param string [$charset]
	 * @see SqlDriverInterface::createDatabase()
	 */
	function createDatabase($name, $charset){
		return $this->query(" CREATE DATABASE `".$name."`".($charset ? " DEFAULT CHARACTER SET ".$charset : ""));
	}
	/**
	 * 
	 * 
	 * Create table function
	 * 
	 * @param string $idname
	 * @param string $name
	 * @param object $data
	 * @param string $type
	 * 
	 * @see SqlDriverInterface::createTable()
	 */
	function createTable($idname, $name, $data, $type = ''){
		$query = '';
		if($idname){
			$query .= "`".$idname."` int(11) NOT NULL AUTO_INCREMENT ";
		}
		if($data instanceof BasicSqlTable){
			$structure = $data->data();
			
			if($structure['fields']){
				foreach ($structure['fields'] as $fname => $field){
					if($query) $query .= ",\n";
					
					if($field['type'] == 'longtext' || $field['type'] == 'mediumtext'){
						$field['default'] = '';
						$field['length'] = null;
					}else if($field['type'] == 'date' || $field['type'] == 'datetime'){
						if($field['null']){
							$field['default'] = '0000-00-00'.($field['type'] == 'datetime' ? ' 00:00:00' : '');
						}
					}else if($field['type'] == 'int' || $field['type'] == 'float'){
						if($field['null']){
							$field['default'] = '0';
						}
					}else{
						if($field['null']){
							$field['null'] = "'".str_replace("'", "\\'", $field['null'])."'";
						}
					}
					
					$query .= "`".$fname."` ".$field['type'].($field['length'] !== null ? "(".$field['length'].")" : '').
						(!$field['null'] ? " NOT" : "")." NULL ".($field['default'] ? "DEFAULT ".$field['default'] : '')." ";
				}
				foreach ($structure['keys'] as $kname => $key){
					if($query) $query .= ",\n";
					
					$query .= ($key['unique'] ? "UNIQUE " : "")."KEY `".$kname."` (`".implode("`,`", $key['fields'])."`) ";
				}
			}else{
				$data = null;
			}
		}else if($data){
			if($query) $query .= ",";
			
			$query .= preg_replace('/,[ ]*$/', '', $data);
		}
		if($idname){
			if($query) $query .= ",";
			
			$query .= "PRIMARY KEY  (`".$idname."`)\n";
		}
		$query = 'CREATE TABLE `'.$name.'`(
			'.$query.'
		)';
		
		if($type){
			$query .= " ENGINE=".$type."";
		}

		return mysql_query($query, $this->connect);
	}
	/**
	 * Create column query
	 * 
	 * @param string $tbl
	 * @param string $data
	 * 
	 * @see SqlDriverInterface::createColumn()
	 */
	function createColumn($tbl, $data){
		return mysql_query("ALTER TABLE `".$tbl."` ADD ".$data." ", $this->connect);
	}
	/**
	 * Drop column query
	 * 
	 * @param string $tbl
	 * @param string $name
	 * @see SqlDriverInterface::drobColumn()
	 */
	function drobColumn($tbl, $name){
		return mysql_query("ALTER TABLE `".$tbl."` DROP COLUMN `".$name."` ", $this->connect); 
	}
    
	/**
	 * 
	 * Create foreign key query
	 * 
	 * @param string $tblChild
	 * @param string $fieldChild
	 * @param string $tblParent
	 * @param string $fieldParent
	 */
	function createForeignKey($tblChild, $fieldChild, $tblParent, $fieldParent){
		return "ALTER TABLE `".$tblChild."`
			ADD CONSTRAINT `".$tblChild.'_'.$tblParent."` FOREIGN KEY (`".$fieldChild."`)
			REFERENCES `".$tblParent."` (`".$fieldParent."`) ON DELETE CASCADE ";
	}
	/**
	 * 
	 * Add a limit to given query 
	 * @param string $query
	 * @param string $from
	 * @param string $to
	 * @param string $SortField
	 * @param string $sortdirection
	 * @return string $query
	 */
	function limit($query,$from,$to,$SortField,$sortdirection){
		$query = $query." LIMIT ".$from.",".$to;
		//die('test'.$query);
		return $query;
	}
	/**
	 * 
	 * Current time
	 */
	function getCurDate(){
		return "now()";
	}
}
/**
 * MYSQL server driver
 *
 * @author Evgeni Baldziyski
 * @version 0.3
 * @since 22.01.2007
 * @package basic.sql
 */
class _MySqli implements SqlDriverInterface{

	var $connect = 0;
	/**
	 * Check for mysqli support
	 * @see SqlDriverInterface::support()
	 */
	function support(){
		return function_exists('mysqli_connect');
	}
	/**
	 * Create database connection
	 * @param string $host
	 * @param string $user
	 * @param string $pwd
	 * @param string $db
	 * 
	 * @see SqlDriverInterface::connect()
	 */
	function connect($host, $user, $pwd, $db = ''){
		$spl = explode(":", $host, 2);
		
		return ($this->connect = @mysqli_connect($spl[0], $user, $pwd, $db, isset($spl[1]) ? $spl[1] : 3306));
	}
	/**
	 * 
	 * 
	 * Execute a query
	 * 
	 * @param string $sql
	 * @see SqlDriverInterface::query()
	 */
	function query($sql){
		return mysqli_query($this->connect, $sql);
	}
	/**
	 * Fetch array from db query
	 * 
	 * @param object $res
	 * @param string $type
	 * @see SqlDriverInterface::fetch_array()
	 */
	function fetch_array($res, $type='BOTH'){
		$type_res = array(
			'BOTH' => MYSQLI_BOTH,
			'ASSOC' => MYSQLI_ASSOC,
			'NUM' => MYSQLI_NUM
		);
		return mysqli_fetch_array($res,isset($type_res[$type]) ? $type_res[$type] : $type_res['BOTH']);
	}
	/**
	 * Get number of rows mysqli_num_rows()
	 * 
	 * @param object $res
	 * @see SqlDriverInterface::num_rows()
	 */
	function num_rows($res){
		return mysqli_num_rows($res);
	}
	/**
	 * Get last insert row id
	 * 
	 * @see SqlDriverInterface::lastId()
	 */
	function lastId(){
		return mysqli_insert_id($this->connect);
	}
	/**
	 * Get mysql connection error
	 * @see SqlDriverInterface::error()
	 */
	function error(){
		if(!$err = @mysqli_error($this->connect)){
			$err = @mysqli_connect_error();
		}
		return $err;
	}
	/**
	 * Errno
	 * 
	 * @see SqlDriverInterface::errno()
	 */
	function errno(){
		if(!$erno = @mysqli_errno($this->connect)){
			$erno = mysqli_connect_errno();
		}
		return $erno;
	}
	/**
	 * Mysqli connection close
	 * 
	 * @see SqlDriverInterface::close()
	 */
	function close(){
		return mysqli_close($this->connect);
	}
	/**
	 * 
	 * Show tables query
	 * 
	 * @param string $base
	 * @see SqlDriverInterface::showTables()
	 */
	function showTables($base = ''){
		return mysqli_query($this->connect, " SHOW TABLES ".($base ? " FROM `".$base."` " : ""));
	}
	/**
	 * Show create table query 
	 * 
	 * @param string $table
	 * @see SqlDriverInterface::showCreateTables()
	 */
	function showCreateTables($table = ''){
		return mysqli_query($this->connect, " SHOW CREATE TABLE `".$table."`");
	}
	/**
	 * Show columns query
	 * 
	 * @param string $table
	 * @param string $base
	 * @see SqlDriverInterface::showFields()
	 */	
	function showFields($table, $base = ''){
		return mysqli_query($this->connect, " SHOW COLUMNS FROM ".($base ? "`".$base."`." : "")."`".$table."` ");
	}
	/**
	 * Create database query
	 * @param string $name
	 * @param string $charset
	 * @see SqlDriverInterface::createDatabase()
	 */
	function createDatabase($name, $charset){
		return $this->query(" CREATE DATABASE `".$name."`".($charset ? " DEFAULT CHARACTER SET ".$charset : ""));
	}
	/**
	 * Create table query
	 * @param string $idname
	 * @param string $name
	 * @param object $data
	 * @param string $type
	 * @see SqlDriverInterface::createTable()
	 */
	function createTable($idname, $name, $data, $type = ''){
		$query = '';
		if($idname){
			$query .= "`".$idname."` int(11) NOT NULL AUTO_INCREMENT ";
		}
		if($data instanceof BasicSqlTable){
			$structure = $data->data();
			
			if($structure['fields']){
				foreach ($structure['fields'] as $fname => $field){
					if($query) $query .= ",\n";
					
					if($field['type'] == 'longtext' || $field['type'] == 'mediumtext'){
						$field['default'] = '';
						$field['length'] = null;
					}else if($field['type'] == 'date' || $field['type'] == 'datetime'){
						if(!$field['null']){
							$field['default'] = '0000-00-00'.($field['type'] == 'datetime' ? ' 00:00:00' : '');
						}
					}else if($field['type'] == 'int' || $field['type'] == 'float'){
						if(!$field['null']){
							$field['default'] = '0';
						}
					}else{
						if($field['default']){
							$field['default'] = "'".str_replace("'", "\\'", $field['default'])."'";
						}
					}
					
					$query .= "`".$fname."` ".$field['type'].($field['length'] !== null ? "(".$field['length'].")" : '').
						(!$field['null'] ? " NOT" : "")." NULL ".($field['default'] ? "DEFAULT ".$field['default'] : '')." ";
				}
				foreach ($structure['keys'] as $kname => $key){
					if($query) $query .= ",\n";
					
					$query .= ($key['unique'] ? "UNIQUE " : "")."KEY `".$kname."` (`".implode("`,`", $key['fields'])."`) ";
				}
			}else{
				$data = null;
			}
		}else{
			if($data){
				if($query) $query .= ",";
				
				$query .= preg_replace('/,[ ]*$/', '', $data);
			}
		}
		if($idname){
			if($query) $query .= ",";
			
			$query .= "PRIMARY KEY  (`".$idname."`)\n";
		}
		$query = 'CREATE TABLE `'.$name.'`(
			'.$query.'
		)';
		
		if($type){
			$query .= " ENGINE=".$type."";
		}
		return mysqli_query($this->connect, $query);
	}
	/**
	 * Create column query
	 * 
	 * @param string $tbl
	 * @param string $data
	 * @see SqlDriverInterface::createColumn()
	 */
	function createColumn($tbl, $data){
		return  mysqli_query($this->connect, "ALTER TABLE `".$tbl."` ADD ".$data." ");
	}
	/**
	 * Drop column in table
	 * 
	 * @param string $tbl
	 * @param string $name
	 * @see SqlDriverInterface::drobColumn()
	 */
	function drobColumn($tbl, $name){
		return mysqli_query($this->connect, "ALTER TABLE `".$tbl."` DROP COLUMN `".$name."` "); 
	}	
	/**
	 * 
	 * Create foreign Key function
	 * 
	 * @param string $tblChild
	 * @param string $fieldChild
	 * @param string $tblParent
	 * @param string $fieldParent
	 */
	function createForeignKey($tblChild, $fieldChild, $tblParent, $fieldParent){
		return "ALTER TABLE `".$tblChild."`
			ADD CONSTRAINT `".$tblChild.'_'.$tblParent."` FOREIGN KEY (`".$fieldChild."`)
			REFERENCES `".$tblParent."` (`".$fieldParent."`) ON DELETE CASCADE ";
	}
	/**
	 * 
	 * Add limit to query
	 * @param string $query
	 * @param string $from
	 * @param string $to
	 * @param string $SortField
	 * @param string $sortdirection
	 */
	function limit($query,$from,$to,$SortField,$sortdirection){
		$query = $query." LIMIT ".$from.",".$to;
		//die('test'.$query);
		return $query;
	}
	/**
	 * 
	 * now() function
	 * 
	 */
	function getCurDate(){
		return "now()";
	}
	/**
	 * Set names
	 * @param string $charset
	 * @see SqlDriverInterface::setName()
	 */
	function setName($charset){
		return mysqli_query($this->connect, " SET NAMES ".$charset);
	}
}
/**
 * Microsoft SQL Server driver
 *
 * @author Evgeny Baldzisky
 * @version 0.1 alpha
 * @since 30.04.2007
 * @package basic.sql
 */
class _MsSql implements SqlDriverInterface{
	
	/**
	 * 
	 * Connection flag
	 * @var int
	 */
	var $connect = 0;
	/**
	 * 
	 * Error
	 * @var string
	 */
	var $last_m_error = '';
	
	/**
	 * 
	 * 
	 * Check for mssql support
	 * @see SqlDriverInterface::support()
	 */
	function support(){
		return function_exists('mssql_connect');
	}
	/**
	 * 
	 * 
	 * Create db connection
	 * 
	 * @param string $host
	 * @param string $user
	 * @param string $pwd
	 * @param string $db
	 * @see SqlDriverInterface::connect()
	 */
	function connect($host,$user,$pwd,$db = ''){
		$this->connect = mssql_connect($host,$user,$pwd);
		if($this->connect){
			 mssql_select_db($db,$this->connect);
		}
		return $this->connect;
	}
	/**
	 * Query function
	 * 
	 * @param string $sql
	 * 
	 * @see SqlDriverInterface::query()
	 */
	function query($sql){
		$sql = preg_replace('/`([^`]+)`/','[$1]',$sql);
		//print($sql."< /br>");
		$res = @mssql_query($sql,$this->connect) or $this->last_m_error = mssql_get_last_message();

		return $res;
	}
	/**
	 * 
	 * Fetch data from query and set array
	 * 
	 * @param object $res
	 * @param string $type
	 * 
	 * @see SqlDriverInterface::fetch_array()
	 */
	function fetch_array($res, $type='BOTH'){
		$type_res = array(
			'BOTH' => MYSQLI_BOTH,
			'ASSOC' => MYSQLI_ASSOC,
			'NUM' => MYSQLI_NUM
		);
		return mssql_fetch_array($res, isset($type_res[$type]) ? $type_res[$type] : $type_res['BOTH']);
	}
	/**
	 * Numbre of rows
	 * 
	 * @param object $res
	 * 
	 * @see SqlDriverInterface::num_rows()
	 */
	function num_rows($res){
		return mssql_num_rows($res);
	}
	function lastId(){

	}
	/**
	 * Return error from the last query
	 * @see SqlDriverInterface::error()
	 */
	function error(){
		return $this->last_m_error;
	}
	/**
	 * 
	 * Return the exact db query error
	 * @see SqlDriverInterface::errno()
	 */
	function errno(){
		$result = mssql_query("select @@ERROR as [error]",$this->connect);
		$err = '';
		while($row = mssql_fetch_array($result)){
			$err = $row['error'];
			break;
		}
		if($err == 208) $err = 1146; // table not existing
		if($err == 207) $err = 1054; // field not existing
		if($err == 911) $err = 1049;

		return $err;
	}
	/**
	 * Close the connection to db server
	 * 
	 * @see SqlDriverInterface::close()
	 */
	function close(){
		return mssql_close($this->connect);
	}
	/**
	 * Show tables query
	 * @param string $base
	 * @see SqlDriverInterface::showTables()
	 */
	function showTables($base = ''){
		return mssql_query("sp_help", $this->connect);
	}
	/**
	 * 
	 * @todo not finished
	 * @see SqlDriverInterface::showCreateTables()
	 */
	function showCreateTables($table = ''){
		return null;
	}	
	/**
	 * Show columns
	 * 
	 * @param string $table
	 * @param string [$base]
	 * @see SqlDriverInterface::showFields()
	 */
	function showFields($table, $base = ''){
		return mssql_query("sp_columns ".$table." ", $this->connect);
	}
	/**
	 * Not supported yet
	 * 
	 * @param string $charset
	 */
	function setName($charset){
		die('No support yet');
	}
	/**
	 * Create database query
	 * 
	 * @param string $name
	 * @param string $charset
	 * @see SqlDriverInterface::createDatabase()
	 */
	function createDatabase($name, $charset){
		return $this->query(" CREATE DATABASE [".$name."] ");
	}
	/**
	 * Create table query
	 * 
	 * @param string $idname
	 * @param string $name
	 * @param object $data
	 * @param string $type
	 * @see SqlDriverInterface::createTable()
	 */
	function createTable($idname, $name, $data, $type = ''){
		$query = "CREATE TABLE ".$name."(\n";
		$query .= " ".$idname." int identity(1,1) NOT NULL,\n";

		$query .= $data;
		$query .= ");";

		return mssql_query($query, $this->connect);
	}
	/**
	 * Not supported yet
	 * @see SqlDriverInterface::createColumn()
	 */
	function createColumn($tbl,$data){
		die('No support yet');
	}
	/**
	 * Not supported yet
	 * @see SqlDriverInterface::drobColumn()
	 */
	function drobColumn($tbl, $name){
		die('No support yet');
	}
	/**
	 * 
	 * Not supported yet
	 * @param string $tblChild
	 * @param string $fieldChild
	 * @param string $tblParent
	 * @param string $fieldParent
	 */
	function createForeignKey($tblChild,$fieldChild,$tblParent,$fieldParent){
		die("No supported yet");
	}
	/**
	 * Add a limit to the query
	 *
	 * @param string $query
	 * @param string $SortField
	 * @param string $SortDirection
	 * @return string
	 */
	function limit($query,$from,$to,$SortField,$SortDirection){
		$query = str_replace("select", 'SELECT',$query);
		$query = str_replace("from", 'FROM',$query);

		$order = '';
		preg_match('/(.+)(order.+)/',$query,$match);
		if(isset($match[1])){
			$query = $match[1];
			$order = $match[2];
		}

		$query = str_replace("SELECT", "SELECT TOP ".$from,$query);
		$query = "SELECT * FROM (
			SELECT TOP ($to) * FROM (
				$query ORDER BY [$SortField] ".($SortDirection=="ASC"?"ASC":"DESC")."
			) as tbl1 ORDER BY [$SortField] ".($SortDirection!="ASC"?"ASC":"DESC")."
		) as tbl2 ".$order;//ORDER BY $SortField ".($SortDirection=="ASC"?"ASC":"DESC");

		return $query;
	}
	/**
	 * 
	 * getdate() function
	 * 
	 * @throws Exception
	 */
	function getCurTime(){
		return 'getdate()';
	}
}