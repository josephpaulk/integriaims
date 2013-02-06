
-- ---------------------------------------------------------------------
-- Table `tincident_stats`
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tincident_stats` (
`id_incident` bigint(20) unsigned NOT NULL auto_increment,
  `minutes` bigint(10) unsigned NOT NULL default 0,
  `metric` enum ('user_time', 'status_time', 'group_time', 'total_time', 'total_w_third') NOT NULL,
  `id_user` varchar(60) NOT NULL default '',
  `status` tinyint NOT NULL default 0,
  `id_group` mediumint(8) NOT NULL default 0,
PRIMARY KEY (`id_incident`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------
-- Table `tincidencia`
-- ---------------------------------------------------
ALTER TABLE `tincidencia` DROP COLUMN `origen`;
ALTER TABLE `tincidencia` ADD COLUMN `last_stat_check`  bigint(20) unsigned NOT NULL default '0';
ALTER TABLE `tincidencia` ADD COLUMN `closed_by` varchar(60) NOT NULL default '';

-- ---------------------------------------------------
-- Table `torigin`
-- ---------------------------------------------------
DROP TABLE `torigin``;

-- ---------------------------------------------------
-- Table `tincident_origin`
-- ---------------------------------------------------
DROP TABLE `tincident_origin``;

-- ---------------------------------------------------------------------
-- Table `tincident_status`
-- ---------------------------------------------------------------------
UPDATE `tincident_status` SET name='Pending on a third person'
WHERE name='Resolved';

-- ---------------------------------------------------
-- Table `tincident_type`
-- ---------------------------------------------------
ALTER TABLE `tincident_type` ADD COLUMN `id_group` int(10) NOT NULL default '0';

-- ---------------------------------------------------
-- Table `tincident_type_field`
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS `tincident_type_field` ( 
  `id` mediumint(8) unsigned NOT NULL auto_increment, 
  `id_incident_type` mediumint(8) unsigned NOT NULL, 
  `label` varchar(100) NOT NULL default '', 
  `type` enum ('textarea', 'text', 'combo') default 'text',
  `combo_value` text default NULL,
  PRIMARY KEY  (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------
-- Table `tincident_field_data`
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS `tincident_field_data` ( 
  `id` bigint(20) unsigned NOT NULL auto_increment, 
  `id_incident` bigint(20) unsigned NOT NULL,
  `id_incident_field` mediumint(0) unsigned NOT NULL,
  `data` text default NULL,
  PRIMARY KEY  (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------
-- Table `tusuario`
-- ---------------------------------------------------
ALTER TABLE `tusuario` ADD COLUMN `num_employee` varchar(125) NOT NULL default '';
ALTER TABLE `tusuario` ADD COLUMN `enable_login` tinyint(1) NOT NULL default '1';
