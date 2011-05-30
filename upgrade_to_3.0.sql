
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
	CONSTRAINT `fk_tapp_tapp_activity_data1`
	  FOREIGN KEY (`id_app`)
	  REFERENCES tapp(`id`) 
	  ON UPDATE CASCADE 
	  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
