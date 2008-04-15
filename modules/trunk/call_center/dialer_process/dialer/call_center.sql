# EMS MySQL Manager Pro 3.2.0.1
# ---------------------------------------
# Host     : localhost
# Port     : 3306
# Database : call_center


SET FOREIGN_KEY_CHECKS=0;

DROP DATABASE IF EXISTS `call_center`;

CREATE DATABASE `call_center`
    CHARACTER SET 'utf8'
    COLLATE 'utf8_general_ci';

USE `call_center`;

#
# Structure for the `agent` table : 
#

DROP TABLE IF EXISTS `agent`;

CREATE TABLE `agent` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `number` varchar(40) NOT NULL,
  `name` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `break` table : 
#

DROP TABLE IF EXISTS `break`;

CREATE TABLE `break` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) default NULL,
  `status` varchar(1) NOT NULL default 'A',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `audit` table : 
#

DROP TABLE IF EXISTS `audit`;

CREATE TABLE `audit` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_agent` int(10) unsigned NOT NULL,
  `id_break` int(10) unsigned NOT NULL,
  `datetime_init` datetime NOT NULL,
  `datetime_end` datetime default NULL,
  `duration` time default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_agent` (`id_agent`),
  KEY `id_break` (`id_break`),
  CONSTRAINT `audit_ibfk_1` FOREIGN KEY (`id_agent`) REFERENCES `agent` (`id`),
  CONSTRAINT `audit_ibfk_2` FOREIGN KEY (`id_break`) REFERENCES `break` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `campaign` table : 
#

DROP TABLE IF EXISTS `campaign`;

CREATE TABLE `campaign` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `datetime_init` date NOT NULL,
  `datetime_end` date NOT NULL,
  `daytime_init` time NOT NULL,
  `daytime_end` time NOT NULL,
  `retries` int(10) unsigned NOT NULL default '1',
  `trunk` varchar(16) NOT NULL,
  `context` varchar(32) NOT NULL,
  `queue` varchar(16) NOT NULL,
  `max_canales` int(10) unsigned NOT NULL default '0',
  `num_completadas` int(10) unsigned default NULL,
  `promedio` int(10) unsigned default NULL,
  `desviacion` int(10) unsigned default NULL,
  `script` text NOT NULL,
  `estatus` varchar(1) NOT NULL default 'A',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `calls` table : 
#

DROP TABLE IF EXISTS `calls`;

CREATE TABLE `calls` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_campaign` int(10) unsigned NOT NULL,
  `phone` varchar(32) NOT NULL,
  `status` varchar(32) default NULL,
  `uniqueid` varchar(32) default NULL,
  `fecha_llamada` datetime default NULL,
  `start_time` datetime default NULL,
  `end_time` datetime default NULL,
  `retries` int(10) unsigned NOT NULL default '0',
  `duration` int(10) unsigned default NULL,
  `id_agent` int(10) unsigned default NULL,
  `transfer` varchar(6) default NULL,
  `datetime_entry_queue` datetime default NULL,
  `duration_wait` int(11) default NULL,

  PRIMARY KEY  (`id`),
  KEY `id_campaign` (`id_campaign`),
  CONSTRAINT `calls_ibfk_1` FOREIGN KEY (`id_campaign`) REFERENCES `campaign` (`id`),
  CONSTRAINT `calls_ibfk_2` FOREIGN KEY (`id_agent`) REFERENCES `agent` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `call_attribute` table : 
#

DROP TABLE IF EXISTS `call_attribute`;

CREATE TABLE `call_attribute` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_call` int(10) unsigned NOT NULL,
  `columna` varchar(32) NOT NULL,
  `value` varchar(128) NOT NULL,
  `column_number` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_call` (`id_call`),
  CONSTRAINT `call_attribute_ibfk_1` FOREIGN KEY (`id_call`) REFERENCES `calls` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `form` table : 
#

DROP TABLE IF EXISTS `form`;

CREATE TABLE `form` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nombre` varchar(40) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `estatus` varchar(1) NOT NULL default 'A',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `campaign_form` table : 
#

DROP TABLE IF EXISTS `campaign_form`;

