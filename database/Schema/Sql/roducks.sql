-- --------------------------------------------------------
-- Roducks SQL --------------------------------------------
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Setup` (
  `id_setup` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `file` varchar(255) NOT NULL,
  `type` enum('php','sql') DEFAULT 'php' NOT NULL,
  `executed_at` datetime NOT NULL,
  PRIMARY KEY (`id_setup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `EAV` (
  `id_index` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_rel` bigint(8) UNSIGNED NOT NULL,
  `entity` varchar(255) NOT NULL,
  `field` varchar(255) NOT NULL,
  `text` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_index`),
  INDEX `idx_rel` (`id_rel`),
  INDEX `idx_text` (`text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Roles` (
  `id_role` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `type` tinyint(1) DEFAULT '1' COMMENT '1 = Admin Users, 2 = Subscribers, 3 = Clients',
  `name` varchar(255) NOT NULL,
  `config` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_by` bigint(8) NOT NULL,
  `updated_by` bigint(8) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
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
  `id_user` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_user_parent` bigint(8) UNSIGNED DEFAULT '0',
  `id_user_tree` blob,
  `id_role` bigint(8) UNSIGNED NOT NULL,
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
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE INDEX `idx_email` (`email`),
  CONSTRAINT `fk_id_role` FOREIGN KEY (`id_role`) REFERENCES `Roles` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Urls` (
  `id_url` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `UrlsLang` (
  `id_url_lang` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_url` bigint(8) UNSIGNED NOT NULL,
  `id_lang` int(11) UNSIGNED NOT NULL,
  `url` blob NOT NULL,
  `url_redirect` blob,
  `dispatch` varchar(255) NOT NULL,
  `title` blob NOT NULL,
  `layout` varchar(255) NULL,
  `template` varchar(255) NULL,
  `tpl` varchar(255) NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_url_lang`),
  CONSTRAINT `fk_id_url` FOREIGN KEY (`id_url`) REFERENCES `Urls` (`id_url`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Categories` (
  `id_category` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_parent` bigint(8) UNSIGNED DEFAULT '0' NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NULL,
  `type` enum('category','hierarchy', 'menu') DEFAULT 'category' NOT NULL,
  `sort` int(11) DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Categories` (`id_category`, `title`, `name`, `type`, `created_at`) VALUES (1, 'Main Menu', 'main_menu', 'menu', NOW());

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Lists` (
  `id_list` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_category` bigint(8) UNSIGNED NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_list`),
  CONSTRAINT `fk_id_category` FOREIGN KEY (`id_category`) REFERENCES `Categories` (`id_category`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Lists` (`id_list`, `id_category`, `created_at`) VALUES (1, 1, NOW());

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ListsFields` (
  `id_field` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_list` bigint(8) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('text', 'textarea', 'html', 'image', 'file', 'checkbox') DEFAULT 'text' NOT NULL,
  `sort` int(11) DEFAULT '0',
  `required` tinyint(1) DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_field`),
  CONSTRAINT `fk_id_list` FOREIGN KEY (`id_list`) REFERENCES `Lists` (`id_list`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ListsFields` (`id_list`, `title`, `name`, `type`, `required`, `created_at`) VALUES
(1, 'Icon', 'icon', 'image', 1, NOW()),
(1, 'Link', 'link', 'text', 1, NOW());

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Layouts` (
  `id_layout` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `title` varchar(255) NOT NULL,
  `config` varchar(255) NOT NULL,
  `type` enum('grid', 'blocks') DEFAULT 'grid',
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_layout`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Layouts` (`id_layout`, `title`, `config`, `created_at`) VALUES (1, 'Default', 'page_layout.json', NOW());

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `PageTypes` (
  `id_type` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `PageTypes` (`id_type`, `title`, `name`, `created_at`) VALUES
(1, 'Article', 'article', NOW()),
(2, 'Slider', 'slider', NOW()),
(3, 'Gallery', 'gallery', NOW()),
(4, 'Forum', 'forum', NOW());

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Fields` (
  `id_field` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_type` bigint(8) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('text', 'textarea', 'html', 'image', 'file', 'checkbox', 'list') DEFAULT 'text' NOT NULL,
  `sort` int(11) DEFAULT '0',
  `required` tinyint(1) DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_field`),
  CONSTRAINT `fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `PageTypes` (`id_type`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Fields` (`id_type`, `title`, `name`, `type`, `required`, `created_at`) VALUES
(1, 'Abstract', 'abstract', 'textarea', 0, NOW()),
(2, 'Image', 'image', 'image', 1, NOW()),
(2, 'Thumbnail', 'thumbnail', 'image', 0, NOW()),
(2, 'Link', 'link', 'text', 0, NOW());

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `Content` (
  `id_content` bigint(8) UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_url` bigint(8) UNSIGNED NOT NULL,
  `id_type` bigint(8) UNSIGNED NOT NULL,
  `id_layout` bigint(8) UNSIGNED NOT NULL,
  `id_user` bigint(8) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NULL,
  `deleted_at` datetime NULL,
  PRIMARY KEY (`id_content`),
  CONSTRAINT `fk_pc_id_url` FOREIGN KEY (`id_url`) REFERENCES `Urls` (`id_url`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pc_id_type` FOREIGN KEY (`id_type`) REFERENCES `PageTypes` (`id_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pc_id_layout` FOREIGN KEY (`id_layout`) REFERENCES `Layouts` (`id_layout`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pc_id_user` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
