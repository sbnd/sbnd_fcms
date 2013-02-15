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
* @version 7.0.4  
*/


/**
 * SBND CMS 7 - instalator
 * 
 * @author Evgeni Baldzhiyski
 * @since 27.01.2012
 * @version 0.4
 */
if(file_exists(BASIC::init()->ini_get('root_path').'conf/site_config.php')){
	BASIC_URL::init()->redirect("/");
}
		
BASIC::init()->imported('bars.mod');
BASIC::init()->imported('form.mod', 'cms');
BASIC::init()->imported('Builder.mod', 'cms/controlers/front');

class BasicInstall extends DysplayComponent{
	
	public $template_form = 'cms-install.tpl';
	
	function main(){
		
		$this->setField("dbdriver", array(
			'text' => 'Database driver',
			'perm' => '*',
			'default' => 'mysqli',
			'formtype' => 'select',
			'attributes' => array(
				'data' => array(
					'mysql' => 'Original MySQL API', //templorary exsclide from installator
					'mysqli' => 'MySQL Improved Extension',
					//'mssql' => 'Microsoft SQL Server (beta)' //templorary exsclide from installator
				)
			)
		));
		$this->setField("dbhost", array(
			'text' => 'Database host',
			'perm' => '*',
			'default' => 'localhost'
		));
		$this->setField("dbuser", array(
			'text' => 'Database User',
			'perm' => '*',
			'default' => 'root',
			'help' => "Make sure that this user can create bases.",
			'messages' => array(
				2 => 'Wrong user name or password or have any other connection problem.'
			)
		));
		$this->setField("dbpass", array(
			'text' => 'Database password',
			'perm' => '*',
			'formtype' => 'password'
		));
		$this->setField("dbname", array(
			'text' => 'Database Name',
			'perm' => '*',
			'default' => 'sbnd_cms7'
		));
				
		$this->setField("adminemail", array(
			'text' => 'Administrator Email',
			'perm' => '*',
			'messages' => array(
				2 => 'Invalid email'
			)
		));		
		$this->setField("adminpass", array(
			'text' => 'Administrator Password',
			'perm' => '*',
			'formtype' => 'password',
			'messages' => array(
				2 => 'Password is invalid. Ex: Passw0rd - length < 8, 1 or more upper word, 1 or more lower letter and 1 or more number'
			)
		));		
		$this->setField("confirmpass", array(
			'text' => 'Confirm Password',
			'perm' => '*',
			'formtype' => 'password',
			'messages' => array(
				2 => 'Confirm password not match'
			)
		));
		$this->setField("adminname", array(
			'text' => 'Administrator Name',
			'default' => 'Admin'
		));					
		
		$this->delAction('add');
		$this->delAction('edit');
		$this->delAction('delete');
		$this->delAction('cancel');
		
		$this->updateAction('list', 'ActionFormAdd');
		
		$this->errorAction = 'list';
		
		$this->specialTest = 'validator';
		
		if(!$file = @fopen(BASIC::init()->ini_get('root_path')."conf/site_config.php", 'w')){
			BASIC_ERROR::init()->append(304, "The folder '".BASIC::init()->ini_get('root_path')."conf/' must be readdable. ");
		}else{
			@fclose($file);
			@unlink(BASIC::init()->ini_get('root_path')."conf/site_config.php");
		}
		
		$this->updateAction('save', null, 'Install');
	}
	function ActionSave($id){
		$buffer = ''; $file = fopen(BASIC::init()->ini_get('root_path')."conf/default_site_config.php", 'r');
		while (!feof($file)) {
			$buffer .= fread($file, 1024);
		}
		fclose($file);
		
		$buffer = str_replace('${dbdriver}', $this->getDataBuffer('dbdriver'), $buffer);
		$buffer = str_replace('${dbuser}', $this->getDataBuffer('dbuser'), $buffer);
		$buffer = str_replace('${dbpass}', $this->getDataBuffer('dbpass'), $buffer);
		$buffer = str_replace('${dbname}', $this->getDataBuffer('dbname'), $buffer);
		$buffer = str_replace('${dbhost}', $this->getDataBuffer('dbhost'), $buffer);
	
		$file = fopen(BASIC::init()->ini_get('root_path')."conf/site_config.php", 'w');
		fwrite($file, $buffer);
		fclose($file);
		
//		copy(BASIC::init()->ini_get('root_path')."conf/default_sbnd_cms7.sql", 
//			BASIC::init()->ini_get('root_path')."conf/sbnd_cms7.sql");
		$file = fopen(BASIC::init()->ini_get('root_path')."conf/default_sbnd_cms7.php", 'r');
		$buffer = ''; while (!@feof($file)) {
			$buffer .= fread($file, 2048);
		}
		@fclose($file);
		
		$buffer .= "\n\nUPDATE `profiles` SET ".
			"`email` = '".$this->getDataBuffer("adminemail")."', ".
			"`password` = '".BASIC_USERS::passwordCripter($this->getDataBuffer('adminpass'))."', ".
			"`name` = '".$this->getDataBuffer('adminname')."' ".
		"WHERE `id` = 1 ";
		
		$file = fopen(BASIC::init()->ini_get('root_path')."conf/sbnd_cms7.php", 'a');
		fwrite($file, $buffer);
		fclose($file);
		
		BASIC_SQL::init(array(
			'backupEngine' => new CmsBackup(array(
				'default' => 'conf/sbnd_cms7.php'
			))
		))->backupEngine->revert();
		
		BASIC_url::init()->redirect("/");
	}
	function validator(){
		if(!BASIC::init()->validEmail($this->getDataBuffer('adminemail'))){
			$this->setMessage('adminemail', 2);
		}
		if(BASIC_USERS::passwordValidator($this->getDataBuffer('adminpass'))){
			$this->setMessage('adminpass', 2);
		}
		if($this->getDataBuffer('adminpass') != $this->getDataBuffer('confirmpass')){
			$this->setMessage('confirmpass', 2);
		}
		try{
			BASIC_SQL::init()->connect($this->getDataBuffer('dbdriver').'://'.$this->getDataBuffer('dbuser').':'.$this->getDataBuffer('dbpass').'@'.$this->getDataBuffer('dbhost').'/'.$this->getDataBuffer('dbname'), 'utf8');
		}catch (Exception $e){
			$this->setMessage('dbuser', 2);

		}
	}
	function createInterface(){
		$this->startManager();
		return parent::createInterface();
	}
}
CMS_SETTINGS::init()->set('SITE_THEME', 'themes/responsive/');
BASIC_USERS::init(array(
	'permition_manager' => null
));

