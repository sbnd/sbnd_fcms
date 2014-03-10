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
* @package cms.controlers.front
* @version 7.0.6
*/


BASIC::init()->imported("ModuleSettings.cmp", 'cms/controlers/back');
/**
 * SearchBar target's interface
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @since 06.01.2012
 * @package cms.controlers.front
 */
interface SearchBarInterface{
	/**
	 * Get ArrayCollection from target components.
	 * 
	 * If search input value: "this is my+search criteria" then 
	 * $criteria == array(
	 * 		"this is my search criteria",
	 * 		"this",
	 * 		"is",
	 * 		"my search",
	 * 		"criteria",
	 * ) 
	 * 
	 * <code>
	 * 		$target->getMatchData(array("this is","my search")); 
	 * 		// == array(
	 * 		//  array(
	 * 		//		title => row 1,
	 * 		//		href => http://mydomayn.com/page1/art1,
	 * 		//  ), ...
	 * 		//)
	 * </code>
	 * 
	 * @param array $criteria
	 * @return ArrayCollection
	 */
	function getMatchData($criteria, $lenght_short_search_results);
}
/** 
 * 
 * 
 * Search bar class
 * 
 * @author Evgeni Baldzhiyski
 * @version 2.0
 * @package cms.controlers.front
 */
class SearchBar extends CmsComponent implements ModuleSettingsInterface{
	/**
	 * 
	 * Prefix
	 * @var string
	 * @access public
	 */
	public $prefix = 's';
	/**
	 * 
	 * User save state flag
	 * @var boolean
	 * @access public
	 */
	public $useSaveState = false;
	/**
	 * 
	 * The template filename
	 * @var string
	 * @access public
	 */
	public $template_form = 'cms-seach-form.tpl';
	/**
	 * 
	 * The template list filename
	 * @var string
	 * @access public
	 */
	public $template_list = 'cms-seach-list.tpl';
	/**
	 * 
	 * @todo description
	 * @var string
	 * @access public
	 */
	public $bar_field_id = 'text';
	/**
	 * 
	 * @todo description
	 * @var string
	 * @access public
	 */
	public $bar_action_id = 'earch';
	/**
	 * 
	 * @todo description
	 * @var string
	 * @access public
	 */
	public $bar_field_label = 'bar_field_label';
	/**
	 * 
	 * Search target array
	 * 
	 * @var array
	 * @access public
	 */
	public $search_targets = array();
	/**
	 * 
	 * Result page
	 * @var string
	 * @access public
	 */
	public $result_page = '';
	/**
	 * 
	 * Main function - the constructor of the component
	 * 
	 * @access public
	 * @see CmsComponent::main()
	 */
	 
	 public $lenght_short_search_results = 0;
	/**
	 * 
	 * Lenght short search results
	 * 
 	 * @var int
	 * @access public
	 */
	public function main(){
		parent::main();
		
		$this->setField($this->bar_field_id, array(
			'attributes' => array(
				'label' => BASIC_LANGUAGE::init()->get($this->bar_field_label),
				'id' => 'search_bar_field',
				'class'	=> 'span2 search-query'
			)
		));
		
		$this->delAction('save');
		$this->delAction('cancel');
		$this->delAction('add');
		$this->delAction('edit');
		$this->delAction('delete');
		
		$this->updateAction('list', 'ActionFormAdd');
		
		$this->addAction($this->bar_action_id, 'ActionSave', BASIC_LANGUAGE::init()->get('search'), 3);
	}
	/**
	 * 
	 * Create interface
	 * @see DysplayComponent::createInterface()
	 */
	function createInterface(){
		$this->cmd = '';
		
		if($this->pdata){
			$this->cmd = $this->bar_action_id;
		}
		return parent::createInterface();
	}
	/**
	 * 
	 * Return the html form
	 * @see DysplayComponent::ActionFormAdd()
	 */
	function ActionFormAdd(){
		if(!$this->result_page){
			return '';
		}else{
			$this->test();
			
			return $this->FORM_MANAGER(array(
				'action' => BASIC_URL::init()->link('/'.Builder::init()->pagesControler->getPageTreeByName($this->result_page))
			));
		}
	}
	
