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
 * Class managing list with countries, used in Account component
 * 
 * @author Evgeni Baldzhiyski
 * @package cms.controlers
 */
class Countries extends CmsComponent {
	/**
	 * Component database table - 'countries'
	 * @access public
	 * @var string
	 */
	public $base = 'countries';
	/**
	 * Main function - the constructor of the component
	 * 
	 * @access public
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();
		
		$this->setField('name', array(
			'text' => BASIC_LANGUAGE::init()->get('name'),
			'perm' => '*',
			'lingual' => true
		));
		
		$this->ordering(true);
		
		$this->addAction('profiles', 'goToParent', BASIC_LANGUAGE::init()->get('back'));
		
		$this->addAction('import',  'ActionOpenImport', BASIC_LANGUAGE::init()->get('Import'), 1);
		$this->addAction('export',  'ActionExport', BASIC_LANGUAGE::init()->get('Export'), 1);
		$this->addAction('_import', 'ActionImport', BASIC_LANGUAGE::init()->get('Import'), 0);		
	}
	/**
	 * Open form for importing list with countries
	 * 
	 * @access public
	 * @param integer $id
	 * @return string
	 */
	function ActionOpenImport($id){
		$this->unsetField('name');
		
		$this->setField('upload_cnt', array(
			'text' => BASIC_LANGUAGE::init()->get('langualge_flag_lanel'),
			'perm' => '*',
			'formtype' => 'file',
			'attributes' => array(
				'max' 	 => '100K',
				'perm' 	 => 'txt',
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
		
		$this->delAction('save');
		$this->delAction('cancel');
		
		$this->updateAction('_import', null, BASIC_LANGUAGE::init()->get('Import'), 3);
		$this->addAction('cancel', 'ActionBack', BASIC_LANGUAGE::init()->get('back'), 3);
		
		return $this->ActionFormAdd($id);
	}
	/**
	 * Import in db list with countries
	 * 
	 * @access public
	 * @param integer $id
	 * @return void
	 */
	function ActionImport($id){
		BASIC::init()->imported('upload.mod');
		
		$upload = new BasicUpload('upload_cnt');
		$upload->setType(array('txt'));
		$upload->maxSize = '100K';
		
		if(!$err = $upload->test()){
			
			$rdr = $this->read();
			$arr = array(); while($rdr->read()){
				$arr[$rdr->item('name')] = $rdr->item('id');	
			}
			
			$tmp_arr = file($upload->tmpName);
			foreach($tmp_arr as $k => $val){
				if(isset($arr[$k])){
					$this->setDataBuffer('name', trim($val));
					$this->ActionSave($arr[$k]);
				}else{
					$this->setDataBuffer("name", trim($val));
					$this->ActionSave();
				}
			}
		}else{
			$this->errorAction = 'Import';
			$this->setMessage('upload_cnt', $err);
		}
	}
	/**
	 * Export list with countries
	 * 
	 * @access public
	 * @return resource download txt file
	 */
	function ActionExport(){
		BASIC::init()->imported('upload.mod');
		
		$rdr = $this->read();
		$data = ''; while($rdr->read()){
			$data .= $rdr->item('name')."\n";
		}
		die(BasicDownload::downloadSource('sbnd-cms7-countries.txt', $data));
	}
}