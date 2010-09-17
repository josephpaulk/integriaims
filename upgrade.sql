alter table tconfig MODIFY value TEXT;
alter table tworkunit MODIFY locked varchar(80) default "";
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

ALTER TABLE tgrupo ADD FOREIGN KEY (`id_user_default`) REFERENCES tusuario(`id_usuario`)
     ON UPDATE CASCADE ON DELETE SET default;

ALTER TABLE tgrupo ADD FOREIGN KEY (`parent`) REFERENCES tgrupo(`id_grupo`)
     ON UPDATE CASCADE ON DELETE SET default;

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

ALTER TABLE tproject ADD FOREIGN KEY (`id_owner`) REFERENCES tusuario(`id_usuario`)
     ON UPDATE CASCADE ON DELETE SET default;

ALTER TABLE tproject ADD FOREIGN KEY (`id_project_group`) REFERENCES tproject_group(`id`)
     ON UPDATE CASCADE ON DELETE SET default;

ALTER TABLE tusuario ADD `pwdhash` varchar(100) default '';


ALTER TABLE `tinventory` ADD
 FOREIGN KEY (`id_parent`) REFERENCES tinventory(`id`)
   ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE `tsla` ADD FOREIGN KEY (`id_sla_base`) REFERENCES tsla(`id`)
  ON UPDATE CASCADE ON DELETE SET default;

-- 2010, v2.1

ALTER TABLE tgrupo ADD `id_inventory_default` mediumint(8) default NULL;
ALTER TABLE tgrupo ADD `soft_limit` int(5) unsigned NOT NULL default 0;
ALTER TABLE tgrupo ADD `enforce_soft_limit` int(2) unsigned NOT NULL default 0;
ALTER TABLE tgrupo ADD `hard_limit` int(5) unsigned NOT NULL default 0;

CREATE TABLE `tdownload` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `location` text NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `description` text NOT NULL default '',
  `tag` text NOT NULL default '',
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
);

ALTER TABLE tincidencia ADD `score` mediumint(8) default 0;

CREATE TABLE `tnewsboard` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `content` text NOT NULL default '', 
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE tconfig SET `value` = "ID100520" WHERE `token` = "db_scheme_build";
UPDATE tconfig SET `value` = "2.1dev" WHERE `token` = "db_scheme_version";

-- 20 may

CREATE TABLE `tapp` (
    `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
	`app_name` tinytext NOT NULL,
    `app_mode` tinyint(2) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tapp_activity_data` (
    `id_app` int(20) unsigned NOT NULL default 0,
    `id_user` varchar(60) NOT NULL default '', 
	`app_extra` text NOT NULL,
	`start_timestamp` int(20) unsigned NOT NULL default 0,
	`end_timestamp` int(20) unsigned NOT NULL default 0,
	`send_timestamp`  int(20) unsigned NOT NULL default 0,
	KEY `idx_app` (`id_app`),
	KEY `idx_user` (`id_user`),
	KEY `idx_start_timestamp` (`start_timestamp`) USING BTREE,
	CONSTRAINT `fk_tapp_tapp_activity_data1`
	  FOREIGN KEY (`id_app`)
	  REFERENCES tapp(`id`) 
	  ON UPDATE CASCADE 
	  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


