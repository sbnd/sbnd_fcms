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
 * 
 * 
 * Permissions class
 * 
 * @author Evgeni Baldzhiyski
 * @since 25.01.2012
 * @version 0.2
 * @package cms.controlers.back
 */
class Permitions extends CmsComponent{
	
	/**
	 * 
	 * Parent data array
	 * @var array
	 * @access private
	 */
	protected $parent_data = array();
	
	/**
	 * Main function - the constructor of the component
	 * 
	 * @see CmsComponent::main()
	 */
	function main(){
		parent::main();

		$this->base = BASIC_USERS::init()->permition_manager->getBase();
		
		$this->delAction('delete');
		$this->delAction('edit');
		$this->delAction('add');
		$this->delAction('cancel');
		
		$this->updateAction('list', 'ActionFormEdit');
		
		$this->addAction('__parent', 'goToParent', BASIC_LANGUAGE::init()->get('back'), 3);
		
		$this->setPrefix();
		$this->system[] = '_parent_id';
	}
	/**
	 * This function will return the actual html of the component
	 * 
	 * @see CmsComponent::startPanel()
	 */
	function startPanel(){
		$this->buildComponentsTreeControles(Builder::init());
		
		return parent::startPanel();
	}
	/**
	 * Action Save
	 * 
	 * @see CmsComponent::ActionSave()
	 */
	function ActionSave($id){
		BASIC_SQL::init()->exec(" DELETE FROM `".$this->base."` WHERE `_parent_id` = ".(int)$this->parent_id." ");
		
		foreach($this->dataBuffer as $k => $v){
			if($k != '_parent_id'){
				BASIC_SQL::init()->exec(" INSERT INTO `".$this->base."` (
						`cmp_name`, `access`, `_parent_id`
					) VALUES (
						'".$k."', '".implode(",", $this->dataBuffer[$k])."', ".(int)$this->parent_id."
					)
				");
			}
		}
	}
	/**
	 * Action Edit
	 * @see CmsComponent::ActionFormEdit()
	 */
	function ActionFormEdit(){
		BASIC_GENERATOR::init()->head('permManager', 'script', null, "
			var permManager = (function (){
				
				var checkControlTreeIndex = [];
				
				function findAction(checkbox, obj){
					
					$(obj).find('input').each(function (i, o){
						
						if(checkbox.value == o.value){
							o.checked = checkbox.checked;
							return false;
						}
					});		
				}
				
				return {
					checkControlTree: function (checkbox){
						if(!checkControlTreeIndex.length){
							checkControlTreeIndex = $('.perm_item');
						}
						if(checkbox.checked){
							if(checkbox.parentNode.parentNode.lang > 0) this.checkControlTreeOn(checkbox);
						}else{
							this.checkControlTreeOff(checkbox);
						}
					},
					checkControlTreeOn: function (checkbox){
						var current = checkbox.parentNode.parentNode,
						level = current.lang,
						match = false
						
						for(var i = checkControlTreeIndex.length-1; i >= 0; i--){
							var obj = checkControlTreeIndex[i];
							
							if(obj.id == current.id){
								match = true; continue;
							}
							
							if(match){
								findAction(checkbox, obj);
								
								if(obj.lang == 0) return false;
							}
						}
					},
					checkControlTreeOff: function (checkbox){
						var current = checkbox.parentNode.parentNode,
							level = current.lang,
							match = false;
					
						$.each(checkControlTreeIndex, function (i, obj){
							if(obj.id == current.id){
								match = true;
								return ;
							}
							
							if(match){
								if(level >= obj.lang) return false;
								
								findAction(checkbox, obj);
							}
						});
					}
				}
			})();
		");
		return parent::ActionFormEdit(1);
	}
	/**
	 * Create system variables
	 * 
	 * @see DysplayComponent::startManager()
	 */
	function startManager(){
		parent::startManager();
		
		$this->parent_data = $this->buildParent()->getRecord($this->parent_id);
		
		if($this->parent_id != -2 && $this->parent_data['order_id'] <= BASIC_USERS::init()->level()){
			$this->delAction('save');
		}
	}
	/**
	 * 
	 * Load data in edit form
	 * @param int $id
	 * 
	 * @see CmsComponent::ActionLoad()
	 */
	function ActionLoad($id){
		$rdr = $this->read('', true);
		
		while($rdr->read()){
			$this->setDataBuffer($rdr->item('cmp_name'), explode(",", $rdr->item('access')));
		}
		
		foreach($this->fields as $k => $v){
			if(!isset($this->dataBuffer[$k]) && isset($v[6]['data'])){
				$this->dataBuffer[$k] = array();
				foreach($v[6]['data'] as $kk => $vv){
					$this->dataBuffer[$k][] = $kk;
				}
			}
			$this->updateField($k, array(
				'attributes' => array(
					'disabled' => ($this->parent_id != -2 && $this->parent_data['order_id'] <= BASIC_USERS::init()->level() ? true : false) 
				)
			));
		}
	}
	/**
	 * 
	 * Set prefix
	 * @access private
	 */
	protected function setPrefix(){
		if($this->prefix == 'permitions')
			$this->prefix = '';
	}
	/**
	 * 
	 * Build component tree controllers
	 * @param object $source
	 * @param int $level
	 * @access private
	 */
	protected function buildComponentsTreeControles($source, $level = 0){
		if($source->child){
			$sep = ''; for($i = 0; $i < $level; $i++) $sep .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			
			foreach ($source->child as $obj){
				if(!$obj->public_name) continue;
				
				$cmp = Builder::init()->build($obj->system_name, false);
				
				if(!$perms = $cmp->getCmpPermitions()) continue;
				
				$this->setField($obj->system_name, array(
					'text' => '<span style="display: block;text-align: left;width: 100%;">'.$sep.$obj->public_name.'</span>',
					'formtype' => 'check',
					'dbtype' => 'none',
					'attributes' => array(
						'lang' => $level,
						'data' => $perms,
						'onclick' => 'permManager.checkControlTree(this)',
						'class' => 'perm_item',
						'id' => 'field_'.$obj->system_name
					)
				));
				$this->buildComponentsTreeControles($obj, $level+1);
			}
		}
	}
}