<?php
/**
 * LSUC — DB Ping (root-level, avoids /api/ SRMS routing conflict)
 * Visit: https://srms.lsc.edu.zm/db_ping.php  OR  http://localhost/lsuc_website_backup/db_ping.php
 * DELETE after use.
 */
header('Content-Type: text/plain; charset=utf-8');

$config = __DIR__ . '/config/db_config.php';
if (!file_exists($config)) { die("FAIL: config/db_config.php not found at: $config"); }
require_once $config;

echo "=== LSUC DB PING ===\n";
echo "PHP version : " . phpversion() . "\n";
echo "PDO MySQL   : " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO — install php-mysql') . "\n";
echo "DB_HOST     : " . DB_HOST . "\n";
echo "DB_USER     : " . DB_USER . "\n";
echo "DB_PASS     : " . (DB_PASS === '' ? '(empty)' : '***') . "\n";
echo "DB_NAME     : " . DB_NAME . "\n\n";

// Test 1: Can we reach MySQL at all?
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $ver = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "MySQL server: OK (version $ver)\n";
} catch (PDOException $e) {
    echo "MySQL server: FAIL — " . $e->getMessage() . "\n";
    echo "\nFIX: Update DB_HOST / DB_USER / DB_PASS in config/db_config.php\n";
    echo "     Get credentials from cPanel → MySQL Databases\n";
    exit;
}

// Test 2: Does the database exist?
$dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array(DB_NAME, $dbs)) {
    echo "Database    : MISSING — '" . DB_NAME . "' does not exist\n";
    echo "\nFIX: Run the SQL in db_setup.sql via phpMyAdmin, then retry.\n";
    exit;
}
echo "Database    : OK (" . DB_NAME . " exists)\n";

// Test 3: Can we connect to it?
try {
    $pdo2 = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "DB connect  : FAIL — " . $e->getMessage() . "\n";
    exit;
}
echo "DB connect  : OK\n";

// Test 4: Does the payments table exist?
$tables = $pdo2->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('payments', $tables)) {
    echo "payments tbl: MISSING\n";
    echo "\nFIX: Run the SQL in db_setup.sql via phpMyAdmin\n";
    exit;
}
echo "payments tbl: OK\n";

// Test 5: Write test
try {
    $ref = 'PING_' . time();
    $pdo2->prepare("INSERT INTO payments (transaction_reference, phone_number, amount, status) VALUES (?,?,?,?)")
         ->execute([$ref, '260000000000', 1.00, 'test']);
    $pdo2->prepare("DELETE FROM payments WHERE transaction_reference=?")->execute([$ref]);
    echo "Write test  : OK\n";
} catch (PDOException $e) {
    echo "Write test  : FAIL — " . $e->getMessage() . "\n";
    exit;
}

echo "\n=== ALL CHECKS PASSED — payments are ready to go! ===\n";
echo "Remember to DELETE this file (db_ping.php) from the server.\n";
