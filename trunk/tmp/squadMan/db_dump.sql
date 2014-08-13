
-- MySQL dump 10.13  Distrib 5.5.32, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: SQUADRON_MANAGER
-- ------------------------------------------------------
-- Server version	5.5.32-0ubuntu0.13.04.1

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
-- Table structure for table `ACCOUNT_LOCKS`
--
CREATE DATABASE IF NOT EXISTS SQUADRON_MANAGER;
USE SQUADRON_MANAGER;

DROP TABLE IF EXISTS `ACCOUNT_LOCKS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ACCOUNT_LOCKS` (
  `CAPID` int(11) NOT NULL,
  `VALID_UNTIL` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`CAPID`),
  UNIQUE KEY `CAPID` (`CAPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ACCOUNT_LOCKS`
--

LOCK TABLES `ACCOUNT_LOCKS` WRITE;
/*!40000 ALTER TABLE `ACCOUNT_LOCKS` DISABLE KEYS */;
/*!40000 ALTER TABLE `ACCOUNT_LOCKS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ACHIEVEMENT`
--

DROP TABLE IF EXISTS `ACHIEVEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ACHIEVEMENT` (
  `ACHIEV_CODE` varchar(5) NOT NULL,
  `MEMBER_TYPE` char(1) NOT NULL,
  `ACHIEV_NAME` varchar(40) NOT NULL,
  `GRADE` varchar(10) NOT NULL,
  `PHASE` smallint(6) DEFAULT NULL,
  `NEXT_ACHIEV` varchar(5) DEFAULT NULL,
  `RIBBON` varchar(5) DEFAULT NULL,
  `ACHIEV_NUM` smallint(6) NOT NULL,
  PRIMARY KEY (`ACHIEV_CODE`,`MEMBER_TYPE`),
  KEY `FK_ACHIEVEMENT_MEMBER_TYPE` (`MEMBER_TYPE`),
  KEY `FK_ACHIEVEMENT_GRADE` (`GRADE`),
  KEY `FK_NEXT_ACHIEVEMENT` (`NEXT_ACHIEV`),
  KEY `FK_ACHIEVEMENT_PHASE` (`PHASE`),
  KEY `FK_AHCIEVEMENT_RIBBON` (`RIBBON`),
  CONSTRAINT `FK_ACHIEVEMENT_MEMBER_TYPE` FOREIGN KEY (`MEMBER_TYPE`) REFERENCES `MEMBERSHIP_TYPES` (`MEMBER_TYPE_CODE`),
  CONSTRAINT `FK_ACHIEVEMENT_GRADE` FOREIGN KEY (`GRADE`) REFERENCES `GRADE` (`GRADE_ABREV`),
  CONSTRAINT `FK_NEXT_ACHIEVEMENT` FOREIGN KEY (`NEXT_ACHIEV`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`),
  CONSTRAINT `FK_ACHIEVEMENT_PHASE` FOREIGN KEY (`PHASE`) REFERENCES `PHASES` (`PHASE_NUM`),
  CONSTRAINT `FK_AHCIEVEMENT_RIBBON` FOREIGN KEY (`RIBBON`) REFERENCES `RIBBON` (`RIBBON_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ACHIEVEMENT`
--

LOCK TABLES `ACHIEVEMENT` WRITE;
/*!40000 ALTER TABLE `ACHIEVEMENT` DISABLE KEYS */;
INSERT INTO `ACHIEVEMENT` VALUES ('0','C','Airman Basic','C/AB',1,'1',NULL,0),('SM','S','Senior Member','SM',6,'2LT',null,0),('1','C','John F. Curry','C/Amn',1,'2',NULL,1),('10','C','Admin. Officer','C/1st Lt',3,'11',NULL,12),('11','C','Public Affairs','C/1st Lt',3,'EAR',NULL,13),('12','C','Leadership Officer','C/Capt',4,'13',NULL,16),('13','C','Aerospace Officer','C/Capt',4,'14',NULL,17),('14','C','Operations Officer','C/Maj',4,'15',NULL,18),('15','C','Logistics Officer','C/Maj',4,'16',NULL,19),('16','C','C/Commander','C/Maj',4,'EAK',NULL,20),('1LT','S','First Lieutenant','1st_Lt',6,'CAP',NULL,4),('2','C','Gen Hap Arnold','C/A1C',1,'3',NULL,2),('2LT','S','Second Lieutenant','2d_Lt',6,'1LT',NULL,3),('3','C','Mary Feik','C/SrA',1,'WB',NULL,3),('4','C','Eddie Rickenbacker','C/TSgt',2,'5',NULL,5),('5','C','Charles Lindberg','C/MSgt',2,'6',NULL,6),('6','C','Gen Jimmy Doolittle','C/SMSgt',2,'7',NULL,7),('7','C','Robert Goddard','C/CMSgt',2,'8',NULL,8),('8','C','Neil Armstrong','C/CMSgt',2,'BMI',NULL,9),('9','C','Flight Commander','C/2d Lt',3,'10',NULL,11),('BMI','C','Gen. Billy Mitchell','C/2d Lt',2,'9',NULL,10),('BRG','S','Brigadier General','Brig_Gen',6,'MAG',NULL,9),('CAP','S','Captain','Capt',6,'MAJ',NULL,5),('CMS','S','Chief Master Sergeant','CMSgt',6,NULL,NULL,-1),('COL','S','Colonel','Col',6,'BRG',NULL,8),('EAK','C','Gen. Ira C Eaker','C/Lt Col',4,'SPA',NULL,20),('EAR','C','Emelia Earhart','C/Capt',3,'12',NULL,14),('FO','S','Flight Officer','FO',6,'TFO',NULL,0),('LCL','S','Lieutenant Colonel','Lt_Col',6,'COL',NULL,7),('MAG','S','Major General','Maj_Gen',6,NULL,NULL,10),('MAJ','S','Major','Maj',6,'LCL',NULL,6),('MS','S','Master Sergeant','MSgt',6,NULL,NULL,-3),('SFO','S','Senior Flight Officer','SFO',6,'2LT',NULL,2),('SMS','S','Senior Master Sergeant','SMSgt',6,NULL,NULL,-2),('SPA','C','Gen Carl A. Spaatz','C/Col',5,NULL,NULL,21),('SS','S','Staff Sergeant','SSgt',6,NULL,NULL,-5),('TFO','S','Techincal Flight Officer','TFO',6,'SFO',NULL,1),('TS','S','Technical Sergeant','TSgt',6,NULL,NULL,-4),('WB','C','Wright Brothers','C/SSgt',1,'4',NULL,4);
/*!40000 ALTER TABLE `ACHIEVEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ATTENDANCE`
--

DROP TABLE IF EXISTS `ATTENDANCE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ATTENDANCE` (
  `CAPID` int(11) NOT NULL,
  `EVENT_CODE` varchar(32) NOT NULL,
  PRIMARY KEY (`CAPID`,`EVENT_CODE`),
  KEY `CAPID` (`CAPID`),
  KEY `EVENT_CODE` (`EVENT_CODE`),
  CONSTRAINT `FK_ATTENDENCE_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_ATTENDENCE_EVENT` FOREIGN KEY (`EVENT_CODE`) REFERENCES `EVENT` (`EVENT_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ATTENDANCE`
--

LOCK TABLES `ATTENDANCE` WRITE;
/*!40000 ALTER TABLE `ATTENDANCE` DISABLE KEYS */;
/*!40000 ALTER TABLE `ATTENDANCE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AUDIT_DUMP`
--

DROP TABLE IF EXISTS `AUDIT_DUMP`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AUDIT_DUMP` (
  `TIME_OF_INTRUSION` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MICROSECONDS` decimal(6,6) NOT NULL DEFAULT '0.000000',
  `FIELD_NAME` varchar(32) NOT NULL,
  `FIELD_VALUE` varchar(200) NOT NULL,
  PRIMARY KEY (`TIME_OF_INTRUSION`,`MICROSECONDS`,`FIELD_NAME`),
  KEY `TIME_OF_INTRUSION` (`TIME_OF_INTRUSION`),
  KEY `FIELD_NAME` (`FIELD_NAME`),
  CONSTRAINT `FK_AUDIT_DUMP_TIME` FOREIGN KEY (`TIME_OF_INTRUSION`) REFERENCES `AUDIT_LOG` (`TIME_OF_INTRUSION`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AUDIT_DUMP`
--

LOCK TABLES `AUDIT_DUMP` WRITE;
/*!40000 ALTER TABLE `AUDIT_DUMP` DISABLE KEYS */;
/*!40000 ALTER TABLE `AUDIT_DUMP` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AUDIT_LOG`
--

DROP TABLE IF EXISTS `AUDIT_LOG`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AUDIT_LOG` (
  `TIME_OF_INTRUSION` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `MICROSECONDS` decimal(6,6) NOT NULL,
  `INTRUSION_TYPE` char(2) NOT NULL,
  `PAGE` varchar(50) NOT NULL,
  `IP_ADDRESS` varchar(15) NOT NULL,
  `NOTIFICATION` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`TIME_OF_INTRUSION`,`MICROSECONDS`),
  KEY `FK_AUDIT_TYPE` (`INTRUSION_TYPE`),
  CONSTRAINT `FK_AUDIT_TYPE` FOREIGN KEY (`INTRUSION_TYPE`) REFERENCES `INTRUSION_TYPE` (`INTRUSION_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AUDIT_LOG`
--

LOCK TABLES `AUDIT_LOG` WRITE;
/*!40000 ALTER TABLE `AUDIT_LOG` DISABLE KEYS */;
/*!40000 ALTER TABLE `AUDIT_LOG` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CAP_UNIT`
--

DROP TABLE IF EXISTS `CAP_UNIT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CAP_UNIT` (
  `CHARTER_NUM` char(10) NOT NULL,
  `REGION` char(3) NOT NULL,
  `WING` char(2) NOT NULL,
  `SQUAD_NAME` varchar(35) NOT NULL,
  `DEFAULT_UNIT` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CHARTER_NUM`),
  UNIQUE KEY `CHARTER_NUM` (`CHARTER_NUM`),
  KEY `FK_UNIT_REGION` (`REGION`),
  KEY `FK_UNIT_WING` (`WING`),
  CONSTRAINT `FK_UNIT_REGION` FOREIGN KEY (`REGION`) REFERENCES `REGION` (`REGION_CODE`),
  CONSTRAINT `FK_UNIT_WING` FOREIGN KEY (`WING`) REFERENCES `WING` (`WING`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CAP_UNIT`
--

LOCK TABLES `CAP_UNIT` WRITE;
/*!40000 ALTER TABLE `CAP_UNIT` DISABLE KEYS */;
/*!40000 ALTER TABLE `CAP_UNIT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONTACT_RELATIONS`
--

DROP TABLE IF EXISTS `CONTACT_RELATIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CONTACT_RELATIONS` (
  `RELATION_CODE` char(2) NOT NULL,
  `RELATION_NAME` varchar(20) NOT NULL,
  PRIMARY KEY (`RELATION_CODE`),
  UNIQUE KEY `RELATION_CODE` (`RELATION_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONTACT_RELATIONS`
--

LOCK TABLES `CONTACT_RELATIONS` WRITE;
/*!40000 ALTER TABLE `CONTACT_RELATIONS` DISABLE KEYS */;
INSERT INTO `CONTACT_RELATIONS` VALUES ('BR','Brother'),('FA','Father'),('GF','GrandFather'),('GM','GrandMother'),('MO','Mother'),('SI','Sister');
/*!40000 ALTER TABLE `CONTACT_RELATIONS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CPFT_ENTRANCE`
--

DROP TABLE IF EXISTS `CPFT_ENTRANCE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CPFT_ENTRANCE` (
  `CAPID` int(11) NOT NULL,
  `ACHIEV_CODE` varchar(5) NOT NULL,
  `TEST_TYPE` char(2) NOT NULL,
  `SCORE` float NOT NULL,
  PRIMARY KEY (`CAPID`,`ACHIEV_CODE`,`TEST_TYPE`),
  KEY `FK_CPFT_TEST_ACHIEV` (`ACHIEV_CODE`),
  KEY `FK_CPFT_TEST_ENTRANCE_TYPE` (`TEST_TYPE`),
  CONSTRAINT `FK_CPFT_TEST_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_CPFT_TEST_ACHIEV` FOREIGN KEY (`ACHIEV_CODE`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`),
  CONSTRAINT `FK_CPFT_TEST_ENTRANCE_TYPE` FOREIGN KEY (`TEST_TYPE`) REFERENCES `CPFT_TEST_TYPES` (`TEST_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CPFT_ENTRANCE`
--

LOCK TABLES `CPFT_ENTRANCE` WRITE;
/*!40000 ALTER TABLE `CPFT_ENTRANCE` DISABLE KEYS */;
/*!40000 ALTER TABLE `CPFT_ENTRANCE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CPFT_REQUIREMENTS`
--

DROP TABLE IF EXISTS `CPFT_REQUIREMENTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CPFT_REQUIREMENTS` (
  `GENDER` char(1) NOT NULL,
  `AGE` smallint(6) NOT NULL,
  `PHASE` smallint(6) NOT NULL,
  `TEST_TYPE` char(2) NOT NULL,
  `REQUIREMENT` float NOT NULL,
  `START_ACHIEV` varchar(5) NOT NULL DEFAULT '',
  `END_ACHIEV` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`GENDER`,`AGE`,`PHASE`,`START_ACHIEV`,`TEST_TYPE`),
  KEY `FK_CPFT_PHASE` (`PHASE`),
  KEY `FK_CPFT_TEST_TYPE` (`TEST_TYPE`),
  KEY `FK_CPFT_START_ACHIEV` (`START_ACHIEV`),
  KEY `FK_CPFT_END_ACHIEV` (`END_ACHIEV`),
  CONSTRAINT `FK_CPFT_END_ACHIEV` FOREIGN KEY (`END_ACHIEV`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`),
  CONSTRAINT `FK_CPFT_PHASE` FOREIGN KEY (`PHASE`) REFERENCES `PHASES` (`PHASE_NUM`),
  CONSTRAINT `FK_CPFT_START_ACHIEV` FOREIGN KEY (`START_ACHIEV`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`),
  CONSTRAINT `FK_CPFT_TEST_TYPE` FOREIGN KEY (`TEST_TYPE`) REFERENCES `CPFT_TEST_TYPES` (`TEST_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CPFT_REQUIREMENTS`
--

LOCK TABLES `CPFT_REQUIREMENTS` WRITE;
/*!40000 ALTER TABLE `CPFT_REQUIREMENTS` DISABLE KEYS */;
INSERT INTO `CPFT_REQUIREMENTS` VALUES ('F',12,1,'CU',29,'1',NULL),('F',12,1,'MR',12.7667,'1',NULL),('F',12,1,'PU',9,'1',NULL),('F',12,1,'RS',12.1,'1',NULL),('F',12,1,'SR',25,'1',NULL),('F',12,2,'CU',31,'4','6'),('F',12,2,'MR',12.0167,'4','6'),('F',12,2,'PU',10,'4','6'),('F',12,2,'RS',11.7,'4','6'),('F',12,2,'SR',27,'4','6'),('F',12,2,'CU',35,'7','BMI'),('F',12,2,'MR',11.0833,'7','BMI'),('F',12,2,'PU',11,'7','BMI'),('F',12,2,'RS',11.3,'7','BMI'),('F',12,2,'SR',30,'7','BMI'),('F',12,3,'CU',38,'9',NULL),('F',12,3,'MR',10.4333,'9',NULL),('F',12,3,'PU',14,'9',NULL),('F',12,3,'RS',11,'9',NULL),('F',12,3,'SR',32,'9',NULL),('F',13,1,'CU',30,'1',NULL),('F',13,1,'MR',12.4833,'1',NULL),('F',13,1,'PU',9,'1',NULL),('F',13,1,'RS',11.8,'1',NULL),('F',13,1,'SR',24,'1',NULL),('F',13,2,'CU',33,'4','6'),('F',13,2,'MR',11.6667,'4','6'),('F',13,2,'PU',10,'4','6'),('F',13,2,'RS',11.5,'4','6'),('F',13,2,'SR',27,'4','6'),('F',13,2,'CU',37,'7','BMI'),('F',13,2,'MR',10.3833,'7','BMI'),('F',13,2,'PU',11,'7','BMI'),('F',13,2,'RS',11.1,'7','BMI'),('F',13,2,'SR',31,'7','BMI'),('F',13,3,'CU',40,'9',NULL),('F',13,3,'MR',9.83333,'9',NULL),('F',13,3,'PU',15,'9',NULL),('F',13,3,'RS',10.9,'9',NULL),('F',13,3,'SR',32,'9',NULL),('F',13,4,'CU',41,'12',NULL),('F',13,4,'MR',9.25,'12',NULL),('F',13,4,'PU',17,'12',NULL),('F',13,4,'RS',10.7,'12',NULL),('F',13,4,'SR',34,'12',NULL),('F',14,1,'CU',31,'1',NULL),('F',14,1,'MR',11.8667,'1',NULL),('F',14,1,'PU',9,'1',NULL),('F',14,1,'RS',11.9,'1',NULL),('F',14,1,'SR',28,'1',NULL),('F',14,2,'CU',34,'4','6'),('F',14,2,'MR',11.1667,'4','6'),('F',14,2,'PU',10,'4','6'),('F',14,2,'RS',11.6,'4','6'),('F',14,2,'SR',30,'4','6'),('F',14,2,'CU',37,'7','BMI'),('F',14,2,'MR',10.1,'7','BMI'),('F',14,2,'PU',11,'7','BMI'),('F',14,2,'RS',11.2,'7','BMI'),('F',14,2,'SR',33,'7','BMI'),('F',14,3,'CU',40,'9',NULL),('F',14,3,'MR',9.45,'9',NULL),('F',14,3,'PU',15,'9',NULL),('F',14,3,'RS',10.9,'9',NULL),('F',14,3,'SR',35,'9',NULL),('F',14,4,'CU',42,'12',NULL),('F',14,4,'MR',8.96667,'12',NULL),('F',14,4,'PU',17,'12',NULL),('F',14,4,'RS',10.7,'12',NULL),('F',14,4,'SR',37,'12',NULL),('F',15,1,'CU',30,'1',NULL),('F',15,1,'MR',11.8,'1',NULL),('F',15,1,'PU',11,'1',NULL),('F',15,1,'RS',11.7,'1',NULL),('F',15,1,'SR',31,'1',NULL),('F',15,2,'CU',32,'4','6'),('F',15,2,'MR',11,'4','6'),('F',15,2,'PU',12,'4','6'),('F',15,2,'RS',11.4,'4','6'),('F',15,2,'SR',32,'4','6'),('F',15,2,'CU',36,'7','BMI'),('F',15,2,'MR',9.96667,'7','BMI'),('F',15,2,'PU',15,'7','BMI'),('F',15,2,'RS',11,'7','BMI'),('F',15,2,'SR',36,'7','BMI'),('F',15,3,'CU',39,'9',NULL),('F',15,3,'MR',9.38333,'9',NULL),('F',15,3,'PU',16,'9',NULL),('F',15,3,'RS',10.7,'9',NULL),('F',15,3,'SR',37,'9',NULL),('F',15,4,'CU',42,'12',NULL),('F',15,4,'MR',8.96667,'12',NULL),('F',15,4,'PU',18,'12',NULL),('F',15,4,'RS',10.5,'12',NULL),('F',15,4,'SR',40,'12',NULL),('F',16,1,'CU',30,'1',NULL),('F',16,1,'MR',12.7,'1',NULL),('F',16,1,'PU',11,'1',NULL),('F',16,1,'RS',11.7,'1',NULL),('F',16,1,'SR',30,'1',NULL),('F',16,2,'CU',32,'4','6'),('F',16,2,'MR',11.4,'4','6'),('F',16,2,'PU',13,'4','6'),('F',16,2,'RS',11.4,'4','6'),('F',16,2,'SR',32,'4','6'),('F',16,2,'CU',35,'7','BMI'),('F',16,2,'MR',10.5167,'7','BMI'),('F',16,2,'PU',12,'7','BMI'),('F',16,2,'RS',10.9,'7','BMI'),('F',16,2,'SR',34,'7','BMI'),('F',16,3,'CU',37,'9',NULL),('F',16,3,'MR',9.8,'9',NULL),('F',16,3,'PU',17,'9',NULL),('F',16,3,'RS',10.7,'9',NULL),('F',16,3,'SR',36,'9',NULL),('F',16,4,'CU',40,'12',NULL),('F',16,4,'MR',9.2,'12',NULL),('F',16,4,'PU',20,'12',NULL),('F',16,4,'RS',10.5,'12',NULL),('F',16,4,'SR',38,'12',NULL),('F',17,1,'CU',28,'1',NULL),('F',17,1,'MR',12.1833,'1',NULL),('F',17,1,'PU',12,'1',NULL),('F',17,1,'RS',11.7,'1',NULL),('F',17,1,'SR',31,'1',NULL),('F',17,2,'CU',30,'4','6'),('F',17,2,'MR',11.3333,'4','6'),('F',17,2,'PU',14,'4','6'),('F',17,2,'RS',11.3,'4','6'),('F',17,2,'SR',33,'4','6'),('F',17,2,'CU',34,'7','BMI'),('F',17,2,'MR',10.3667,'7','BMI'),('F',17,2,'PU',16,'7','BMI'),('F',17,2,'RS',11,'7','BMI'),('F',17,2,'SR',35,'7','BMI'),('F',17,3,'CU',36,'9',NULL),('F',17,3,'MR',9.85,'9',NULL),('F',17,3,'PU',19,'9',NULL),('F',17,3,'RS',10.7,'9',NULL),('F',17,3,'SR',37,'9',NULL),('F',17,4,'CU',39,'12',NULL),('F',17,4,'MR',9.23333,'12',NULL),('F',17,4,'PU',22,'12',NULL),('F',17,4,'RS',10.5,'12',NULL),('F',17,4,'SR',39,'12',NULL),('M',12,1,'CU',34,'1',NULL),('M',12,1,'MR',10.3667,'1',NULL),('M',12,1,'PU',12,'1',NULL),('M',12,1,'RS',11.2,'1',NULL),('M',12,1,'SR',21,'1',NULL),('M',12,2,'CU',37,'4','6'),('M',12,2,'MR',9.66667,'4','6'),('M',12,2,'PU',14,'4','6'),('M',12,2,'RS',11,'4','6'),('M',12,2,'SR',23,'4','6'),('M',12,2,'CU',40,'7','BMI'),('M',12,2,'MR',8.66667,'7','BMI'),('M',12,2,'PU',18,'7','BMI'),('M',12,2,'RS',10.6,'7','BMI'),('M',12,2,'SR',26,'7','BMI'),('M',12,3,'CU',43,'9',NULL),('M',12,3,'MR',8.23333,'9',NULL),('M',12,3,'PU',22,'9',NULL),('M',12,3,'RS',10.4,'9',NULL),('M',12,3,'SR',27,'9',NULL),('M',13,1,'CU',36,'1',NULL),('M',13,1,'MR',9.38333,'1',NULL),('M',13,1,'PU',16,'1',NULL),('M',13,1,'RS',10.8,'1',NULL),('M',13,1,'SR',20,'1',NULL),('M',13,2,'CU',39,'4','6'),('M',13,2,'MR',8.9,'4','6'),('M',13,2,'PU',18,'4','6'),('M',13,2,'RS',10.6,'4','6'),('M',13,2,'SR',23,'4','6'),('M',13,2,'CU',42,'7','BMI'),('M',13,2,'MR',8.1,'7','BMI'),('M',13,2,'PU',24,'7','BMI'),('M',13,2,'RS',10.2,'7','BMI'),('M',13,2,'SR',26,'7','BMI'),('M',13,3,'CU',45,'9',NULL),('M',13,3,'MR',7.68333,'9',NULL),('M',13,3,'PU',28,'9',NULL),('M',13,3,'RS',10.1,'9',NULL),('M',13,3,'SR',27,'9',NULL),('M',13,4,'CU',48,'12',NULL),('M',13,4,'MR',7.41667,'12',NULL),('M',13,4,'PU',32,'12',NULL),('M',13,4,'RS',9.9,'12',NULL),('M',13,4,'SR',29,'12',NULL),('M',14,1,'CU',39,'1',NULL),('M',14,1,'MR',9.16667,'1',NULL),('M',14,1,'PU',18,'1',NULL),('M',14,1,'RS',10.5,'1',NULL),('M',14,1,'SR',23,'1',NULL),('M',14,2,'CU',41,'4','6'),('M',14,2,'MR',8.5,'4','6'),('M',14,2,'PU',20,'4','6'),('M',14,2,'RS',10.2,'4','6'),('M',14,2,'SR',25,'4','6'),('M',14,2,'CU',45,'7','BMI'),('M',14,2,'MR',7.73333,'7','BMI'),('M',14,2,'PU',24,'7','BMI'),('M',14,2,'RS',9.9,'7','BMI'),('M',14,2,'SR',28,'7','BMI'),('M',14,3,'CU',48,'9',NULL),('M',14,3,'MR',7.31667,'9',NULL),('M',14,3,'PU',28,'9',NULL),('M',14,3,'RS',9.7,'9',NULL),('M',14,3,'SR',30,'9',NULL),('M',14,4,'CU',51,'12',NULL),('M',14,4,'MR',6.98333,'12',NULL),('M',14,4,'PU',34,'12',NULL),('M',14,4,'RS',9.5,'12',NULL),('M',14,4,'SR',32,'12',NULL),('M',15,1,'CU',38,'1',NULL),('M',15,1,'MR',8.81667,'1',NULL),('M',15,1,'PU',22,'1',NULL),('M',15,1,'RS',10.2,'1',NULL),('M',15,1,'SR',24,'1',NULL),('M',15,2,'CU',41,'4','6'),('M',15,2,'MR',8.13333,'4','6'),('M',15,2,'PU',25,'4','6'),('M',15,2,'RS',10,'4','6'),('M',15,2,'SR',27,'4','6'),('M',15,2,'CU',45,'7','BMI'),('M',15,2,'MR',7.5,'7','BMI'),('M',15,2,'PU',30,'7','BMI'),('M',15,2,'RS',9.7,'7','BMI'),('M',15,2,'SR',30,'7','BMI'),('M',15,3,'CU',49,'9',NULL),('M',15,3,'MR',7.1,'9',NULL),('M',15,3,'PU',34,'9',NULL),('M',15,3,'RS',9.5,'9',NULL),('M',15,3,'SR',32,'9',NULL),('M',15,4,'CU',52,'12',NULL),('M',15,4,'MR',6.85,'12',NULL),('M',15,4,'PU',37,'12',NULL),('M',15,4,'RS',9.3,'12',NULL),('M',15,4,'SR',33,'12',NULL),('M',16,1,'CU',38,'1',NULL),('M',16,1,'MR',8.61667,'1',NULL),('M',16,1,'PU',24,'1',NULL),('M',16,1,'RS',10,'1',NULL),('M',16,1,'SR',25,'1',NULL),('M',16,2,'CU',40,'4','6'),('M',16,2,'MR',7.88333,'4','6'),('M',16,2,'PU',26,'4','6'),('M',16,2,'RS',9.7,'4','6'),('M',16,2,'SR',27,'4','6'),('M',16,2,'CU',45,'7','BMI'),('M',16,2,'MR',7.16667,'7','BMI'),('M',16,2,'PU',30,'7','BMI'),('M',16,2,'RS',9.4,'7','BMI'),('M',16,2,'SR',30,'7','BMI'),('M',16,3,'CU',48,'9',NULL),('M',16,3,'MR',6.83333,'9',NULL),('M',16,3,'PU',35,'9',NULL),('M',16,3,'RS',9.2,'9',NULL),('M',16,3,'SR',32,'9',NULL),('M',16,4,'CU',50,'12',NULL),('M',16,4,'MR',6.63333,'12',NULL),('M',16,4,'PU',38,'12',NULL),('M',16,4,'RS',9,'12',NULL),('M',16,4,'SR',35,'12',NULL),('M',17,1,'CU',38,'1',NULL),('M',17,1,'MR',8.1,'1',NULL),('M',17,1,'PU',26,'1',NULL),('M',17,1,'RS',9.9,'1',NULL),('M',17,1,'SR',28,'1',NULL),('M',17,2,'CU',40,'4','6'),('M',17,2,'MR',7.58333,'4','6'),('M',17,2,'PU',30,'4','6'),('M',17,2,'RS',9.6,'4','6'),('M',17,2,'SR',31,'4','6'),('M',17,2,'CU',44,'7','BMI'),('M',17,2,'MR',7.06667,'7','BMI'),('M',17,2,'PU',37,'7','BMI'),('M',17,2,'RS',9.4,'7','BMI'),('M',17,2,'SR',34,'7','BMI'),('M',17,3,'CU',46,'9',NULL),('M',17,3,'MR',6.83333,'9',NULL),('M',17,3,'PU',42,'9',NULL),('M',17,3,'RS',9.2,'9',NULL),('M',17,3,'SR',36,'9',NULL),('M',17,4,'CU',49,'12',NULL),('M',17,4,'MR',6.58333,'12',NULL),('M',17,4,'PU',46,'12',NULL),('M',17,4,'RS',9,'12',NULL),('M',17,4,'SR',39,'12',NULL);
/*!40000 ALTER TABLE `CPFT_REQUIREMENTS` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `CPFT_TEST_TYPES`
--

DROP TABLE IF EXISTS `CPFT_TEST_TYPES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CPFT_TEST_TYPES` (
  `TEST_CODE` char(2) NOT NULL,
  `TEST_NAME` varchar(20) NOT NULL,
  `IS_RUNNING` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`TEST_CODE`),
  UNIQUE KEY `TEST_CODE` (`TEST_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CPFT_TEST_TYPES`
--

LOCK TABLES `CPFT_TEST_TYPES` WRITE;
/*!40000 ALTER TABLE `CPFT_TEST_TYPES` DISABLE KEYS */;
INSERT INTO `CPFT_TEST_TYPES` VALUES ('CU','Curl-ups',0),('MR','Mile Run',1),('PU','Push-ups',0),('RS','Shuttle Run',1),('SR','Sit and Reach',0);
/*!40000 ALTER TABLE `CPFT_TEST_TYPES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DELETE_REQUESTS`
--

DROP TABLE IF EXISTS `DELETE_REQUESTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DELETE_REQUESTS` (
  `REQUESTER` int(11) NOT NULL,
  `REQUEST_DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `REQUEST_NANO`  decimal(6,6) NOT NULL,
  `CLEAR_AUDIT` tinyint(1) NOT NULL DEFAULT '0',
  `CLEAR_LOGIN` tinyint(1) NOT NULL DEFAULT '0',
  `DELETE_MEMBER` int(11)  NULL DEFAULT null,
  PRIMARY KEY (`REQUESTER`,`REQUEST_DATE`,`REQUEST_NANO`),
  KEY `FK_DELETE_LOGS_DELETEE` (`DELETE_MEMBER`),
  CONSTRAINT `FK_DELETE_LOGS_REQUESTER` FOREIGN KEY (`REQUESTER`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_DELETE_LOGS_DELETEE` FOREIGN KEY (`DELETE_MEMBER`) REFERENCES `MEMBER` (`CAPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DELETE_REQUESTS`
--

LOCK TABLES `DELETE_REQUESTS` WRITE;
/*!40000 ALTER TABLE `DELETE_REQUESTS` DISABLE KEYS */;
/*!40000 ALTER TABLE `DELETE_REQUESTS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DISCIPLINE_LOG`
--

DROP TABLE IF EXISTS `DISCIPLINE_LOG`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DISCIPLINE_LOG` (
  `CAPID` int(11) NOT NULL,
  `TYPE_OF_ACTION` char(3) NOT NULL,
  `EVENT_CODE` varchar(32) NOT NULL,
  `OFFENSE` char(3) NOT NULL,
  `SEVERITY` smallint(6) NOT NULL,
  `GIVEN_BY` int(11) NOT NULL,
  `DETAILS` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`CAPID`,`TYPE_OF_ACTION`,`EVENT_CODE`,`OFFENSE`,`GIVEN_BY`),
  KEY `FK_DISCIPLINE_GIVEN_BY` (`GIVEN_BY`),
  KEY `CAPID` (`CAPID`),
  KEY `EVENT_CODE` (`EVENT_CODE`),
  KEY `OFFENSE` (`OFFENSE`),
  KEY `TYPE_OF_ACTION` (`TYPE_OF_ACTION`),
  CONSTRAINT `FK_DISCIPLINE_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_DISCIPLINE_DISCIPLINE_ACTION` FOREIGN KEY (`TYPE_OF_ACTION`) REFERENCES `DISCIPLINE_TYPE` (`DISCIPLINE_CODE`),
  CONSTRAINT `FK_DISCIPLINE_EVENT_CODE` FOREIGN KEY (`EVENT_CODE`) REFERENCES `EVENT` (`EVENT_CODE`),
  CONSTRAINT `FK_DISCIPLINE_OFFENSE` FOREIGN KEY (`OFFENSE`) REFERENCES `DISCIPLINE_OFFENSES` (`OFFENSE_CODE`),
  CONSTRAINT `FK_DISCIPLINE_GIVEN_BY` FOREIGN KEY (`GIVEN_BY`) REFERENCES `MEMBER` (`CAPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DISCIPLINE_LOG`
--

LOCK TABLES `DISCIPLINE_LOG` WRITE;
/*!40000 ALTER TABLE `DISCIPLINE_LOG` DISABLE KEYS */;
/*!40000 ALTER TABLE `DISCIPLINE_LOG` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DISCIPLINE_OFFENSES`
--

DROP TABLE IF EXISTS `DISCIPLINE_OFFENSES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DISCIPLINE_OFFENSES` (
  `OFFENSE_CODE` char(3) NOT NULL,
  `OFFENSE_NAME` varchar(64) NOT NULL,
  PRIMARY KEY (`OFFENSE_CODE`),
  UNIQUE KEY `OFFENSE_CODE` (`OFFENSE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DISCIPLINE_OFFENSES`
--

LOCK TABLES `DISCIPLINE_OFFENSES` WRITE;
/*!40000 ALTER TABLE `DISCIPLINE_OFFENSES` DISABLE KEYS */;
INSERT INTO `DISCIPLINE_OFFENSES` VALUES ('DIS','disrespectful behavior'),('LGS','Not meeting the grooming standards'),('SPE','Speaking out of turn'),('UNP','Unproffesional behavior');
/*!40000 ALTER TABLE `DISCIPLINE_OFFENSES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DISCIPLINE_TYPE`
--

DROP TABLE IF EXISTS `DISCIPLINE_TYPE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DISCIPLINE_TYPE` (
  `DISCIPLINE_CODE` char(3) NOT NULL,
  `DISCIPLINE_NAME` varchar(32) NOT NULL,
  PRIMARY KEY (`DISCIPLINE_CODE`),
  UNIQUE KEY `DISCIPLINE_CODE` (`DISCIPLINE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DISCIPLINE_TYPE`
--

LOCK TABLES `DISCIPLINE_TYPE` WRITE;
/*!40000 ALTER TABLE `DISCIPLINE_TYPE` DISABLE KEYS */;
/*!40000 ALTER TABLE `DISCIPLINE_TYPE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EMERGENCY_CONTACT`
--

DROP TABLE IF EXISTS `EMERGENCY_CONTACT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EMERGENCY_CONTACT` (
  `CAPID` int(11) NOT NULL,
  `RELATION` char(2) NOT NULL,
  `CONTACT_NAME` varchar(32) NOT NULL,
  `CONTACT_NUMBER` varchar(12) NOT NULL,
  PRIMARY KEY (`CAPID`,`RELATION`),
  KEY `FK_EMERGENCY_RELATION` (`RELATION`),
  KEY `CAPID` (`CAPID`),
  CONSTRAINT `FK_EMERGENCY_CONTACT_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_EMERGENCY_RELATION` FOREIGN KEY (`RELATION`) REFERENCES `CONTACT_RELATIONS` (`RELATION_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EMERGENCY_CONTACT`
--

LOCK TABLES `EMERGENCY_CONTACT` WRITE;
/*!40000 ALTER TABLE `EMERGENCY_CONTACT` DISABLE KEYS */;
/*!40000 ALTER TABLE `EMERGENCY_CONTACT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EVENT`
--

DROP TABLE IF EXISTS `EVENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EVENT` (
  `EVENT_CODE` varchar(32) NOT NULL,
  `EVENT_DATE` date NOT NULL,
  `EVENT_TYPE` varchar(2) NOT NULL,
  `EVENT_NAME` varchar(32) DEFAULT NULL,
  `IS_CURRENT` tinyint(1) NOT NULL DEFAULT '0',
  `LOCATION` varchar(5) DEFAULT NULL,
  `END_DATE` date DEFAULT NULL,
  PRIMARY KEY (`EVENT_CODE`),
  UNIQUE KEY `EVENT_CODE` (`EVENT_CODE`),
  KEY `EVENT_DATE` (`EVENT_DATE`),
  KEY `EVENT_TYPE` (`EVENT_TYPE`),
  KEY `EVENT_NAME` (`EVENT_NAME`),
  KEY `LOCATION` (`LOCATION`),
  CONSTRAINT `FK_EVENT_TYPE` FOREIGN KEY (`EVENT_TYPE`) REFERENCES `EVENT_TYPES` (`EVENT_TYPE_CODE`),
  CONSTRAINT `FK_EVENT_LOCAT` FOREIGN KEY (`LOCATION`) REFERENCES `EVENT_LOCATION` (`LOCAT_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EVENT`
--

LOCK TABLES `EVENT` WRITE;
/*!40000 ALTER TABLE `EVENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `EVENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EVENT_LOCATION`
--

DROP TABLE IF EXISTS `EVENT_LOCATION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EVENT_LOCATION` (
  `LOCAT_CODE` varchar(5) NOT NULL,
  `LOCAT_NAME` varchar(50) NOT NULL,
  `DEFAULT_LOCAT` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`LOCAT_CODE`),
  UNIQUE KEY `LOCAT_CODE` (`LOCAT_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EVENT_LOCATION`
--

LOCK TABLES `EVENT_LOCATION` WRITE;
/*!40000 ALTER TABLE `EVENT_LOCATION` DISABLE KEYS */;
/*!40000 ALTER TABLE `EVENT_LOCATION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EVENT_TYPES`
--

DROP TABLE IF EXISTS `EVENT_TYPES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EVENT_TYPES` (
  `EVENT_TYPE_CODE` varchar(5) NOT NULL,
  `EVENT_TYPE_NAME` varchar(40) NOT NULL,
  PRIMARY KEY (`EVENT_TYPE_CODE`),
  UNIQUE KEY `EVENT_TYPE_CODE` (`EVENT_TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EVENT_TYPES`
--

LOCK TABLES `EVENT_TYPES` WRITE;
/*!40000 ALTER TABLE `EVENT_TYPES` DISABLE KEYS */;
INSERT INTO `EVENT_TYPES` VALUES ('CG','Color Guard'),('CP','CyberPatriot'),('ENC','Basic Encampment'),('HG','Honor Guard'),('M','Meeting'),('SARX','Search and Rescue Exercise'),('SE','Squadron Events');
/*!40000 ALTER TABLE `EVENT_TYPES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `GRADE`
--

DROP TABLE IF EXISTS `GRADE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GRADE` (
  `GRADE_ABREV` varchar(10) NOT NULL,
  `MEMBER_TYPE` char(1) NOT NULL,
  `GRADE_NAME` varchar(32) NOT NULL,
  PRIMARY KEY (`GRADE_ABREV`),
  UNIQUE KEY `GRADE_ABREV` (`GRADE_ABREV`),
  KEY `FK_GRADE_MEMBER_TYPE` (`MEMBER_TYPE`),
  CONSTRAINT `FK_GRADE_MEMBER_TYPE` FOREIGN KEY (`MEMBER_TYPE`) REFERENCES `MEMBERSHIP_TYPES` (`MEMBER_TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `GRADE`
--

LOCK TABLES `GRADE` WRITE;
/*!40000 ALTER TABLE `GRADE` DISABLE KEYS */;
INSERT INTO `GRADE` VALUES ('1st_Lt','S','First Lieutenant'),('2d_Lt','S','Second Lieutenant'),('Brig_Gen','S','Brigadier General'),('C/1st Lt','C','C/1st Lieutenant'),('C/2d Lt','C','C/2nd Lieutenant'),('C/A1C','C','Airman 1st Class'),('C/AB','C','Airman Basic'),('C/Amn','C','Airman'),('C/Capt','C','C/Captain'),('C/CMSgt','C','C/Chief Master Sergeant'),('C/Col','C','C/Colonel'),('C/Lt Col','C','C/Lieutenant Colonel'),('C/Maj','C','C/Major'),('C/MSgt','C','C/Master Sergeant'),('C/SMSgt','C','C/Senior Master Sergeant'),('C/SrA','C','Senior Airman'),('C/SSgt','C','C/Staff Sergeant'),('C/TSgt','C','C/Technical Sergeant'),('Capt','S','Captain'),('CMSgt','S','Chief Master Sergeant'),('Col','S','Colonel'),('FO','S','Flight Officer'),('Lt_Col','S','Lieutenant Colonel'),('Maj','S','Major'),('Maj_Gen','S','Major General'),('MSgt','S','Master Sergeant'),('SFO','S','Senior Flight Officer'),('SM','S','Senior Member'),('SMSgt','S','Senior Master Sergeant'),('SSgt','S','Staff Sergeant'),('TFO','S','Technical Flight Officer'),('TSgt','S','Technical Sergeant');
/*!40000 ALTER TABLE `GRADE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `INTRUSION_TYPE`
--

DROP TABLE IF EXISTS `INTRUSION_TYPE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `INTRUSION_TYPE` (
  `INTRUSION_CODE` char(2) NOT NULL,
  `INTRUSION_NAME` varchar(50) NOT NULL,
  PRIMARY KEY (`INTRUSION_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `INTRUSION_TYPE`
--

LOCK TABLES `INTRUSION_TYPE` WRITE;
/*!40000 ALTER TABLE `INTRUSION_TYPE` DISABLE KEYS */;
INSERT INTO `INTRUSION_TYPE` VALUES ('DC','Page direct call'),('DR','Delete Record'),('ER','Error with DB'),('EX','php exception'),('FA','File upload attack'),('FM','File upload exceeded max. size'),('FR','File upload error'),('FT','File Upload of improper type'),('KS','Killed a Session'),('RS','Re-signin'),('SH','Session Hijacking'),('SI','SQL injections'),('UF','File Upload');
/*!40000 ALTER TABLE `INTRUSION_TYPE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LOGIN_LOG`
--

DROP TABLE IF EXISTS `LOGIN_LOG`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LOGIN_LOG` (
  `TIME_LOGIN` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CAPID` int(11) NOT NULL,
  `IP_ADDRESS` varchar(15) NOT NULL,
  `SUCEEDED` tinyint(1) NOT NULL,
  `FACTORED` tinyint(1) NOT NULL DEFAULT '1',
  `LOG_OFF` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`TIME_LOGIN`,`CAPID`,`IP_ADDRESS`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LOGIN_LOG`
--

LOCK TABLES `LOGIN_LOG` WRITE;
/*!40000 ALTER TABLE `LOGIN_LOG` DISABLE KEYS */;
/*!40000 ALTER TABLE `LOGIN_LOG` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MEMBER`
--

DROP TABLE IF EXISTS `MEMBER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MEMBER` (
  `CAPID` int(11) NOT NULL,
  `NAME_LAST` varchar(32) NOT NULL,
  `NAME_FIRST` varchar(32) NOT NULL,
  `GENDER` char(1) NOT NULL,
  `DATE_OF_BIRTH` date NOT NULL,
  `ACHIEVEMENT` varchar(5) NOT NULL DEFAULT '0',
  `MEMBER_TYPE` char(1) NOT NULL,
  `TEXTBOOK_SET` varchar(5) DEFAULT NULL,
  `HOME_UNIT` char(10) NOT NULL DEFAULT 'RMR-ID-073',
  `DATE_JOINED` date NOT NULL,
  `DATE_CURRENT` date NOT NULL,
  `DATE_TERMINATED` date DEFAULT NULL,
  `APPROVED` tinyint(1) NOT NULL DEFAULT '0',
  `PASS_HASH` char(88) DEFAULT NULL,
  `LAST_PASS_CHANGE` date DEFAULT NULL,
  PRIMARY KEY (`CAPID`),
  UNIQUE KEY `CAPID` (`CAPID`),
  KEY `FK_MEMBER_TEXT_SET` (`TEXTBOOK_SET`),
  KEY `FK_MEMBER_HOME_UNIT` (`HOME_UNIT`),
  KEY `NAME_LAST` (`NAME_LAST`),
  KEY `NAME_FIRST` (`NAME_FIRST`),
  KEY `MEMBER_TYPE` (`MEMBER_TYPE`),
  KEY `ACHIEVEMENT` (`ACHIEVEMENT`),
  CONSTRAINT `FK_MEMBER_ACHIEVEMENT` FOREIGN KEY (`ACHIEVEMENT`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`),
  CONSTRAINT `FK_MEMBER_MEMBER_TYPE` FOREIGN KEY (`MEMBER_TYPE`) REFERENCES `MEMBERSHIP_TYPES` (`MEMBER_TYPE_CODE`),
  CONSTRAINT `FK_MEMBER_TEXT_SET` FOREIGN KEY (`TEXTBOOK_SET`) REFERENCES `TEXT_SETS` (`TEXT_SET_CODE`),
  CONSTRAINT `FK_MEMBER_HOME_UNIT` FOREIGN KEY (`HOME_UNIT`) REFERENCES `CAP_UNIT` (`CHARTER_NUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MEMBER`
--

LOCK TABLES `MEMBER` WRITE;
/*!40000 ALTER TABLE `MEMBER` DISABLE KEYS */;
/*!40000 ALTER TABLE `MEMBER` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MEMBERSHIP_TYPES`
--

DROP TABLE IF EXISTS `MEMBERSHIP_TYPES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MEMBERSHIP_TYPES` (
  `MEMBER_TYPE_CODE` char(1) NOT NULL,
  `MEMBER_TYPE_NAME` varchar(20) NOT NULL,
  PRIMARY KEY (`MEMBER_TYPE_CODE`),
  UNIQUE KEY `MEMBER_TYPE_CODE` (`MEMBER_TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MEMBERSHIP_TYPES`
--

LOCK TABLES `MEMBERSHIP_TYPES` WRITE;
/*!40000 ALTER TABLE `MEMBERSHIP_TYPES` DISABLE KEYS */;
INSERT INTO `MEMBERSHIP_TYPES` VALUES ('A','Aerospace Educator M'),('C','cadet'),('L','Legislative and Cong'),('P','Cadet Sponsor Member'),('S','senior member');
/*!40000 ALTER TABLE `MEMBERSHIP_TYPES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `NEW_MEMBER`
--

DROP TABLE IF EXISTS `NEW_MEMBER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NEW_MEMBER` (
  `NAME_LAST` varchar(50) NOT NULL,
  `NAME_FIRST` varchar(50) NOT NULL,
  `DATE_CAME` date DEFAULT NULL,
  `EMERGENCY_CONTACT_NAME` varchar(50) NOT NULL,
  `EMERGENCY_CONTACT_NUMBER` varchar(12) NOT NULL,
  `GREETED` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`NAME_LAST`,`NAME_FIRST`),
  KEY `NAME_LAST` (`NAME_LAST`),
  KEY `GREETED` (`GREETED`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NEW_MEMBER`
--

LOCK TABLES `NEW_MEMBER` WRITE;
/*!40000 ALTER TABLE `NEW_MEMBER` DISABLE KEYS */;
/*!40000 ALTER TABLE `NEW_MEMBER` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `NEXT_VISIT`
--

DROP TABLE IF EXISTS `NEXT_VISIT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NEXT_VISIT` (
  `LAST_CODE` char(3) NOT NULL,
  `NEXT_URL` varchar(128) NOT NULL,
  PRIMARY KEY (`LAST_CODE`,`NEXT_URL`),
  CONSTRAINT `FK_NEXT_VIST_OLD` FOREIGN KEY (`LAST_CODE`) REFERENCES `TASKS` (`TASK_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NEXT_VISIT`
--

LOCK TABLES `NEXT_VISIT` WRITE;
/*!40000 ALTER TABLE `NEXT_VISIT` DISABLE KEYS */;
INSERT INTO `NEXT_VISIT` VALUES ('ADL','member/report.php'),('EST','member/report.php'),('PRB','member/report.php'),('CLO','adminis/deleteIt.php'),('CLO','member/report.php'),('DDE','member/report.php'),('DME','adminis/finishRecordDel.php'),('EVR','member/report.php'),('LLO','member/report.php'),('MEA','member/finalApprove.php'),('MSE','member/report.php'),('PSE','member/report.php'),('PTT','testing/ptCSV.php'),('SPM','member/report.php'),('TSE','member/report.php'),('PTT','member/report.php'),('DME','member/report.php');
/*!40000 ALTER TABLE `NEXT_VISIT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PHASES`
--

DROP TABLE IF EXISTS `PHASES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PHASES` (
  `PHASE_NUM` smallint(6) NOT NULL DEFAULT '0',
  `MEMBER_TYPE` char(1) NOT NULL DEFAULT '',
  `PHASE_NAME` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`PHASE_NUM`,`MEMBER_TYPE`),
  KEY `FK_PHASE_MEMBER_TYPE` (`MEMBER_TYPE`),
  KEY `PHASE_NUM` (`PHASE_NUM`),
  CONSTRAINT `FK_PHASE_MEMBER_TYPE` FOREIGN KEY (`MEMBER_TYPE`) REFERENCES `MEMBERSHIP_TYPES` (`MEMBER_TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PHASES`
--

LOCK TABLES `PHASES` WRITE;
/*!40000 ALTER TABLE `PHASES` DISABLE KEYS */;
INSERT INTO `PHASES` VALUES (1,'C','The Learning Phase'),(2,'C','The Leadership Phase'),(3,'C','The command Phase'),(4,'C','The Executive Phase'),(5,'C','Spaatz'),(6,'S','Senior Member');
/*!40000 ALTER TABLE `PHASES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PROMOTION_BOARD`
--

DROP TABLE IF EXISTS `PROMOTION_BOARD`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROMOTION_BOARD` (
  `CAPID` int(11) NOT NULL,
  `BOARD_DATE` date NOT NULL,
  `APPROVED` tinyint(1) NOT NULL DEFAULT '1',
  `NEXT_SCHEDULED` date DEFAULT NULL,
  `BOARD_PRESIDENT` int(11) NOT NULL,
  PRIMARY KEY (`CAPID`,`BOARD_DATE`),
  KEY `FK_PROMO_BOARD_PRES` (`BOARD_PRESIDENT`),
  KEY `CAPID` (`CAPID`),
  CONSTRAINT `FK_PROMO_BOARD_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_PROMO_BOARD_PRES` FOREIGN KEY (`BOARD_PRESIDENT`) REFERENCES `MEMBER` (`CAPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PROMOTION_BOARD`
--

LOCK TABLES `PROMOTION_BOARD` WRITE;
/*!40000 ALTER TABLE `PROMOTION_BOARD` DISABLE KEYS */;
/*!40000 ALTER TABLE `PROMOTION_BOARD` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PROMOTION_RECORD`
--

DROP TABLE IF EXISTS `PROMOTION_RECORD`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROMOTION_RECORD` (
  `CAPID` int(11) NOT NULL,
  `ACHIEVEMENT` varchar(5) NOT NULL,
  `DATE_PROMOTED` date NOT NULL,
  `APROVER` int(11) NOT NULL,
  `ON_ESERVICES` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CAPID`,`ACHIEVEMENT`),
  KEY `FK_PROMOTIONS_ACHIEVEMENT` (`ACHIEVEMENT`),
  KEY `FK_PROMOTIONS_APROVE` (`APROVER`),
  KEY `CAPID` (`CAPID`),
  CONSTRAINT `FK_PROMOTIONS_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_PROMOTIONS_ACHIEVEMENT` FOREIGN KEY (`ACHIEVEMENT`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`),
  CONSTRAINT `FK_PROMOTIONS_APROVE` FOREIGN KEY (`APROVER`) REFERENCES `MEMBER` (`CAPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PROMOTION_RECORD`
--

LOCK TABLES `PROMOTION_RECORD` WRITE;
/*!40000 ALTER TABLE `PROMOTION_RECORD` DISABLE KEYS */;
/*!40000 ALTER TABLE `PROMOTION_RECORD` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PROMOTION_REQUIREMENT`
--

DROP TABLE IF EXISTS `PROMOTION_REQUIREMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROMOTION_REQUIREMENT` (
  `ACHIEV_CODE` varchar(5) NOT NULL,
  `REQUIREMENT_TYPE` char(2) NOT NULL,
  `TEXT_SET` varchar(5) NOT NULL DEFAULT '',
  `NAME` varchar(50) DEFAULT NULL,
  `PASSING_PERCENT` float DEFAULT NULL,
  `IS_ONLINE` tinyint(1) NOT NULL DEFAULT '0',
  `NUMBER_QUESTIONS` int(11) DEFAULT NULL,
  PRIMARY KEY (`ACHIEV_CODE`,`REQUIREMENT_TYPE`,`TEXT_SET`),
  KEY `FK_PROMO_REQUIRE_TEXT_SET` (`TEXT_SET`),
  KEY `ACHIEV_CODE` (`ACHIEV_CODE`),
  KEY `REQUIREMENT_TYPE` (`REQUIREMENT_TYPE`),
  CONSTRAINT `FK_PROM_REQUIRE_ACHIEVEMENT` FOREIGN KEY (`ACHIEV_CODE`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`),
  CONSTRAINT `FK_PROMO_REQUIRE_TYPE` FOREIGN KEY (`REQUIREMENT_TYPE`) REFERENCES `REQUIREMENT_TYPE` (`TYPE_CODE`),
  CONSTRAINT `FK_PROMO_REQUIRE_TEXT_SET` FOREIGN KEY (`TEXT_SET`) REFERENCES `TEXT_SETS` (`TEXT_SET_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PROMOTION_REQUIREMENT`
--

LOCK TABLES `PROMOTION_REQUIREMENT` WRITE;
/*!40000 ALTER TABLE `PROMOTION_REQUIREMENT` DISABLE KEYS */;
INSERT INTO `PROMOTION_REQUIREMENT` VALUES ('1','AC','ALL',NULL,NULL,0,NULL),('1','CD','ALL','Foundations Module',NULL,0,NULL),('1','CO','ALL',NULL,NULL,0,NULL),('1','DT','L2L',NULL,0.7333,0,NULL),('1','GS','ALL',NULL,NULL,0,NULL),('1','LT','L2L','Chapter 1',0.8,1,NULL),('1','PT','ALL',NULL,NULL,0,NULL),('1','SA','ALL',NULL,NULL,0,NULL),('10','AC','ALL',NULL,NULL,0,NULL),('10','AE','ALL','Chapters 2,9,10',0.8,1,NULL),('10','CD','ALL',NULL,NULL,0,NULL),('10','CO','ALL',NULL,NULL,0,NULL),('10','GS','ALL',NULL,NULL,0,NULL),('10','LT','L2L','Chapter 10',0.8,1,NULL),('10','ME','ALL',NULL,NULL,0,NULL),('10','PT','ALL',NULL,NULL,0,NULL),('10','SA','ALL',NULL,NULL,0,NULL),('10','SD','ALL','Administrative Officer',NULL,0,NULL),('11','AC','ALL',NULL,NULL,0,NULL),('11','AE','L2L','Chapters 3,18,19',0.8,1,NULL),('11','CD','ALL',NULL,NULL,0,NULL),('11','CO','ALL',NULL,NULL,0,NULL),('11','GS','ALL',NULL,NULL,0,NULL),('11','LT','L2L','Chapter 11',0.8,1,NULL),('11','ME','ALL',NULL,NULL,0,NULL),('11','PT','ALL',NULL,NULL,0,NULL),('11','SA','ALL',NULL,NULL,0,NULL),('11','SD','ALL','Public Affairs Officer',NULL,0,NULL),('12','AC','ALL',NULL,NULL,0,NULL),('12','CD','ALL',NULL,NULL,0,NULL),('12','CO','ALL',NULL,NULL,0,NULL),('12','GS','ALL',NULL,NULL,0,NULL),('12','LT','L2L','Chapters 12',0.8,1,NULL),('12','ME','ALL',NULL,NULL,0,NULL),('12','PT','ALL',NULL,NULL,0,NULL),('12','SA','ALL',NULL,NULL,0,NULL),('12','SD','ALL','Leadership Officer',NULL,0,NULL),('13','AC','ALL',NULL,NULL,0,NULL),('13','CD','ALL',NULL,NULL,0,NULL),('13','CO','ALL',NULL,NULL,0,NULL),('13','GS','ALL',NULL,NULL,0,NULL),('13','LT','L2L','Chapters 13',0.8,1,NULL),('13','ME','ALL',NULL,NULL,0,NULL),('13','PT','ALL',NULL,NULL,0,NULL),('13','SA','ALL',NULL,NULL,0,NULL),('13','SD','ALL','Aerospace Education Officer',NULL,0,NULL),('14','AC','ALL',NULL,NULL,0,NULL),('14','AE','ALL','Chapters 4,21,23',0.8,1,NULL),('14','CD','ALL',NULL,NULL,0,NULL),('14','CO','ALL',NULL,NULL,0,NULL),('14','GS','ALL',NULL,NULL,0,NULL),('14','LT','L2L','Chapters 14',0.8,1,NULL),('14','ME','ALL',NULL,NULL,0,NULL),('14','PT','ALL',NULL,NULL,0,NULL),('14','SA','ALL',NULL,NULL,0,NULL),('14','SD','ALL','Operations Officer',NULL,0,NULL),('15','AC','ALL',NULL,NULL,0,NULL),('15','AE','ALL','Chapters 5,24,25',0.8,1,NULL),('15','CD','ALL',NULL,NULL,0,NULL),('15','CO','ALL',NULL,NULL,0,NULL),('15','GS','ALL',NULL,NULL,0,NULL),('15','LT','L2L','Chapters 15',0.8,1,NULL),('15','ME','ALL',NULL,NULL,0,NULL),('15','PT','ALL',NULL,NULL,0,NULL),('15','SA','ALL',NULL,NULL,0,NULL),('15','SD','ALL','Logistics Officer',NULL,0,NULL),('16','AC','ALL',NULL,NULL,0,NULL),('16','AE','ALL','Chapters 6,26,27',0.8,1,NULL),('16','CD','ALL',NULL,NULL,0,NULL),('16','CO','ALL',NULL,NULL,0,NULL),('16','GS','ALL',NULL,NULL,0,NULL),('16','LT','L2L','Chapters 16',0.8,1,NULL),('16','ME','ALL',NULL,NULL,0,NULL),('16','PT','ALL',NULL,NULL,0,NULL),('16','SA','ALL',NULL,NULL,0,NULL),('16','SD','ALL','Cadet Commander',NULL,0,NULL),('2','AC','ALL',NULL,NULL,0,NULL),('2','AE','ALL','Module 1',0.8,1,NULL),('2','CD','ALL',NULL,NULL,0,NULL),('2','CO','ALL',NULL,NULL,0,NULL),('2','DT','L2L',NULL,0.7333,0,NULL),('2','GS','ALL',NULL,NULL,0,NULL),('2','LT','L2L','Chapter 2',0.8,1,NULL),('2','PT','ALL',NULL,NULL,0,NULL),('2','SA','ALL',NULL,NULL,0,NULL),('3','AC','ALL',NULL,NULL,0,NULL),('3','AE','ALL','Module 2',0.8,1,NULL),('3','CD','ALL',NULL,NULL,0,NULL),('3','CO','ALL',NULL,NULL,0,NULL),('3','DT','L2L',NULL,0.727273,0,NULL),('3','GS','ALL',NULL,NULL,0,NULL),('3','LT','L2L','Chapter 3',0.8,1,NULL),('3','PT','ALL',NULL,NULL,0,NULL),('3','SA','ALL',NULL,NULL,0,NULL),('4','AC','ALL',NULL,NULL,0,NULL),('4','AE','ALL','Module 3',0.8,1,NULL),('4','CD','ALL',NULL,NULL,0,NULL),('4','CO','ALL',NULL,NULL,0,NULL),('4','DT','L2L',NULL,0.8,0,NULL),('4','GS','ALL',NULL,NULL,0,NULL),('4','LT','L2L','Chapter 4',0.8,1,NULL),('4','PT','ALL',NULL,NULL,0,NULL),('4','SA','ALL',NULL,NULL,0,NULL),('5','AC','ALL',NULL,NULL,0,NULL),('5','AE','ALL','Module 4',0.8,1,NULL),('5','CD','ALL',NULL,NULL,0,NULL),('5','CO','ALL',NULL,NULL,0,NULL),('5','DT','L2L',NULL,0.7619,0,NULL),('5','GS','ALL',NULL,NULL,0,NULL),('5','LT','L2L','Chapter 5',0.8,1,NULL),('5','PT','ALL',NULL,NULL,0,NULL),('5','SA','ALL',NULL,NULL,0,NULL),('6','AC','ALL',NULL,NULL,0,NULL),('6','AE','ALL','Module 5',0.8,1,NULL),('6','CD','ALL',NULL,NULL,0,NULL),('6','CO','ALL',NULL,NULL,0,NULL),('6','DT','L2L',NULL,0.8,0,NULL),('6','GS','ALL',NULL,NULL,0,NULL),('6','LT','L2L','Chapter 6',0.8,1,NULL),('6','SA','ALL',NULL,NULL,0,NULL),('7','AC','ALL',NULL,NULL,0,NULL),('7','AE','ALL','Module 6',0.8,1,NULL),('7','CD','ALL',NULL,NULL,0,NULL),('7','CO','ALL',NULL,NULL,0,NULL),('7','DT','ALL',NULL,0.8,0,NULL),('7','GS','ALL',NULL,NULL,0,NULL),('7','LT','L2L','Chapter 7',0.8,1,NULL),('7','PT','ALL',NULL,NULL,0,NULL),('7','SA','ALL',NULL,NULL,0,NULL),('8','AC','ALL',NULL,NULL,0,NULL),('8','CD','ALL',NULL,NULL,0,NULL),('8','CO','ALL',NULL,NULL,0,NULL),('8','DT','ALL',NULL,0.7,0,NULL),('8','GS','ALL',NULL,NULL,0,NULL),('8','PT','ALL',NULL,NULL,0,NULL),('8','SA','ALL',NULL,NULL,0,NULL),('8','SE','ALL','Speech and Essay',NULL,0,NULL),('9','AC','ALL',NULL,NULL,0,NULL),('9','AE','ALL','Chapters 1,7,8',0.8,1,NULL),('9','CD','ALL',NULL,NULL,0,NULL),('9','CO','ALL',NULL,NULL,0,NULL),('9','GS','ALL',NULL,NULL,0,NULL),('9','LT','L2L','Chapter 9',0.8,1,NULL),('9','ME','ALL',NULL,NULL,0,NULL),('9','PT','ALL',NULL,NULL,0,NULL),('9','SA','ALL',NULL,NULL,0,NULL),('9','SD','ALL','Flight Commander',NULL,0,NULL),('BMI','AC','ALL',NULL,NULL,0,NULL),('BMI','AE','ALL','Review of all modules',0.8,0,NULL),('BMI','CD','ALL',NULL,NULL,0,NULL),('BMI','CO','ALL',NULL,NULL,0,NULL),('BMI','DT','L2L',NULL,NULL,0,NULL),('BMI','EC','ALL',NULL,NULL,0,NULL),('BMI','GS','ALL',NULL,NULL,0,NULL),('BMI','LT','L2L','Ch. 1-7 Cumulitive Review',0.8,0,NULL),('BMI','PT','ALL',NULL,NULL,0,NULL),('BMI','SA','ALL',NULL,NULL,0,NULL),('EAK','AC','ALL',NULL,NULL,0,NULL),('EAK','CD','ALL',NULL,NULL,0,NULL),('EAK','CO','ALL',NULL,NULL,0,NULL),('EAK','GS','ALL',NULL,NULL,0,NULL),('EAK','LA','ALL','COS or RCLS',NULL,0,NULL),('EAK','ME','ALL',NULL,NULL,0,NULL),('EAK','PT','ALL',NULL,NULL,0,NULL),('EAK','SA','ALL',NULL,NULL,0,NULL),('EAK','SS','ALL','LG, AE, DO, C/CC',NULL,0,NULL),('EAR','AC','ALL',NULL,NULL,0,NULL),('EAR','AE','ALL','Modules 1-6',0.8,1,NULL),('EAR','CD','ALL',NULL,NULL,0,NULL),('EAR','CO','ALL',NULL,NULL,0,NULL),('EAR','GS','ALL',NULL,NULL,0,NULL),('EAR','LT','L2L','Chapters 9-11',0.8,1,NULL),('EAR','ME','ALL',NULL,NULL,0,NULL),('EAR','PT','ALL',NULL,NULL,0,NULL),('EAR','SA','ALL',NULL,NULL,0,NULL),('EAR','SS','ALL','Flight CC, Admin, or PAO',NULL,0,NULL),('SPA','AE','ALL','Chapters 1-27',0.8,0,NULL),('SPA','CD','ALL','Write an Essay',NULL,0,NULL),('SPA','LT','L2L','Chatpers 1-16',0.8,0,NULL),('SPA','PT','ALL',NULL,NULL,0,NULL),('WB','AC','ALL',NULL,NULL,0,NULL),('WB','CD','ALL',NULL,NULL,0,NULL),('WB','CO','ALL',NULL,NULL,0,NULL),('WB','DT','L2L',NULL,0.7333,0,NULL),('WB','GS','ALL',NULL,NULL,0,NULL),('WB','LT','L2L','Ch. 1-3 Cumulative Review',0.8,0,NULL),('WB','PT','ALL',NULL,NULL,0,NULL),('WB','SA','ALL',NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `PROMOTION_REQUIREMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PROMOTION_SIGN_UP`
--

DROP TABLE IF EXISTS `PROMOTION_SIGN_UP`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROMOTION_SIGN_UP` (
  `CAPID` int(11) NOT NULL,
  `ACHIEV_CODE` varchar(5) NOT NULL,
  `DATE_REQUESTED` date NOT NULL,
  `APPROVED` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CAPID`),
  KEY `APPROVED` (`APPROVED`),
  KEY `ACHIEV_CODE` (`ACHIEV_CODE`),
  CONSTRAINT `FK_PROMO_SIGN_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_PROMO_SIGN_ACHIEV` FOREIGN KEY (`ACHIEV_CODE`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PROMOTION_SIGN_UP`
--

LOCK TABLES `PROMOTION_SIGN_UP` WRITE;
/*!40000 ALTER TABLE `PROMOTION_SIGN_UP` DISABLE KEYS */;
/*!40000 ALTER TABLE `PROMOTION_SIGN_UP` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `REGION`
--

DROP TABLE IF EXISTS `REGION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `REGION` (
  `REGION_CODE` char(3) NOT NULL,
  `REGION_NAME` varchar(40) NOT NULL,
  PRIMARY KEY (`REGION_CODE`),
  UNIQUE KEY `REGION_CODE` (`REGION_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `REGION`
--

LOCK TABLES `REGION` WRITE;
/*!40000 ALTER TABLE `REGION` DISABLE KEYS */;
INSERT INTO `REGION` VALUES ('GLR','Great Lakes Region'),('MER','Midwest Region'),('NCR','North Central Region'),('NER','Northeast Region'),('PCR','Pacific Region'),('RMR','Rocky Mountain Region'),('SER','Southeast Region'),('SWR','Southwest Region');
/*!40000 ALTER TABLE `REGION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `REQUIREMENTS_PASSED`
--

DROP TABLE IF EXISTS `REQUIREMENTS_PASSED`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `REQUIREMENTS_PASSED` (
  `CAPID` int(11) NOT NULL,
  `ACHIEV_CODE` varchar(5) NOT NULL,
  `REQUIREMENT_TYPE` char(2) NOT NULL,
  `TEXT_SET` varchar(5) NOT NULL,
  `PASSED_DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ON_ESERVICES` tinyint(1) NOT NULL DEFAULT '0',
  `PERCENTAGE` float DEFAULT NULL,
  `WAIVER` tinyint(1) NOT NULL DEFAULT '0',
  `TESTER` int(11) NOT NULL,
  PRIMARY KEY (`CAPID`,`ACHIEV_CODE`,`REQUIREMENT_TYPE`),
  KEY `FK_REQUIRE_PASSED_TESTER` (`TESTER`),
  KEY `CAPID` (`CAPID`),
  KEY `ACHIEV_CODE` (`ACHIEV_CODE`),
  KEY `REQUIREMENT_TYPE` (`REQUIREMENT_TYPE`),
  CONSTRAINT `FK_REQUIRE_PASSED_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_REQUIRE_PASSED_ACHIEV` FOREIGN KEY (`ACHIEV_CODE`) REFERENCES `ACHIEVEMENT` (`ACHIEV_CODE`),
  CONSTRAINT `FK_REQUIRE_PASSED_TYPE` FOREIGN KEY (`REQUIREMENT_TYPE`) REFERENCES `REQUIREMENT_TYPE` (`TYPE_CODE`),
  CONSTRAINT `FK_REQUIRE_PASSED_TESTER` FOREIGN KEY (`TESTER`) REFERENCES `MEMBER` (`CAPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `REQUIREMENTS_PASSED`
--

LOCK TABLES `REQUIREMENTS_PASSED` WRITE;
/*!40000 ALTER TABLE `REQUIREMENTS_PASSED` DISABLE KEYS */;
/*!40000 ALTER TABLE `REQUIREMENTS_PASSED` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `REQUIREMENT_TYPE`
--

DROP TABLE IF EXISTS `REQUIREMENT_TYPE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `REQUIREMENT_TYPE` (
  `TYPE_CODE` char(2) NOT NULL,
  `TYPE_NAME` varchar(50) NOT NULL,
  `MEMBER_TYPE` char(1) DEFAULT NULL,
  `IS_SUBEVENT` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`TYPE_CODE`),
  UNIQUE KEY `TYPE_CODE` (`TYPE_CODE`),
  KEY `FK_REQUIREMENT_TYPE_MEMBER` (`MEMBER_TYPE`),
  CONSTRAINT `FK_REQUIREMENT_TYPE_MEMBER` FOREIGN KEY (`MEMBER_TYPE`) REFERENCES `MEMBERSHIP_TYPES` (`MEMBER_TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `REQUIREMENT_TYPE`
--

LOCK TABLES `REQUIREMENT_TYPE` WRITE;
/*!40000 ALTER TABLE `REQUIREMENT_TYPE` DISABLE KEYS */;
INSERT INTO `REQUIREMENT_TYPE` VALUES ('AC','Squadron Activity','C',0),('AE','Aerospace Test','C',0),('CD','Character Development','C',0),('CO','Cadet Oath','C',0),('DT','Drill Test','C',0),('EC','Basic Encampment','C',0),('GS','Grooming Standards','C',0),('LA','Leadership Academy','C',0),('LT','Leadership Test','C',0),('ME','Mentor a cadet','C',0),('PB','Promotion Board','C',0),('PT','Cadet Physical Fitness Test','C',0),('SA','Safety',NULL,0),('SD','Staff Duty Analysis','C',0),('SE','Speech and Essay','C',0),('SS','Staff Service','C',0);
/*!40000 ALTER TABLE `REQUIREMENT_TYPE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RIBBON`
--

DROP TABLE IF EXISTS `RIBBON`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RIBBON` (
  `RIBBON_CODE` varchar(5) NOT NULL,
  `RIBBON_NAME` varchar(30) NOT NULL,
  `MEMBER_TYPE` char(1) DEFAULT NULL,
  PRIMARY KEY (`RIBBON_CODE`),
  UNIQUE KEY `RIBBON_CODE` (`RIBBON_CODE`),
  KEY `FK_RIBBON_MEMBER` (`MEMBER_TYPE`),
  CONSTRAINT `FK_RIBBON_MEMBER` FOREIGN KEY (`MEMBER_TYPE`) REFERENCES `MEMBERSHIP_TYPES` (`MEMBER_TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RIBBON`
--

LOCK TABLES `RIBBON` WRITE;
/*!40000 ALTER TABLE `RIBBON` DISABLE KEYS */;
/*!40000 ALTER TABLE `RIBBON` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RIBBON_REQUEST`
--

DROP TABLE IF EXISTS `RIBBON_REQUEST`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RIBBON_REQUEST` (
  `CAPID` int(11) NOT NULL,
  `RIBBON` varchar(5) NOT NULL,
  `DATE_REQUESTED` date NOT NULL,
  `APPROVED` tinyint(1) NOT NULL DEFAULT '0',
  `APROVED_BY` int(11) DEFAULT NULL,
  PRIMARY KEY (`CAPID`,`RIBBON`),
  KEY `FK_RIBBON_REQUEST_RIBBON` (`RIBBON`),
  KEY `FK_RIBBON_REQUEST_APPROVE_MEMBER` (`APROVED_BY`),
  CONSTRAINT `FK_RIBBON_REQUEST_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_RIBBON_REQUEST_RIBBON` FOREIGN KEY (`RIBBON`) REFERENCES `RIBBON` (`RIBBON_CODE`),
  CONSTRAINT `FK_RIBBON_REQUEST_APPROVE_MEMBER` FOREIGN KEY (`APROVED_BY`) REFERENCES `MEMBER` (`CAPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RIBBON_REQUEST`
--

LOCK TABLES `RIBBON_REQUEST` WRITE;
/*!40000 ALTER TABLE `RIBBON_REQUEST` DISABLE KEYS */;
/*!40000 ALTER TABLE `RIBBON_REQUEST` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SPECIAL_PERMISSION`
--

DROP TABLE IF EXISTS `SPECIAL_PERMISSION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SPECIAL_PERMISSION` (
  `CAPID` int(11) NOT NULL,
  `TASK_CODE` char(3) NOT NULL,
  PRIMARY KEY (`CAPID`,`TASK_CODE`),
  KEY `FK_SPECIAL_PERMIS_TASK` (`TASK_CODE`),
  CONSTRAINT `FK_SPECIAL_PERMIS_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_SPECIAL_PERMIS_TASK` FOREIGN KEY (`TASK_CODE`) REFERENCES `TASKS` (`TASK_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SPECIAL_PERMISSION`
--

LOCK TABLES `SPECIAL_PERMISSION` WRITE;
/*!40000 ALTER TABLE `SPECIAL_PERMISSION` DISABLE KEYS */;
/*!40000 ALTER TABLE `SPECIAL_PERMISSION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `STAFF_PERMISSIONS`
--

DROP TABLE IF EXISTS `STAFF_PERMISSIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `STAFF_PERMISSIONS` (
  `STAFF_CODE` char(3) NOT NULL,
  `TASK_CODE` char(3) NOT NULL,
  PRIMARY KEY (`STAFF_CODE`,`TASK_CODE`),
  KEY `FK_STAFF_PERM_TASK` (`TASK_CODE`),
  CONSTRAINT `FK_STAFF_PERM_STAFF_POS` FOREIGN KEY (`STAFF_CODE`) REFERENCES `STAFF_POSITIONS` (`STAFF_CODE`),
  CONSTRAINT `FK_STAFF_PERM_TASK` FOREIGN KEY (`TASK_CODE`) REFERENCES `TASKS` (`TASK_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `STAFF_PERMISSIONS`
--

LOCK TABLES `STAFF_PERMISSIONS` WRITE;
/*!40000 ALTER TABLE `STAFF_PERMISSIONS` DISABLE KEYS */;
INSERT INTO `STAFF_PERMISSIONS` VALUES ('AL','PAS'),('AL','MEA'),('AL','MSE'),('AL','EMR'),('CC','ADL'),('DCC','ADL'),('DCS','ADL'),('CC','LLO'),('DCC','LLO'),('DCS','LLO'),('CC','CLO'),('DCC','CLO'),('DCS','CLO'),('CC','DME'),('DCC','DME'),('DCS','DME'),('CC','EST'),('DCC','EST'),('DCS','EST'),('CC','ESR'),('DCC','ESR'),('DCS','ESR'),('CC','NME'),('CC','PRR'),('CC','PSE'),('CC','SPM'),('CC','PRB'),('CC','PTT'),('DCC','CPR'),('DCC','CPS'),('DCC','PTT'),('DCC','PRB'),('DCS','SPR'),('DCS','SPS'),('ITO','ADL'),('CTO','ADL'),('ITO','CLO'),('CTO','CLO'),('ITO','LLO'),('CTO','LLO'),('ITO','NME'),('CTO','NME'),('ITO','SPM'),('CTO','SPM'),('TO','EST'),('TO','ESR'),('TO','PTT'),('TO','TSE'),('ADM','PRR'),('ADM','TSE'),('ADM','EST'),('ADM','ESR'),('ADM','PRL'),('ADM','PTT'),('CCC','CPR'),('CDC','CPR'),('CXO','CPR'),('CCC','PPC'),('CDC','PPC'),('CXO','PPC'),('CCC','TSE'),('CDC','TSE'),('CXO','TSE'),('CCC','PTT'),('CDC','PTT'),('CXO','PTT'),('CCC','CAO'),('CDC','CAO'),('CXO','CAO'),('FLC','CAO'),('FLS','CAO'),('FLC','DRT'),('FLS','DRT'),('PTT','PTT'),('DRT','DRT'),('AL','EVI'),('AL','EVR'),('AL','HOM'),('AL','NEV'),('CC','TME'),('DCC','TME'),('DCS','TME'),('CC','TSE'),('DCC','TSE'),('DCS','TSE');
/*!40000 ALTER TABLE `STAFF_PERMISSIONS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `STAFF_POSITIONS`
--

DROP TABLE IF EXISTS `STAFF_POSITIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `STAFF_POSITIONS` (
  `STAFF_CODE` varchar(3) NOT NULL,
  `STAFF_NAME` varchar(50) NOT NULL,
  `MEMBER_TYPE` char(1) NOT NULL,
  PRIMARY KEY (`STAFF_CODE`),
  UNIQUE KEY `STAFF_CODE` (`STAFF_CODE`),
  KEY `FK_STAFF_MEMBER_TYPE` (`MEMBER_TYPE`),
  CONSTRAINT `FK_STAFF_MEMBER_TYPE` FOREIGN KEY (`MEMBER_TYPE`) REFERENCES `MEMBERSHIP_TYPES` (`MEMBER_TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `STAFF_POSITIONS`
--

LOCK TABLES `STAFF_POSITIONS` WRITE;
/*!40000 ALTER TABLE `STAFF_POSITIONS` DISABLE KEYS */;
INSERT INTO `STAFF_POSITIONS` VALUES ('AL','All staff positions','A'),('CC','Squadron Commander','S'),('CCC','Cadet Commander','C'),('CDC','Cadet Deputy Commander','C'),('CTO','Cadet Information Technology Officer','C'),('CXO','Cadet Executive Officer','C'),('DCC','Deputy Commander of Cadets','S'),('FLC','Flight Commander','C'),('FLS','Flight Sergeant','C'),('ITO','Information Technology Officer','S'),('TO','Testing Officer','S'),('ADM','Administrative Officer','S'),('PTT','Cadet Physical Fitness Tester','C'),('DRT','Drill Tester','C'),('DCS','Deputy Commander of Seniors','S');
/*!40000 ALTER TABLE `STAFF_POSITIONS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `STAFF_POSITIONS_HELD`
--

DROP TABLE IF EXISTS `STAFF_POSITIONS_HELD`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `STAFF_POSITIONS_HELD` (
  `STAFF_POSITION` varchar(3) NOT NULL,
  `CAPID` int(11) NOT NULL,
  PRIMARY KEY (`STAFF_POSITION`,`CAPID`),
  KEY `FK_STAFF_HOLD_CAPID` (`CAPID`),
  CONSTRAINT `FK_STAFF_HOLD_CHAIN` FOREIGN KEY (`STAFF_POSITION`) REFERENCES `STAFF_POSITIONS` (`STAFF_CODE`),
  CONSTRAINT `FK_STAFF_HOLD_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `STAFF_POSITIONS_HELD`
--

LOCK TABLES `STAFF_POSITIONS_HELD` WRITE;
/*!40000 ALTER TABLE `STAFF_POSITIONS_HELD` DISABLE KEYS */;
/*!40000 ALTER TABLE `STAFF_POSITIONS_HELD` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SUBEVENT`
--

DROP TABLE IF EXISTS `SUBEVENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SUBEVENT` (
  `PARENT_EVENT_CODE` varchar(32) NOT NULL,
  `SUBEVENT_CODE` char(3) NOT NULL,
  `START_TIME` time DEFAULT NULL,
  `END_TIME` time DEFAULT NULL,
  `DESCRIPTION` varchar(125) DEFAULT NULL,
  PRIMARY KEY (`PARENT_EVENT_CODE`,`SUBEVENT_CODE`),
  KEY `PARENT_EVENT_CODE` (`PARENT_EVENT_CODE`),
  KEY `SUBEVENT_CODE` (`SUBEVENT_CODE`),
  CONSTRAINT `FK_SUBEVENT_PARENT_EVENT` FOREIGN KEY (`PARENT_EVENT_CODE`) REFERENCES `EVENT` (`EVENT_CODE`),
  CONSTRAINT `FK_SUBEVENT_TYPE` FOREIGN KEY (`SUBEVENT_CODE`) REFERENCES `SUBEVENT_TYPE` (`SUBEVENT_TYPE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SUBEVENT`
--

LOCK TABLES `SUBEVENT` WRITE;
/*!40000 ALTER TABLE `SUBEVENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `SUBEVENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SUBEVENT_TYPE`
--

DROP TABLE IF EXISTS `SUBEVENT_TYPE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SUBEVENT_TYPE` (
  `SUBEVENT_TYPE` char(3) NOT NULL,
  `SUBEVENT_NAME` varchar(40) NOT NULL,
  PRIMARY KEY (`SUBEVENT_TYPE`),
  UNIQUE KEY `SUBEVENT_TYPE` (`SUBEVENT_TYPE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SUBEVENT_TYPE`
--

LOCK TABLES `SUBEVENT_TYPE` WRITE;
/*!40000 ALTER TABLE `SUBEVENT_TYPE` DISABLE KEYS */;
INSERT INTO `SUBEVENT_TYPE` VALUES ('AE','Aerospace Education'),('CD','Character Development'),('ES','Emergency Services'),('SAF','Safety'),('HG','Honor Guard'),('CG','Color Guard'),('CP','Cyber Patriot'),('DC','Drill and Cermenoies'),('PT','Physical Training');
/*!40000 ALTER TABLE `SUBEVENT_TYPE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TASKS`
--

DROP TABLE IF EXISTS `TASKS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TASKS` (
  `TASK_CODE` char(3) NOT NULL,
  `TASK_NAME` varchar(32) NOT NULL,
  `URL` varchar(128) NOT NULL,
  `NEW_TAB` tinyint(1) NOT NULL DEFAULT '0',
  `UNGRANTABLE` tinyint(1) NOT NULL DEFAULT '0',
  `TYPE_CODE` char(2) NOT NULL,
  `GET_FIELD` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`TASK_CODE`),
  UNIQUE KEY `TASK_CODE` (`TASK_CODE`),
  KEY `FK_TAST_TYPE` (`TYPE_CODE`),
  KEY `URL` (`URL`),
  CONSTRAINT `FK_TAST_TYPE` FOREIGN KEY (`TYPE_CODE`) REFERENCES `TASK_TYPE` (`TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TASKS`
--

LOCK TABLES `TASKS` WRITE;
/*!40000 ALTER TABLE `TASKS` DISABLE KEYS */;
INSERT INTO `TASKS` VALUES ('ADL','View site Logs','adminis/auditLog.php',0,1,'AD',NULL),('CAO','Cadet Oath and Grooming','testing/cadetOath.php',0,0,'TP',NULL),('CLO','Clear System Logs','adminis/clearLog.php',0,1,'AD',NULL),('CPR','Cadet Promotion Record','testing/promoRecord.php',0,1,'TP','C'),('CPS','Cadet Promotion Sign-Up','testing/promotionSign.php',0,1,'TP','C'),('DDE','Find Discipline Event','discipline/details.php',0,0,'DA',NULL),('DME','Delete Member record','adminis/deleteRecord.php',0,1,'AD',NULL),('DRT','Drill Testing Sign-Up','testing/testSignUp.php',0,0,'TP','DT'),('EMR','Emergency Contact Information','reporting/emergency_contact.php',1,0,'RE',NULL),('ESR','Eservice Report','reporting/eservReport.php',1,0,'RE',NULL),('EST','Enter Online Testing','testing/onlineTesting.php',0,0,'TP',NULL),('EVI','Insert attendance for an Event','attendance/add.php',0,0,'EV',NULL),('EVR','Find Event Report','attendance/event.php',0,0,'EV',NULL),('HOM','Home','home.php',0,0,'ME',NULL),('LLO','Login Logs and Locks','adminis/loginLog.php',0,1,'AD',NULL),('MEA','Approve Added Members','member/approve.php',0,0,'ME',NULL),('MSE','Member Search','member/search.php',0,0,'ME',NULL),('NEV','Create a new Event','attendance/new.php',0,0,'EV',NULL),('NME','Create Staff Member','adminis/newMember.php',0,1,'AD',NULL),('PAS','Change your Password','adminis/pswdChange.php',0,0,'AD',NULL),('PPC','Cadet Promotion Sign-up-locked','testing/promotionSign.php',0,0,'TP','CL'),('PRB','Manage Promotion Boards','testing/promoBoard.php',0,0,'TP',NULL),('PRR','Edit Promotion Record','testing/promoRecord.php',0,1,'TP',NULL),('PSE','Promotion Sign-up','testing/promotionSign.php',0,1,'TP',NULL),('PTT','Manage CPFT Testing','testing/PTtest.php',0,0,'TP',NULL),('SPM','Change Staff Permissions','adminis/staffPerm.php',0,1,'AD',NULL),('SPR','Senior Member Promotion Record','testing/promoRecord.php',0,1,'TP','S'),('SPS','Senior Member Promotions','testing/promotionSign.php',0,1,'TP','S'),('TME','Terminate Members','member/termMembership.php',0,1,'ME',NULL),('TSE','View Testing Sign-up','testing/testSignUp.php',0,0,'TP',NULL),('PRL','Promotion sign-up locked','testing/promotionSign.php',0,0,'TP','AL');
/*!40000 ALTER TABLE `TASKS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TASK_TYPE`
--

DROP TABLE IF EXISTS `TASK_TYPE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TASK_TYPE` (
  `TYPE_CODE` char(2) NOT NULL,
  `TYPE_NAME` varchar(32) NOT NULL,
  PRIMARY KEY (`TYPE_CODE`),
  UNIQUE KEY `TYPE_CODE` (`TYPE_CODE`),
  UNIQUE KEY `TYPE_NAME` (`TYPE_NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TASK_TYPE`
--

LOCK TABLES `TASK_TYPE` WRITE;
/*!40000 ALTER TABLE `TASK_TYPE` DISABLE KEYS */;
INSERT INTO `TASK_TYPE` VALUES ('AD','Administration'),('DA','Discipline action'),('EV','Event Management'),('ME','Membership Action'),('RE','Squadron Reports'),('TP','Testing and Promotions');
/*!40000 ALTER TABLE `TASK_TYPE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TESTING_SIGN_UP`
--

DROP TABLE IF EXISTS `TESTING_SIGN_UP`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TESTING_SIGN_UP` (
  `CAPID` int(11) NOT NULL,
  `REQUIRE_TYPE` char(2) NOT NULL,
  `REQUESTED_DATE` date NOT NULL,
  PRIMARY KEY (`CAPID`,`REQUIRE_TYPE`),
  KEY `FK_TEST_SIGN_UP_TYPE` (`REQUIRE_TYPE`),
  CONSTRAINT `FK_TEST_SIGN_UP_CAPID` FOREIGN KEY (`CAPID`) REFERENCES `MEMBER` (`CAPID`),
  CONSTRAINT `FK_TEST_SIGN_UP_TYPE` FOREIGN KEY (`REQUIRE_TYPE`) REFERENCES `REQUIREMENT_TYPE` (`TYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TESTING_SIGN_UP`
--

LOCK TABLES `TESTING_SIGN_UP` WRITE;
/*!40000 ALTER TABLE `TESTING_SIGN_UP` DISABLE KEYS */;
/*!40000 ALTER TABLE `TESTING_SIGN_UP` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TEXT_SETS`
--

DROP TABLE IF EXISTS `TEXT_SETS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TEXT_SETS` (
  `TEXT_SET_CODE` varchar(5) NOT NULL,
  `TEXT_SET_NAME` varchar(45) NOT NULL,
  PRIMARY KEY (`TEXT_SET_CODE`),
  UNIQUE KEY `TEXT_SET_CODE` (`TEXT_SET_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TEXT_SETS`
--

LOCK TABLES `TEXT_SETS` WRITE;
/*!40000 ALTER TABLE `TEXT_SETS` DISABLE KEYS */;
INSERT INTO `TEXT_SETS` VALUES ('ALL','All text sets'),('L2L','Learn to Lead'),('PD','Professional Development');
/*!40000 ALTER TABLE `TEXT_SETS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `WING`
--

DROP TABLE IF EXISTS `WING`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `WING` (
  `WING` char(2) NOT NULL,
  `WING_NAME` varchar(40) NOT NULL,
  PRIMARY KEY (`WING`),
  UNIQUE KEY `WING` (`WING`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `WING`
--

LOCK TABLES `WING` WRITE;
/*!40000 ALTER TABLE `WING` DISABLE KEYS */;
INSERT INTO `WING` VALUES ('AK','Alaska'),('AL','Alabama'),('AR','Arkansas'),('AZ','Arizona'),('CA','California'),('CO','Colorado'),('CT','Connecticut'),('DC','District of Columbia'),('DE','Delaware'),('FL','Florida'),('GA','Georgia'),('HI','Hawaii'),('IA','Iowa'),('ID','Idaho'),('IL','Illinois'),('IN','Indiana'),('KS','Kansas'),('KY','Kentucky'),('LA','Louisiana'),('MA','Massachusetts'),('MD','Maryland'),('ME','Maine'),('MI','Michigan'),('MN','Minnesota'),('MO','Missouri'),('MS','Mississippi'),('MT','Montana'),('NC','North Carolina'),('ND','North Dakota'),('NE','Nebraska'),('NH','New Hampshire'),('NJ','New Jersey'),('NM','New Mexico'),('NV','Nevada'),('NY','New York'),('OH','Ohio'),('OK','Oklahoma'),('OR','Oregon'),('PA','Pennsylvania'),('PC','Puerto Rico'),('RI','Rhode Island'),('SC','South Carolina'),('SD','South Dakota'),('TN','Tennessee'),('TX','Texas'),('UT','Utah'),('VA','Virginia'),('VT','Vermont'),('WA','Washington'),('WI','Wisconsin'),('WV','West Virginia'),('WY','Wyoming');
/*!40000 ALTER TABLE `WING` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-10-11 17:23:40
