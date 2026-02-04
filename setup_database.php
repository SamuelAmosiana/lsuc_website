<?php
/**
 * Database Setup Script for LSUC Payment System
 * 
 * This script creates the required database and tables for the payment system.
 * Run this script once to initialize the database.
 */

echo "<h2>LSUC Payment System - Database Setup</h2>";

// Include the database configuration
require_once './config/db_config.php';

echo "<h3>Creating Database and Tables...</h3>";

try {
    // Connect without specifying database name first
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "<p style='color: green;'>✓ Database '" . DB_NAME . "' created or already exists.</p>";
    
    // Select the database
    $pdo->exec("USE " . DB_NAME);
    
    // Create payments table
    $sql = "CREATE TABLE IF NOT EXISTS payments (
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
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Payments table created successfully!</p>";
    
    // Create payment metadata table (optional)
    $metadataSql = "CREATE TABLE IF NOT EXISTS payment_metadata (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_reference VARCHAR(255) NOT NULL,
        key_name VARCHAR(100) NOT NULL,
        value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (transaction_reference) REFERENCES payments(transaction_reference) ON DELETE CASCADE,
        INDEX idx_transaction_key (transaction_reference, key_name)
    )";
    
    $pdo->exec($metadataSql);
    echo "<p style='color: green;'>✓ Payment metadata table created successfully!</p>";
    
    // Create payment logs table (optional)
    $logsSql = "CREATE TABLE IF NOT EXISTS payment_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_reference VARCHAR(255),
        event_type VARCHAR(50) NOT NULL,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_transaction_reference (transaction_reference),
        INDEX idx_event_type (event_type),
        INDEX idx_created_at (created_at)
    )";
    
    $pdo->exec($logsSql);
    echo "<p style='color: green;'>✓ Payment logs table created successfully!</p>";
    
    echo "<h3>Database Setup Complete!</h3>";
    echo "<p style='color: green;'>✓ All required tables have been created.</p>";
    echo "<p>You can now use the payment system. Remember to update the database credentials in <code>config/db_config.php</code> if needed.</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Database setup failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration and ensure MySQL is running.</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "    <li>Verify the database configuration in <code>config/db_config.php</code></li>";
echo "    <li>Test the payment system by running <code>test_payment_system.php</code></li>";
echo "    <li>Integrate with a real mobile money provider API for production use</li>";
echo "</ul>";
?>