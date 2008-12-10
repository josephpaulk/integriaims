alter table tconfig MODIFY value TEXT;
alter table tworkunit MODIFY locked varchar(80) default "";
alter table tkb_data ADD `id_language` varchar(15) NOT NULL default '';

ALTER TABLE tagenda ADD FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE tagenda ADD FOREIGN KEY (`id_group`) REFERENCES tgrupo(`id_grupo`)
      ON UPDATE CASCADE ON DELETE CASCADE;

DROP TABLE tcost;
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

ALTER TABLE tevent ADD FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE tgrupo ADD `banner` varchar(150) default NULL;
ALTER TABLE tgrupo ADD `url` varchar(150) default NULL;
ALTER TABLE tgrupo ADD `email` varchar(150) default NULL;
ALTER TABLE tgrupo ADD `lang` varchar(10) default NULL;
ALTER TABLE tgrupo ADD `id_user_default` varchar(60) NOT NULL default '';
ALTER TABLE tgrupo ADD `forced_email` tinyint(1) unsigned NOT NULL DEFAULT 1;
ALTER TABLE tgrupo ADD FOREIGN KEY (`id_user_default`) REFERENCES tusuario(`id_usuario`)
     ON UPDATE CASCADE ON DELETE SET default;

ALTER TABLE tgrupo ADD FOREIGN KEY (`parent`) REFERENCES tgrupo(`id_grupo`)
     ON UPDATE CASCADE ON DELETE SET default;

CREATE TABLE `tproject_group` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `icon` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
);

ALTER TABLE tincidencia ADD `id_parent` bigint(20) unsigned NULL default 0;
ALTER TABLE tincidencia ADD `sla_disabled` mediumint(8) unsigned NOT NULL DEFAULT 0;
ALTER TABLE tincidencia ADD `affected_sla_id` tinyint unsigned NOT NULL DEFAULT 0;
ALTER TABLE tincidencia ADD `id_incident_type` mediumint(8) unsigned NULL;

ALTER TABLE tincidencia ADD FOREIGN KEY (`id_incident_type`) REFERENCES tincident_type(`id`)
      ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE tincidencia ADD FOREIGN KEY (`id_grupo`) REFERENCES tgrupo(`id_grupo`)
      ON UPDATE CASCADE ON DELETE SET default;

ALTER TABLE tincidencia ADD FOREIGN KEY (`id_creator`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE tincidencia ADD FOREIGN KEY (`id_task`) REFERENCES ttask(`id`)
      ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE tincidencia ADD FOREIGN KEY (`affected_sla_id`) REFERENCES tsla(`id`)
      ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE `tincidencia` ADD FOREIGN KEY (`id_parent`) REFERENCES tincidencia(`id_incidencia`)
  ON UPDATE CASCADE ON DELETE SET default;

ALTER TABLE `tincident_track` ADD `description` text NOT NULL default '';

ALTER TABLE tkb_data ADD FOREIGN KEY (`id_user`) REFERENCES tusuario(`id_usuario`)
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE tkb_data ADD FOREIGN KEY (`id_product`) REFERENCES tkb_product(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE tkb_data ADD FOREIGN KEY (`id_category`) REFERENCES tkb_category(`id`)
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `tkb_product` ADD FOREIGN KEY (`parent`) REFERENCES tkb_product(`id`)
     ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE `tkb_category` ADD FOREIGN KEY (`parent`) REFERENCES tkb_category(`id`)
     ON UPDATE CASCADE ON DELETE RESTRICT;

DROP TABLE tnews;

DROP TABLE tnota;

DROP TABLE tmensajes;

DROP TABLE `tnota_inc`;

DROP TABLE `tnotification_track`;

ALTER TABLE tprofile ADD `vr` tinyint(1) NOT NULL default '0';

ALTER TABLE tprofile ADD `vw` tinyint(1) NOT NULL default '0';

ALTER TABLE tprofile ADD `vm` tinyint(1) NOT NULL default '0';

ALTER TABLE tproject ADD `id_project_group` mediumint(8) unsigned NOT NULL default '0';
ALTER TABLE tproject ADD FOREIGN KEY (`id_owner`) REFERENCES tusuario(`id_usuario`)
     ON UPDATE CASCADE ON DELETE SET default;
ALTER TABLE tproject ADD FOREIGN KEY (`id_project_group`) REFERENCES tproject_group(`id`)
     ON UPDATE CASCADE ON DELETE SET default;

--ALTER TABLE ttask ADD `id_group` int(10) NOT NULL default '0';
ALTER TABLE ttask ADD `periodicity` enum ('none', 'weekly', 'monthly', 'year', '15days', '21days', '10days', '15days', '60days', '90days', '120days', '180days') default 'none';

ALTER TABLE tusuario ADD   `lang` varchar(10) default '';
ALTER TABLE `tworkunit` ADD `public` tinyint(1) unsigned NOT NULL DEFAULT 1;



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


CREATE TABLE `tincident_inventory` (
  `id_incident` bigint(20) unsigned NOT NULL auto_increment,
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

INSERT INTO `um_tupdate_settings` VALUES  ('current_update', '0'), ('customer_key', 'INTEGRIA-FREE'), ('keygen_path', '/usr/share/integria/util/keygen'), ('update_server_host', 'www.artica.es'), ('update_server_port', '80'), ('update_server_path', '/integriaupdate/server.php'), ('updating_binary_path', 'Path where the updated binary files will be stored'), ('updating_code_path', 'Path where the updated code is stored'), ('dbname', ''), ('dbhost', ''), ('dbpass', ''), ('dbuser', ''), ('proxy', ''), ('proxy_port', ''), ('proxy_user', ''), ('proxy_pass', '');

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

ALTER TABLE tgrupo ADD `email` varchar(128) default '';

CREATE TABLE `tinventory_contact` (
  `id_inventory` mediumint(8) unsigned NOT NULL,
  `id_company_contact` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id_inventory`, `id_company_contact`),
  FOREIGN KEY (`id_company_contact`) REFERENCES tcompany_contact(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_inventory`) REFERENCES tinventory(`id`)
     ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO `um_tupdate_settings` VALUES  ('current_update', '0'), ('customer_key', 'INTEGRIA-FREE'), ('keygen_path', '/usr/share/integria/util/keygen'), ('update_server_host', 'www.artica.es'), ('update_server_port', '80'), ('update_server_path', '/integriaupdate/server.php'), ('updating_binary_path', 'Path where the updated binary files will be stored'), ('updating_code_path', 'Path where the updated code is stored'), ('dbname', ''), ('dbhost', ''), ('dbpass', ''), ('dbuser', ''), ('proxy', ''), ('proxy_port', ''), ('proxy_user', ''), ('proxy_pass', '');