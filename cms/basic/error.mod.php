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
* @package basic.error
* @version 7.0.4  
*/

/**
 * Global messages manager
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.0
 * @since 01.05.2006
 * @package basic.messages
 */
class BASIC_ERROR{
	/**
	 * BASIC_ERROR container
	 * 
	 * @access private
	 * @var hashmap using $typeRegister structure
	 */
	private $arrErr = array();
	/**
	 * temporary error register. 
	 * 
	 * @access private
	 * @var array
	 */
	private $_arrErr = array();
	/**
	 * temporary incrementer. Use from the method "error" for simulate while().
	 * 
	 * @access private
	 * @var int
	 */
	private $increment = -1;
	/**
	 * The message type collection using from $arrErr
	 * 
	 * @access protected
	 * @var array
	 */
	protected $typeRegister = array(
		'message' => array(0, 499),
		'warning' => array(500, 999),
		'fatal'   => array(1000)
	);
	/**
	 * Get BASIC_ERROR object using sigleton pattern
	 * 
	 * @static
	 * @access public
	 * @param array [$config]
	 * @return BASIC_ERROR
	 */
	static public function init($config = array()){
		if(!isset($GLOBALS['BASIC_ERROR'])){
			$GLOBALS['BASIC_ERROR'] = new BASIC_ERROR();
		}
		foreach ($config as $k => $v){
			$GLOBALS['BASIC_ERROR']->$k = $v;
		}
		return $GLOBALS['BASIC_ERROR'];
	}
	/**
	 * Add additional type to $typeRegister, using for describing type of errors
	 * @access
	 * @param string $type 
	 * @param int $from    
	 * @param int $to      
	 */
	function setTypeRegister($type, $from, $to){
		$this->typeRegister[$type] = array($from, $to);
	}
	/**
	 * Remove custom type from $typeRegister (using for describing type of errors)
	 * 
	 * @access public
	 * @param string $type
	 */
	function unsetTypeRegister($type){
		if($type != 'message' && $type != 'warning' && $type != 'fatal' && isset($this->typeRegister[$type])) unset($this->typeRegister[$type]);
	}
	/**
	 * Add error, warning or notice depends on its code to BASIC_ERROR container
	 * 
	 * @access public
	 * @param integer $code
	 * @param string $message
	 */
	function append($code, $message){
		$this->arrErr[$this->checkForType($code)][] = array('code' => $code, 'message' => $message);
	}
	/**
	 * Add error to BASIC_ERROR container
	 * 
	 * @access public
	 * @param string $txt
	 */
	function setError($txt){
		$this->arrErr['fatal'][] = array('code' => 1000, 'message' => $txt);
	}
	/**
	 * Add warning to BASIC_ERROR container
	 * 
	 * @access public
	 * @param string $txt
	 */
	function setWarning($txt){
		$this->arrErr['warning'][] = array('code' => 500, 'message' => $txt);
	}
	/** 
	 * Add message to BASIC_ERROR container
	 * 
	 * @access public
	 * @param string $txt
	 */
	function setMessage($txt){
		$this->arrErr['message'][] = array('code' => 0, 'message' => $txt);
	}
	/**
	 * Check type of error
	 * 
	 * @param integer $number
	 * @return string
	 */
	function checkForType($number){
		if(is_numeric($number)){
			foreach ($this->typeRegister as $k => $v){
				if($number >= $v[0] && (!isset($v[1]) || (isset($v[1]) && $number <= $v[1]))){
					return $k;
				}
			}
		}
		return 'fatal';
	}
	/**
	 * Return number of errors from type, defined in $type
	 * 
	 * @access public
	 * @param array|string [$type]
	 * @return integer
	 */
	function exist($type = ''){
		if($type){
			if(is_array($type)){
				$total = 0;
				foreach ($type as $v){
					if(isset($this->arrErr[$v])){
						$total += count($this->arrErr[$v]);
					}
				}
				return $total;
			}else{
				if(isset($this->arrErr[$type])){
					return count($this->arrErr[$type]);
				}
			}
		}else{
			$total = 0;
			foreach ($this->arrErr as $v){
				$total += count($v);
			}
			return $total;
		}
		return 0;
	}
	/**
	 * @access public
	 * @return array|false (array(code,message))
	 */
	function error($type = ''){

		if($type){
			$this->increment++;
			if(isset($this->arrErr[$type][$this->increment])){
				return $this->arrErr[$type][$this->increment];
			}
		}else{
			if($this->increment == -1){
				foreach ($this->arrErr as $v){
					foreach ($v as $V) $this->_arrErr[] = $V;
				}
			}
			$this->increment++;
			if(isset($this->_arrErr[$this->increment])){
				return $this->_arrErr[$this->increment];
			}
			$this->_arrErr = array();
		}

		$this->increment = -1;
		return false;
	}
	/**
	 * clear temporary properties.
	 * 
	 * @access public
	 * @return void
	 */
	function reset(){
		$this->_arrErr = array();
		$this->increment = -1;
	}
	/**
	 * Access to message buffer
	 * 
	 * @access public
	 * @param [$type]
	 * @return hashmap
	 */
	function getData($type = ''){
		if($type){
			if(isset($this->arrErr[$type])){
				return $this->arrErr[$type];
			}else{
				return array();
			}
		}
		return $this->arrErr;
	}
	/**
	 * Remove errors from BASIC_ERROR container
	 * @param string [$type]
	 * @return void
	 */
	function clean($type = ''){
		if($type){
			if(isset($this->arrErr[$type])){
				$this->arrErr[$type] = array();
			}
		}else{
			$this->arrErr = array();
		}
	}
}