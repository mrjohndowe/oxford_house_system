CREATE DATABASE IF NOT EXISTS `oxford_central` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `oxford_central`;

CREATE TABLE IF NOT EXISTS `oxford_master_houses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `house_name` varchar(150) NOT NULL,
  `house_code` varchar(100) NOT NULL,
  `database_name` varchar(150) NOT NULL,
  `city` varchar(100) NOT NULL DEFAULT '',
  `state` varchar(50) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_house_code` (`house_code`),
  UNIQUE KEY `uniq_database_name` (`database_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oxford_master_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('house_user','house_manager','regional_admin','central_admin','super_admin') NOT NULL DEFAULT 'house_user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oxford_master_house_user_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `house_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_house_user` (`house_id`,`user_id`),
  CONSTRAINT `fk_oxford_house_access_house` FOREIGN KEY (`house_id`) REFERENCES `oxford_master_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_oxford_house_access_user` FOREIGN KEY (`user_id`) REFERENCES `oxford_master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `oxford_master_house_settings` (
  `house_id` int unsigned NOT NULL,
  `contract_stamp_password_hash` varchar(255) NOT NULL DEFAULT '',
  `updated_by_user_id` int unsigned DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`house_id`),
  CONSTRAINT `fk_oxford_master_house_settings_house` FOREIGN KEY (`house_id`) REFERENCES `oxford_master_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_oxford_master_house_settings_user` FOREIGN KEY (`updated_by_user_id`) REFERENCES `oxford_master_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oxford_master_activity` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `house_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `page_name` varchar(255) NOT NULL DEFAULT '',
  `event_name` varchar(100) NOT NULL DEFAULT '',
  `details_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_house_created` (`house_id`,`created_at`),
  KEY `idx_user_created` (`user_id`,`created_at`),
  CONSTRAINT `fk_oxford_master_activity_house` FOREIGN KEY (`house_id`) REFERENCES `oxford_master_houses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_oxford_master_activity_user` FOREIGN KEY (`user_id`) REFERENCES `oxford_master_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oxford_master_audit_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `house_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `action_name` varchar(100) NOT NULL DEFAULT '',
  `page_name` varchar(255) NOT NULL DEFAULT '',
  `target_table` varchar(150) NOT NULL DEFAULT '',
  `target_id` varchar(150) NOT NULL DEFAULT '',
  `ip_address` varchar(64) NOT NULL DEFAULT '',
  `details_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_house` (`house_id`,`created_at`),
  KEY `idx_audit_user` (`user_id`,`created_at`),
  CONSTRAINT `fk_oxford_master_audit_house` FOREIGN KEY (`house_id`) REFERENCES `oxford_master_houses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_oxford_master_audit_user` FOREIGN KEY (`user_id`) REFERENCES `oxford_master_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `oxford_master_houses` (`house_name`, `house_code`, `database_name`, `city`, `state`)
SELECT 'Default Oxford House', 'default-house', 'secretary', '', ''
WHERE NOT EXISTS (SELECT 1 FROM `oxford_master_houses` WHERE `house_code` = 'default-house');

INSERT INTO `oxford_master_users` (`full_name`, `email`, `password_hash`, `role`, `status`)
SELECT 'Oxford Central Admin', 'admin@oxford.local', '$2y$10$1Y8NDcxD4s6r5Q86A9A4yOtD7D2G9a9sY1A9vT0eM7s4R4l8wzWzK', 'central_admin', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `oxford_master_users` WHERE `email` = 'admin@oxford.local');
