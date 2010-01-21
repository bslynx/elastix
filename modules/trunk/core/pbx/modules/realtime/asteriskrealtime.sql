-- MySQL dump 10.11
--
-- Host: localhost    Database: asteriskrealtime
-- ------------------------------------------------------
-- Server version	5.0.77

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
-- Table structure for table `extensions`
--

DROP TABLE IF EXISTS `extensions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `extensions` (
  `id` int(11) NOT NULL auto_increment,
  `context` varchar(20) NOT NULL default '',
  `exten` varchar(20) NOT NULL default '',
  `priority` tinyint(4) NOT NULL default '0',
  `app` varchar(20) NOT NULL default '',
  `appdata` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`context`,`exten`,`priority`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `extensions`
--

LOCK TABLES `extensions` WRITE;
/*!40000 ALTER TABLE `extensions` DISABLE KEYS */;
INSERT INTO `extensions` VALUES (1,'from-internal','105',1,'Dial','SIP/105');
/*!40000 ALTER TABLE `extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iax_buddies`
--

DROP TABLE IF EXISTS `iax_buddies`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `iax_buddies` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL,
  `username` varchar(30) default NULL,
  `type` varchar(6) NOT NULL,
  `secret` varchar(50) default NULL,
  `md5secret` varchar(32) default NULL,
  `dbsecret` varchar(100) default NULL,
  `notransfer` varchar(10) default NULL,
  `inkeys` varchar(100) default NULL,
  `outkey` varchar(100) default NULL,
  `auth` varchar(100) default NULL,
  `accountcode` varchar(100) default NULL,
  `amaflags` varchar(100) default NULL,
  `callerid` varchar(100) default NULL,
  `context` varchar(100) default NULL,
  `defaultip` varchar(15) default NULL,
  `host` varchar(31) NOT NULL default 'dynamic',
  `language` char(5) default NULL,
  `mailbox` varchar(50) default NULL,
  `deny` varchar(95) default NULL,
  `permit` varchar(95) default NULL,
  `qualify` varchar(4) default NULL,
  `disallow` varchar(100) default NULL,
  `allow` varchar(100) default NULL,
  `ipaddr` varchar(15) default NULL,
  `port` int(11) default '0',
  `regseconds` int(11) default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `iax_buddies`
--

LOCK TABLES `iax_buddies` WRITE;
/*!40000 ALTER TABLE `iax_buddies` DISABLE KEYS */;
INSERT INTO `iax_buddies` VALUES (1,'701','701','friend','701',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default',NULL,'labA',NULL,'dynamic',NULL,NULL,'0.0.0.0/0.0.0.0',NULL,NULL,'all','alaw',NULL,0,0);
/*!40000 ALTER TABLE `iax_buddies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue_members`
--

DROP TABLE IF EXISTS `queue_members`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `queue_members` (
  `uniqueid` int(10) unsigned NOT NULL auto_increment,
  `membername` varchar(40) default NULL,
  `queue_name` varchar(128) default NULL,
  `interface` varchar(128) default NULL,
  `penalty` int(11) default NULL,
  `paused` tinyint(1) default NULL,
  PRIMARY KEY  (`uniqueid`),
  UNIQUE KEY `queue_interface` (`queue_name`,`interface`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `queue_members`
--

LOCK TABLES `queue_members` WRITE;
/*!40000 ALTER TABLE `queue_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queues`
--

DROP TABLE IF EXISTS `queues`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `queues` (
  `name` varchar(128) NOT NULL,
  `musiconhold` varchar(128) default NULL,
  `announce` varchar(128) default NULL,
  `context` varchar(128) default NULL,
  `timeout` int(11) default NULL,
  `monitor_type` varchar(50) NOT NULL,
  `monitor_format` varchar(128) default NULL,
  `queue_youarenext` varchar(128) default NULL,
  `queue_thereare` varchar(128) default NULL,
  `queue_callswaiting` varchar(128) default NULL,
  `queue_holdtime` varchar(128) default NULL,
  `queue_minutes` varchar(128) default NULL,
  `queue_seconds` varchar(128) default NULL,
  `queue_lessthan` varchar(128) default NULL,
  `queue_thankyou` varchar(128) default NULL,
  `queue_reporthold` varchar(128) default NULL,
  `announce_frequency` int(11) default NULL,
  `announce_round_seconds` int(11) default NULL,
  `announce_holdtime` varchar(128) default NULL,
  `retry` int(11) default NULL,
  `wrapuptime` int(11) default NULL,
  `maxlen` int(11) default NULL,
  `servicelevel` int(11) default NULL,
  `strategy` varchar(128) default NULL,
  `joinempty` varchar(128) default NULL,
  `leavewhenempty` varchar(128) default NULL,
  `eventmemberstatus` varchar(4) default NULL,
  `eventwhencalled` varchar(4) default NULL,
  `reportholdtime` tinyint(1) default NULL,
  `memberdelay` int(11) default NULL,
  `weight` int(11) default NULL,
  `timeoutrestart` tinyint(1) default NULL,
  `periodic_announce` varchar(50) default NULL,
  `periodic_announce_frequency` int(11) default NULL,
  `ringinuse` tinyint(1) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `queues`
--

LOCK TABLES `queues` WRITE;
/*!40000 ALTER TABLE `queues` DISABLE KEYS */;
/*!40000 ALTER TABLE `queues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sip_buddies`
--

DROP TABLE IF EXISTS `sip_buddies`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sip_buddies` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `host` varchar(31) NOT NULL default '',
  `nat` varchar(5) NOT NULL default 'no',
  `type` enum('user','peer','friend') NOT NULL default 'friend',
  `accountcode` varchar(20) default NULL,
  `amaflags` varchar(13) default NULL,
  `call-limit` smallint(5) unsigned default NULL,
  `callgroup` varchar(10) default NULL,
  `callerid` varchar(80) default NULL,
  `cancallforward` char(3) default 'yes',
  `canreinvite` char(3) default 'yes',
  `context` varchar(80) default NULL,
  `defaultip` varchar(15) default NULL,
  `dtmfmode` varchar(7) default NULL,
  `fromuser` varchar(80) default NULL,
  `fromdomain` varchar(80) default NULL,
  `insecure` varchar(4) default NULL,
  `language` char(2) default NULL,
  `mailbox` varchar(50) default NULL,
  `md5secret` varchar(80) default NULL,
  `deny` varchar(95) default NULL,
  `permit` varchar(95) default NULL,
  `mask` varchar(95) default NULL,
  `musiconhold` varchar(100) default NULL,
  `pickupgroup` varchar(10) default NULL,
  `qualify` char(3) default NULL,
  `regexten` varchar(80) default NULL,
  `restrictcid` char(3) default NULL,
  `rtptimeout` char(3) default NULL,
  `rtpholdtimeout` char(3) default NULL,
  `secret` varchar(80) default NULL,
  `setvar` varchar(100) default NULL,
  `disallow` varchar(100) default 'all',
  `allow` varchar(100) default 'g729;ilbc;gsm;ulaw;alaw',
  `fullcontact` varchar(80) NOT NULL default '',
  `ipaddr` varchar(15) NOT NULL default '',
  `port` smallint(5) unsigned NOT NULL default '0',
  `regserver` varchar(100) default NULL,
  `regseconds` int(11) NOT NULL default '0',
  `lastms` int(11) NOT NULL default '0',
  `username` varchar(80) NOT NULL default '',
  `defaultuser` varchar(80) NOT NULL default '',
  `subscribecontext` varchar(80) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `name_2` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sip_buddies`
--

LOCK TABLES `sip_buddies` WRITE;
/*!40000 ALTER TABLE `sip_buddies` DISABLE KEYS */;
INSERT INTO `sip_buddies` VALUES (1,'201','','no','friend',NULL,'default',NULL,NULL,NULL,'yes','yes','internal',NULL,'rfc2833',NULL,NULL,NULL,NULL,NULL,NULL,'0.0.0.0/0.0.0.0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'201',NULL,'all','alaw','','',0,NULL,0,0,'201','',NULL);
/*!40000 ALTER TABLE `sip_buddies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voicemail_users`
--

DROP TABLE IF EXISTS `voicemail_users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `voicemail_users` (
  `uniqueid` int(11) NOT NULL auto_increment,
  `customer_id` varchar(11) NOT NULL default '0',
  `context` varchar(50) NOT NULL default '',
  `mailbox` varchar(11) NOT NULL default '0',
  `password` varchar(5) NOT NULL default '0',
  `fullname` varchar(150) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `pager` varchar(50) NOT NULL default '',
  `tz` varchar(10) NOT NULL default 'central',
  `attach` varchar(4) NOT NULL default 'yes',
  `saycid` varchar(4) NOT NULL default 'yes',
  `dialout` varchar(10) NOT NULL default '',
  `callback` varchar(10) NOT NULL default '',
  `review` varchar(4) NOT NULL default 'no',
  `operator` varchar(4) NOT NULL default 'no',
  `envelope` varchar(4) NOT NULL default 'no',
  `sayduration` varchar(4) NOT NULL default 'no',
  `saydurationm` tinyint(4) NOT NULL default '1',
  `sendvoicemail` varchar(4) NOT NULL default 'no',
  `delete` varchar(4) NOT NULL default 'no',
  `nextaftercmd` varchar(4) NOT NULL default 'yes',
  `forcename` varchar(4) NOT NULL default 'no',
  `forcegreetings` varchar(4) NOT NULL default 'no',
  `hidefromdir` varchar(4) NOT NULL default 'yes',
  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`uniqueid`),
  KEY `mailbox_context` (`mailbox`,`context`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `voicemail_users`
--

LOCK TABLES `voicemail_users` WRITE;
/*!40000 ALTER TABLE `voicemail_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `voicemail_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voicemessages`
--

DROP TABLE IF EXISTS `voicemessages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `voicemessages` (
  `id` int(11) NOT NULL auto_increment,
  `msgnum` int(11) NOT NULL default '0',
  `dir` varchar(80) default '',
  `context` varchar(80) default '',
  `macrocontext` varchar(80) default '',
  `callerid` varchar(40) default '',
  `origtime` varchar(40) default '',
  `duration` varchar(20) default '',
  `mailboxuser` varchar(80) default '',
  `mailboxcontext` varchar(80) default '',
  `recording` longblob,
  PRIMARY KEY  (`id`),
  KEY `dir` (`dir`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `voicemessages`
--

LOCK TABLES `voicemessages` WRITE;
/*!40000 ALTER TABLE `voicemessages` DISABLE KEYS */;
/*!40000 ALTER TABLE `voicemessages` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-01-21 22:01:56
