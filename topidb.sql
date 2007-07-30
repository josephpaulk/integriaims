
-- Table structure for table `tattachment`
--

CREATE TABLE `tattachment` (
  `id_attachment` bigint(20) unsigned NOT NULL auto_increment,
  `id_incidencia` bigint(20) NOT NULL default '0',
  `id_task` bigint(20) NOT NULL default '0',
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

--
-- Table structure for table `tmensajes`
--

DROP TABLE IF EXISTS `tmensajes`;
CREATE TABLE `tmensajes` (
  `id_mensaje` bigint(20) unsigned NOT NULL auto_increment,
  `id_usuario_origen` varchar(100) NOT NULL default '',
  `id_usuario_destino` varchar(100) NOT NULL default '',
  `mensaje` tinytext NOT NULL,
  `timestamp` datetime NOT NULL default '2000-01-01 00:00:00',
  `subject` varchar(255) NOT NULL default '',
  `estado` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_mensaje`)
);

--
-- Table structure for table `tnota`
--

DROP TABLE IF EXISTS `tnota`;
CREATE TABLE `tnota` (
  `id_nota` mediumint(8) unsigned NOT NULL auto_increment,
  `id_usuario` varchar(100) NOT NULL default '0',
  `timestamp` datetime NOT NULL default '2000-01-01 00:00:00',
  `nota` mediumtext NOT NULL,
  PRIMARY KEY  (`id_nota`)
);

--
-- Table structure for table `tnota_inc`
--

DROP TABLE IF EXISTS `tnota_inc`;
CREATE TABLE `tnota_inc` (
  `id_nota_inc` mediumint(8) unsigned NOT NULL auto_increment,
  `id_incidencia` mediumint(9) NOT NULL default '0',
  `id_nota` mediumint(9) NOT NULL default '0',
  PRIMARY KEY  (`id_nota_inc`)
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
  `tw` tinyint(1) NOT NULL default '0',
  `tm` tinyint(1) NOT NULL default '0',
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
  `avatar` varchar(100) default 'people_2',
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

CREATE TABLE `tnews` (
  `id_news` INTEGER UNSIGNED NOT NULL  AUTO_INCREMENT,
  `author` varchar(255)  NOT NULL DEFAULT '',
  `subject` varchar(255)  NOT NULL DEFAULT '',
  `text` TEXT  NOT NULL DEFAULT '',
  `timestamp` DATETIME  NOT NULL DEFAULT 0,
  PRIMARY KEY(`id_news`)
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
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(240) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `start` date NOT NULL default '0000-00-00',
  `end` date NOT NULL default '0000-00-00',
  `id_owner` VARCHAR(125), 
  PRIMARY KEY  (`id`)
);


CREATE TABLE `ttask_track` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_task` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_external` int(10) unsigned NOT NULL default '0',
  `state` tinyint unsigned NOT NULL default '0',
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
);


CREATE TABLE `ttask` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_project` int(10) unsigned NOT NULL default '0',
  `id_parent_task` int(10) unsigned NOT NULL default '0',
  `name` varchar(240) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `completion` tinyint unsigned NOT NULL default '0',
  `priority` tinyint unsigned NOT NULL default '0',
  `dep_type` tinyint unsigned NOT NULL DEFAULT 0,
  `start` date NOT NULL default '0000-00-00',
  `end` date NOT NULL default '0000-00-00',
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
  PRIMARY KEY  (`id`)
);


CREATE TABLE `tworkunit_task` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_task` int(10) unsigned NOT NULL default '0',
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
`id` INT NOT NULL ,
`name` VARCHAR( 2048 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
`task` INT NOT NULL ,
`assigned_user` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`created_by_user` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`priority` INT NOT NULL,
`timestamp` DATETIME NOT NULL
) ENGINE = MYISAM ;
