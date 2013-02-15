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
* @package cms.language
* @version 7.0.4
*/

BASIC::init()->imported('language.mod');
BASIC::init()->imported('settings.mod', 'cms');

/**
 * Use db table for save language's list. Use CMS_SETTINGS's variables 'SITE_CHARSET' and 'SITE_LANGUAGE'.  
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.1
 * @since 10.08.2011
 * @package cms.languages
 */
class CMS_LANGUAGE extends BASIC_LANGUAGE {
	
	/**
	 * 
	 * Language container
	 * @var string
	 * @access public
	 */
	public $lcontainer = 'languages';
	/**
	 * 
	 * method
	 * @var string
	 * @access public
	 */
	public $lmethod = 'db'; // 'disc'
	/**
	 * 
	 * Language folder
	 * @var string
	 * @access public
	 */
	public $lfolder = '';
	/**
	 * 
	 * Is admin param
	 * @var boolean
	 * @access public
	 */
	public $isAdmin = false;
	/**
	 * 
	 * Character set
	 * @var string
	 * @access public
	 */
	public $default_charset = 'utf-8';
	/**
	 * Initialisation
	 * @static
	 * @access public
	 */
	static public function init(){
		$GLOBALS['BASIC_LANG'] = new CMS_LANGUAGE();
	}
	/**
	 * Class constructor
	 * @return CMS_LANGUAGE
	 */
	public function start(){
		$this->default = CMS_SETTINGS::init()->get('SITE_LANGUAGE');
		$this->lload();
		
		$lang = parent::start();
		
		return $lang;
	}
	/**
	 * 
	 * Reload language
	 * 
	 * @param string $code
	 * @return CMS_LANGUAGE
	 */
	public function reloadLanguage($code){
		$old_land = $this->current();
		
		parent::reloadLanguage($code);
		
		$charset = BASIC_LANGUAGE::init()->info('encode');
		if(!$charset) $charset = CMS_SETTINGS::init()->get('SITE_CHARSET');
		if(!$charset) $charset = $this->default_charset;
		
		BASIC_GENERATOR::init()->head('charset', 'meta', array(
			'http-equiv' => "Content-Type",
			'content' => "text/html; charset=".$charset
		));
		
		Builder::init()->META_NAMES(CMS_SETTINGS::init()->get('SITE_NAME'));
    	Builder::init()->META_DESC(CMS_SETTINGS::init()->get('SITE_DESK'));
    	Builder::init()->META_KEYS(CMS_SETTINGS::init()->get('SITE_KEYS'));
		
		BASIC_URL::init()->un($this->varLog);
		
		if(!$this->isAdmin){
			if($this->current() != $old_land){
				$virtual = BASIC::init()->ini_get('root_virtual');
				if($old_land){
					$virtual = str_replace($old_land.'/', '', BASIC::init()->ini_get('root_virtual'));
				}
				BASIC::init()->ini_set('root_virtual',$virtual.$this->current()."/");	
			}

			BASIC_USERS::init()->refresh();
		}
		
    	return $this;
	}
	/**
	 * 
	 * Load the language data
	 * @throws Exception
	 */
	protected function lload(){
		if($this->lmethod == 'db'){
			$rdr = BASIC_SQL::init()->read_exec(" SELECT * FROM `".$this->lcontainer."` WHERE 1=1 AND `publish` = 1 ORDER BY `order_id` ");
			
			$err = BASIC_ERROR::init()->error();
			if($err['code'] == 1146){
				BASIC_SQL::init()->createTable('id', $this->container, "
					`code` varchar(2) NOT NULL DEFAULT '',
					`text` varchar(255) NOT NULL DEFAULT '',
					`encode` varchar(100) NOT NULL DEFAULT 'utf-8',
					`publish` int(1) NOT NULL DEFAULT '0',
					`flag` varchar(255) DEFAULT NULL,
					`order_id` int(11) NOT NULL DEFAULT '0',
					UNIQUE KEY `code` (`code`),
					KEY `publish` (`publish`)
				");
				
				BASIC_ERROR::init()->clean();
				$this->lload();
				return;
			}

			while ($rdr->read()) {
				$rdr->setItem('folder', $this->lfolder ? $this->lfolder : BASIC::init()->ini_get('upload_path'));
				
				$this->language[$rdr->item('code')] = $rdr->getItems();
 			}
		}else{
			throw new Exception(" The method '".$this->lmethod."' is not supported yet.");
		}
	}
}
CMS_LANGUAGE::init();