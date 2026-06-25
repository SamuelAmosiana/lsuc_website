-- ============================================================
--  LSUC Payment System — Database Setup
--  Run this in phpMyAdmin → SQL tab
--  Works on both localhost (XAMPP) and live server
-- ============================================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS `lsuc_payment_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Step 2: Use it
USE `lsuc_payment_db`;

-- Step 3: Create payments table
CREATE TABLE IF NOT EXISTS `payments` (
  `id`                    INT AUTO_INCREMENT PRIMARY KEY,
  `transaction_reference` VARCHAR(100)   NOT NULL UNIQUE,
  `phone_number`          VARCHAR(30)    NOT NULL DEFAULT '',
  `amount`                DECIMAL(10,2)  NOT NULL,
  `status`                VARCHAR(30)    NOT NULL DEFAULT 'pending',
  `category`              VARCHAR(100)   NULL,
  `description`           VARCHAR(255)   NULL,
  `source_id`             VARCHAR(255)   NULL,
  `swish_trans_link_id`   VARCHAR(100)   NULL,
  `swish_method`          VARCHAR(20)    NULL,
  `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Create payment_logs table
CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id`                    INT AUTO_INCREMENT PRIMARY KEY,
  `transaction_reference` VARCHAR(100)  NULL,
  `raw_response`          TEXT          NULL,
  `action_taken`          VARCHAR(50)   NULL,
  `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Done! You should see "2 table(s) affected" or similar.
SELECT 'Setup complete! lsuc_payment_db is ready.' AS result;
