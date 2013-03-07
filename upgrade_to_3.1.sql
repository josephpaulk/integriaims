-- Added 20121206

UPDATE tlanguage SET id_language = 'en_GB' WHERE id_language = 'en';
ALTER TABLE ttask ADD count_hours TINYINT(1) DEFAULT '1';

-- Added 20121210

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

