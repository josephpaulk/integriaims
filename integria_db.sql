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

-- Table structure for table `tattachment`
--

CREATE TABLE `tattachment` (
  `id_attachment` bigint(20) unsigned NOT NULL auto_increment,
  `id_incidencia` bigint(20) NOT NULL default '0',
  `id_task` bigint(20) NOT NULL default '0',
  `id_kb` bigint(20) NOT NULL default '0',
  `id_usuario` varchar(60) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `description` varchar(150) default '',
  `size` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id_attachment`)
);

--
-- Table structure for table `tconfig`
--

CREATE TABLE `tconfig` (
  `id_config` int(10) unsigned NOT NULL auto_increment,
  `token` varchar(100) NOT NULL default '',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_config`)
);


CREATE TABLE `tgrupo` (
  `id_grupo` mediumint(8) unsigned NOT NULL auto_increment,
  `nombre` varchar(100) NOT NULL default '',
  `icon` varchar(50) default NULL,
  `parent` tinyint(4) NOT NULL default '-1',
  PRIMARY KEY  (`id_grupo`)
);

CREATE TABLE `tincident_type` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` mediumtext NULL default NULL,
  `id_wizard` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
);

--
-- Table structure for table `tincidencia`
--

CREATE TABLE `tincidencia` (
  `id_incidencia` bigint(20) unsigned NOT NULL auto_increment,
  `inicio` datetime NOT NULL default '0000-00-00 00:00:00',
  `cierre` datetime NOT NULL default '0000-00-00 00:00:00',
  `titulo` varchar(100) NOT NULL default '',
  `descripcion` mediumtext NOT NULL,
  `id_usuario` varchar(100) NOT NULL default '',
  `origen` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `estado` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `prioridad` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `id_grupo` mediumint(9) NOT NULL default '0',
  `actualizacion` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_creator` varchar(60) default NULL,
  `notify_email` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `id_task` int(10) NOT NULL default '0',
  `resolution` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `epilog` mediumtext NOT NULL,
  `id_incident_linked` bigint(20) unsigned NOT NULL default 0,
  `sla_disabled` tinyint UNSIGNED NOT NULL DEFAULT 0,
  `affected_sla_id` tinyint UNSIGNED NOT NULL DEFAULT 0,
  `id_incident_type` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_incidencia`),
  KEY `incident_index_1` (`id_usuario`,`id_incidencia`)
);

--
-- Table structure for table `tlanguage`
--

DROP TABLE IF EXISTS `tlanguage`;
CREATE TABLE `tlanguage` (
  `id_language` varchar(6) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_language`)
);

--
-- Table structure for table `tlink`
--

DROP TABLE IF EXISTS `tlink`;
CREATE TABLE `tlink` (
  `id_link` int(10) unsigned zerofill NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id_link`)
);


CREATE TABLE  `tprofile` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `ir` tinyint(1) NOT NULL default '0',
  `iw` tinyint(1) NOT NULL default '0',
  `im` tinyint(1) NOT NULL default '0',
  `um` tinyint(1) NOT NULL default '0',
  `dm` tinyint(1) NOT NULL default '0',
  `fm` tinyint(1) NOT NULL default '0',
  `ar` tinyint(1) NOT NULL default '0',
  `aw` tinyint(1) NOT NULL default '0',
  `am` tinyint(1) NOT NULL default '0',
  `pr` tinyint(1) NOT NULL default '0',
  `pw` tinyint(1) NOT NULL default '0',
  `pm` tinyint(1) NOT NULL default '0',
  `tw` tinyint(1) NOT NULL default '0',
  `tm` tinyint(1) NOT NULL default '0',
  `kr` tinyint(1) NOT NULL default '0',
  `kw` tinyint(1) NOT NULL default '0',
  `km` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);


CREATE TABLE `tsesion` (
  `ID_sesion` bigint(4) unsigned NOT NULL auto_increment,
  `ID_usuario` varchar(60) NOT NULL default '0',
  `IP_origen` varchar(100) NOT NULL default '',
  `accion` varchar(100) NOT NULL default '',
  `descripcion` varchar(200) NOT NULL default '',
  `fecha` datetime NOT NULL default '0000-00-00 00:00:00',
  `utimestamp` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_sesion`)
);


--
-- Table structure for table `tusuario`
--

CREATE TABLE `tusuario` (
  `id_usuario` varchar(60) NOT NULL default '0',
  `nombre_real` varchar(125) NOT NULL default '',
  `password` varchar(45) default NULL,
  `comentarios` varchar(200) default NULL,
  `fecha_registro` datetime NOT NULL default '0000-00-00 00:00:00',
  `direccion` varchar(100) default '',
  `telefono` varchar(100) default '',
  `nivel` tinyint(1) NOT NULL default '0',
  `avatar` varchar(100) default 'people_1',
   PRIMARY KEY  (`id_usuario`)
);

