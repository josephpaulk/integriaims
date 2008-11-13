-- INTEGRIA - the ITIL Management System
-- http://integria.sourceforge.net
-- ==================================================
-- Copyright (c) 2008 Ártica Soluciones Tecnológicas
-- http://www.artica.es  <info@artica.es>

-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; version 2
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.

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
  `lang` varchar(10) default '',
   PRIMARY KEY  (`id_usuario`)
);

CREATE TABLE `tgrupo` (
  `id_grupo` mediumint(8) unsigned NOT NULL auto_increment,
  `nombre` varchar(100) NOT NULL default '',
  `icon` varchar(50) default NULL,
  `banner` varchar(150) default NULL,
  `url` varchar(150) default NULL,
  `lang` varchar(10) default NULL,
  `parent` mediumint(8) unsigned NOT NULL default 0,
  `id_user_default` varchar(60) NOT NULL default '',
  `forced_email` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY  (`id_grupo`),
  FOREIGN KEY (`id_user_default`) REFERENCES tusuario(`id_usuario`)
     ON UPDATE CASCADE ON DELETE SET default
);

ALTER TABLE tgrupo ADD FOREIGN KEY (`parent`) REFERENCES tgrupo(`id_grupo`)
     ON UPDATE CASCADE ON DELETE SET default;

CREATE TABLE `tproject_group` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `icon` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
);

-- New tables created 23/04/07 for project management

CREATE TABLE `tproject` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(240) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `start` date NOT NULL default '0000-00-00',
  `end` date NOT NULL default '0000-00-00',
  `id_owner` varchar(60)  NOT NULL default '',
  `disabled` int(2) unsigned NOT NULL default '0',
  `id_project_group` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_owner`) REFERENCES tusuario(`id_usuario`)
     ON UPDATE CASCADE ON DELETE SET default,
  FOREIGN KEY (`id_project_group`) REFERENCES tproject_group(`id`)
     ON UPDATE CASCADE ON DELETE SET default
);

CREATE TABLE `ttask` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_project` int(10) unsigned NOT NULL default 0,
  `id_parent_task` int(10) unsigned NOT NULL default '0',
  `name` varchar(240) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `completion` tinyint unsigned NOT NULL default '0',
  `priority` tinyint unsigned NOT NULL default '0',
  `dep_type` tinyint unsigned NOT NULL DEFAULT 0,
  `start` date NOT NULL default '0000-00-00',
  `end` date NOT NULL default '0000-00-00',
  `hours` int unsigned NOT NULL DEFAULT 0,
  `estimated_cost` float (9,2) unsigned NOT NULL DEFAULT 0.0,
  `id_group` int(10) NOT NULL default '0',
  `periodicity` enum ('none', 'weekly', 'monthly', 'year', '15days', '21days', '10days', '15days', '60days', '90days', '120days', '180days') default 'none',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_project`) REFERENCES tproject(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_group`) REFERENCES tgrupo(`id_grupo`)
     ON UPDATE CASCADE ON DELETE SET default
);

ALTER TABLE ttask ADD FOREIGN KEY (`id_parent_task`) REFERENCES ttask(`id`)
     ON UPDATE CASCADE ON DELETE SET default;

