-- MySQL dump 10.13  Distrib 5.5.18, for Linux (i686)
--
-- Host: localhost    Database: integria
-- ------------------------------------------------------
-- Server version	5.5.18

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
-- Dumping data for table `tagenda`
--

LOCK TABLES `tagenda` WRITE;
/*!40000 ALTER TABLE `tagenda` DISABLE KEYS */;
INSERT INTO `tagenda` VALUES (1,'2012-03-15 18:31:00','admin',0,120,1,3,'Dentist!'),(2,'2012-05-01 18:31:00','admin',0,240,2,4,'Take&#x20;my&#x20;wife&#x20;and&#x20;go&#x20;to&#x20;a&#x20;expensive&#x20;restaurant...'),(3,'2012-09-01 18:32:00','admin',0,0,0,4,'Have&#x20;a&#x20;live...&#x20;Holidays&#x20;!'),(4,'2012-10-01 18:32:00','admin',0,0,0,4,'Back&#x20;to&#x20;hell...&#x20;welcome&#x20;to&#x20;the&#x20;office&#x20;!&#x20;:&#40;');
/*!40000 ALTER TABLE `tagenda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tapp`
--

LOCK TABLES `tapp` WRITE;
/*!40000 ALTER TABLE `tapp` DISABLE KEYS */;
/*!40000 ALTER TABLE `tapp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tapp_activity_data`
--

LOCK TABLES `tapp_activity_data` WRITE;
/*!40000 ALTER TABLE `tapp_activity_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `tapp_activity_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tapp_category`
--

LOCK TABLES `tapp_category` WRITE;
/*!40000 ALTER TABLE `tapp_category` DISABLE KEYS */;
INSERT INTO `tapp_category` VALUES (1,'Allowed'),(2,'Forbidden');
/*!40000 ALTER TABLE `tapp_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tapp_default`
--

LOCK TABLES `tapp_default` WRITE;
/*!40000 ALTER TABLE `tapp_default` DISABLE KEYS */;
/*!40000 ALTER TABLE `tapp_default` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tapp_extra_submode`
--

LOCK TABLES `tapp_extra_submode` WRITE;
/*!40000 ALTER TABLE `tapp_extra_submode` DISABLE KEYS */;
/*!40000 ALTER TABLE `tapp_extra_submode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tattachment`
--

LOCK TABLES `tattachment` WRITE;
/*!40000 ALTER TABLE `tattachment` DISABLE KEYS */;
INSERT INTO `tattachment` VALUES (1,1,0,0,'julio','shot0000.jpg','See&#x20;how&#x20;it&#x20;works&#x20;for&#x20;me...&#x20;fast&#x20;!&#x0a;',131537);
/*!40000 ALTER TABLE `tattachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tbuilding`
--

LOCK TABLES `tbuilding` WRITE;
/*!40000 ALTER TABLE `tbuilding` DISABLE KEYS */;
INSERT INTO `tbuilding` VALUES (1,'Main&#x20;Office',''),(2,'Barcelona&#x20;office','');
/*!40000 ALTER TABLE `tbuilding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcompany`
--

LOCK TABLES `tcompany` WRITE;
/*!40000 ALTER TABLE `tcompany` DISABLE KEYS */;
INSERT INTO `tcompany` VALUES (1,'Energy&#x20;Field&#x20;LTD','c/&#x20;Pito&#x20;sereno&#x20;23,&#x0d;&#x0a;23802&#x20;Madrid','3434','',3,4),(2,'Samsung&#x20;LTD','N/A','343434','',1,4);
/*!40000 ALTER TABLE `tcompany` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcompany_activity`
--

LOCK TABLES `tcompany_activity` WRITE;
/*!40000 ALTER TABLE `tcompany_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `tcompany_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcompany_contact`
--

LOCK TABLES `tcompany_contact` WRITE;
/*!40000 ALTER TABLE `tcompany_contact` DISABLE KEYS */;
INSERT INTO `tcompany_contact` VALUES (1,2,'Federico&#x20;Piedra','fedepie@nowhere.com','234234324','34343434','Responsable&#x20;T&eacute;cnico','',0),(2,1,'Martin&#x20;Shu','shum@nothe.com','555347347','555834983','Man&#x20;for&#x20;everything','Warning:&#x20;Bad&#x20;mood&#x20;on&#x20;Tuesdays.',0);
/*!40000 ALTER TABLE `tcompany_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcompany_role`
--

LOCK TABLES `tcompany_role` WRITE;
/*!40000 ALTER TABLE `tcompany_role` DISABLE KEYS */;
INSERT INTO `tcompany_role` VALUES (1,'Vendor',''),(2,'Partner',''),(3,'Customer',''),(4,'Prospect',''),(5,'Other','');
/*!40000 ALTER TABLE `tcompany_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tconfig`
--

LOCK TABLES `tconfig` WRITE;
/*!40000 ALTER TABLE `tconfig` DISABLE KEYS */;
INSERT INTO `tconfig` VALUES (1,'language_code','en'),(2,'block_size','25'),(3,'db_scheme_version','3.0-dev'),(4,'db_scheme_build','ID111231'),(5,'date_format','F j, Y, g:i a'),(6,'currency','eu'),(7,'sitename','Integria IMS - the ITIL Management System'),(8,'hours_perday','8'),(9,'FOOTER_EMAIL','Please do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n'),(10,'HEADER_EMAIL','Hello, \n\nThis is an automated message coming from Integria\n\n'),(11,'notification_period','24'),(12,'limit_size','250'),(13,'api_password',''),(14,'flash_charts','1'),(15,'fontsize','6'),(16,'auth_methods','mysql'),(17,'wiki_plugin_dir','include/wiki/plugins/'),(18,'conf_var_dir','wiki_data/'),(19,'enterprise_installed','1');
/*!40000 ALTER TABLE `tconfig` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tcontract`
--

LOCK TABLES `tcontract` WRITE;
/*!40000 ALTER TABLE `tcontract` DISABLE KEYS */;
INSERT INTO `tcontract` VALUES (2,'Desarrollo&#x20;Movil','','','2012-01-17','2015-01-17',2,1,4);
/*!40000 ALTER TABLE `tcontract` ENABLE KEYS */;
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
INSERT INTO `tdownload` VALUES (1,'Corporate&#x20;Antivirus','attachment/downloads/shot0000.jpg','2012-01-17 18:30:52','','',1,'admin');
/*!40000 ALTER TABLE `tdownload` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tdownload_category`
--

LOCK TABLES `tdownload_category` WRITE;
/*!40000 ALTER TABLE `tdownload_category` DISABLE KEYS */;
INSERT INTO `tdownload_category` VALUES (1,'Tools','babel.png');
/*!40000 ALTER TABLE `tdownload_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tdownload_category_group`
--

LOCK TABLES `tdownload_category_group` WRITE;
/*!40000 ALTER TABLE `tdownload_category_group` DISABLE KEYS */;
INSERT INTO `tdownload_category_group` VALUES (1,4);
/*!40000 ALTER TABLE `tdownload_category_group` ENABLE KEYS */;
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
INSERT INTO `tevent` VALUES (1,'MANUFACTURER CREATED','2012-01-17 18:01:16','admin',1,0,'Samsung'),(2,'COMPANY ROLE CREATED','2012-01-17 18:01:27','admin',1,0,'Vendor'),(3,'COMPANY ROLE CREATED','2012-01-17 18:01:32','admin',2,0,'Partner'),(4,'COMPANY ROLE CREATED','2012-01-17 18:01:37','admin',3,0,'Customer'),(5,'COMPANY ROLE CREATED','2012-01-17 18:01:45','admin',4,0,'Prospect'),(6,'COMPANY ROLE CREATED','2012-01-17 18:01:53','admin',5,0,'Other'),(7,'COMPANY CREATED','2012-01-17 18:02:49','admin',1,0,'Energy&#x20;Field&#x20;LTD'),(8,'COMPANY CREATED','2012-01-17 18:03:01','admin',2,0,'Samsung&#x20;LTD'),(9,'CONTRACT CREATED','2012-01-17 18:03:17','admin',2,0,'Desarrollo&#x20;Movil'),(10,'CONTACT CREATED','2012-01-17 18:03:37','admin',1,0,'Federico&#x20;Piedra'),(11,'BUILDING CREATED','2012-01-17 18:04:08','admin',1,0,'Oficina&#x20;Principal'),(12,'BUILDING CREATED','2012-01-17 18:04:18','admin',1,0,'Barcelona&#x20;office'),(13,'BUILDING','2012-01-17 18:04:24','admin',1,0,'Main&#x20;Office'),(14,'PRODUCT CREATED','2012-01-17 18:04:30','admin',1,0,'Phones'),(15,'PRODUCT UPDATED','2012-01-17 18:04:54','admin',1,0,'Phones'),(16,'PRODUCT CREATED','2012-01-17 18:05:06','admin',2,0,'Computers'),(17,'PROJECT GROUP CREATED','2012-01-17 18:12:08','admin',1,0,'Development'),(18,'PROJECT GROUP CREATED','2012-01-17 18:12:21','admin',2,0,'Marketing'),(19,'KB ITEM CREATED','2012-01-17 18:28:03','admin',1,0,'General&#x20;problems&#x20;with&#x20;Samsung&#x20;LH3483'),(20,'CATEGORY CREATED','2012-01-17 18:28:15','admin',1,0,'Articles'),(21,'CONTACT CREATED','2012-01-17 18:29:15','admin',2,0,'Martin&#x20;Shu'),(22,'DOWNLOAD CATEGORY CREATED','2012-01-17 18:29:25','admin',1,0,'Tools'),(23,'DOWNLOAD ITEM CREATED','2012-01-17 18:30:52','admin',1,0,'Corporate&#x20;Antivirus'),(24,'INSERT CALENDAR EVENT','2012-01-17 18:31:28','admin',0,0,'Dentist!'),(25,'INSERT CALENDAR EVENT','2012-01-17 18:32:01','admin',0,0,'Take&#x20;my&#x20;wife&#x20;and&#x20;go&#x20;to&#x20;a&#x20;expensive&#x20;restaurant...'),(26,'INSERT CALENDAR EVENT','2012-01-17 18:32:27','admin',0,0,'Have&#x20;a&#x20;live...&#x20;Holidays&#x20;!'),(27,'INSERT CALENDAR EVENT','2012-01-17 18:32:49','admin',0,0,'Back&#x20;to&#x20;hell...&#x20;welcome&#x20;to&#x20;the&#x20;office&#x20;!&#x20;:&#40;'),(28,'PWU INSERT','2012-01-17 18:44:51','admin',5,0,'Preparing&#x20;the&#x20;development&#x20;servers.'),(29,'PWU INSERT','2012-01-17 18:49:10','julio',2,0,'Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users'),(30,'PWU INSERT','2012-01-17 20:07:49','admin',1,0,'Testing');
/*!40000 ALTER TABLE `tevent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tgrupo`
--

LOCK TABLES `tgrupo` WRITE;
/*!40000 ALTER TABLE `tgrupo` DISABLE KEYS */;
INSERT INTO `tgrupo` VALUES (1,'All','world.png',NULL,NULL,NULL,0,'admin',NULL,0,0,1,'',0),(3,'Marketing','eye.png','',NULL,NULL,0,'support',2,0,0,1,'',0),(4,'Engineering','computer.png','',NULL,NULL,0,'support',1,0,0,1,'',0);
/*!40000 ALTER TABLE `tgrupo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincidencia`
--

LOCK TABLES `tincidencia` WRITE;
/*!40000 ALTER TABLE `tincidencia` DISABLE KEYS */;
INSERT INTO `tincidencia` VALUES (1,'2012-01-17 18:07:17','0000-00-00 00:00:00','Problems&#x20;in&#x20;the&#x20;server','Server&#x20;response&#x20;too&#x20;slow.&#x20;I&#x20;cannot&#x20;work&#x20;with&#x20;that!','javi',1,3,2,4,'2012-01-17 21:38:06','julio',1,0,0,'',NULL,0,0,0,0,''),(2,'2012-01-17 18:25:03','2012-01-17 18:26:28','Keyboard&#x20;broken','I&#x20;cannot&#x20;type&#x20;nothing&#x20;because&#x20;my&#x20;keyboard&#x20;is&#x20;broken.&#x0a;','support',1,6,2,4,'2012-01-17 18:26:28','julio',1,0,1,'Reconnect&#x20;again&#x20;the&#x20;device&#x20;make&#x20;it&#x20;work.&#x20;&#x0a;',NULL,0,0,0,10,''),(3,'2012-01-17 18:27:01','2012-01-17 22:11:36','My&#x20;mouse&#x20;doesnt&#x20;work','I&#x20;cannot&#x20;click&#x20;with&#x20;right&#x20;button.&#x20;HELP&#x20;!','javi',1,6,2,4,'2012-01-17 22:11:36','julio',1,0,0,'Lo&#x20;pudimos&#x20;arreglar&#x20;haciendo&#x20;XXX.',NULL,0,0,0,0,'');
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
INSERT INTO `tincident_status` VALUES (1,'New'),(2,'Unconfirmed'),(3,'Assigned'),(4,'Re-opened'),(5,'Pending to be closed'),(6,'Resolved'),(7,'Closed');
/*!40000 ALTER TABLE `tincident_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tincident_track`
--

LOCK TABLES `tincident_track` WRITE;
/*!40000 ALTER TABLE `tincident_track` DISABLE KEYS */;
INSERT INTO `tincident_track` VALUES (1,1,10,'2012-01-17 18:07:17','julio',1,'Unknown update'),(2,1,2,'2012-01-17 18:07:35','admin',0,'Workunit added'),(3,1,2,'2012-01-17 18:08:02','julio',0,'Workunit added'),(4,1,3,'2012-01-17 18:09:28','julio',0,'File added'),(5,1,2,'2012-01-17 18:10:58','admin',0,'Workunit added'),(6,1,2,'2012-01-17 18:11:01','julio',0,'Workunit added'),(7,1,10,'2012-01-17 18:25:03','julio',1,'Unknown update'),(8,2,2,'2012-01-17 18:25:20','admin',0,'Workunit added'),(9,2,2,'2012-01-17 18:25:31','julio',0,'Workunit added'),(10,2,2,'2012-01-17 18:25:49','admin',0,'Workunit added'),(11,2,2,'2012-01-17 18:26:00','julio',0,'Workunit added'),(12,2,7,'2012-01-17 18:26:28','admin',6,'Status changed -> Resolved'),(13,2,8,'2012-01-17 18:26:28','admin',0,'Resolution changed -> '),(14,2,1,'2012-01-17 18:26:28','admin',0,'Updated'),(15,1,10,'2012-01-17 18:27:01','julio',1,'Unknown update'),(16,1,17,'2012-01-17 21:38:06','admin',0,'Assigned user changed -> Javier&#x20;Nadie'),(17,1,1,'2012-01-17 21:38:06','admin',0,'Updated'),(18,3,2,'2012-01-17 21:44:23','admin',0,'Workunit added'),(19,3,2,'2012-01-17 21:47:03','luis',0,'Workunit added'),(20,3,17,'2012-01-17 21:48:33','luis',0,'Assigned user changed -> '),(21,3,1,'2012-01-17 21:48:33','luis',0,'Updated'),(22,3,17,'2012-01-17 21:51:15','luis',0,'Assigned user changed -> Javier&#x20;Nadie'),(23,3,1,'2012-01-17 21:51:16','luis',0,'Updated'),(24,3,1,'2012-01-17 21:51:16','luis',0,'Updated'),(25,3,7,'2012-01-17 22:11:36','javi',6,'Status changed -> Resolved'),(26,3,1,'2012-01-17 22:11:36','javi',0,'Updated'),(27,3,1,'2012-01-17 22:11:36','javi',0,'Updated');
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
INSERT INTO `tinventory` VALUES (1,'Servidor&#x20;NEC&#x20;2300','Servidor&#x20;de&#x20;desarrollo.','SN7348734b','NA/S9347374H',NULL,0,5000.000,'192.168.70.200',2,2,1,0,2,0,'','',NULL,NULL,'','','',NULL),(2,'Samsung&#x20;XJ34&#x20;-&#x20;Pruebas','','','',NULL,0,0.000,'',2,0,1,0,0,0,'','',NULL,NULL,'','','',NULL);
/*!40000 ALTER TABLE `tinventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tinventory_contact`
--

LOCK TABLES `tinventory_contact` WRITE;
/*!40000 ALTER TABLE `tinventory_contact` DISABLE KEYS */;
INSERT INTO `tinventory_contact` VALUES (2,1);
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
-- Dumping data for table `tinvoice`
--