--
-- Table structure for table `tusuario_perfil`
--

CREATE TABLE `tusuario_perfil` (
  `id_up` bigint(20) unsigned NOT NULL auto_increment,
  `id_usuario` varchar(100) NOT NULL default '',
  `id_perfil` int(20) NOT NULL default '0',
  `id_grupo` int(11) NOT NULL default '0',
  `assigned_by` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_up`)
);

CREATE TABLE `tincident_track` (
  `id_it` int(10) unsigned NOT NULL auto_increment,
  `id_incident` int(10) unsigned NOT NULL default '0',
  `state` int(10) unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(250) NOT NULL default '',
  `id_aditional` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_it`)
);

-- New tables created 23/04/07 for project management

CREATE TABLE `tproject` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(240) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `start` date NOT NULL default '0000-00-00',
  `end` date NOT NULL default '0000-00-00',
  `id_owner` VARCHAR(125),
  `disabled` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);


CREATE TABLE `ttask_track` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_task` int(10) NOT NULL default '0',
  `id_user` varchar(240) NOT NULL default '',
  `id_external` int(10) unsigned NOT NULL default '0',
  `state` tinyint unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
);


CREATE TABLE `ttask` (
  `id` int(10) NOT NULL auto_increment,
  `id_project` int(10) NOT NULL default '0',
  `id_parent_task` int(10) unsigned NOT NULL default '0',
  `name` varchar(240) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `completion` tinyint unsigned NOT NULL default '0',
  `priority` tinyint unsigned NOT NULL default '0',
  `dep_type` tinyint unsigned NOT NULL DEFAULT 0,
  `start` date NOT NULL default '0000-00-00',
  `end` date NOT NULL default '0000-00-00',
  `hours` int unsigned NOT NULL DEFAULT 0,
  `estimated_cost` float (9,2) UNSIGNED NOT NULL DEFAULT 0.0,
  `id_group` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);


CREATE TABLE `tworkunit` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `duration` float (10,2) unsigned NOT NULL default '0',
  `id_user` VARCHAR(125) DEFAULT NULL,
  `description` mediumtext NOT NULL,
  `have_cost` tinyint unsigned NOT NULL DEFAULT 0,
  `id_profile` int(10) unsigned NOT NULL default '0',
  `locked` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tworkunit_task` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_task` int(10) NOT NULL default '0',
  `id_workunit` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);


CREATE TABLE `tworkunit_incident` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_incident` int(10) unsigned NOT NULL default '0',
  `id_workunit` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tagenda` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` datetime NOT NULL default '2000-01-01 00:00:00',
  `id_user` varchar(125) NOT NULL default '',
  `public` tinyint unsigned NOT NULL DEFAULT 0,
  `alarm` int(10) unsigned NOT NULL DEFAULT 0,
  `duration` int(10) unsigned NOT NULL DEFAULT 0,
  `id_group` int(10) NOT NULL default '0',
  `content` VARCHAR(255) DEFAULT '',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tincident_resolution` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tincident_status` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tincident_origin` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `trole` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(125) NOT NULL default '',
  `description` VARCHAR(255) DEFAULT '',
  `cost` int(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `trole_people_task` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_user` varchar(125) NOT NULL default '',
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_task` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `trole_people_project` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_user` varchar(125) NOT NULL default '',
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_project` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `ttodo` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(250) character set utf8 collate utf8_unicode_ci default NULL,
  `progress` int(11) NOT NULL,
  `assigned_user` varchar(250) character set utf8 collate utf8_unicode_ci NOT NULL,
  `created_by_user` varchar(250) character set utf8 collate utf8_unicode_ci NOT NULL,
  `priority` int(11) NOT NULL,
  `timestamp` datetime NOT NULL default '2000-01-01 00:00:00',
  `description` mediumtext,
  `last_update` datetime NOT NULL default '2000-01-01 00:00:00',
  `id_task` int(11) unsigned NULL default NULL,
  PRIMARY KEY  (`id`)
);


CREATE TABLE `tmilestone` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_project` int(10) unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
);

-- Table for special days, non working or corporate vacations --

CREATE TABLE `tvacationday` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `day` int(4) unsigned NOT NULL default '0',
  `month` int(4) unsigned NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

-- Table for bills, and externals expenses imputable to a task / project

CREATE TABLE `tcost` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `id_user` varchar(250) default NULL,
  `id_task` int(11) unsigned NULL default NULL,
  `bill_id` varchar(50) NOT NULL default '',
  `ammount` float(9,2) NOT NULL DEFAULT '0.0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `description` mediumtext NOT NULL,
  `id_attachment` int(11) unsigned NULL default NULL,
  `locked` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
);

