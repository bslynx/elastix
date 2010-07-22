-- MySQL dump 10.11
--
-- Host: localhost    Database: mya2billing
-- ------------------------------------------------------
-- Server version	5.0.45

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
-- Current Database: `mya2billing`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mya2billing` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `mya2billing`;

--
-- Table structure for table `cc_alarm`
--

DROP TABLE IF EXISTS `cc_alarm`;
CREATE TABLE `cc_alarm` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` text collate utf8_bin NOT NULL,
  `periode` int(11) NOT NULL default '1',
  `type` int(11) NOT NULL default '1',
  `maxvalue` float NOT NULL,
  `minvalue` float NOT NULL default '-1',
  `id_trunk` int(11) default NULL,
  `status` int(11) NOT NULL default '0',
  `numberofrun` int(11) NOT NULL default '0',
  `numberofalarm` int(11) NOT NULL default '0',
  `datecreate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `datelastrun` timestamp NOT NULL default '0000-00-00 00:00:00',
  `emailreport` varchar(50) collate utf8_bin default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_alarm`
--

LOCK TABLES `cc_alarm` WRITE;
/*!40000 ALTER TABLE `cc_alarm` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_alarm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_alarm_report`
--

DROP TABLE IF EXISTS `cc_alarm_report`;
CREATE TABLE `cc_alarm_report` (
  `id` bigint(20) NOT NULL auto_increment,
  `cc_alarm_id` bigint(20) NOT NULL,
  `calculatedvalue` float NOT NULL,
  `daterun` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_alarm_report`
--

LOCK TABLES `cc_alarm_report` WRITE;
/*!40000 ALTER TABLE `cc_alarm_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_alarm_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_autorefill_report`
--

DROP TABLE IF EXISTS `cc_autorefill_report`;
CREATE TABLE `cc_autorefill_report` (
  `id` bigint(20) NOT NULL auto_increment,
  `daterun` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `totalcardperform` int(11) default NULL,
  `totalcredit` decimal(15,5) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_autorefill_report`
--

LOCK TABLES `cc_autorefill_report` WRITE;
/*!40000 ALTER TABLE `cc_autorefill_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_autorefill_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_backup`
--

DROP TABLE IF EXISTS `cc_backup`;
CREATE TABLE `cc_backup` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_bin NOT NULL,
  `path` varchar(255) collate utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_backup_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_backup`
--

LOCK TABLES `cc_backup` WRITE;
/*!40000 ALTER TABLE `cc_backup` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_call`
--

DROP TABLE IF EXISTS `cc_call`;
CREATE TABLE `cc_call` (
  `id` bigint(20) NOT NULL auto_increment,
  `sessionid` char(40) collate utf8_bin NOT NULL,
  `uniqueid` char(30) collate utf8_bin NOT NULL,
  `username` char(40) collate utf8_bin NOT NULL,
  `nasipaddress` char(30) collate utf8_bin default NULL,
  `starttime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `stoptime` timestamp NOT NULL default '0000-00-00 00:00:00',
  `sessiontime` int(11) default NULL,
  `calledstation` char(30) collate utf8_bin default NULL,
  `startdelay` int(11) default NULL,
  `stopdelay` int(11) default NULL,
  `terminatecause` char(20) collate utf8_bin default NULL,
  `usertariff` char(20) collate utf8_bin default NULL,
  `calledprovider` char(20) collate utf8_bin default NULL,
  `calledcountry` char(30) collate utf8_bin default NULL,
  `calledsub` char(20) collate utf8_bin default NULL,
  `calledrate` float default NULL,
  `sessionbill` float default NULL,
  `destination` char(40) collate utf8_bin default NULL,
  `id_tariffgroup` int(11) default NULL,
  `id_tariffplan` int(11) default NULL,
  `id_ratecard` int(11) default NULL,
  `id_trunk` int(11) default NULL,
  `sipiax` int(11) default '0',
  `src` char(40) collate utf8_bin default NULL,
  `id_did` int(11) default NULL,
  `buyrate` decimal(15,5) default '0.00000',
  `buycost` decimal(15,5) default '0.00000',
  `id_card_package_offer` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_call`
--

LOCK TABLES `cc_call` WRITE;
/*!40000 ALTER TABLE `cc_call` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_call` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_callback_spool`
--

DROP TABLE IF EXISTS `cc_callback_spool`;
CREATE TABLE `cc_callback_spool` (
  `id` bigint(20) NOT NULL auto_increment,
  `uniqueid` varchar(40) collate utf8_bin default NULL,
  `entry_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `status` varchar(80) collate utf8_bin default NULL,
  `server_ip` varchar(40) collate utf8_bin default NULL,
  `num_attempt` int(11) NOT NULL default '0',
  `last_attempt_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `manager_result` varchar(60) collate utf8_bin default NULL,
  `agi_result` varchar(60) collate utf8_bin default NULL,
  `callback_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `channel` varchar(60) collate utf8_bin default NULL,
  `exten` varchar(60) collate utf8_bin default NULL,
  `context` varchar(60) collate utf8_bin default NULL,
  `priority` varchar(60) collate utf8_bin default NULL,
  `application` varchar(60) collate utf8_bin default NULL,
  `data` varchar(60) collate utf8_bin default NULL,
  `timeout` varchar(60) collate utf8_bin default NULL,
  `callerid` varchar(60) collate utf8_bin default NULL,
  `variable` varchar(100) collate utf8_bin default NULL,
  `account` varchar(60) collate utf8_bin default NULL,
  `async` varchar(60) collate utf8_bin default NULL,
  `actionid` varchar(60) collate utf8_bin default NULL,
  `id_server` int(11) default NULL,
  `id_server_group` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cc_callback_spool_uniqueid_key` (`uniqueid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_callback_spool`
--

LOCK TABLES `cc_callback_spool` WRITE;
/*!40000 ALTER TABLE `cc_callback_spool` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_callback_spool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_callerid`
--

DROP TABLE IF EXISTS `cc_callerid`;
CREATE TABLE `cc_callerid` (
  `id` bigint(20) NOT NULL auto_increment,
  `cid` char(100) collate utf8_bin default NULL,
  `id_cc_card` bigint(20) NOT NULL,
  `activated` char(1) collate utf8_bin NOT NULL default 't',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_callerid_cid` (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_callerid`
--

LOCK TABLES `cc_callerid` WRITE;
/*!40000 ALTER TABLE `cc_callerid` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_callerid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_campaign`
--

DROP TABLE IF EXISTS `cc_campaign`;
CREATE TABLE `cc_campaign` (
  `id` int(11) NOT NULL auto_increment,
  `campaign_name` char(50) collate utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `startingdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `description` mediumtext collate utf8_bin,
  `id_trunk` int(11) default '0',
  `secondusedreal` int(11) default '0',
  `nb_callmade` int(11) default '0',
  `enable` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_campaign_campaign_name` (`campaign_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_campaign`
--

LOCK TABLES `cc_campaign` WRITE;
/*!40000 ALTER TABLE `cc_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_card`
--

DROP TABLE IF EXISTS `cc_card`;
CREATE TABLE `cc_card` (
  `id` bigint(20) NOT NULL auto_increment,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `firstusedate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `enableexpire` int(11) default '0',
  `expiredays` int(11) default '0',
  `username` char(50) collate utf8_bin NOT NULL,
  `useralias` char(50) collate utf8_bin NOT NULL,
  `userpass` char(50) collate utf8_bin NOT NULL,
  `uipass` char(50) collate utf8_bin default NULL,
  `credit` decimal(15,5) NOT NULL default '0.00000',
  `tariff` int(11) default '0',
  `id_didgroup` int(11) default '0',
  `activated` char(1) collate utf8_bin NOT NULL default 'f',
  `lastname` char(50) collate utf8_bin default NULL,
  `firstname` char(50) collate utf8_bin default NULL,
  `address` char(100) collate utf8_bin default NULL,
  `city` char(40) collate utf8_bin default NULL,
  `state` char(40) collate utf8_bin default NULL,
  `country` char(40) collate utf8_bin default NULL,
  `zipcode` char(20) collate utf8_bin default NULL,
  `phone` char(20) collate utf8_bin default NULL,
  `email` char(70) collate utf8_bin default NULL,
  `fax` char(20) collate utf8_bin default NULL,
  `inuse` int(11) default '0',
  `simultaccess` int(11) default '0',
  `currency` char(3) collate utf8_bin default 'USD',
  `lastuse` timestamp NOT NULL default '0000-00-00 00:00:00',
  `nbused` int(11) default '0',
  `typepaid` int(11) default '0',
  `creditlimit` int(11) default '0',
  `voipcall` int(11) default '0',
  `sip_buddy` int(11) default '0',
  `iax_buddy` int(11) default '0',
  `language` char(5) collate utf8_bin default 'en',
  `redial` char(50) collate utf8_bin default NULL,
  `runservice` int(11) default '0',
  `nbservice` int(11) default '0',
  `id_campaign` int(11) default '0',
  `num_trials_done` bigint(20) default '0',
  `callback` char(50) collate utf8_bin default NULL,
  `vat` float NOT NULL default '0',
  `servicelastrun` timestamp NOT NULL default '0000-00-00 00:00:00',
  `initialbalance` decimal(15,5) NOT NULL default '0.00000',
  `invoiceday` int(11) default '1',
  `autorefill` int(11) default '0',
  `loginkey` char(40) collate utf8_bin default NULL,
  `activatedbyuser` char(1) collate utf8_bin NOT NULL default 't',
  `id_subscription_fee` int(11) default '0',
  `mac_addr` char(17) collate utf8_bin NOT NULL default '00-00-00-00-00-00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_card_username` (`username`),
  UNIQUE KEY `cons_cc_card_useralias` (`useralias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_card`
--

LOCK TABLES `cc_card` WRITE;
/*!40000 ALTER TABLE `cc_card` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_card_package_offer`
--

DROP TABLE IF EXISTS `cc_card_package_offer`;
CREATE TABLE `cc_card_package_offer` (
  `id` bigint(20) NOT NULL auto_increment,
  `id_cc_card` bigint(20) NOT NULL,
  `id_cc_package_offer` bigint(20) NOT NULL,
  `date_consumption` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `used_secondes` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ind_cc_card_package_offer_id_card` (`id_cc_card`),
  KEY `ind_cc_card_package_offer_id_package_offer` (`id_cc_package_offer`),
  KEY `ind_cc_card_package_offer_date_consumption` (`date_consumption`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_card_package_offer`
--

LOCK TABLES `cc_card_package_offer` WRITE;
/*!40000 ALTER TABLE `cc_card_package_offer` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_card_package_offer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_charge`
--

DROP TABLE IF EXISTS `cc_charge`;
CREATE TABLE `cc_charge` (
  `id` bigint(20) NOT NULL auto_increment,
  `id_cc_card` bigint(20) NOT NULL,
  `iduser` int(11) NOT NULL default '0',
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `amount` float NOT NULL default '0',
  `currency` char(3) collate utf8_bin default 'USD',
  `chargetype` int(11) default '0',
  `description` mediumtext collate utf8_bin,
  `id_cc_did` bigint(20) default '0',
  `id_cc_subscription_fee` bigint(20) default '0',
  PRIMARY KEY  (`id`),
  KEY `ind_cc_charge_id_cc_card` (`id_cc_card`),
  KEY `ind_cc_charge_id_cc_subscription_fee` (`id_cc_subscription_fee`),
  KEY `ind_cc_charge_creationdate` (`creationdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_charge`
--

LOCK TABLES `cc_charge` WRITE;
/*!40000 ALTER TABLE `cc_charge` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_charge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_configuration`
--

DROP TABLE IF EXISTS `cc_configuration`;
CREATE TABLE `cc_configuration` (
  `configuration_id` int(11) NOT NULL auto_increment,
  `configuration_title` varchar(64) collate utf8_bin NOT NULL,
  `configuration_key` varchar(64) collate utf8_bin NOT NULL,
  `configuration_value` varchar(255) collate utf8_bin NOT NULL,
  `configuration_description` varchar(255) collate utf8_bin NOT NULL,
  `configuration_type` int(11) NOT NULL default '0',
  `use_function` varchar(255) collate utf8_bin default NULL,
  `set_function` varchar(255) collate utf8_bin default NULL,
  PRIMARY KEY  (`configuration_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_configuration`
--

LOCK TABLES `cc_configuration` WRITE;
/*!40000 ALTER TABLE `cc_configuration` DISABLE KEYS */;
INSERT INTO `cc_configuration` VALUES (1,'Login Username','MODULE_PAYMENT_AUTHORIZENET_LOGIN','testing','The login username used for the Authorize.net service',0,NULL,NULL),(2,'Transaction Key','MODULE_PAYMENT_AUTHORIZENET_TXNKEY','Test','Transaction Key used for encrypting TP data',0,NULL,NULL),(3,'Transaction Mode','MODULE_PAYMENT_AUTHORIZENET_TESTMODE','Test','Transaction mode used for processing orders',0,NULL,'tep_cfg_select_option(array(\'Test\', \'Production\'), '),(4,'Transaction Method','MODULE_PAYMENT_AUTHORIZENET_METHOD','Credit Card','Transaction method used for processing orders',0,NULL,'tep_cfg_select_option(array(\'Credit Card\', \'eCheck\'), '),(5,'Customer Notifications','MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER','False','Should Authorize.Net e-mail a receipt to the customer?',0,NULL,'tep_cfg_select_option(array(\'True\', \'False\'), '),(6,'Enable Authorize.net Module','MODULE_PAYMENT_AUTHORIZENET_STATUS','True','Do you want to accept Authorize.net payments?',0,NULL,'tep_cfg_select_option(array(\'True\', \'False\'), '),(7,'Enable PayPal Module','MODULE_PAYMENT_PAYPAL_STATUS','True','Do you want to accept PayPal payments?',0,NULL,'tep_cfg_select_option(array(\'True\', \'False\'), '),(8,'E-Mail Address','MODULE_PAYMENT_PAYPAL_ID','you@yourbusiness.com','The e-mail address to use for the PayPal service',0,NULL,NULL),(9,'Transaction Currency','MODULE_PAYMENT_PAYPAL_CURRENCY','Selected Currency','The currency to use for credit card transactions',0,NULL,'tep_cfg_select_option(array(\'Selected Currency\',\'USD\',\'CAD\',\'EUR\',\'GBP\',\'JPY\'), '),(10,'E-Mail Address','MODULE_PAYMENT_MONEYBOOKERS_ID','you@yourbusiness.com','The eMail address to use for the moneybookers service',0,NULL,NULL),(11,'Referral ID','MODULE_PAYMENT_MONEYBOOKERS_REFID','989999','Your personal Referral ID from moneybookers.com',0,NULL,NULL),(12,'Transaction Currency','MODULE_PAYMENT_MONEYBOOKERS_CURRENCY','Selected Currency','The default currency for the payment transactions',0,NULL,'tep_cfg_select_option(array(\'Selected Currency\',\'EUR\', \'USD\', \'GBP\', \'HKD\', \'SGD\', \'JPY\', \'CAD\', \'AUD\', \'CHF\', \'DKK\', \'SEK\', \'NOK\', \'ILS\', \'MYR\', \'NZD\', \'TWD\', \'THB\', \'CZK\', \'HUF\', \'SKK\', \'ISK\', \'INR\'), '),(13,'Transaction Language','MODULE_PAYMENT_MONEYBOOKERS_LANGUAGE','Selected Language','The default language for the payment transactions',0,NULL,'tep_cfg_select_option(array(\'Selected Language\',\'EN\', \'DE\', \'ES\', \'FR\'), '),(14,'Enable moneybookers Module','MODULE_PAYMENT_MONEYBOOKERS_STATUS','True','Do you want to accept moneybookers payments?',0,NULL,'tep_cfg_select_option(array(\'True\', \'False\'), ');
/*!40000 ALTER TABLE `cc_configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_country`
--

DROP TABLE IF EXISTS `cc_country`;
CREATE TABLE `cc_country` (
  `id` bigint(20) NOT NULL auto_increment,
  `countrycode` char(80) collate utf8_bin NOT NULL,
  `countryprefix` char(80) collate utf8_bin NOT NULL,
  `countryname` char(80) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=246 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_country`
--

LOCK TABLES `cc_country` WRITE;
/*!40000 ALTER TABLE `cc_country` DISABLE KEYS */;
INSERT INTO `cc_country` VALUES (1,'AFG','93','Afghanistan'),(2,'ALB','355','Albania'),(3,'DZA','213','Algeria'),(4,'ASM','684','American Samoa'),(5,'AND','376','Andorra'),(6,'AGO','244','Angola'),(7,'AIA','1264','Anguilla'),(8,'ATA','672','Antarctica'),(9,'ATG','1268','Antigua And Barbuda'),(10,'ARG','54','Argentina'),(11,'ARM','374','Armenia'),(12,'ABW','297','Aruba'),(13,'AUS','61','Australia'),(14,'AUT','43','Austria'),(15,'AZE','994','Azerbaijan'),(16,'BHS','1242','Bahamas'),(17,'BHR','973','Bahrain'),(18,'BGD','880','Bangladesh'),(19,'BRB','1246','Barbados'),(20,'BLR','375','Belarus'),(21,'BEL','32','Belgium'),(22,'BLZ','501','Belize'),(23,'BEN','229','Benin'),(24,'BMU','1441','Bermuda'),(25,'BTN','975','Bhutan'),(26,'BOL','591','Bolivia'),(27,'BIH','387','Bosnia And Herzegovina'),(28,'BWA','267','Botswana'),(29,'BV','0','Bouvet Island'),(30,'BRA','55','Brazil'),(31,'IO','1284','British Indian Ocean Territory'),(32,'BRN','673','Brunei Darussalam'),(33,'BGR','359','Bulgaria'),(34,'BFA','226','Burkina Faso'),(35,'BDI','257','Burundi'),(36,'KHM','855','Cambodia'),(37,'CMR','237','Cameroon'),(38,'CAN','1','Canada'),(39,'CPV','238','Cape Verde'),(40,'CYM','1345','Cayman Islands'),(41,'CAF','236','Central African Republic'),(42,'TCD','235','Chad'),(43,'CHL','56','Chile'),(44,'CHN','86','China'),(45,'CXR','618','Christmas Island'),(46,'CCK','61','Cocos (Keeling); Islands'),(47,'COL','57','Colombia'),(48,'COM','269','Comoros'),(49,'COG','242','Congo'),(50,'COD','243','Congo, The Democratic Republic Of The'),(51,'COK','682','Cook Islands'),(52,'CRI','506','Costa Rica'),(54,'HRV','385','Croatia'),(55,'CUB','53','Cuba'),(56,'CYP','357','Cyprus'),(57,'CZE','420','Czech Republic'),(58,'DNK','45','Denmark'),(59,'DJI','253','Djibouti'),(60,'DMA','1767','Dominica'),(61,'DOM','1809','Dominican Republic'),(62,'ECU','593','Ecuador'),(63,'EGY','20','Egypt'),(64,'SLV','503','El Salvador'),(65,'GNQ','240','Equatorial Guinea'),(66,'ERI','291','Eritrea'),(67,'EST','372','Estonia'),(68,'ETH','251','Ethiopia'),(69,'FLK','500','Falkland Islands (Malvinas);'),(70,'FRO','298','Faroe Islands'),(71,'FJI','679','Fiji'),(72,'FIN','358','Finland'),(73,'FRA','33','France'),(74,'GUF','596','French Guiana'),(75,'PYF','594','French Polynesia'),(76,'ATF','689','French Southern Territories'),(77,'GAB','241','Gabon'),(78,'GMB','220','Gambia'),(79,'GEO','995','Georgia'),(80,'DEU','49','Germany'),(81,'GHA','233','Ghana'),(82,'GIB','350','Gibraltar'),(83,'GRC','30','Greece'),(84,'GRL','299','Greenland'),(85,'GRD','1473','Grenada'),(86,'GLP','590','Guadeloupe'),(87,'GUM','1671','Guam'),(88,'GTM','502','Guatemala'),(89,'GIN','224','Guinea'),(90,'GNB','245','Guinea-Bissau'),(91,'GUY','592','Guyana'),(92,'HTI','509','Haiti'),(93,'HM','0','Heard Island And McDonald Islands'),(94,'VAT','0','Holy See (Vatican City State);'),(95,'HND','504','Honduras'),(96,'HKG','852','Hong Kong'),(97,'HUN','36','Hungary'),(98,'ISL','354','Iceland'),(99,'IND','91','India'),(100,'IDN','62','Indonesia'),(101,'IRN','98','Iran, Islamic Republic Of'),(102,'IRQ','964','Iraq'),(103,'IRL','353','Ireland'),(104,'ISR','972','Israel'),(105,'ITA','39','Italy'),(106,'JAM','1876','Jamaica'),(107,'JPN','81','Japan'),(108,'JOR','962','Jordan'),(109,'KAZ','7','Kazakhstan'),(110,'KEN','254','Kenya'),(111,'KIR','686','Kiribati'),(112,'PRK','850','Korea, Democratic People\'s Republic Of'),(113,'KOR','82','Korea, Republic of'),(114,'KWT','965','Kuwait'),(115,'KGZ','996','Kyrgyzstan'),(116,'LAO','856','Lao Peoples Democratic Republic'),(117,'LVA','371','Latvia'),(118,'LBN','961','Lebanon'),(119,'LSO','266','Lesotho'),(120,'LBR','231','Liberia'),(121,'LBY','218','Libyan Arab Jamahiriya'),(122,'LIE','423','Liechtenstein'),(123,'LTU','370','Lithuania'),(124,'LUX','352','Luxembourg'),(125,'MAC','853','Macao'),(126,'MKD','389','Macedonia, The Former Yugoslav Republic Of'),(127,'MDG','261','Madagascar'),(128,'MWI','265','Malawi'),(129,'MYS','60','Malaysia'),(130,'MDV','960','Maldives'),(131,'MLI','223','Mali'),(132,'MLT','356','Malta'),(133,'MHL','692','Marshall islands'),(134,'MTQ','596','Martinique'),(135,'MRT','222','Mauritania'),(136,'MUS','230','Mauritius'),(137,'MYT','269','Mayotte'),(138,'MEX','52','Mexico'),(139,'FSM','691','Micronesia, Federated States Of'),(140,'MDA','1808','Moldova, Republic Of'),(141,'MCO','377','Monaco'),(142,'MNG','976','Mongolia'),(143,'MSR','1664','Montserrat'),(144,'MAR','212','Morocco'),(145,'MOZ','258','Mozambique'),(146,'MMR','95','Myanmar'),(147,'NAM','264','Namibia'),(148,'NRU','674','Nauru'),(149,'NPL','977','Nepal'),(150,'NLD','31','Netherlands'),(151,'ANT','599','Netherlands Antilles'),(152,'NCL','687','New Caledonia'),(153,'NZL','64','New Zealand'),(154,'NIC','505','Nicaragua'),(155,'NER','227','Niger'),(156,'NGA','234','Nigeria'),(157,'NIU','683','Niue'),(158,'NFK','672','Norfolk Island'),(159,'MNP','1670','Northern Mariana Islands'),(160,'NOR','47','Norway'),(161,'OMN','968','Oman'),(162,'PAK','92','Pakistan'),(163,'PLW','680','Palau'),(164,'PSE','970','Palestinian Territory, Occupied'),(165,'PAN','507','Panama'),(166,'PNG','675','Papua New Guinea'),(167,'PRY','595','Paraguay'),(168,'PER','51','Peru'),(169,'PHL','63','Philippines'),(170,'PN','0','Pitcairn'),(171,'POL','48','Poland'),(172,'PRT','351','Portugal'),(173,'PRI','1787','Puerto Rico'),(174,'QAT','974','Qatar'),(175,'REU','262','Reunion'),(176,'ROU','40','Romania'),(177,'RUS','7','Russian Federation'),(178,'RWA','250','Rwanda'),(179,'SHN','290','SaINT Helena'),(180,'KNA','1869','SaINT Kitts And Nevis'),(181,'LCA','1758','SaINT Lucia'),(182,'SPM','508','SaINT Pierre And Miquelon'),(183,'VCT','1784','SaINT Vincent And The Grenadines'),(184,'WSM','685','Samoa'),(185,'SMR','378','San Marino'),(186,'STP','239','SÃ£o TomÃ© And Principe'),(187,'SAU','966','Saudi Arabia'),(188,'SEN','221','Senegal'),(189,'SYC','248','Seychelles'),(190,'SLE','232','Sierra Leone'),(191,'SGP','65','Singapore'),(192,'SVK','421','Slovakia'),(193,'SVN','386','Slovenia'),(194,'SLB','677','Solomon Islands'),(195,'SOM','252','Somalia'),(196,'ZAF','27','South Africa'),(197,'GS','0','South Georgia And The South Sandwich Islands'),(198,'ESP','34','Spain'),(199,'LKA','94','Sri Lanka'),(200,'SDN','249','Sudan'),(201,'SUR','597','Suriname'),(202,'SJ','0','Svalbard and Jan Mayen'),(203,'SWZ','268','Swaziland'),(204,'SWE','46','Sweden'),(205,'CHE','41','Switzerland'),(206,'SYR','963','Syrian Arab Republic'),(207,'TWN','886','Taiwan, Province Of China'),(208,'TJK','992','Tajikistan'),(209,'TZA','255','Tanzania, United Republic Of'),(210,'THA','66','Thailand'),(211,'TL','0','Timor LEste'),(212,'TGO','228','Togo'),(213,'TKL','690','Tokelau'),(214,'TON','676','Tonga'),(215,'TTO','1868','Trinidad And Tobago'),(216,'TUN','216','Tunisia'),(217,'TUR','90','Turkey'),(218,'TKM','993','Turkmenistan'),(219,'TCA','1649','Turks And Caicos Islands'),(220,'TUV','688','Tuvalu'),(221,'UGA','256','Uganda'),(222,'UKR','380','Ukraine'),(223,'ARE','971','United Arab Emirates'),(224,'GBR','44','United Kingdom'),(225,'USA','1','United States'),(226,'UM','0','United States Minor Outlying Islands'),(227,'URY','598','Uruguay'),(228,'UZB','998','Uzbekistan'),(229,'VUT','678','Vanuatu'),(230,'VEN','58','Venezuela'),(231,'VNM','84','Vietnam'),(232,'VGB','1284','Virgin Islands, British'),(233,'VIR','808','Virgin Islands, U.S.'),(234,'WLF','681','Wallis And Futuna'),(235,'EH','0','Western Sahara'),(236,'YEM','967','Yemen'),(237,'YUG','0','Yugoslavia'),(238,'ZMB','260','Zambia'),(239,'ZWE','263','Zimbabwe'),(240,'ASC','0','Ascension Island'),(241,'DGA','0','Diego Garcia'),(242,'XNM','0','Inmarsat'),(243,'TMP','670','East timor'),(244,'AK','0','Alaska'),(245,'HI','0','Hawaii'),(53,'CIV','225','CÃ´te d\'Ivoire');
/*!40000 ALTER TABLE `cc_country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_currencies`
--

DROP TABLE IF EXISTS `cc_currencies`;
CREATE TABLE `cc_currencies` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `currency` char(3) collate utf8_bin NOT NULL default '',
  `name` varchar(30) collate utf8_bin NOT NULL default '',
  `value` float(7,5) unsigned NOT NULL default '0.00000',
  `lastupdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `basecurrency` char(3) collate utf8_bin NOT NULL default 'USD',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_currencies_currency` (`currency`)
) ENGINE=MyISAM AUTO_INCREMENT=151 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_currencies`
--

LOCK TABLES `cc_currencies` WRITE;
/*!40000 ALTER TABLE `cc_currencies` DISABLE KEYS */;
INSERT INTO `cc_currencies` VALUES (1,'ALL','Albanian Lek (ALL)',0.00974,'2007-07-11 04:35:40','USD'),(2,'DZD','Algerian Dinar (DZD)',0.01345,'2007-07-11 04:35:40','USD'),(3,'XAL','Aluminium Ounces (XAL)',1.08295,'2007-07-11 04:35:40','USD'),(4,'ARS','Argentine Peso (ARS)',0.32455,'2007-07-11 04:35:40','USD'),(5,'AWG','Aruba Florin (AWG)',0.55866,'2007-07-11 04:35:40','USD'),(6,'AUD','Australian Dollar (AUD)',0.73384,'2007-07-11 04:35:40','USD'),(7,'BSD','Bahamian Dollar (BSD)',1.00000,'2007-07-11 04:35:40','USD'),(8,'BHD','Bahraini Dinar (BHD)',2.65322,'2007-07-11 04:35:40','USD'),(9,'BDT','Bangladesh Taka (BDT)',0.01467,'2007-07-11 04:35:40','USD'),(10,'BBD','Barbados Dollar (BBD)',0.50000,'2007-07-11 04:35:40','USD'),(11,'BYR','Belarus Ruble (BYR)',0.00046,'2007-07-11 04:35:40','USD'),(12,'BZD','Belize Dollar (BZD)',0.50569,'2007-07-11 04:35:40','USD'),(13,'BMD','Bermuda Dollar (BMD)',1.00000,'2007-07-11 04:35:40','USD'),(14,'BTN','Bhutan Ngultrum (BTN)',0.02186,'2007-07-11 04:35:40','USD'),(15,'BOB','Bolivian Boliviano (BOB)',0.12500,'2007-07-11 04:35:40','USD'),(16,'BRL','Brazilian Real (BRL)',0.46030,'2007-07-11 04:35:40','USD'),(17,'GBP','British Pound (GBP)',1.73702,'2007-07-11 04:35:40','USD'),(18,'BND','Brunei Dollar (BND)',0.61290,'2007-07-11 04:35:40','USD'),(19,'BGN','Bulgarian Lev (BGN)',0.60927,'2007-07-11 04:35:40','USD'),(20,'BIF','Burundi Franc (BIF)',0.00103,'2007-07-11 04:35:40','USD'),(21,'KHR','Cambodia Riel (KHR)',0.00000,'2007-07-11 04:35:40','USD'),(22,'CAD','Canadian Dollar (CAD)',0.86386,'2007-07-11 04:35:40','USD'),(23,'KYD','Cayman Islands Dollar (KYD)',1.16496,'2007-07-11 04:35:40','USD'),(24,'XOF','CFA Franc (BCEAO) (XOF)',0.00182,'2007-07-11 04:35:40','USD'),(25,'XAF','CFA Franc (BEAC) (XAF)',0.00182,'2007-07-11 04:35:40','USD'),(26,'CLP','Chilean Peso (CLP)',0.00187,'2007-07-11 04:35:40','USD'),(27,'CNY','Chinese Yuan (CNY)',0.12425,'2007-07-11 04:35:40','USD'),(28,'COP','Colombian Peso (COP)',0.00044,'2007-07-11 04:35:40','USD'),(29,'KMF','Comoros Franc (KMF)',0.00242,'2007-07-11 04:35:40','USD'),(30,'XCP','Copper Ounces (XCP)',2.16403,'2007-07-11 04:35:40','USD'),(31,'CRC','Costa Rica Colon (CRC)',0.00199,'2007-07-11 04:35:40','USD'),(32,'HRK','Croatian Kuna (HRK)',0.16249,'2007-07-11 04:35:40','USD'),(33,'CUP','Cuban Peso (CUP)',1.00000,'2007-07-11 04:35:40','USD'),(34,'CYP','Cyprus Pound (CYP)',2.07426,'2007-07-11 04:35:40','USD'),(35,'CZK','Czech Koruna (CZK)',0.04133,'2007-07-11 04:35:40','USD'),(36,'DKK','Danish Krone (DKK)',0.15982,'2007-07-11 04:35:40','USD'),(37,'DJF','Dijibouti Franc (DJF)',0.00000,'2007-07-11 04:35:40','USD'),(38,'DOP','Dominican Peso (DOP)',0.03035,'2007-07-11 04:35:40','USD'),(39,'XCD','East Caribbean Dollar (XCD)',0.37037,'2007-07-11 04:35:40','USD'),(40,'ECS','Ecuador Sucre (ECS)',0.00004,'2007-07-11 04:35:40','USD'),(41,'EGP','Egyptian Pound (EGP)',0.17433,'2007-07-11 04:35:40','USD'),(42,'SVC','El Salvador Colon (SVC)',0.11426,'2007-07-11 04:35:40','USD'),(43,'ERN','Eritrea Nakfa (ERN)',0.00000,'2007-07-11 04:35:40','USD'),(44,'EEK','Estonian Kroon (EEK)',0.07615,'2007-07-11 04:35:40','USD'),(45,'ETB','Ethiopian Birr (ETB)',0.11456,'2007-07-11 04:35:40','USD'),(46,'EUR','Euro (EUR)',1.19175,'2007-07-11 04:35:40','USD'),(47,'FKP','Falkland Islands Pound (FKP)',0.00000,'2007-07-11 04:35:40','USD'),(48,'GMD','Gambian Dalasi (GMD)',0.03515,'2007-07-11 04:35:40','USD'),(49,'GHC','Ghanian Cedi (GHC)',0.00011,'2007-07-11 04:35:40','USD'),(50,'GIP','Gibraltar Pound (GIP)',0.00000,'2007-07-11 04:35:40','USD'),(51,'XAU','Gold Ounces (XAU)',99.99999,'2007-07-11 04:35:40','USD'),(52,'GTQ','Guatemala Quetzal (GTQ)',0.13103,'2007-07-11 04:35:40','USD'),(53,'GNF','Guinea Franc (GNF)',0.00022,'2007-07-11 04:35:40','USD'),(54,'HTG','Haiti Gourde (HTG)',0.02387,'2007-07-11 04:35:40','USD'),(55,'HNL','Honduras Lempira (HNL)',0.05292,'2007-07-11 04:35:40','USD'),(56,'HKD','Hong Kong Dollar (HKD)',0.12884,'2007-07-11 04:35:40','USD'),(57,'HUF','Hungarian ForINT (HUF)',0.00461,'2007-07-11 04:35:40','USD'),(58,'ISK','Iceland Krona (ISK)',0.01436,'2007-07-11 04:35:40','USD'),(59,'INR','Indian Rupee (INR)',0.02253,'2007-07-11 04:35:40','USD'),(60,'IDR','Indonesian Rupiah (IDR)',0.00011,'2007-07-11 04:35:40','USD'),(61,'IRR','Iran Rial (IRR)',0.00011,'2007-07-11 04:35:40','USD'),(62,'ILS','Israeli Shekel (ILS)',0.21192,'2007-07-11 04:35:40','USD'),(63,'JMD','Jamaican Dollar (JMD)',0.01536,'2007-07-11 04:35:40','USD'),(64,'JPY','Japanese Yen (JPY)',0.00849,'2007-07-11 04:35:40','USD'),(65,'JOD','Jordanian Dinar (JOD)',1.41044,'2007-07-11 04:35:40','USD'),(66,'KZT','Kazakhstan Tenge (KZT)',0.00773,'2007-07-11 04:35:40','USD'),(67,'KES','Kenyan Shilling (KES)',0.01392,'2007-07-11 04:35:40','USD'),(68,'KRW','Korean Won (KRW)',0.00102,'2007-07-11 04:35:40','USD'),(69,'KWD','Kuwaiti Dinar (KWD)',3.42349,'2007-07-11 04:35:40','USD'),(70,'LAK','Lao Kip (LAK)',0.00000,'2007-07-11 04:35:40','USD'),(71,'LVL','Latvian Lat (LVL)',1.71233,'2007-07-11 04:35:40','USD'),(72,'LBP','Lebanese Pound (LBP)',0.00067,'2007-07-11 04:35:40','USD'),(73,'LSL','Lesotho Loti (LSL)',0.15817,'2007-07-11 04:35:40','USD'),(74,'LYD','Libyan Dinar (LYD)',0.00000,'2007-07-11 04:35:40','USD'),(75,'LTL','Lithuanian Lita (LTL)',0.34510,'2007-07-11 04:35:40','USD'),(76,'MOP','Macau Pataca (MOP)',0.12509,'2007-07-11 04:35:40','USD'),(77,'MKD','Macedonian Denar (MKD)',0.01945,'2007-07-11 04:35:40','USD'),(78,'MGF','Malagasy Franc (MGF)',0.00011,'2007-07-11 04:35:40','USD'),(79,'MWK','Malawi Kwacha (MWK)',0.00752,'2007-07-11 04:35:40','USD'),(80,'MYR','Malaysian Ringgit (MYR)',0.26889,'2007-07-11 04:35:40','USD'),(81,'MVR','Maldives Rufiyaa (MVR)',0.07813,'2007-07-11 04:35:40','USD'),(82,'MTL','Maltese Lira (MTL)',2.77546,'2007-07-11 04:35:40','USD'),(83,'MRO','Mauritania Ougulya (MRO)',0.00369,'2007-07-11 04:35:40','USD'),(84,'MUR','Mauritius Rupee (MUR)',0.03258,'2007-07-11 04:35:40','USD'),(85,'MXN','Mexican Peso (MXN)',0.09320,'2007-07-11 04:35:40','USD'),(86,'MDL','Moldovan Leu (MDL)',0.07678,'2007-07-11 04:35:40','USD'),(87,'MNT','Mongolian Tugrik (MNT)',0.00084,'2007-07-11 04:35:40','USD'),(88,'MAD','Moroccan Dirham (MAD)',0.10897,'2007-07-11 04:35:40','USD'),(89,'MZM','Mozambique Metical (MZM)',0.00004,'2007-07-11 04:35:40','USD'),(90,'NAD','Namibian Dollar (NAD)',0.15817,'2007-07-11 04:35:40','USD'),(91,'NPR','Nepalese Rupee (NPR)',0.01408,'2007-07-11 04:35:40','USD'),(92,'ANG','Neth Antilles Guilder (ANG)',0.55866,'2007-07-11 04:35:40','USD'),(93,'TRY','New Turkish Lira (TRY)',0.73621,'2007-07-11 04:35:40','USD'),(94,'NZD','New Zealand Dollar (NZD)',0.65096,'2007-07-11 04:35:40','USD'),(95,'NIO','Nicaragua Cordoba (NIO)',0.05828,'2007-07-11 04:35:40','USD'),(96,'NGN','Nigerian Naira (NGN)',0.00777,'2007-07-11 04:35:40','USD'),(97,'NOK','Norwegian Krone (NOK)',0.14867,'2007-07-11 04:35:40','USD'),(98,'OMR','Omani Rial (OMR)',2.59740,'2007-07-11 04:35:40','USD'),(99,'XPF','Pacific Franc (XPF)',0.00999,'2007-07-11 04:35:40','USD'),(100,'PKR','Pakistani Rupee (PKR)',0.01667,'2007-07-11 04:35:40','USD'),(101,'XPD','Palladium Ounces (XPD)',99.99999,'2007-07-11 04:35:40','USD'),(102,'PAB','Panama Balboa (PAB)',1.00000,'2007-07-11 04:35:40','USD'),(103,'PGK','Papua New Guinea Kina (PGK)',0.33125,'2007-07-11 04:35:40','USD'),(104,'PYG','Paraguayan Guarani (PYG)',0.00017,'2007-07-11 04:35:40','USD'),(105,'PEN','Peruvian Nuevo Sol (PEN)',0.29999,'2007-07-11 04:35:40','USD'),(106,'PHP','Philippine Peso (PHP)',0.01945,'2007-07-11 04:35:40','USD'),(107,'XPT','Platinum Ounces (XPT)',99.99999,'2007-07-11 04:35:40','USD'),(108,'PLN','Polish Zloty (PLN)',0.30574,'2007-07-11 04:35:40','USD'),(109,'QAR','Qatar Rial (QAR)',0.27476,'2007-07-11 04:35:40','USD'),(110,'ROL','Romanian Leu (ROL)',0.00000,'2007-07-11 04:35:40','USD'),(111,'RON','Romanian New Leu (RON)',0.34074,'2007-07-11 04:35:40','USD'),(112,'RUB','Russian Rouble (RUB)',0.03563,'2007-07-11 04:35:40','USD'),(113,'RWF','Rwanda Franc (RWF)',0.00185,'2007-07-11 04:35:40','USD'),(114,'WST','Samoa Tala (WST)',0.35492,'2007-07-11 04:35:40','USD'),(115,'STD','Sao Tome Dobra (STD)',0.00000,'2007-07-11 04:35:40','USD'),(116,'SAR','Saudi Arabian Riyal (SAR)',0.26665,'2007-07-11 04:35:40','USD'),(117,'SCR','Seychelles Rupee (SCR)',0.18114,'2007-07-11 04:35:40','USD'),(118,'SLL','Sierra Leone Leone (SLL)',0.00034,'2007-07-11 04:35:40','USD'),(119,'XAG','Silver Ounces (XAG)',9.77517,'2007-07-11 04:35:40','USD'),(120,'SGD','Singapore Dollar (SGD)',0.61290,'2007-07-11 04:35:40','USD'),(121,'SKK','Slovak Koruna (SKK)',0.03157,'2007-07-11 04:35:40','USD'),(122,'SIT','Slovenian Tolar (SIT)',0.00498,'2007-07-11 04:35:40','USD'),(123,'SOS','Somali Shilling (SOS)',0.00000,'2007-07-11 04:35:40','USD'),(124,'ZAR','South African Rand (ZAR)',0.15835,'2007-07-11 04:35:40','USD'),(125,'LKR','Sri Lanka Rupee (LKR)',0.00974,'2007-07-11 04:35:40','USD'),(126,'SHP','St Helena Pound (SHP)',0.00000,'2007-07-11 04:35:40','USD'),(127,'SDD','Sudanese Dinar (SDD)',0.00427,'2007-07-11 04:35:40','USD'),(128,'SRG','Surinam Guilder (SRG)',0.36496,'2007-07-11 04:35:40','USD'),(129,'SZL','Swaziland Lilageni (SZL)',0.15817,'2007-07-11 04:35:40','USD'),(130,'SEK','Swedish Krona (SEK)',0.12609,'2007-07-11 04:35:40','USD'),(131,'CHF','Swiss Franc (CHF)',0.76435,'2007-07-11 04:35:40','USD'),(132,'SYP','Syrian Pound (SYP)',0.00000,'2007-07-11 04:35:40','USD'),(133,'TWD','Taiwan Dollar (TWD)',0.03075,'2007-07-11 04:35:40','USD'),(134,'TZS','Tanzanian Shilling (TZS)',0.00083,'2007-07-11 04:35:40','USD'),(135,'THB','Thai Baht (THB)',0.02546,'2007-07-11 04:35:40','USD'),(136,'TOP','Tonga Paanga (TOP)',0.48244,'2007-07-11 04:35:40','USD'),(137,'TTD','Trinidad&Tobago Dollar (TTD)',0.15863,'2007-07-11 04:35:40','USD'),(138,'TND','Tunisian Dinar (TND)',0.73470,'2007-07-11 04:35:40','USD'),(139,'USD','U.S. Dollar (USD)',1.00000,'2007-07-11 04:35:40','USD'),(140,'AED','UAE Dirham (AED)',0.27228,'2007-07-11 04:35:40','USD'),(141,'UGX','Ugandan Shilling (UGX)',0.00055,'2007-07-11 04:35:40','USD'),(142,'UAH','Ukraine Hryvnia (UAH)',0.19755,'2007-07-11 04:35:40','USD'),(143,'UYU','Uruguayan New Peso (UYU)',0.04119,'2007-07-11 04:35:40','USD'),(144,'VUV','Vanuatu Vatu (VUV)',0.00870,'2007-07-11 04:35:40','USD'),(145,'VEB','Venezuelan Bolivar (VEB)',0.00037,'2007-07-11 04:35:40','USD'),(146,'VND','Vietnam Dong (VND)',0.00006,'2007-07-11 04:35:40','USD'),(147,'YER','Yemen Riyal (YER)',0.00510,'2007-07-11 04:35:40','USD'),(148,'ZMK','Zambian Kwacha (ZMK)',0.00031,'2007-07-11 04:35:40','USD'),(149,'ZWD','Zimbabwe Dollar (ZWD)',0.00001,'2007-07-11 04:35:40','USD'),(150,'GYD','Guyana Dollar (GYD)',0.00527,'2007-07-11 04:35:40','USD');
/*!40000 ALTER TABLE `cc_currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_did`
--

DROP TABLE IF EXISTS `cc_did`;
CREATE TABLE `cc_did` (
  `id` bigint(20) NOT NULL auto_increment,
  `id_cc_didgroup` bigint(20) NOT NULL,
  `id_cc_country` int(11) NOT NULL,
  `activated` int(11) NOT NULL default '1',
  `reserved` int(11) default '0',
  `iduser` int(11) NOT NULL default '0',
  `did` char(50) collate utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `startingdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `description` mediumtext collate utf8_bin,
  `secondusedreal` int(11) default '0',
  `billingtype` int(11) default '0',
  `fixrate` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_did_did` (`did`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_did`
--

LOCK TABLES `cc_did` WRITE;
/*!40000 ALTER TABLE `cc_did` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_did` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_did_destination`
--

DROP TABLE IF EXISTS `cc_did_destination`;
CREATE TABLE `cc_did_destination` (
  `id` bigint(20) NOT NULL auto_increment,
  `destination` char(50) collate utf8_bin NOT NULL,
  `priority` int(11) NOT NULL default '0',
  `id_cc_card` bigint(20) NOT NULL,
  `id_cc_did` bigint(20) NOT NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `activated` int(11) NOT NULL default '1',
  `secondusedreal` int(11) default '0',
  `voip_call` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_did_destination`
--

LOCK TABLES `cc_did_destination` WRITE;
/*!40000 ALTER TABLE `cc_did_destination` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_did_destination` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_did_use`
--

DROP TABLE IF EXISTS `cc_did_use`;
CREATE TABLE `cc_did_use` (
  `id` bigint(20) NOT NULL auto_increment,
  `id_cc_card` bigint(20) default NULL,
  `id_did` bigint(20) NOT NULL,
  `reservationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `releasedate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `activated` int(11) default '0',
  `month_payed` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_did_use`
--

LOCK TABLES `cc_did_use` WRITE;
/*!40000 ALTER TABLE `cc_did_use` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_did_use` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_didgroup`
--

DROP TABLE IF EXISTS `cc_didgroup`;
CREATE TABLE `cc_didgroup` (
  `id` bigint(20) NOT NULL auto_increment,
  `iduser` int(11) NOT NULL default '0',
  `didgroupname` char(50) collate utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_didgroup`
--

LOCK TABLES `cc_didgroup` WRITE;
/*!40000 ALTER TABLE `cc_didgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_didgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_ecommerce_product`
--

DROP TABLE IF EXISTS `cc_ecommerce_product`;
CREATE TABLE `cc_ecommerce_product` (
  `id` bigint(20) NOT NULL auto_increment,
  `product_name` varchar(255) collate utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `description` mediumtext collate utf8_bin,
  `expirationdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `enableexpire` int(11) default '0',
  `expiredays` int(11) default '0',
  `mailtype` varchar(50) collate utf8_bin NOT NULL,
  `credit` float NOT NULL default '0',
  `tariff` int(11) default '0',
  `id_didgroup` int(11) default '0',
  `activated` char(1) collate utf8_bin NOT NULL default 'f',
  `simultaccess` int(11) default '0',
  `currency` char(3) collate utf8_bin default 'USD',
  `typepaid` int(11) default '0',
  `creditlimit` int(11) default '0',
  `language` char(5) collate utf8_bin default 'en',
  `runservice` int(11) default '0',
  `sip_friend` int(11) default '0',
  `iax_friend` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_ecommerce_product`
--

LOCK TABLES `cc_ecommerce_product` WRITE;
/*!40000 ALTER TABLE `cc_ecommerce_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_ecommerce_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_epayment_log`
--

DROP TABLE IF EXISTS `cc_epayment_log`;
CREATE TABLE `cc_epayment_log` (
  `id` int(11) NOT NULL auto_increment,
  `cardid` int(11) NOT NULL default '0',
  `amount` float NOT NULL default '0',
  `vat` float NOT NULL default '0',
  `paymentmethod` char(50) collate utf8_bin NOT NULL,
  `cc_owner` varchar(64) collate utf8_bin default NULL,
  `cc_number` varchar(32) collate utf8_bin default NULL,
  `cc_expires` varchar(7) collate utf8_bin default NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_epayment_log`
--

LOCK TABLES `cc_epayment_log` WRITE;
/*!40000 ALTER TABLE `cc_epayment_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_epayment_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_iax_buddies`
--

DROP TABLE IF EXISTS `cc_iax_buddies`;
CREATE TABLE `cc_iax_buddies` (
  `id` int(11) NOT NULL auto_increment,
  `id_cc_card` int(11) NOT NULL default '0',
  `name` char(80) collate utf8_bin NOT NULL default '',
  `accountcode` char(20) collate utf8_bin default NULL,
  `regexten` char(20) collate utf8_bin default NULL,
  `amaflags` char(7) collate utf8_bin default NULL,
  `callgroup` char(10) collate utf8_bin default NULL,
  `callerid` char(80) collate utf8_bin default NULL,
  `canreinvite` char(3) collate utf8_bin default 'yes',
  `context` char(80) collate utf8_bin default NULL,
  `DEFAULTip` char(15) collate utf8_bin default NULL,
  `dtmfmode` char(7) collate utf8_bin NOT NULL default 'RFC2833',
  `fromuser` char(80) collate utf8_bin default NULL,
  `fromdomain` char(80) collate utf8_bin default NULL,
  `host` char(31) collate utf8_bin NOT NULL default '',
  `insecure` char(4) collate utf8_bin default NULL,
  `language` char(2) collate utf8_bin default NULL,
  `mailbox` char(50) collate utf8_bin default NULL,
  `md5secret` char(80) collate utf8_bin default NULL,
  `nat` char(3) collate utf8_bin default 'yes',
  `permit` char(95) collate utf8_bin default NULL,
  `deny` char(95) collate utf8_bin default NULL,
  `mask` char(95) collate utf8_bin default NULL,
  `pickupgroup` char(10) collate utf8_bin default NULL,
  `port` char(5) collate utf8_bin NOT NULL default '',
  `qualify` char(7) collate utf8_bin default 'yes',
  `restrictcid` char(1) collate utf8_bin default NULL,
  `rtptimeout` char(3) collate utf8_bin default NULL,
  `rtpholdtimeout` char(3) collate utf8_bin default NULL,
  `secret` char(80) collate utf8_bin default NULL,
  `type` char(6) collate utf8_bin NOT NULL default 'friend',
  `username` char(80) collate utf8_bin NOT NULL default '',
  `disallow` char(100) collate utf8_bin default 'all',
  `allow` char(100) collate utf8_bin default 'gsm,ulaw,alaw',
  `musiconhold` char(100) collate utf8_bin default NULL,
  `regseconds` int(11) NOT NULL default '0',
  `ipaddr` char(15) collate utf8_bin NOT NULL default '',
  `cancallforward` char(3) collate utf8_bin default 'yes',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_iax_buddies_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_iax_buddies`
--

LOCK TABLES `cc_iax_buddies` WRITE;
/*!40000 ALTER TABLE `cc_iax_buddies` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_iax_buddies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_invoice_history`
--

DROP TABLE IF EXISTS `cc_invoice_history`;
CREATE TABLE `cc_invoice_history` (
  `id` int(11) NOT NULL auto_increment,
  `invoiceid` int(11) NOT NULL,
  `invoicesent_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `invoicestatus` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `ind_cc_invoice_history` (`invoicesent_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_invoice_history`
--

LOCK TABLES `cc_invoice_history` WRITE;
/*!40000 ALTER TABLE `cc_invoice_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_invoice_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_invoices`
--

DROP TABLE IF EXISTS `cc_invoices`;
CREATE TABLE `cc_invoices` (
  `id` int(11) NOT NULL auto_increment,
  `cardid` bigint(20) NOT NULL,
  `orderref` varchar(50) collate utf8_bin default NULL,
  `invoicecreated_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `cover_startdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `cover_enddate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `amount` decimal(15,5) default '0.00000',
  `tax` decimal(15,5) default '0.00000',
  `total` decimal(15,5) default '0.00000',
  `invoicetype` int(11) default NULL,
  `filename` varchar(250) collate utf8_bin default NULL,
  `payment_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `payment_status` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `ind_cc_invoices` (`cover_startdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_invoices`
--

LOCK TABLES `cc_invoices` WRITE;
/*!40000 ALTER TABLE `cc_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_logpayment`
--

DROP TABLE IF EXISTS `cc_logpayment`;
CREATE TABLE `cc_logpayment` (
  `id` int(11) NOT NULL auto_increment,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `payment` float NOT NULL,
  `card_id` bigint(20) NOT NULL,
  `reseller_id` bigint(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_logpayment`
--

LOCK TABLES `cc_logpayment` WRITE;
/*!40000 ALTER TABLE `cc_logpayment` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_logpayment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_logrefill`
--

DROP TABLE IF EXISTS `cc_logrefill`;
CREATE TABLE `cc_logrefill` (
  `id` int(11) NOT NULL auto_increment,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `credit` float NOT NULL,
  `card_id` bigint(20) NOT NULL,
  `reseller_id` bigint(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_logrefill`
--

LOCK TABLES `cc_logrefill` WRITE;
/*!40000 ALTER TABLE `cc_logrefill` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_logrefill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_outbound_cid_group`
--

DROP TABLE IF EXISTS `cc_outbound_cid_group`;
CREATE TABLE `cc_outbound_cid_group` (
  `id` int(11) NOT NULL auto_increment,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `group_name` varchar(70) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_outbound_cid_group`
--

LOCK TABLES `cc_outbound_cid_group` WRITE;
/*!40000 ALTER TABLE `cc_outbound_cid_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_outbound_cid_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_outbound_cid_list`
--

DROP TABLE IF EXISTS `cc_outbound_cid_list`;
CREATE TABLE `cc_outbound_cid_list` (
  `id` int(11) NOT NULL auto_increment,
  `outbound_cid_group` int(11) NOT NULL,
  `cid` char(100) collate utf8_bin default NULL,
  `activated` int(11) NOT NULL default '0',
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_outbound_cid_list`
--

LOCK TABLES `cc_outbound_cid_list` WRITE;
/*!40000 ALTER TABLE `cc_outbound_cid_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_outbound_cid_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_package_offer`
--

DROP TABLE IF EXISTS `cc_package_offer`;
CREATE TABLE `cc_package_offer` (
  `id` bigint(20) NOT NULL auto_increment,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `label` varchar(70) collate utf8_bin NOT NULL,
  `packagetype` int(11) NOT NULL,
  `billingtype` int(11) NOT NULL,
  `startday` int(11) NOT NULL,
  `freetimetocall` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_package_offer`
--

LOCK TABLES `cc_package_offer` WRITE;
/*!40000 ALTER TABLE `cc_package_offer` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_package_offer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_payment_methods`
--

DROP TABLE IF EXISTS `cc_payment_methods`;
CREATE TABLE `cc_payment_methods` (
  `id` int(11) NOT NULL auto_increment,
  `payment_method` char(100) collate utf8_bin NOT NULL,
  `payment_filename` char(200) collate utf8_bin NOT NULL,
  `active` char(1) collate utf8_bin NOT NULL default 'f',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_payment_methods`
--

LOCK TABLES `cc_payment_methods` WRITE;
/*!40000 ALTER TABLE `cc_payment_methods` DISABLE KEYS */;
INSERT INTO `cc_payment_methods` VALUES (1,'paypal','paypal.php','t'),(2,'Authorize.Net','authorizenet.php','t'),(3,'MoneyBookers','moneybookers.php','t');
/*!40000 ALTER TABLE `cc_payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_payments`
--

DROP TABLE IF EXISTS `cc_payments`;
CREATE TABLE `cc_payments` (
  `id` int(11) NOT NULL auto_increment,
  `customers_id` varchar(60) collate utf8_bin NOT NULL,
  `customers_name` varchar(200) collate utf8_bin NOT NULL,
  `customers_email_address` varchar(96) collate utf8_bin NOT NULL,
  `item_name` varchar(127) collate utf8_bin default NULL,
  `item_id` varchar(127) collate utf8_bin default NULL,
  `item_quantity` int(11) NOT NULL default '0',
  `payment_method` varchar(32) collate utf8_bin NOT NULL,
  `cc_type` varchar(20) collate utf8_bin default NULL,
  `cc_owner` varchar(64) collate utf8_bin default NULL,
  `cc_number` varchar(32) collate utf8_bin default NULL,
  `cc_expires` varchar(4) collate utf8_bin default NULL,
  `orders_status` int(5) NOT NULL,
  `orders_amount` decimal(14,6) default NULL,
  `last_modified` datetime default NULL,
  `date_purchased` datetime default NULL,
  `orders_date_finished` datetime default NULL,
  `currency` char(3) collate utf8_bin default NULL,
  `currency_value` decimal(14,6) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_payments`
--

LOCK TABLES `cc_payments` WRITE;
/*!40000 ALTER TABLE `cc_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_payments_status`
--

DROP TABLE IF EXISTS `cc_payments_status`;
CREATE TABLE `cc_payments_status` (
  `id` int(11) NOT NULL auto_increment,
  `status_id` int(11) NOT NULL,
  `status_name` varchar(200) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_payments_status`
--

LOCK TABLES `cc_payments_status` WRITE;
/*!40000 ALTER TABLE `cc_payments_status` DISABLE KEYS */;
INSERT INTO `cc_payments_status` VALUES (1,-2,'Failed'),(2,-1,'Denied'),(3,0,'Pending'),(4,1,'In-Progress'),(5,2,'Completed'),(6,3,'Processed'),(7,4,'Refunded'),(8,5,'Unknown');
/*!40000 ALTER TABLE `cc_payments_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_paypal`
--

DROP TABLE IF EXISTS `cc_paypal`;
CREATE TABLE `cc_paypal` (
  `id` int(11) NOT NULL auto_increment,
  `payer_id` varchar(60) collate utf8_bin default NULL,
  `payment_date` varchar(50) collate utf8_bin default NULL,
  `txn_id` varchar(50) collate utf8_bin default NULL,
  `first_name` varchar(50) collate utf8_bin default NULL,
  `last_name` varchar(50) collate utf8_bin default NULL,
  `payer_email` varchar(75) collate utf8_bin default NULL,
  `payer_status` varchar(50) collate utf8_bin default NULL,
  `payment_type` varchar(50) collate utf8_bin default NULL,
  `memo` tinytext collate utf8_bin,
  `item_name` varchar(127) collate utf8_bin default NULL,
  `item_number` varchar(127) collate utf8_bin default NULL,
  `quantity` int(11) NOT NULL default '0',
  `mc_gross` decimal(9,2) default NULL,
  `mc_fee` decimal(9,2) default NULL,
  `tax` decimal(9,2) default NULL,
  `mc_currency` char(3) collate utf8_bin default NULL,
  `address_name` varchar(255) collate utf8_bin NOT NULL default '',
  `address_street` varchar(255) collate utf8_bin NOT NULL default '',
  `address_city` varchar(255) collate utf8_bin NOT NULL default '',
  `address_state` varchar(255) collate utf8_bin NOT NULL default '',
  `address_zip` varchar(255) collate utf8_bin NOT NULL default '',
  `address_country` varchar(255) collate utf8_bin NOT NULL default '',
  `address_status` varchar(255) collate utf8_bin NOT NULL default '',
  `payer_business_name` varchar(255) collate utf8_bin NOT NULL default '',
  `payment_status` varchar(255) collate utf8_bin NOT NULL default '',
  `pending_reason` varchar(255) collate utf8_bin NOT NULL default '',
  `reason_code` varchar(255) collate utf8_bin NOT NULL default '',
  `txn_type` varchar(255) collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `txn_id` (`txn_id`),
  KEY `txn_id_2` (`txn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_paypal`
--

LOCK TABLES `cc_paypal` WRITE;
/*!40000 ALTER TABLE `cc_paypal` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_paypal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_phonelist`
--

DROP TABLE IF EXISTS `cc_phonelist`;
CREATE TABLE `cc_phonelist` (
  `id` int(11) NOT NULL auto_increment,
  `id_cc_campaign` int(11) NOT NULL default '0',
  `numbertodial` char(50) collate utf8_bin NOT NULL,
  `name` char(60) collate utf8_bin NOT NULL,
  `inuse` int(11) default '0',
  `enable` int(11) NOT NULL default '1',
  `num_trials_done` int(11) default '0',
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_attempt` timestamp NOT NULL default '0000-00-00 00:00:00',
  `secondusedreal` int(11) default '0',
  `additionalinfo` mediumtext collate utf8_bin,
  PRIMARY KEY  (`id`),
  KEY `ind_cc_phonelist_numbertodial` (`numbertodial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_phonelist`
--

LOCK TABLES `cc_phonelist` WRITE;
/*!40000 ALTER TABLE `cc_phonelist` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_phonelist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_prefix`
--

DROP TABLE IF EXISTS `cc_prefix`;
CREATE TABLE `cc_prefix` (
  `id` bigint(20) NOT NULL auto_increment,
  `prefixe` varchar(50) collate utf8_bin NOT NULL,
  `destination` varchar(100) collate utf8_bin NOT NULL,
  `id_cc_country` bigint(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=284 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_prefix`
--

LOCK TABLES `cc_prefix` WRITE;
/*!40000 ALTER TABLE `cc_prefix` DISABLE KEYS */;
INSERT INTO `cc_prefix` VALUES (1,'93','Afghanistan',1),(2,'355','Albania',2),(3,'213','Algeria',3),(4,'684','American Samoa',4),(5,'376','Andorra',5),(6,'244','Angola',6),(7,'1264','Anguilla',7),(8,'672','Antarctica',8),(9,'1268','Antigua',9),(10,'54','Argentina',10),(11,'374','Armenia',11),(12,'297','Aruba',12),(13,'247','Ascension',NULL),(14,'61','Australia',13),(15,'672','Australian External Territories',13),(16,'43','Austria',14),(17,'994','Azerbaijan',15),(18,'1242','Bahamas',16),(19,'973','Bahrain',17),(20,'880','Bangladesh',18),(21,'1246','Barbados',19),(22,'1268','Barbuda',NULL),(23,'375','Belarus',20),(24,'32','Belgium',21),(25,'501','Belize',22),(26,'229','Benin',23),(27,'1441','Bermuda',24),(28,'975','Bhutan',25),(29,'591','Bolivia',26),(30,'387','Bosnia & Herzegovina',27),(31,'267','Botswana',28),(32,'55','Brazil',30),(33,'5514','Brasil Telecom',30),(34,'5515','Brazil Telefonica',30),(35,'5521','Brazil Embratel',30),(36,'5523','Brazil Intelig',30),(37,'5531','Brazil Telemar',30),(38,'550','Brazil mobile phones',30),(39,'1284','British Virgin Islands',31),(40,'673','Brunei Darussalam',32),(41,'359','Bulgaria',33),(42,'226','Burkina Faso',34),(43,'257','Burundi',35),(44,'855','Cambodia',36),(45,'237','Cameroon',37),(46,'1','Canada',38),(47,'238','Cape Verde Islands',39),(48,'1345','Cayman Islands',40),(49,'236','Central African Republic',41),(50,'235','Chad',42),(51,'64','Chatham Island (New Zealand)',NULL),(52,'56','Chile',43),(53,'86','China (PRC)',44),(54,'618','Christmas Island',45),(55,'61','Cocos-Keeling Islands',46),(56,'57','Colombia',47),(57,'573','Colombia Mobile Phones',47),(58,'575','Colombia Orbitel',47),(59,'577','Colombia ETB',47),(60,'579','Colombia Telecom',47),(61,'269','Comoros',48),(62,'242','Congo',49),(63,'243','Congo, Dem. Rep. of  (former Zaire)',NULL),(64,'682','Cook Islands',51),(65,'506','Costa Rica',52),(66,'225','CÃ´te d\'Ivoire (Ivory Coast)',53),(67,'385','Croatia',54),(68,'53','Cuba',55),(69,'5399','Cuba (Guantanamo Bay)',55),(70,'599','CuraÃ¢o',NULL),(71,'357','Cyprus',56),(72,'420','Czech Republic',57),(73,'45','Denmark',58),(74,'246','Diego Garcia',241),(75,'253','Djibouti',59),(76,'1767','Dominica',60),(77,'1809','Dominican Republic',61),(78,'670','East Timor',211),(79,'56','Easter Island',NULL),(80,'593','Ecuador',62),(81,'20','Egypt',63),(82,'503','El Salvador',64),(83,'8812','Ellipso (Mobile Satellite service)',NULL),(84,'88213','EMSAT (Mobile Satellite service)',NULL),(85,'240','Equatorial Guinea',65),(86,'291','Eritrea',66),(87,'372','Estonia',67),(88,'251','Ethiopia',68),(89,'500','Falkland Islands (Malvinas)',69),(90,'298','Faroe Islands',70),(91,'679','Fiji Islands',71),(92,'358','Finland',72),(93,'33','France',73),(94,'596','French Antilles',74),(95,'594','French Guiana',75),(96,'689','French Polynesia',76),(97,'241','Gabonese Republic',77),(98,'220','Gambia',78),(99,'995','Georgia',79),(100,'49','Germany',80),(101,'233','Ghana',81),(102,'350','Gibraltar',82),(103,'881','Global Mobile Satellite System (GMSS)',NULL),(104,'8810-8811','ICO Global',NULL),(105,'8812-8813','Ellipso',NULL),(106,'8816-8817','Iridium',NULL),(107,'8818-8819','Globalstar',NULL),(108,'8818-8819','Globalstar (Mobile Satellite Service)',NULL),(109,'30','Greece',83),(110,'299','Greenland',84),(111,'1473','Grenada',85),(112,'590','Guadeloupe',86),(113,'1671','Guam',87),(114,'5399','Guantanamo Bay',NULL),(115,'502','Guatemala',88),(116,'245','Guinea-Bissau',90),(117,'224','Guinea',89),(118,'592','Guyana',91),(119,'509','Haiti',92),(120,'504','Honduras',95),(121,'852','Hong Kong',96),(122,'36','Hungary',97),(123,'8810-8811','ICO Global (Mobile Satellite Service)',NULL),(124,'354','Iceland',98),(125,'91','India',99),(126,'62','Indonesia',100),(127,'871','Inmarsat (Atlantic Ocean - East)',242),(128,'874','Inmarsat (Atlantic Ocean - West)',242),(129,'873','Inmarsat (Indian Ocean)',242),(130,'872','Inmarsat (Pacific Ocean)',242),(131,'870','Inmarsat SNAC',242),(132,'800','International Freephone Service',NULL),(133,'808','International Shared Cost Service (ISCS)',NULL),(134,'98','Iran',101),(135,'964','Iraq',102),(136,'353','Ireland',103),(137,'8816-8817','Iridium (Mobile Satellite service)',NULL),(138,'972','Israel',104),(139,'39','Italy',105),(140,'1876','Jamaica',106),(141,'81','Japan',107),(142,'962','Jordan',108),(143,'7','Kazakhstan',109),(144,'254','Kenya',110),(145,'686','Kiribati',111),(146,'850','Korea (North)',112),(147,'82','Korea (South)',113),(148,'965','Kuwait',114),(149,'996','Kyrgyz Republic',115),(150,'856','Laos',116),(151,'371','Latvia',117),(152,'961','Lebanon',118),(153,'266','Lesotho',119),(154,'231','Liberia',120),(155,'218','Libya',121),(156,'423','Liechtenstein',122),(157,'370','Lithuania',123),(158,'352','Luxembourg',124),(159,'853','Macao',125),(160,'389','Macedonia (Former Yugoslav Rep of.)',126),(161,'261','Madagascar',127),(162,'265','Malawi',128),(163,'60','Malaysia',129),(164,'960','Maldives',130),(165,'223','Mali Republic',131),(166,'356','Malta',132),(167,'692','Marshall Islands',133),(168,'596','Martinique',134),(169,'222','Mauritania',135),(170,'230','Mauritius',136),(171,'269','Mayotte Island',137),(172,'52','Mexico',138),(173,'691','Micronesia, (Federal States of)',139),(174,'1808','Midway Island',NULL),(175,'373','Moldova',140),(176,'377','Monaco',141),(177,'976','Mongolia',142),(178,'1664','Montserrat',143),(179,'212','Morocco',144),(180,'258','Mozambique',145),(181,'95','Myanmar',146),(182,'264','Namibia',147),(183,'674','Nauru',148),(184,'977','Nepal',149),(185,'31','Netherlands',150),(186,'599','Netherlands Antilles',151),(187,'1869','Nevis',NULL),(188,'687','New Caledonia',152),(189,'64','New Zealand',153),(190,'505','Nicaragua',154),(191,'227','Niger',155),(192,'234','Nigeria',156),(193,'683','Niue',157),(194,'672','Norfolk Island',158),(195,'1670','Northern Marianas Islands(Saipan, Rota, & Tinian)',159),(196,'47','Norway',160),(197,'968','Oman',161),(198,'92','Pakistan',162),(199,'680','Palau',163),(200,'970','Palestinian Settlements',164),(201,'507','Panama',165),(202,'675','Papua New Guinea',166),(203,'595','Paraguay',167),(204,'51','Peru',168),(205,'63','Philippines',169),(206,'48','Poland',171),(207,'351','Portugal',172),(208,'1787','Puerto Rico',173),(209,'974','Qatar',174),(210,'262','RÃ©union Island',175),(211,'40','Romania',176),(212,'7','Russia',177),(213,'250','Rwandese Republic',178),(214,'290','St. Helena',179),(215,'1869','St. Kitts/Nevis',180),(216,'1758','St. Lucia',181),(217,'508','St. Pierre & Miquelon',182),(218,'1784','St. Vincent & Grenadines',183),(219,'378','San Marino',185),(220,'239','SÃ£o TomÃ© and Principe',186),(221,'966','Saudi Arabia',187),(222,'221','Senegal',188),(223,'381','Serbia and Montenegro',NULL),(224,'248','Seychelles Republic',189),(225,'232','Sierra Leone',190),(226,'65','Singapore',191),(227,'421','Slovak Republic',192),(228,'386','Slovenia',193),(229,'677','Solomon Islands',194),(230,'252','Somali Democratic Republic',195),(231,'27','South Africa',196),(232,'34','Spain',198),(233,'94','Sri Lanka',199),(234,'249','Sudan',200),(235,'597','Suriname',201),(236,'268','Swaziland',203),(237,'46','Sweden',204),(238,'41','Switzerland',205),(239,'963','Syria',206),(240,'886','Taiwan',207),(241,'992','Tajikistan',208),(242,'255','Tanzania',209),(243,'66','Thailand',210),(244,'88216','Thuraya (Mobile Satellite service)',NULL),(245,'228','Togolese Republic',212),(246,'690','Tokelau',213),(247,'676','Tonga Islands',214),(248,'1868','Trinidad & Tobago',215),(249,'216','Tunisia',216),(250,'90','Turkey',217),(251,'993','Turkmenistan',218),(252,'1649','Turks and Caicos Islands',219),(253,'688','Tuvalu',220),(254,'256','Uganda',221),(255,'380','Ukraine',222),(256,'971','United Arab Emirates',223),(257,'44','United Kingdom',224),(258,'1','United States of America',225),(259,'1340','US Virgin Islands',225),(260,'878','Universal Personal Telecommunications (UPT)',NULL),(261,'598','Uruguay',227),(262,'998','Uzbekistan',228),(263,'678','Vanuatu',229),(264,'39','Vatican City',NULL),(265,'58','Venezuela',230),(266,'58102','Venezuela Etelix',230),(267,'58107','Venezuela http://www.multiphone.net.ve',230),(268,'58110','Venezuela CANTV',230),(269,'58111','Venezuela Convergence Comunications',230),(270,'58114','Venezuela Telcel, C.A.',230),(271,'58119','Venezuela Totalcom Venezuela',230),(272,'58123','Venezuela Orbitel de Venezuela, C.A. ENTEL Venezuela',230),(273,'58150','Venezuela LD Telecomunicaciones, C.A.',230),(274,'58133','Venezuela Telecomunicaciones NGTV',230),(275,'58199','Venezuela Veninfotel Comunicaciones',230),(276,'84','Vietnam',231),(277,'808','Wake Island',NULL),(278,'681','Wallis and Futuna Islands',NULL),(279,'685','Western Samoa',184),(280,'967','Yemen',236),(281,'260','Zambia',238),(282,'255','Zanzibar',NULL),(283,'263','Zimbabwe',239);
/*!40000 ALTER TABLE `cc_prefix` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_provider`
--

DROP TABLE IF EXISTS `cc_provider`;
CREATE TABLE `cc_provider` (
  `id` int(11) NOT NULL auto_increment,
  `provider_name` char(30) collate utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `description` mediumtext collate utf8_bin,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_provider_provider_name` (`provider_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_provider`
--

LOCK TABLES `cc_provider` WRITE;
/*!40000 ALTER TABLE `cc_provider` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_ratecard`
--

DROP TABLE IF EXISTS `cc_ratecard`;
CREATE TABLE `cc_ratecard` (
  `id` int(11) NOT NULL auto_increment,
  `idtariffplan` int(11) NOT NULL default '0',
  `dialprefix` char(30) collate utf8_bin NOT NULL,
  `destination` char(50) collate utf8_bin NOT NULL,
  `buyrate` float NOT NULL default '0',
  `buyrateinitblock` int(11) NOT NULL default '0',
  `buyrateincrement` int(11) NOT NULL default '0',
  `rateinitial` float NOT NULL default '0',
  `initblock` int(11) NOT NULL default '0',
  `billingblock` int(11) NOT NULL default '0',
  `connectcharge` float NOT NULL default '0',
  `disconnectcharge` float NOT NULL default '0',
  `stepchargea` float NOT NULL default '0',
  `chargea` float NOT NULL default '0',
  `timechargea` int(11) NOT NULL default '0',
  `billingblocka` int(11) NOT NULL default '0',
  `stepchargeb` float NOT NULL default '0',
  `chargeb` float NOT NULL default '0',
  `timechargeb` int(11) NOT NULL default '0',
  `billingblockb` int(11) NOT NULL default '0',
  `stepchargec` float NOT NULL default '0',
  `chargec` float NOT NULL default '0',
  `timechargec` int(11) NOT NULL default '0',
  `billingblockc` int(11) NOT NULL default '0',
  `startdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `stopdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `starttime` smallint(5) unsigned default '0',
  `endtime` smallint(5) unsigned default '10079',
  `id_trunk` int(11) default '-1',
  `musiconhold` char(100) collate utf8_bin NOT NULL,
  `freetimetocall_package_offer` int(11) NOT NULL default '0',
  `id_outbound_cidgroup` int(11) default '-1',
  PRIMARY KEY  (`id`),
  KEY `ind_cc_ratecard_dialprefix` (`dialprefix`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_ratecard`
--

LOCK TABLES `cc_ratecard` WRITE;
/*!40000 ALTER TABLE `cc_ratecard` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_ratecard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_server_group`
--

DROP TABLE IF EXISTS `cc_server_group`;
CREATE TABLE `cc_server_group` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(60) collate utf8_bin default NULL,
  `description` mediumtext collate utf8_bin,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_server_group`
--

LOCK TABLES `cc_server_group` WRITE;
/*!40000 ALTER TABLE `cc_server_group` DISABLE KEYS */;
INSERT INTO `cc_server_group` VALUES (1,'default','default group of server');
/*!40000 ALTER TABLE `cc_server_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_server_manager`
--

DROP TABLE IF EXISTS `cc_server_manager`;
CREATE TABLE `cc_server_manager` (
  `id` bigint(20) NOT NULL auto_increment,
  `id_group` int(11) default '1',
  `server_ip` varchar(40) collate utf8_bin default NULL,
  `manager_host` varchar(50) collate utf8_bin default NULL,
  `manager_username` varchar(50) collate utf8_bin default NULL,
  `manager_secret` varchar(50) collate utf8_bin default NULL,
  `lasttime_used` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_server_manager`
--

LOCK TABLES `cc_server_manager` WRITE;
/*!40000 ALTER TABLE `cc_server_manager` DISABLE KEYS */;
INSERT INTO `cc_server_manager` VALUES (1,1,'localhost','localhost','myasterisk','mycode','2007-07-11 04:35:40');
/*!40000 ALTER TABLE `cc_server_manager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_service`
--

DROP TABLE IF EXISTS `cc_service`;
CREATE TABLE `cc_service` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` char(100) collate utf8_bin NOT NULL,
  `amount` float NOT NULL,
  `period` int(11) NOT NULL default '1',
  `rule` int(11) NOT NULL default '0',
  `daynumber` int(11) NOT NULL default '0',
  `stopmode` int(11) NOT NULL default '0',
  `maxnumbercycle` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `numberofrun` int(11) NOT NULL default '0',
  `datecreate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `datelastrun` timestamp NOT NULL default '0000-00-00 00:00:00',
  `emailreport` char(100) collate utf8_bin NOT NULL,
  `totalcredit` float NOT NULL default '0',
  `totalcardperform` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_service`
--

LOCK TABLES `cc_service` WRITE;
/*!40000 ALTER TABLE `cc_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_service_report`
--

DROP TABLE IF EXISTS `cc_service_report`;
CREATE TABLE `cc_service_report` (
  `id` bigint(20) NOT NULL auto_increment,
  `cc_service_id` bigint(20) NOT NULL,
  `daterun` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `totalcardperform` int(11) default NULL,
  `totalcredit` float default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_service_report`
--

LOCK TABLES `cc_service_report` WRITE;
/*!40000 ALTER TABLE `cc_service_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_service_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_sip_buddies`
--

DROP TABLE IF EXISTS `cc_sip_buddies`;
CREATE TABLE `cc_sip_buddies` (
  `id` int(11) NOT NULL auto_increment,
  `id_cc_card` int(11) NOT NULL default '0',
  `name` char(80) collate utf8_bin NOT NULL default '',
  `accountcode` char(20) collate utf8_bin default NULL,
  `regexten` char(20) collate utf8_bin default NULL,
  `amaflags` char(7) collate utf8_bin default NULL,
  `callgroup` char(10) collate utf8_bin default NULL,
  `callerid` char(80) collate utf8_bin default NULL,
  `canreinvite` char(3) collate utf8_bin default 'yes',
  `context` char(80) collate utf8_bin default NULL,
  `DEFAULTip` char(15) collate utf8_bin default NULL,
  `dtmfmode` char(7) collate utf8_bin NOT NULL default 'RFC2833',
  `fromuser` char(80) collate utf8_bin default NULL,
  `fromdomain` char(80) collate utf8_bin default NULL,
  `host` char(31) collate utf8_bin NOT NULL default '',
  `insecure` char(4) collate utf8_bin default NULL,
  `language` char(2) collate utf8_bin default NULL,
  `mailbox` char(50) collate utf8_bin default NULL,
  `md5secret` char(80) collate utf8_bin default NULL,
  `nat` char(3) collate utf8_bin default 'yes',
  `permit` char(95) collate utf8_bin default NULL,
  `deny` char(95) collate utf8_bin default NULL,
  `mask` char(95) collate utf8_bin default NULL,
  `pickupgroup` char(10) collate utf8_bin default NULL,
  `port` char(5) collate utf8_bin NOT NULL default '',
  `qualify` char(7) collate utf8_bin default 'yes',
  `restrictcid` char(1) collate utf8_bin default NULL,
  `rtptimeout` char(3) collate utf8_bin default NULL,
  `rtpholdtimeout` char(3) collate utf8_bin default NULL,
  `secret` char(80) collate utf8_bin default NULL,
  `type` char(6) collate utf8_bin NOT NULL default 'friend',
  `username` char(80) collate utf8_bin NOT NULL default '',
  `disallow` char(100) collate utf8_bin default 'all',
  `allow` char(100) collate utf8_bin default 'gsm,ulaw,alaw',
  `musiconhold` char(100) collate utf8_bin default NULL,
  `regseconds` int(11) NOT NULL default '0',
  `ipaddr` char(15) collate utf8_bin NOT NULL default '',
  `cancallforward` char(3) collate utf8_bin default 'yes',
  `fullcontact` varchar(80) collate utf8_bin default NULL,
  `setvar` varchar(100) collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_sip_buddies_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_sip_buddies`
--

LOCK TABLES `cc_sip_buddies` WRITE;
/*!40000 ALTER TABLE `cc_sip_buddies` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_sip_buddies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_speeddial`
--

DROP TABLE IF EXISTS `cc_speeddial`;
CREATE TABLE `cc_speeddial` (
  `id` bigint(20) NOT NULL auto_increment,
  `id_cc_card` bigint(20) NOT NULL default '0',
  `phone` varchar(100) collate utf8_bin NOT NULL,
  `name` varchar(100) collate utf8_bin NOT NULL,
  `speeddial` int(11) default '0',
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_speeddial_id_cc_card_speeddial` (`id_cc_card`,`speeddial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_speeddial`
--

LOCK TABLES `cc_speeddial` WRITE;
/*!40000 ALTER TABLE `cc_speeddial` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_speeddial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_subscription_fee`
--

DROP TABLE IF EXISTS `cc_subscription_fee`;
CREATE TABLE `cc_subscription_fee` (
  `id` bigint(20) NOT NULL auto_increment,
  `label` text collate utf8_bin NOT NULL,
  `fee` float NOT NULL default '0',
  `currency` char(3) collate utf8_bin default 'USD',
  `status` int(11) NOT NULL default '0',
  `numberofrun` int(11) NOT NULL default '0',
  `datecreate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `datelastrun` timestamp NOT NULL default '0000-00-00 00:00:00',
  `emailreport` text collate utf8_bin,
  `totalcredit` float NOT NULL default '0',
  `totalcardperform` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_subscription_fee`
--

LOCK TABLES `cc_subscription_fee` WRITE;
/*!40000 ALTER TABLE `cc_subscription_fee` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_subscription_fee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_system_log`
--

DROP TABLE IF EXISTS `cc_system_log`;
CREATE TABLE `cc_system_log` (
  `id` int(11) NOT NULL auto_increment,
  `iduser` int(11) NOT NULL default '0',
  `loglevel` int(11) NOT NULL default '0',
  `action` text collate utf8_bin NOT NULL,
  `description` mediumtext collate utf8_bin,
  `data` blob,
  `tablename` varchar(255) collate utf8_bin default NULL,
  `pagename` varchar(255) collate utf8_bin default NULL,
  `ipaddress` varchar(255) collate utf8_bin default NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=137 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_system_log`
--

LOCK TABLES `cc_system_log` WRITE;
/*!40000 ALTER TABLE `cc_system_log` DISABLE KEYS */;
INSERT INTO `cc_system_log` VALUES (1,0,1,'Page Visit','User Visited the Page','','','index.php','190.10.152.140','2007-07-11 05:08:45'),(2,0,1,'Page Visit','User Visited the Page','','','PP_intro.php','190.10.152.140','2007-07-11 05:09:02'),(3,2,1,'User Logged In','User Logged in to website','','','PP_Intro.php','190.10.152.140','2007-07-11 05:09:02'),(4,2,1,'Page Visit','User Visited the Page','','','PP_intro.php','190.10.152.140','2007-07-11 05:10:03'),(5,2,1,'User Logged In','User Logged in to website','','','PP_Intro.php','190.10.152.140','2007-07-11 05:10:03'),(6,2,1,'Page Visit','User Visited the Page','','','A2B_entity_card.php','190.10.152.140','2007-07-11 05:10:24'),(7,2,1,'Page Visit','User Visited the Page','','','A2B_entity_card.php','190.10.152.140','2007-07-11 05:11:17'),(8,2,1,'Page Visit','User Visited the Page','','','CC_card_import.php','190.10.152.140','2007-07-11 05:11:26'),(9,2,1,'Page Visit','User Visited the Page','','','A2B_entity_card_multi.php','190.10.152.140','2007-07-11 05:11:35'),(10,2,1,'Page Visit','User Visited the Page','','','A2B_entity_card_multi.php','190.10.152.140','2007-07-11 05:11:47'),(11,2,1,'Page Visit','User Visited the Page','','','A2B_entity_card.php','190.10.152.140','2007-07-11 05:13:34'),(12,2,1,'Page Visit','User Visited the Page','','','A2B_entity_card.php','190.10.152.140','2007-07-11 05:13:39'),(13,2,1,'Page Visit','User Visited the Page','','','CC_card_import.php','190.10.152.140','2007-07-11 05:13:45'),(14,2,1,'Page Visit','User Visited the Page','','','A2B_entity_card_multi.php','190.10.152.140','2007-07-11 05:13:56'),(15,2,1,'Page Visit','User Visited the Page','','','A2B_entity_friend.php','190.10.152.140','2007-07-11 05:14:01'),(16,2,1,'Page Visit','User Visited the Page','','','A2B_entity_friend.php','190.10.152.140','2007-07-11 05:14:23'),(17,2,1,'Page Visit','User Visited the Page','','','A2B_entity_friend.php','190.10.152.140','2007-07-11 05:14:43'),(18,2,1,'Page Visit','User Visited the Page','','','A2B_entity_friend.php','190.10.152.140','2007-07-11 05:14:47'),(19,2,1,'Page Visit','User Visited the Page','','','A2B_entity_friend.php','190.10.152.140','2007-07-11 05:14:56'),(20,2,1,'Page Visit','User Visited the Page','','','A2B_entity_friend.php','190.10.152.140','2007-07-11 05:15:08'),(21,2,1,'Page Visit','User Visited the Page','','','A2B_entity_callerid.php','190.10.152.140','2007-07-11 05:15:15'),(22,2,1,'Page Visit','User Visited the Page','','','A2B_entity_speeddial.php','190.10.152.140','2007-07-11 05:15:21'),(23,2,1,'Page Visit','User Visited the Page','','','A2B_entity_speeddial.php','190.10.152.140','2007-07-11 05:15:29'),(24,2,1,'Page Visit','User Visited the Page','','','A2B_entity_speeddial.php','190.10.152.140','2007-07-11 05:15:29'),(25,2,1,'Page Visit','User Visited the Page','','','A2B_entity_speeddial.php','190.10.152.140','2007-07-11 05:16:10'),(26,2,1,'Page Visit','User Visited the Page','','','A2B_entity_transactions.php','190.10.152.140','2007-07-11 05:16:19'),(27,2,1,'Page Visit','User Visited the Page','','','A2B_entity_payment_configuration.php','190.10.152.140','2007-07-11 05:16:27'),(28,2,1,'Page Visit','User Visited the Page','','','A2B_entity_moneysituation.php','190.10.152.140','2007-07-11 05:16:32'),(29,2,1,'Page Visit','User Visited the Page','','','A2B_entity_payment_configuration.php','190.10.152.140','2007-07-11 05:16:41'),(30,2,1,'Page Visit','User Visited the Page','','','A2B_entity_moneysituation.php','190.10.152.140','2007-07-11 05:16:46'),(31,2,1,'Page Visit','User Visited the Page','','','A2B_entity_payment.php','190.10.152.140','2007-07-11 05:16:50'),(32,2,1,'Page Visit','User Visited the Page','','','A2B_entity_payment.php','190.10.152.140','2007-07-11 05:16:54'),(33,2,1,'Page Visit','User Visited the Page','','','A2B_entity_voucher.php','190.10.152.140','2007-07-11 05:16:57'),(34,2,1,'Page Visit','User Visited the Page','','','A2B_entity_voucher.php','190.10.152.140','2007-07-11 05:17:01'),(35,2,1,'Page Visit','User Visited the Page','','','A2B_entity_voucher_multi.php','190.10.152.140','2007-07-11 05:17:06'),(36,2,1,'Page Visit','User Visited the Page','','','A2B_currencies.php','190.10.152.140','2007-07-11 05:17:11'),(37,2,1,'Page Visit','User Visited the Page','','','A2B_entity_charge.php','190.10.152.140','2007-07-11 05:17:15'),(38,2,1,'Page Visit','User Visited the Page','','','A2B_entity_charge.php','190.10.152.140','2007-07-11 05:17:19'),(39,2,1,'Page Visit','User Visited the Page','','','A2B_entity_ecommerce.php','190.10.152.140','2007-07-11 05:17:27'),(40,2,1,'Page Visit','User Visited the Page','','','A2B_entity_ecommerce.php','190.10.152.140','2007-07-11 05:17:31'),(41,2,1,'Page Visit','User Visited the Page','','','A2B_entity_tariffgroup.php','190.10.152.140','2007-07-11 05:20:22'),(42,2,1,'Page Visit','User Visited the Page','','','A2B_entity_tariffgroup.php','190.10.152.140','2007-07-11 05:20:27'),(43,2,1,'Page Visit','User Visited the Page','','','A2B_entity_tariffplan.php','190.10.152.140','2007-07-11 05:20:31'),(44,2,1,'Page Visit','User Visited the Page','','','A2B_entity_tariffplan.php','190.10.152.140','2007-07-11 05:20:36'),(45,2,1,'Page Visit','User Visited the Page','','','A2B_entity_tariffplan.php','190.10.152.140','2007-07-11 05:20:46'),(46,2,1,'Page Visit','User Visited the Page','','','A2B_entity_def_ratecard.php','190.10.152.140','2007-07-11 05:20:51'),(47,2,1,'Page Visit','User Visited the Page','','','A2B_entity_def_ratecard.php','190.10.152.140','2007-07-11 05:20:56'),(48,2,1,'Page Visit','User Visited the Page','','','CC_ratecard_import.php','190.10.152.140','2007-07-11 05:21:02'),(49,2,1,'Page Visit','User Visited the Page','','','CC_entity_sim_ratecard.php','190.10.152.140','2007-07-11 05:21:07'),(50,2,1,'Page Visit','User Visited the Page','','','A2B_entity_def_ratecard.php','190.10.152.140','2007-07-11 05:21:42'),(51,2,1,'Page Visit','User Visited the Page','','','A2B_entity_def_ratecard.php','190.10.152.140','2007-07-11 05:21:46'),(52,2,1,'Page Visit','User Visited the Page','','','CC_ratecard_import.php','190.10.152.140','2007-07-11 05:21:50'),(53,2,1,'Page Visit','User Visited the Page','','','CC_entity_sim_ratecard.php','190.10.152.140','2007-07-11 05:21:55'),(54,2,1,'Page Visit','User Visited the Page','','','A2B_entity_package.php','190.10.152.140','2007-07-11 05:22:18'),(55,2,1,'Page Visit','User Visited the Page','','','CC_entity_sim_ratecard.php','190.10.152.140','2007-07-11 05:22:19'),(56,2,1,'Page Visit','User Visited the Page','','','A2B_entity_package.php','190.10.152.140','2007-07-11 05:22:31'),(57,2,1,'Page Visit','User Visited the Page','','','A2B_entity_package.php','190.10.152.140','2007-07-11 05:22:38'),(58,2,1,'Page Visit','User Visited the Page','','','A2B_detail_package.php','190.10.152.140','2007-07-11 05:22:39'),(59,2,1,'Page Visit','User Visited the Page','','','A2B_detail_package.php','190.10.152.140','2007-07-11 05:22:44'),(60,2,1,'Page Visit','User Visited the Page','','','A2B_entity_outbound_cidgroup.php','190.10.152.140','2007-07-11 05:22:53'),(61,2,1,'Page Visit','User Visited the Page','','','A2B_entity_outbound_cidgroup.php','190.10.152.140','2007-07-11 05:22:58'),(62,2,1,'Page Visit','User Visited the Page','','','A2B_entity_outbound_cid.php','190.10.152.140','2007-07-11 05:23:05'),(63,2,1,'Page Visit','User Visited the Page','','','A2B_entity_outbound_cid.php','190.10.152.140','2007-07-11 05:23:14'),(64,2,1,'Page Visit','User Visited the Page','','','A2B_entity_trunk.php','190.10.152.140','2007-07-11 05:23:34'),(65,2,1,'Page Visit','User Visited the Page','','','A2B_entity_trunk.php','190.10.152.140','2007-07-11 05:25:08'),(66,2,1,'Page Visit','User Visited the Page','','','A2B_entity_provider.php','190.10.152.140','2007-07-11 05:25:12'),(67,2,1,'Page Visit','User Visited the Page','','','A2B_entity_provider.php','190.10.152.140','2007-07-11 05:25:16'),(68,2,1,'Page Visit','User Visited the Page','','','A2B_entity_didgroup.php','190.10.152.140','2007-07-11 05:25:23'),(69,2,1,'Page Visit','User Visited the Page','','','A2B_entity_didgroup.php','190.10.152.140','2007-07-11 05:25:28'),(70,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did.php','190.10.152.140','2007-07-11 05:25:33'),(71,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did.php','190.10.152.140','2007-07-11 05:25:37'),(72,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did_import.php','190.10.152.140','2007-07-11 05:25:42'),(73,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did_destination.php','190.10.152.140','2007-07-11 05:25:49'),(74,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did.php','190.10.152.140','2007-07-11 05:26:10'),(75,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did_import.php','190.10.152.140','2007-07-11 05:26:15'),(76,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did_destination.php','190.10.152.140','2007-07-11 05:26:20'),(77,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did_destination.php','190.10.152.140','2007-07-11 05:26:25'),(78,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did_billing.php','190.10.152.140','2007-07-11 05:26:34'),(79,2,1,'Page Visit','User Visited the Page','','','A2B_entity_did_use.php','190.10.152.140','2007-07-11 05:26:38'),(80,2,1,'Page Visit','User Visited the Page','','','call-log-customers.php','190.10.152.140','2007-07-11 05:26:53'),(81,2,1,'Page Visit','User Visited the Page','','','call-comp.php','190.10.152.140','2007-07-11 05:27:00'),(82,2,1,'Page Visit','User Visited the Page','','','call-last-month.php','190.10.152.140','2007-07-11 05:27:07'),(83,2,1,'Page Visit','User Visited the Page','','','call-daily-load.php','190.10.152.140','2007-07-11 05:27:13'),(84,2,1,'Page Visit','User Visited the Page','','','call-count-reporting.php','190.10.152.140','2007-07-11 05:27:17'),(85,2,1,'Page Visit','User Visited the Page','','','A2B_entity_view_invoice.php','190.10.152.140','2007-07-11 05:27:35'),(86,2,1,'Page Visit','User Visited the Page','','','A2B_entity_create_invoice.php','190.10.152.140','2007-07-11 05:27:40'),(87,2,1,'Page Visit','User Visited the Page','','','invoices.php','190.10.152.140','2007-07-11 05:27:44'),(88,2,1,'Page Visit','User Visited the Page','','','invoices.php','190.10.152.140','2007-07-11 05:27:55'),(89,2,1,'Page Visit','User Visited the Page','','','invoices_customer.php','190.10.152.140','2007-07-11 05:28:01'),(90,2,1,'Page Visit','User Visited the Page','','','A2B_entity_invoices.php','190.10.152.140','2007-07-11 05:28:19'),(91,2,1,'Page Visit','User Visited the Page','','','A2B_entity_autorefill.php','190.10.152.140','2007-07-11 05:28:41'),(92,2,1,'Page Visit','User Visited the Page','','','A2B_entity_service.php','190.10.152.140','2007-07-11 05:28:46'),(93,2,1,'Page Visit','User Visited the Page','','','A2B_entity_service.php','190.10.152.140','2007-07-11 05:28:51'),(94,2,1,'Page Visit','User Visited the Page','','','A2B_entity_alarm.php','190.10.152.140','2007-07-11 05:28:56'),(95,2,1,'Page Visit','User Visited the Page','','','A2B_entity_alarm.php','190.10.152.140','2007-07-11 05:29:00'),(96,2,1,'Page Visit','User Visited the Page','','','A2B_entity_subscription.php','190.10.152.140','2007-07-11 05:29:05'),(97,2,1,'Page Visit','User Visited the Page','','','A2B_entity_subscription.php','190.10.152.140','2007-07-11 05:29:21'),(98,2,1,'Page Visit','User Visited the Page','','','A2B_entity_callback.php','190.10.152.140','2007-07-11 05:29:31'),(99,2,1,'Page Visit','User Visited the Page','','','A2B_entity_callback.php','190.10.152.140','2007-07-11 05:29:49'),(100,2,1,'Page Visit','User Visited the Page','','','A2B_entity_callback.php','190.10.152.140','2007-07-11 05:30:02'),(101,2,1,'Page Visit','User Visited the Page','','','A2B_entity_server_group.php','190.10.152.140','2007-07-11 05:30:07'),(102,2,1,'Page Visit','User Visited the Page','','','A2B_entity_server_group.php','190.10.152.140','2007-07-11 05:30:12'),(103,2,1,'Page Visit','User Visited the Page','','','A2B_entity_server.php','190.10.152.140','2007-07-11 05:30:16'),(104,2,1,'Page Visit','User Visited the Page','','','A2B_entity_server.php','190.10.152.140','2007-07-11 05:30:21'),(105,2,1,'Page Visit','User Visited the Page','','','A2B_entity_mailtemplate.php','190.10.152.140','2007-07-11 05:30:29'),(106,2,1,'Page Visit','User Visited the Page','','','A2B_entity_mailtemplate.php','190.10.152.140','2007-07-11 05:30:34'),(107,2,1,'Page Visit','User Visited the Page','','','A2B_entity_prefix.php','190.10.152.140','2007-07-11 05:30:42'),(108,2,1,'Page Visit','User Visited the Page','','','A2B_entity_prefix.php','190.10.152.140','2007-07-11 05:30:47'),(109,2,1,'Page Visit','User Visited the Page','','','A2B_entity_user.php','190.10.152.140','2007-07-11 05:31:19'),(110,2,1,'Page Visit','User Visited the Page','','','A2B_entity_user.php','190.10.152.140','2007-07-11 05:31:23'),(111,2,1,'Page Visit','User Visited the Page','','','A2B_entity_user.php','190.10.152.140','2007-07-11 05:31:31'),(112,2,1,'Page Visit','User Visited the Page','','','A2B_entity_user.php','190.10.152.140','2007-07-11 05:31:47'),(113,2,1,'Page Visit','User Visited the Page','','','A2B_entity_user.php','190.10.152.140','2007-07-11 05:31:52'),(114,2,1,'Page Visit','User Visited the Page','','','A2B_entity_backup.php','190.10.152.140','2007-07-11 05:32:01'),(115,2,1,'Page Visit','User Visited the Page','','','A2B_logfile.php','190.10.152.140','2007-07-11 05:32:31'),(116,2,1,'Page Visit','User Visited the Page','','','A2B_entity_log_viewer.php','190.10.152.140','2007-07-11 05:32:35'),(117,2,1,'Page Visit','User Visited the Page','','','CC_musiconhold.php','190.10.152.140','2007-07-11 05:33:31'),(118,2,1,'Page Visit','User Visited the Page','','','CC_musiconhold.php','190.10.152.140','2007-07-11 05:33:53'),(119,2,1,'Page Visit','User Visited the Page','','','CC_upload.php','190.10.152.140','2007-07-11 05:33:58'),(120,2,1,'Page Visit','User Visited the Page','','','CC_musiconhold.php','190.10.152.140','2007-07-11 05:34:06'),(121,2,1,'Page Visit','User Visited the Page','','','logout.php','190.10.152.140','2007-07-11 05:34:18'),(122,0,1,'USER LOGGED OUT','User Logged out from website','','','logout.php','190.10.152.140','2007-07-11 05:34:18'),(123,0,1,'Page Visit','User Visited the Page','','','index.php','190.10.152.140','2007-07-11 05:34:18'),(124,0,1,'Page Visit','User Visited the Page','','','index.php','190.10.152.140','2007-07-11 05:46:19'),(125,0,1,'Page Visit','User Visited the Page','','','PP_intro.php','190.10.152.140','2007-07-11 05:46:26'),(126,2,1,'User Logged In','User Logged in to website','','','PP_Intro.php','190.10.152.140','2007-07-11 05:46:26'),(127,2,1,'Page Visit','User Visited the Page','','','PP_intro.php','190.10.152.140','2007-07-11 05:49:22'),(128,2,1,'User Logged In','User Logged in to website','','','PP_Intro.php','190.10.152.140','2007-07-11 05:49:22'),(129,2,1,'Page Visit','User Visited the Page','','','index.php','190.10.152.140','2007-07-11 05:59:45'),(130,2,1,'Page Visit','User Visited the Page','','','PP_intro.php','190.10.152.140','2007-07-11 06:08:26'),(131,2,1,'User Logged In','User Logged in to website','','','PP_Intro.php','190.10.152.140','2007-07-11 06:08:26'),(132,2,1,'Page Visit','User Visited the Page','','','A2B_entity_friend.php','190.10.152.140','2007-07-11 06:09:32'),(133,2,1,'Page Visit','User Visited the Page','','','A2B_entity_friend.php','190.10.152.140','2007-07-11 06:09:37'),(134,2,1,'Page Visit','User Visited the Page','','','A2B_entity_callerid.php','190.10.152.140','2007-07-11 06:09:42'),(135,2,1,'Page Visit','User Visited the Page','','','A2B_entity_speeddial.php','190.10.152.140','2007-07-11 06:09:46'),(136,2,1,'Page Visit','User Visited the Page','','','A2B_entity_speeddial.php','190.10.152.140','2007-07-11 06:09:49');
/*!40000 ALTER TABLE `cc_system_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_tariffgroup`
--

DROP TABLE IF EXISTS `cc_tariffgroup`;
CREATE TABLE `cc_tariffgroup` (
  `id` int(11) NOT NULL auto_increment,
  `iduser` int(11) NOT NULL default '0',
  `idtariffplan` int(11) NOT NULL default '0',
  `tariffgroupname` char(50) collate utf8_bin NOT NULL,
  `lcrtype` int(11) NOT NULL default '0',
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `removeinterprefix` int(11) NOT NULL default '0',
  `id_cc_package_offer` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_tariffgroup`
--

LOCK TABLES `cc_tariffgroup` WRITE;
/*!40000 ALTER TABLE `cc_tariffgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_tariffgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_tariffgroup_plan`
--

DROP TABLE IF EXISTS `cc_tariffgroup_plan`;
CREATE TABLE `cc_tariffgroup_plan` (
  `idtariffgroup` int(11) NOT NULL,
  `idtariffplan` int(11) NOT NULL,
  PRIMARY KEY  (`idtariffgroup`,`idtariffplan`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_tariffgroup_plan`
--

LOCK TABLES `cc_tariffgroup_plan` WRITE;
/*!40000 ALTER TABLE `cc_tariffgroup_plan` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_tariffgroup_plan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_tariffplan`
--

DROP TABLE IF EXISTS `cc_tariffplan`;
CREATE TABLE `cc_tariffplan` (
  `id` int(11) NOT NULL auto_increment,
  `iduser` int(11) NOT NULL default '0',
  `tariffname` char(50) collate utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `startingdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `description` mediumtext collate utf8_bin,
  `id_trunk` int(11) default '0',
  `secondusedreal` int(11) default '0',
  `secondusedcarrier` int(11) default '0',
  `secondusedratecard` int(11) default '0',
  `reftariffplan` int(11) default '0',
  `idowner` int(11) default '0',
  `dnidprefix` char(30) collate utf8_bin NOT NULL default 'all',
  `calleridprefix` char(30) collate utf8_bin NOT NULL default 'all',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_tariffplan_iduser_tariffname` (`iduser`,`tariffname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_tariffplan`
--

LOCK TABLES `cc_tariffplan` WRITE;
/*!40000 ALTER TABLE `cc_tariffplan` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_tariffplan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_templatemail`
--

DROP TABLE IF EXISTS `cc_templatemail`;
CREATE TABLE `cc_templatemail` (
  `mailtype` char(50) collate utf8_bin default NULL,
  `fromemail` char(70) collate utf8_bin default NULL,
  `fromname` char(70) collate utf8_bin default NULL,
  `subject` char(70) collate utf8_bin default NULL,
  `messagetext` longtext collate utf8_bin,
  `messagehtml` longtext collate utf8_bin,
  UNIQUE KEY `cons_cc_templatemail_mailtype` (`mailtype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_templatemail`
--

LOCK TABLES `cc_templatemail` WRITE;
/*!40000 ALTER TABLE `cc_templatemail` DISABLE KEYS */;
INSERT INTO `cc_templatemail` VALUES ('signup','info@call-labs.com','Call-Labs','SIGNUP CONFIRMATION','\nThank you for registering with us\n\nPlease click on below link to activate your account.\n\nhttp://call-labs.com/A2Billing_UI/signup/activate.php?key$loginkey\n\nPlease make sure you active your account by making payment to us either by\ncredit card, wire transfer, money order, cheque, and western union money\ntransfer, money Gram, and Pay pal.\n\n\nKind regards,\nCall Labs\n',''),('reminder','info@call-labs.com','Call-Labs','REMINDER','\nOur record indicates that you have less than $min_credit usd in your \"$card_gen\" account.\n\nWe hope this message provides you with enough notice to refill your account.\nWe value your business, but our system can disconnect you automatically\nwhen you reach your pre-paid balance.\nPlease login to your account through our website to check your account\ndetails. Plus,\nyou can pay by credit card, on demand.\nhttp://call-labs.com/A2BCustomer_UI/\n\nIf you believe this information to be incorrect please contact\ninfo@call-labs.com\nimmediately.\n\n\nKind regards,\nCall Labs\n',''),('forgetpassword','info@call-labs.com','Call-Labs','Login Information','Your login information is as below:\n\nYour account is $card_gen\n\nYour password is $password\n\nYour cardalias is $cardalias\n\nhttp://call-labs.com/A2BCustomer_UI/\n\nKind regards,\nCall Labs\n',''),('signupconfirmed','info@call-labs.com','Call-Labs','SIGNUP CONFIRMATION','Thank you for registering with us\n\nPlease make sure you active your account by making payment to us either by\ncredit card, wire transfer, money order, cheque, and western union money\ntransfer, money Gram, and Pay pal.\n\nYour account is $card_gen\n\nYour password is $password\n\nTo go to your account :\nhttp://call-labs.com/A2BCustomer_UI/\n\nKind regards,\nCall Labs\n',''),('epaymentverify','info@call-labs.com','Call-Labs','Epayment Gateway Security Verification Failed','Dear Administrator\n\nPlease check the Epayment Log, System has logged a Epayment Security failure. that may be a possible attack on epayment processing.\n\nTime of Transaction: $time\nPayment Gateway: $paymentgateway\nAmount: $amount\n\n\n\nKind regards,\nCall Labs\n',''),('payment','info@call-labs.com','Call-Labs','PAYMENT CONFIRMATION','Thank you for shopping at Call-Labs.\n\nShopping details is as below.\n\nItem Name = <b>$itemName</b>\nItem ID = <b>$itemID</b>\nAmount = <b>$itemAmount</b>\nPayment Method = <b>$paymentMethod</b>\nStatus = <b>$paymentStatus</b>\n\n\nKind regards,\nCall Labs\n',''),('invoice','info@call-labs.com','Call-Labs','A2BILLING INVOICE','Dear Customer.\n\nAttached is the invoice.\n\nKind regards,\nCall Labs\n','');
/*!40000 ALTER TABLE `cc_templatemail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_trunk`
--

DROP TABLE IF EXISTS `cc_trunk`;
CREATE TABLE `cc_trunk` (
  `id_trunk` int(11) NOT NULL auto_increment,
  `trunkcode` char(20) collate utf8_bin NOT NULL,
  `trunkprefix` char(20) collate utf8_bin default NULL,
  `providertech` char(20) collate utf8_bin NOT NULL,
  `providerip` char(80) collate utf8_bin NOT NULL,
  `removeprefix` char(20) collate utf8_bin default NULL,
  `secondusedreal` int(11) default '0',
  `secondusedcarrier` int(11) default '0',
  `secondusedratecard` int(11) default '0',
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `failover_trunk` int(11) default NULL,
  `addparameter` char(120) collate utf8_bin default NULL,
  `id_provider` int(11) default NULL,
  PRIMARY KEY  (`id_trunk`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_trunk`
--

LOCK TABLES `cc_trunk` WRITE;
/*!40000 ALTER TABLE `cc_trunk` DISABLE KEYS */;
INSERT INTO `cc_trunk` VALUES (1,'DEFAULT','011','IAX2','kiki@switch-2.kiki.net','',0,0,0,'2005-03-14 06:01:36',0,'',NULL);
/*!40000 ALTER TABLE `cc_trunk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_ui_authen`
--

DROP TABLE IF EXISTS `cc_ui_authen`;
CREATE TABLE `cc_ui_authen` (
  `userid` bigint(20) NOT NULL auto_increment,
  `login` char(50) collate utf8_bin NOT NULL,
  `password` char(50) collate utf8_bin NOT NULL,
  `groupid` int(11) default NULL,
  `perms` int(11) default NULL,
  `confaddcust` int(11) default NULL,
  `name` char(50) collate utf8_bin default NULL,
  `direction` char(80) collate utf8_bin default NULL,
  `zipcode` char(20) collate utf8_bin default NULL,
  `state` char(20) collate utf8_bin default NULL,
  `phone` char(30) collate utf8_bin default NULL,
  `fax` char(30) collate utf8_bin default NULL,
  `datecreation` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`userid`),
  UNIQUE KEY `cons_cc_ui_authen_login` (`login`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_ui_authen`
--

LOCK TABLES `cc_ui_authen` WRITE;
/*!40000 ALTER TABLE `cc_ui_authen` DISABLE KEYS */;
INSERT INTO `cc_ui_authen` VALUES (2,'admin','mypassword',0,32767,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2005-02-27 02:14:05'),(1,'root','myroot',0,32767,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2005-02-27 01:33:27');
/*!40000 ALTER TABLE `cc_ui_authen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_voucher`
--

DROP TABLE IF EXISTS `cc_voucher`;
CREATE TABLE `cc_voucher` (
  `id` bigint(20) NOT NULL auto_increment,
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `usedate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `voucher` char(50) collate utf8_bin NOT NULL,
  `usedcardnumber` char(50) collate utf8_bin default NULL,
  `tag` char(50) collate utf8_bin default NULL,
  `credit` float NOT NULL default '0',
  `activated` char(1) collate utf8_bin NOT NULL default 'f',
  `used` int(11) default '0',
  `currency` char(3) collate utf8_bin default 'USD',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cons_cc_voucher_voucher` (`voucher`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `cc_voucher`
--

LOCK TABLES `cc_voucher` WRITE;
/*!40000 ALTER TABLE `cc_voucher` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_voucher` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-09-03 14:51:35
