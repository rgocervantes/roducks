-- --------------------------------------------------------
-- Roducks SQL --------------------------------------------
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Setup` (
  `id_setup` bigint(8) AUTO_INCREMENT NOT NULL,
  `file` varchar(255) NOT NULL,
  `type` enum('php','sql') DEFAULT 'php' NOT NULL,
  `executed_at` datetime NOT NULL,
  PRIMARY KEY (`id_setup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `EAV` (
  `id_index` bigint(8) AUTO_INCREMENT NOT NULL,
  `id_rel` bigint(8) NOT NULL,
  `entity` varchar(255) NOT NULL,
  `field` varchar(255) NOT NULL,
  `text` varchar(255) NOT NULL,  
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id_index`),
  INDEX `idx_rel` (`id_rel`),
  INDEX `idx_text` (`text`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Roles` (
  `id_role` bigint(8) AUTO_INCREMENT NOT NULL,
  `type` tinyint(1) DEFAULT '1' COMMENT '1 = Admin Users, 2 = Subscribers, 3 = Clients',
  `name` varchar(255) NOT NULL,
  `config` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_by` bigint(8) NOT NULL,
  `updated_by` bigint(8) NOT NULL,    
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id_role`)   
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

INSERT INTO `Roles` (`id_role`, `type`, `name`, `config`, `active`, `created_by`,`updated_by`, `created_at`,`updated_at`) VALUES
(1, 1, 'Super Admin', 'super-admin.lock', 1, 1, 1, '2017-09-02 12:00:00', '2017-09-02 12:00:00'),
(2, 1, 'Super Admin', 'super-admin.json', 1, 1, 1, '2017-09-02 12:00:00', '2017-09-02 12:00:00'),
(3, 1, 'Admin Golden', 'admin-golden.json', 1, 1, 1, '2017-09-02 12:00:00', '2017-09-02 12:00:00'),
(4, 1, 'Admin Silver', 'admin-silver.json', 1, 1, 1, '2017-09-02 12:00:00', '2017-09-02 12:00:00'),
(5, 1, 'Admin Bronze', 'admin-bronze.json', 1, 1, 1, '2017-09-02 12:00:00', '2017-09-02 12:00:00'),
(6, 1, 'Admin', 'admin.json', 1, 1, 1, '2017-09-02 12:00:00', '2017-09-02 12:00:00'),
(7, 2, 'Subscribers', 'subscribers.json', 1, 1, 1, '2017-09-02 12:00:00', '2017-09-02 12:00:00'),
(8, 3, 'Clients', 'clients.json', 1, 1, 1, '2017-09-02 12:00:00', '2017-09-02 12:00:00');

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Users` (
  `id_user` bigint(8) AUTO_INCREMENT NOT NULL,
  `id_user_parent` bigint(8) DEFAULT '0',
  `id_user_tree` blob,
  `id_role` bigint(8) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `picture` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,  
  `active` tinyint(1) DEFAULT '1',
  `trash` tinyint(1) DEFAULT '0' COMMENT 'Logic delete',    
  `token` varchar(255) NOT NULL,
  `loggedin` tinyint(1) DEFAULT '0',
  `location` varchar(255) NOT NULL,
  `expires` tinyint(1) DEFAULT '0',
  `expiration_date` date NULL, 
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,  
  `deleted_at` datetime NULL, 
  PRIMARY KEY (`id_user`),
  UNIQUE INDEX `idx_email` (`email`),
  CONSTRAINT `id_role` FOREIGN KEY (`id_role`) REFERENCES `Roles` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Urls` (
  `id_url` bigint(8) AUTO_INCREMENT NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_url`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `UrlsLang` (
  `id_url_lang` bigint(8) AUTO_INCREMENT NOT NULL,
  `id_url` bigint(8) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `url` blob,
  `dispatch` varchar(255) NOT NULL,
  `title` blob NOT NULL,
  `layout` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `pview` varchar(255) NOT NULL,
  `updated_at` datetime NOT NULL,    
  PRIMARY KEY (`id_url_lang`),
  CONSTRAINT `id_url` FOREIGN KEY (`id_url`) REFERENCES `Urls` (`id_url`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------