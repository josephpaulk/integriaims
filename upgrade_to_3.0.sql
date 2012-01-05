
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

CREATE TABLE `tapp` (
    `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
	`app_name` tinytext NOT NULL,
    `app_mode` tinyint(2) unsigned NOT NULL DEFAULT 0,
    `id_group` mediumint(8) unsigned NOT NULL,
    `id_category` int(20) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id_group`) REFERENCES tgrupo(`id_grupo`)
		ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`id_category`) REFERENCES tapp_category(`id_category`)
		ON UPDATE CASCADE ON DELETE CASCADE,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tapp_category` (
    `id_category` int(20) unsigned NOT NULL AUTO_INCREMENT,
	`category_name` tinytext NOT NULL,
	PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tapp_default` (
    `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
	`app_name` tinytext NOT NULL,
    `app_mode` tinyint(2) unsigned NOT NULL DEFAULT 0,
    `id_category` int(20) unsigned NOT NULL,    
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id_category`) REFERENCES tapp_category(`id_category`)
		ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tapp_extra_submode` (
    `id_app` int(20) unsigned NOT NULL default 0,
	`extra_substring` varchar(100) NOT NULL,
    `app_mode` tinyint(2) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id_app`, `extra_substring`)	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tapp_activity_data` (
    `id_app` int(20) unsigned NOT NULL default 0,
    `id_user` varchar(60) NOT NULL default '', 
	`app_extra` text NOT NULL,
	`activity_time` int(20) unsigned NOT NULL default 0,
	`start_timestamp` int(20) unsigned NOT NULL default 0,
	`end_timestamp` int(20) unsigned NOT NULL default 0,
	`send_timestamp`  int(20) unsigned NOT NULL default 0,
	KEY `idx_app` (`id_app`),
	KEY `idx_user` (`id_user`),
	KEY `idx_start_timestamp` (`start_timestamp`),
	  FOREIGN KEY (`id_app`)
	  REFERENCES tapp(`id`) 
	  ON UPDATE CASCADE 
	  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tmenu_visibility` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `menu_section` varchar(100) NOT NULL default '',
  `id_group` int(10) unsigned NOT NULL default '0',
  `mode` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Changes done in Development 3.0 version after July 2011


ALTER TABLE tincidencia ADD `email_copy` mediumtext not NULL;
ALTER TABLE tusuario ADD `disabled` int default 0;

-- Added 25 Nov 2011

ALTER TABLE tcompany ADD `id_grupo` mediumint(8) unsigned DEFAULT 0;
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

-- Added 28 Dec 2011

ALTER TABLE tprofile ADD `wr` tinyint(1) NOT NULL default '0';
ALTER TABLE tprofile ADD `ww` tinyint(1) NOT NULL default '0';
ALTER TABLE tprofile ADD `wm` tinyint(1) NOT NULL default '0';

-- Table for bills, and externals expenses imputable to a task / project / company

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

-- Not used anymore (never used, anyway)
DROP TABLE tcost;

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
