-- Database Schema for LSUC Payment System

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS lsuc_payment_db;

-- Use the database
USE lsuc_payment_db;

-- Create payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_reference VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'successful', 'failed') DEFAULT 'pending',
    provider VARCHAR(50) DEFAULT 'MobileMoney',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_transaction_reference (transaction_reference),
    INDEX idx_phone_number (phone_number),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Optional: Create a table for storing payment metadata
CREATE TABLE payment_metadata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_reference VARCHAR(255) NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_reference) REFERENCES payments(transaction_reference) ON DELETE CASCADE,
    INDEX idx_transaction_key (transaction_reference, key_name)
);

-- Create a table for logging payment events
CREATE TABLE payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_reference VARCHAR(255),
    event_type VARCHAR(50) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction_reference (transaction_reference),
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at)
);