-- 1.1 new table
-- Assign a manager for each group (to automatically
-- assign a user to an incident

CREATE TABLE `tgroup_manager` (
  `id_group` int(10) unsigned NOT NULL default '0',
  `id_user` varchar(250) NOT NULL default '',
  `forced_email` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `max_response_hr` int(10) unsigned NOT NULL default '0',
  `max_resolution_hr` int(10) unsigned NOT NULL default '0',
  `max_active` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_group`)
);

-- 1.1 new table
-- Used to track notificacion (emails) for agenda,
-- incident SLA notifications, system events and more
-- in the future.

CREATE TABLE `tevent` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `type` varchar(250) default NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(150) NOT NULL default '',
  `id_item` int(11) unsigned NULL default NULL,
  `id_item2` int(11) unsigned NULL default NULL,
  `id_item3` varchar(250) default NULL,
  PRIMARY KEY  (`id`)
);


CREATE TABLE `tkb_data` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `title` varchar(250) default NULL,
  `data` mediumtext NOT NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(150) NOT NULL default '',
  `id_product` mediumint(8) unsigned default 0,
  `id_category` mediumint(8) unsigned default 0,
  PRIMARY KEY  (`id`)
);

-- Product: OS, OS/Windows, OS/Windows/IE
-- Category: Module, Plugin, Article, Howto, Workaround, Download, Patch, etc

CREATE TABLE `tkb_category` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) default NULL,
  `icon` varchar(75) default NULL,
  `parent` tinyint(4) NOT NULL default '-1',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tkb_product` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) default NULL,
  `icon` varchar(75) default NULL,
  `parent` tinyint(4) NOT NULL default '-1',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tbuilding` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tincident_inventory` (
  `id_incident` bigint(20) unsigned NOT NULL auto_increment,
  `id_inventory` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_incident`, `id_inventory`),
  FOREIGN KEY (`id_incident`) REFERENCES tincidencia(`id_incidencia`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_inventory`) REFERENCES tinventory(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tcompany_role` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tcompany` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  `fiscal_information` varchar(250) NULL default NULL,
  `comments` varchar(1024) NULL default NULL,
  `id_company_role` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_company_role`) REFERENCES tcompany_role(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tcompany_contact` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `id_company` mediumint(8) unsigned NOT NULL,
  `fullname` varchar(150) NOT NULL default '',
  `email` varchar(100) NULL default NULL,
  `phone` varchar(20) NULL default NULL,
  `mobile_phone` varchar(20) NULL default NULL,
  `position` varchar(100) NULL default NULL,
  `disabled` tinyint NULL default 0,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_company`) REFERENCES tcompany(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tcontract` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_company` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_company`) REFERENCES tcompany(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tsla_base` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  `min_response` int(11) NULL default NULL,
  `max_response` int(11) NULL default NULL,
  `max_incidents` int(11) NULL default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tsla_specific` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  `min_response` int(11) NULL default NULL,
  `max_response` int(11) NULL default NULL,
  `max_incidents` int(11) NULL default NULL,
  `enforced` tinyint NULL default 0,
  `id_sla_base` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tmanufacturer` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `address` varchar(250) NULL default NULL,
  `fiscal_information` varchar(250) NULL default NULL,
  `id_company` mediumint(8) unsigned NOT NULL,
  `comments` varchar(250) NULL default NULL,
  `id_company_role` mediumint(8) unsigned NOT NULL,
  `id_sla` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_company_role`) REFERENCES tcompany_role(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tproduct` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  `icon` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tinventory` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  `serial_number` varchar(250) NULL default NULL,
  `part_number` varchar(250) NULL default NULL,
  `comments` varchar(250) NULL default NULL,
  `confirmed` tinyint(4) NULL default '0',
  `ip_address` varchar(60) NULL default NULL,
  `id_contract` mediumint(8) unsigned default NULL,
  `id_product` mediumint(8) unsigned default NULL,
  `id_sla` mediumint(8) unsigned default NULL,
  `id_manufacturer` mediumint(8) unsigned default NULL,
  `id_building` mediumint(8) unsigned default NULL,
  `id_parent` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_contract`) REFERENCES tcontract(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_product`) REFERENCES tproduct(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_sla`) REFERENCES tsla_specific(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_manufacturer`) REFERENCES tmanufacturer(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_building`) REFERENCES tbuilding(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE `tinventory` ADD
 FOREIGN KEY (`id_parent`) REFERENCES tinventory(`id`)
   ON UPDATE CASCADE ON DELETE SET NULL;

CREATE TABLE `tcontact` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `fullname` varchar(255) NOT NULL default '',
  `phone` varchar(55) NOT NULL default '',
  `mobile` varchar(55) NOT NULL default '',
  `email` varchar(200) NOT NULL default '',
  `position` varchar(200) NOT NULL default '',
  `id_company` mediumint(9) NOT NULL default '0',
  `disabled` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);
