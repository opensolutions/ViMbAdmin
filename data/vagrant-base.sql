-- MySQL dump 10.16  Distrib 10.1.14-MariaDB, for osx10.11 (x86_64)
--
-- Host: localhost    Database: vimbadmin
-- ------------------------------------------------------
-- Server version	10.1.14-MariaDB

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
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `super` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_Username_1` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'vagrant@example.com','$2a$09$OPTiEY5TIarUdLwcNBZiuuOqKaHQvRAzmvnb886gljFZ1a8A9ENtO',1,1,'2016-06-04 20:39:59','2016-06-04 20:39:59');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_pref`
--

DROP TABLE IF EXISTS `admin_pref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_pref` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ix` int(11) NOT NULL DEFAULT '0',
  `op` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT ':=',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expire` bigint(20) NOT NULL DEFAULT '0',
  `Admin_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_AdminPreference_1` (`Admin_id`,`attribute`,`ix`),
  KEY `IDX_814C1AD19D5DE046` (`Admin_id`),
  CONSTRAINT `FK_814C1AD19D5DE046` FOREIGN KEY (`Admin_id`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_pref`
--

LOCK TABLES `admin_pref` WRITE;
/*!40000 ALTER TABLE `admin_pref` DISABLE KEYS */;
INSERT INTO `admin_pref` VALUES (1,'auth.last_login_from',0,'=','127.0.0.1',0,1),(2,'auth.last_login_at',0,'=','1465072810',0,1),(3,'version_last_check_at',0,'=','1465072811',0,1);
/*!40000 ALTER TABLE `admin_pref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alias`
--

DROP TABLE IF EXISTS `alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alias` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `goto` longtext COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `Domain_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E16C6B94D4E6F81` (`address`),
  KEY `IDX_E16C6B9493AE8C46` (`Domain_id`),
  CONSTRAINT `FK_E16C6B9493AE8C46` FOREIGN KEY (`Domain_id`) REFERENCES `domain` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alias`
--

LOCK TABLES `alias` WRITE;
/*!40000 ALTER TABLE `alias` DISABLE KEYS */;
INSERT INTO `alias` VALUES (1,'a@example.com','a@example.com',1,'2016-06-04 20:40:34',NULL,1),(2,'b@example.com','b@example.com',1,'2016-06-04 20:40:45',NULL,1),(3,'c@example.com','a@example.com',1,'2016-06-04 20:40:54',NULL,1);
/*!40000 ALTER TABLE `alias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alias_pref`
--

DROP TABLE IF EXISTS `alias_pref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alias_pref` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ix` int(11) NOT NULL DEFAULT '0',
  `op` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT ':=',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expire` bigint(20) NOT NULL DEFAULT '0',
  `Alias_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3279E911A72028B4` (`Alias_id`),
  KEY `IX_AliasPreference_1` (`Alias_id`,`attribute`,`ix`),
  CONSTRAINT `FK_3279E911A72028B4` FOREIGN KEY (`Alias_id`) REFERENCES `alias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alias_pref`
--

LOCK TABLES `alias_pref` WRITE;
/*!40000 ALTER TABLE `alias_pref` DISABLE KEYS */;
/*!40000 ALTER TABLE `alias_pref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archive`
--

DROP TABLE IF EXISTS `archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `archived_at` datetime NOT NULL,
  `status_changed_at` datetime NOT NULL,
  `homedir_server` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `homedir_file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `homedir_orig_size` bigint(20) DEFAULT NULL,
  `homedir_size` bigint(20) DEFAULT NULL,
  `maildir_server` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `maildir_file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `maildir_orig_size` bigint(20) DEFAULT NULL,
  `maildir_size` bigint(20) DEFAULT NULL,
  `data` longtext COLLATE utf8_unicode_ci NOT NULL,
  `Domain_id` bigint(20) DEFAULT NULL,
  `Admin_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D5FC5D9CF85E0677` (`username`),
  KEY `IDX_D5FC5D9C93AE8C46` (`Domain_id`),
  KEY `IDX_D5FC5D9C9D5DE046` (`Admin_id`),
  CONSTRAINT `FK_D5FC5D9C93AE8C46` FOREIGN KEY (`Domain_id`) REFERENCES `domain` (`id`),
  CONSTRAINT `FK_D5FC5D9C9D5DE046` FOREIGN KEY (`Admin_id`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archive`
--

LOCK TABLES `archive` WRITE;
/*!40000 ALTER TABLE `archive` DISABLE KEYS */;
/*!40000 ALTER TABLE `archive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dbversion`
--

DROP TABLE IF EXISTS `dbversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbversion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `applied_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dbversion`
--

LOCK TABLES `dbversion` WRITE;
/*!40000 ALTER TABLE `dbversion` DISABLE KEYS */;
INSERT INTO `dbversion` VALUES (1,1,'Venus','2016-06-04 20:39:59');
/*!40000 ALTER TABLE `dbversion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directory_entry`
--

DROP TABLE IF EXISTS `directory_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `directory_entry` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mailbox_id` bigint(20) NOT NULL,
  `businessCategory` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `carLicense` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `departmentNumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `displayName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `employeeNumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `employeeType` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `homePhone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `homePostalAddress` longtext COLLATE utf8_unicode_ci,
  `initials` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jpegPhoto` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  `labeledURI` longtext COLLATE utf8_unicode_ci,
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `manager` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `o` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pager` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `preferredLanguage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `roomNumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `secretary` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `personalTitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ou` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facsimileTelephoneNumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `givenName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telephoneNumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vimb_created` datetime NOT NULL,
  `vimb_update` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6553C92D66EC35CC` (`mailbox_id`),
  CONSTRAINT `FK_6553C92D66EC35CC` FOREIGN KEY (`mailbox_id`) REFERENCES `mailbox` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `directory_entry`
--

LOCK TABLES `directory_entry` WRITE;
/*!40000 ALTER TABLE `directory_entry` DISABLE KEYS */;
/*!40000 ALTER TABLE `directory_entry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain`
--

DROP TABLE IF EXISTS `domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_aliases` int(11) NOT NULL DEFAULT '0',
  `alias_count` bigint(20) NOT NULL DEFAULT '0',
  `max_mailboxes` int(11) NOT NULL DEFAULT '0',
  `mailbox_count` bigint(20) NOT NULL DEFAULT '0',
  `max_quota` bigint(20) NOT NULL DEFAULT '0',
  `quota` bigint(20) NOT NULL DEFAULT '0',
  `transport` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'virtual',
  `backupmx` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `homedir` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `maildir` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `gid` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_Domain_1` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain`
--

LOCK TABLES `domain` WRITE;
/*!40000 ALTER TABLE `domain` DISABLE KEYS */;
INSERT INTO `domain` VALUES (1,'example.com','',0,1,0,2,0,0,'virtual',0,1,NULL,NULL,NULL,NULL,'2016-06-04 20:40:19',NULL);
/*!40000 ALTER TABLE `domain` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_admins`
--

DROP TABLE IF EXISTS `domain_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain_admins` (
  `Admin_id` bigint(20) NOT NULL,
  `Domain_id` bigint(20) NOT NULL,
  PRIMARY KEY (`Admin_id`,`Domain_id`),
  KEY `IDX_CD8319C69D5DE046` (`Admin_id`),
  KEY `IDX_CD8319C693AE8C46` (`Domain_id`),
  CONSTRAINT `FK_CD8319C693AE8C46` FOREIGN KEY (`Domain_id`) REFERENCES `domain` (`id`),
  CONSTRAINT `FK_CD8319C69D5DE046` FOREIGN KEY (`Admin_id`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_admins`
--

LOCK TABLES `domain_admins` WRITE;
/*!40000 ALTER TABLE `domain_admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_pref`
--

DROP TABLE IF EXISTS `domain_pref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain_pref` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ix` int(11) NOT NULL DEFAULT '0',
  `op` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT ':=',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expire` bigint(20) NOT NULL DEFAULT '0',
  `Domain_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C89B55693AE8C46` (`Domain_id`),
  KEY `IX_DomainPreference_1` (`Domain_id`,`attribute`,`ix`),
  CONSTRAINT `FK_C89B55693AE8C46` FOREIGN KEY (`Domain_id`) REFERENCES `domain` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_pref`
--

LOCK TABLES `domain_pref` WRITE;
/*!40000 ALTER TABLE `domain_pref` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_pref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci,
  `timestamp` datetime NOT NULL,
  `Admin_id` bigint(20) DEFAULT NULL,
  `Domain_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8F3F68C59D5DE046` (`Admin_id`),
  KEY `IDX_8F3F68C593AE8C46` (`Domain_id`),
  CONSTRAINT `FK_8F3F68C593AE8C46` FOREIGN KEY (`Domain_id`) REFERENCES `domain` (`id`),
  CONSTRAINT `FK_8F3F68C59D5DE046` FOREIGN KEY (`Admin_id`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
INSERT INTO `log` VALUES (1,'DOMAIN_ADD','vagrant@example.com  added domain example.com','2016-06-04 20:40:19',1,1),(2,'MAILBOX_ADD','vagrant@example.com  added mailbox a@example.com','2016-06-04 20:40:34',1,1),(3,'MAILBOX_ADD','vagrant@example.com  added mailbox b@example.com','2016-06-04 20:40:45',1,1),(4,'ALIAS_ADD','vagrant@example.com  added alias c@example.com','2016-06-04 20:40:54',1,1);
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mailbox`
--

DROP TABLE IF EXISTS `mailbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailbox` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alt_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quota` bigint(20) NOT NULL DEFAULT '0',
  `local_part` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `access_restriction` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ALL',
  `homedir` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `maildir` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uid` bigint(20) DEFAULT NULL,
  `gid` bigint(20) DEFAULT NULL,
  `homedir_size` bigint(20) DEFAULT NULL,
  `maildir_size` bigint(20) DEFAULT NULL,
  `size_at` datetime DEFAULT NULL,
  `delete_pending` tinyint(1) DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `Domain_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A69FE20B93AE8C46` (`Domain_id`),
  CONSTRAINT `FK_A69FE20B93AE8C46` FOREIGN KEY (`Domain_id`) REFERENCES `domain` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mailbox`
--

LOCK TABLES `mailbox` WRITE;
/*!40000 ALTER TABLE `mailbox` DISABLE KEYS */;
INSERT INTO `mailbox` VALUES (1,'a@example.com','817ebc64fa60d6b582578f76916057bb','A Mailbox','',0,'a',1,'ALL','/srv/vmail/example.com/a','maildir:/srv/vmail/example.com/a/mail:LAYOUT=fs',2000,2000,NULL,NULL,NULL,0,'2016-06-04 20:40:34',NULL,1),(2,'b@example.com','a7b62725d6f104b70271d25f69fcdeaa','B Mailbox','',0,'b',1,'ALL','/srv/vmail/example.com/b','maildir:/srv/vmail/example.com/b/mail:LAYOUT=fs',2000,2000,NULL,NULL,NULL,0,'2016-06-04 20:40:45',NULL,1);
/*!40000 ALTER TABLE `mailbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mailbox_pref`
--

DROP TABLE IF EXISTS `mailbox_pref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailbox_pref` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ix` int(11) NOT NULL DEFAULT '0',
  `op` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT ':=',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expire` bigint(20) NOT NULL DEFAULT '0',
  `Mailbox_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F9C4B42A29B1361C` (`Mailbox_id`),
  KEY `IX_MailboxPreference_1` (`Mailbox_id`,`attribute`,`ix`),
  CONSTRAINT `FK_F9C4B42A29B1361C` FOREIGN KEY (`Mailbox_id`) REFERENCES `mailbox` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mailbox_pref`
--

LOCK TABLES `mailbox_pref` WRITE;
/*!40000 ALTER TABLE `mailbox_pref` DISABLE KEYS */;
/*!40000 ALTER TABLE `mailbox_pref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remember_me`
--

DROP TABLE IF EXISTS `remember_me`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remember_me` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userhash` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ckey` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `original_ip` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `expires` datetime NOT NULL,
  `last_used` datetime NOT NULL,
  `created` datetime NOT NULL,
  `Admin_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7AF2A7289D5DE046` (`Admin_id`),
  CONSTRAINT `FK_7AF2A7289D5DE046` FOREIGN KEY (`Admin_id`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remember_me`
--

LOCK TABLES `remember_me` WRITE;
/*!40000 ALTER TABLE `remember_me` DISABLE KEYS */;
/*!40000 ALTER TABLE `remember_me` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-06-04 21:41:58
