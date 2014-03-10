<?php
class TinyMCEComponent extends CmsBox{
	public $manager =  "";
	
	function startPanel(){
		return 'Miss UI for this component';
	}
	function prepareCofiguration($check = false, $owner = null){
		if($check) return true;
		
		BASIC::init()->imported('TinyMCEControle', BASIC::init()->package(__FILE__));
		
		$tinyMCE = new TinyMCEControle();
		$tinyMCE->manager = Builder::init()->build($owner->system_name)->manager;
		
		BASIC_GENERATOR::init()->registrateControle('html', $tinyMCE);
	}
	/**
	 * Define setting for component. Values will be overwrite values of these class properties
	 *
	 * @access public
	 * @return array
	 */
	function settingsData(){
		return array(
			'manager' => $this->manager
		);
	}
	/**
	 *
	 * Desciption of fields for component setting
	 * @access public
	 * @return array
	 */
	function settingsUI(){
		$cmps = array();
		foreach(Builder::init()->build('modules', false)->genesateAssignList(array('' => ' ')) as $k => $v){
			if(!$k){
				$cmps[$k] = $v;
			}else{
				$cmp = Builder::init()->build($k, false);
				if($cmp instanceof AssetManager){
					$cmps[$k] = $v;
				}
			}
		}
		
		return array(
			'manager' => array(
				'text' => BASIC_LANGUAGE::init()->get('manager'),
				'formtype' => 'select',
				'attributes' => array(
					'data' => $cmps
				)
			)
		);
	}
}