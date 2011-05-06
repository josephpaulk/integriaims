-- Integria 1.1 - http://integria.sourceforge.net
-- ==================================================
-- Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
-- Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; version 2
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.


INSERT INTO `tconfig` (`token`, `value`) VALUES  
('language_code','en'),
('block_size','25'),
('db_scheme_version','3.0-dev'),
('db_scheme_build','ID110506'),
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
('fontsize', 6);

-- Default password is 'integria'

INSERT INTO `tusuario` (id_usuario, nombre_real, password, comentarios, fecha_registro, direccion, telefono, nivel, avatar, lang) VALUES ('admin','Default Admin','2f62afb6e17e46f0717225bcca6225b7','Default Integria Admin superuser. Please change password ASAP','2007-03-27 18:59:39','admin@integria.sf.net','555-555-555',1,'people_1','');


INSERT INTO `tgrupo` (id_grupo, nombre, icon, parent, id_user_default) VALUES (1,'All','world.png',0, 'admin');
INSERT INTO `tgrupo` (id_grupo, nombre, icon, parent, id_user_default) VALUES (2,'Customer #A','eye.png',0, 'admin');
INSERT INTO `tgrupo` (id_grupo, nombre, icon, parent, id_user_default) VALUES (3,'Customer #B','eye.png',0, 'admin');
INSERT INTO `tgrupo` (id_grupo, nombre, icon, parent, id_user_default) VALUES (4,'Engineering','computer.png',0, 'admin');

INSERT INTO `tlanguage` VALUES ('en','English');
INSERT INTO `tlanguage` VALUES ('es','Español');
INSERT INTO `tlanguage` VALUES ('bn', 'বাংলা');
INSERT INTO `tlanguage` VALUES ('da','Dansk');
INSERT INTO `tlanguage` VALUES ('el','Ελληνικά');
INSERT INTO `tlanguage` VALUES ('fr','Français');
INSERT INTO `tlanguage` VALUES ('ko','한국어');
INSERT INTO `tlanguage` VALUES ('nl','Nederlands');
INSERT INTO `tlanguage` VALUES ('ru','Русский');
INSERT INTO `tlanguage` VALUES ('tr','Türkçe');

INSERT INTO `tlink` VALUES  (0000000001,'Integria Project','http://integria.sourceforge.net'), (0000000002,'Artica ST','http://www.artica.es'),(0000000003,'Pandora FMS Project','http://pandora.sourceforge.net'),(0000000004,'Babel Project','http://babel.sourceforge.net'),(0000000005,'Google','http://www.google.com');

INSERT INTO `tproject` VALUES  (-1,'Non imputable hours (Special)','','0000-00-00','0000-00-00','',1,0);

ALTER TABLE tproject AUTO_INCREMENT = 1;

INSERT INTO `ttask` (`id`, `id_project`, `id_parent_task`, `name`, `description`, `completion`, `priority`, `dep_type`, `start`, `id_group`) VALUES (-1,-1,0,'Vacations','',0,0,0,'0000-00-00',0),(-2,-1,0,'Health issues','',0,0,0,'0000-00-00',0),(-3,-1,0,'Not justified','',0,0,0,'0000-00-00',0), (-4,-1,0,'Workunits lost (without project/task)','',0,0,0,'0000-00-00',0);

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
(6,'Resolved'), 
(7,'Closed');

INSERT INTO tincident_origin (name) VALUES ('User report'), ('Customer'), ('Internal department'), ('External department'), ('Application data'), ('Bug report'), ('Problem detected'), ('Other source' );

INSERT INTO `trole` VALUES (1,'Project manager','',125),(2,'Systems engineer','',40),(3,'Junior consultant','',50),(4,'Junior programmer','',45),(5,'Senior programmer','',65),(6,'Analist','',75),(7,'Senior consultant','',75),(8,'Support engineer','',30);

INSERT INTO `um_tupdate_settings` VALUES  ('current_update', '0'), ('customer_key', 'INTEGRIA-FREE'), ('keygen_path', '/srv/www/htdocs/integria/include/keygen'), ('update_server_host', 'www.artica.es'), ('update_server_port', '80'), ('update_server_path', '/integriaupdate/server.php'), ('updating_binary_path', 'Path where the updated binary files will be stored'), ('updating_code_path', ''), ('dbname', ''), ('dbhost', ''), ('dbpass', ''), ('dbuser', ''), ('proxy', ''), ('proxy_port', ''), ('proxy_user', ''), ('proxy_pass', '');

INSERT INTO `tprofile` VALUES (1,'Project Participant',1,1,0,0,0,0,1,1,0,1,0,0,0,0,1,0,0,1,0,0);
INSERT INTO `tprofile` VALUES (2,'Project Manager',    1,1,1,0,0,0,1,1,1,1,1,1,1,1,1,1,0,1,0,0);
INSERT INTO `tprofile` VALUES (3,'Incident Manager',   1,1,1,0,0,0,1,1,0,0,0,0,0,0,1,1,0,1,1,1);
INSERT INTO `tprofile` VALUES (4,'Incident Operator',  1,1,0,0,0,0,1,1,0,0,0,0,0,0,1,0,0,1,0,0);
INSERT INTO `tprofile` VALUES (5,'Global Manager',     1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);

INSERT INTO `tusuario_perfil` VALUES (2,'admin',5,1,'admin');

