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
 * Component for managing list with supported languages
 * @author Evgeni Baldzhiyski
 * @package cms.controlers.back
 */
class Languages extends CmsComponent{
	/**
	 * Contain field that will be use instead title in breadcrumb ('code')
	 * @access public
	 * @var string
	 */
	public $breadcrumps = 'code';
	/**
	 * Component db table - 'language'
	 * @access public
	 * @var string
	 */
	public $base = 'language';
	/**
	 * Contains folder for uploading
	 * @access public
	 * @var string
	 */
	public $upload_folder = '';
	/**
	 * Main function - the constructor of the component
	 * @access public
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();
		
		$this->base = 'languages';
		if(!$this->upload_folder) $this->upload_folder = BASIC::init()->ini_get('upload_path');
		
		$this->setField('code', array(
			'text' => BASIC_LANGUAGE::init()->get('language_code_label'),
			'perm' => '*',
			'length' => 5,
			'messages' => array(
				2 => BASIC_LANGUAGE::init()->get('invalid_lang_code_characters')
			)
		));	
		$this->setField('text',array(
			'text' => BASIC_LANGUAGE::init()->get('language_title_label')
		));
		$this->setField('encode',array(
			'text' => BASIC_LANGUAGE::init()->get('language_encode_label'),
			'perm' => '*',
			'default' => 'utf-8'
		));
		$this->setField('publish', array(
			'text' => BASIC_LANGUAGE::init()->get('langualge_publish_lanel'),
			'dbtype' => 'int',
			'length' => 1,
			'formtype' => 'radio',
			'default' => 1,
			'attributes' => array(
				'data' => array(
					BASIC_LANGUAGE::init()->get('no'),
					BASIC_LANGUAGE::init()->get('yes')
				)
			)
		));
		$this->setField('flag', array(
			'text' => BASIC_LANGUAGE::init()->get('langualge_flag_lanel'),
			'formtype' => 'file',
			'attributes' => array(
				'max' 	 => '150K',
				'rand'   => 'true',
				'as' 	 => 'FLAG',
				'preview'=> '50,50',
				'dir' 	 => $this->upload_folder,
				'perm' 	 => 'jpg,gif,png,ico',
				'delete_btn' => array(
					'text' => BASIC_LANGUAGE::init()->get('delete')
				)	
			),
			'messages' => array(
				1  => BASIC_LANGUAGE::init()->get('upoad_error_1'),
				2  => BASIC_LANGUAGE::init()->get('upoad_error_2'),
				3  => BASIC_LANGUAGE::init()->get('upoad_error_3'),
				4  => BASIC_LANGUAGE::init()->get('upoad_error_4'),
				5  => BASIC_LANGUAGE::init()->get('upoad_error_5'),
				10 => BASIC_LANGUAGE::init()->get('upoad_error_10'),
				11 => BASIC_LANGUAGE::init()->get('upoad_error_11'),
				12 => BASIC_LANGUAGE::init()->get('upoad_error_12'),
				13 => BASIC_LANGUAGE::init()->get('upoad_error_13'),
				14 => BASIC_LANGUAGE::init()->get('upoad_error_14'),
				15 => BASIC_LANGUAGE::init()->get('upoad_error_15'),
				16 => BASIC_LANGUAGE::init()->get('upoad_error_16')
			)
		));
		
		$this->specialTest = 'validator';
		
		$this->addAction('import',  'ActionOpenImport', BASIC_LANGUAGE::init()->get('Import'), 2);
		$this->addAction('export',  'ActionExport', BASIC_LANGUAGE::init()->get('Export'), 2);
		$this->addAction('_import', 'ActionImport', BASIC_LANGUAGE::init()->get('Import'), 0);
		
		$this->ordering(true);
		$this->updateAction('__childlingual', null, BASIC_LANGUAGE::init()->get('language_open'));
	}
	/**
	 * Extends parent method like update code field to readonly
	 * 
	 * @access public
	 * @param integer $id
	 * @return string
	 */
	function ActionFormEdit($id){
		if(!$this->getMessage('code')){
			$this->updateField("code", array(
				'attributes' => array(
					'readonly' => true
				)
			));
		}
		return parent::ActionFormEdit($id);
	}
	/**
	 * Map columns for action list and return html for list view
	 * @access public
	 * @return string
	 */
	function ActionList(){
		
		$this->map('text', BASIC_LANGUAGE::init()->get('language_title_label'), 'formater');
		$this->map('code', BASIC_LANGUAGE::init()->get('language_code_label'), 'formater', 'width=1');
		$this->map('encode', BASIC_LANGUAGE::init()->get('language_encode_label'), 'formater');
		$this->map('publish', BASIC_LANGUAGE::init()->get('langualge_publish_lanel'), 'formater');
		$this->map('flag', BASIC_LANGUAGE::init()->get('langualge_flag_lanel'), 'formater',  'width=1', false);

		return parent::ActionList();
	}
	/**
	 * Extends parent method like disable row with default language
	 * @access public
	 * @return void
	 */
	function ActionLoad($id){
		parent::ActionLoad($id);
		
		if($id && BASIC_LANGUAGE::init()->default_() == $this->getDataBuffer('code')){
			$this->updateField('publish', array(
				'attributes' => array(
					'disabled' => 'disabled'
				)
			));
		}
	}
	/**
	 * Method called before save, its name is saved in parent specialTest property
	 * 
	 * @access public
	 * @return void
	 */
	function validator(){
		if($this->id && BASIC_LANGUAGE::init()->default_() == $this->getDataBuffer('code')){
			$this->unsetDataBuffer('publish');
		}
		if(!preg_match("/^[a-zA-Z_]+$/", $this->getDataBuffer('code'))){
			$this->setMessage('code', 2);
		}
	}
	/**
	 * Format cell in list view
	 * 
	 * @access public
	 * @param string $value
	 * @param string $index
	 * @param array [$record_data]
	 */
	function formater($value, $index, $record_data = array()){
		switch ($index){
			case 'flag': {
				return  BASIC_GENERATOR::init()->image($value,'width=50|height=50|folder='.BASIC::init()->ini_get('upload_path'));
				break;
			}
			case 'publish':
				return BASIC_LANGUAGE::init()->get((int)$record_data[$index] == 1 ? 'yes' : 'no' );
			}
		return $value;
	}
	/**
	 * Open form for importing list with language variable from ini file
	 * 
	 * @access public
	 * @param integer $id
	 * @return string
	 */
	function ActionOpenImport($id){
		foreach($this->fields as $k => $v){
			if($k == 'flag'){
				$this->updateField($k, array(
					'text' => BASIC_LANGUAGE::init()->get('Import'),
					'attributes' => array(
						'perm' 	 => 'ini,txt',
					)
				));
			}else{
				$this->unsetField($k);	
			}
		}
		$this->delAction('save');
		$this->delAction('cancel');
		
		$this->updateAction('_import', null, BASIC_LANGUAGE::init()->get('Import'), 3);
		$this->addAction('cancel', 'ActionBack', BASIC_LANGUAGE::init()->get('back'), 3);
		
		return $this->ActionFormAdd($id);
	}
	/**
	 * Import Lingual variables from ini file in data buffer array
	 * 
	 * @access public
	 * @param integer $id
	 * @return void
	 */
	function ActionImport($id){
		BASIC::init()->imported('upload.mod');
		
		$upload = new BasicUpload('flag');
		$upload->setType(array('ini', 'txt'));
		$upload->maxSize = '100K';
		
		if(!$err = $upload->test()){

			$tmp_arr = BASIC_LANGUAGE::ini_parcer(file($upload->tmpName));
			
			$lang = $this->buildChild('lingual');
			$lang->loadURLActions();
			$lang->autoTest = false;
			
			$rdr = $lang->read();
			$arr = array(); while($rdr->read()){
				$arr[$rdr->item('variable')] = $rdr->item('id');	
			}
			foreach($tmp_arr as $k => $val){
				$lang->cleanBuffer();
				
				if(isset($arr[$k])){
					$lang->setDataBuffer('value', charAdd($val, false));
					$lang->ActionSave($arr[$k]);
				}else{
					$lang->setDataBuffer("value", charAdd($val, false));
					$lang->setDataBuffer("variable", charAdd($k));
					$lang->ActionSave();
				}
			}
		}else{
			$this->errorAction = 'import';
			$this->setMessage('flag', $err);
		}
	}
	/**
	 * Export lingual variables from db in ini file for language with $id
	 * 
	 * @access public
	 * @param integer $id
	 * @return resources download ini file
	 */
	function ActionExport($id){
		$this->ActionLoad($id);
		BASIC::init()->imported('upload.mod');

		$ldata = $this->buildChild('lingual');
		$ldata->loadURLActions();
		
		$rdr = $ldata->read();
		$data = ''; while($rdr->read()){
			$data .= $rdr->item('variable').'='.$rdr->item('value')."\n";
		}
		die(BasicDownload::downloadSource($this->getDataBuffer('code').'.ini', $data));
	}
	/**
	 * Remove language
	 * 
	 * @access public
	 * @return boolean
	 */
	function ActionRemove($ids,$riles = ''){
		
		if(!$ids) $ids = array();
		if(!is_array($ids)) $ids = array($ids);
		
		$rdr = $this->getRecords($ids);
		$tmp_res = array();
		while($rdr->read()){
			$tmp_res[] = $rdr->item('code');
		}
		
		$rdr_tables = BASIC_SQL::init()->showTables();
		while($rdr_tables->read()){
			$rdr_fields = BASIC_SQL::init()->showFields($rdr_tables->item(0));
			while($rdr_fields->read()){
				foreach($tmp_res as $v){
					if(preg_match("/_".$v."$/",$rdr_fields->item('Field'))){
						BASIC_SQL::init()->drobColumn($rdr_tables->item(0), $rdr_fields->item('Field'));
					}
				}
			}
		}
		$this->model->child = array();
		
		Builder::init()->build('pages')->clearMenuCash();
		
		return parent::ActionRemove($ids,$riles);
	}
	/**
	 * Add new language
	 * 
	 * @access public
	 * @return integer
	 */
	function ActionAdd(){
		if($id = parent::ActionAdd()){
			$obj = $this->buildChild('lingual');
			
			$obj->autoTest = false;
//			$obj->setField('value_'.BASIC_LANGUAGE::init()->default_());
			$obj->setField('value_'.$this->getDataBuffer('code'));
			$obj->setLangCode($this->getDataBuffer('code'));
			
			$rdr = $obj->getRecords();
			while($rdr->read()){
				$obj->setDataBuffer('variable', $rdr->item('variable'));
				$obj->setDataBuffer('value', $rdr->item('value_'.BASIC_LANGUAGE::init()->default_()).'('.$rdr->item('variable').')');
				$obj->ActionSave($rdr->item('id'));
			}
			Builder::init()->build('pages')->clearMenuCash();
		}
		return $id;
	}
}