<?php
class ModuleFields extends CmsComponent {
	/**
	 * @var CmsComponent
	 * @access private
	 */
	private $parent_build = null;
	/**
	 * @var CmsComponent
	 * @access private
	 */
	private $module_build = null;
	
	function main(){
		parent::main();
			
		$this->setField('name', array(
			'text' => BASIC_LANGUAGE::init()->get('name') 
		));
		$this->setField('text', array(
			'text' => BASIC_LANGUAGE::init()->get('text'),
			'lingual' => true
		));
		$this->setField('perm', array(
			'text' => BASIC_LANGUAGE::init()->get('perm')
		));
		
		$this->setField('dbtype', array(
			'text' => BASIC_LANGUAGE::init()->get('dbtype'),
			'formtype' => 'select',
			'attributes' => array(
				'data' => array(
					'varchar' => 'varchar',
					'int' => 'int',
					'text' => 'text',
					'longtext' => 'longtext',
				)
			)
		));
		$this->setField('length', array(
			'text' => BASIC_LANGUAGE::init()->get('length'),
			'dbtype' => 'int'
		));
		
		$formtypes = $fdata = array();
		foreach(BASIC_GENERATOR::init()->getControls() as $k => $v){
			$formtypes[$k] = $v->specificParameters();
			$fdata[$k] = $k;
		}
		$this->setField('formtype', array(
			'text' => BASIC_LANGUAGE::init()->get('formtype'),
			'attributes' => array(
				'data' => $fdata
			)
		));
		
		$this->setField('attributes', array(
			'text' => BASIC_LANGUAGE::init()->get('modul_cmp_settings_label'),
			'formtype' => 'selectmanage',
			'dbtype' => 'text',
			'attributes' => array(
				"del" => BASIC_LANGUAGE::init()->get('delete_attribute'),
				'data' => array(
				
				)
			)
		));
	}
	/**
	 * Check request for action's variables.
	 *
	 * @see CmsComponent::loadURLActions()
	 */
	function loadURLActions(){
		parent::loadURLActions();
	
		$this->parent_build = $this->buildParent();
	
		$res = $this->parent_build->getRecord($this->parent_id);
	
		if(!isset(Builder::init()->model[$res['name']])){
			Builder::init()->registerComponent('#'.$res['name'], array(
				'class' => $res['class'],
				'folder' => $res['folder']
			));
		}
		$this->module_build = Builder::init()->build($res['name'], false);
		
		foreach($this->module_build->fields as $k => $v){
			
		}
	}	
}