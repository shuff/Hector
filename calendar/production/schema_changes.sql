
--
-- Dumping data for table `calendar`
--

LOCK TABLES `calendar` WRITE;
/*!40000 ALTER TABLE `calendar` DISABLE KEYS */;
INSERT INTO `calendar` VALUES (66,'this is another test','1346302800'),(67,'Woohoo! Monday again!','1344229200'),(74,'This is going to be a very very long entry into the calendar to see what it is gioing to look like','1344229200'),(75,'Meeting at 10AM','1345006800'),(76,'Need to add LPL statements','1345525200'),(77,'Brands and agencies such as Facebook, Virgin, Amazon, Target and Ogilvy as well as thousands of small businesses use the Wildfire platform to engage with audiences on major social networks, including Facebook, LinkedIn, YouTube and Twitter. The only socia','1345179600'),(80,'fff','1344920400');
/*!40000 ALTER TABLE `calendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drivers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `number` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `birthdate` datetime NOT NULL,
  `hiredate` datetime NOT NULL,
  `type` int(3) NOT NULL,
  `active` int(3) NOT NULL DEFAULT '1',
  `license_id` int(10) DEFAULT NULL,
  `login_id` int(10) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_id` (`login_id`),
  UNIQUE KEY `license_id` (`license_id`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drivers`
--

LOCK TABLES `drivers` WRITE;
/*!40000 ALTER TABLE `drivers` DISABLE KEYS */;
INSERT INTO `drivers` VALUES (1,'Robert Paulson','200','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,1,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(2,'John Smith','341','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(3,'Bill Additington','200','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(4,'John Smith','341','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(5,'Roy Jones','232','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(6,'Chris Matthews','23','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(7,'Clark Edwards','432','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(8,'Susan Jones','322','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(9,'Jon Jones','3488','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(10,'Jim Jones','534','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(11,'Sam Elliot','45','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(12,'Jesus Jones','3433','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(13,'James Taylor','4343','0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `drivers` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `licenses` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `region` varchar(16) COLLATE utf8_bin NOT NULL,
  `issued` datetime NOT NULL,
  `expiry` datetime NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `driver_id` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licenses`
--

LOCK TABLES `licenses` WRITE;
/*!40000 ALTER TABLE `licenses` DISABLE KEYS */;
INSERT INTO `licenses` VALUES (1,'Texas','2012-02-14 00:00:00','2012-08-09 00:00:00','2012-08-01 00:00:00','2012-08-04 21:14:43','1','Class A'),(2,'Texas','2012-02-14 00:00:00','2012-08-08 00:00:00','2012-02-14 00:00:00','2012-02-14 00:00:00','1','Twic Card'),(3,'Texas','2012-02-14 00:00:00','2012-08-10 00:00:00','2012-02-14 00:00:00','2012-08-04 21:21:40','1','Physical Exam');
/*!40000 ALTER TABLE `licenses` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `trucks`
--

DROP TABLE IF EXISTS `trucks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trucks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vin` varchar(17) COLLATE utf8_bin NOT NULL,
  `unitnumber` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `carrier_id` int(10) unsigned NOT NULL,
  `make` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `model` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `engine` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `platenumber` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  `plateregion` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  `plateexpiry` datetime DEFAULT NULL,
  `weight` int(10) DEFAULT NULL,
  `dotexpiry` datetime DEFAULT NULL,
  `status` int(3) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trucks`
--

LOCK TABLES `trucks` WRITE;
/*!40000 ALTER TABLE `trucks` DISABLE KEYS */;
INSERT INTO `trucks` VALUES (31,'1M8GDM9A0KP042788',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2012-08-10 00:00:00',NULL,'2012-08-09 00:00:00',0,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(32,'1M8GDM9A0KP004123',NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,'2012-08-11 00:00:00',NULL,'2012-08-07 00:00:00',0,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(33,'1M8GDM9A0Kaa42788',NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,'2012-08-06 00:00:00',NULL,'2012-08-09 00:00:00',0,'0000-00-00 00:00:00','0000-00-00 00:00:00'),(34,'1M8GDM9A0KP0aa330',NULL,13,NULL,NULL,NULL,NULL,NULL,NULL,'2012-08-05 00:00:00',NULL,'2012-08-09 00:00:00',0,'0000-00-00 00:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `trucks` ENABLE KEYS */;
UNLOCK TABLES;
