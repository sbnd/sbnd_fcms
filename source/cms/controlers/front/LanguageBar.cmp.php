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


/**
 * Component Box. Build language site bar.
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.4
 * @package cms.controlers.front
 */
class LanguageBar extends CmsBox{
	
	/**
	 * 
	 * Template filename
	 * @var string
	 * @access public
	 */
	public $template_list = 'cms-language-bar.tpl';
	/**
	 * 
	 * Switch mode flag
	 * @var boolean
	 * @access public
	 */
	public $switchMode = true;
	/**
	 * 
	 * This function will return the actual html of the component
	 * 
	 * @see CmsBox::startPanel()
	 * @access public
	 */
	public function startPanel(){
	    $languages = array();
		while ($k = BASIC_LANGUAGE::init()->listing()){
			if($k['code'] == BASIC_LANGUAGE::init()->current()){
				if($this->switchMode){
					 continue;
				}else{
					$k['current'] = true;
				}
			}
			$k['lang_link'] =  BASIC_URL::init()->link("./", BASIC_URL::init()->serialize(array(BASIC_LANGUAGE::init()->varLog),'get','get'));
			$k['lang_link'] = str_replace("/".BASIC_LANGUAGE::init()->current()."/", "/".$k['code']."/", $k['lang_link']);
			
			$languages[] = $k;
		}
		BASIC_TEMPLATE2::init()->set("languages", $languages, $this->template_list);

		return BASIC_TEMPLATE2::init()->parse($this->template_list);
	}
	/**
	 * 
	 * Define the settings for the component. Values will be overwrite values of these class properties.
	 * @return array
	 */
	function settingsData(){
		return array(
			'template_list' => $this->template_list,
			'switchMode' 	=> $this->switchMode
		);
	}
	/**
	 * 
	 * Desciption of fields for component settings
	 * @return array
	 */
	function settingsUI(){
		return array(
			'template_list' => array(
				'text' => BASIC_LANGUAGE::init()->get('template_list')
			),
			'switchMode' => array(
				'text' => BASIC_LANGUAGE::init()->get('switchMode'),
				'formtype' => 'radio',
				'attributes' => array(
					'data' => array(
						BASIC_LANGUAGE::init()->get('no'),
						BASIC_LANGUAGE::init()->get('yes')
					)
				)
			)
		);
	}	
}