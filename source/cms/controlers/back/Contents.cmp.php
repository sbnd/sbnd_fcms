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
* @package cms.controlers.back
* @version 7.0.4  
*/

/**
 * Component for site pages management and navigations
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.2
 * @since 17.10.2012
 * @package cms.controlers.back
 */
class Contents extends Tree{
    /**
     * Name of registered component - 'pages'
     * @access public
     * @var string
     */
	public $controler = 'pages';
	/**
	 * Container of menu names - top, bottom, main 
	 * @access private
	 * @var array
	 */
	protected $positions = array();
	/**
	 * @access private
	 * @var array
	 */
	protected $targets = array();
	/**
	 * @access private
	 * @var array
	 */
	protected $targetActions = array();
	/**
	 * Contains Pages component object cms.controlers.Pages.cmp.php
	 * @access private
	 * @var object Pages
	 */
	protected $ctrl = null;
	/**
	 * 
	 * Main function - the constructor of the component
	 * @access public
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();
		
		$this->ctrl = Builder::init()->build($this->controler);
		
		$this->base = $this->ctrl->base;
		
		foreach ($this->ctrl->fields as $k => $v){
			$this->setField($k, $this->ctrl->getField($k));
		}
		
		$this->targetActions = $this->ctrl->getTargetActions();
		
		$this->specialTest = 'beforeSave';
		$this->treeTitleItem = 'name';
	
		$tmp = $this->actions;
		$this->actions = array();
		
		$this->addAction('preview', 'ActionPreview', BASIC_LANGUAGE::init()->get('preview'), 2, 'javascript:this.target = \'_blank\'');
		
		// re-order actions
		foreach($tmp as $k => $v){
			$this->actions[$k] = $v;
		}
		if(isset($this->fields['position']) && BASIC_USERS::init()->getPermission('menu-positions', 'list')){
			$this->addAction('menu-positions', 'goToParent', BASIC_LANGUAGE::init()->get('cms_cmp_menu_positions'));
		}		
		
		$this->setCmpPermition('add',    $this->actions['add'][2]);
		$this->setCmpPermition('edit',   $this->actions['edit'][2]);
		$this->setCmpPermition('delete', $this->actions['delete'][2]);
		
		$this->setCmpPermition('cmp', BASIC_LANGUAGE::init()->get('component_name'));
	}
	/**
	 * Load array with menu positions - top, bottom, main,...
	 * 
	 * @access private
	 * @return array
	 */
	protected function getPositions(){
		if(!$this->positions){
			$this->positions = Builder::init()->build('menu-positions', false)->read()->getSelectData('id', 'name');
		}
		return $this->positions;
	}
	/**
	 * Action in list view for each row generate link "Preview", which open page in frontend 
	 * 
	 * @access public
	 * @param integer $id
	 * @return void
	 */
	function ActionPreview($id){
		$res = $this->getRecord($id);
		
		BASIC_URL::init()->redirect('/'.BASIC_LANGUAGE::init()->current().'/'.$this->ctrl->getPageTreeById($id));
	}
	/**
	 * Used in ActionList for definding/mapping columns in list view
	 * 
	 * @access public
	 * @return void
	 */
	function mapping(){
		$this->map('name', 			BASIC_LANGUAGE::init()->get('content_name_label'), 	'mapFormatter', 'align=left');
		$this->map('title', 		BASIC_LANGUAGE::init()->get('title'), 				'mapFormatter');
		$this->map('target', 		BASIC_LANGUAGE::init()->get('target'), 				'mapFormatter');
		$this->map('component_name',BASIC_LANGUAGE::init()->get('component_name'), 		'mapFormatter');
		
		if(isset($this->fields['position'])){
			$this->map('position', 		BASIC_LANGUAGE::init()->get('position'), 			'mapFormatter');
		}
		
		$this->map('publish', 		BASIC_LANGUAGE::init()->get('content_pblish_label'),'mapFormatter', 'nowrap=nowrap');
	}
	/**
	 * Extends parent method like adding column mapping and set filter fields
	 * 
	 * @access public
	 * @return string
	 */
	function ActionList(){
		$this->mapping();
		
		$this->filter = new BasicFilter($this->prefix);
		$this->filter->template($this->template_filter);
		$this->filter->field('pname', array(
			'text' => BASIC_LANGUAGE::init()->get('content_name_label'),
			'filter' => " AND `name` LIKE '%{v}%' "
		));
		$this->filter->field('pbody', array(
			'text' => BASIC_LANGUAGE::init()->get('content_body_label'),
			'filter' => " AND `body` LIKE '%{v}%'"
		));
		$this->filter->field('pcomponent_name', array(
			'text' => BASIC_LANGUAGE::init()->get('component_name'),
			'filter' => " AND `component_name` LIKE '%{v}%' "
		));
		$this->filter->field('ppublish', array(
			'text' => BASIC_LANGUAGE::init()->get('content_pblish_label'),
			'formtype' => 'radio',
			'filter' => " AND `publish` = ({v} - 1) ",// mahnati sa %{v}% na zaiavkata za da ne vrashta error v pages -> filter po show languages
			'dbtype' => 'int',
			'length' => 1,
			'attributes' => array(
				'data' => array(
					'' => BASIC_LANGUAGE::init()->get('all'),
					1 => BASIC_LANGUAGE::init()->get('no'), 
					2 => BASIC_LANGUAGE::init()->get('yes')
				)
			)
		));
		return parent::ActionList();
	}
	/**
	 * Used already overrided ActionFormEdit without id parameter
	 * @access public
	 * @return string
	 */
	public function ActionFormAdd(){
		return $this->ActionFormEdit();	
	}
	/**
	 * Extends paren method like adding parent_self field and update some component fields
	 * 
	 * @access public
	 * @param integer [$id]
	 */
	public function ActionFormEdit($id = 0){
		if($id && !$this->messages){
			$this->ActionLoad($id);
		}
		if($id != 0){
		$this->actions['save'][2] = BASIC_LANGUAGE::init()->get('update');
		}
		$this->getParentSelfElement('', '', ' AND `id` != '.(int)$this->id);
		$this->updateField('position', array(
			'attributes' => array(
				'data' => $this->getPositions()
			)
		));
		
		$scr = 'var types = {}; '."\n";
		foreach ($this->targetActions as $k => $v){
			$this->targets[$k] = $v->getOptionName();
			$attrs = ''; foreach($v->getAttributes() as $key => $val){
				if($attrs) $attrs .= "&"; $attrs .= $key."=".$val;
			}
			$scr .= "	types['".$k."'] = '".$attrs."';\n";
		}
		BASIC_GENERATOR::init()->head('target_manager', 'script', null, "$(document).ready(function (){
			".$scr."
			$('#pagetarget').change(function (){
				$('#pagetarget_params').attr('value', types[this.value])
					.parent().parent().parent()[types[this.value] ? 'show' : 'hide']();
			}).change();
		});");
		
