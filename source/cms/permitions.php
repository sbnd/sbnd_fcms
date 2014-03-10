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
* @package cms.permitions
* @version 7.0.6
*/


/** 
 * 
 * Permissions manager class
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.4
 * @since 30.12.2011
 * @package cms.permitions
 */
class PermitionsManager implements PermitionInterface {
	/**
	 * 
	 * Permissions array data
	 * @var array
	 * @access private
	 */
	protected $perms = array();
	/**
	 * 
	 * Db table name
	 * @var string
	 * @access private
	 */
	protected $base = 'permitions';
	/**
	 * Get permission
	 * @see PermitionInterface::getPermission()
	 * @param string $cmp_owner
	 * @param string $perm_name
	 * @param int $level
	 */
	function getPermission($cmp_owner, $perm_name, $level = 0){
		if(!isset($this->perms[$level])){
			
			$this->perms[$level] = array();
			
			$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->base."` WHERE `_parent_id` = ".$level." ");
			
			$err = BASIC_ERROR::init()->error();
			if($err['code'] == 1146){
				$table = new BasicSqlTable();
				$table->field('cmp_name','varchar', 255);
				$table->field('access','text');
				$table->field('_parent_id','int', 11, false, 0);
				
				$table->key('_parent_id', 'foreign');
				
				if(BASIC_SQL::init()->createTable('id', $this->base, $table)){
					$GLOBALS['BASIC_ERROR']->clean();
					return $this->getPermission($cmp_owner, $perm_name, $level);
				}
				return false;
			}elseif($err['code'] == 1054){
				$table = new BasicSqlTable();
				
				preg_match("/column( name)? '([^']+)'/", $err['message'], $match);
				if(isset($match[2])){
					$spl = explode(".", $match[2]);
					$match[2] = $spl[count($spl) - 1];
					
					if($match[2] == 'cmp_name'){
						$table->field('cmp_name','varchar', 255);
					}else if($match[2] == 'access'){
						$table->field('access','text');
					}else if($match[2] == '_parent_id'){
						$table->field('_parent_id','int', 11, false, 0);
					}
					if(BASIC_SQL::init()->createColumn($this->base, $table)){	
						BASIC_ERROR::init()->clean();
						return $this->getPermission($cmp_owner, $perm_name, $level);
					}
				}
				return false;
			}
			while($rdr->read()){
				$this->perms[$level][$rdr->item('cmp_name')] = array();
				
				foreach(explode(",", $rdr->item('access')) as $v){
					$this->perms[$level][$rdr->item('cmp_name')][$v] = 1;
				}
			}
		}
		if(!isset($this->perms[$level]) || !isset($this->perms[$level][$cmp_owner]) || isset($this->perms[$level][$cmp_owner][$perm_name])){
			return true;
		}
		return false;
	}
	/**
	 * Set permission
	 * @see PermitionInterface::setPermission()
	 * @param string $cmp_owner
	 * @param string $perm_name
	 * @param  $status
	 * @param int $level
	 */
	function setPermission($cmp_owner, $perm_name, $status, $level = 0){
		if(!isset($this->perms[$level])) $this->perms[$level] = array();
		if(!isset($this->perms[$level][$cmp_owner])) $this->perms[$level][$cmp_owner] = array();
		
		if($status){
			$this->perms[$level][$cmp_owner][$perm_name] = 1;
		}else{
			unset($this->perms[$level][$cmp_owner][$perm_name]);
		}
		$tmp_access = ''; foreach($this->perms[$level][$cmp_owner] as $k => $v){
			if($tmp_access) $tmp_access .= ","; 
			
			$tmp_access .= $k;
		}
		
		if(BASIC_SQL::init()->read_exec(" SELECT 1 FROM `".$this->base."` WHERE 1=1 AND `cmp_name` = '".$cmp_owner."' AND `_parent_id` = ".$level." ")){
			
			BASIC_SQL::init()->exec(" UPDATE `".$this->base."` SET 
				`cmp_name` = '".$cmp_owner.",
				`access` = '".$tmp_access.",
				`_parent_id` = ".$level." ");
		}else{
			BASIC_SQL::init()->exec(" INSERT INTO `".$this->base."` (
					`cmp_name`, `access`, `_parent_id` 
				) VALUES (
					'".$cmp_owner."', '".$tmp_access."', ".$level."	
				)
			");
		}
	}
	/**
	 * Get database table name
	 * @see PermitionInterface::getBase()
	 */
	function getBase(){
		return $this->base;
	}
}
BASIC_USERS::init(array(
	'permition_manager' => new PermitionsManager()
));