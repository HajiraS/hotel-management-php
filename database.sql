-- =====================================================
-- Hotel Room Management System - database script
-- Run once:  mysql -u root -p < database.sql
-- or paste into phpMyAdmin's "SQL" tab.
-- =====================================================

CREATE DATABASE IF NOT EXISTS hotel_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hotel_db;

CREATE TABLE IF NOT EXISTS rooms (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10)   NOT NULL UNIQUE,            -- UNIQUE = database-level duplicate protection
    room_type   VARCHAR(50)   NOT NULL,                   -- Standard, Deluxe, Suite
    price       DECIMAL(10,2) NOT NULL,                   -- price per night (exact decimals, never FLOAT for money)
    status      VARCHAR(20)   NOT NULL DEFAULT 'Available', -- Available, Occupied, Maintenance
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample data (INSERT IGNORE skips rows whose room_number already exists,
-- so re-running this script will not fail).
INSERT IGNORE INTO rooms (room_number, room_type, price, status) VALUES
    ('101', 'Standard', 89.00,  'Available'),
    ('102', 'Standard', 89.00,  'Occupied'),
    ('103', 'Standard', 95.00,  'Maintenance'),
    ('201', 'Deluxe',   149.50, 'Available'),
    ('202', 'Deluxe',   149.50, 'Occupied'),
    ('203', 'Deluxe',   165.00, 'Available'),
    ('301', 'Suite',    289.00, 'Occupied'),
    ('302', 'Suite',    325.00, 'Available'),
    ('303', 'Suite',    399.99, 'Maintenance');
