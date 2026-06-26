-- ============================================================
--  The Travel CEO - Skema Database (PRO)
--  Aman dijalankan ulang. Gunakan via Installer Wizard
--  atau import manual lewat phpMyAdmin.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `settings` (
  `skey`   VARCHAR(100) NOT NULL,
  `svalue` TEXT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150) NOT NULL,
  `email`       VARCHAR(190) NOT NULL,
  `password`    VARCHAR(255) NOT NULL,
  `wa`          VARCHAR(30)  NULL,
  `role`        ENUM('admin','member') NOT NULL DEFAULT 'member',
  `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `ref_code`    VARCHAR(50)  NULL,
  `referred_by` INT UNSIGNED NULL,
  `created_at`  DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_wa` (`wa`),
  UNIQUE KEY `uq_ref` (`ref_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`        VARCHAR(40) NOT NULL DEFAULT 'ecourse',
  `title`       VARCHAR(190) NOT NULL,
  `slug`        VARCHAR(190) NOT NULL,
  `price`       INT UNSIGNED NOT NULL DEFAULT 0,
  `thumbnail`   VARCHAR(255) NULL,
  `short_desc`  VARCHAR(255) NULL,
  `long_desc`   TEXT NULL,
  `category_id` INT UNSIGNED NULL,
  `status`      ENUM('publish','draft') NOT NULL DEFAULT 'publish',
  `created_at`  DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pslug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lessons` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `title`      VARCHAR(190) NOT NULL,
  `short_desc` VARCHAR(255) NULL,
  `youtube`    VARCHAR(255) NULL,
  `sort`       INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `k_prod` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `salespages` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`          VARCHAR(190) NOT NULL,
  `slug`           VARCHAR(190) NOT NULL,
  `html`           MEDIUMTEXT NULL,
  `meta_title`     VARCHAR(190) NULL,
  `meta_desc`      VARCHAR(255) NULL,
  `facebook_pixel_id` VARCHAR(50) NULL,
  `featured_image` VARCHAR(255) NULL,
  `status`         ENUM('publish','draft') NOT NULL DEFAULT 'draft',
  `show_home`      TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`     DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sslug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `access_pages` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`     INT UNSIGNED NOT NULL,
  `title`          VARCHAR(190) NOT NULL,
  `slug`           VARCHAR(190) NOT NULL,
  `html`           MEDIUMTEXT NULL,
  `meta_title`     VARCHAR(190) NULL,
  `meta_desc`      VARCHAR(255) NULL,
  `featured_image` VARCHAR(255) NULL,
  `status`         ENUM('publish','draft') NOT NULL DEFAULT 'draft',
  `created_at`     DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_access_slug` (`slug`),
  UNIQUE KEY `uq_access_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`          VARCHAR(190) NOT NULL,
  `slug`           VARCHAR(190) NOT NULL,
  `excerpt`        VARCHAR(255) NULL,
  `html`           MEDIUMTEXT NULL,
  `meta_title`     VARCHAR(190) NULL,
  `meta_desc`      VARCHAR(255) NULL,
  `featured_image` VARCHAR(255) NULL,
  `status`         ENUM('publish','draft') NOT NULL DEFAULT 'draft',
  `published_at`   DATETIME NULL,
  `created_at`     DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_post_slug` (`slug`),
  KEY `k_post_status_date` (`status`,`published_at`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `coupons` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(150) NOT NULL,
  `code`         VARCHAR(60) NOT NULL,
  `percent`      INT NOT NULL DEFAULT 0,
  `nominal`      INT NOT NULL DEFAULT 0,
  `product_id`   INT UNSIGNED NULL,
  `affiliate_id` INT UNSIGNED NULL,
  `start_date`   DATE NULL,
  `end_date`     DATE NULL,
  `max_use`      INT NOT NULL DEFAULT 0,
  `used_count`   INT NOT NULL DEFAULT 0,
  `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`   DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transactions` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(40) NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `product_id`  INT UNSIGNED NOT NULL,
  `bank`        VARCHAR(40) NULL,
  `amount`      INT UNSIGNED NOT NULL DEFAULT 0,
  `coupon_code` VARCHAR(60) NULL,
  `discount`    INT UNSIGNED NOT NULL DEFAULT 0,
  `total`       INT UNSIGNED NOT NULL DEFAULT 0,
  `proof`       VARCHAR(255) NULL,
  `note`        VARCHAR(500) NULL,
  `ref_code`    VARCHAR(50) NULL,
  `status`      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at`  DATETIME NOT NULL,
  `approved_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tcode` (`code`),
  KEY `k_user` (`user_id`),
  KEY `k_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `enrollments` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enroll` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `progress` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `lesson_id`  INT UNSIGNED NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_prog` (`user_id`,`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `commissions` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id`   INT UNSIGNED NOT NULL,
  `transaction_id` INT UNSIGNED NOT NULL,
  `amount`         INT UNSIGNED NOT NULL DEFAULT 0,
  `status`         ENUM('pending','approved','paid') NOT NULL DEFAULT 'pending',
  `withdrawal_id`  BIGINT UNSIGNED NULL,
  `created_at`     DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_aff` (`affiliate_id`),
  KEY `k_withdrawal` (`withdrawal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `commission_withdrawals` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id`   INT UNSIGNED NOT NULL,
  `amount`         INT UNSIGNED NOT NULL DEFAULT 0,
  `bank_name`      VARCHAR(100) NOT NULL,
  `account_name`   VARCHAR(150) NOT NULL,
  `account_number` VARCHAR(100) NOT NULL,
  `note`           VARCHAR(500) NULL,
  `admin_note`     VARCHAR(500) NULL,
  `status`         ENUM('requested','approved','rejected','paid') NOT NULL DEFAULT 'requested',
  `created_at`     DATETIME NOT NULL,
  `approved_at`    DATETIME NULL,
  `rejected_at`    DATETIME NULL,
  `paid_at`        DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `k_affiliate_status` (`affiliate_id`,`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clicks` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` INT UNSIGNED NOT NULL,
  `created_at`   DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_aff2` (`affiliate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NULL,
  `action`     VARCHAR(120) NOT NULL,
  `detail`     VARCHAR(500) NULL,
  `ip`         VARCHAR(60) NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `login_otps` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `purpose`    VARCHAR(40) NOT NULL DEFAULT 'login',
  `code_hash`  VARCHAR(64) NOT NULL,
  `attempts`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `used_at`    DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_user_purpose` (`user_id`,`purpose`,`created_at`),
  KEY `k_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `token_hash` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at`    DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_user_created` (`user_id`,`created_at`),
  KEY `k_token_hash` (`token_hash`),
  KEY `k_reset_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
