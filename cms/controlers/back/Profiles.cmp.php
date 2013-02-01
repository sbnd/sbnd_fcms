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
 * Profiles component
 * 
 * @author Evgeni Baldzhiyski
 * @package cms.controlers.back
 */
class Profiles extends CmsComponent{
	
	/**
	 * 
	 * Types of users
	 * @var array
	 * @access private
	 */
    protected $users_types = array();
    /**
     * 
     * @todo description
     * @var int
     * @access private
     */
    protected $users_types_url_var_value = 0;
    /**
     * 
     * Path to the upload folder
     * @var string
     * @access public
     */
	public $upload_folder = 'upload/profiles';
	/**
	 * 
	 * Special action variable
	 * @var string
	 * @access public
	 */
    public $special_action_var_name = 'scmd';
    /**
     * 
     * Us avatar flag
     * @var boolean
     * @access public
     */
	public $use_avatar			 = true;
	/**
	 * 
	 * Max with of the avatar image
	 * @var int
	 * @access public
	 */
 	public $max_avatar_width 	 = 80;
 	/**
 	 * 
 	 * Max height of the avatar image
 	 * @var int
 	 * @access public
 	 */
	public $max_avatar_height 	 = 80;
	/**
	 * 
	 * Max filesize of the avatar image
	 * @var string
	 * @access public
	 */
	public $max_avatar_size 	 = '100K';
	/**
	 * 
	 * Available images types for avatar
	 * @var string
	 * @access public
	 */
	public $support_avatar_types = 'jpg,jpeg,gif,png';
	/**
	 * 
	 * Geocode service url
	 * @var string
	 * @access public
	 */
	public $geocode_service = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
	/**
	 * 
	 * Additional fields array
	 * @var array
	 * @access private
	 */
	protected $additionals_fields = array();
	/**
	 * 
	 * Countries list
	 * @var array
	 * @access private
	 */
	protected $countries = array();
	/**
	 * 
	 * Languages list
	 * @var array
	 * @access private
	 */
	protected $langs = array();
	/**
	 * 
	 * Set user types array
	 */
    function users_types_fields(){
   		$rdr = Builder::init()->build('profiles-types', false)->read();
   		
   		while($rdr->read()){
   			if((string)$rdr->item('order_id') == ''){
   				$rdr->setItem('order_id', 0);
   			}else{
   				$rdr->setItem('order_id', (int)$rdr->item('order_id') + 1);
   			}
     		$this->users_types[$rdr->item('id')] = $rdr->getItems();
    	}
    }
    /**
     * 
     * Set countries array
     */
    function getCountries(){
    	if(!$this->countries){
    		$rdr = Builder::init()->build('countries', false)->read();
    		if($rdr->num_rows()){
    			$this->countries = $rdr->getSelectData('id', 'name', array('' => ' '));
    		}else{
    			$this->countries = array(
    				'United Kingdom',
    				'United States',
    				'Bulgaria',
    				'France',
    				'Germany'
    			);
    		}
    	}
    	return $this->countries;
    }
    /**
     * 
     * Set language array
     */
    function getLanguages(){
    	if(!$this->langs){
    	   	$this->langs = array(); 
    	   	while($l = BASIC_LANGUAGE::init()->listing()){
        		$this->langs[$l['code']] = $l['text'];
    	   	}
    	}
    	return $this->langs;
    }
    /**
     * Main function - the constructor of the component
     * 
     * @see CmsComponent::main()
     */
	function main(){
		parent::main();
		
		$this->base = BASIC_USERS::init()->db_table;
		$this->breadcrumps = BASIC_USERS::init()->name_column;
		/*$this->additionals_fields = array(
			5 => array(
				'zip_code' => array(
					'text' => BASIC_LANGUAGE::init()->get('zip_code')
				),
				'name_of_institution' => array(
					'text' => BASIC_LANGUAGE::init()->get('name_of_institution')
				),
				'url_for_more_info' => array(
					'text' => BASIC_LANGUAGE::init()->get('url_for_more_info'),
				),
				'aggree_to_appear_to_map' => array(
					'text' => BASIC_LANGUAGE::init()->get('aggree_to_appear_to_map'),
					'formtype' => 'checkbox',
					'dbtype' => 'int',
					'length' => 1,
					'default' => 0
				)
			)
		);*/
		$this->setField(BASIC_USERS::init()->level_column, array(
            'text' => BASIC_LANGUAGE::init()->get('profiles_role'),
            'formtype' => 'select',
            'dbtype' => 'int',
            'length' => '1',
			'perm' => '*',
            'attributes' => array(
        		'onchange' => 'this.form.submit();'
            )
        ));
		$this->setField('name', array(
        	'text' => BASIC_LANGUAGE::init()->get('profiles_name'),
        	'perm' => '*',
			'filter' => 'auto',
        	'messages' => array(
        		1 => BASIC_LANGUAGE::init()->get('is_required')
        	)
        ));
        $this->setField(BASIC_USERS::init()->name_column, array(
            'text' => BASIC_LANGUAGE::init()->get('_'.BASIC_USERS::init()->name_column),
        	'perm' => '*',
        	'filter' => 'auto',
         	'messages' => array(
        		1 => BASIC_LANGUAGE::init()->get('is_required'),
        		2 => BASIC_LANGUAGE::init()->get('email_already_in_db'),
        		3 => BASIC_LANGUAGE::init()->get('invalid_email_format')
         	)
        ));
        
        $this->setField('address', array(
			'text' => BASIC_LANGUAGE::init()->get('complete_address'),
		));
		$this->setField('zip_code', array(
			'text' => BASIC_LANGUAGE::init()->get('zip_code'),
		));
		$this->setField('city', array(
			'text' => BASIC_LANGUAGE::init()->get('City')
		));
		$this->setField('countries', array(
			'text' => BASIC_LANGUAGE::init()->get('countries'),
			'formtype' => 'select',
			'dbtype' => 'int'
	 	));
		if($this->use_avatar){
			$this->setField('avatar', array(
				'text' 		=> BASIC_LANGUAGE::init()->get('photo_of_the_institution'),
				'formtype' 	=> 'file',
				'messages' 	=> array(
					1  => BASIC_LANGUAGE::init()->get('upoad_error_1'),
					2  => BASIC_LANGUAGE::init()->get('upoad_error_2'),
					3  => BASIC_LANGUAGE::init()->get('upoad_error_3'),
					4  => BASIC_LANGUAGE::init()->get('upoad_error_4'),
					10 => BASIC_LANGUAGE::init()->get('upoad_error_10'),
					11 => BASIC_LANGUAGE::init()->get('upoad_error_11'),
					12 => BASIC_LANGUAGE::init()->get('upoad_error_12'),
					13 => BASIC_LANGUAGE::init()->get('upoad_error_13'),
					14 => BASIC_LANGUAGE::init()->get('upoad_error_14'),
					15 => BASIC_LANGUAGE::init()->get('upoad_error_15'),
					16 => BASIC_LANGUAGE::init()->get('upoad_error_16'),
				),
				'attributes' => array(
					'max' 	 		=> $this->max_avatar_size,
					'rand'   		=> 'true',
					'as' 	 		=> 'ACC',
					'dir' 	 		=> $this->upload_folder,
					'preview' 		=> $this->max_avatar_width.','.$this->max_avatar_height,    
					'perm' 	 		=> $this->support_avatar_types,
					'onComplete' 	=> array($this, 'uploadComplete'),	
					'delete_btn' 	=> array(
						'text' => BASIC_LANGUAGE::init()->get('delete')
					)	
				)
			));
		}
        $this->setField(BASIC_USERS::init()->pass_column, array(
            'text' => BASIC_LANGUAGE::init()->get(BASIC_USERS::init()->pass_column),
            'formtype' => 'password',
            'length' => '32',
        	'messages' => array(
        		2 => BASIC_LANGUAGE::init()->get('invalid_password')
        	)
        ));
        $this->setField('confirm_'.BASIC_USERS::init()->pass_column,array(
            'text' => BASIC_LANGUAGE::init()->get('_'.BASIC_USERS::init()->pass_column),
            'formtype' => 'password',
        	'dbtype' => 'none',
            'length' => '32',
        	'messages' => array(
        		2 => BASIC_LANGUAGE::init()->get('is_required'),
        		3 => BASIC_LANGUAGE::init()->get('not_match_pass')
         	)
        ));
        $this->setField(BASIC_USERS::init()->perm_column, array(
            'text' => BASIC_LANGUAGE::init()->get(BASIC_USERS::init()->perm_column),
            'formtype' => 'radio',
            'dbtype' => 'int',
            'length' => '1',
            'default' => '1',
        	//'filter' => " AND `".BASIC_USERS::init()->perm_column."` = {V} ",
            'attributes' => array(
            	'data' => array(
        			BASIC_LANGUAGE::init()->get('no'),
        			BASIC_LANGUAGE::init()->get('yes')
        		)
            )
        ));
        $this->setField('language', array(
        	'text' => BASIC_LANGUAGE::init()->get('profiles_lingual_label'),
        	'formtype' => 'select',
        	'length' => 2,
        	'default' => BASIC_LANGUAGE::init()->current(),
        	'attributes' => array(
        		'data' => array()
        	)
        ));
        $this->setField('page_max_rows', array(
        	'text' => BASIC_LANGUAGE::init()->get('profiles_page_max_rows_label'),
        	'formtype' => 'select',
        	'dbtype' => 'int',
        	'length' => 3,
        	'default' => (int)CMS_SETTINGS::init()->get('list_max_rows'),
        	'attributes' => array(
        		'data' => $this->getMaxRowsOptions()
        	)
        ));
        $this->setField('latitude', array(
        	'formtype' => 'none'
        ));
		$this->setField('longitude', array(
			'formtype' => 'none'
		));
		
		$this->specialTest = 'validator';
	}
	/**
	 * This function will return the actual html of the component
	 * @see CmsComponent::startPanel()
	 */
	function startPanel(){
	    $this->sorting = new BasicSorting(BASIC_USERS::init()->name_column, false, $this->prefix);
        $this->users_types_fields();
		$this->filter = new BasicFilter($this->prefix);
		$this->filter->template($this->template_filter);
 		$this->filter->field('perm_column', array(
 			'text' => BASIC_LANGUAGE::init()->get(BASIC_USERS::init()->perm_column),
			'formtype' => 'radio',
			'filter' => " AND `active` = ({v} - 1) ",
			'dbtype' => 'int',
			'attributes' => array(
				'data' => array(
					'' => BASIC_LANGUAGE::init()->get('all'),
					1 => BASIC_LANGUAGE::init()->get('no'), 
					2 => BASIC_LANGUAGE::init()->get('yes')
				)
			)
		));
	    if(BASIC_USERS::init()->getPermission("profiles-types", 'list')){	
	    	$this->addAction('profiles-types', 'goToChild', BASIC_LANGUAGE::init()->get('cms_cmp_profiles_types'));
	    }
	 	else{
	    	$this->delAction('profiles-types');
	    }
	    if(BASIC_USERS::init()->getPermission("countries", 'list')){
	    	$this->addAction('countries', 'goToChild', BASIC_LANGUAGE::init()->get('countries'), 1);
	    }
	    $this->startManager();
		
	    $this->system[] = $this->special_action_var_name;
        
		if(!$this->cmd){
			if($this->cmd = BASIC_URL::init()->request($this->special_action_var_name)){
				$special_test = $this->specialTest;
				$this->specialTest = false;
				$this->test();
				$this->messages = array();
				$this->specialTest = $special_test;
			}
		}else{
			if($this->cmd == 'edit' || $this->cmd == 'add' || $this->cmd == 'save'){
				BASIC_URL::init()->set($this->special_action_var_name, ($this->cmd == 'save' ? BASIC_URL::init()->request($this->special_action_var_name) : $this->cmd));
			}else{
				BASIC_URL::init()->un($this->special_action_var_name);
			}
		}
		// @TODO need make security
//		if($this->id && $this->getDataBuffer(BASIC_USERS::init()->level_column) <= BASIC_USERS::init()->level()){
//			if($this->getDataBuffer('id') != BASIC_USERS::init()->getUserId()){
//			
//			}
//		}
		return $this->createInterface();
	}
	/**
	 * Set the available actions list
	 * @see DysplayComponent::ActionList()
	 */
	function ActionList(){
	 	//$this->map($this->field_id,BASIC_LANGUAGE::init()->get($this->field_id), null, 'width=1');
	 	$this->map('','', null, 'width=10');
	 	
 		//$this->map(BASIC_USERS::init()->name_column, BASIC_LANGUAGE::init()->get('_'.BASIC_USERS::init()->name_column),'formater');
 		
 		$this->map('name',BASIC_LANGUAGE::init()->get('profiles_name'));
 		$this->map('email',BASIC_LANGUAGE::init()->get('_email'));
 		
        $this->map(BASIC_USERS::init()->perm_column, BASIC_LANGUAGE::init()->get(BASIC_USERS::init()->perm_column), 'formater');
        $this->map(BASIC_USERS::init()->level_column, BASIC_LANGUAGE::init()->get('profiles_role'), 'formater');		
		
        foreach ($this->fields as $name => $settings){
			if(isset($settings['map'])){
				$this->map($name,$settings[4],$settings['map']);		
			}
		}
		return parent::ActionList();
	}
	/**
	 * Action form add
	 * @see DysplayComponent::ActionFormAdd()
	 */
	function ActionFormAdd(){
		$this->id = 0;

		return $this->ActionFormEdit();
	}
	/**
	 * 
	 * Return the html of the form
	 * 
	 * @see CmsComponent::ActionFormEdit()
	 */
	function ActionFormEdit($id = 0){
		$this->updateField('countries', array(
			'attributes' => array(
				'data' => $this->getCountries()
			)
		));
				
	    if($id && !$this->messages) $this->ActionLoad($id);
        
        if($id == BASIC_USERS::init()->getUserId()){
        	$this->updateField(BASIC_USERS::init()->level_column, array(
        		'formtype' => 'hidden'
        	));
        }
		$disabled = true;
		if($this->getDataBuffer(BASIC_USERS::init()->level_column) || BASIC_URL::init()->request(BASIC_USERS::init()->level_column)){
			$disabled = false;
			if(!$aad = BASIC_URL::init()->request(BASIC_USERS::init()->level_column)){
				$aad = $this->getDataBuffer(BASIC_USERS::init()->level_column);
			}
			/*
			if(isset($this->additionals_fields[$aad])){
				foreach($this->additionals_fields[$aad] as $k => $v){
					$this->setField($k, $v);				
				}
				if($id && !$this->messages) $this->ActionLoad($id);
			}
			*/
			$this->setDataBuffer(BASIC_USERS::init()->level_column, $aad);
		}
		
		$is_high_level = false;
		if($id && $this->getDataBuffer(BASIC_USERS::init()->level_column) <= BASIC_USERS::init()->level()){
			
			if($id != BASIC_USERS::init()->getUserId()){
				$is_high_level = true;
				$disabled = true;
			}
		}
		
		foreach($this->fields as $k => $v){
			if($k == BASIC_USERS::init()->level_column && !$is_high_level) continue;
			
			$tmp = $this->getField($k);
			$tmp['attributes']['disabled'] = $disabled;
			$this->updateField($k,$tmp);
		}		
		
	    $weight = BASIC_USERS::init()->level() == -1 || $is_high_level;
        $drop_down = array('' => ' ');
        foreach ($this->users_types as $id => $data){
        	if($weight && $id > 0){
        		$drop_down[$id] = $data['title'];
        	}
        	
        	if(!$weight && BASIC_USERS::init()->level() == $id){
        		$weight = true;
        	}
        }
        $this->updateField(BASIC_USERS::init()->level_column, array(
            'attributes' => array(
                'data' => $drop_down
            )
        ));
        
     
        $this->updateField("language", array(
        	'attributes' => array(
        		'data' => $this->getLanguages()
        	)
        ));
        
        if($disabled){
        	$this->delAction('save');	
        }
        
		return $this->FORM_MANAGER();
	}
	/**
	 * 
	 * Form validator
	 */
	function validator(){
		$err = false;
		
		if($this->id != BASIC_USERS::init()->getUserId() && 
			BASIC_USERS::init()->level() != -1 &&
			$this->users_types[$this->getDataBuffer('level')]['order_id'] <= $this->users_types[BASIC_USERS::init()->level()]['order_id']
		){
			BASIC_ERROR::init()->setError(BASIC_LANGUAGE::init()->get('not_have_level_perms')); return true;
		}
        if(!$this->id){
            if(!$this->getDataBuffer(BASIC_USERS::init()->pass_column)){
            	$err = $this->setMessage(BASIC_USERS::init()->pass_column,2);
            }
            if(!$this->getDataBuffer('confirm_'.BASIC_USERS::init()->pass_column)){
            	$err = $this->setMessage('confirm_'.BASIC_USERS::init()->pass_column,2);
            }
        }else{
        	if(!$this->getDataBuffer(BASIC_USERS::init()->pass_column)){
            	$this->unsetDataBuffer(BASIC_USERS::init()->pass_column);
        	}
        }
        if(BASIC_SQL::init()->read_exec(" SELECT 1 FROM `".$this->base."` WHERE 1=1 AND `".BASIC_USERS::init()->name_column."` = '".$this->getDataBuffer(BASIC_USERS::init()->name_column)."' AND `".$this->field_id."` != ".(int)$this->id." ")->num_rows()){
        	$err = $this->setMessage(BASIC_USERS::init()->name_column, 2);
        }
 		if(
            ($this->getDataBuffer(BASIC_USERS::init()->pass_column) || $this->getDataBuffer('confirm_'.BASIC_USERS::init()->pass_column)) &&
            ($this->getDataBuffer(BASIC_USERS::init()->pass_column) != $this->getDataBuffer('confirm_'.BASIC_USERS::init()->pass_column))
        ){
            $err = $this->setMessage('confirm_'.BASIC_USERS::init()->pass_column,3);
        }
        if($this->getField('email') && !BASIC::init()->validEmail($this->getDataBuffer('email'))){
        	$err = $this->setMessage('email',3);
        }
        if(!$err){
        	$this->googleGeoCode();
        	
            if($this->getDataBuffer(BASIC_USERS::init()->pass_column)){
	        	$this->setDataBuffer(
	            	BASIC_USERS::init()->pass_column,
	            	BASIC_USERS::init()->passwordCripter($this->getDataBuffer(BASIC_USERS::init()->pass_column))
	            );
            }
        }
        if($this->id && $this->id == BASIC_USERS::init()->getUserId()){
        	$this->setDataBuffer('level', BASIC_USERS::init()->get(BASIC_USERS::init()->level_column));
        }
		return $err;
	}
	/**
	 * 
	 * Google geocode service
	 * @access private
	 */
	protected function googleGeoCode(){
		if($this->getDataBuffer('countries')){
			$countryes = $this->getCountries();
			$country = $countryes[$this->getDataBuffer('countries')];
			
			$city = trim($this->getDataBuffer('city'));
			
			$address = trim($this->getDataBuffer('address'));
			$address = str_replace(' ', '+', $address);
			
			$curl = curl_init($this->geocode_service.'&address='.$address.
				',+'.str_replace(" ", "-", $city).
				($country ? ',+'.$country : '')
			);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			
			$response = curl_exec($curl);
			
			curl_close($curl);
			
			$json = json_decode($response);
			if($json->{'status'} == "OK"){
				$location = $json->{'results'}[0]->{'geometry'}->{'location'};
				$lat = $location->{'lat'};
				$lng = $location->{'lng'};
				$this->setDataBuffer('latitude', strval($lat));
				$this->setDataBuffer('longitude', strval($lng));
			}
		}
	}
	/**
	 * 
	 * Format the names of the columns
	 * @param string $value
	 * @param string $index
	 * @param array $record_data
	 * @return mixed
	 */
	function formater($value, $index, $record_data = array()){
		if($index == BASIC_USERS::init()->perm_column /*|| $index == 'admin_access'*/){
			return BASIC_LANGUAGE::init()->get($value ? 'yes' : 'no');
		}
		if($index == BASIC_USERS::init()->level_column){
			return (isset($this->users_types[$value]) ? $this->users_types[$value]['title'] : 'N/A');
		}
		return $value;
	}
	/**
	 * 
	 * Create manager bar with actions
	 * 
	 * @param array $row
	 * @param array [$settings]
	 * @see DysplayComponent::rowActionsBar()
	 */
	function rowActionsBar($row, $settings = array()){
		if(!is_array($settings)) $settings = array();
		
	   	if(BASIC_USERS::init()->level() > -1 && (
	   		$this->users_types &&
	   		BASIC_USERS::init()->getUserId() != $row['id'] &&
	   		$this->users_types[BASIC_USERS::init()->level()]['order_id'] >= $this->users_types[$row['level']]['order_id']
	   	)){
	  		$settings['mark'] = array(
  				'disabled' => 'disabled'
  			);
	  		$settings['actions'] = array(
	  			'edit' => 'disable', //hide
		   	);
	   	}
	   	if(BASIC_USERS::init()->getUserId() == $row['id']){
	  		$settings['mark'] = array(
  				'disabled' => 'disabled'
  			);	
	   	}
		return parent::rowActionsBar($row,$settings);
	}
	/**
	 * Action save - on form submit
	 * @param int [$id]
	 * @see CmsComponent::ActionSave()
	 * @return int
	 */
	function ActionSave($id = 0){
		$aad = (int)BASIC_URL::init()->request(BASIC_USERS::init()->level_column);
		if(isset($this->additionals_fields[$aad])){
			foreach($this->additionals_fields[$aad] as $k => $v){
				$this->setField($k, $v);				
			}
		}
		if($id = parent::ActionSave($id)){
			if(BASIC_USERS::init()->getUserId() == $id){
				BASIC_LANGUAGE::init()->reloadLanguage($this->getDataBuffer('language'));
			}
		}
		return $id;
	}
	/**
	 * 
	 * Need for child classes
	 * 
	 * @param int [$id]
	 */
	function ParentActionSave($id = 0){
		return parent::ActionSave($id);
	}
	/**
	 * Action remove
	 * 
	 * @param int $id
	 * @param string $action
	 * @param string $rules
	 * @see CmsComponent::ActionRemove()
	 */
	function ActionRemove($id=0, $action = '',$rules = ''){
		if($id){
    		if(!is_array($id)) $id = array($id);
    		foreach ($id as $k => $v){
    			if($v == BASIC_USERS::init()->getUserId()){
    				unset($id[$k]);
    			}
    		}
		}
		return parent::ActionRemove($id, $action, $rules);
	}
	/**
	 * 
	 * On complete upload
	 * 
	 * @param BASIC_UPLOAD $obj
	 */
    function uploadComplete($obj){
    	BASIC::init()->imported('media.mod');
    	
		$obj_upload = new BasicMediaImage($obj->returnName, $obj->upDir);
		$obj_upload->resize($this->max_avatar_width, $this->max_avatar_height);
	}
	/**
	 * 
	 * Define the settings for the component. Values will be overwrite values of these class properties.
	 * @return array
	 */
	function settingsData(){
		return array(
			'template_form' 		=> $this->template_form,
			'template_list' 		=> $this->template_list,
			'prefix' 				=> $this->prefix,
			'geocode_service' 		=> $this->geocode_service,
			'use_avatar' 			=> $this->use_avatar,
			'max_avatar_width' 		=> $this->max_avatar_width,
			'max_avatar_heigh' 		=> $this->max_avatar_heigh,
			'max_avatar_size' 		=> $this->max_avatar_size,
			'support_avatar_types' 	=> $this->support_avatar_types,
			'upload_folder' 		=> $this->upload_folder
		);
	}
	/**
	 * 
	 * Description of fields for component settings
	 * @return array
	 */
	function settingsUI(){
		return array(
			'template_form' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_form')
			),
			'template_list' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_list')
			),
			'prefix' => array(
				'text' => BASIC_LANGUAGE::init()->get('prefix')
			),
			'geocode_service' => array(
				'text' => BASIC_LANGUAGE::init()->get('geocode_service')
			),
			'use_avatar' => array(
				'text' => BASIC_LANGUAGE::init()->get('use_avatar'),
				'formtype' => 'radio',
				'attributes' => array(
					'data' => array(
						BASIC_LANGUAGE::init()->get('no'),
						BASIC_LANGUAGE::init()->get('yes')
					)
				)
			),			
			'max_avatar_width' => array(
				'text' => BASIC_LANGUAGE::init()->get('max_avatar_width')
			),
			'max_avatar_heigh' => array(
				'text' => BASIC_LANGUAGE::init()->get('max_avatar_heigh')
			),
			'max_avatar_size' => array(
				'text' => BASIC_LANGUAGE::init()->get('max_avatar_size')
			),
			'support_avatar_types' => array(
				'text' => BASIC_LANGUAGE::init()->get('support_avatar_types')
			),
			'upload_folder' => array(
				'text' => BASIC_LANGUAGE::init()->get('upload_folder')
			)
		);
	}
}