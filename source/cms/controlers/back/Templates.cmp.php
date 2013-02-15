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
 * File manager.
 * 
 * @author Evgeni Baldziyski
 * @version 0.3
 * @since 15.12.2011
 * @package cms.controlers.back
 */
class Templates extends CmsComponent {
	/**
	 * 
	 * Breaddcrumbs
	 * @var string
	 * @access public
	 */
	public $breadcrumps = 'name';
	/**
	 * 
	 * List array
	 * @var array
	 * @access private
	 */
	protected $list = array();
	
	/**
	 * 
	 * Main function - the constructor of the component
	 * @access public
	 * @see CmsComponent::main()
	 */
	public function main(){
		parent::main();
		
		$this->base = CMS_SETTINGS::init()->get('FSITE_THEME')."tpl";
		
		$this->makeIploadField();
		
		$this->sorting = new BasicSorting('name', false, $this->prefix);
		
		$this->addAction("import", 'ActionImport', BASIC_LANGUAGE::init()->get('templates_import_action'), 0);
		$this->addAction("import-form", 'ActionImportForm', BASIC_LANGUAGE::init()->get('templates_import_form_action'), 2);
		$this->addAction("export", 'ActionExport', BASIC_LANGUAGE::init()->get('templates_export_action'), 2);
	
		$this->prefix = 'tpl_';
	}
	/**
	 * 
	 * Return the html of the listing view
	 * 
	 * @see DysplayComponent::ActionList()
	 */
	function ActionList(){
		$this->map('name', BASIC_LANGUAGE::init()->get('templates_name_label'));
		$this->map('mdate', BASIC_LANGUAGE::init()->get('templates_mdate_label'), 'formatter');
		
		$this->filter = new BasicFilter('f');
		$this->filter->template($this->template_filter);
		$this->filter->field('name', array(
			'text' => BASIC_LANGUAGE::init()->get('templates_name_label'),
			'filter' => " AND `name` LIKE '%{v}%'"
		));
		$this->filter->field('body', array(
			'text' => BASIC_LANGUAGE::init()->get('templates_body_label'),
			'filter' => " AND `body` LIKE '%{v}%'"
		));
		
		return parent::ActionList();
	}
	/**
	 * Get all records
	 * @param array [$ids]
	 * @param string [$criteria]
	 * 
	 * @return object
	 * @see CmsComponent::getRecords()
	 */
	function getRecords($ids = array(), $criteria = ''){
		$reader = ComponentReader::getEmptyReader();
		
		$list = $this->buildList();
		
		$list = $this->sorting->sortCollection($list);
		
		$this->paging = new BasicComponentPaging($this->prefix);
		$this->paging->init(count($list), $this->maxrow);
		
		$reader->addRows($this->paging->filterArray($list, true));

		return $reader;
	}
	/**
	 * Get single record
	 * @param int $id
	 * @see DysplayComponent::getRecord()
	 * @return array
	 * 
	 */
	function getRecord($id){
		$list = $this->buildList();
		
		return $list[$id-1];
	}
	/**
	 * On submit form this action will be called
	 * 
	 * @param int $id
	 * @see CmsComponent::ActionSave()
	 * 
	 */
	function ActionSave($id){
		$this->buildList();
		
		$name = $this->getDataBuffer('name');
		if($id) $name = $this->list[$id-1]['name'];
		
		$fo = fopen(BASIC::init()->ini_get('root_path').$this->base.'/'.$name, 'w');
		fwrite($fo, $this->getDataBuffer('body'));
		fclose($fo);
		//rename ako faila e s promeneno ime
		if($name != $this->getDataBuffer('name')) rename(BASIC::init()->ini_get('root_path').$this->base.'/'.$name,BASIC::init()->ini_get('root_path').$this->base.'/'.$this->getDataBuffer('name'));
	}
	/**
	 * 
	 * Function to format the columns titles 
	 * @param mixed $value
	 * @param string $column_name
	 * @return mixed
	 */
	function formatter($value, $column_name){
		if($column_name == 'mdate'){
			return @date('d.m.Y H:i:s', $value);
		}
		return $value;
	}
	/**
	 * 
	 * Set form available actions and return the form
	 * 
	 * @param int $id
	 */
	function ActionImportForm($id){
		$this->delAction('save');
		$this->delAction('cancel');
		
		$this->updateAction('import', null, null, 3);
		$this->addAction('cancel', 'ActionBack', BASIC_LANGUAGE::init()->get('back'), 3);
		
		$this->makeIploadField(true);
		return $this->ActionFormAdd($id);
	}
	/**
	 * 
	 * Action Import
	 * @param int $id
	 * @return boolean
	 */
	function ActionImport($id){
		$this->errorAction = 'import-form';
		$this->makeIploadField(true);
		
		if(!$this->test()){
			$fo = $this->getDataBuffer('import');
			
			if($f = fopen($fo->tmpName, 'r')){
				$data = ''; while (!feof($f))
					$data .=  fread($f, (1024*1024));
				
				$this->setDataBuffer('body', $data);
				$this->ActionSave($id);
				
				return true;	
			}
		}
		$this->setDataBuffer('import', '');
		
		$this->makeIploadField();
		return false;
	}
	/**
	 * 
	 * Action Export
	 * @param int $id
	 */
	function ActionExport($id){
		BASIC::init()->imported('upload.mod');
		$this->buildList();
		
		die(BasicDownload::downloadSource($this->list[$id-1]['name'], $this->list[$id-1]['body'], 'utf-8'));
	}
	/**
	 * Action remove (delete)
	 * @param int $id
	 * @see CmsComponent::ActionRemove()
	 */
	function ActionRemove($id){
		$this->buildList();
		
		if(!is_array($id)){
			if((int)$id){
				unlink(BASIC::init()->ini_get('root_path').$this->base."/".$this->list[$id-1]['name']);
			}
		}else{
			foreach ($id as $i){
				if((int)$i){
					unlink(BASIC::init()->ini_get('root_path').$this->base."/".$this->list[$i-1]['name']);
				}
			}
		}
	}
	/**
	 * 
	 * HTML tags cleaner
	 * @param string $str
	 * @return string
	 */
	function skipHtmlCleaner($str){
		return stripslashes($str);
	}
	
