

/*!40000 ALTER TABLE `tconfig` DISABLE KEYS */;
LOCK TABLES `tconfig` WRITE;
INSERT INTO `tconfig` VALUES (1,'language_code','en'),(3,'block_size','20'),(4,'days_purge','60'),(5,'days_compact','15'),(6,'graph_res','5'),(7,'step_compact','1'),(8,'db_scheme_version','1.3'),(9,'db_scheme_build','PD60328'),(12,'bgimage','background4.jpg'),(13,'show_unknown','0'),(14,'show_lastalerts','1');
UNLOCK TABLES;
/*!40000 ALTER TABLE `tconfig` ENABLE KEYS */;

--
-- Dumping data for table `tconfig_os`
--


/*!40000 ALTER TABLE `tconfig_os` DISABLE KEYS */;
LOCK TABLES `tconfig_os` WRITE;
INSERT INTO `tconfig_os` VALUES (1,'Linux','Linux: All versions','so_linux.gif'),(2,'Solaris','Sun Solaris','so_solaris.gif'),(3,'AIX','IBM AIX','so_aix.gif'),(4,'BSD','OpenBSD, FreeBSD and Others','so_bsd.gif'),(5,'HP-UX','HPUX Unix OS','so_hpux.gif'),(6,'BeOS','BeOS','so_beos.gif'),(7,'Cisco','CISCO IOS','so_cisco.gif'),(8,'MacOS','MAC OS','so_mac.gif'),(9,'Windows','Microsoft Windows OS','so_win.gif'),(10,'Other','Other SO','so_other.gif'),(11,'Network','Pandora Network Agent','network.gif');
UNLOCK TABLES;
/*!40000 ALTER TABLE `tconfig_os` ENABLE KEYS */;

--
-- Dumping data for table `tgrupo`
--

LOCK TABLES `tgrupo` WRITE;
INSERT INTO `tgrupo` VALUES (1,'All','world',0);
INSERT INTO `tgrupo` VALUES (2,'Servers','server_database',0);
INSERT INTO `tgrupo` VALUES (3,'IDS','eye',0);
INSERT INTO `tgrupo` VALUES (4,'Firewalls','firewall',0);
INSERT INTO `tgrupo` VALUES (8,'Databases','database_gear',0);
INSERT INTO `tgrupo` VALUES (9,'Comms','transmit',0);
INSERT INTO `tgrupo` VALUES (10,'Others','house',0);
INSERT INTO `tgrupo` VALUES (11,'Workstations','computer',0);
INSERT INTO `tgrupo` VALUES (12,'Applications','bricks',0);
UNLOCK TABLES;

LOCK TABLES `tlanguage` WRITE;
INSERT INTO `tlanguage` VALUES ('bb','Bable'),('ca','Catal&agrave;'),('de','German'),('en','English'),('es','Espa&ntilde;ol'),('es_gl','Gallego'),('es_la','Espa&ntilde;ol-Latinoam&eacute;rica'),('eu','Euskera'),('fr','Fran&ccedil;ais'),('pt_br','Portuguese-Brazil');
UNLOCK TABLES;



LOCK TABLES `tlink` WRITE;
INSERT INTO `tlink` VALUES (0000000001,'GeekTools','www.geektools.com'),(0000000002,'CentralOPS','http://www.centralops.net/'),(0000000003,'Pandora Project','http://pandora.sourceforge.net'),(0000000004,'Babel Project','http://babel.sourceforge.net'),(0000000005,'Google','http://www.google.com');
UNLOCK TABLES;


INSERT INTO `tusuario` VALUES ('admin','Default Admin','fe705027892dc1b806629706b6a445fe','Default FRITS Admin superuser. Please change password ASAP','2007-03-27 18:59:39','admin@frits.sf.net','555-555-555',1),('demo','Demo user','fe01ce2a7fbac8fafaed7c982a04e229','Other users can connect with this account.','2006-04-20 13:00:05','demo@nowhere.net','+4555435435',0);



INSERT INTO `tusuario_perfil` VALUES (1,'demo',1,1,'admin'),(2,'admin',5,1,'admin');



INSERT INTO `tperfil` VALUES (1,'Operator (Read)',0,1,0,1,0,0,0,0,0,0),(2,'Operator (Write)',1,1,0,1,0,0,0,0,0,0),(3,'Chief Operator',1,1,1,1,0,0,0,0,0,0),(4,'Group coordinator',1,1,1,1,1,1,1,0,0,0),(5,'Pandora Administrator',1,1,1,1,1,1,1,1,1,1);


INSERT INTO tincident_resolution (name) VALUES ('Fixed'), ('Invalid'), ('Wont fix'), ('Duplicate'), ('Works for me'), ('Incomplete'), ('Expired'), ('Moved'), ('In process');

INSERT INTO tincident_state (name) VALUES ('New'), ('Unconfirmed'), ('Assigned'), ('Re-opened'), ('Verified'), ('Resolved'), ('Closed');

INSERT INTO tincident_origin (name) VALUES ('User report'), ('Customer'), ('Internal department'), ('External department'), ('Application data'), ('Bug report'), ('Problem detected'), ('Other source' );


