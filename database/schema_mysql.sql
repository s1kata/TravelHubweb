-- MySQL schema for Travel Hub
-- Используйте этот файл для создания таблиц в MySQL на SpaceWeb
-- Выполните этот SQL через phpMyAdmin или консоль MySQL

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    age INT DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    passport_series VARCHAR(20) DEFAULT NULL,
    passport_number VARCHAR(50) DEFAULT NULL,
    passport_issued_by TEXT DEFAULT NULL,
    passport_issue_date DATE DEFAULT NULL,
    passport_expiry_date DATE DEFAULT NULL,
    role VARCHAR(20) DEFAULT 'user',
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'active',
    source VARCHAR(20) DEFAULT 'website',
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