CREATE TABLE `campaign_form` (
  `id_campaign` int(10) unsigned NOT NULL,
  `id_form` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_campaign`,`id_form`),
  KEY `id_campaign` (`id_campaign`),
  KEY `id_form` (`id_form`),
  CONSTRAINT `campaign_form_ibfk_2` FOREIGN KEY (`id_form`) REFERENCES `form` (`id`),
  CONSTRAINT `campaign_form_ibfk_1` FOREIGN KEY (`id_campaign`) REFERENCES `campaign` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `current_calls` table : 
#

DROP TABLE IF EXISTS `current_calls`;

CREATE TABLE `current_calls` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_call` int(10) unsigned NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `uniqueid` varchar(32) default NULL,
  `queue` varchar(16) NOT NULL,
  `agentnum` varchar(16) NOT NULL,
  `event`    varchar(32) NOT NULL,
  `Channel`  varchar(32) NOT NULL DEFAULT '',
  `ChannelClient` varchar(32) NOT NULL DEFAULT '',
  `hold` enum('N','S') default 'N',
  PRIMARY KEY  (`id`),
  KEY `id_call` (`id_call`),
  CONSTRAINT `current_calls_ibfk_1` FOREIGN KEY (`id_call`) REFERENCES `calls` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `form_field` table : 
#

DROP TABLE IF EXISTS `form_field`;

CREATE TABLE `form_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL,
  `etiqueta` varchar(40) NOT NULL,
  `value` varchar(250) NOT NULL,
  `tipo` varchar(25) NOT NULL,
  `orden` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_form` (`id_form`),
  CONSTRAINT `form_field_ibfk_1` FOREIGN KEY (`id_form`) REFERENCES `form` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `form_data_recolected` table : 
#

DROP TABLE IF EXISTS `form_data_recolected`;

CREATE TABLE `form_data_recolected` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_calls` int(10) unsigned NOT NULL,
  `id_form_field` int(10) unsigned NOT NULL,
  `value` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_form_field` (`id_form_field`),
  KEY `id_calls` (`id_calls`),
  CONSTRAINT `form_data_recolected_ibfk_2` FOREIGN KEY (`id_calls`) REFERENCES `calls` (`id`),
  CONSTRAINT `form_data_recolected_ibfk_1` FOREIGN KEY (`id_form_field`) REFERENCES `form_field` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

grant select, update, delete, insert on call_center.* to asterisk@localhost identified by 'asterisk';


#
# A continuacion las sentencias SQL para la creacion de las tablas que seran utilizadas para la 
# campania de llamadas entrantes. Autor:  Ana Maria Vivar - Carlos Barcos
#


#
# Structure for the `queue_call_entry` table : 
#

DROP TABLE IF EXISTS `queue_call_entry`;

CREATE TABLE `queue_call_entry` (
  `id` int(10) unsigned not null auto_increment,
  `queue` varchar(50),
  `date_init` date default null,
  `time_init` time default null,
  `date_end` date default null,
  `time_end` time default null,
  `estatus` varchar(1) not null default 'A',
  `script` text default null,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `contact` table : 
#

DROP TABLE IF EXISTS `contact`;

CREATE TABLE `contact` (
  `id` int(10) unsigned not null auto_increment,
  `telefono` varchar(15) not null,
  `cedula_ruc` varchar(15) not null,
  `name` varchar(50)  not null,  
  `apellido` varchar(50)  not null,
  `origen` varchar(4)  not null default 'crm',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `call_entry` table : 
#

DROP TABLE IF EXISTS `call_entry`;

CREATE TABLE `call_entry` (
  `id` int(10) unsigned not null auto_increment,
  `id_agent` int(10) unsigned,
  `id_queue_call_entry` int(10) unsigned not null,
  `id_contact` int(10) unsigned default null,
  `callerid` varchar(15) not null,
  `datetime_init` datetime,
  `datetime_end` datetime,
  `duration` int(10) unsigned,
  `status` varchar(32),
  `transfer` varchar(6) default NULL,
  `datetime_entry_queue` datetime default NULL,
  `duration_wait` int(11) default NULL,
  `uniqueid` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_agent` (`id_agent`),
  KEY `id_queue_call_entry` (`id_queue_call_entry`),
  KEY `id_contact` (`id_contact`),
  CONSTRAINT `call_entry_ibfk_1` FOREIGN KEY (`id_agent`) REFERENCES `agent` (`id`),
  CONSTRAINT `call_entry_ibfk_2` FOREIGN KEY (`id_queue_call_entry`) REFERENCES `queue_call_entry` (`id`),
  CONSTRAINT `call_entry_ibfk_3` FOREIGN KEY (`id_contact`) REFERENCES `contact` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `current_call_entry` table : 
#

DROP TABLE IF EXISTS `current_call_entry`;

CREATE TABLE `current_call_entry` (
  `id` int(10) unsigned not null auto_increment,
  `id_agent` int(10) unsigned not null,
  `id_queue_call_entry` int(10) unsigned not null,
  `id_call_entry` int(10) unsigned not null,
  `callerid` varchar(15) not null,
  `datetime_init` datetime not null,
  `uniqueid` varchar(32) default null,
  `ChannelClient` varchar(32) not null,
  `hold` enum('N','S') default 'N',
  PRIMARY KEY  (`id`),
  KEY `id_agent` (`id_agent`),
  KEY `id_queue_call_entry` (`id_queue_call_entry`),
  KEY `id_call_entry` (`id_call_entry`),
  CONSTRAINT `current_call_entry_ibfk_1` FOREIGN KEY (`id_agent`) REFERENCES `agent` (`id`),
  CONSTRAINT `current_call_entry_ibfk_2` FOREIGN KEY (`id_queue_call_entry`) REFERENCES `queue_call_entry` (`id`),
  CONSTRAINT `current_call_entry_ibfk_3` FOREIGN KEY (`id_call_entry`) REFERENCES `call_entry` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