-- Table structure for table `tattachment`
--
CREATE TABLE `tattachment` (
  `id_attachment` bigint(20) unsigned NOT NULL auto_increment,
  `id_incidencia` bigint(20) NOT NULL default '0',
  `id_task` int(10) unsigned NOT NULL default 0,
  `id_kb` bigint(20) NOT NULL default '0',
  `id_usuario` varchar(60) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `description` varchar(150) default '',
  `size` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id_attachment`),
  FOREIGN KEY (`id_usuario`) REFERENCES tusuario(`id_usuario`)
     ON UPDATE CASCADE ON DELETE SET default,
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
     ON UPDATE CASCADE ON DELETE SET default
);

--
-- Table structure for table `tconfig`
--

CREATE TABLE `tconfig` (
  `id_config` int(10) unsigned NOT NULL auto_increment,
  `token` varchar(100) NOT NULL default '',
  `value` text NOT NULL default '',
  PRIMARY KEY  (`id_config`)
);

CREATE TABLE `twizard` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tincident_type` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  `id_wizard` mediumint(8) unsigned NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_wizard`) REFERENCES twizard(`id`)
      ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE `tsla` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  `min_response` int(11) NULL default NULL,
  `max_response` int(11) NULL default NULL,
  `max_incidents` int(11) NULL default NULL,
  `enforced` tinyint NULL default 0,
  `id_sla_base` mediumint(8) unsigned NULL default 0,
  PRIMARY KEY  (`id`)
);

ALTER TABLE `tsla` ADD FOREIGN KEY (`id_sla_base`) REFERENCES tsla(`id`)
  ON UPDATE CASCADE ON DELETE SET default;

--
-- Table structure for table `tincidencia`
--

CREATE TABLE `tincidencia` (
  `id_incidencia` bigint(20) unsigned NOT NULL auto_increment,
  `inicio` datetime NOT NULL default '0000-00-00 00:00:00',
  `cierre` datetime NOT NULL default '0000-00-00 00:00:00',
  `titulo` varchar(100) NOT NULL default '',
  `descripcion` mediumtext NOT NULL,
  `id_usuario` varchar(60) NOT NULL default '',
  `origen` tinyint unsigned NOT NULL DEFAULT 0,
  `estado` tinyint unsigned NOT NULL DEFAULT 0,
  `prioridad` tinyint unsigned NOT NULL DEFAULT 0,
  `id_grupo` mediumint(9) NOT NULL default 0,
  `actualizacion` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_creator` varchar(60) default NULL,
  `notify_email` tinyint unsigned NOT NULL DEFAULT 0,
  `id_task` int(10) unsigned NOT NULL default '0',
  `resolution` tinyint unsigned NOT NULL DEFAULT 0,
  `epilog` mediumtext NOT NULL,
  `id_parent` bigint(20) unsigned NULL default 0,
  `sla_disabled` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `affected_sla_id` tinyint unsigned NOT NULL DEFAULT 0,
  `id_incident_type` mediumint(8) unsigned NULL,
  PRIMARY KEY  (`id_incidencia`),
  KEY `incident_index_1` (`id_usuario`,`id_incidencia`),
  FOREIGN KEY (`id_incident_type`) REFERENCES tincident_type(`id`)
      ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (`id_grupo`) REFERENCES tgrupo(`id_grupo`)
      ON UPDATE CASCADE ON DELETE SET default,
  FOREIGN KEY (`id_creator`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
      ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (`affected_sla_id`) REFERENCES tsla(`id`)
      ON UPDATE CASCADE ON DELETE RESTRICT
);

ALTER TABLE `tincidencia` ADD FOREIGN KEY (`id_parent`) REFERENCES tincidencia(`id_incidencia`)
  ON UPDATE CASCADE ON DELETE SET default;

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

CREATE TABLE `tsesion` (
  `ID_sesion` bigint(4) unsigned NOT NULL auto_increment,
  `ID_usuario` varchar(60) NOT NULL default '0',
  `IP_origen` varchar(100) NOT NULL default '',
  `accion` varchar(100) NOT NULL default '',
  `descripcion` varchar(200) NOT NULL default '',
  `fecha` datetime NOT NULL default '0000-00-00 00:00:00',
  `utimestamp` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_sesion`),
  FOREIGN KEY (`ID_usuario`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tincident_track` (
  `id_it` int(10) unsigned NOT NULL auto_increment,
  `id_incident` bigint(20) unsigned NOT NULL default '0',
  `state` int(10) unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(60) NOT NULL default '',
  `id_aditional` int(10) unsigned NOT NULL default '0',
  `description` text NOT NULL default '',
  PRIMARY KEY  (`id_it`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_incident`) REFERENCES tincidencia(`id_incidencia`)
      ON UPDATE CASCADE ON DELETE CASCADE
);


CREATE TABLE `ttask_track` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_task` int(10) unsigned NOT NULL default '0',
  `id_user` varchar(60) NOT NULL default '',
  `id_external` int(10) unsigned NOT NULL default '0',
  `state` tinyint unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tproject_track` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_project` int(10) unsigned NOT NULL default '0',
  `id_user` varchar(60) NOT NULL default '',
  `state` tinyint unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_aditional`  int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE SET default,
  FOREIGN KEY (`id_project`) REFERENCES tproject(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tworkunit` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `duration` float (10,2) unsigned NOT NULL default '0',
  `id_user` varchar(125) DEFAULT NULL,
  `description` mediumtext NOT NULL,
  `have_cost` tinyint unsigned NOT NULL DEFAULT 0,
  `id_profile` int(10) unsigned NOT NULL default '0',
  `locked` varchar(125) DEFAULT '',
  `public` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tworkunit_task` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_task` int(10) unsigned NOT NULL default '0',
  `id_workunit` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
      ON UPDATE CASCADE ON DELETE SET default,
  FOREIGN KEY (`id_workunit`) REFERENCES tworkunit(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tworkunit_incident` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_incident` int(10) unsigned NOT NULL default '0',
  `id_workunit` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_workunit`) REFERENCES tworkunit(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tagenda` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` datetime NOT NULL default '2000-01-01 00:00:00',
  `id_user` varchar(60) NOT NULL default '',
  `public` tinyint unsigned NOT NULL DEFAULT 0,
  `alarm` int(10) unsigned NOT NULL DEFAULT 0,
  `duration` int(10) unsigned NOT NULL DEFAULT 0,
  `id_group` int(10) NOT NULL default '0',
  `content` varchar(255) DEFAULT '',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_group`) REFERENCES tgrupo(`id_grupo`)
      ON UPDATE CASCADE ON DELETE CASCADE
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
  `description` varchar(255) DEFAULT '',
  `cost` int(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `trole_people_task` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_user` varchar(60) NOT NULL default '',
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_task` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_role`) REFERENCES trole(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `trole_people_project` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_user` varchar(60) NOT NULL default '',
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_project` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_role`) REFERENCES trole(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_project`) REFERENCES tproject(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE
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
  `id_task` int(10) unsigned NULL default NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`assigned_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`created_by_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id_task`)
      ON UPDATE CASCADE ON DELETE CASCADE
);


CREATE TABLE `tmilestone` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_project` int(10) unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_project`) REFERENCES tproject(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE
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
  `id_user` varchar(60) default NULL,
  `id_wu` int(10) unsigned NULL default NULL,
  `bill_id` varchar(50) NOT NULL default '',
  `ammount` float(9,2) NOT NULL DEFAULT '0.0',
  `description` mediumtext NOT NULL,
  `id_attachment` bigint(20) unsigned NULL default NULL,
  `locked` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_id_user` varchar(60) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_wu`) REFERENCES tworkunit(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`locked_id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

-- Used to track notificacion (emails) for agenda,
-- incident SLA notifications, system events and more
-- in the future.

CREATE TABLE `tevent` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `type` varchar(250) default NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(60) NOT NULL default '',
  `id_item` int(11) unsigned NULL default NULL,
  `id_item2` int(11) unsigned NULL default NULL,
  `id_item3` varchar(250) default NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

-- Product: OS, OS/Windows, OS/Windows/IE
-- Category: Module, Plugin, Article, Howto, Workaround, Download, Patch, etc

CREATE TABLE `tkb_category` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text default NULL,
  `icon` varchar(75) default NULL,
  `parent` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

ALTER TABLE `tkb_category` ADD FOREIGN KEY (`parent`) REFERENCES tkb_category(`id`)
     ON UPDATE CASCADE ON DELETE RESTRICT;

CREATE TABLE `tkb_product` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text default NULL,
  `icon` varchar(75) default NULL,
  `parent` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

ALTER TABLE `tkb_product` ADD FOREIGN KEY (`parent`) REFERENCES tkb_product(`id`)
     ON UPDATE CASCADE ON DELETE SET default;

CREATE TABLE `tkb_data` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `title` varchar(250) default NULL,
  `data` mediumtext NOT NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(150) NOT NULL default '',
  `id_product` mediumint(8) unsigned default 0,
  `id_category` mediumint(8) unsigned default 0,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_product`) REFERENCES tkb_product(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_category`) REFERENCES tkb_category(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tbuilding` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tcompany_role` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `tcompany` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `address` varchar(300) NOT NULL default '', 
  `fiscal_id` varchar(250) NULL default NULL,
  `comments` text NULL default NULL,
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
  `phone` varchar(55) NULL default NULL,
  `mobile` varchar(55) NULL default NULL,
  `position` varchar(150) NULL default NULL,
  `description` text NULL DEFAULT NULL,
  `disabled` tinyint(1) NULL default 0,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_company`) REFERENCES tcompany(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tcontract` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `contract_number` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  `date_begin` date NOT NULL default '0000-00-00',
  `date_end` date NOT NULL default '0000-00-00',
  `id_company` mediumint(8) unsigned NULL default NULL,
  `id_sla` mediumint(8) unsigned NULL default NULL,
  `id_group` mediumint(8) unsigned NULL default NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_sla`) REFERENCES tsla(`id`)
     ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`id_group`) REFERENCES tgrupo(`id_grupo`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_company`) REFERENCES tcompany(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tmanufacturer` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `address` varchar(250) NULL default NULL,
  `comments` varchar(250) NULL default NULL,
  `id_company_role` mediumint(8) unsigned NOT NULL,
  `id_sla` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_company_role`) REFERENCES tcompany_role(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_sla`) REFERENCES tsla(`id`)
     ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE `tinventory` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NULL default NULL,
  `serial_number` varchar(250) NULL default NULL,
  `part_number` varchar(250) NULL default NULL,
  `comments` varchar(250) NULL default NULL,
  `confirmed` tinyint(1) NULL default 0,
  `cost` float(10,3) NULL default 0.0,
  `ip_address` varchar(60) NULL default NULL,
  `id_contract` mediumint(8) unsigned default NULL,
  `id_product` mediumint(8) unsigned default NULL,
  `id_sla` mediumint(8) unsigned default NULL,
  `id_manufacturer` mediumint(8) unsigned default NULL,
  `id_building` mediumint(8) unsigned default NULL,
  `id_parent` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_contract`) REFERENCES tcontract(`id`)
     ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (`id_product`) REFERENCES tkb_product(`id`)
     ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (`id_sla`) REFERENCES tsla (`id`)
     ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (`id_manufacturer`) REFERENCES tmanufacturer(`id`)
     ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (`id_building`) REFERENCES tbuilding(`id`)
     ON UPDATE CASCADE ON DELETE SET NULL
);

ALTER TABLE `tinventory` ADD
 FOREIGN KEY (`id_parent`) REFERENCES tinventory(`id`)
   ON UPDATE CASCADE ON DELETE SET NULL;

CREATE TABLE `tinventory_contact` (
  `id_inventory` mediumint(8) unsigned NOT NULL,
  `id_company_contact` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_inventory`, `id_company_contact`),
  FOREIGN KEY (`id_company_contact`) REFERENCES tcompany_contact(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_inventory`) REFERENCES tinventory(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tincident_inventory` (
  `id_incident` bigint(20) unsigned NOT NULL,
  `id_inventory` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_incident`, `id_inventory`),
  FOREIGN KEY (`id_incident`) REFERENCES tincidencia(`id_incidencia`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_inventory`) REFERENCES tinventory(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `ttask_inventory` (
  `id_task` int(10) unsigned unsigned NOT NULL,
  `id_inventory` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_task`, `id_inventory`),
  FOREIGN KEY (`id_inventory`) REFERENCES tinventory(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `tcustom_search` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL,
  `section` varchar(20) NOT NULL,
  `id_user` varchar(60) NOT NULL,
  `form_values` text NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE (`id_user`, `name`, `section`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE `um_tupdate_settings` (
  `key` varchar(255) default '',
  `value` varchar(255) default '',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `um_tupdate_package` (
  id int(11) unsigned NOT NULL auto_increment,
  timestamp datetime NOT NULL,  description mediumtext NOT NULL default '', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `um_tupdate` (
  id int(11) unsigned NOT NULL auto_increment,
  type enum('code', 'db_data', 'db_schema', 'binary'),
  id_update_package int(11) unsigned NOT NULL default 0, 
  filename  varchar(250) default '', 
  checksum  varchar(250) default '',
  previous_checksum  varchar(250) default '',
  svn_version int(4) unsigned NOT NULL default 0,
  data LONGTEXT default '',
  data_rollback LONGTEXT default '',
  description TEXT default '',
  db_table_name varchar(140) default '',
  db_field_name varchar(140) default '',
  db_field_value varchar(1024) default '',
  PRIMARY KEY  (`id`), 
  FOREIGN KEY (`id_update_package`) REFERENCES um_tupdate_package(`id`)
  ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `um_tupdate_journal` (
  id int(11) unsigned NOT NULL auto_increment,
  id_update int(11) unsigned NOT NULL default 0,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_update`) REFERENCES um_tupdate(`id`)
  ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

