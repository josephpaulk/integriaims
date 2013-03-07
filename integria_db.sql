-- INTEGRIA - the ITIL Management System
-- http://integria.sourceforge.net
-- ==================================================
-- Copyright (c) 2007-2012 Ártica Soluciones Tecnológicas
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
  `id_usuario` varchar(60) NOT NULL default '',
  `nombre_real` varchar(125) NOT NULL default '',
  `password` varchar(45) default NULL,
  `comentarios` varchar(200) default NULL,
  `fecha_registro` datetime NOT NULL default '0000-00-00 00:00:00',
  `direccion` varchar(100) default '',
  `telefono` varchar(100) default '',
  `nivel` tinyint(1) NOT NULL default '0',
  `avatar` varchar(100) default 'people_1',
  `lang` varchar(10) default '',
  `pwdhash` varchar(100) default '',
  `disabled` int default 0,
  `id_company` int(10) unsigned NULL default 0,
  `simple_mode` tinyint(2) unsigned NOT NULL DEFAULT 0,
  `force_change_pass` tinyint(1) unsigned NOT NULL default 0,
  `last_pass_change` DATETIME  NOT NULL DEFAULT 0,
  `last_failed_login` DATETIME  NOT NULL DEFAULT 0,
  `failed_attempt` int(4) NOT NULL DEFAULT 0,
  `login_blocked` tinyint(1) unsigned NOT NULL DEFAULT 0,
   PRIMARY KEY  (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tgrupo` (
  `id_grupo` mediumint(8) unsigned NOT NULL auto_increment,
  `nombre` varchar(100) NOT NULL default '',
  `icon` varchar(50) default NULL,
  `banner` varchar(150) default NULL,
  `url` varchar(150) default NULL,
  `lang` varchar(10) default NULL,
  `parent` mediumint(8) unsigned NOT NULL default 0,
  `id_user_default` varchar(60) NOT NULL default '',
  `id_inventory_default` mediumint(8) default NULL,
  `soft_limit` int(5) unsigned NOT NULL default 0,
  `hard_limit` int(5) unsigned NOT NULL default 0,
  `forced_email` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `email` varchar(128) default '',
  `enforce_soft_limit` int(2) unsigned NOT NULL default 0,
  PRIMARY KEY  (`id_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `tproject_group` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `icon` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tproject` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(240) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `start` date NOT NULL default '0000-00-00',
  `end` date NOT NULL default '0000-00-00',
  `id_owner` varchar(60)  NOT NULL default '',
  `disabled` int(2) unsigned NOT NULL default '0',
  `id_project_group` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `iproject_idx_1` (`id_project_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ttask` (
  `id` int(10) NOT NULL auto_increment,
  `id_project` int(10) NOT NULL default 0,
  `id_parent_task` int(10) NULL default '0',
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
  `periodicity` enum ('none', 'weekly', 'monthly', 'year', '21days', '10days', '15days', '60days', '90days', '120days', '180days') default 'none',
  `count_hours` TINYINT (1) DEFAULT '1',
  PRIMARY KEY  (`id`),
  KEY `itask_idx_1` (`id_project`),
  FOREIGN KEY (`id_project`) REFERENCES tproject(`id`)
     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table structure for table `tattachment`
--

CREATE TABLE `tattachment` (
  `id_attachment` bigint(20) unsigned NOT NULL auto_increment,
  `id_incidencia` bigint(20) NOT NULL default '0',
  `id_task` int(10) NULL default 0,
  `id_kb` bigint(20) NOT NULL default '0',
  `id_usuario` varchar(60) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `description` varchar(150) default '',
  `size` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id_attachment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `tconfig`
--

CREATE TABLE `tconfig` (
  `id_config` int(10) unsigned NOT NULL auto_increment,
  `token` varchar(100) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id_config`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `twizard` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincident_type` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  `id_wizard` mediumint(8) unsigned NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tsla` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  `min_response` int(11) NULL default NULL,
  `max_response` int(11) NULL default NULL,
  `max_incidents` int(11) NULL default NULL,
  `max_inactivity` int(11) unsigned NULL default 96,
  `enforced` tinyint NULL default 0,
  `five_daysonly` tinyint NULL default 0,
  `time_from` tinyint NULL default 0,
  `time_to` tinyint NULL default 0,
  `id_sla_base` mediumint(8) unsigned NULL default 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `tincidencia`
--

CREATE TABLE `tincidencia` (
  `id_incidencia` bigint(20) unsigned NOT NULL auto_increment,
  `inicio` datetime NOT NULL default '0000-00-00 00:00:00',
  `cierre` datetime NOT NULL default '0000-00-00 00:00:00',
  `titulo` mediumtext DEFAULT NULL,
  `descripcion` mediumtext DEFAULT NULL,
  `id_usuario` varchar(60) NOT NULL default '',
  `origen` tinyint unsigned NOT NULL DEFAULT 0,
  `estado` tinyint unsigned NOT NULL DEFAULT 0,
  `prioridad` tinyint unsigned NOT NULL DEFAULT 0,
  `id_grupo` mediumint(9) NOT NULL default 0,
  `actualizacion` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_creator` varchar(60) default NULL,
  `notify_email` tinyint unsigned NOT NULL DEFAULT 0,
  `id_task` int(10) NOT NULL default '0',
  `resolution` tinyint unsigned NOT NULL DEFAULT 0,
  `epilog` mediumtext NOT NULL,
  `id_parent` bigint(20) unsigned NULL default NULL,
  `sla_disabled` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `affected_sla_id` tinyint unsigned NOT NULL DEFAULT 0,
  `id_incident_type` mediumint(8) unsigned NULL,
  `score` mediumint(8) default 0,
  `email_copy` mediumtext not NULL,
  `editor` varchar(60) NOT NULL default '',
  `id_group_creator` mediumint(9) NOT NULL default 0,

  PRIMARY KEY  (`id_incidencia`),
  KEY `incident_idx_1` (`id_usuario`),
  KEY `incident_idx_2` (`estado`),
  KEY `incident_idx_3` (`id_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `tincidencia` ADD FOREIGN KEY (`id_parent`) REFERENCES tincidencia(`id_incidencia`)
   ON DELETE CASCADE;

--
-- Table structure for table `tlanguage`
--

DROP TABLE IF EXISTS `tlanguage`;
CREATE TABLE `tlanguage` (
  `id_language` varchar(6) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `tlink`
--

DROP TABLE IF EXISTS `tlink`;
CREATE TABLE `tlink` (
  `id_link` int(10) unsigned zerofill NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id_link`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tsesion` (
  `ID_sesion` bigint(4) unsigned NOT NULL auto_increment,
  `ID_usuario` varchar(60) NOT NULL default '0',
  `IP_origen` varchar(100) NOT NULL default '',
  `accion` varchar(100) NOT NULL default '',
  `descripcion` varchar(200) NOT NULL default '',
  `extra_info` TEXT default NULL,
  `fecha` datetime NOT NULL default '0000-00-00 00:00:00',
  `utimestamp` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_sesion`),
  KEY `tsession_idx_1` (`ID_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincident_track` (
  `id_it` int(10) unsigned NOT NULL auto_increment,
  `id_incident` bigint(20) unsigned NOT NULL default '0',
  `state` int(10) unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(60) NOT NULL default '',
  `id_aditional` int(10) unsigned NOT NULL default '0',
  `description` text NOT NULL,
  PRIMARY KEY  (`id_it`),
  KEY `tit_idx_1` (`id_incident`),
  FOREIGN KEY (`id_incident`) REFERENCES tincidencia(`id_incidencia`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ttask_track` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_task` int(10) NOT NULL default '0',
  `id_user` varchar(60) NOT NULL default '',
  `id_external` int(10) unsigned NOT NULL default '0',
  `state` tinyint unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `ttt_idx_1` (`id_task`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
       ON DELETE CASCADE, 
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tproject_track` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_project` int(10) NOT NULL default '0',
  `id_user` varchar(60) NOT NULL default '',
  `state` tinyint unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_aditional`  int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `tpt_idx_1` (`id_project`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
       ON DELETE CASCADE,
  FOREIGN KEY (`id_project`) REFERENCES tproject(`id`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `tw_idx_1` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tworkunit_task` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_task` int(10) NOT NULL default '0',
  `id_workunit` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `twt_idx_1` (`id_task`),
  FOREIGN KEY (`id_workunit`) REFERENCES tworkunit(`id`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tworkunit_incident` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_incident` int(10) unsigned NOT NULL default '0',
  `id_workunit` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `twi_idx_1` (`id_incident`),
  KEY `twi_idx_2` (`id_workunit`),
  FOREIGN KEY (`id_workunit`) REFERENCES tworkunit(`id`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `ta_idx_1` (`id_user`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincident_resolution` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincident_status` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincident_origin` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `trole` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(125) NOT NULL default '',
  `description` varchar(255) DEFAULT '',
  `cost` int(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `trole_people_task` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_user` varchar(60) NOT NULL default '',
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_task` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
       ON DELETE CASCADE,
  FOREIGN KEY (`id_role`) REFERENCES trole(`id`)
       ON DELETE CASCADE,
  FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `trole_people_project` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_user` varchar(60) NOT NULL default '',
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_project` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `trp_idx_1` (`id_user`),
  KEY `trp_idx_2` (`id_project`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
       ON DELETE CASCADE,
  FOREIGN KEY (`id_role`) REFERENCES trole(`id`)
       ON DELETE CASCADE,
  FOREIGN KEY (`id_project`) REFERENCES tproject(`id`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ttodo` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(250) default NULL,
  `progress` int(11) NOT NULL,
  `assigned_user` varchar(60)  NOT NULL default '',
  `created_by_user` varchar(60)  NOT NULL default '',
  `priority` int(11) NOT NULL,
  `timestamp` datetime NOT NULL default '2000-01-01 00:00:00',
  `description` mediumtext,
  `last_update` datetime NOT NULL default '2000-01-01 00:00:00',
  `id_task` int(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `tt_idx_1` (`assigned_user`),
  KEY `tt_idx_2` (`created_by_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `tmilestone` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_project` int(10)  NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tm_idx_1` (`id_project`),
  FOREIGN KEY (`id_project`) REFERENCES tproject(`id`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for special days, non working or corporate vacations --

CREATE TABLE `tvacationday` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `day` int(4) unsigned NOT NULL default '0',
  `month` int(4) unsigned NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tinvoice` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `id_user` varchar(60) default NULL,
  `id_task` int(10) unsigned NULL default NULL,
  `id_company` int(10) unsigned NULL default NULL,  
  `bill_id` varchar(50) NOT NULL default '',
  `ammount` float(11,2) NOT NULL DEFAULT '0.0',
  `description` mediumtext NOT NULL,
  `id_attachment` bigint(20) unsigned NULL default NULL,
  `locked` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_id_user` varchar(60) DEFAULT NULL,
  `invoice_create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `invoice_payment_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `tcost_idx_1` (`id_user`),
  KEY `tcost_idx_2` (`id_company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `tevent_idx_1` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Product: OS, OS/Windows, OS/Windows/IE
-- Category: Module, Plugin, Article, Howto, Workaround, Download, Patch, etc

CREATE TABLE `tkb_category` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text default NULL,
  `icon` varchar(75) default NULL,
  `parent` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tkb_product` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text default NULL,
  `icon` varchar(75) default NULL,
  `parent` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tkb_data` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `title` varchar(250) default NULL,
  `data` mediumtext NOT NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_language` varchar(6) NOT NULL default '',
  `id_user` varchar(150) NOT NULL default '',
  `id_product` mediumint(8) unsigned default 0,
  `id_category` mediumint(8) unsigned default 0,
  PRIMARY KEY  (`id`),
  KEY `tkb_idx_1` (`id_product`),
  KEY `tkb_idx_2` (`id_category`),
  KEY `tkb_idx_3` (`id_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tbuilding` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tcompany_role` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NULL default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tcompany` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `address` varchar(300) NOT NULL default '', 
  `fiscal_id` varchar(250) NULL default NULL,
  `comments` text NULL default NULL,
  `id_company_role` mediumint(8) unsigned NOT NULL,
  `id_grupo` mediumint(8) unsigned DEFAULT 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincident_contact_reporters` (
  `id_incident` bigint(20) unsigned NOT NULL,
  `id_contact` mediumint(8) unsigned NOT NULL,
  UNIQUE (`id_incident`, `id_contact`),
  FOREIGN KEY (`id_incident`) REFERENCES tincidencia(`id_incidencia`)
      ON DELETE CASCADE,
  FOREIGN KEY (`id_contact`) REFERENCES tcompany_contact(`id`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `private` tinyint(2) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_company`) REFERENCES tcompany(`id`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tmanufacturer` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `address` varchar(250) NULL default NULL,
  `comments` varchar(250) NULL default NULL,
  `id_company_role` mediumint(8) unsigned NOT NULL,
  `id_sla` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tinventory` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` mediumtext NULL default NULL,
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
  `generic_1` varchar(255) default '',
  `generic_2` varchar(255) default '',
  `generic_3` text,
  `generic_4` text,
  `generic_5` varchar(255) default '',
  `generic_6` varchar(255) default '',
  `generic_7` varchar(255) default '',
  `generic_8` text,
  PRIMARY KEY  (`id`),
  KEY `tinv_idx_1` (`id_contract`),
  KEY `tinv_idx_2` (`id_sla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tinventory_contact` (
  `id_inventory` mediumint(8) unsigned NOT NULL,
  `id_company_contact` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_inventory`, `id_company_contact`),
  FOREIGN KEY (`id_company_contact`) REFERENCES tcompany_contact(`id`)
      ON DELETE CASCADE,
  FOREIGN KEY (`id_inventory`) REFERENCES tinventory(`id`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tinventory_reports` (
  `id` mediumint unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `sql` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincident_inventory` (
  `id_incident` bigint(20) unsigned NOT NULL,
  `id_inventory` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_incident`, `id_inventory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ttask_inventory` (
  `id_task` int(10) NOT NULL,
  `id_inventory` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_task`, `id_inventory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tcustom_search` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL,
  `section` varchar(20) NOT NULL,
  `id_user` varchar(60) NOT NULL,
  `form_values` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE (`id_user`, `name`, `section`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tdownload` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `location` text NOT NULL, 
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `description` text NOT NULL, 
  `tag` text NOT NULL,
  `id_category` mediumint(8) unsigned NOT NULL default 0,
  `id_user` varchar(60) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tdownload_category` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `icon` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tdownload_category_group` (
  `id_category` mediumint(8) unsigned NOT NULL,
  `id_group` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (id_category, id_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `tdownload_tracking` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `id_download` mediumint(8) unsigned NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `tnewsboard` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `content` text NOT NULL, 
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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
  `vr` tinyint(1) NOT NULL default '0',
  `vw` tinyint(1) NOT NULL default '0',
  `vm` tinyint(1) NOT NULL default '0',
  `wr` tinyint(1) NOT NULL default '0',
  `ww` tinyint(1) NOT NULL default '0',
  `wm` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table structure for table `tusuario_perfil`
--
CREATE TABLE `tusuario_perfil` (
  `id_up` bigint(20) unsigned NOT NULL auto_increment,
  `id_usuario` varchar(60) NOT NULL default '',
  `id_perfil` int(10) unsigned NOT NULL default '0',
  `id_grupo` mediumint(8) unsigned NOT NULL default '0',
  `assigned_by` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_up`),
  FOREIGN KEY (`id_usuario`) REFERENCES tusuario(`id_usuario`)
      ON DELETE CASCADE,
  FOREIGN KEY (`id_grupo`) REFERENCES tgrupo(`id_grupo`)
      ON DELETE CASCADE,
  FOREIGN KEY (`id_perfil`) REFERENCES tprofile(`id`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tpending_mail` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `attempts` int(10) unsigned NOT NULL default 0,
  `status` int(2) unsigned NOT NULL default 0,
  `recipient` text DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `body` text DEFAULT NULL,
  `attachment_list` text DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tmenu_visibility` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `menu_section` varchar(100) NOT NULL default '',
  `id_group` int(10) unsigned NOT NULL default '0',
  `mode` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tcompany_activity` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `id_company` mediumint(8) unsigned NOT NULL,
  `written_by` varchar(60) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `description` text NULL DEFAULT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_company`) REFERENCES tcompany(`id`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tuser_report` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `id_user` varchar(60) NOT NULL default '',
  `name` text default NULL,
  `report_type` text default NULL,
  `interval_days` integer unsigned NOT NULL default 7,
  `lenght` integer unsigned NOT NULL default 7,
  `last_executed` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_external` mediumint(8) unsigned NOT NULL,
  `id_group` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincident_sla_graph` (
  `id_incident` int(10) NOT NULL default '0',
  `utimestamp` int(20) unsigned NOT NULL default 0,
  `value` int(1) unsigned NOT NULL default '0',
    KEY `sla_graph_index1` (`id_incident`),
  KEY `idx_utimestamp_sla_graph` USING BTREE (`utimestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 23 may 2012

CREATE TABLE `tnewsletter` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` text default NULL,
  `id_group` mediumint(8) unsigned NOT NULL,
  `description` text default NULL,
  `from_desc` text default NULL,
  `from_address` text default NULL,
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tnewsletter_content` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `id_newsletter` bigint(20) NOT NULL,
  `email_subject` text default NULL,
  `html` text default NULL,
  `plain` text default NULL,
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',  
  `status` int unsigned NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- status could be 0-pending, 1-ready, 2-sent

CREATE TABLE `tnewsletter_address` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `id_newsletter` bigint(20) NOT NULL,
  `name` tinytext default NULL,
  `email` tinytext default NULL,
  `status` int unsigned NOT NULL default '0',
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',  
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- status could be 0-ok, 1-disabled/removed

CREATE TABLE `tnewsletter_tracking` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `id_newsletter` bigint(20) NOT NULL,
  `id_newsletter_address` bigint(20) NOT NULL,
  `id_newsletter_content` bigint(20) NOT NULL,
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',  
  `status` int unsigned NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- status could be 1-read

CREATE TABLE `tnewsletter_queue` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `id_newsletter_content` bigint(20) NOT NULL,
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',  
  `status` int unsigned NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- status could be 0-not ready, 1-ready!, 2-in process, 3-end

CREATE TABLE `tnewsletter_queue_data` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `id_queue` bigint(20) NOT NULL,
  `id_newsletter` bigint(20) NOT NULL,
  `id_newsletter_content` bigint(20) NOT NULL,
  `email` tinytext default NULL,
  `name` tinytext default NULL,
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',  
  `status` int unsigned NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- status could be 0-ready, 1-sent, 2-error

CREATE TABLE `ttranslate_string` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lang` tinytext NOT NULL,
  `string` text NOT NULL,
  `translation` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Post 3.1 release

CREATE TABLE `tlead` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `id_company` mediumint(8) unsigned NOT NULL,
  `id_language` varchar(6) default NULL,
  `id_category` mediumint(8) unsigned default NULL,
  `owner` varchar(60) default NULL,
  `fullname` varchar(150) DEFAULT NULL,
  `email` tinytext  default NULL,
  `phone` tinytext  default NULL,
  `mobile` tinytext  default NULL,
  `position` tinytext  default NULL,
  `company` tinytext  default NULL,
  `country` tinytext  default NULL,
  `description` mediumtext DEFAULT NULL,
  `creation` datetime NOT NULL default '0000-00-00 00:00:00',  
  `modification` datetime NOT NULL default '0000-00-00 00:00:00',  
  `progress` mediumint(5) NULL default 0,
  `estimated_sale` mediumint NULL default 0,
  PRIMARY KEY  (`id`),
  KEY `id_company_idx` (`id_company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tlead_activity` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `id_lead` mediumint(8) unsigned NOT NULL,
  `written_by` mediumtext DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `creation` datetime NOT NULL default '0000-00-00 00:00:00',  
  PRIMARY KEY  (`id`),
  KEY `id_lead_idx` (`id_lead`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tlead_history` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `id_lead` mediumint(8) unsigned NOT NULL,
  `id_user` mediumtext DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',   
  PRIMARY KEY  (`id`),
  KEY `id_lead_idx` (`id_lead`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