LOCK TABLES `tinvoice` WRITE;
/*!40000 ALTER TABLE `tinvoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `tinvoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tkb_category`
--

LOCK TABLES `tkb_category` WRITE;
/*!40000 ALTER TABLE `tkb_category` DISABLE KEYS */;
INSERT INTO `tkb_category` VALUES (1,'Articles','','dialog-information.png',0);
/*!40000 ALTER TABLE `tkb_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tkb_data`
--

LOCK TABLES `tkb_data` WRITE;
/*!40000 ALTER TABLE `tkb_data` DISABLE KEYS */;
INSERT INTO `tkb_data` VALUES (1,'General&#x20;problems&#x20;with&#x20;Samsung&#x20;LH3483','Integria&#x20;IMS&#x20;es&#x20;una&#x20;aplicaci&oacute;n&#x20;PHP&#x20;que&#x20;necesita&#x20;una&#x20;base&#x20;de&#x20;datos&#x20;MySQL&#x20;para&#x20;funcionar.&#x20;Actualmente&#x20;s&oacute;lo&#x20;soporta&#x20;MySQL&#x20;y&#x20;necesita&#x20;una&#x20;versi&oacute;n&#x20;de&#x20;PHP&#x20;5.2&#x20;o&#x20;superior&#x20;&#40;debido&#x20;a&#x20;su&#x20;uso&#x20;intensivo&#x20;de&#x20;AJAX,&#x20;entre&#x20;otras&#x20;razones&#41;.&#x20;Debido&#x20;a&#x20;que&#x20;es&#x20;una&#x20;aplicaci&oacute;n&#x20;completamente&#x20;WEB,&#x20;puede&#x20;instalarla&#x20;en&#x20;un&#x20;servidor&#x20;y&#x20;acceder&#x20;a&#x20;ella&#x20;desde&#x20;cualquier&#x20;puesto&#x20;de&#x20;trabajo&#x20;con&#x20;un&#x20;navegador&#x20;moderno&#x20;&#40;Firefox,&#x20;o&#x20;Internet&#x20;Explorer&#x20;a&#x20;partir&#x20;de&#x20;la&#x20;versi&oacute;n&#x20;6&#41;.&#x20;La&#x20;resolution&#x20;minima&#x20;de&#x20;trabajo&#x20;es&#x20;800x600&#x20;aunque&#x20;se&#x20;recomienda&#x20;1024x768.&#x0d;&#x0a;&#x0d;&#x0a;Las&#x20;gr&aacute;ficas&#x20;utilizan&#x20;Flash&#x20;y&#x20;puede&#x20;que&#x20;necesite&#x20;un&#x20;complemento&#x20;para&#x20;visualizarlas.&#x20;Integria&#x20;IMS&#x20;puede&#x20;funcionar&#x20;sobre&#x20;sistemas&#x20;Windows,&#x20;Unix&#x20;o&#x20;GNU/Linux&#x20;mientras&#x20;tenga&#x20;satisfechas&#x20;sus&#x20;dependencias.&#x20;No&#x20;obstante,&#x20;nuestra&#x20;plataforma&#x20;favorita&#x20;es&#x20;SUSE&#x20;Linux&#x20;&oacute;&#x20;Ubuntu&#x20;Server&#x20;Linux.&#x0d;&#x0a;&#x0d;&#x0a;Todas&#x20;las&#x20;dependencias&#x20;necesarias&#x20;que&#x20;ha&#x20;de&#x20;tener&#x20;nuestro&#x20;sistema&#x20;para&#x20;el&#x20;correcto&#x20;funcionamiento&#x20;de&#x20;Integria&#x20;IMS&#x20;son:&#x0d;&#x0a;&#x0d;&#x0a;php5&#x20;php5-mysql&#x20;mysql-server&#x20;php5-gd&#x20;php5-mbstring&#x20;php5-ldap&#x20;php5-gettext&#x20;php5-mcrypt&#x20;curl&#x20;graphviz&#x0d;&#x0a;&#x0d;&#x0a;Opcionalmente,&#x20;y&#x20;si&#x20;se&#x20;desea&#x20;dibujar&#x20;los&#x20;gr&aacute;ficos&#x20;en&#x20;&aacute;rbol&#x20;que&#x20;genera&#x20;Integria,&#x20;a&#x20;parte&#x20;de&#x20;instalar&#x20;&#039;graphviz&#039;,&#x20;ser&aacute;&#x20;necesario&#x20;instalar&#x20;el&#x20;paquete&#x20;&#039;imap&#039;&#x20;en&#x20;Apache&#x20;y&#x20;activarlo.&#x20;Para&#x20;ello&#x20;instalaremos&#x20;el&#x20;paquete:&#x0d;&#x0a;&#x0d;&#x0a;php5-imap&#x0d;&#x0a;&#x0d;&#x0a;Y&#x20;configuraremos&#x20;Apache&#x20;de&#x20;esta&#x20;forma&#x20;&#40;ser&aacute;&#x20;necesario&#x20;reiniciar&#x20;el&#x20;servidor&#x20;de&#x20;apache&#x20;una&#x20;vez&#x20;hecho&#x20;los&#x20;cambios&#41;:&#x0d;&#x0a;&#x0d;&#x0a;/etc/apache2/mods-enabled&#x20;#&#x20;ln&#x20;-s&#x20;../mods-available/imagemap.load&#x0d;&#x0a;#&#x20;echo&#x20;&quot;AddHandler&#x20;imap-file&#x20;map&quot;&#x20;&gt;&#x20;/etc/apache2/mods-enabled/imagemap.conf&#x0d;&#x0a;&#x0d;&#x0a;La&#x20;instalaci&oacute;n&#x20;la&#x20;podremos&#x20;hacer&#x20;de&#x20;forma&#x20;manual&#x20;a&#x20;trav&eacute;s&#x20;de&#x20;las&#x20;fuentes&#x20;&#40;.tar.gz&#41;,&#x20;mediante&#x20;paquetes&#x20;.DEB&#x20;para&#x20;sistemas&#x20;basados&#x20;en&#x20;Debian&#x20;o&#x20;.RPM&#x20;para&#x20;Suse,&#x20;Red&#x20;Hat&#x20;Enterprise&#x20;Linux,&#x20;Fedora&#x20;y&#x20;CentOS,&#x20;o&#x20;bien&#x20;descarg&aacute;ndonos&#x20;el&#x20;c&oacute;digo&#x20;de&#x20;la&#x20;versi&oacute;n&#x20;de&#x20;desarrollo,&#x20;utilizando&#x20;el&#x20;SVN&#x20;&#40;Subversi&oacute;n&#41;.&#x0d;&#x0a;&#x0d;&#x0a;Integria&#x20;IMS&#x20;se&#x20;puede&#x20;instalar&#x20;tambi&eacute;n&#x20;sobre&#x20;sistemas&#x20;operativos&#x20;Microsoft&#x20;Windows,&#x20;mediante&#x20;el&#x20;paquete&#x20;WAMP&#x20;Server[1],&#x20;que&#x20;instala&#x20;Apache.&#x20;PHP&#x20;5&#x20;y&#x20;MySQL&#x20;sobre&#x20;el&#x20;sistema&#x20;operativo.&#x20;','2012-01-17 18:28:02','es','admin',1,1);
/*!40000 ALTER TABLE `tkb_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tkb_product`
--

LOCK TABLES `tkb_product` WRITE;
/*!40000 ALTER TABLE `tkb_product` DISABLE KEYS */;
INSERT INTO `tkb_product` VALUES (1,'Phones','','battery.png',0),(2,'Computers','','computer.png',0);
/*!40000 ALTER TABLE `tkb_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tlanguage`
--

LOCK TABLES `tlanguage` WRITE;
/*!40000 ALTER TABLE `tlanguage` DISABLE KEYS */;
INSERT INTO `tlanguage` VALUES ('bn','à¦¬à¦¾à¦‚à¦²à¦¾'),('da','Dansk'),('el','Î•Î»Î»Î·Î½Î¹ÎºÎ¬'),('en','English'),('es','EspaÃ±ol'),('fr','FranÃ§ais'),('ko','í•œêµ­ì–´'),('nl','Nederlands'),('ru','Ð ÑƒÑÑÐºÐ¸Ð¹'),('tr','TÃ¼rkÃ§e');
/*!40000 ALTER TABLE `tlanguage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tlink`
--

LOCK TABLES `tlink` WRITE;
/*!40000 ALTER TABLE `tlink` DISABLE KEYS */;
INSERT INTO `tlink` VALUES (0000000001,'Integria Project','http://integria.sourceforge.net'),(0000000002,'Artica ST','http://www.artica.es'),(0000000003,'Report a bug','https://sourceforge.net/tracker/?func=add&group_id=193754&atid=946680');
/*!40000 ALTER TABLE `tlink` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tmanufacturer`
--

LOCK TABLES `tmanufacturer` WRITE;
/*!40000 ALTER TABLE `tmanufacturer` DISABLE KEYS */;
INSERT INTO `tmanufacturer` VALUES (1,'Samsung','','',1,0);
/*!40000 ALTER TABLE `tmanufacturer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tmenu_visibility`
--

LOCK TABLES `tmenu_visibility` WRITE;
/*!40000 ALTER TABLE `tmenu_visibility` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmenu_visibility` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tmilestone`
--

LOCK TABLES `tmilestone` WRITE;
/*!40000 ALTER TABLE `tmilestone` DISABLE KEYS */;
INSERT INTO `tmilestone` VALUES (1,1,'2012-05-31 00:00:00','First&#x20;PoC',''),(2,1,'2012-06-01 00:00:00','First&#x20;Real&#x20;Scenario&#x20;Test',''),(3,1,'2012-07-16 00:00:00','Final&#x20;test','');
/*!40000 ALTER TABLE `tmilestone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tnewsboard`
--

LOCK TABLES `tnewsboard` WRITE;
/*!40000 ALTER TABLE `tnewsboard` DISABLE KEYS */;
/*!40000 ALTER TABLE `tnewsboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tpending_mail`
--

LOCK TABLES `tpending_mail` WRITE;
/*!40000 ALTER TABLE `tpending_mail` DISABLE KEYS */;
INSERT INTO `tpending_mail` VALUES (1,'2012-01-17 18:07:17',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] NEW incident #1 Problems in the server. [TicketID#1/6c5de/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nA NEW incident has been created: #1 Problems in the server\n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n   ID          : #1 - Problems in the server\n   CREATED ON  : 2012-01-17 18:07:17\n   GROUP       : Engineering\n   AUTHOR      : Julio Marin\n   ASSIGNED TO : Default Admin\n   PRIORITY    : Medium\n===================================================\n\nServer response too slow. I cannot work with that!\n\n---------------------------------------------------------------------\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(2,'2012-01-17 18:07:17',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] NEW incident #1 Problems in the server. [TicketID#1/7889d/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nA NEW incident has been created: #1 Problems in the server\n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n   ID          : #1 - Problems in the server\n   CREATED ON  : 2012-01-17 18:07:17\n   GROUP       : Engineering\n   AUTHOR      : Julio Marin\n   ASSIGNED TO : Default Admin\n   PRIORITY    : Medium\n===================================================\n\nServer response too slow. I cannot work with that!\n\n---------------------------------------------------------------------\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(3,'2012-01-17 18:07:36',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #1 Problems in the server has a new WORKUNIT. [TicketID#1/6c5de/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #1 ((Problems in the server)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n ID          : #1 - Problems in the server\n CREATED ON  : 2012-01-17 18:07:17\n LAST UPDATE : 2012-01-17 18:07:17\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : New\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nServer response too slow. I cannot work with that!\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\nHave you tried to turn it off and on ?\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(4,'2012-01-17 18:07:36',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #1 Problems in the server has a new WORKUNIT. [TicketID#1/7889d/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #1 ((Problems in the server)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n ID          : #1 - Problems in the server\n CREATED ON  : 2012-01-17 18:07:17\n LAST UPDATE : 2012-01-17 18:07:17\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : New\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nServer response too slow. I cannot work with that!\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\nHave you tried to turn it off and on ?\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(5,'2012-01-17 18:08:02',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #1 Problems in the server has a new WORKUNIT. [TicketID#1/6c5de/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #1 ((Problems in the server)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n ID          : #1 - Problems in the server\n CREATED ON  : 2012-01-17 18:07:17\n LAST UPDATE : 2012-01-17 18:07:35\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nServer response too slow. I cannot work with that!\n\n===================================================\nWORKUNIT added by : Julio Marin\n===================================================\n\nI also see IT Crowd... but not, a restart doesn\'t solve the problem, please ADVICE:\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(6,'2012-01-17 18:08:02',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #1 Problems in the server has a new WORKUNIT. [TicketID#1/7889d/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #1 ((Problems in the server)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n ID          : #1 - Problems in the server\n CREATED ON  : 2012-01-17 18:07:17\n LAST UPDATE : 2012-01-17 18:07:35\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nServer response too slow. I cannot work with that!\n\n===================================================\nWORKUNIT added by : Julio Marin\n===================================================\n\nI also see IT Crowd... but not, a restart doesn\'t solve the problem, please ADVICE:\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(7,'2012-01-17 18:10:58',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #1 Problems in the server has a new WORKUNIT. [TicketID#1/6c5de/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #1 ((Problems in the server)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n ID          : #1 - Problems in the server\n CREATED ON  : 2012-01-17 18:07:17\n LAST UPDATE : 2012-01-17 18:08:02\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nServer response too slow. I cannot work with that!\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\nGive me a few hours to check it out.\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(8,'2012-01-17 18:10:58',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #1 Problems in the server has a new WORKUNIT. [TicketID#1/7889d/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #1 ((Problems in the server)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n ID          : #1 - Problems in the server\n CREATED ON  : 2012-01-17 18:07:17\n LAST UPDATE : 2012-01-17 18:08:02\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nServer response too slow. I cannot work with that!\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\nGive me a few hours to check it out.\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(9,'2012-01-17 18:11:02',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #1 Problems in the server has a new WORKUNIT. [TicketID#1/6c5de/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #1 ((Problems in the server)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n ID          : #1 - Problems in the server\n CREATED ON  : 2012-01-17 18:07:17\n LAST UPDATE : 2012-01-17 18:10:58\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nServer response too slow. I cannot work with that!\n\n===================================================\nWORKUNIT added by : Julio Marin\n===================================================\n\nThx\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(10,'2012-01-17 18:11:02',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #1 Problems in the server has a new WORKUNIT. [TicketID#1/7889d/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #1 ((Problems in the server)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=1\n\n===================================================\n ID          : #1 - Problems in the server\n CREATED ON  : 2012-01-17 18:07:17\n LAST UPDATE : 2012-01-17 18:10:58\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nServer response too slow. I cannot work with that!\n\n===================================================\nWORKUNIT added by : Julio Marin\n===================================================\n\nThx\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(11,'2012-01-17 18:25:03',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] NEW incident #2 Keyboard broken. [TicketID#2/063ab/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nA NEW incident has been created: #2 Keyboard broken\n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n   ID          : #2 - Keyboard broken\n   CREATED ON  : 2012-01-17 18:25:03\n   GROUP       : Engineering\n   AUTHOR      : Julio Marin\n   ASSIGNED TO : Default Admin\n   PRIORITY    : Medium\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n---------------------------------------------------------------------\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(12,'2012-01-17 18:25:03',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] NEW incident #2 Keyboard broken. [TicketID#2/34624/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nA NEW incident has been created: #2 Keyboard broken\n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n   ID          : #2 - Keyboard broken\n   CREATED ON  : 2012-01-17 18:25:03\n   GROUP       : Engineering\n   AUTHOR      : Julio Marin\n   ASSIGNED TO : Default Admin\n   PRIORITY    : Medium\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n---------------------------------------------------------------------\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(13,'2012-01-17 18:25:20',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #2 Keyboard broken has a new WORKUNIT. [TicketID#2/063ab/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #2 ((Keyboard broken)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n ID          : #2 - Keyboard broken\n CREATED ON  : 2012-01-17 18:25:03\n LAST UPDATE : 2012-01-17 18:25:03\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : New\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\nI how its possible you type that ?\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(14,'2012-01-17 18:25:20',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #2 Keyboard broken has a new WORKUNIT. [TicketID#2/34624/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #2 ((Keyboard broken)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n ID          : #2 - Keyboard broken\n CREATED ON  : 2012-01-17 18:25:03\n LAST UPDATE : 2012-01-17 18:25:03\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : New\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\nI how its possible you type that ?\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(15,'2012-01-17 18:25:31',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #2 Keyboard broken has a new WORKUNIT. [TicketID#2/063ab/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #2 ((Keyboard broken)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n ID          : #2 - Keyboard broken\n CREATED ON  : 2012-01-17 18:25:03\n LAST UPDATE : 2012-01-17 18:25:20\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n===================================================\nWORKUNIT added by : Julio Marin\n===================================================\n\nFrom another system... stupid! \n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(16,'2012-01-17 18:25:31',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #2 Keyboard broken has a new WORKUNIT. [TicketID#2/34624/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #2 ((Keyboard broken)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n ID          : #2 - Keyboard broken\n CREATED ON  : 2012-01-17 18:25:03\n LAST UPDATE : 2012-01-17 18:25:20\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n===================================================\nWORKUNIT added by : Julio Marin\n===================================================\n\nFrom another system... stupid! \n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(17,'2012-01-17 18:25:49',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #2 Keyboard broken has a new WORKUNIT. [TicketID#2/063ab/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #2 ((Keyboard broken)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n ID          : #2 - Keyboard broken\n CREATED ON  : 2012-01-17 18:25:03\n LAST UPDATE : 2012-01-17 18:25:31\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\nHave you tried to plug off and plugin in again the keyboard ?\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(18,'2012-01-17 18:25:49',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #2 Keyboard broken has a new WORKUNIT. [TicketID#2/34624/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #2 ((Keyboard broken)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n ID          : #2 - Keyboard broken\n CREATED ON  : 2012-01-17 18:25:03\n LAST UPDATE : 2012-01-17 18:25:31\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\nHave you tried to plug off and plugin in again the keyboard ?\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(19,'2012-01-17 18:26:00',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #2 Keyboard broken has a new WORKUNIT. [TicketID#2/063ab/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #2 ((Keyboard broken)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n ID          : #2 - Keyboard broken\n CREATED ON  : 2012-01-17 18:25:03\n LAST UPDATE : 2012-01-17 18:25:49\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n===================================================\nWORKUNIT added by : Julio Marin\n===================================================\n\nOMG... amazing :-))) it works !\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(20,'2012-01-17 18:26:00',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #2 Keyboard broken has a new WORKUNIT. [TicketID#2/34624/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #2 ((Keyboard broken)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=2\n\n===================================================\n ID          : #2 - Keyboard broken\n CREATED ON  : 2012-01-17 18:25:03\n LAST UPDATE : 2012-01-17 18:25:49\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot type nothing because my keyboard is broken.\r\n\n\n===================================================\nWORKUNIT added by : Julio Marin\n===================================================\n\nOMG... amazing :-))) it works !\n\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(21,'2012-01-17 18:26:28',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] CLOSED incident #2 Keyboard broken.\n [TicketID#2/063ab/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(22,'2012-01-17 18:26:28',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] CLOSED incident #2 Keyboard broken.\n [TicketID#2/34624/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(23,'2012-01-17 18:27:01',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] NEW incident #3 My mouse doesnt work. [TicketID#3/dac31/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nA NEW incident has been created: #3 My mouse doesnt work\n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=3\n\n===================================================\n   ID          : #3 - My mouse doesnt work\n   CREATED ON  : 2012-01-17 18:27:01\n   GROUP       : Engineering\n   AUTHOR      : Julio Marin\n   ASSIGNED TO : Default Admin\n   PRIORITY    : Medium\n===================================================\n\nI cannot click with right button. HELP !\n\n---------------------------------------------------------------------\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(24,'2012-01-17 18:27:01',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] NEW incident #3 My mouse doesnt work. [TicketID#3/a94d0/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nA NEW incident has been created: #3 My mouse doesnt work\n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=3\n\n===================================================\n   ID          : #3 - My mouse doesnt work\n   CREATED ON  : 2012-01-17 18:27:01\n   GROUP       : Engineering\n   AUTHOR      : Julio Marin\n   ASSIGNED TO : Default Admin\n   PRIORITY    : Medium\n===================================================\n\nI cannot click with right button. HELP !\n\n---------------------------------------------------------------------\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(25,'2012-01-17 18:31:28',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] New calendar event','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nHello, \n\nThis is an automated message coming from Integria\n\nA new entry in calendar has been created by user admin (Default Admin)\n\n\n		Date and time: 2012-03-15 18:31\n\n		Description  : Dentist!\n\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n','Array'),(26,'2012-01-17 18:32:01',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] New calendar event','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nHello, \n\nThis is an automated message coming from Integria\n\nA new entry in calendar has been created by user admin (Default Admin)\n\n\n		Date and time: 2012-05-01 18:31\n\n		Description  : Take my wife and go to a expensive restaurant...\n\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n','Array'),(27,'2012-01-17 18:32:27',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] New calendar event','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nHello, \n\nThis is an automated message coming from Integria\n\nA new entry in calendar has been created by user admin (Default Admin)\n\n\n		Date and time: 2012-09-01 18:32\n\n		Description  : Have a live... Holidays !\n\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n','Array'),(28,'2012-01-17 18:32:49',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] New calendar event','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nHello, \n\nThis is an automated message coming from Integria\n\nA new entry in calendar has been created by user admin (Default Admin)\n\n\n		Date and time: 2012-10-01 18:32\n\n		Description  : Back to hell... welcome to the office ! :(\n\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n','Array'),(29,'2012-01-17 18:44:51',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Task \"Pre-Development\" has a new work report from Default Admin \n','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nThis is part of a multi-workunit assigment of 40 hours\n\nTask Pre-Development of project Super Waporware 1.0 has been updated by user Default Admin and a new workunit has been added to history. You could track this workunit in the following URL (need to use your credentials):\n\n	http://localhost/integria/index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=1&id_task=5\n\n============================================================\n    TASK        : Pre-Development \n    DATE        : 2012-01-23 00:00:00\n    REPORTED by : Default Admin\n    TIME USED   : 8.00 \n============================================================\n\nPreparing the development servers.\n\n============================================================\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(30,'2012-01-17 18:49:10',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Task \"re-Requisites recollecting.\" has a new work report from Julio Marin \n','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nThis is part of a multi-workunit assigment of 80 hours\n\nTask re-Requisites recollecting. of project Super Waporware 1.0 has been updated by user Julio Marin and a new workunit has been added to history. You could track this workunit in the following URL (need to use your credentials):\n\n	http://localhost/integria/index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=1&id_task=2\n\n============================================================\n    TASK        : re-Requisites recollecting. \n    DATE        : 2012-01-12 00:00:00\n    REPORTED by : Julio Marin\n    TIME USED   : 8.00 \n============================================================\n\nInterview with the customer and other power-users\n\n============================================================\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(31,'2012-01-17 20:07:49',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Task \"Planning\" has a new work report from Default Admin \n','Hello, \n\nThis is an automated message coming from Integria\n\n\r\n\n\nTask Planning of project Super Waporware 1.0 has been updated by user Default Admin and a new workunit has been added to history. You could track this workunit in the following URL (need to use your credentials):\n\n	http://localhost/integria/index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=1&id_task=1\n\n============================================================\n    TASK        : Planning \n    DATE        : 2012-01-17 00:00:00\n    REPORTED by : Default Admin\n    TIME USED   : 4.00 \n============================================================\n\nTesting\n\n============================================================\n\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(32,'2012-01-17 21:44:23',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #3 My mouse doesnt work has a new WORKUNIT. [TicketID#3/a94d0/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #3 ((My mouse doesnt work)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=3\n\n===================================================\n ID          : #3 - My mouse doesnt work\n CREATED ON  : 2012-01-17 18:27:01\n LAST UPDATE : 2012-01-17 18:27:01\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : -\n PRIORITY    : Medium\n STATUS      : New\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot click with right button. HELP !\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\n\"Veo lo que me cuentas y voy a intentar reproducirlo para determinar el origen del problema\".\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(33,'2012-01-17 21:44:23',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #3 My mouse doesnt work has a new WORKUNIT. [TicketID#3/dac31/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #3 ((My mouse doesnt work)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=3\n\n===================================================\n ID          : #3 - My mouse doesnt work\n CREATED ON  : 2012-01-17 18:27:01\n LAST UPDATE : 2012-01-17 18:27:01\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : -\n PRIORITY    : Medium\n STATUS      : New\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot click with right button. HELP !\n\n===================================================\nWORKUNIT added by : Default Admin\n===================================================\n\n\"Veo lo que me cuentas y voy a intentar reproducirlo para determinar el origen del problema\".\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(34,'2012-01-17 21:47:03',0,0,'admin@integria.sf.net','[Integria IMS - the ITIL Management System] Incident #3 My mouse doesnt work has a new WORKUNIT. [TicketID#3/dac31/admin]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #3 ((My mouse doesnt work)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=3\n\n===================================================\n ID          : #3 - My mouse doesnt work\n CREATED ON  : 2012-01-17 18:27:01\n LAST UPDATE : 2012-01-17 21:44:23\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot click with right button. HELP !\n\n===================================================\nWORKUNIT added by : \n===================================================\n\nEstamos mirandolo, el problema parece complicado.\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(35,'2012-01-17 21:47:03',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] Incident #3 My mouse doesnt work has a new WORKUNIT. [TicketID#3/a94d0/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\nIncident #3 ((My mouse doesnt work)) has been UPDATED.  \n\nYou can reply to this mail to add a Workunit to this incident. You also could track this incident in the following URL (need to use your credentials):\n\n   http://localhost/integria/index.php?sec=incidents&sec2=operation/incidents/incident&id=3\n\n===================================================\n ID          : #3 - My mouse doesnt work\n CREATED ON  : 2012-01-17 18:27:01\n LAST UPDATE : 2012-01-17 21:44:23\n GROUP       : Engineering\n AUTHOR      : Julio Marin\n ASSIGNED TO : Default Admin\n PRIORITY    : Medium\n STATUS      : Assigned\n RESOLUTION  : None\n TIME USED   : 0.25\n===================================================\n\nI cannot click with right button. HELP !\n\n===================================================\nWORKUNIT added by : \n===================================================\n\nEstamos mirandolo, el problema parece complicado.\n\n===================================================\n		\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(36,'2012-01-17 22:11:36',0,0,'','[Integria IMS - the ITIL Management System] CLOSED incident #3 My mouse doesnt work.\n [TicketID#3/dda71/javi]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(37,'2012-01-17 22:11:36',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] CLOSED incident #3 My mouse doesnt work.\n [TicketID#3/a94d0/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(38,'2012-01-17 22:11:36',0,0,'','[Integria IMS - the ITIL Management System] CLOSED incident #3 My mouse doesnt work.\n [TicketID#3/dda71/javi]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n',''),(39,'2012-01-17 22:11:36',0,0,'julio@marinnet.com','[Integria IMS - the ITIL Management System] CLOSED incident #3 My mouse doesnt work.\n [TicketID#3/a94d0/julio]','Hello, \n\nThis is an automated message coming from Integria\n\n\r\n\r\nPlease do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n','');
/*!40000 ALTER TABLE `tpending_mail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tprofile`
--

LOCK TABLES `tprofile` WRITE;
/*!40000 ALTER TABLE `tprofile` DISABLE KEYS */;
INSERT INTO `tprofile` VALUES (1,'Project&#x20;Participant',1,1,0,0,0,0,1,1,0,1,0,0,1,0,1,0,0,1,0,0,1,1,0),(2,'Project Manager',1,1,1,0,0,0,1,1,1,1,1,1,1,1,1,1,0,1,0,0,1,1,1),(3,'Incident Manager',1,1,1,0,0,0,1,1,0,0,0,0,0,0,1,1,0,1,1,1,1,0,0),(4,'Incident&#x20;Operator',1,1,0,0,0,0,1,1,0,0,0,0,0,0,1,0,0,0,0,0,1,0,0),(5,'Global Manager',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
/*!40000 ALTER TABLE `tprofile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tproject`
--

LOCK TABLES `tproject` WRITE;
/*!40000 ALTER TABLE `tproject` DISABLE KEYS */;
INSERT INTO `tproject` VALUES (-1,'Non imputable hours (Special)','','0000-00-00','0000-00-00','',1,0),(1,'Super&#x20;Waporware&#x20;1.0','This&#x20;will&#x20;be&#x20;future&#x20;of&#x20;IT&#x20;management.','2012-01-01','2012-08-31','admin',0,1);
/*!40000 ALTER TABLE `tproject` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tproject_group`
--

LOCK TABLES `tproject_group` WRITE;
/*!40000 ALTER TABLE `tproject_group` DISABLE KEYS */;
INSERT INTO `tproject_group` VALUES (1,'Development','preferences-system.png'),(2,'Marketing','applications-office.png');
/*!40000 ALTER TABLE `tproject_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tproject_track`
--

LOCK TABLES `tproject_track` WRITE;
/*!40000 ALTER TABLE `tproject_track` DISABLE KEYS */;
INSERT INTO `tproject_track` VALUES (1,1,'admin',21,'2012-01-17 18:12:55',0),(2,1,'admin',26,'2012-01-17 18:18:48',0),(3,1,'admin',26,'2012-01-17 18:19:09',0),(4,1,'admin',26,'2012-01-17 18:19:29',0),(5,1,'admin',26,'2012-01-17 18:19:44',0),(6,1,'admin',26,'2012-01-17 18:20:06',0),(7,1,'admin',26,'2012-01-17 18:20:24',0),(8,1,'admin',26,'2012-01-17 18:20:42',0),(9,1,'admin',26,'2012-01-17 18:20:59',0);
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
INSERT INTO `trole_people_project` VALUES (1,'admin',1,1),(2,'julio',3,1);
/*!40000 ALTER TABLE `trole_people_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `trole_people_task`
--

LOCK TABLES `trole_people_task` WRITE;
/*!40000 ALTER TABLE `trole_people_task` DISABLE KEYS */;
INSERT INTO `trole_people_task` VALUES (1,'admin',1,1),(2,'julio',3,1),(3,'admin',1,2),(4,'julio',3,2),(5,'admin',1,3),(6,'julio',3,3),(7,'admin',1,4),(8,'julio',3,4),(9,'admin',1,5),(10,'julio',3,5),(11,'admin',1,6),(12,'julio',3,6),(13,'admin',1,7),(14,'julio',3,7),(15,'admin',1,8),(16,'julio',3,8);
/*!40000 ALTER TABLE `trole_people_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tsesion`
--

LOCK TABLES `tsesion` WRITE;
/*!40000 ALTER TABLE `tsesion` DISABLE KEYS */;
INSERT INTO `tsesion` VALUES (1,'admin','127.0.0.1','Logon','Logged in','','2012-01-17 17:58:36',1326819516),(2,'julio','127.0.0.1','Logon','Logged in','','2012-01-17 17:59:23',1326819563),(3,'julio','127.0.0.1','ACL Violation','Trying to access inventory search','','2012-01-17 18:00:15',1326819615),(4,'admin','127.0.0.1','SLA Created','Created a new SLA (SLA&#x20;base)','INSERT INTO tsla (`name`, `description`, id_sla_base,\n		min_response, max_response, max_incidents, `enforced`, five_daysonly, time_from, time_to)\n		VALUE (\"SLA&#x20;base\", \"\", 0, 2, 480, 10, 1, 1, 8, 18)','2012-01-17 18:01:07',1326819667),(5,'julio','127.0.0.1','ACL Violation','Trying to access inventory search','','2012-01-17 18:06:58',1326820018),(6,'julio','127.0.0.1','Incident updated','Unknown update','','2012-01-17 18:07:17',1326820037),(7,'julio','127.0.0.1','Incident created','User julio created incident #1','','2012-01-17 18:07:17',1326820037),(8,'admin','127.0.0.1','Incident updated','Workunit added','','2012-01-17 18:07:35',1326820055),(9,'julio','127.0.0.1','Incident updated','Workunit added','','2012-01-17 18:08:02',1326820082),(10,'julio','127.0.0.1','Incident updated','File added','','2012-01-17 18:09:28',1326820168),(11,'admin','127.0.0.1','Incident updated','Workunit added','','2012-01-17 18:10:58',1326820258),(12,'julio','127.0.0.1','Incident updated','Workunit added','','2012-01-17 18:11:01',1326820261),(13,'admin','','Project created','User admin created project \'Super&#x20;Waporware&#x20;1.0\'','','2012-01-17 18:12:55',1326820375),(14,'admin','','Project #1 tracking updated','State #21','','2012-01-17 18:12:55',1326820375),(15,'admin','127.0.0.1','User/Role added to project','User julio added to project Super&#x20;Waporware&#x20;1.0','','2012-01-17 18:13:12',1326820392),(16,'admin','127.0.0.1','Task added to project','Task \'Planning\' added to project \'1\'','','2012-01-17 18:18:48',1326820728),(17,'admin','','Task #1 tracking updated','State #11','','2012-01-17 18:18:48',1326820728),(18,'admin','','Project #1 tracking updated','State #26','','2012-01-17 18:18:48',1326820728),(19,'admin','127.0.0.1','Task added to project','Task \'re-Requisites&#x20;recollecting.\' added to project \'1\'','','2012-01-17 18:19:09',1326820749),(20,'admin','','Task #2 tracking updated','State #11','','2012-01-17 18:19:09',1326820749),(21,'admin','','Project #1 tracking updated','State #26','','2012-01-17 18:19:09',1326820749),(22,'admin','127.0.0.1','Task added to project','Task \'Formal&#x20;Analysys\' added to project \'1\'','','2012-01-17 18:19:29',1326820769),(23,'admin','','Task #3 tracking updated','State #11','','2012-01-17 18:19:29',1326820769),(24,'admin','','Project #1 tracking updated','State #26','','2012-01-17 18:19:29',1326820769),(25,'admin','127.0.0.1','Task added to project','Task \'Formal&#x20;design\' added to project \'1\'','','2012-01-17 18:19:43',1326820783),(26,'admin','','Task #4 tracking updated','State #11','','2012-01-17 18:19:44',1326820784),(27,'admin','','Project #1 tracking updated','State #26','','2012-01-17 18:19:44',1326820784),(28,'admin','127.0.0.1','Task added to project','Task \'Pre-Development\' added to project \'1\'','','2012-01-17 18:20:05',1326820805),(29,'admin','','Task #5 tracking updated','State #11','','2012-01-17 18:20:06',1326820806),(30,'admin','','Project #1 tracking updated','State #26','','2012-01-17 18:20:06',1326820806),(31,'admin','127.0.0.1','Task added to project','Task \'Development\' added to project \'1\'','','2012-01-17 18:20:24',1326820824),(32,'admin','','Task #6 tracking updated','State #11','','2012-01-17 18:20:24',1326820824),(33,'admin','','Project #1 tracking updated','State #26','','2012-01-17 18:20:24',1326820824),(34,'admin','127.0.0.1','Task added to project','Task \'Testing\' added to project \'1\'','','2012-01-17 18:20:42',1326820842),(35,'admin','','Task #7 tracking updated','State #11','','2012-01-17 18:20:42',1326820842),(36,'admin','','Project #1 tracking updated','State #26','','2012-01-17 18:20:42',1326820842),(37,'admin','127.0.0.1','Task added to project','Task \'Documentation\' added to project \'1\'','','2012-01-17 18:20:59',1326820859),(38,'admin','','Task #8 tracking updated','State #11','','2012-01-17 18:20:59',1326820859),(39,'admin','','Project #1 tracking updated','State #26','','2012-01-17 18:20:59',1326820859),(40,'julio','127.0.0.1','Incident updated','Unknown update','','2012-01-17 18:25:03',1326821103),(41,'julio','127.0.0.1','Incident created','User julio created incident #2','','2012-01-17 18:25:03',1326821103),(42,'admin','127.0.0.1','Incident updated','Workunit added','','2012-01-17 18:25:20',1326821120),(43,'julio','127.0.0.1','Incident updated','Workunit added','','2012-01-17 18:25:31',1326821131),(44,'admin','127.0.0.1','Incident updated','Workunit added','','2012-01-17 18:25:49',1326821149),(45,'julio','127.0.0.1','Incident updated','Workunit added','','2012-01-17 18:26:00',1326821160),(46,'admin','127.0.0.1','Incident updated','Status changed -> Resolved','','2012-01-17 18:26:28',1326821188),(47,'admin','127.0.0.1','Incident updated','Resolution changed -> ','','2012-01-17 18:26:28',1326821188),(48,'admin','127.0.0.1','Incident updated','Updated','','2012-01-17 18:26:28',1326821188),(49,'admin','127.0.0.1','Incident updated','User admin incident updated #2','','2012-01-17 18:26:28',1326821188),(50,'julio','127.0.0.1','Incident updated','Unknown update','','2012-01-17 18:27:01',1326821221),(51,'julio','127.0.0.1','Incident created','User julio created incident #3','','2012-01-17 18:27:01',1326821221),(52,'julio','127.0.0.1','Logoff','Logged out','','2012-01-17 18:30:33',1326821433),(53,'admin','127.0.0.1','Logon','Logged in','','2012-01-17 18:30:37',1326821437),(54,'admin','127.0.0.1','Logoff','Logged out','','2012-01-17 18:41:53',1326822113),(55,'julio','127.0.0.1','Logon','Logged in','','2012-01-17 18:41:56',1326822116),(56,'julio','127.0.0.1','Logoff','Logged out','','2012-01-17 18:49:18',1326822558),(57,'julio','127.0.0.1','Logon','Logged in','','2012-01-17 18:49:21',1326822561),(58,'julio','127.0.0.1','Logoff','Logged out','','2012-01-17 18:54:15',1326822855),(59,'admin','127.0.0.1','Logon','Logged in','','2012-01-17 18:54:18',1326822858),(60,'admin','127.0.0.1','Spare work unit added','Workunit for admin added to Task ID #1','','2012-01-17 20:07:49',1326827269),(61,'admin','127.0.0.1','Logon','Logged in','','2012-01-17 21:14:41',1326831281),(62,'julio','127.0.0.1','Logon','Logged in','','2012-01-17 21:33:35',1326832415),(63,'julio','127.0.0.1','Logoff','Logged out','','2012-01-17 21:34:26',1326832466),(64,'admin','127.0.0.1','Logon','Logged in','','2012-01-17 21:34:28',1326832468),(65,'admin','127.0.0.1','Logoff','Logged out','','2012-01-17 21:36:27',1326832587),(66,'javi','127.0.0.1','Logon','Logged in','','2012-01-17 21:36:29',1326832589),(67,'javi','127.0.0.1','Logoff','Logged out','','2012-01-17 21:36:39',1326832599),(68,'admin','127.0.0.1','Logon Failed','Invalid username: admin / n****e','','2012-01-17 21:36:41',1326832601),(69,'admin','127.0.0.1','Logon','Logged in','','2012-01-17 21:36:44',1326832604),(70,'admin','127.0.0.1','Incident updated','Assigned user changed -> Javier&#x20;Nadie','','2012-01-17 21:38:06',1326832686),(71,'admin','127.0.0.1','Incident updated','Updated','','2012-01-17 21:38:06',1326832686),(72,'support','127.0.0.1','Incident updated','User admin incident updated #1','','2012-01-17 21:38:06',1326832686),(73,'admin','127.0.0.1','Incident updated','Workunit added','','2012-01-17 21:44:23',1326833063),(74,'admin','127.0.0.1','Logoff','Logged out','','2012-01-17 21:46:02',1326833162),(75,'luis','127.0.0.1','Logon','Logged in','','2012-01-17 21:46:05',1326833165),(76,'luis','127.0.0.1','Incident updated','Workunit added','','2012-01-17 21:47:03',1326833223),(77,'luis','127.0.0.1','Incident updated','Assigned user changed -> ','','2012-01-17 21:48:33',1326833313),(78,'luis','127.0.0.1','Incident updated','Updated','','2012-01-17 21:48:33',1326833313),(79,'admin','127.0.0.1','Incident updated','User luis incident updated #3','','2012-01-17 21:48:33',1326833313),(80,'luis','127.0.0.1','Incident updated','Assigned user changed -> Javier&#x20;Nadie','','2012-01-17 21:51:15',1326833475),(81,'luis','127.0.0.1','Incident updated','Updated','','2012-01-17 21:51:16',1326833476),(82,'luis','127.0.0.1','Incident updated','User luis incident updated #3','','2012-01-17 21:51:16',1326833476),(83,'luis','127.0.0.1','Incident updated','Updated','','2012-01-17 21:51:16',1326833476),(84,'javi','127.0.0.1','Incident updated','User luis incident updated #3','','2012-01-17 21:51:16',1326833476),(85,'luis','127.0.0.1','Logoff','Logged out','','2012-01-17 22:11:15',1326834675),(86,'javi','127.0.0.1','Logon','Logged in','','2012-01-17 22:11:19',1326834679),(87,'javi','127.0.0.1','Incident updated','Status changed -> Resolved','','2012-01-17 22:11:36',1326834696),(88,'javi','127.0.0.1','Incident updated','Updated','','2012-01-17 22:11:36',1326834696),(89,'javi','127.0.0.1','Incident updated','User javi incident updated #3','','2012-01-17 22:11:36',1326834696),(90,'javi','127.0.0.1','Incident updated','Updated','','2012-01-17 22:11:36',1326834696),(91,'javi','127.0.0.1','Incident updated','User javi incident updated #3','','2012-01-17 22:11:36',1326834696);
/*!40000 ALTER TABLE `tsesion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tsla`
--

LOCK TABLES `tsla` WRITE;
/*!40000 ALTER TABLE `tsla` DISABLE KEYS */;
INSERT INTO `tsla` VALUES (1,'SLA&#x20;base','',2,480,10,1,1,8,18,0);
/*!40000 ALTER TABLE `tsla` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `ttask`
--

LOCK TABLES `ttask` WRITE;
/*!40000 ALTER TABLE `ttask` DISABLE KEYS */;
INSERT INTO `ttask` VALUES (-4,-1,0,'Workunits lost (without project/task)','',0,0,0,'0000-00-00','0000-00-00',0,0.00,0,'none'),(-3,-1,0,'Not justified','',0,0,0,'0000-00-00','0000-00-00',0,0.00,0,'none'),(-2,-1,0,'Health issues','',0,0,0,'0000-00-00','0000-00-00',0,0.00,0,'none'),(-1,-1,0,'Vacations','',0,0,0,'0000-00-00','0000-00-00',0,0.00,0,'none'),(1,1,0,'Planning','',0,0,0,'2012-01-23','2012-02-10',144,0.00,4,'none'),(2,1,0,'re-Requisites&#x20;recollecting.','',0,0,0,'2012-01-01','2012-01-22',168,0.00,4,'none'),(3,1,0,'Formal&#x20;Analysys','',0,0,0,'2012-02-01','2012-02-29',224,0.00,4,'none'),(4,1,0,'Formal&#x20;design','',0,0,0,'2012-03-01','2012-04-01',247,0.00,4,'none'),(5,1,1,'Pre-Development','',0,0,0,'2012-01-31','2012-02-23',184,0.00,4,'none'),(6,1,4,'Development','',0,0,0,'2012-04-01','2012-05-31',480,0.00,4,'none'),(7,1,4,'Testing','',0,0,0,'2012-05-01','2012-06-29',472,0.00,4,'none'),(8,1,0,'Documentation','',0,0,0,'2012-05-31','2012-07-31',228,0.00,4,'none');
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
INSERT INTO `ttask_track` VALUES (1,1,'admin',0,11,'2012-01-17 18:18:48'),(2,2,'admin',0,11,'2012-01-17 18:19:09'),(3,3,'admin',0,11,'2012-01-17 18:19:29'),(4,4,'admin',0,11,'2012-01-17 18:19:44'),(5,5,'admin',0,11,'2012-01-17 18:20:06'),(6,6,'admin',0,11,'2012-01-17 18:20:24'),(7,7,'admin',0,11,'2012-01-17 18:20:42'),(8,8,'admin',0,11,'2012-01-17 18:20:59');
/*!40000 ALTER TABLE `ttask_track` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `ttodo`
--

LOCK TABLES `ttodo` WRITE;
/*!40000 ALTER TABLE `ttodo` DISABLE KEYS */;
INSERT INTO `ttodo` VALUES (1,'Revisar&#x20;documentacion&#x20;para&#x20;formato&#x20;PDF',0,'admin','admin',0,'2012-01-17 20:06:55','','2012-01-17 20:07:01',1);
/*!40000 ALTER TABLE `ttodo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tuser_report`
--

LOCK TABLES `tuser_report` WRITE;
/*!40000 ALTER TABLE `tuser_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `tuser_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tusuario`
--

LOCK TABLES `tusuario` WRITE;
/*!40000 ALTER TABLE `tusuario` DISABLE KEYS */;
INSERT INTO `tusuario` VALUES ('admin','Default Admin','2f62afb6e17e46f0717225bcca6225b7','Default Integria Admin superuser. Please change password ASAP','2012-01-17 21:36:44','admin@integria.sf.net','555-555-555',1,'people_1','','',0),('javi','Javier&#x20;Nadie','334c4a4c42fdb79d7ebc3e73b517e6f8','','2012-01-17 22:11:19',' ','',0,'people_1','','',0),('julio','Julio&#x20;Marin','334c4a4c42fdb79d7ebc3e73b517e6f8','Usuario&#x20;de&#x20;xxxxx','2012-01-17 21:33:35','julio@marinnet.com&#x20; ','627348347',0,'people_1','','',0),('luis','','334c4a4c42fdb79d7ebc3e73b517e6f8','','2012-01-17 21:46:05','','',1,'people_1','','',0),('support','-','334c4a4c42fdb79d7ebc3e73b517e6f8','','2012-01-17 21:35:06','','',1,'people_1','','',0);
/*!40000 ALTER TABLE `tusuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tusuario_perfil`
--

LOCK TABLES `tusuario_perfil` WRITE;
/*!40000 ALTER TABLE `tusuario_perfil` DISABLE KEYS */;
INSERT INTO `tusuario_perfil` VALUES (2,'admin',5,1,'admin'),(3,'julio',4,1,'admin'),(4,'julio',1,4,'admin'),(5,'javi',3,4,'admin');
/*!40000 ALTER TABLE `tusuario_perfil` ENABLE KEYS */;
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
INSERT INTO `tworkunit` VALUES (1,'2012-01-17 18:07:35',0.25,'admin','Have&#x20;you&#x20;tried&#x20;to&#x20;turn&#x20;it&#x20;off&#x20;and&#x20;on&#x20;?&#x0a;',0,0,'',1),(2,'2012-01-17 18:08:02',0.25,'julio','I&#x20;also&#x20;see&#x20;IT&#x20;Crowd...&#x20;but&#x20;not,&#x20;a&#x20;restart&#x20;doesn&#039;t&#x20;solve&#x20;the&#x20;problem,&#x20;please&#x20;ADVICE:&#x0a;',0,0,'',1),(3,'2012-01-17 18:09:28',0.05,'julio','Automatic WU: Added a file to this issue. Filename uploaded: shot0000.jpg',0,0,'',1),(4,'2012-01-17 18:10:58',0.25,'admin','Give&#x20;me&#x20;a&#x20;few&#x20;hours&#x20;to&#x20;check&#x20;it&#x20;out.',0,0,'',1),(5,'2012-01-17 18:11:01',0.25,'julio','Thx&#x0a;',0,0,'',1),(6,'2012-01-17 18:25:20',0.25,'admin','I&#x20;how&#x20;its&#x20;possible&#x20;you&#x20;type&#x20;that&#x20;?',0,0,'',1),(7,'2012-01-17 18:25:31',0.25,'julio','From&#x20;another&#x20;system...&#x20;stupid!&#x20;&#x0a;',0,0,'',1),(8,'2012-01-17 18:25:49',0.25,'admin','Have&#x20;you&#x20;tried&#x20;to&#x20;plug&#x20;off&#x20;and&#x20;plugin&#x20;in&#x20;again&#x20;the&#x20;keyboard&#x20;?&#x0a;',0,0,'',1),(9,'2012-01-17 18:26:00',0.25,'julio','OMG...&#x20;amazing&#x20;:-&#41;&#41;&#41;&#x20;it&#x20;works&#x20;!&#x0a;',0,0,'',1),(10,'2012-01-17 00:00:00',8.00,'admin','Preparing&#x20;the&#x20;development&#x20;servers.',0,5,'',1),(11,'2012-01-18 00:00:00',8.00,'admin','Preparing&#x20;the&#x20;development&#x20;servers.',0,5,'',1),(12,'2012-01-19 00:00:00',8.00,'admin','Preparing&#x20;the&#x20;development&#x20;servers.',0,5,'',1),(13,'2012-01-20 00:00:00',8.00,'admin','Preparing&#x20;the&#x20;development&#x20;servers.',0,5,'',1),(14,'2012-01-23 00:00:00',8.00,'admin','Preparing&#x20;the&#x20;development&#x20;servers.',0,5,'',1),(15,'2012-01-01 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(16,'2012-01-02 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(17,'2012-01-03 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(18,'2012-01-04 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(19,'2012-01-05 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(20,'2012-01-06 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(21,'2012-01-09 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(22,'2012-01-10 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(23,'2012-01-11 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(24,'2012-01-12 00:00:00',8.00,'julio','Interview&#x20;with&#x20;the&#x20;customer&#x20;and&#x20;other&#x20;power-users',0,3,'',1),(25,'2012-01-17 00:00:00',4.00,'admin','Testing',0,0,'',1),(26,'2012-01-17 21:44:23',0.25,'javi','&quot;Veo&#x20;lo&#x20;que&#x20;me&#x20;cuentas&#x20;y&#x20;voy&#x20;a&#x20;intentar&#x20;reproducirlo&#x20;para&#x20;determinar&#x20;el&#x20;origen&#x20;del&#x20;problema&quot;.',0,0,'',1),(27,'2012-01-17 21:47:03',0.25,'luis','Estamos&#x20;mirandolo,&#x20;el&#x20;problema&#x20;parece&#x20;complicado.',0,0,'',1);
/*!40000 ALTER TABLE `tworkunit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tworkunit_incident`
--

LOCK TABLES `tworkunit_incident` WRITE;
/*!40000 ALTER TABLE `tworkunit_incident` DISABLE KEYS */;
INSERT INTO `tworkunit_incident` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,1,5),(6,2,6),(7,2,7),(8,2,8),(9,2,9),(10,3,26),(11,3,27);
/*!40000 ALTER TABLE `tworkunit_incident` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tworkunit_task`
--

/*!40000 ALTER TABLE `tworkunit_task` DISABLE KEYS */;
INSERT INTO `tworkunit_task` VALUES (1,5,10),(2,5,11),(3,5,12),(4,5,13),(5,5,14),(6,2,15),(7,2,16),(8,2,17),(9,2,18),(10,2,19),(11,2,20),(12,2,21),(13,2,22),(14,2,23),(15,2,24),(16,1,25);
/*!40000 ALTER TABLE `tworkunit_task` ENABLE KEYS */;
