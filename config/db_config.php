<?php
/**
 * Database Configuration for LSUC Payment System
 *
 * Update DB_HOST, DB_USER, DB_PASS, DB_NAME with your hosting/cPanel
 * MySQL credentials before deploying to the live server.
 *
 * On XAMPP (local dev):  host=localhost, user=root, pass=(empty)
 * On cPanel hosting:     use the credentials from cPanel > MySQL Databases
 *
 * Run /api/db_setup.php once on the live server to create the database
 * and tables automatically, then delete db_setup.php and db_test.php.
 */

define('DB_HOST', 'localhost');        // Try '127.0.0.1' if 'localhost' fails on live server
define('DB_USER', 'root');             // cPanel: your MySQL username (NOT your cPanel login)
define('DB_PASS', '');                 // cPanel: your MySQL password
define('DB_NAME', 'lsuc_payment_db'); // Must exist — run db_setup.php to create it

/** Last connection error message — set by getDbConnection() on failure */
$GLOBALS['DB_LAST_ERROR'] = '';

/**
 * Returns a PDO instance or null on failure.
 * Check $GLOBALS['DB_LAST_ERROR'] for the reason when null is returned.
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $GLOBALS['DB_LAST_ERROR'] = '';
        return $pdo;
    } catch (PDOException $e) {
        $GLOBALS['DB_LAST_ERROR'] = $e->getMessage();
        error_log("DB connection failed: " . $e->getMessage());
        return null;
    }
}

/** Returns true/false and populates $GLOBALS['DB_LAST_ERROR'] on failure. */
function testDbConnection() {
    $pdo = getDbConnection();
    return ($pdo !== null);
}