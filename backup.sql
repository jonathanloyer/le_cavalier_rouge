-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: le_cavalier_rouge
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `club`
--

DROP TABLE IF EXISTS `club`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `club` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `club`
--

LOCK TABLES `club` WRITE;
/*!40000 ALTER TABLE `club` DISABLE KEYS */;
INSERT INTO `club` VALUES (1,'Cavalier Rouge Clamart'),(2,'Sevres-Ville d\'Avray');
/*!40000 ALTER TABLE `club` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `competitions`
--

DROP TABLE IF EXISTS `competitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `competitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `competition_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `competitions`
--

LOCK TABLES `competitions` WRITE;
/*!40000 ALTER TABLE `competitions` DISABLE KEYS */;
INSERT INTO `competitions` VALUES (2,'championat nationale','En attente','2025-03-29 00:00:00');
/*!40000 ALTER TABLE `competitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feuille_match`
--

DROP TABLE IF EXISTS `feuille_match`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feuille_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_a_id` int(11) DEFAULT NULL,
  `club_b_id` int(11) DEFAULT NULL,
  `creation` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `date_match` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `ronde` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `groupe` varchar(50) NOT NULL,
  `interclub` varchar(50) NOT NULL,
  `joueurs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`joueurs`)),
  PRIMARY KEY (`id`),
  KEY `IDX_1628A52E5F1699B8` (`club_a_id`),
  KEY `IDX_1628A52E4DA33656` (`club_b_id`),
  CONSTRAINT `FK_1628A52E4DA33656` FOREIGN KEY (`club_b_id`) REFERENCES `club` (`id`),
  CONSTRAINT `FK_1628A52E5F1699B8` FOREIGN KEY (`club_a_id`) REFERENCES `club` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feuille_match`
--

LOCK TABLES `feuille_match` WRITE;
/*!40000 ALTER TABLE `feuille_match` DISABLE KEYS */;
INSERT INTO `feuille_match` VALUES (17,1,2,'2025-01-22 13:09:05','2025-01-22 00:00:00',1,'Crit├®rium','Groupe 1','Interclub Jeune','[{\"joueurA\":\"5\",\"resultat\":\"Gain Blanc\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"6\",\"resultat\":\"Gain Noir\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"8\",\"resultat\":\"Gain Blanc\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"9\",\"resultat\":\"Nulle\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"id\":\"5\",\"role\":\"capitaineA\",\"resultat\":null},{\"id\":\"14\",\"role\":\"capitaineB\",\"resultat\":null},{\"id\":\"10\",\"role\":\"arbitre\",\"resultat\":null}]'),(19,1,2,'2025-01-22 16:35:39','2025-01-22 16:35:39',1,'Nationale','Groupe 2','Interclub Jeune','[{\"joueurA\":\"5\",\"resultat\":\"Gain Blanc\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"6\",\"resultat\":\"Gain Noir\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"7\",\"resultat\":\"Nulle\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"8\",\"resultat\":\"Gain Blanc\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"9\",\"resultat\":\"Gain Noir\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"11\",\"resultat\":\"Nulle\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"12\",\"resultat\":\"Gain Blanc\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"13\",\"resultat\":\"Gain Noir\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"10\",\"resultat\":\"Nulle\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"joueurA\":\"1\",\"resultat\":\"Gain Blanc\",\"joueurB\":\"14\",\"id\":null,\"role\":null},{\"id\":\"6\",\"role\":\"capitaineA\",\"resultat\":null},{\"id\":\"14\",\"role\":\"capitaineB\",\"resultat\":null},{\"id\":\"10\",\"role\":\"arbitre\",\"resultat\":null}]');
/*!40000 ALTER TABLE `feuille_match` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `joueurs`
--

DROP TABLE IF EXISTS `joueurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `joueurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F0FD889D61190A32` (`club_id`),
  CONSTRAINT `FK_F0FD889D61190A32` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `joueurs`
--

LOCK TABLES `joueurs` WRITE;
/*!40000 ALTER TABLE `joueurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `joueurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_role`
--

DROP TABLE IF EXISTS `player_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `role_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F573DA59A76ED395` (`user_id`),
  CONSTRAINT `FK_F573DA59A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_role`
--

LOCK TABLES `player_role` WRITE;
/*!40000 ALTER TABLE `player_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) DEFAULT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `pseudo` varchar(255) NOT NULL,
  `ffe_id` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `avatar` varchar(180) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `password` varchar(255) NOT NULL,
  `code_ffe` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`),
  UNIQUE KEY `UNIQ_8D93D64986CC499D` (`pseudo`),
  KEY `IDX_8D93D64961190A32` (`club_id`),
  CONSTRAINT `FK_8D93D64961190A32` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,'jonathanloyer1@gmail.com','[\"ROLE_ADMIN\"]','Loyer','Jonathan','adminjo',NULL,0,'avatartype-679505f196530.png','2024-11-28 19:50:25','$2y$13$gBVlFDLz2r3R9SJpA/yaxex/7N6QFPrcJ7ygNVUvfQh7wKJ4nl.9m','Z00000'),(3,1,'jojo@jojo.fr','[\"ROLE_USER\",\"ROLE_CAPITAINE\"]','Martin','Henri','jojo',NULL,1,'avatartype-6787cac3a4cc7.png','2025-01-15 14:44:52','$2y$13$tlrWppVcGmFllLN1cqmc5OsFYLR3OMj5uLPWlGfzh6I42yHyDpf8a',NULL),(5,1,'lucas@lucas.fr','[\"ROLE_USER\"]','Socoliuc','Lucas','Lucas',NULL,1,'avatartype-6794c01fc8262.png','2025-01-16 11:11:25','$2y$13$4FkoiQyesOX3OQuEYCPQEeRVnJjwR5jJD3EWD7OQTLIKVFlCoja.a','U55429'),(6,1,'octave@octave.fr','[]','Granieri','Octave','Octave',NULL,1,'avatartype-6788ee38943b2.png','2025-01-16 11:31:27','$2y$13$VEORr2kX.7vEquQh8/YyPeMIL.OgfL3V9IGOeKc5lt6AlGYE0RFVq','X59382'),(7,1,'arthur@arthur.fr','[]','Gouraud','Arthur','arthur',NULL,1,'avatartype-6788eef2b5f5d.png','2025-01-16 11:33:40','$2y$13$8tJTF.NFULLXFTrfXdx7v.qHydPePWf6P3bEyYQyjgp87Re7tKXV2','T60758'),(8,1,'alexandre@alexandre.fr','[\"ROLE_USER\"]','Cabioch','Alexandre','alexandre',NULL,1,'avatartype-6788ef79a5e19.png','2025-01-16 11:36:55','$2y$13$X/vaCPRsc8HmFC56sLvldOl33n3esGfbsUDFtAXu98rgFVsjkpsM.','V55380'),(9,1,'alexandreh@alexandreh.fr','[]','Hebert','Alexandre','alexandreh',NULL,1,'avatartype-6788f07f86227.png','2025-01-16 11:41:02','$2y$13$4C2SICtgbNHTnB/v/SQd7eiYlmQP/wJ5O0X/cWRWyYsPS6mKP2Kqy','X53222'),(10,1,'riou.luc@cavalier-rouge.fr','[]','RIOU','LUC','lucriou',NULL,1,'avatartype-6788f0ea652dd.png','2025-01-16 11:43:09','$2y$13$.IcHTZ40aseagMvMW/c/1.8fCds.6xYF47dh328SZC1WgfKjxo4ya','K53547'),(11,1,'leonardo@leonardo.fr','[]','Liuni','Leonardo','leonardo',NULL,1,'avatartype-6788f1ad9e92e.png','2025-01-16 11:46:17','$2y$13$8Am3WNaBc3qZkNPDt36yIOcq8N69XDpH.bcgMXkqzxnjGhLhbYxhO','U58804'),(12,1,'maxime@maxime.fr','[]','Navarro','Maxime','Maxime',NULL,1,'avatartype-6788f273ca6e1.png','2025-01-16 11:49:38','$2y$13$s35OyhnvV9i8Yh8LDGmsSeLWEqZePW4wXz.fG8bLF.fDecQNf6maS','W78432'),(13,1,'olivier@olivier.fr','[]','Guebel','Olivier','olivier',NULL,1,'avatartype-6788f2d103855.png','2025-01-16 11:51:18','$2y$13$l1IWXseqguUBmq9Xhx4k3eUMSsAiByFEYHz/poCNBN/eEqL0IbnsO','V62578'),(14,2,'axel@axel.fr','[]','Hus','Axel','axel',NULL,1,'avatartype-67893375a90e3.png','2025-01-16 16:26:29','$2y$13$4pWLhPJgwqrlquRxOvc2k.qMRUqOpxAU19mSeL3gDGLQO3qThVSGG','S62171'),(17,1,'seve@seve.fr','[]','seve','seves','reine',NULL,0,'avatartype-6794c59107a1d.png','2025-01-25 11:05:26','$2y$13$6DXU/FQhXezMu.keomuGae1AXhczQ5hy.Z7RWqRON9PE15L1Mg4Z6','Z123456'),(23,1,'att@att.fr','[\"ROLE_USER\"]','nene','NENE','nene',NULL,1,NULL,'2025-01-26 18:16:07','$2y$13$zhBY5z/xrjYSTquYyry/veBuXZtmS4ThsOgtRebR6s5NXZwnAH/sS','F45678');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-01-27 20:47:06
