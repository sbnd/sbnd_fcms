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
* @package cms.ajax
* @version 7.0.4
*/

BASIC::init()->imported('ajax.mod');
/**
 * Plugin for BASIC_TEMPLATE2. Make templates tag <!-- ajaxbox(service_name, parametters) -->. The tag make virtual box
 * that work like IFRAME when click links that have attribute "name=ajaxbox if the link didn`t have this attribute the system will run 
 * main page. 
 * The plugin used the javascript library called "Svincs".
 * 
 * 	service_name - service that call from box init
 * 	params - standart HTML div parameters + attribute "params" for service URL request
 * 
 * example code:
 * <code>
 * 		template :: <!-- ajaxbox(ConferenceGalleryConf,id=gallery_conf|params=id:${conference_id}) -->
 * 	</code>
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.ajax 
 */
class BasicTemplatePluginAjaxBox implements BasicTemplatePluginInterface{
	/**
	 * Function to parse syntax
	 * @see BasicTemplatePluginInterface::parse()
	 * @param string $source
	 * @access public
	 */
	public function parse($source){
		return preg_replace_callback('/<!-- (ajaxbox)\(([^\)]+)\) -->/',array($this,'_parse'),$source);
	}
	/**
	 * 
	 * Private function called from parse callback
	 * @param array $match
	 * @access private
	 */
	private function _parse($match){
		$spl = explode(',', $match[2]);
		
		$build = '';
		if($spl[0][0] == '$'){
			$build .= preg_replace('/\$\{([^\}]+)\}/', "@$$1", $spl[0]);
		}else{
			$build .= "'".preg_replace('/["\']+/', "", $spl[0])."'";
		}
		if(isset($spl[1])){
			$spl[1] = preg_replace('/[\'"]/', '', $spl[1]);
			$spl[1] = preg_replace('/\$\{([^\}]+)\}/', "'.@$$1.'", $spl[1]);
			
			$build .= ", '".$spl[1]."'";
		}
		return "<?php print BasicTemplatePluginAjaxBox::parser(".$build."); ?>";
	}
	
	static public function parser($service, $attributes = ''){
		return BASIC_AJAX::init()->ajaxbox($service, $attributes);
	}
}
/**
 * Basic ajax extension that save two special services:
 * 	"component" - support standart cms component's serv and use Builder::init()->pagesControler->template_popup for return data
 * 	"service"  - same like "component" but return directly startPanel data 
 * 
 * @author Evgeni Baldzhiyski
 * @version 0.1
 * @package cms.ajax 
 */
class CmsAjax extends BASIC_AJAX{
	/**
	 * @param array $arr
	 * @static
	 * @return BASIC_AJAX
	 */
	static public function init($arr = array()){
		if(!isset($GLOBALS['BASIC_AJAX'])){
			$GLOBALS['BASIC_AJAX'] = new CmsAjax();
			
			BASIC_TEMPLATE2::init()->driver->addPlugin('BasicTemplatePluginAjaxBox', new BasicTemplatePluginAjaxBox());
		}
		foreach ($arr as $k => $v){
			$GLOBALS['BASIC_AJAX']->$k = $v;
		}
		return $GLOBALS['BASIC_AJAX'];
	}
	/**
	 * 
	 * Listener for query
	 * 	
	 * @see BASIC_AJAX::listenerRemote()
	 */
	function listenerRemote(){
		if(BASIC_URL::init()->test('ajax') && BASIC_URL::init()->request('ajax') == ''){
			Builder::init()->baseTemplate = Builder::init()->pagesControler->template_popup;
			
			BASIC_TEMPLATE2::init()->set('CONTENT', Builder::init()->compilePage()->startPanel());
		
			die(Builder::init()->compileTemplate());
		}
		parent::listenerRemote();
	}
}
CmsAjax::init();