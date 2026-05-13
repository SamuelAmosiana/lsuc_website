<?php
/**
 * Database Configuration for LSUC Payment System
 * 
 * This file contains the database configuration settings used by the payment system.
 * Update these values to match your database setup before deployment.
 */

// Database Configuration
define('DB_HOST', 'localhost');           // Database host
define('DB_USER', 'root');               // Database username
define('DB_PASS', '');                   // Database password
define('DB_NAME', 'lsuc_payment_db');    // Database name

// Optional: Create a function to get PDO connection
function getDbConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Test the database connection
function testDbConnection() {
    try {
        $pdo = getDbConnection();
        if ($pdo) {
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        }
        return false;
    } catch(Exception $e) {
        error_log("Database test connection failed: " . $e->getMessage());
        return false;
    }
}
?>