<?php
/**
 * Swish Payment — Create Payment Record
 * ============================================================
 * Creates a pending payment row in the payments table before
 * the modal is opened, returning an internal_ref the JS uses
 * to track the whole payment lifecycle.
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/swish_config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$full_name   = trim($_POST['full_name']   ?? 'Website Visitor');
$phone       = trim($_POST['phone']       ?? '');
$amount      = trim($_POST['amount']      ?? '0');
$category    = trim($_POST['category']    ?? 'General Payment');
$description = trim($_POST['description'] ?? '');
$source_id   = trim($_POST['source_id']   ?? '');

if (!is_numeric($amount) || (float)$amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'A valid amount is required']);
    exit;
}

// Generate unique transaction reference
$internal_ref = 'LSUC' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));

$pdo = getDbConnection();
if (!$pdo) {
    // Try to auto-create the database and reconnect
    $dbError = $GLOBALS['DB_LAST_ERROR'] ?? 'unknown';
    try {
        $pdoRoot = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdoRoot->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo = getDbConnection(); // retry
    } catch (Exception $e2) {
        // ignore - will fall through to error below
    }
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' =>
            'Database connection failed: ' . ($GLOBALS['DB_LAST_ERROR'] ?? $dbError) .
            ' — Please visit /api/db_setup.php on the server to set up the database.']);
        exit;
    }
}

// Ensure payments table has required columns
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        id                   INT AUTO_INCREMENT PRIMARY KEY,
        transaction_reference VARCHAR(100) UNIQUE NOT NULL,
        phone_number          VARCHAR(30)  NOT NULL DEFAULT '',
        amount                DECIMAL(10,2) NOT NULL,
        status                VARCHAR(30)  NOT NULL DEFAULT 'pending',
        category              VARCHAR(100) NULL,
        description           VARCHAR(255) NULL,
        source_id             VARCHAR(255) NULL,
        swish_trans_link_id   VARCHAR(100) NULL,
        swish_method          VARCHAR(20)  NULL,
        created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Add Swish columns if table already existed without them
    try { $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS swish_trans_link_id VARCHAR(100) NULL"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS swish_method VARCHAR(20) NULL"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS category VARCHAR(100) NULL"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS description VARCHAR(255) NULL"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS source_id VARCHAR(255) NULL"); } catch(Exception $e){}

} catch (Exception $e) {
    // Table exists — continue
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO payments
            (transaction_reference, phone_number, amount, status, category, description, source_id)
         VALUES (?, ?, ?, 'pending', ?, ?, ?)"
    );
    $stmt->execute([$internal_ref, $phone, (float)$amount, $category, $description, $source_id]);

    echo json_encode([
        'success'      => true,
        'internal_ref' => $internal_ref,
        'amount'       => (float)$amount,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not create payment record: ' . $e->getMessage()]);
}