BASIC_GENERATOR::init()->head("ptitle", 'title', null, 'SBND CMS 7 - Install');
BASIC_GENERATOR::init()->head('style', 'style', null, "
.install {
	clear: both;
	margin: 65px 0 0;
	padding: 20px;
	min-width: 944px;
	background: #fff;
	border: 1px solid #d1d1d1;
	position: relative;
}
.install table.frmcnt {
	width: 960px;
}
.install table td {
	padding: 7px 5px;
	vertical-align: top;
}
.install .btn{
	margin: 0 !important;
}
.install label {
	display: block;
	margin: 7px 0 0;
	width: 200px;
	text-align: right;
	font-weight: bold;
	font-size: 14px;
	color: #000;
}
.frm select{
	width: 224px;
}
.frm input {
	width: 200px;
}
.frmwrap {
	position: relative;
}
.tooltipicon {
	display: block;
	width: 17px;
	height: 17px;
	background: url(".CMS_SETTINGS::init()->get('SITE_THEME')."img/tooltip.png) top left no-repeat;
	cursor: pointer;
	position: absolute;
	top: 6px;
	left: 186px;
	z-index: 10;
}
");
BASIC_TEMPLATE2::init(array(
	'template_path' => CMS_SETTINGS::init()->get('SITE_THEME').'tpl'
))->createTemplate('cms-install.tpl', '<div class="frm install">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="frmcnt">
		<!-- foreach(${fields},key,field) -->
		<!-- if(${key} == "adminemail") -->
			<tr><td colspan="2" style="height:20px;"></td></tr>
		<!-- end -->
		<tr>
			<td><label><!-- if(${field.perm}) --><span>${field.perm}</span><!-- end -->${field.label}</label></td>
			<td width="100%">
				<div class="frmwrap">
					${field.ctrl}
					<!-- if(${field.message}) -->
					<span class="tooltipicon" title="${field.message}">&nbsp;</span>
					<!-- end -->
				</div>
			</td>
		</tr>
		<!-- end -->
		<tr>
			<td>&nbsp;</td>
			<td>
				<!-- template(cmp-form-action-bar.tpl,${buttons_bar}) -->
			</td>
		</tr>		
	</table>
</div>');
Builder::init(array(
	'jQueryVersion' => '1.4.2',
	'jQueryUIVersion' => '1.7.3.custom',
	'useJSSvincs' => true,
	'baseTemplate' 	=> 'base-login.tpl'
));

$install = new BasicInstall();
$install->main();

BASIC_TEMPLATE2::init()->set(array(
	'CONTENT' => $install->createInterface(),
	'THEME'   => BASIC::init()->ini_get('root_virtual').CMS_SETTINGS::init()->get('SITE_THEME')
));

die(Builder::init()->compileTemplate());