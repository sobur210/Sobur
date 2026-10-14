-- Sobur portfolio — database schema
-- MySQL 5.7+ / MariaDB 10.2+ / MySQL 8
-- Import: mysql -u root -p < database/schema.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS `sobur_portfolio`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `sobur_portfolio`;

-- ---------------------------------------------------------------------
-- Admins
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(60)  NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Projects
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`            VARCHAR(160) NOT NULL,
    `title`           VARCHAR(160) NOT NULL,
    `category`        VARCHAR(80)  NOT NULL DEFAULT '',
    `tagline`         VARCHAR(255) NOT NULL DEFAULT '',
    `description`     TEXT         NULL,
    `tags`            VARCHAR(255) NOT NULL DEFAULT '',
    `brand_primary`   CHAR(7)      NOT NULL DEFAULT '#0A7F52',
    `brand_secondary` CHAR(7)      NOT NULL DEFAULT '#FFFFFF',
    `brand_surface`   CHAR(7)      NOT NULL DEFAULT '#F3FAF6',
    `live_url`        VARCHAR(255) NOT NULL DEFAULT '',
    `mockup_type`     VARCHAR(40)  NOT NULL DEFAULT 'isobash',
    `is_featured`     TINYINT(1)   NOT NULL DEFAULT 0,
    `sort_order`      INT          NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_projects_slug` (`slug`),
    KEY `idx_projects_sort` (`sort_order`),
    KEY `idx_projects_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Contact messages
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120) NOT NULL,
    `email`      VARCHAR(190) NOT NULL,
    `message`    TEXT         NOT NULL,
    `ip_address` VARCHAR(45)  NOT NULL DEFAULT '',
    `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_messages_created` (`created_at`),
    KEY `idx_messages_read` (`is_read`),
    KEY `idx_messages_ip_created` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Login attempts (brute-force lockout)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_address`   VARCHAR(45)  NOT NULL DEFAULT '',
    `username`     VARCHAR(60)  NOT NULL DEFAULT '',
    `successful`   TINYINT(1)   NOT NULL DEFAULT 0,
    `attempted_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_attempts_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
