-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: data_sample
-- ------------------------------------------------------
-- Server version	5.7.17-1

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
-- Database creation
--

DROP DATABASE IF EXISTS `pivot`;

CREATE DATABASE `pivot` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `pivot`;


--
-- Table structure for table `dataSample`
--

DROP TABLE IF EXISTS `records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `records` (
  `period` varchar(7) DEFAULT NULL,
  `customerid` varchar(10) DEFAULT NULL,
  `customername` varchar(50) DEFAULT NULL,
  `salesid` varchar(10) DEFAULT NULL,
  `salesname` varchar(50) DEFAULT NULL,
  `zoneid` varchar(3) DEFAULT NULL,
  `zonename` varchar(20) DEFAULT NULL,
  `order` INT UNSIGNED DEFAULT NULL,
  `invoiced` DATETIME DEFAULT NULL,
  `line` TINYINT UNSIGNED DEFAULT NULL,
  `partnumber` varchar(10) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `quantity` INT DEFAULT NULL,
  `unitprice` DECIMAL(12,2) DEFAULT NULL,
  `unitcost` DECIMAL(12,2) DEFAULT NULL,
  `totalsale` DECIMAL(12,2) DEFAULT NULL,
  `totalcost` DECIMAL(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-04-12 12:39:29
