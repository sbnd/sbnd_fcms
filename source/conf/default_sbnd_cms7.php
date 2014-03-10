<?php die('access denied'); /*
#
# BASIC CMS 7 - install database 
#
DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL DEFAULT '',
  `text` varchar(255) NOT NULL DEFAULT '',
  `encode` varchar(100) NOT NULL DEFAULT 'utf-8',
  `publish` int(1) NOT NULL DEFAULT '0',
  `flag` varchar(255) NOT NULL DEFAULT '',
  `order_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `publish_and_order` (`publish`,`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `languages` VALUES (1,'en','English','utf-8',1,'upload/FLAG969595.ico',0);

DROP TABLE IF EXISTS `lingual`;
CREATE TABLE `lingual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(255) NOT NULL,
  `value_en` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variable` (`variable`)
) ENGINE=MyISAM AUTO_INCREMENT=381 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `lingual` VALUES (419,'username','Username');
INSERT INTO `lingual` VALUES (420,'password','Password');
INSERT INTO `lingual` VALUES (421,'invalid_password','Invalid Password Format');
INSERT INTO `lingual` VALUES (422,'rememberme','Remember Me');
INSERT INTO `lingual` VALUES (423,'login','Log In');
INSERT INTO `lingual` VALUES (424,'cms_cmp_login','Log In');
INSERT INTO `lingual` VALUES (425,'cms_cmp_reg','Register');
INSERT INTO `lingual` VALUES (426,'forgot_pass_label','Forgot your password?');
INSERT INTO `lingual` VALUES (427,'cms_cmp_forgot_pass','Forgot your password?');
INSERT INTO `lingual` VALUES (428,'cms_cmp_profile','Profile');
INSERT INTO `lingual` VALUES (429,'is_required','Mandatory field');
INSERT INTO `lingual` VALUES (430,'not_valid_data','Invalid Data');
INSERT INTO `lingual` VALUES (431,'not_have_permitions','Not Permitted');
INSERT INTO `lingual` VALUES (432,'username_or_password','Username or E-mail');
INSERT INTO `lingual` VALUES (433,'invalid_email_format','Invalid E-mail Format');
INSERT INTO `lingual` VALUES (434,'email_already_in_db','There is already a user in our database with this E-mail');
INSERT INTO `lingual` VALUES (435,'not_existing_email','This E-mail does not exist in our database');
INSERT INTO `lingual` VALUES (436,'fault_to_generate_new_pass','There was a problem to generate a new password');
INSERT INTO `lingual` VALUES (437,'get_new_password','Get New Password');
INSERT INTO `lingual` VALUES (438,'new_password_sent_success','Check your e-mail for the confirmation link.');
INSERT INTO `lingual` VALUES (439,'back_to_login','Back to Log In');
INSERT INTO `lingual` VALUES (440,'logout','Log Out');
INSERT INTO `lingual` VALUES (441,'welcome','Welcome');
INSERT INTO `lingual` VALUES (442,'add','Add');
INSERT INTO `lingual` VALUES (443,'delete','Delete');
INSERT INTO `lingual` VALUES (444,'list','List');
INSERT INTO `lingual` VALUES (445,'cms_cmp_languages','Languages');
INSERT INTO `lingual` VALUES (446,'cms_cmp_pages','Pages');
INSERT INTO `lingual` VALUES (447,'of','of');
INSERT INTO `lingual` VALUES (448,'you_are_here','You are editing');
INSERT INTO `lingual` VALUES (449,'vertical_menu','Menu');
INSERT INTO `lingual` VALUES (450,'edit','Edit');
INSERT INTO `lingual` VALUES (451,'filter','Filter Data');
INSERT INTO `lingual` VALUES (452,'update','Update');
INSERT INTO `lingual` VALUES (453,'save','Save');
INSERT INTO `lingual` VALUES (454,'back','Back');
INSERT INTO `lingual` VALUES (455,'page','Page');
INSERT INTO `lingual` VALUES (456,'items','Items');
INSERT INTO `lingual` VALUES (457,'show','Show');
INSERT INTO `lingual` VALUES (458,'all','All');
INSERT INTO `lingual` VALUES (459,'filter_btn','Filter');
INSERT INTO `lingual` VALUES (460,'no','No');
INSERT INTO `lingual` VALUES (461,'yes','Yes');
INSERT INTO `lingual` VALUES (462,'settings_variable_label','Name');
INSERT INTO `lingual` VALUES (463,'are_you_sure','Are You Sure?');
INSERT INTO `lingual` VALUES (464,'language_open','Language Variables');
INSERT INTO `lingual` VALUES (465,'Import','Import from a File');
INSERT INTO `lingual` VALUES (466,'Export','Export to a File');
INSERT INTO `lingual` VALUES (467,'not_match_pass','Passwords do not Match');
INSERT INTO `lingual` VALUES (468,'cms_cmp_module_settings','Module Settings');
INSERT INTO `lingual` VALUES (469,'welcome_home_page','Welcome to the SBND F&CMS Home page!');
INSERT INTO `lingual` VALUES (470,'cms_cmp_menu_positions','Menu Positions');
INSERT INTO `lingual` VALUES (471,'unique_position','This position already exists');
INSERT INTO `lingual` VALUES (472,'menu_positions_label','Position');
INSERT INTO `lingual` VALUES (473,'cms_cmp_settings','Settings');
INSERT INTO `lingual` VALUES (474,'settings_value_label','Value');
INSERT INTO `lingual` VALUES (475,'settings_system_label','System Variable Only');
INSERT INTO `lingual` VALUES (476,'language_title_label','Title');
INSERT INTO `lingual` VALUES (477,'language_code_label','Code');
INSERT INTO `lingual` VALUES (478,'language_encode_label','Encoding');
INSERT INTO `lingual` VALUES (479,'langualge_publish_lanel','Published');
INSERT INTO `lingual` VALUES (480,'langualge_flag_lanel','Flag Image');
INSERT INTO `lingual` VALUES (481,'langual_variable_labe','Name');
INSERT INTO `lingual` VALUES (482,'lingual_value_label','Value');
INSERT INTO `lingual` VALUES (483,'cms_cmp_lingual','Language Variables');
INSERT INTO `lingual` VALUES (484,'cms_cmp_templates','Templates');
INSERT INTO `lingual` VALUES (485,'templates_name_label','Name');
INSERT INTO `lingual` VALUES (486,'templates_import_form_action','Import from a File');
INSERT INTO `lingual` VALUES (487,'templates_export_action','Export to a File');
INSERT INTO `lingual` VALUES (488,'templates_body_label','Content');
INSERT INTO `lingual` VALUES (489,'template_import_label','Import from a File');
INSERT INTO `lingual` VALUES (492,'templates_import_action','Import');
INSERT INTO `lingual` VALUES (493,'module_class_not_exist','Such PHP Class does not exist into the BASIC Source Code');
INSERT INTO `lingual` VALUES (494,'module_folder_not_exist','Such Folder does not Exist');
INSERT INTO `lingual` VALUES (495,'modul_name_label','System Name');
INSERT INTO `lingual` VALUES (496,'modul_class_label','PHP Class Name');
INSERT INTO `lingual` VALUES (497,'modul_folder_label','Residing Folder');
INSERT INTO `lingual` VALUES (498,'modul_public_name_label','Public Name');
INSERT INTO `lingual` VALUES (499,'module_admin_group_field','Participate in Admin Menu Group');
INSERT INTO `lingual` VALUES (500,'parent','Parent');
INSERT INTO `lingual` VALUES (501,'cms_cmp_module_groups','Admin Menu Groups');
INSERT INTO `lingual` VALUES (502,'cms_cmp_modules','Components');
INSERT INTO `lingual` VALUES (503,'modul_cmp_settings_label','Settings');
INSERT INTO `lingual` VALUES (504,'modul_aname_label','Name');
INSERT INTO `lingual` VALUES (505,'modul_avalue_label','Value');
INSERT INTO `lingual` VALUES (506,'modul_del_label','Delete');
INSERT INTO `lingual` VALUES (507,'modul_group_name_label','Name');
INSERT INTO `lingual` VALUES (508,'cms_cmp_profiles','Users');
INSERT INTO `lingual` VALUES (509,'profiles_name','Name');
INSERT INTO `lingual` VALUES (510,'_email','E-mail');
INSERT INTO `lingual` VALUES (511,'complete_address','Address');
INSERT INTO `lingual` VALUES (512,'active','Active');
INSERT INTO `lingual` VALUES (513,'cms_cmp_profiles_types','Manage Roles');
INSERT INTO `lingual` VALUES (514,'can_not_remove_used_module_groups','Can not Delete a Role that was already Assigned to a User');
INSERT INTO `lingual` VALUES (515,'profiles_role','Role');
INSERT INTO `lingual` VALUES (516,'_password','Password Again');
INSERT INTO `lingual` VALUES (517,'profil_admin_access_label','Administrative Access');
INSERT INTO `lingual` VALUES (518,'profiles_lingual_label','Preferable Language');
INSERT INTO `lingual` VALUES (519,'profiles_page_max_rows_label','Preferable results to be shown on page');
INSERT INTO `lingual` VALUES (520,'image','Image');
INSERT INTO `lingual` VALUES (521,'email','E-mail');
INSERT INTO `lingual` VALUES (522,'message','Message');
INSERT INTO `lingual` VALUES (523,'title','Title');
INSERT INTO `lingual` VALUES (524,'name','Name');
INSERT INTO `lingual` VALUES (525,'url_name','Url Name');
INSERT INTO `lingual` VALUES (526,'description','Description');
INSERT INTO `lingual` VALUES (527,'short_description','Short Description');
INSERT INTO `lingual` VALUES (528,'forgoten','Forgotten Password');
INSERT INTO `lingual` VALUES (529,'registration','Register');
INSERT INTO `lingual` VALUES (530,'content_menu_position_label','Include in menu');
INSERT INTO `lingual` VALUES (531,'publish','Publish');
INSERT INTO `lingual` VALUES (532,'body','Body');
INSERT INTO `lingual` VALUES (533,'target_parameters','Open in - settings');
INSERT INTO `lingual` VALUES (534,'content_target_label','Open in');
INSERT INTO `lingual` VALUES (535,'component_name','Component Name');
INSERT INTO `lingual` VALUES (536,'meta_description','META Description');
INSERT INTO `lingual` VALUES (537,'meta_key','META Key');
INSERT INTO `lingual` VALUES (538,'urlvars','Variables in URL');
INSERT INTO `lingual` VALUES (539,'permalink','Permalink');
INSERT INTO `lingual` VALUES (540,'search_results_number','Search Results');
INSERT INTO `lingual` VALUES (541,'content_public_name_label','Public name');
INSERT INTO `lingual` VALUES (542,'content_name_label','System name');
INSERT INTO `lingual` VALUES (543,'content_pblish_label','Show in language version');
INSERT INTO `lingual` VALUES (544,'content_body_label','Content');
INSERT INTO `lingual` VALUES (545,'position','Position');
INSERT INTO `lingual` VALUES (546,'db_table','Database Table');
INSERT INTO `lingual` VALUES (547,'preview','Preview');
INSERT INTO `lingual` VALUES (548,'module_perm_group_label','Group  Name');
INSERT INTO `lingual` VALUES (549,'accessibility','Accessibility');
INSERT INTO `lingual` VALUES (550,'order_by','Order By');
INSERT INTO `lingual` VALUES (551,'order_type','Order Type');
INSERT INTO `lingual` VALUES (552,'id','ID');
INSERT INTO `lingual` VALUES (553,'order_id','Order ID');
INSERT INTO `lingual` VALUES (554,'ASC','Ascending');
INSERT INTO `lingual` VALUES (555,'DESC','Descending');
INSERT INTO `lingual` VALUES (556,'private','Private');
INSERT INTO `lingual` VALUES (557,'public','Public');
INSERT INTO `lingual` VALUES (558,'target','Target');
INSERT INTO `lingual` VALUES (559,'zip_code','Zip Code');
INSERT INTO `lingual` VALUES (560,'City','City');
INSERT INTO `lingual` VALUES (562,'photo_of_the_institution','Photo');
INSERT INTO `lingual` VALUES (563,'left_menu','Left Menu');
INSERT INTO `lingual` VALUES (564,'top_menu','Top Menu');
INSERT INTO `lingual` VALUES (565,'right_menu','Right Menu');
INSERT INTO `lingual` VALUES (566,'article_menu','Article Menu');
INSERT INTO `lingual` VALUES (567,'bottom_menu','Bottom Menu');
INSERT INTO `lingual` VALUES (568,'search_results_empty','Your search did not match any documents.');
INSERT INTO `lingual` VALUES (569,'search','Search');
INSERT INTO `lingual` VALUES (570,'parm_fields','Mandatory Fields');
INSERT INTO `lingual` VALUES (571,'invalid_email','Invalid E-mail');
INSERT INTO `lingual` VALUES (572,'no_unique_name','Not a unique name');
INSERT INTO `lingual` VALUES (573,'template_form',' Form Template');
INSERT INTO `lingual` VALUES (574,'cms_cmp_access','Login');
INSERT INTO `lingual` VALUES (575,'invalid_lang_code_characters','Invalid character. You can use only "a-z", "A-Z" or "_" symbols for language\'s code.');
INSERT INTO `lingual` VALUES (576,'templates_mdate_label','Date Created');
INSERT INTO `lingual` VALUES (577,'settings_lingual_label','Multi Language Variable');
INSERT INTO `lingual` VALUES (578,'template_list','List Template');
INSERT INTO `lingual` VALUES (579,'prefix','Prefix');
INSERT INTO `lingual` VALUES (580,'template_total','Total Template');
INSERT INTO `lingual` VALUES (581,'max_image_size','Maximum Image Size');
INSERT INTO `lingual` VALUES (582,'need_set_required_settings','You Need To Set Required Settings');
INSERT INTO `lingual` VALUES (583,'user_message_required_settings','You Need To Set Required Settings');

INSERT INTO `lingual` VALUES (585,'invalid_sec_code','Invalid Security Code');
INSERT INTO `lingual` VALUES (586,'cms_cmp_permitions','Permissions');
INSERT INTO `lingual` VALUES (588,'cms_cmp_search_bar','Language Bar');
INSERT INTO `lingual` VALUES (589,'cms_cmp_language_bar','Invalid character. You can use only "a-z", "A-Z" or "_" symbols for language\'s code.');
INSERT INTO `lingual` VALUES (590,'profil_user_access_label',' Profile');
INSERT INTO `lingual` VALUES (591,'module_forgot_pass_mail_subject','SBND CMS Password recovery');
INSERT INTO `lingual` VALUES (592,'active_new_password','Activate New Password');
INSERT INTO `lingual` VALUES (593,'subtitle','Subtitle');

INSERT INTO `lingual` VALUES (614,'access_denied','Access Denied');
INSERT INTO `lingual` VALUES (615,'too_short_password','Password is too short');
INSERT INTO `lingual` VALUES (616,'not_exist_number','There must be atleast one number [1-9]');
INSERT INTO `lingual` VALUES (617,'not_exist_upper_case','Atleast one capital letter');
INSERT INTO `lingual` VALUES (618,'not_exist_lower_case','Atleast one lower case letter');
INSERT INTO `lingual` VALUES (619,'forgot_mail_template','Forgotten Email Template');
INSERT INTO `lingual` VALUES (620,'go_to_after_send','Destination after sending the password');
INSERT INTO `lingual` VALUES (621,'password_rules','Use At least one capital letter, one lowercase and one number');
INSERT INTO `lingual` VALUES (622,'use_auto_login','Use Auto Login');
INSERT INTO `lingual` VALUES (623,'use_auto_active','Use Auto Activate');
INSERT INTO `lingual` VALUES (624,'use_free_password','Use Free Password');
INSERT INTO `lingual` VALUES (625,'regist_user_level','Level for registred user');
INSERT INTO `lingual` VALUES (626,'go_to_after_reg','Destination after registration');

INSERT INTO `lingual` VALUES (627,'switchMode','Switch Mode');
INSERT INTO `lingual` VALUES (628,'useSaveState','Use Save State');
INSERT INTO `lingual` VALUES (629,'search_targets','Search Targets');
INSERT INTO `lingual` VALUES (630,'result_page','Result Page');
INSERT INTO `lingual` VALUES (631,'sess_var_for_last_path','Session Variable for last path');
INSERT INTO `lingual` VALUES (632,'forgotten_page','Forgotten Password Page');
INSERT INTO `lingual` VALUES (633,'register_page','Registration Page');
INSERT INTO `lingual` VALUES (634,'profile_page','Profile Page');
INSERT INTO `lingual` VALUES (635,'site_login_total','Site Login Total');

INSERT INTO `lingual` VALUES (637,'site_login_none','Site Login Mode : none');
INSERT INTO `lingual` VALUES (638,'site_login_box','Site Login Mode : box');
INSERT INTO `lingual` VALUES (639,'file','Attachment');
INSERT INTO `lingual` VALUES (640,'module_settings','Module Settings');
INSERT INTO `lingual` VALUES (641,'dashboard','Dashboard');
INSERT INTO `lingual` VALUES (642,'menu_image1','Additional menu image 1');
INSERT INTO `lingual` VALUES (643,'menu_image2','Additional menu image 2');
INSERT INTO `lingual` VALUES (644,'upoad_error_1','The uploaded file exceeds the upload_max_filesize directive in php.ini.');
INSERT INTO `lingual` VALUES (645,'upoad_error_2','The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
INSERT INTO `lingual` VALUES (646,'upoad_error_3','The uploaded file was only partially uploaded.');
INSERT INTO `lingual` VALUES (647,'upoad_error_4','No file was uploaded.');
INSERT INTO `lingual` VALUES (648,'upoad_error_5','No exist file variable in request');
INSERT INTO `lingual` VALUES (649,'upoad_error_10','No success reamed uploaded file.');
INSERT INTO `lingual` VALUES (650,'upoad_error_11','The uploaded file exceeds the max field directive');
INSERT INTO `lingual` VALUES (651,'upoad_error_12','This file type is not supported. Select only the allowed types');
INSERT INTO `lingual` VALUES (652,'upoad_error_13','The uploaded file name is olready exist');
INSERT INTO `lingual` VALUES (653,'upoad_error_14','The uploaded file can\'t copied in destination directory');
INSERT INTO `lingual` VALUES (654,'upoad_error_15','Cannot removed file.');
INSERT INTO `lingual` VALUES (655,'upoad_error_16','Upload folder does\'t exist and can't create it.');
INSERT INTO `lingual` VALUES (656,'upoad_error_17','Cannot create temporary file.');
INSERT INTO `lingual` VALUES (657,'browse','browse');

DROP TABLE IF EXISTS `positions`;
CREATE TABLE `positions` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `tag` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tag_name` (`name`,`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `positions` VALUES (1, 'top', 'menu-positions');
INSERT INTO `positions` VALUES (2, 'bottom', 'menu-positions');

DROP TABLE IF EXISTS `module_groups`;
CREATE TABLE `module_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `_parent_self` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `name_en` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `_parent_self` (`_parent_self`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `module_groups` VALUES (1,0,0,'system');

DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `class` varchar(255) NOT NULL DEFAULT '',
  `folder` varchar(255) NOT NULL DEFAULT '',
  `cmp_settings` text,
  `public_name_en` varchar(255) NOT NULL DEFAULT '',
  `_parent_self` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `require_settings` int(1) NOT NULL DEFAULT '0',
  `admin_group` int(11) NOT NULL DEFAULT '0',
  `admin_support` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `admin_group` (`admin_group`),
  KEY `_parent_self` (`_parent_self`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `modules` VALUES (1,'language-bar','LanguageBar','cms/controlers/front','','Language Bar',0,0,0,0,0);
INSERT INTO `modules` VALUES (2,'search-bar','SearchBar','cms/controlers/front','','Search',0,1,0,0,0);
INSERT INTO `modules` VALUES (3,'login','Login','cms/controlers/front','','Site Login',0,2,0,0,0);
INSERT INTO `modules` VALUES (4,'tiny-editor','TinyMCEComponent','plugins/tinymce','a:2:{s:7:"manager";s:0:"";s:19:"prepareCofiguration";i:1;}','TinyMCE Editor',0,3,0,0,0);

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) NOT NULL DEFAULT '',
  `_parent_self` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `meta_key_en` varchar(255) NOT NULL DEFAULT '',
  `meta_description_en` varchar(255) NOT NULL DEFAULT '',
  `component_name` varchar(255) NOT NULL DEFAULT '',
  `location` varchar(255) NOT NULL DEFAULT '',
  `target` varchar(10) NOT NULL DEFAULT '_self',
  `target_params` varchar(500) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `publish_en` int(1) NOT NULL DEFAULT '0',
  `body_en` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `_parent_self` (`_parent_self`),
  KEY `order_id` (`order_id`),
  KEY `get_page` (`name`,`_parent_self`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `pages` VALUES (1,'Home',0,0,'Home','Home','','','_self','','home',1,'Welcome to SBND F&CMS');

CREATE TABLE `multiple_int_11` (
  `fkey` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `tag` varchar(255) NOT NULL,
  KEY `multiple_index` (`fkey`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `multiple_int_11` VALUES (1,1,'pages_position');

DROP TABLE IF EXISTS `permitions`;
CREATE TABLE `permitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmp_name` varchar(255) NOT NULL DEFAULT '',
  `access` text,
  `_parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `foreign` (`_parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `permitions` VALUES (1,'menu-positions','',-2); 
INSERT INTO `permitions` VALUES (3,'contents','',-2);
INSERT INTO `permitions` VALUES (4,'settings','',-2);
INSERT INTO `permitions` VALUES (5,'languages','',-2);
INSERT INTO `permitions` VALUES (6,'lingual','',-2);
INSERT INTO `permitions` VALUES (7,'templates','',-2);
INSERT INTO `permitions` VALUES (8,'modules','',-2);
INSERT INTO `permitions` VALUES (9,'module-settings','',-2);
INSERT INTO `permitions` VALUES (10,'module-groups','',-2);
INSERT INTO `permitions` VALUES (11,'profiles','',-2);
INSERT INTO `permitions` VALUES (12,'profiles-types','',-2);
INSERT INTO `permitions` VALUES (13,'permitions','',-2);
INSERT INTO `permitions` VALUES (14,'search-bar','list',-2);
INSERT INTO `permitions` VALUES (15,'language-bar','list',-2);
INSERT INTO `permitions` VALUES (16,'login','user-access',-2);
INSERT INTO `permitions` VALUES (17,'admin-login','',-2);
INSERT INTO `permitions` VALUES (18,'dashboard','',-2);

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `active` int(1) NOT NULL DEFAULT '0',
  `level` int(1) NOT NULL DEFAULT '0',
  `last_log` int(15) NOT NULL DEFAULT '0',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `zip_code` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `language` varchar(2) NOT NULL DEFAULT '',
  `page_max_rows` int(3) NOT NULL DEFAULT '0',
  `latitude` varchar(255) NOT NULL DEFAULT '',
  `longitude` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`email`,`password`),
  UNIQUE KEY `email` (`email`),
  KEY `active` (`active`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `profiles` VALUES (1,'admin@admin.net','',1,1,0,'','Admin','','','','','en',-1,'','');

DROP TABLE IF EXISTS `profiles_types`;
CREATE TABLE `profiles_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `profiles_types` VALUES (-2,'Guest',0);
INSERT INTO `profiles_types` VALUES (1,'Admin',1);

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(500) DEFAULT NULL,
  `lingual` int(1) NOT NULL DEFAULT '0',
  `system` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `variable` (`variable`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `settings` VALUES (1,'SITE_LANGUAGE','en',0,1);
INSERT INTO `settings` VALUES (2,'SITE_CHARSET','utf-8',0,1);
INSERT INTO `settings` VALUES (3,'list_max_rows','10',0,1);
INSERT INTO `settings` VALUES (4,'SITE_NAME','en=SBND F&CMS',1,1);
INSERT INTO `settings` VALUES (5,'SITE_DESK','en=SBND F&CMS - Framework & CMS for PHP developers',1,1);
INSERT INTO `settings` VALUES (6,'SITE_KEYS','en=new,php,cms,SBND',1,1);
INSERT INTO `settings` VALUES (7,'session_time','1800',0,1);
INSERT INTO `settings` VALUES (8,'SITE_START_PAGE','home',0,1);
INSERT INTO `settings` VALUES (9,'SITE_EMAIL','support@sbnd.net',0,1);
INSERT INTO `settings` VALUES (10,'SITE_THEME','themes/responsive/',0,1);
INSERT INTO `settings` VALUES (11,'SITE_LOGIN_MODE','box',0,1);
INSERT INTO `settings` VALUES (12,'SITE_TEMPLATE','base.tpl',0,1);
INSERT INTO `settings` VALUES (13,'SITE_OPEN','',1,1);
INSERT INTO `settings` VALUES (14,'SITE_THEME_NAME','responsive',0,1);
INSERT INTO `settings` VALUES (15,'SITE_DATA_DELETE','archive',0,1);