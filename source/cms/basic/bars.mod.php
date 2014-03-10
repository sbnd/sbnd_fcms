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
* @package basic.bars
* @version 7.0.6  
*/

/**
 * This is standart workflow is: ComponentDisplay classes use this interface for building component's paging bars.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.bars
 */
interface BasicPagingInterface {
	/**
	 * @param integer $total_number
	 * @param integer $num_to_show
	 * @param string  $prefix
	 * @return void
	 */
	function init($total_number, $num_to_show = 10, $prefix = '');
	/**
	 * @return HashMap
	 */
	function getBar();
	/**
	 * @return String
	 **/
	function getSql();
}
/**
 * Standart ComponentDisplay's paging bar builder.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.bars
 */
class BasicComponentPaging extends BasicPaging implements BasicPagingInterface{
	
	function __construct($prefix = ''){
		$this->prefix = $prefix;
	}
	public function init($total_number, $num_to_show = 10, $prefix = ''){
		parent::__construct($total_number, $num_to_show, $prefix);
	}
	public function getBar(){
		return $this->getTemplateVars();
	}
	public function getSql(){
		return $this->limitCriteria();
	}
}
/**
 * Standart paging bars builder.
 * 
 * @author Evgeni Baldzisky
 * @version 2.0
 * @since 30.01.2007
 * @package basic.bars
 */
