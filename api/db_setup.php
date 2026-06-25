<?php
/**
 * LSUC Payment System — Database Setup Script
 * =============================================
 * Run this ONCE on your live server to create the database and tables:
 *   https://srms.lsc.edu.zm/api/db_setup.php
 *
 * DELETE THIS FILE after running it successfully.
 */

header('Cache-Control: no-store');
header('Content-Type: text/html; charset=utf-8');

$configPath = __DIR__ . '/../config/db_config.php';
if (!file_exists($configPath)) {
    die('<p style="color:red">config/db_config.php not found at: ' . $configPath . '</p>');
}
require_once $configPath;

$steps  = [];
$errors = [];

/* ── Step 1: Connect to MySQL (no DB selected) ───────────── */
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $steps[] = "✅ Connected to MySQL server (" . DB_HOST . ") as " . DB_USER;
} catch (PDOException $e) {
    $errors[] = "❌ Cannot connect to MySQL: " . $e->getMessage();
    $errors[] = "→ Update DB_HOST, DB_USER, DB_PASS in config/db_config.php with your hosting credentials.";
    goto output;
}

/* ── Step 2: Create database if it doesn't exist ─────────── */
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $steps[] = "✅ Database '" . DB_NAME . "' is ready (created if it did not exist).";
} catch (PDOException $e) {
    $errors[] = "❌ Could not create database '" . DB_NAME . "': " . $e->getMessage();
    $errors[] = "→ Either create it manually in cPanel, or check that your DB user has CREATE DATABASE privilege.";
    goto output;
}

/* ── Step 3: Switch to the target database ───────────────── */
try {
    $pdo->exec("USE `" . DB_NAME . "`");
    $steps[] = "✅ Switched to database '" . DB_NAME . "'.";
} catch (PDOException $e) {
    $errors[] = "❌ Could not USE database: " . $e->getMessage();
    goto output;
}

/* ── Step 4: Create payments table ──────────────────────── */
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (
        `id`                    INT AUTO_INCREMENT PRIMARY KEY,
        `transaction_reference` VARCHAR(100)  UNIQUE NOT NULL,
        `phone_number`          VARCHAR(30)   NOT NULL DEFAULT '',
        `amount`                DECIMAL(10,2) NOT NULL,
        `status`                VARCHAR(30)   NOT NULL DEFAULT 'pending',
        `category`              VARCHAR(100)  NULL,
        `description`           VARCHAR(255)  NULL,
        `source_id`             VARCHAR(255)  NULL,
        `swish_trans_link_id`   VARCHAR(100)  NULL,
        `swish_method`          VARCHAR(20)   NULL,
        `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $steps[] = "✅ 'payments' table is ready.";
} catch (PDOException $e) {
    $errors[] = "❌ Could not create payments table: " . $e->getMessage();
    goto output;
}

/* ── Step 5: Create payment_logs table ──────────────────── */
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `payment_logs` (
        `id`                    INT AUTO_INCREMENT PRIMARY KEY,
        `transaction_reference` VARCHAR(100)  NULL,
        `raw_response`          TEXT          NULL,
        `action_taken`          VARCHAR(50)   NULL,
        `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $steps[] = "✅ 'payment_logs' table is ready.";
} catch (PDOException $e) {
    $errors[] = "❌ Could not create payment_logs table: " . $e->getMessage();
    goto output;
}

/* ── Step 6: Quick write test ────────────────────────────── */
try {
    $testRef = 'SETUP_TEST_' . time();
    $pdo->prepare("INSERT INTO payments (transaction_reference, phone_number, amount, status, category)
                   VALUES (?, '260000000000', 1.00, 'test', 'Setup Test')")
        ->execute([$testRef]);
    $pdo->prepare("DELETE FROM payments WHERE transaction_reference = ?")
        ->execute([$testRef]);
    $steps[] = "✅ Read/write test PASSED — database is fully operational.";
} catch (PDOException $e) {
    $errors[] = "❌ Read/write test failed: " . $e->getMessage();
}

output:
?>
<!DOCTYPE html>
<html>
<head>
<title>LSUC DB Setup</title>
<style>
body{font-family:monospace;background:#111;color:#eee;padding:30px;font-size:14px;}
h2{color:#ffb347;}
.ok{color:#6fcf6f;} .fail{color:#e74c3c;} .info{color:#56d0e0;}
pre{background:#1a1a1a;padding:16px;border-radius:8px;line-height:1.8;}
a{color:#ffb347;}
</style>
</head>
<body>
<h2>LSUC Payment System — Database Setup</h2>

<?php if (!empty($steps)): ?>
<h3 class="ok">Steps Completed:</h3>
<pre class="ok"><?php echo implode("\n", array_map('htmlspecialchars', $steps)); ?></pre>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<h3 class="fail">Errors:</h3>
<pre class="fail"><?php echo implode("\n", array_map('htmlspecialchars', $errors)); ?></pre>
<?php endif; ?>

<?php if (empty($errors)): ?>
<h3 class="ok">🎉 Setup Complete!</h3>
<pre>
Your payment database is ready. Here's a summary:

  Database : <?= htmlspecialchars(DB_NAME) ?>

  Tables   : payments
             payment_logs

  Next steps:
  1. Test a payment on the website
  2. DELETE this file: /api/db_setup.php
  3. DELETE the diagnostic file: /api/db_test.php
</pre>
<?php else: ?>
<h3 class="info">What to do:</h3>
<pre>
1. Log in to your hosting cPanel (or Plesk / DirectAdmin).
2. Go to "MySQL Databases".
3. Note the:
     • Database host  (often "localhost" or an IP like "127.0.0.1")
     • MySQL username (NOT your cPanel login — a database user)
     • MySQL password (set when you created the DB user)
4. Edit:  config/db_config.php
   Update: DB_HOST, DB_USER, DB_PASS, DB_NAME
5. Upload the updated config/db_config.php to the server.
6. Visit this page again: /api/db_setup.php
</pre>
<?php endif; ?>

<p style="color:#666;margin-top:30px;font-size:11px;">
⚠️ Delete <strong>db_setup.php</strong> and <strong>db_test.php</strong> from the server after your issue is resolved.
</p>
</body>
</html>
