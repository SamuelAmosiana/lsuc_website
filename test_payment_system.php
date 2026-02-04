<?php
/**
 * Test script for LSUC Payment System
 * This script tests the database connection and basic functionality
 */

echo "<h2>LSUC Payment System Test</h2>";

// Include the database configuration
require_once './config/db_config.php';

echo "<h3>Testing Database Connection...</h3>";

// Test database connection
if (testDbConnection()) {
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test getting a connection
    $pdo = getDbConnection();
    if ($pdo) {
        echo "<p style='color: green;'>✓ PDO connection established!</p>";
        
        // Test creating the payments table
        require_once './api/initiate-payment.php'; // This will run the createPaymentsTable function
        
        // Manually call the function to test table creation
        if (function_exists('createPaymentsTable')) {
            if (createPaymentsTable()) {
                echo "<p style='color: green;'>✓ Payments table created/referenced successfully!</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to create payments table!</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Failed to establish PDO connection!</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database connection failed!</p>";
    echo "<p>Please check your database configuration in ./config/db_config.php</p>";
}

echo "<h3>API Endpoint Tests</h3>";
echo "<ul>";
echo "<li>Initiate Payment API: <a href='./api/initiate-payment.php' target='_blank'>./api/initiate-payment.php</a> (should return JSON error for invalid request)</li>";
echo "<li>Payment Callback API: <a href='./api/payment-callback.php' target='_blank'>./api/payment-callback.php</a> (should return JSON error for invalid request)</li>";
echo "</ul>";

echo "<h3>Frontend Test</h3>";
echo "<p>Navigate to the #payment-instructions section on your website and verify the 'Pay or Make Payment' button appears.</p>";

echo "<h3>Configuration Check</h3>";
echo "<p>Make sure to update the database credentials in <code>./config/db_config.php</code> with your actual database settings.</p>";
?>