class BasicPaging{
	/**
	 * current page number
	 *
	 * @access private
	 * @var int
	 */
	protected $page = 1;
	/**
	 * paging bar links (url's path)
	 *
	 *@access public
	 * @var string
	 */
	public $script = '';
	/**
	 * Maximum results number
	 *
	 * @access private
	 * @var integer
	 */
	protected $total_number = 0;
	/**
	 * Maximum results number per page
	 *
	 * @access public
	 * @var integer
	 */
	public $num_to_show = 10;
	/**
	 * current page
	 * 
	 * @access private
	 * @var integer
	 */
	protected $num_pages = 1;
	/**
	 * @access public
	 * @var string
	 */
	public $prefix = '';
	/**
	 * url variable's name.
	 *
	 * @access public
	 * @var string
	 */
	public $page_url_var = 'page';
	/**
	 * template variables map.
	 *
	 * @access public
	 * @var array
	 */
	public $template_var_map = array(
		'min-link'  		=> 'min_link', 			// button link of first page
		'prev-link' 		=> 'prev_link',			// button link of previous page
		
		'max-link' 			=> 'max_link',			// button link of last page
		'next-link' 		=> 'next_link',			// button link of next page

		'current-page'		=> 'current_page',
		'max-pages' 		=> 'max_pages',		  	// max page numbers
	
		'show-all-items'	=> 'show_all_items',
		'show-from-items' 	=> 'show_from_items',
		'show-to-items' 	=> 'show_to_items',
	
		'prefix' 			=> 'prefix',			// using prefix
		
		'paging' 			=> 'pages',				// pages list
		'paging-current' 	=> 'current',
		'paging-link' 		=> 'link',
		'paging-number' 	=> 'number'
	);
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param int $total_number
	 * @param int [$num_to_show]
	 * @param string [$prefix]
	 * 
	 * @return void
	 */
	function __construct($total_number, $num_to_show = 10, $prefix = ''){
		if($prefix) $this->prefix = $prefix;
		
		$this->total_number = ($total_number > 0 ? $total_number : 1);
		$this->num_to_show = ($num_to_show > 0 ? $num_to_show : $this->total_number);
		
		$this->num_pages = ceil($this->total_number / $this->num_to_show);
		$this->script = BASIC::init()->scriptName();
		
		$this->refresh();
	}
	/**
	 * Refresh as set again current page
	 * 
	 * @access public
	 */
	function refresh(){
		//$this->page = 1;
		if($page = BASIC_URL::init()->request($this->prefix.$this->page_url_var)){
			$this->page = (int)$page;
		}
		if($this->page == 'min') $this->page = 1;
		if($this->page == 'max') $this->page = $this->num_pages;

		if($this->num_pages > 0 && $this->page > $this->num_pages){
			$this->page = $this->num_pages;
		}		
	}
	/**
	 * Set prefix
	 * 
	 * @access public
	 * @param string [$text]
	 * @return string|void
	 */
	function prefix($text = null){
		if($text === null){
			return $this->prefix;
		}
		$this->prefix = $text;
		$this->refresh();
	}
	/**
	 * Change object configuration. 
	 *
	 * @access public
	 * @param string $name
	 * @param mix [$value]
	 */
	function change($name, $value = ''){
		if(!$value){
			foreach ($name as $k => $v){
				$this->$k = $v;
			}
		}else{
			$this->$name = $value;
		}
		$this->__construct($this->total_number, $this->num_to_show, $this->prefix);
	}
	/**
	 * 
	 * Return current page
	 * 
	 * @access public
	 * @return integer
	 */
	function getPage(){
		return $this->page;
	}
	/**
	 * 
	 * Return number of all pages
	 * 
	 * @access public
	 * @return integer
	 */
	function getNumAllPages(){
		return $this->num_pages;
	}
	/**
	 * UI generator
	 * 
	 * valid attributes
	 * 	page_string - text for page list if does not exist template atribute
	 * 	template - basic template
	 * 	max
	 * 	min
	 *  next
	 *  prev
	 *  text_mode
	 * 
	 * @access public
	 * @param array [$attribute]
	 * @return string
	 */
	function show_page($attribute = array()){
		if($this->num_pages > 1){
			$attribute = BASIC_GENERATOR::init()->convertStringAtt($attribute);
			
			$ajax = array();
			if(isset($attribute['ajax'])){
				$ajax['ajax'] = $attribute['ajax'];
				unset($attribute['ajax']);
			}
			if(isset($attribute['bin'])){
				if(!isset($attribute['bin'])) die('No render !');
				$ajax['bin'] = $attribute['bin'];
				unset($attribute['bin']);
			}
			if(isset($attribute['group'])){
				$ajax['group'] = $attribute['group'];
				unset($attribute['group']);
			}
			if(isset($attribute['clean'])){
				$ajax['clean'] = $attribute['clean'];
				unset($attribute['clean']);
			}
			if(isset($attribute['state'])){
				$ajax['state'] = $attribute['state'].','.$this->prefix.$this->page_url_var;
			}else{
				$ajax['state'] = $this->prefix.$this->page_url_var;
			}
			$template = null;
			if(isset($attribute['template'])){
			    $template = $attribute['template'];
			    unset($attribute['template']);
			}
			$page_string = '';
			if(isset($attribute['page_string'])){
			    $page_string = $attribute['page_string'];
			    unset($attribute['page_string']);
			}
			$max = '&#187';
			if(isset($attribute['max'])){
			    $max = $attribute['max']; unset($attribute['max']);
			}
			$min = '&#171';
			if(isset($attribute['min'])){
			    $min = $attribute['min']; unset($attribute['min']);
			}
			$prev = '&#8249';
			if(isset($attribute['prev'])){
			    $prev = $attribute['prev']; unset($attribute['prev']);
			}
			$next = '&#8250';
			if(isset($attribute['next'])){
			    $next = $attribute['next']; unset($attribute['next']);
			}
			$text_mode = false;
			if(isset($attribute['text_mode'])){
			    $text_mode = $attribute['text_mode']; unset($attribute['text_mode']);
			}
			
			if($template){
     		    return BASIC_TEMPLATE2::init()->set($this->getTemplateVars(), $template)->parse($template);
			}else{
    			$tmp = "<tr>\n";
    			if($this->page == 1){
    				$tmp .=  '<td><span class="paging_arrows paging_disabled">'.$min.'</span></td>'."\n";
    				$tmp .=  '<td><span class="paging_arrows paging_disabled">'.$prev.'</span></td>'."\n";
    			}else{
    				$tmp .= BASIC_GENERATOR::init()->element('td',null,
    					BASIC_GENERATOR::init()->link('<span class="paging_arrows">'.$min.'</span>',$this->script."?".$this->prefix.$this->page_url_var."=1",$ajax)
    				);
    				$tmp .= BASIC_GENERATOR::init()->element('td',null,
    					BASIC_GENERATOR::init()->link('<span class="paging_arrows">'.$prev.'</span>',$this->script."?".$this->prefix.$this->page_url_var."=".($this->page-1),$ajax)
    				);
    			}
    			$tmp .=  '<td align="center" nowrap="nowrap">';
    			if ($text_mode == true) {
    				$tmp .= sprintf("%s <b>%d</b>/<b>%d</b> :: ", $page_string, $this->page, $this->num_pages);
    			}
    			$start = 1;
    			if ( ($this->page >= 6) && ($this->page <= $this->num_pages-6) ) {
    				$start = $this->page-3;
    			}elseif ($this->page >= $this->num_pages-6){
    				$start = $this->num_pages-6;
    			}
    			for ($i = $start; $i <=$start+6; $i++) { 
    				if ($i <= 0) continue;
    				if ($i == $this->page) {
    					$tmp .= BASIC_GENERATOR::init()->element('b',null,$i);
    				}else{
    					$tmp .= BASIC_GENERATOR::init()->link("[".$i."]",$this->script."?".$this->prefix.$this->page_url_var."=".($i),$ajax);
    				}
    			}
    			$tmp .= "</td>\n";
    			if ($this->num_pages <= $this->page) {
    				$tmp .=  '<td><span class="paging_arrows paging_disabled">'.$next.'</span></td>'."\n";
    				$tmp .=  '<td><span class="paging_arrows paging_disabled">'.$max.'</span></td>'."\n";
    			}else{
    				$tmp .= BASIC_GENERATOR::init()->element('td',null,
    					BASIC_GENERATOR::init()->link('<span class="paging_arrows">'.$next.'</span>',$this->script."?".$this->prefix.$this->page_url_var."=".($this->page+1),$ajax)
    				);
    				$tmp .= BASIC_GENERATOR::init()->element('td',null,
    					BASIC_GENERATOR::init()->link('<span class="paging_arrows">'.$max.'</span>',$this->script."?".$this->prefix.$this->page_url_var."=".($this->num_pages),$ajax)
    				);
    			}
    			$tmp .=  "</tr>\n";
    			return ($tmp ? BASIC_GENERATOR::init()->element('table', $attribute, $tmp) : '');
			}
		}
		return '';
	}
	/**
	 * Paging bar's variables for UI builders or templates.
	 * 
	 * @access public
	 * @return HashMap
	 */
	public function getTemplateVars(){
		$arr = array();
		if($this->num_pages > 1){
			$start = 1;
	    	if ( ($this->page >= 6) && ($this->page <= $this->num_pages-6) ) {
	    		$start = $this->page-3;
	    	}elseif ($this->page >= $this->num_pages-6){
	    		$start = $this->num_pages-6;
	    	}	
	    	$pages = array();
	    	$sclink = $this->script."?".BASIC_URL::init()->serialize(array($this->prefix.$this->page_url_var));
	    	for ($i = $start; $i <= $start+6; $i++) { 
	    		if ($i <= 0) continue;
	    		$pages[] = array(
	    			$this->template_var_map['paging-current'] => ($i == $this->page),
	    			$this->template_var_map['paging-number'] => $i,
	    			$this->template_var_map['paging-link'] => BASIC_URL::init()->link($sclink.$this->prefix.$this->page_url_var."=".($i))
	    		);
	    	}
	    	$from = (($this->page-1)*$this->num_to_show)+1;
	    	$to = (($this->page-1)*$this->num_to_show)+$this->num_to_show;
	    	if($to > $this->total_number){
	    		$to = $this->total_number;
	    	}
			$arr = array(
	    		$this->template_var_map['min-link'] 	  => ( $this->page == 1 ? '' : BASIC_URL::init()->link($sclink.$this->prefix.$this->page_url_var."=1")),
	    		$this->template_var_map['prev-link'] 	  => ( $this->page == 1 ? '' : BASIC_URL::init()->link($sclink.$this->prefix.$this->page_url_var."=".($this->page-1))),
	    			
	    		$this->template_var_map['max-link'] 	  => ($this->num_pages <= $this->page ? '' : BASIC_URL::init()->link($sclink.$this->prefix.$this->page_url_var."=".($this->num_pages))),
	    		$this->template_var_map['next-link'] 	  => ($this->num_pages <= $this->page ? '' : BASIC_URL::init()->link($sclink.$this->prefix.$this->page_url_var."=".($this->page+1))),
	    				
	    	    $this->template_var_map['current-page']   => $this->page,
	    	    $this->template_var_map['max-pages'] 	  => $this->num_pages,
	    	        
	    	    $this->template_var_map['show-all-items'] => $this->total_number,
	    	    $this->template_var_map['show-from-items']=> $from,
	    	    $this->template_var_map['show-to-items']  => $to,
	    	    
	    	    $this->template_var_map['prefix'] 		  => $this->prefix,    
	    		$this->template_var_map['paging']		  => $pages
	    	);
		}
    	return $arr;
	}
	/**
	 * The sql criteria builder. Use when the sql server is not MySQL  
	 *
	 * @access public
	 * @param string $query
	 * @param string [$field]
	 * @param string [$desc]
	 * 
	 * @return string
	 */
	function SQLLimit($query, $field = 0, $desc = 'ASC') {
		$num_show = 0;
		if($GLOBALS['BASIC_SQL']->server == 'mssql'){
			$num_show = $this->num_to_show;
		}

		return $GLOBALS['BASIC_SQL']->getLimit(
			$query,
			(($this->page-1)*$this->num_to_show+$num_show),
			$this->num_to_show,$field,
			$desc
		);
	}
	/**
	 * Specific MySQL sql criteria. Use this for more good performers.
	 *
	 * @access public
	 * @return string
	 */
	function limitCriteria(){
		return " LIMIT ".(($this->page-1)*$this->num_to_show).",".$this->num_to_show;
	}
	/**
	 * Get space from results list.
	 *
	 * @access public
	 * @param string $range [max|min]
	 * @return integer|HashMap
	 */
	function getSpace($range = ''){
		$num_show = 0;
		if(BASIC_SQL::init()->server == 'mssql'){
			$num_show = $this->num_to_show;
		}
		
		$min = (($this->page-1)*$this->num_to_show+$num_show);
		$max = $min + $this->num_to_show;
		
		if($range == 'min'){
			return $min;
		}else if($range == 'max'){
			return $max;
		}else{
			return array(
				'from' => $min,
				'to' => $max		
			);
		}
	}
	/**
	 * Get space from array.
	 * 
	 * @access public
	 * @param array $array
	 * @param boolean [$isHash]  filter's algorithm
	 * @return array
	 * @version 0.2
	 */
	function filterArray($array, $isHash = false){
		$space = $this->getSpace();
		
		$max = $space['to'];
		$min = $space['from'];
		$count = count($array);
		
		$tmp = array();
		
		if($min >= $count || !$count) return $tmp;
		
		if($isHash){
			$i = 0; foreach($array as $k => $v){
				if($i >= $min && $i < $max){
					$tmp[$k] = $v;
				}
				$i++;
				if($i == $max) break;
			}
		}else{
			for ($i = $min;$i < $count; $i++){
				if($i >= $min && $i < $max){
					$tmp[] = $array[$i];
				}
				if($i >= $max) break;
			}
		}
		return $tmp;
	}
	
