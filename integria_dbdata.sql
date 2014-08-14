-- Integria - http://integria.sourceforge.net
-- ==================================================
-- Copyright (c) 2007-2011 Artica Soluciones Tecnologicas

-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; version 2
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.


INSERT INTO `tconfig` (`token`, `value`) VALUES  
('language_code','en_GB'),
('block_size','25'),
('db_scheme_version','4.1.15'),
('db_scheme_build','140814'),
('date_format', 'F j, Y, g:i a'),
('currency', 'eu'),
('sitename', 'Integria IMS - the ITIL Management System'),
('hours_perday','8'),
('FOOTER_EMAIL','Please do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n'),
('HEADER_EMAIL','Hello, \n\nThis is an automated message coming from Integria\n\n'),
('notification_period','24'),
('limit_size','250'),
('api_password',''),
('flash_charts','1'),
('fontsize', 6),
('auth_methods', 'mysql'),
('wiki_plugin_dir', 'include/wiki/plugins/'),
('conf_var_dir', 'wiki_data/'),
('enable_pass_policy', 0),
('pass_size', 4),
('pass_needs_numbers', 0),
('pass_needs_symbols', 0),
('pass_expire', 0),
('first_login', 0),
('mins_fail_pass', 5),
('number_attempts', 5),
('max_days_events', 30),
('max_days_incidents', 15),
('max_days_wu', 365),
('max_days_wo', 365),
('max_days_audit', 15),
('max_days_session', 7),
('max_days_workflow_events', 900),
('update_manager_installed', 1),
('current_package', 15),
('url_updatemanager', 'https://artica.es/integriaupdate4/server.php'),
('license', 'INTEGRIA-FREE'),
('minor_release', 12);

-- Default password is 'integria'

INSERT INTO `tusuario` (id_usuario, nombre_real, password, comentarios, fecha_registro, direccion, telefono, nivel, avatar, lang, pwdhash, disabled) VALUES ('admin','Default Admin','2f62afb6e17e46f0717225bcca6225b7','Default Integria Admin superuser. Please change password ASAP','2007-03-27 18:59:39','admin@integria.sf.net','555-555-555',1,'moustache3','en_GB','',0);


INSERT INTO `tgrupo` (id_grupo, nombre, icon, parent, id_user_default) VALUES (1,'All','world.png',0, 'admin');
INSERT INTO `tgrupo` (id_grupo, nombre, icon, parent, id_user_default) VALUES (2,'Customer #A','eye.png',0, 'admin');
INSERT INTO `tgrupo` (id_grupo, nombre, icon, parent, id_user_default) VALUES (3,'Customer #B','eye.png',0, 'admin');
INSERT INTO `tgrupo` (id_grupo, nombre, icon, parent, id_user_default) VALUES (4,'Engineering','computer.png',0, 'admin');

INSERT INTO `tlanguage` VALUES ('en_GB','English');
INSERT INTO `tlanguage` VALUES ('es','Español');
INSERT INTO `tlanguage` VALUES ('ru','Русский');
INSERT INTO `tlanguage` VALUES ('fr','Français');
INSERT INTO `tlanguage` VALUES ('zh_CN','简化字');
INSERT INTO `tlanguage` VALUES ('de','Deutch');
INSERT INTO `tlanguage` VALUES ('pl','Polski');


INSERT INTO `tlink` VALUES  (1,'Integria Project','http://integria.sourceforge.net'), (2,'Artica ST','http://www.artica.es'), (3, 'Report a bug', 'https://sourceforge.net/tracker/?func=add&group_id=193754&atid=946680');

INSERT INTO `tproject` VALUES  (-1,'Non imputable hours (Special)','','0000-00-00','0000-00-00','',1,0);

ALTER TABLE tproject AUTO_INCREMENT = 1;

INSERT INTO `ttask` (`id`, `id_project`, `id_parent_task`, `name`, `description`, `completion`, `priority`, `dep_type`, `start`) VALUES (-1,-1,0,'Vacations','',0,0,0,'0000-00-00'),(-2,-1,0,'Health issues','',0,0,0,'0000-00-00'),(-3,-1,0,'Not justified','',0,0,0,'0000-00-00'), (-4,-1,0,'Workunits lost (without project/task)','',0,0,0,'0000-00-00');

ALTER TABLE ttask AUTO_INCREMENT = 1;

INSERT INTO tincident_resolution (id, name) VALUES 
(1,'Fixed'), 
(2,'Invalid'), 
(3,'Wont fix'), 
(4,'Duplicate'), 
(5,'Works for me'), 
(6,'Incomplete'), 
(7,'Expired'), 
(8,'Moved'), 
(9,'In process');

