-- Table structure for table `club`
DROP TABLE IF EXISTS `club`;
CREATE TABLE `club` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `club`
INSERT INTO `club` VALUES 
(1, 'Cavalier Rouge Clamart'),
(2, 'Sevres-Ville d\'Avray');

-- Table structure for table `competitions`
DROP TABLE IF EXISTS `competitions`;
CREATE TABLE `competitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `competition_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `competitions`
INSERT INTO `competitions` VALUES 
(2, 'championat nationale', 'En attente', '2025-03-29 00:00:00');

-- Table structure for table `doctrine_migration_versions`
DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table `doctrine_migration_versions`
INSERT INTO `doctrine_migration_versions` VALUES 
('DoctrineMigrations\\Version20250120135839', '2025-01-20 13:58:56', 55);

-- Table structure for table `feuille_match`
DROP TABLE IF EXISTS `feuille_match`;
CREATE TABLE `feuille_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_a_id` int(11) DEFAULT NULL,
  `club_b_id` int(11) DEFAULT NULL,
  `creation` datetime NOT NULL,
  `date_match` datetime NOT NULL,
  `ronde` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `groupe` varchar(50) NOT NULL,
  `interclub` varchar(50) NOT NULL,
  `joueurs` longtext NOT NULL COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  KEY `IDX_1628A52E5F1699B8` (`club_a_id`),
  KEY `IDX_1628A52E4DA33656` (`club_b_id`),
  CONSTRAINT `FK_1628A52E4DA33656` FOREIGN KEY (`club_b_id`) REFERENCES `club` (`id`),
  CONSTRAINT `FK_1628A52E5F1699B8` FOREIGN KEY (`club_a_id`) REFERENCES `club` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `feuille_match`
INSERT INTO `feuille_match` VALUES 
(17, 1, 2, '2025-01-22 13:09:05', '2025-01-22 00:00:00', 1, 'Critérium', 'Groupe 1', 'Interclub Jeune', '[{\"joueurA\":\"5\",\"resultat\":\"Gain Blanc\",\"joueurB\":\"14\"}]'),
(19, 1, 2, '2025-01-22 16:35:39', '2025-01-22 16:35:39', 1, 'Nationale', 'Groupe 2', 'Interclub Jeune', '[{\"joueurA\":\"6\",\"resultat\":\"Gain Noir\",\"joueurB\":\"14\"}]');

-- Table structure for table `joueurs`
DROP TABLE IF EXISTS `joueurs`;
CREATE TABLE `joueurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F0FD889D61190A32` (`club_id`),
  CONSTRAINT `FK_F0FD889D61190A32` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `messenger_messages`
DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `user`
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) DEFAULT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext NOT NULL COMMENT '(DC2Type:json)',
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `pseudo` varchar(255) NOT NULL,
  `avatar` varchar(180) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `user`
INSERT INTO `user` VALUES 
(1, 1, 'jonathanloyer1@gmail.com', '[\"ROLE_ADMIN\"]', 'Loyer', 'Jonathan', 'adminjo', NULL, '2024-11-28 19:50:25', '$2y$13$gBVlFDLz2r3R9SJpA/yaxex/7N6QFPrcJ7ygNVUvfQh7wKJ4nl.9m');