	// Checkers 
	/**
	 * Help method for getIsFrom, getIsTo and getIsRange
	 * 
	 * @access private
	 * @param integer $num
	 * @param string [$type]
	 * @return boolean
	 */
	protected function _testRange($num,$type = ''){
	    $range = $this->getSpace();
	    if($type == 'from'){
	        if($num < $range['from']) return false;
	    }else if($type == 'to'){
	        if($num >= $range['to']) return false;
	    }else{
	       if($num < $range['from'] || $num >= $range['to']) return false;
	    }
	    return true;
	}
	/**
	 * Get result is < from space
	 * 
	 * @access public
	 * @param int $num
	 * @return boolen
	 */
	function getIsFrom($num){
	    return $this->__testRange($num,'from');
	}
	/**
	 * Get result is > from space
	 * 
	 * @access public
	 * @param int $num
	 * @return boolen
	 */
	function getIsTo($num){
	    return $this->_testRange($num,'to');
	}
	/**
	 * get result is > from space < 
	 *
	 * @access public
	 * @param int $num
	 * @return boolen
	 */
	function getIsRange($num){
	    return $this->_testRange($num);
	}
	/**
	 * Return number of pages
	 * 
	 * @access public
	 * @return integer
	 */
	function getNumberPages(){
		return $this->num_pages;
	}
}
/**
 * Use from ComponentDisplays for build column headers and make order by sql criteria.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package basic.bars
 */