INSERT INTO tincident_status (id,name) VALUES 
(1,'New'), 
(2,'Unconfirmed'), 
(3,'Assigned'), 
(4,'Re-opened'), 
(5,'Pending to be closed'), 
(6,'Pending on a third person'), 
(7,'Closed');

INSERT INTO `trole` VALUES (1,'Project manager','',125),(2,'Systems engineer','',40),(3,'Junior consultant','',50),(4,'Junior programmer','',45),(5,'Senior programmer','',65),(6,'Analist','',75),(7,'Senior consultant','',75),(8,'Support engineer','',30);

INSERT INTO `tprofile` VALUES (1,'Administrator',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1),(2,'Regular&#x20;User',1,1,0,0,0,0,1,1,0,1,1,0,1,0,1,0,0,1,0,0,1,0,0,1,0,0,0,1,0,0),(3,'Manager',1,1,1,1,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);

INSERT INTO `tobject_type` VALUES (1,'Computer','','computer.png',2,0),
(2,'Pandora&#x20;agents','Imported&#x20;agents&#x20;from&#x20;Pandora&#x20;FMS','pandora.png',0,1),
(3,'CDROM','','cd-dvd.png',0,0),
(4,'NIC','','network.png',0,0),
(5,'Software','','box.png',0,0),
(6,'RAM','','memory-card.png',0,0),
(7,'Service','','engine.png',0,0),
(8,'CPU','','server.png',0,0),
(9,'HD','','harddisk.png',0,0),
(10,'Video','','system-monitor.png',0,0),
(11,'Patches','','attachment.png',0,0);

INSERT INTO `tobject_type_field` VALUES (1,1,'Serial&#x20;Number','numeric','','','',1,0,1),
(2,1,'CPU','text','','','',0,0,1),
(3,1,'Memory','text','','','',0,0,1),
(4,1,'IP&#x20;Address','text','','','',1,0,1),
(5,1,'MAC&#x20;Address','text','','','',1,0,1),
(6,1,'Users','text','','','',0,0,0),
(7,1,'File&#x20;system','text','','','',0,0,0),
(8,NULL,'OS','text',NULL,NULL,NULL,0,0,1),
(9,NULL,'IP Address','text',NULL,NULL,NULL,0,0,1),
(10,NULL,'URL Address','text',NULL,NULL,NULL,0,0,1),
(11,NULL,'ID Agent','text',NULL,NULL,NULL,0,0,1),
(12,2,'OS','text',NULL,NULL,NULL,0,0,1),
(13,2,'IP Address','text',NULL,NULL,NULL,0,0,1),
(14,2,'URL Address','text',NULL,NULL,NULL,0,0,1),
(15,2,'ID Agent','text',NULL,NULL,NULL,0,0,1),
(16,2,'Users','text',NULL,NULL,NULL,0,0,1),
(17,2,'File system','text',NULL,NULL,NULL,0,0,1),
(19,3,'Description','text','','','',0,0,0),
(20,3,'Mount&#x20;point','text','','','',0,0,0),
(22,4,'MAC','text','','','',1,0,0),
(23,4,'IP&#x20;Address','text','','','',0,0,0),
(26,5,'Version','text','','','',0,0,0),
(27,6,'Size','text','','','',0,0,0),
(28,6,'Type','text','','','',0,0,0),
(29,7,'Path','text','','','',0,0,0),
(30,7,'Status','text','','','',0,0,0),
(31,8,'Speed','text','','','',0,0,0),
(32,8,'Model','text','','','',0,0,0),
(33,9,'Capacity','text','','','',0,0,0),
(34,9,'Name','text','','','',0,0,0),
(35,10,'Memory','text','','','',0,0,0),
(36,10,'Type','text','','','',0,0,0),
(37,11,'Description','text','','','',0,0,0),
(38,11,'Extra','text','','','',0,0,0),
(39,11,'Internal&#x20;Code','text','','','',0,0,0);


INSERT INTO `tupdate_settings` VALUES ('customer_key', 'INTEGRIA-FREE'), ('updating_binary_path', 'Path where the updated binary files will be stored'), ('updating_code_path', 'Path where the updated code is stored'), ('dbname', ''), ('dbhost', ''), ('dbpass', ''), ('dbuser', ''), ('dbport', ''), ('proxy', ''), ('proxy_port', ''), ('proxy_user', ''), ('proxy_pass', '');

INSERT INTO tlead_progress (id,name) VALUES 
(0,'New'), 
(20,'Meeting arranged'), 
(40,'Needs discovered'), 
(60,'Proposal delivered'), 
(80,'Offer accepted'), 
(100,'Closed, not response or dead'),
(101,'Closed, lost'), 
(102,'Closed, invalid or N/A'), 
(200,'Closed successfully');

UPDATE tlead_progress SET `id`=0 WHERE `id`=1;
