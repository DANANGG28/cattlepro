-- =============================================
-- CattlePro Database Schema
-- Cattle Reproduction Management System
-- =============================================

CREATE DATABASE IF NOT EXISTS `cattlepro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cattlepro`;

-- =============================================
-- Table: users
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'petugas') NOT NULL DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: sapi
-- =============================================
CREATE TABLE IF NOT EXISTS `sapi` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_sapi` VARCHAR(20) NOT NULL UNIQUE,
    `jenis` VARCHAR(50) NOT NULL,
    `tanggal_lahir` DATE NOT NULL,
    `berat` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `status_reproduksi` ENUM('Kosong', 'Sudah Birahi', 'Sudah IB', 'Bunting', 'Gagal Hamil') NOT NULL DEFAULT 'Kosong',
    `tanggal_ib` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: birahi
-- =============================================
CREATE TABLE IF NOT EXISTS `birahi` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_sapi` INT(11) NOT NULL,
    `tanggal_birahi` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_sapi`) REFERENCES `sapi`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: log_aktivitas
-- =============================================
CREATE TABLE IF NOT EXISTS `log_aktivitas` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `jenis_aktivitas` VARCHAR(50) NOT NULL,
    `deskripsi` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Default Admin Account
-- Email: admin@cattlepro.com
-- Password: admin123
-- =============================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Administrator', 'admin@cattlepro.com', '$2y$10$ELQI9LHKSe6OIjVsuCHdkef0tgJF9VyOZFsM0VDG4kCD44wrMHqR2', 'admin');

-- Default login: admin@cattlepro.com / admin123
