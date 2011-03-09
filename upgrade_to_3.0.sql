
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

