-- MySQL dump 10.13  Distrib 5.1.36, for suse-linux-gnu (i686)
--
-- Host: localhost    Database: crap
-- ------------------------------------------------------
-- Server version	5.0.67-Max

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Not dumping tablespaces as no INFORMATION_SCHEMA.FILES table on this server
--

--
-- Dumping data for table `tagenda`
--

LOCK TABLES `tagenda` WRITE;
/*!40000 ALTER TABLE `tagenda` DISABLE KEYS */;
/*!40000 ALTER TABLE `tagenda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tattachment`
--

LOCK TABLES `tattachment` WRITE;
/*!40000 ALTER TABLE `tattachment` DISABLE KEYS */;
INSERT INTO `tattachment` VALUES (1,1,0,0,'admin','Nil_computer_small.png','It&#039;s me !',34026);
/*!40000 ALTER TABLE `tattachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tbuilding`
--

LOCK TABLES `tbuilding` WRITE;
/*!40000 ALTER TABLE `tbuilding` DISABLE KEYS */;
INSERT INTO `tbuilding` VALUES (1,'Silva 2','This is the main office building of Artica ST.');
/*!40000 ALTER TABLE `tbuilding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcompany`
--

LOCK TABLES `tcompany` WRITE;
/*!40000 ALTER TABLE `tcompany` DISABLE KEYS */;
INSERT INTO `tcompany` VALUES (1,'Spatial Curvature Ltd.','C/ Pez 3\r\n28013 Madrid\r\nSpain','B9493353','',1);
/*!40000 ALTER TABLE `tcompany` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcompany_contact`
--

LOCK TABLES `tcompany_contact` WRITE;
/*!40000 ALTER TABLE `tcompany_contact` DISABLE KEYS */;
INSERT INTO `tcompany_contact` VALUES (1,1,'Sancho Lerena','slerena@nowhere.net','23443534545','435748574','Production Engineer','',0);
/*!40000 ALTER TABLE `tcompany_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcompany_role`
--

LOCK TABLES `tcompany_role` WRITE;
/*!40000 ALTER TABLE `tcompany_role` DISABLE KEYS */;
INSERT INTO `tcompany_role` VALUES (1,'Developer','');
/*!40000 ALTER TABLE `tcompany_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tconfig`
--

LOCK TABLES `tconfig` WRITE;
/*!40000 ALTER TABLE `tconfig` DISABLE KEYS */;
INSERT INTO `tconfig` VALUES (1,'language_code','en'),(2,'block_size','25'),(3,'db_scheme_version','2.1-dev'),(4,'db_scheme_build','ID100219'),(5,'date_format','F j, Y, g:i a'),(6,'currency','eu'),(7,'sitename','Integria Demo'),(8,'hours_perday','8'),(46,'FOOTER_EMAIL','Please do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\r\n\r\nThanks for your time and have a nice day\r\n\r\n'),(45,'HEADER_EMAIL','Hello, \r\n\r\nThis is an automated message coming from Integria\r\n\r\n'),(47,'notification_period','86400'),(12,'limit_size','1000'),(48,'mail_from','integria@localhost'),(49,'smtp_port','0'),(50,'smtp_host','localhost'),(51,'smtp_user',''),(52,'smtp_pass',''),(53,'pop_host',''),(54,'pop_user',''),(55,'pop_pass',''),(35,'timezone','Europe/Madrid'),(36,'autowu_completion','0'),(37,'no_wu_completion',''),(38,'fontsize','10'),(39,'incident_reporter','0'),(40,'show_creator_incident','1'),(41,'show_owner_incident','1'),(42,'pwu_defaultime','4'),(43,'iwu_defaultime','0.25'),(44,'api_acl','127.0.0.1, *');
/*!40000 ALTER TABLE `tconfig` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcontract`
--

LOCK TABLES `tcontract` WRITE;
/*!40000 ALTER TABLE `tcontract` DISABLE KEYS */;
INSERT INTO `tcontract` VALUES (1,'Support','234574854','','2010-02-17','2010-02-17',1,1,2);
/*!40000 ALTER TABLE `tcontract` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcost`
--

LOCK TABLES `tcost` WRITE;
/*!40000 ALTER TABLE `tcost` DISABLE KEYS */;
/*!40000 ALTER TABLE `tcost` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcustom_search`
--

LOCK TABLES `tcustom_search` WRITE;
/*!40000 ALTER TABLE `tcustom_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `tcustom_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tdownload`
--

LOCK TABLES `tdownload` WRITE;
/*!40000 ALTER TABLE `tdownload` DISABLE KEYS */;
/*!40000 ALTER TABLE `tdownload` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tdownload_category`
--

LOCK TABLES `tdownload_category` WRITE;
/*!40000 ALTER TABLE `tdownload_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `tdownload_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tdownload_tracking`
--

LOCK TABLES `tdownload_tracking` WRITE;
/*!40000 ALTER TABLE `tdownload_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `tdownload_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tevent`
--

LOCK TABLES `tevent` WRITE;
/*!40000 ALTER TABLE `tevent` DISABLE KEYS */;
INSERT INTO `tevent` VALUES (1,'MANUFACTURER CREATED','2010-02-17 01:16:29','admin',1,0,'Artica ST'),(2,'SLA CREATED','2010-02-17 01:16:51','admin',1,0,'Basic SLA'),(3,'BUILDING CREATED','2010-02-17 01:17:09','admin',1,0,'Silva 2'),(4,'PRODUCT CREATED','2010-02-17 01:17:23','admin',1,0,'Integria'),(5,'COMPANY ROLE CREATED','2010-02-17 01:17:44','admin',1,0,'Developer'),(6,'COMPANY CREATED','2010-02-17 01:18:24','admin',1,0,'Spatial Curvature Ltd.'),(7,'CONTACT CREATED','2010-02-17 01:18:47','admin',1,0,'Sancho Lerena'),(8,'CONTRACT CREATED','2010-02-17 01:19:09','admin',1,0,'Support'),(9,'PROJECT GROUP CREATED','2010-02-17 01:22:20','admin',1,0,'R&amp;D'),(10,'PROJECT GROUP UPDATED','2010-02-17 01:22:24','admin',1,0,'R&amp;D'),(11,'PWU INSERT','2010-02-17 01:51:08','demo',2,0,'Working on that.'),(12,'PWU INSERT','2010-02-17 01:51:44','demo',3,0,'Working on that.'),(13,'PWU INSERT','2010-02-17 01:52:08','demo',6,0,'Working on that.'),(14,'PWU INSERT','2010-02-17 01:52:32','demo',5,0,'Working on that.'),(15,'PWU INSERT','2010-02-17 01:52:57','demo',4,0,'Working on that. TOO DIFFICULT!'),(16,'PWU INSERT','2010-02-17 01:53:30','demo',4,0,'Extra hours on customer'),(17,'PWU INSERT','2010-02-17 01:54:15','admin',3,0,'This is crazy !'),(18,'PWU INSERT','2010-02-17 01:54:41','admin',3,0,'Working on that.'),(19,'PWU INSERT','2010-02-17 01:54:58','admin',1,0,'Working on that.'),(20,'PWU INSERT','2010-02-17 01:55:21','admin',2,0,''),(21,'KB ITEM CREATED','2010-02-17 01:57:01','demo',1,0,'Sample KB article'),(22,'CATEGORY CREATED','2010-02-17 01:57:13','demo',1,0,'Demo articles'),(23,'KB ITEM UPDATED','2010-02-17 02:00:52','admin',1,0,'Sample KB article'),(24,'PWU INSERT','2010-02-17 02:05:29','admin',0,0,'Holidays.'),(25,'PWU INSERT','2010-02-17 02:05:47','admin',0,0,'Holydays.\r\n'),(26,'INCIDENT_DELETED','2010-02-17 02:09:10','admin',0,0,'API incident test'),(27,'PWU INSERT','2010-02-17 02:15:30','admin',0,0,'Holydays');
/*!40000 ALTER TABLE `tevent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tgrupo`
--

LOCK TABLES `tgrupo` WRITE;
/*!40000 ALTER TABLE `tgrupo` DISABLE KEYS */;
INSERT INTO `tgrupo` VALUES (1,'All','world.png',NULL,NULL,NULL,0,'admin',NULL,0,0,1,'',0),(2,'Customer #A','eye.png','','','en',0,'demo',1,0,0,1,'',0),(3,'Customer #B','eye.png','','','en',0,'demo',1,0,0,1,'',0),(4,'Engineering','computer.png','','','en',0,'demo',1,0,0,1,'',0);
/*!40000 ALTER TABLE `tgrupo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincidencia`
--

LOCK TABLES `tincidencia` WRITE;
/*!40000 ALTER TABLE `tincidencia` DISABLE KEYS */;
INSERT INTO `tincidencia` VALUES (1,'2010-02-17 01:35:35','0000-00-00 00:00:00','My first incident !','Hi, this is my first demo incident.\r\n\r\nFantastic !\r\n','demo',1,1,3,3,'2010-02-17 01:50:05','admin',1,0,0,'',0,0,0,0),(2,'2010-02-17 01:56:33','0000-00-00 00:00:00','Another incident','Another sample incident.','admin',1,3,1,4,'2010-02-17 02:16:57','demo',1,0,9,'',0,0,0,0);
/*!40000 ALTER TABLE `tincidencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincident_contact_reporters`
--

LOCK TABLES `tincident_contact_reporters` WRITE;
/*!40000 ALTER TABLE `tincident_contact_reporters` DISABLE KEYS */;
/*!40000 ALTER TABLE `tincident_contact_reporters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincident_inventory`
--

LOCK TABLES `tincident_inventory` WRITE;
/*!40000 ALTER TABLE `tincident_inventory` DISABLE KEYS */;
INSERT INTO `tincident_inventory` VALUES (1,1),(2,1),(3,1);
/*!40000 ALTER TABLE `tincident_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincident_origin`
--

LOCK TABLES `tincident_origin` WRITE;
/*!40000 ALTER TABLE `tincident_origin` DISABLE KEYS */;
INSERT INTO `tincident_origin` VALUES (1,'User report'),(2,'Customer'),(3,'Internal department'),(4,'External department'),(5,'Application data'),(6,'Bug report'),(7,'Problem detected'),(8,'Other source');
/*!40000 ALTER TABLE `tincident_origin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincident_resolution`
--

LOCK TABLES `tincident_resolution` WRITE;
/*!40000 ALTER TABLE `tincident_resolution` DISABLE KEYS */;
INSERT INTO `tincident_resolution` VALUES (1,'Fixed'),(2,'Invalid'),(3,'Wont fix'),(4,'Duplicate'),(5,'Works for me'),(6,'Incomplete'),(7,'Expired'),(8,'Moved'),(9,'In process');
/*!40000 ALTER TABLE `tincident_resolution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincident_status`
--

LOCK TABLES `tincident_status` WRITE;
/*!40000 ALTER TABLE `tincident_status` DISABLE KEYS */;
INSERT INTO `tincident_status` VALUES (1,'New'),(2,'Unconfirmed'),(3,'Assigned'),(4,'Re-opened'),(5,'Verified'),(6,'Resolved'),(7,'Closed');
/*!40000 ALTER TABLE `tincident_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincident_track`
--

LOCK TABLES `tincident_track` WRITE;
/*!40000 ALTER TABLE `tincident_track` DISABLE KEYS */;
INSERT INTO `tincident_track` VALUES (1,1,10,'2010-02-17 01:35:35','admin',1,'Unknown update'),(2,1,0,'2010-02-17 01:35:35','admin',0,'Created'),(3,1,2,'2010-02-17 01:42:58','admin',0,'Workunit added'),(4,1,3,'2010-02-17 01:47:33','admin',0,'File added'),(5,1,2,'2010-02-17 01:47:46','admin',0,'Workunit added'),(6,1,2,'2010-02-17 01:48:56','admin',0,'Workunit added'),(7,1,2,'2010-02-17 01:50:22','demo',0,'Workunit added'),(8,1,10,'2010-02-17 01:56:33','demo',1,'Unknown update'),(9,2,0,'2010-02-17 01:56:33','demo',0,'Created'),(10,2,2,'2010-02-17 02:06:58','demo',0,'Workunit added'),(11,2,2,'2010-02-17 02:07:11','admin',0,'Workunit added'),(12,1,10,'2010-02-17 02:09:03','',1,'Unknown update'),(14,3,18,'2010-02-17 02:09:10','admin',0,'Incident deleted'),(15,2,2,'2010-02-17 02:17:01','admin',0,'Workunit added');
/*!40000 ALTER TABLE `tincident_track` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincident_type`
--

LOCK TABLES `tincident_type` WRITE;
/*!40000 ALTER TABLE `tincident_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `tincident_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tinventory`
--

LOCK TABLES `tinventory` WRITE;
/*!40000 ALTER TABLE `tinventory` DISABLE KEYS */;
INSERT INTO `tinventory` VALUES (1,'Sample inventory object.','This is a sample inventory object. \n\nPut here description you want.','','',NULL,1,0.000,'',1,1,1,1,1,0,'','',NULL,NULL,'','','',NULL);
/*!40000 ALTER TABLE `tinventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tinventory_contact`
--

LOCK TABLES `tinventory_contact` WRITE;
/*!40000 ALTER TABLE `tinventory_contact` DISABLE KEYS */;
INSERT INTO `tinventory_contact` VALUES (1,1);
/*!40000 ALTER TABLE `tinventory_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tinventory_reports`
--

LOCK TABLES `tinventory_reports` WRITE;
/*!40000 ALTER TABLE `tinventory_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `tinventory_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tkb_category`
--

LOCK TABLES `tkb_category` WRITE;
/*!40000 ALTER TABLE `tkb_category` DISABLE KEYS */;
INSERT INTO `tkb_category` VALUES (1,'Demo articles','','bricks.png',0);
/*!40000 ALTER TABLE `tkb_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tkb_data`
--

LOCK TABLES `tkb_data` WRITE;
/*!40000 ALTER TABLE `tkb_data` DISABLE KEYS */;
INSERT INTO `tkb_data` VALUES (1,'Sample KB article','Swift Mailer version 3 throws exceptions in PHP5. These exceptions should ideally be caught so that you can recover from them if required. It&#039;s not compulsory to catch exceptions but it is good practice and it does help. The API documentation packaged in the ?docs? folder indicates where exceptions are thrown. The only time this should happen in practise, is if your mail server is not configured correctly or is unavailable, or if you try doing something illegal with the message object. ','2010-02-17 02:00:52','en','admin',1,1);
/*!40000 ALTER TABLE `tkb_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tkb_product`
--

LOCK TABLES `tkb_product` WRITE;
/*!40000 ALTER TABLE `tkb_product` DISABLE KEYS */;
INSERT INTO `tkb_product` VALUES (1,'Integria','','box.png',0);
/*!40000 ALTER TABLE `tkb_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tlanguage`
--

LOCK TABLES `tlanguage` WRITE;
/*!40000 ALTER TABLE `tlanguage` DISABLE KEYS */;
INSERT INTO `tlanguage` VALUES ('en','English'),('es','Español'),('bn','বাংলা'),('da','Dansk'),('el','Ελληνικά'),('fr','Français'),('ko','한국어'),('nl','Nederlands'),('ru','Русский'),('tr','Türkçe');
/*!40000 ALTER TABLE `tlanguage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tlink`
--

LOCK TABLES `tlink` WRITE;
/*!40000 ALTER TABLE `tlink` DISABLE KEYS */;
INSERT INTO `tlink` VALUES (0000000001,'Integria Project','http://integria.sourceforge.net'),(0000000002,'Artica ST','http://www.artica.es'),(0000000003,'Pandora FMS Project','http://pandora.sourceforge.net'),(0000000004,'Babel Project','http://babel.sourceforge.net'),(0000000005,'Google','http://www.google.com');
/*!40000 ALTER TABLE `tlink` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tmanufacturer`
--

LOCK TABLES `tmanufacturer` WRITE;
/*!40000 ALTER TABLE `tmanufacturer` DISABLE KEYS */;
INSERT INTO `tmanufacturer` VALUES (1,'Artica ST','C/ Silva 2, 1-1\r\n28013 Madrid\r\nSpain','Hey, we&#039;re the developers of Integria !',0,0);
/*!40000 ALTER TABLE `tmanufacturer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tmilestone`
--

LOCK TABLES `tmilestone` WRITE;
/*!40000 ALTER TABLE `tmilestone` DISABLE KEYS */;
INSERT INTO `tmilestone` VALUES (1,1,'2010-05-17 00:00:00','First functional full-test','');
/*!40000 ALTER TABLE `tmilestone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tproject`
--

LOCK TABLES `tproject` WRITE;
/*!40000 ALTER TABLE `tproject` DISABLE KEYS */;
INSERT INTO `tproject` VALUES (-1,'Non imputable hours (Special)','','0000-00-00','0000-00-00','',1,0),(1,'Sample project','This is a sample project.','2010-02-17','2010-12-31','admin',0,1);
/*!40000 ALTER TABLE `tproject` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tproject_group`
--

LOCK TABLES `tproject_group` WRITE;
/*!40000 ALTER TABLE `tproject_group` DISABLE KEYS */;
INSERT INTO `tproject_group` VALUES (1,'R&amp;D','applications-system.png');
/*!40000 ALTER TABLE `tproject_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tproject_track`
--

LOCK TABLES `tproject_track` WRITE;
/*!40000 ALTER TABLE `tproject_track` DISABLE KEYS */;
INSERT INTO `tproject_track` VALUES (1,1,'admin',21,'2010-02-17 01:22:04',0),(2,1,'admin',22,'2010-02-17 01:22:34',0),(3,1,'admin',26,'2010-02-17 01:23:03',0),(4,1,'admin',26,'2010-02-17 01:23:55',0),(5,1,'admin',26,'2010-02-17 01:32:55',0),(6,1,'admin',26,'2010-02-17 01:33:18',0),(7,1,'admin',26,'2010-02-17 01:34:05',0),(8,1,'admin',26,'2010-02-17 01:34:29',0);
/*!40000 ALTER TABLE `tproject_track` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `trole`
--

LOCK TABLES `trole` WRITE;
/*!40000 ALTER TABLE `trole` DISABLE KEYS */;
INSERT INTO `trole` VALUES (1,'Project manager','',125),(2,'Systems engineer','',40),(3,'Junior consultant','',50),(4,'Junior programmer','',45),(5,'Senior programmer','',65),(6,'Analist','',75),(7,'Senior consultant','',75),(8,'Support engineer','',30);
/*!40000 ALTER TABLE `trole` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `trole_people_project`
--

LOCK TABLES `trole_people_project` WRITE;
/*!40000 ALTER TABLE `trole_people_project` DISABLE KEYS */;
INSERT INTO `trole_people_project` VALUES (1,'admin',1,1),(2,'demo',3,1);
/*!40000 ALTER TABLE `trole_people_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `trole_people_task`
--

LOCK TABLES `trole_people_task` WRITE;
/*!40000 ALTER TABLE `trole_people_task` DISABLE KEYS */;
INSERT INTO `trole_people_task` VALUES (1,'admin',1,1),(2,'admin',1,2),(3,'demo',3,2),(4,'admin',1,3),(5,'demo',3,3),(6,'admin',1,4),(7,'demo',3,4),(8,'admin',1,5),(9,'demo',3,5),(10,'admin',1,6),(11,'demo',3,6);
/*!40000 ALTER TABLE `trole_people_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tsesion`
--

LOCK TABLES `tsesion` WRITE;
/*!40000 ALTER TABLE `tsesion` DISABLE KEYS */;
INSERT INTO `tsesion` VALUES (1,'admin','','Project created','User admin created project \'Sample project\'','2010-02-17 01:22:04',1266366124),(2,'admin','','Project #1 tracking updated','State #21','2010-02-17 01:22:04',1266366124),(3,'admin','::1','Project updated','Project Sample project','2010-02-17 01:22:34',1266366154),(4,'admin','','Project #1 tracking updated','State #22','2010-02-17 01:22:34',1266366154),(5,'admin','::1','Task added to project','Task \'Analysis\' added to project \'1\'','2010-02-17 01:23:03',1266366183),(6,'admin','','Task #1 tracking updated','State #11','2010-02-17 01:23:03',1266366183),(7,'admin','','Project #1 tracking updated','State #26','2010-02-17 01:23:03',1266366183),(8,'admin','::1','User/Role added to project','User demo added to project Sample project','2010-02-17 01:23:15',1266366195),(9,'admin','::1','Task added to project','Task \'Design\' added to project \'1\'','2010-02-17 01:23:55',1266366235),(10,'admin','','Task #2 tracking updated','State #11','2010-02-17 01:23:55',1266366235),(11,'admin','','Project #1 tracking updated','State #26','2010-02-17 01:23:55',1266366235),(12,'admin','::1','Task updated','Task \'Analysis\' updated to project \'1\'','2010-02-17 01:32:28',1266366748),(13,'admin','','Task #1 tracking updated','State #12','2010-02-17 01:32:28',1266366748),(14,'admin','::1','Task added to project','Task \'Development\' added to project \'1\'','2010-02-17 01:32:55',1266366775),(15,'admin','','Task #3 tracking updated','State #11','2010-02-17 01:32:55',1266366775),(16,'admin','','Project #1 tracking updated','State #26','2010-02-17 01:32:55',1266366775),(17,'admin','::1','Task added to project','Task \'Testing\' added to project \'1\'','2010-02-17 01:33:18',1266366798),(18,'admin','','Task #4 tracking updated','State #11','2010-02-17 01:33:18',1266366798),(19,'admin','','Project #1 tracking updated','State #26','2010-02-17 01:33:18',1266366798),(20,'admin','::1','Task added to project','Task \'Documentation\' added to project \'1\'','2010-02-17 01:34:05',1266366845),(21,'admin','','Task #5 tracking updated','State #11','2010-02-17 01:34:05',1266366845),(22,'admin','','Project #1 tracking updated','State #26','2010-02-17 01:34:05',1266366845),(23,'admin','::1','Task added to project','Task \'Packaging\' added to project \'1\'','2010-02-17 01:34:29',1266366869),(24,'admin','','Task #6 tracking updated','State #11','2010-02-17 01:34:29',1266366869),(25,'admin','','Project #1 tracking updated','State #26','2010-02-17 01:34:29',1266366869),(26,'admin','::1','Incident updated','Unknown update','2010-02-17 01:35:35',1266366935),(27,'admin','::1','Incident created','User admin created incident #1','2010-02-17 01:35:35',1266366935),(28,'admin','::1','Incident updated','Created','2010-02-17 01:35:35',1266366935),(29,'admin','127.0.0.1','Incident updated','Workunit added','2010-02-17 01:42:58',1266367378),(30,'admin','127.0.0.1','Incident updated','File added','2010-02-17 01:47:33',1266367653),(31,'admin','127.0.0.1','Incident updated','Workunit added','2010-02-17 01:47:46',1266367666),(32,'admin','127.0.0.1','Incident updated','Workunit added','2010-02-17 01:48:56',1266367736),(33,'demo','127.0.0.1','Logon','Logged in','2010-02-17 01:49:41',1266367781),(34,'demo','127.0.0.1','Incident updated','Workunit added','2010-02-17 01:50:22',1266367822),(35,'admin','127.0.0.1','Spare work unit added','Workunit for admin added to Task ID #3','2010-02-17 01:54:15',1266368055),(36,'demo','::1','Incident updated','Unknown update','2010-02-17 01:56:33',1266368193),(37,'demo','::1','Incident created','User demo created incident #2','2010-02-17 01:56:33',1266368193),(38,'demo','::1','Incident updated','Created','2010-02-17 01:56:33',1266368193),(39,'admin','127.0.0.1','Task updated','Task \'Analysis\' updated to project \'1\'','2010-02-17 02:02:16',1266368536),(40,'admin','','Task #1 tracking updated','State #12','2010-02-17 02:02:16',1266368536),(41,'admin','127.0.0.1','Task updated','Task \'Design\' updated to project \'1\'','2010-02-17 02:02:23',1266368543),(42,'admin','','Task #2 tracking updated','State #12','2010-02-17 02:02:23',1266368543),(43,'admin','127.0.0.1','Task updated','Task \'Development\' updated to project \'1\'','2010-02-17 02:02:29',1266368549),(44,'admin','','Task #3 tracking updated','State #12','2010-02-17 02:02:29',1266368549),(45,'admin','127.0.0.1','Task updated','Task \'Documentation\' updated to project \'1\'','2010-02-17 02:02:34',1266368554),(46,'admin','','Task #5 tracking updated','State #12','2010-02-17 02:02:34',1266368554),(47,'admin','127.0.0.1','Task updated','Task \'Testing\' updated to project \'1\'','2010-02-17 02:02:40',1266368560),(48,'admin','','Task #4 tracking updated','State #12','2010-02-17 02:02:40',1266368560),(49,'admin','127.0.0.1','Task updated','Task \'Packaging\' updated to project \'1\'','2010-02-17 02:02:48',1266368568),(50,'admin','','Task #6 tracking updated','State #12','2010-02-17 02:02:48',1266368568),(51,'demo','::1','Incident updated','Workunit added','2010-02-17 02:06:58',1266368818),(52,'admin','::1','Incident updated','Workunit added','2010-02-17 02:07:11',1266368831),(53,'','::1','Incident updated','Unknown update','2010-02-17 02:09:03',1266368943),(54,'admin','::1','Incident created (From API)','User admin created incident #3','2010-02-17 02:09:03',1266368943),(55,'','::1','Incident updated','Created','2010-02-17 02:09:03',1266368943),(56,'admin','::1','Incident updated','Incident deleted','2010-02-17 02:09:10',1266368950),(57,'admin','::1','Incident deleted','User admin deleted incident #3','2010-02-17 02:09:10',1266368950),(58,'admin','::1','Logoff','Logged out','2010-02-17 02:09:18',1266368958),(59,'admin','::1','Logon','Logged in','2010-02-17 02:10:55',1266369055),(60,'admin','::1','Work unit locked','Workunit for admin','2010-02-17 02:11:53',1266369113),(61,'admin','::1','Work unit locked','Workunit for admin','2010-02-17 02:11:55',1266369115),(62,'admin','::1','Logoff','Logged out','2010-02-17 02:16:17',1266369377),(63,'admin','::1','Logon','Logged in','2010-02-17 02:16:54',1266369414),(64,'admin','::1','Incident updated','Workunit added','2010-02-17 02:17:01',1266369421),(65,'admin','::1','Logoff','Logged out','2010-02-17 02:17:12',1266369432);
/*!40000 ALTER TABLE `tsesion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tsla`
--

LOCK TABLES `tsla` WRITE;
/*!40000 ALTER TABLE `tsla` DISABLE KEYS */;
INSERT INTO `tsla` VALUES (1,'Basic SLA','',12,240,10,1,0);
/*!40000 ALTER TABLE `tsla` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `ttask`
--

LOCK TABLES `ttask` WRITE;
/*!40000 ALTER TABLE `ttask` DISABLE KEYS */;
INSERT INTO `ttask` VALUES (-1,-1,0,'Vacations','',0,0,0,'0000-00-00','0000-00-00',0,0.00,0,'none'),(-2,-1,0,'Disease','',0,0,0,'0000-00-00','0000-00-00',0,0.00,0,'none'),(-3,-1,0,'Not justified','',0,0,0,'0000-00-00','0000-00-00',0,0.00,0,'none'),(-4,-1,0,'Workunits lost (without project/task)','',0,0,0,'0000-00-00','0000-00-00',0,0.00,0,'none'),(1,1,0,'Analysis','',65,0,0,'2010-02-17','2010-03-04',120,2000.00,1,'none'),(2,1,0,'Design','',90,0,0,'2010-03-15','2010-04-08',191,25000.00,1,'none'),(3,1,0,'Development','',90,0,0,'2010-03-31','2010-05-31',488,40000.00,1,'none'),(4,1,0,'Testing','',85,0,0,'2010-05-31','2010-06-30',240,12555.00,1,'none'),(5,1,0,'Documentation','',35,0,0,'2010-04-17','2010-05-10',184,12000.00,1,'none'),(6,1,3,'Packaging','',100,0,0,'2010-04-30','2010-05-14',112,5000.00,1,'none');
/*!40000 ALTER TABLE `ttask` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `ttask_inventory`
--

LOCK TABLES `ttask_inventory` WRITE;
/*!40000 ALTER TABLE `ttask_inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `ttask_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `ttask_track`
--

LOCK TABLES `ttask_track` WRITE;
/*!40000 ALTER TABLE `ttask_track` DISABLE KEYS */;
INSERT INTO `ttask_track` VALUES (1,1,'admin',0,11,'2010-02-17 01:23:03'),(2,2,'admin',0,11,'2010-02-17 01:23:55'),(3,1,'admin',0,12,'2010-02-17 01:32:28'),(4,3,'admin',0,11,'2010-02-17 01:32:55'),(5,4,'admin',0,11,'2010-02-17 01:33:18'),(6,5,'admin',0,11,'2010-02-17 01:34:05'),(7,6,'admin',0,11,'2010-02-17 01:34:29'),(8,1,'admin',0,12,'2010-02-17 02:02:16'),(9,2,'admin',0,12,'2010-02-17 02:02:23'),(10,3,'admin',0,12,'2010-02-17 02:02:29'),(11,5,'admin',0,12,'2010-02-17 02:02:34'),(12,4,'admin',0,12,'2010-02-17 02:02:40'),(13,6,'admin',0,12,'2010-02-17 02:02:48');
/*!40000 ALTER TABLE `ttask_track` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `ttodo`
--

LOCK TABLES `ttodo` WRITE;
/*!40000 ALTER TABLE `ttodo` DISABLE KEYS */;
INSERT INTO `ttodo` VALUES (1,'Sample todo',0,'demo','admin',0,'2010-02-17 02:01:23','Remember to finish design task.','2010-02-17 02:01:23',2),(2,'Myself reminder: Call the doctor !',0,'admin','admin',4,'2010-02-17 02:01:43','Sample todo item.','2010-02-17 02:01:43',0);
/*!40000 ALTER TABLE `ttodo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tusuario`
--

LOCK TABLES `tusuario` WRITE;
/*!40000 ALTER TABLE `tusuario` DISABLE KEYS */;
INSERT INTO `tusuario` VALUES ('admin','Default Admin','2f62afb6e17e46f0717225bcca6225b7','Default Integria Admin superuser. Please change password ASAP','2010-02-17 02:16:54','admin@integria.sf.net','555-555-555',1,'people_1',''),('demo','Demo user','fe01ce2a7fbac8fafaed7c982a04e229','Other users can connect with this account.','2010-02-17 01:49:41','demo@nowhere.net','+4555435435',0,'people_3','');
/*!40000 ALTER TABLE `tusuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tvacationday`
--

LOCK TABLES `tvacationday` WRITE;
/*!40000 ALTER TABLE `tvacationday` DISABLE KEYS */;
/*!40000 ALTER TABLE `tvacationday` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `twizard`
--

LOCK TABLES `twizard` WRITE;
/*!40000 ALTER TABLE `twizard` DISABLE KEYS */;
/*!40000 ALTER TABLE `twizard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tworkunit`
--

LOCK TABLES `tworkunit` WRITE;
/*!40000 ALTER TABLE `tworkunit` DISABLE KEYS */;
INSERT INTO `tworkunit` VALUES (1,'2010-02-17 01:42:36',0.25,'admin','This is a work &quot;report&quot; on this incident added by Admin',0,0,'',1),(2,'2010-02-17 01:47:41',0.25,'admin','More workunits !',0,0,'',1),(3,'2010-02-17 01:48:48',0.25,'admin','Another one !',0,0,'',1),(4,'2010-02-17 01:50:05',0.25,'demo','Hi, I&#039;m the demo user and I&#039;ve assigned this incident.\n\nI will try to solve ! ',0,0,'',1),(5,'2010-02-17 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(6,'2010-02-18 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(7,'2010-02-19 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(8,'2010-02-22 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(9,'2010-02-23 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(10,'2010-02-24 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(11,'2010-02-25 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(12,'2010-02-26 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(13,'2010-03-01 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(14,'2010-03-02 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(15,'2010-03-03 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(16,'2010-03-04 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(17,'2010-03-05 00:00:00',4.00,'demo','Working on that.',0,3,'',1),(18,'2010-04-01 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(19,'2010-04-02 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(20,'2010-04-05 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(21,'2010-04-06 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(22,'2010-04-07 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(23,'2010-04-08 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(24,'2010-04-09 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(25,'2010-04-12 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(26,'2010-04-13 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(27,'2010-04-14 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(28,'2010-04-15 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(29,'2010-04-16 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(30,'2010-04-19 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(31,'2010-04-20 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(32,'2010-04-21 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(33,'2010-04-22 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(34,'2010-04-23 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(35,'2010-04-26 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(36,'2010-04-27 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(37,'2010-04-28 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(38,'2010-04-29 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(39,'2010-04-30 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(40,'2010-05-03 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(41,'2010-05-04 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(42,'2010-05-05 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(43,'2010-05-05 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(44,'2010-05-06 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(45,'2010-05-07 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(46,'2010-05-10 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(47,'2010-05-11 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(48,'2010-05-12 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(49,'2010-05-13 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(50,'2010-05-14 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(51,'2010-05-17 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(52,'2010-05-18 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(53,'2010-05-19 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(54,'2010-05-20 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(55,'2010-05-21 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(56,'2010-05-24 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(57,'2010-05-25 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(58,'2010-04-20 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(59,'2010-04-21 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(60,'2010-04-22 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(61,'2010-04-23 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(62,'2010-04-26 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(63,'2010-04-27 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(64,'2010-04-28 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(65,'2010-04-29 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(66,'2010-04-30 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(67,'2010-05-03 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(68,'2010-05-04 00:00:00',8.00,'demo','Working on that.',0,3,'',1),(69,'2010-05-05 00:00:00',2.00,'demo','Working on that.',0,3,'',1),(70,'2010-05-17 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(71,'2010-05-18 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(72,'2010-05-19 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(73,'2010-05-20 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(74,'2010-05-21 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(75,'2010-05-24 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(76,'2010-05-25 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(77,'2010-05-26 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(78,'2010-05-27 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(79,'2010-05-28 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(80,'2010-05-31 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(81,'2010-06-01 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(82,'2010-06-02 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(83,'2010-06-03 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(84,'2010-06-04 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(85,'2010-06-07 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(86,'2010-06-08 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(87,'2010-06-09 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(88,'2010-06-10 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(89,'2010-06-11 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(90,'2010-06-14 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(91,'2010-06-15 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(92,'2010-06-16 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(93,'2010-06-17 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(94,'2010-06-18 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(95,'2010-06-21 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(96,'2010-06-22 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(97,'2010-06-23 00:00:00',8.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(98,'2010-06-24 00:00:00',6.00,'demo','Working on that. TOO DIFFICULT!',0,3,'',1),(99,'2010-06-04 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(100,'2010-06-07 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(101,'2010-06-08 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(102,'2010-06-09 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(103,'2010-06-10 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(104,'2010-06-11 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(105,'2010-06-14 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(106,'2010-06-15 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(107,'2010-06-16 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(108,'2010-06-17 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(109,'2010-06-18 00:00:00',8.00,'demo','Extra hours on customer',1,3,'',1),(110,'2010-06-21 00:00:00',2.00,'demo','Extra hours on customer',1,3,'',1),(111,'2010-04-10 00:00:00',190.00,'admin','This is crazy !',0,1,'',1),(112,'2010-05-24 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(113,'2010-05-25 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(114,'2010-05-26 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(115,'2010-05-27 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(116,'2010-05-28 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(117,'2010-05-31 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(118,'2010-06-01 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(119,'2010-06-02 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(120,'2010-06-03 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(121,'2010-06-04 00:00:00',8.00,'admin','Working on that.',0,1,'',1),(122,'2010-06-07 00:00:00',8.00,'admin','Working on that.',0,1,'admin',1),(123,'2010-06-08 00:00:00',2.00,'admin','Working on that.',0,1,'admin',1),(124,'2010-02-17 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(125,'2010-02-18 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(126,'2010-02-19 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(127,'2010-02-22 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(128,'2010-02-23 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(129,'2010-02-24 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(130,'2010-02-25 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(131,'2010-02-26 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(132,'2010-03-01 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(133,'2010-03-02 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(134,'2010-03-03 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(135,'2010-03-04 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(136,'2010-03-05 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(137,'2010-03-08 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(138,'2010-03-09 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(139,'2010-03-10 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(140,'2010-03-11 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(141,'2010-03-12 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(142,'2010-03-15 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(143,'2010-03-16 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(144,'2010-03-17 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(145,'2010-03-18 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(146,'2010-03-19 00:00:00',8.00,'admin','Working on that.',0,0,'',1),(147,'2010-03-22 00:00:00',6.00,'admin','Working on that.',0,0,'',1),(148,'2010-03-23 00:00:00',8.00,'admin','',0,0,'',1),(149,'2010-03-24 00:00:00',8.00,'admin','',0,0,'',1),(150,'2010-03-25 00:00:00',8.00,'admin','',0,0,'',1),(151,'2010-03-26 00:00:00',8.00,'admin','',0,0,'',1),(152,'2010-03-29 00:00:00',8.00,'admin','',0,0,'',1),(153,'2010-03-30 00:00:00',8.00,'admin','',0,0,'',1),(154,'2010-03-31 00:00:00',8.00,'admin','',0,0,'',1),(155,'2010-04-01 00:00:00',8.00,'admin','',0,0,'',1),(156,'2010-04-02 00:00:00',8.00,'admin','',0,0,'',1),(157,'2010-04-05 00:00:00',8.00,'admin','',0,0,'',1),(158,'2010-04-06 00:00:00',8.00,'admin','',0,0,'',1),(159,'2010-04-07 00:00:00',2.00,'admin','',0,0,'',1),(160,'2010-08-01 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(161,'2010-08-02 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(162,'2010-08-03 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(163,'2010-08-04 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(164,'2010-08-05 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(165,'2010-08-06 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(166,'2010-08-09 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(167,'2010-08-10 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(168,'2010-08-11 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(169,'2010-08-12 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(170,'2010-08-13 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(171,'2010-08-16 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(172,'2010-08-17 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(173,'2010-08-18 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(174,'2010-08-19 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(175,'2010-08-20 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(176,'2010-08-23 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(177,'2010-08-24 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(178,'2010-08-25 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(179,'2010-08-26 00:00:00',8.00,'admin','Holidays.',0,0,'',1),(180,'2010-06-01 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(181,'2010-06-02 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(182,'2010-06-03 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(183,'2010-06-04 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(184,'2010-06-07 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(185,'2010-06-08 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(186,'2010-06-09 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(187,'2010-06-10 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(188,'2010-06-11 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(189,'2010-06-14 00:00:00',8.00,'demo','Holydays.\r\n',0,0,'',1),(190,'2010-02-17 02:06:49',0.25,'demo','Hi there. I&#039;m alive and running !',0,0,'',1),(191,'2010-02-17 02:07:03',0.25,'admin','Try that, and if not, the other one !',0,0,'',1),(192,'2010-08-01 00:00:00',8.00,'admin','Holydays',0,0,'',1),(193,'2010-08-02 00:00:00',8.00,'admin','Holydays',0,0,'',1),(194,'2010-08-03 00:00:00',8.00,'admin','Holydays',0,0,'',1),(195,'2010-08-04 00:00:00',8.00,'admin','Holydays',0,0,'',1),(196,'2010-08-05 00:00:00',8.00,'admin','Holydays',0,0,'',1),(197,'2010-02-17 02:16:57',0.25,'admin','This rocks !',0,0,'',1);
/*!40000 ALTER TABLE `tworkunit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tworkunit_incident`
--

LOCK TABLES `tworkunit_incident` WRITE;
/*!40000 ALTER TABLE `tworkunit_incident` DISABLE KEYS */;
INSERT INTO `tworkunit_incident` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,2,190),(6,2,191),(7,2,197);
/*!40000 ALTER TABLE `tworkunit_incident` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tworkunit_task`
--

LOCK TABLES `tworkunit_task` WRITE;
/*!40000 ALTER TABLE `tworkunit_task` DISABLE KEYS */;
INSERT INTO `tworkunit_task` VALUES (1,2,5),(2,2,6),(3,2,7),(4,2,8),(5,2,9),(6,2,10),(7,2,11),(8,2,12),(9,2,13),(10,2,14),(11,2,15),(12,2,16),(13,2,17),(14,3,18),(15,3,19),(16,3,20),(17,3,21),(18,3,22),(19,3,23),(20,3,24),(21,3,25),(22,3,26),(23,3,27),(24,3,28),(25,3,29),(26,3,30),(27,3,31),(28,3,32),(29,3,33),(30,3,34),(31,3,35),(32,3,36),(33,3,37),(34,3,38),(35,3,39),(36,3,40),(37,3,41),(38,3,42),(39,6,43),(40,6,44),(41,6,45),(42,6,46),(43,6,47),(44,6,48),(45,6,49),(46,6,50),(47,6,51),(48,6,52),(49,6,53),(50,6,54),(51,6,55),(52,6,56),(53,6,57),(54,5,58),(55,5,59),(56,5,60),(57,5,61),(58,5,62),(59,5,63),(60,5,64),(61,5,65),(62,5,66),(63,5,67),(64,5,68),(65,5,69),(66,4,70),(67,4,71),(68,4,72),(69,4,73),(70,4,74),(71,4,75),(72,4,76),(73,4,77),(74,4,78),(75,4,79),(76,4,80),(77,4,81),(78,4,82),(79,4,83),(80,4,84),(81,4,85),(82,4,86),(83,4,87),(84,4,88),(85,4,89),(86,4,90),(87,4,91),(88,4,92),(89,4,93),(90,4,94),(91,4,95),(92,4,96),(93,4,97),(94,4,98),(95,4,99),(96,4,100),(97,4,101),(98,4,102),(99,4,103),(100,4,104),(101,4,105),(102,4,106),(103,4,107),(104,4,108),(105,4,109),(106,4,110),(107,3,111),(108,3,112),(109,3,113),(110,3,114),(111,3,115),(112,3,116),(113,3,117),(114,3,118),(115,3,119),(116,3,120),(117,3,121),(118,3,122),(119,3,123),(120,1,124),(121,1,125),(122,1,126),(123,1,127),(124,1,128),(125,1,129),(126,1,130),(127,1,131),(128,1,132),(129,1,133),(130,1,134),(131,1,135),(132,1,136),(133,1,137),(134,1,138),(135,1,139),(136,1,140),(137,1,141),(138,1,142),(139,1,143),(140,1,144),(141,1,145),(142,1,146),(143,1,147),(144,2,148),(145,2,149),(146,2,150),(147,2,151),(148,2,152),(149,2,153),(150,2,154),(151,2,155),(152,2,156),(153,2,157),(154,2,158),(155,2,159),(156,-1,160),(157,-1,161),(158,-1,162),(159,-1,163),(160,-1,164),(161,-1,165),(162,-1,166),(163,-1,167),(164,-1,168),(165,-1,169),(166,-1,170),(167,-1,171),(168,-1,172),(169,-1,173),(170,-1,174),(171,-1,175),(172,-1,176),(173,-1,177),(174,-1,178),(175,-1,179),(176,0,180),(177,0,181),(178,0,182),(179,0,183),(180,0,184),(181,0,185),(182,0,186),(183,0,187),(184,0,188),(185,0,189),(186,-1,192),(187,-1,193),(188,-1,194),(189,-1,195),(190,-1,196);
/*!40000 ALTER TABLE `tworkunit_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `um_tupdate`
--

LOCK TABLES `um_tupdate` WRITE;
/*!40000 ALTER TABLE `um_tupdate` DISABLE KEYS */;
/*!40000 ALTER TABLE `um_tupdate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `um_tupdate_journal`
--

LOCK TABLES `um_tupdate_journal` WRITE;
/*!40000 ALTER TABLE `um_tupdate_journal` DISABLE KEYS */;
/*!40000 ALTER TABLE `um_tupdate_journal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `um_tupdate_package`
--

LOCK TABLES `um_tupdate_package` WRITE;
/*!40000 ALTER TABLE `um_tupdate_package` DISABLE KEYS */;
/*!40000 ALTER TABLE `um_tupdate_package` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `um_tupdate_settings`
--

LOCK TABLES `um_tupdate_settings` WRITE;
/*!40000 ALTER TABLE `um_tupdate_settings` DISABLE KEYS */;
INSERT INTO `um_tupdate_settings` VALUES ('current_update','0'),('customer_key','INTEGRIA-FREE'),('dbhost',''),('dbname',''),('dbpass',''),('dbuser',''),('keygen_path','/srv/www/htdocs/integria/include/keygen'),('proxy',''),('proxy_pass',''),('proxy_port',''),('proxy_user',''),('update_server_host','www.artica.es'),('update_server_path','/integriaupdate/server.php'),('update_server_port','80'),('updating_binary_path','Path where the updated binary files will be stored'),('updating_code_path','');
/*!40000 ALTER TABLE `um_tupdate_settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-03-10 20:29:04