interface BasicSortingInterface{
	function sortlink($column, $text, $attribute = array(), $miss = array());
	function getPrefix();
	function createUrlForLink($column, $miss = array());
	function getsql();
}
/**
 * @author Evgeni Baldzhiski
 * @version 0.8 
 * @since 10.02.2007
 * @package basic.bars
 */
class BasicSorting implements BasicSortingInterface{
	/**
	 * The name of the URL variable describing ordering column name
	 * 
	 * @staticvar
	 * @access public
	 * @var string
	 */
	public static $column_var_name = 'column';
	/**
	 * The name of the URL variable describing ordering direction (DESC, ASC)
	 * 
	 * @staticvar
	 * @access public
	 * @var string
	 */
	public static $desk_var_name = 'dir';
	
	/**
	 * Prefix for url variables
	 * 
	 * @access public
	 * @var string
	 */
	public $prefix = '';
	/**
	 * Sorting direction
	 *
	 * @access private
	 * @var integer
	 */
	protected $dir = -1;
	/**
	 * The name of field name for the URL variable.
	 *
	 *@access private
	 * @var string
	 */
	protected $column = '';
	/**
	 * Temporary property for save valid for sql colimns names.
	 * 
	 * @access private
	 * @var string
	 */
	protected $clean_column = '';
	/**
	 * Default sorting column
	 * 
	 * @access private
	 * @var string
	 */
	protected $default = '';
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param array|string [$default]  columns for ORDER BY clause
	 * @param bollean [$desc]		   sorting direction
	 * @param string [$prefix]		   prefix for url variables
	 */
	function __construct($default = '', $desc = false, $prefix = ''){
		
		$this->default = $default;
		$this->prefix = $prefix;
		
		$this->dir = ($desc ? 1 : 0);
		
		$this->refresh();
	}
	/**
	 * Return or set prefix
	 * 
	 * @access public
	 * @param string [$text]
	 * @return string|void
	 */
	public function prefix($text = null){
		if($text === null){
			return $this->prefix;
		}
		$this->prefix = $text;
		$this->refresh();
	}
	/**
	 * Get prefix
	 * 
	 * @access public
	 * @return string
	 * @see BasicSortingInterface::getPrefix()
	 */
	public function getPrefix(){
		return $this->prefix;
	}
	/**
	 * Create Url for link
	 * 
	 * @access public
	 * @param string $column
	 * @param array [$miss]
	 * @return string 
	 * @see BasicSortingInterface::createUrlForLink()
	 */
	public function createUrlForLink($column, $miss = array()){
		$miss[] = $this->prefix.self::$column_var_name;
		$miss[] = $this->prefix.self::$desk_var_name;		
		
		return BASIC_URL::init()->link(BASIC::init()->scriptName(), BASIC_URL::init()->serialize($miss).
			$this->prefix.self::$column_var_name."=".$column."&".$this->prefix.self::$desk_var_name."=".(str_replace("`", "", $this->clean_column) == $column ? (int)(!$this->dir) : $this->dir)
		);
	}
	/**
	 *	HTML link generator.
	 * 
	 * @access public
	 * @param string $column
	 * @param string $text
	 * @param array [$attribute] tag attributes
	 * @param array [$miss] 	 variables which don't have to be sent in the state
	 * @return string
	 */
	public function sortlink($column, $text, $attribute = array(), $miss = array()){
		$attribute = BASIC_GENERATOR::init()->convertStringAtt($attribute);
		
		$attribute['href'] = $this->createUrlForLink($column, $miss);
		
		return BASIC_GENERATOR::init()->element("a", $attribute, $text);
	}
	/**
	 * 	SQL sorting criteria
	 * 
	 *	<code>
	 * 		$sorting = new sorting('start_sortable_column_name','prefix_string');
	 * 
	 * 		$rdr = BASIC_SQL::init()->read_exec(" 
	 * 			SELECT * FROM `table_name` WHERE 1=1 ".$sorting->getsql()." 
	 * 		");
	 * 	</code>
	 * 
	 * @access public
	 * @return string
	 */
	public function getsql(){
		if($this->column){
			$criteria = '';
			foreach(explode(",", $this->column) as $column){
				if($criteria) $criteria .= ",";
				
				$criteria .= $column.($this->dir ? ' DESC' : ' ');
			}
			return " ORDER BY ".$criteria;
		}
		return '';
	}
	/**
	 * version 0.2 support sort by multy properties.
	 * 
	 * @author Evgeni Baldziyski
	 * @version 0.2
	 * @since 15.12.2011
	 * 
	 * @access public
	 * @param arrayCollection $coll
	 * @throws Exception
	 * @return arrayCollection
	 */
	public function sortCollection($coll){
		if(!$coll) return array();
	
		$sort_prop = explode(",", str_replace("`", "", $this->column));
		
		$sortable = array();
		$tmp_hash = array();
		
		$err = '';
		foreach($sort_prop as $v){
			if(!isset($coll[0][$v])){ 
				$err .= " ".$v;
			}
		}
		if($err){
			throw new Exception('Invalid property name: "'.$err.'". ', 1001); return null;
		}
		
		foreach ($coll as $i => $row){

			$key = '';
			foreach($sort_prop as $v){
				$key .= $row[$v];
			}
			
			if(isset($tmp_hash[$key])){
				$key .= $i;
			}
			
			$sortable[] = $key;
			$tmp_hash[$key] = $row;
		}
		$coll = array();
		
		if($this->dir){
			rsort($sortable);
		}else{
			sort($sortable);
		}
		
		foreach($sortable as $v){
			$coll[] = $tmp_hash[$v];
		}
		return $coll;
	}
	/**
	 * Check if exists ordering by column (the parameter).
	 * 
	 * @access public
	 * @param string $column
	 * @return boolean
	 */
	public function selected($column){
		return ($column == str_replace("`", "", $this->column));
	}
	/**
	 * Check if ordering is DESC.
	 * 
	 * @access public
	 * @return boolean
	 */
	public function isDown(){
		return !!$this->dir;
	}
	/**
	 * Change ordering to DESC.
	 * 
	 * @access public
	 * @param boolean [$desc] true if the order is DESC
	 * @return void
	 */
	public function setDown($desc = true){
		$this->dir = ($desc ? 1 : 0);
	}
	/**
	 * Check URL request and setup system variables.
	 * 
	 * @access private
	 * @return void
	 */
	protected function refresh(){
		if(BASIC_URL::init()->test($this->prefix.self::$desk_var_name)){
			$this->dir = (int)BASIC_URL::init()->request($this->prefix.self::$desk_var_name);
		}
		$this->column = '';
		if(BASIC_URL::init()->test($this->prefix.self::$column_var_name)){
			$this->column = BASIC_URL::init()->request($this->prefix.self::$column_var_name, 'addslashes');
		}
		$this->column = $this->_getColumn();
	}
	/**
	 * Get column
	 * 
	 * @access private
	 * @return string
	 */
	protected function _getColumn(){
		$this->clean_column = '';
		if(!$this->column){
			if(is_array($this->default)){
				foreach ($this->default as $v){
					if($this->clean_column) $this->clean_column = ',';
					
					$this->clean_column = $v;
				}
			}else{
				$this->clean_column = $this->default;
			}
		}else{
			$this->clean_column = $this->column;
		}
		return $this->_cleancolumn($this->clean_column);
	}
	/**
	 * Clean column name
	 * 
	 * @param string|array $column
	 * @return string
	 */
	protected function _cleancolumn($column){
		if($column){
			$tmp = '';
			foreach(explode(',',$column) as $k => $v){
				preg_match("/^(([^\.]+)\.)?`?([a-zA-Z_0-9]+)`?$/", $v, $reg);
	
				if(isset($reg[2]) && $reg[2] != ''){
					$tmp .= $reg[2].".`".$reg[3]."`,";
				}else{
					$tmp .= "`".$reg[3]."`,";
				}	
			}
			return substr($tmp,0,-1);
		}
		return '';
	}
}