	/**
	 * 
	 * Create upload field
	 * 
	 * @access private
	 * @param boolean $special
	 */
	protected function makeIploadField($special = false){
		if(!$special){
			$this->setField('name', array(
				'text' => BASIC_LANGUAGE::init()->get('templates_name_label'),
				'perm' => '*',
				'length' => 100
			));
			$this->setField('body', array(
				'text' => BASIC_LANGUAGE::init()->get('templates_body_label'),
				'formtype' => 'textarea',
				'dbtype' => 'longtext',
				'length' => 0,
				'cleaners' => array(
					array($this, 'skipHtmlCleaner'),
					'cleanHTMLT'
				)
			));
			$this->setField('mdate', array(
				'dbtype' => 'int',
				'length' => 15,
				'formtype' => 'hidden'
			));
		}else{
			foreach($this->fields as $k => $v){
				$this->unsetField($k);
			}
			$this->setField('import', array(
				'text' => BASIC_LANGUAGE::init()->get('template_import_label'),
				'formtype' => 'file',
				'perm' => '*',
				'attributes' => array(
					'max' 	 => '150K',
					'perm' 	 => 'tpl'
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
		}
	}
	/**
	 * 
	 * @todo description
	 * @access private
	 * @version 0.3
	 * @return hashmap
	 */
	protected function buildList(){
		if(!$this->list){
			$i = 0;
			$buffer = $this->filter ? $this->filter->buffer() : array();
			
			$dir = opendir(BASIC::init()->ini_get('root_path').$this->base);
			while ($f = readdir($dir)){
				if($f == '.' || $f == '..' || $f == '.svn') continue;
			
				$fo = fopen(BASIC::init()->ini_get('root_path').$this->base."/".$f, 'r');
				$data = '';
				while (!feof($fo)){
					$data .=  fread($fo, (1024*1024));
				}
				
				$time = @filemtime(BASIC::init()->ini_get('root_path').$this->base."/".$f);
				if(
					!$this->checkForMiss($buffer, 'name', $f) &&
					!$this->checkForMiss($buffer, 'body', $data) &&
					!$this->checkForMiss($buffer, 'mdate', $this->formatter($time, 'mdate'))
				){
					$this->list[$i] = array(
						'id' => ($i+1),
						'name' => $f,
					 	'body' => $data,
						'mdate' => $time
					);
				}
				$i++;
			}
		}
		return $this->list;
	}
	/**
	 * 
	 * Helper function to escape variables in buffer
	 * @access private
	 * @param array $buffer
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	protected function checkForMiss($buffer, $key, $value){
		$miss = true;
		if($buffer){
			if(isset($buffer[$key])){
				if(
					($buffer[$key]['type'] == 'match' && $buffer[$key]['data'] == $value) ||
					($buffer[$key]['type'] == 'middle' && strpos($value, $buffer[$key]['data']) !== false) || 
					(preg_match("/".($buffer[$key]['type'] == 'start' ? '^'.$buffer[$key]['data'] : $buffer[$key]['data'].'$')."/", $value))
				){
					$miss = false;
				}
			}else{
				$miss = false;
			}
		}else{
			$miss = false;
		}
		return $miss;
	}
}