		$this->updateField('target', array(
			'attributes' => array(
				'data' => $this->targets
			)
		));
		if(!$id || ($id && !$this->getDataBuffer('location'))){
			$this->setDataBuffer('location', BASIC::init()->ini_get('root_virtual'));
		}
		if(BASIC_USERS::init()->getPermission('contents', 'cmp')){
			$this->updateField('component_name', array(
				'attributes' => array(
					'data' => Builder::init()->build('modules', false)->genesateAssignList(array('' => ' '))
				)
			));
		}else{
			$this->unsetField('component_name');
		}
		
		$this->setDataBuffer('location', str_replace('${INSIDE}', BASIC::init()->virtual(), $this->getDataBuffer('location')));
		
		return $this->FORM_MANAGER();
	}
	/**
	 * Set value for 'location' field, its name is saved in $this->specialTest = 'beforeSave';
	 * 
	 * @access public
	 * @return void
	 */
	function beforeSave(){
		$ex = explode(BASIC::init()->ini_get('root_virtual') ,$this->getDataBuffer('location'));
		if(isset($ex[1])){	
			$ex[1] = '${INSIDE}'.str_replace('${INSIDE}', '', $ex[1]);
			$this->setDataBuffer('location', $ex[1]);
		}
	}
	/**
	 * Return component html, which value is set in CONTENT template variable in base template
	 * @see CmsComponent::startPanel()
	 */
	function startPanel(){
		$this->startManager();
		return $this->createInterface();
	}
	/**
	 * Format cells in list view
	 * 
	 * @access public
	 * @param string $val
	 * @param string $name
	 * @param array $row
	 * @return string
	 */
	function mapFormatter($val, $name, $row){
		if($name == 'target'){
			if(!$this->targets){
				foreach ($this->targetActions as $k => $v){
					$this->targets[$k] = $v->getOptionName();
				}
			}
			return isset($this->targets[$val]) ? 
				$this->targets[$val]/*.($val == '_popup' || $val == '_modal' ? "(".$row['target_params'].")" : "")*/ : $this->targets['_self'];
		}
		if($name == 'position'){
			$pp = $this->getPositions();
			$tmp = ''; foreach($val as $v){
				if($tmp) $tmp .= " ,";
				$tmp .= isset($pp[$v]) ? $pp[$v] : $v;
			}
			return $tmp;
		}
		if($name == 'publish'){
			$tmp = '';
			while($l = BASIC_LANGUAGE::init()->listing()){
				if(isset($row['publish_'.$l['code']]) && $row['publish_'.$l['code']]){
					if($tmp) $tmp .= ", ";
					
					$tmp .= $l['text'];
				}
			}
			return $tmp;
		}
		return $val;
	}
	/**
	 * Extends parent method like changePublishTree and clear menu cache
	 * 
	 * @access public
	 * @param integer $id
	 * @return integer
	 */
	function ActionSave($id){
		if($id = parent::ActionSave($id)){
			while ($l = BASIC_LANGUAGE::init()->listing()){
				if(isset($this->dataBuffer['publish_'.$l['code']])){
					$this->ctrl->changePublishTree($id, $l['code'], $this->getDataBuffer('publish_'.$l['code']));
				}
			}
			$this->ctrl->clearMenuCash();
		}
		return $id;
	}	
	/**
	 * Change record order, clear menu cache
	 * 
	 * @access public
	 * @return void
	 */
	function ActionOrder($id, $action = '', $criteria = ''){
		$this->ctrl->clearMenuCash();
		
		return parent::ActionOrder($id, $action, $criteria);
	}
	/**
	 * Extends parent method like adding cleanin menu cache
	 * 
	 * @access public
	 * @return void
	 */
	function ActionRemove($id, $rules = ''){
		$this->ctrl->clearMenuCash();
		
		return parent::ActionRemove($id, $rules);
	}	
	/**
	 * Generate sql query that get all data of component db table
	 * 
	 * @access public
	 * @return string
	 */
	function select($criteria = '', $include_all = false){
		
		return parent::select($criteria, true);
	}
}