	/**
	 * 
	 * Every time will serch in pages + custom components.
	 * If search match string is empty will be skip search. In this case will return 0 results.
	 * 
	 * template variables map: array(
	 * 		page_data 		=> array with all component "pages"'s variables
	 * 		search_results 	=> array( - collection from search targets
	 * 			array(
	 * 				target_name 	=> string - serch-target's name
	 * 				search_results 	=> arrayCollection( - collection with hashmaps from target's view variables
	 * 
	 * 				)
	 * 				count_results   => count from all results
	 * 			), ... 
	 * 		)
	 * )
	 */
	function ActionSave(){
		$tpl_data = array(
			'search_results' => array(),
			'count_results' => 0
		);
		
		if(preg_match("/^[ ]+$/", $this->getDataBuffer($this->bar_field_id))){
			$this->setDataBuffer($this->bar_field_id, '');
		}
		
		if($this->getDataBuffer($this->bar_field_id)){
			$count = 0;
			$criteria = array(str_replace("+", " ", $this->getDataBuffer($this->bar_field_id)));
			foreach(explode(" ", $this->getDataBuffer($this->bar_field_id)) as $val){
				$criteria[] = str_replace("+", " ", str_replace("+", " ", $val));
			}
			
			$this->paging = new BasicComponentPaging($this->prefix);
			
			$sarr = array('' => 'pages')+$this->search_targets;
			
			$results = array();
			foreach($sarr as $val){
				if(BASIC_USERS::init()->getPermission($val, 'list')){
				
					$cmp = Builder::init()->build($val);
					if(!$cmp || !method_exists($cmp, 'getMatchData')) continue;
					
					if($res = $cmp->getMatchData($criteria, $this->lenght_short_search_results)){
	//					$count += count($res);
	//					
	//					$tpl_data['search_results'][] = array(
	//						'target_name' => $val, //@FIXME da se pokazvat public_name's
	//						'results' => $res
	//					);
	
					$temp_component = Builder::init()->getRegisterComponent($val);
					$temp_public_name = $temp_component->public_name;
	
						foreach ($res as $el){
							$el['target_name'] = $val;
							$el['public_name'] = $temp_public_name; 								
							
							$results[] = $el;
						}
					}
				}
			}
			$count = count($results);
			$this->paging->init($count, $this->maxrow);
			
			$tpl_data['search_results'] = $this->paging->filterArray($results);
			$tpl_data['count_results'] = $count;
			$tpl_data['paging_bar'] = $this->paging->getBar();
		}
		return BASIC_TEMPLATE2::init()->set($tpl_data, $this->template_list)
			->parse($this->template_list);
	}
	/**
	 * 
	 * Define the settings for the component. Values will be overwrite values of these class properties.
	 * 
	 * @return array
	 * @see ModuleSettingsInterface::settingsData()
	 */
	function settingsData(){
		return array(
			'useSaveState' 	=> $this->useSaveState,
			'template_form' => $this->template_form,
			'template_list' => $this->template_list,
			'prefix' 		=> $this->prefix,
			'search_targets'=> $this->search_targets,
			'result_page'	=> $this->result_page,
			'lenght_short_search_results'	=> $this->lenght_short_search_results,
		);
	}
	/**
	 * 
	 * Desciption of fields for component settings
	 * 
	 * @return array
	 * @see ModuleSettingsInterface::settingsUI()
	 */
	function settingsUI(){
		
		return array(
			'useSaveState' => array(
				'text' => BASIC_LANGUAGE::init()->get('useSaveState'),
				'formtype' => 'radio',
				'attributes' => array(
					'data' => array(
						BASIC_LANGUAGE::init()->get('no'),
						BASIC_LANGUAGE::init()->get('yes')
					)
				)
			),
			'template_form' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_form')
			),
			'template_list' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_list')
			),
			'prefix' => array(
				'text' => BASIC_LANGUAGE::init()->get('prefix')
			),
			'search_targets' => array(
				'text' => BASIC_LANGUAGE::init()->get('search_targets'),
				'formtype' => 'selectmove',
				'attributes' => array(
					'data' => Builder::init()->getdisplayComponent('modules', false)->genesateAssignList()
				)
			),
			'lenght_short_search_results' => array(
				'text' => BASIC_LANGUAGE::init()->get('lenght_short_search_results'),
				'dbtype' => 'int'
			),
			'result_page' => array(
				'text' => BASIC_LANGUAGE::init()->get('result_page'),
				'formtype' => 'select',
				'perm' => '*',
				'attributes' => array(
					'data' => Builder::init()->getdisplayComponent('pages')->getSelTree('', 0, 'name', "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;")
				)
			)
		);
	}
	/**
	 * 
	 * Settings format
	 * 
	 * @return $data
	 * @see ModuleSettingsInterface::settingsFormat()
	 */
	function settingsFormat($data){
		return $data;
	}
	/**
	 * SQL search criteria builder.
	 * 
	 * @param array $fields
	 * @param array $criteria
	 * @static
	 * @access public
	 */
	static public function buildSqlCriteria($fields, $criteria){
		$crt = '';
		foreach($fields as $v){
			
			foreach($criteria as $item){
				if($crt) $crt .= " OR ";
				
				$crt .= "`".$v."` LIKE '%".$item."%'";
			}
		}
		return $crt;
	}
	/**
	 * 
	 * Is require settings flag. Returns always true
	 * 
	 * @return boolean
	 */
	function isRequireSettings(){
		return true;
	}
	function prepareCofiguration($check = false, $owner = null){
		return false;
	}
}