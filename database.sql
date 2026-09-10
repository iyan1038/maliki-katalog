-- ============================================================
-- E-Katalog Database Schema
-- Struktur identik untuk environment XAMPP (testing) & Laragon (server)
-- MySQL 5.7+ / MariaDB 10.x
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `ekatalog`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `ekatalog`;

-- ------------------------------------------------------------
-- Tabel: users
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `google_id` VARCHAR(64) NULL DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NULL DEFAULT NULL,
  `avatar` VARCHAR(255) NULL DEFAULT 'avatar1.png',
  `role` ENUM('admin','member') NOT NULL DEFAULT 'member',
  `wa_number` VARCHAR(20) NULL DEFAULT NULL,
  `dark_mode` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_google` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: companies
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `npwp` VARCHAR(30) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `logo` VARCHAR(255) NULL DEFAULT NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_companies_user` (`user_id`),
  CONSTRAINT `fk_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: kbli
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `kbli`;
CREATE TABLE `kbli` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kbli_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: company_kbli
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `company_kbli`;
CREATE TABLE `company_kbli` (
  `company_id` INT UNSIGNED NOT NULL,
  `kbli_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`company_id`,`kbli_id`),
  KEY `fk_company_kbli_kbli` (`kbli_id`),
  CONSTRAINT `fk_company_kbli_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_company_kbli_kbli` FOREIGN KEY (`kbli_id`) REFERENCES `kbli` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: platforms
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `platforms`;
CREATE TABLE `platforms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `logo` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_platforms_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: products
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `price` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `unit` VARCHAR(50) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `long_description` TEXT NULL,
  `avg_rating` DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  `rating_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_promo` TINYINT(1) NOT NULL DEFAULT 0,
  `promo_price` DECIMAL(15,2) NULL DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `total_views` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_products_company` (`company_id`),
  KEY `idx_products_rating` (`avg_rating`,`rating_count`),
  KEY `idx_products_promo` (`is_promo`),
  CONSTRAINT `fk_products_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: product_kbli
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `product_kbli`;
CREATE TABLE `product_kbli` (
  `product_id` INT UNSIGNED NOT NULL,
  `kbli_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`,`kbli_id`),
  KEY `fk_product_kbli_kbli` (`kbli_id`),
  CONSTRAINT `fk_product_kbli_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_kbli_kbli` FOREIGN KEY (`kbli_id`) REFERENCES `kbli` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: product_images
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `position` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_product_images_product` (`product_id`),
  CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: product_platforms
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `product_platforms`;
CREATE TABLE `product_platforms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `platform_id` INT UNSIGNED NOT NULL,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 0,
  `product_url` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_platforms_product` (`product_id`),
  KEY `fk_product_platforms_platform` (`platform_id`),
  CONSTRAINT `fk_product_platforms_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_platforms_platform` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: ratings
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `ratings`;
CREATE TABLE `ratings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ratings_user_product` (`user_id`,`product_id`),
  KEY `fk_ratings_product` (`product_id`),
  CONSTRAINT `fk_ratings_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ratings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ck_ratings_value` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: favorites
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_favorites_user_product` (`user_id`,`product_id`),
  KEY `fk_favorites_product` (`product_id`),
  CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_favorites_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: banners
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `url` VARCHAR(255) NULL DEFAULT NULL,
  `position` ENUM('top','middle','bottom') NOT NULL DEFAULT 'top',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: user_behaviors
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `user_behaviors`;
CREATE TABLE `user_behaviors` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `behavior_type` ENUM('view','search','click') NOT NULL DEFAULT 'view',
  `platform_id` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_behaviors_user` (`user_id`),
  KEY `fk_behaviors_product` (`product_id`),
  KEY `fk_behaviors_platform` (`platform_id`),
  CONSTRAINT `fk_behaviors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_behaviors_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_behaviors_platform` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: search_logs
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `search_logs`;
CREATE TABLE `search_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `keyword` VARCHAR(150) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_search_logs_user` (`user_id`),
  CONSTRAINT `fk_search_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: password_resets
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_resets_token` (`token`),
  KEY `idx_password_resets_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: wa_logs
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `wa_logs`;
CREATE TABLE `wa_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `message_type` ENUM('promo','rekomendasi') NOT NULL DEFAULT 'promo',
  `status` ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `response` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_wa_logs_user` (`user_id`),
  CONSTRAINT `fk_wa_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: settings
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: categories (kategori produk berbasis KBLI)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`),
  KEY `idx_categories_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: category_kbli (relasi kategori -> KBLI)
-- Produk otomatis masuk kategori melalui KBLI yang dimilikinya.
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `category_kbli`;
CREATE TABLE `category_kbli` (
  `category_id` INT UNSIGNED NOT NULL,
  `kbli_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`category_id`,`kbli_id`),
  KEY `fk_category_kbli_kbli` (`kbli_id`),
  CONSTRAINT `fk_category_kbli_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_category_kbli_kbli` FOREIGN KEY (`kbli_id`) REFERENCES `kbli` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEEDER
-- ============================================================

-- Users (admin & member) — password di-hash bcrypt
INSERT INTO `users` (`id`, `google_id`, `name`, `email`, `password`, `avatar`, `role`, `wa_number`, `is_active`) VALUES
(1, NULL, 'Administrator', 'admin@ekatalog.test', '$2y$10$blwwhtGtIg2bho9BLm79eupXtEOWaYa7WDje4gftKCiVewtQEcW7.', 'avatar1.png', 'admin', NULL, 1),
(2, NULL, 'Member', 'member@ekatalog.test', '$2y$10$Vk1acEniW4GF9hMvPSCDwOlv1/dsEA/reEaJ5rTpFfGioAy8af.Ba', 'avatar1.png', 'member', NULL, 1);

-- Platforms marketplace default
INSERT INTO `platforms` (`id`, `name`, `slug`, `url`, `logo`) VALUES
(1, 'SIPLah', 'siplah', 'https://siplah.blibli.com', NULL),
(2, 'Tokoladang', 'tokoladang', 'https://tokoladang.id', NULL),
(3, 'GratisOngkir', 'gratisongkir', 'https://gratisongkir.id', NULL);

-- KBLI contoh
INSERT INTO `kbli` (`id`, `code`, `name`) VALUES
(1, '21010', 'Industri Farmasi dan Obat Tradisional'),
(2, '10761', 'Industri Kopi'),
(3, '14120', 'Industri Pakaian Jadi'),
(4, '22112', 'Industri Ban Vulkanisir');

-- Kategori produk + relasi ke KBLI (diatur sekali; produk masuk otomatis)
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `is_active`) VALUES
(1, 'Kopi & Minuman', 'kopi-minuman', 1, 1),
(2, 'Pakaian', 'pakaian', 2, 1),
(3, 'Farmasi', 'farmasi', 3, 1),
(4, 'Otomotif', 'otomotif', 4, 1);

INSERT INTO `category_kbli` (`category_id`, `kbli_id`) VALUES
(1, 2),
(2, 3),
(3, 1),
(4, 4);

-- Setting default aplikasi
INSERT INTO `settings` (`key`, `value`) VALUES
('app_name', 'E-Katalog'),
('app_tagline', 'Katalog Produk Digital'),
('google_login_enabled', '0'),
('wa_enabled', '0');

SET FOREIGN_KEY_CHECKS = 1;
