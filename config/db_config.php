<?php
/**
 * Database Configuration — LSUC Payment System
 * ============================================================
 * LOCAL (XAMPP):  DB_NAME = website_db, user = root, pass = (empty)
 * LIVE (VPS):     DB_NAME = website_db, user = lsuc_db_user (created by vps_db_setup.sh)
 *
 * To switch between environments, this file auto-detects by hostname.
 * You can also override manually by setting the constants below.
 * ============================================================
 */

// ── Auto-detect environment ────────────────────────────────────────────────────
$_isLive = (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'lsc.edu.zm') !== false)
         || (isset($_SERVER['HTTP_HOST'])   && strpos($_SERVER['HTTP_HOST'],   'lsc.edu.zm') !== false);

if ($_isLive) {
    // ── LIVE VPS CREDENTIALS ──────────────────────────────────────────────────
    // These are set by the vps_db_setup.sh script.
    // If you changed the password in that script, update it here too.
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'lsuc_db_user');
    define('DB_PASS', 'LsucPay@2026!');   // ← change this if you set a different password
    define('DB_NAME', 'website_db');
} else {
    // ── LOCAL XAMPP CREDENTIALS ───────────────────────────────────────────────
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'website_db');
}

// ── Last error from failed connection attempt ──────────────────────────────────
$GLOBALS['DB_LAST_ERROR'] = '';

/**
 * Returns a PDO connection or null on failure.
 * On failure, $GLOBALS['DB_LAST_ERROR'] holds the reason.
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
        error_log("LSUC DB connection failed: " . $e->getMessage());
        return null;
    }
}

/** Simple boolean test — populates DB_LAST_ERROR on failure. */
function testDbConnection() {
    return (getDbConnection() !== null);
}