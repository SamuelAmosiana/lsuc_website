<?php
/**
 * LSUC Payment System — Database Diagnostic Tool
 * ================================================
 * Visit this file in your browser on the live server to diagnose
 * database connection issues:
 *   https://srms.lsc.edu.zm/api/db_test.php
 *
 * DELETE THIS FILE after fixing the issue — it exposes server info.
 */

// Prevent caching
header('Cache-Control: no-store');
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<title>LSUC DB Diagnostic</title>
<style>
body{font-family:monospace;background:#111;color:#eee;padding:30px;font-size:14px;}
h2{color:#ffb347;}
.ok{color:#6fcf6f;} .fail{color:#e74c3c;} .info{color:#56d0e0;}
table{border-collapse:collapse;margin:10px 0;}
td,th{padding:6px 14px;border:1px solid #333;text-align:left;}
th{background:#222;color:#ffb347;}
pre{background:#1a1a1a;padding:12px;border-radius:6px;overflow-x:auto;color:#ccc;}
</style>
</head>
<body>
<h2>LSUC Payment System — Database Diagnostic</h2>

<?php

/* ── 1. PHP & PDO info ───────────────────────────────────── */
echo "<h3 class='info'>1. PHP Environment</h3><table>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>PHP Version</td><td class='ok'>" . phpversion() . "</td></tr>";
echo "<tr><td>PDO Available</td><td class='" . (extension_loaded('pdo') ? 'ok' : 'fail') . "'>" . (extension_loaded('pdo') ? 'YES' : 'NO — PDO not installed') . "</td></tr>";
echo "<tr><td>PDO MySQL</td><td class='" . (extension_loaded('pdo_mysql') ? 'ok' : 'fail') . "'>" . (extension_loaded('pdo_mysql') ? 'YES' : 'NO — pdo_mysql driver missing') . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "</td></tr>";
echo "<tr><td>Script Path</td><td>" . __FILE__ . "</td></tr>";
echo "</table>";

/* ── 2. Config file check ────────────────────────────────── */
echo "<h3 class='info'>2. Config File</h3>";
$configPath = __DIR__ . '/../config/db_config.php';
if (!file_exists($configPath)) {
    echo "<p class='fail'>❌ config/db_config.php NOT FOUND at: $configPath</p>";
} else {
    echo "<p class='ok'>✅ config/db_config.php found</p>";
    require_once $configPath;

    echo "<table><tr><th>Constant</th><th>Value</th></tr>";
    echo "<tr><td>DB_HOST</td><td>" . (defined('DB_HOST') ? DB_HOST : '<span class=fail>NOT DEFINED</span>') . "</td></tr>";
    echo "<tr><td>DB_USER</td><td>" . (defined('DB_USER') ? DB_USER : '<span class=fail>NOT DEFINED</span>') . "</td></tr>";
    echo "<tr><td>DB_PASS</td><td>" . (defined('DB_PASS') ? (DB_PASS === '' ? '<em>(empty)</em>' : '***') : '<span class=fail>NOT DEFINED</span>') . "</td></tr>";
    echo "<tr><td>DB_NAME</td><td>" . (defined('DB_NAME') ? DB_NAME : '<span class=fail>NOT DEFINED</span>') . "</td></tr>";
    echo "</table>";

    /* ── 3. Connection test ──────────────────────────────── */
    echo "<h3 class='info'>3. Connection Test</h3>";

    // Test without DB name first (to check if server/user/pass works)
    echo "<p><strong>Step A:</strong> Connecting to MySQL server (no database selected)...</p>";
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p class='ok'>✅ Connected to MySQL server successfully!</p>";
        $version = $pdo->query("SELECT VERSION()")->fetchColumn();
        echo "<p class='info'>MySQL version: $version</p>";

        // List available databases
        echo "<p><strong>Step B:</strong> Available databases you have access to:</p><pre>";
        $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($dbs as $db) {
            $marker = ($db === DB_NAME) ? ' ← TARGET' : '';
            echo htmlspecialchars($db) . $marker . "\n";
        }
        echo "</pre>";

        // Check target database
        echo "<p><strong>Step C:</strong> Checking target database <code>" . htmlspecialchars(DB_NAME) . "</code>...</p>";
        if (in_array(DB_NAME, $dbs)) {
            echo "<p class='ok'>✅ Database '" . htmlspecialchars(DB_NAME) . "' EXISTS.</p>";

            // Connect to it
            $pdo2 = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check payments table
            echo "<p><strong>Step D:</strong> Checking 'payments' table...</p>";
            $tables = $pdo2->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (in_array('payments', $tables)) {
                echo "<p class='ok'>✅ 'payments' table exists.</p>";
                $cols = $pdo2->query("DESCRIBE payments")->fetchAll();
                echo "<pre>";
                foreach ($cols as $col) {
                    echo htmlspecialchars($col['Field']) . " — " . htmlspecialchars($col['Type']) . "\n";
                }
                echo "</pre>";
            } else {
                echo "<p class='fail'>❌ 'payments' table does NOT exist. Run db_setup.php to create it.</p>";
            }

        } else {
            echo "<p class='fail'>❌ Database '" . htmlspecialchars(DB_NAME) . "' does NOT exist.</p>";
            echo "<p>→ You need to either:<br>
                  &nbsp;&nbsp;• Create it: <code>CREATE DATABASE " . htmlspecialchars(DB_NAME) . ";</code><br>
                  &nbsp;&nbsp;• Or visit <a href='db_setup.php' style='color:#ffb347'>db_setup.php</a> to auto-create it.<br>
                  &nbsp;&nbsp;• Or change DB_NAME in config/db_config.php to an existing database.</p>";
        }

    } catch (PDOException $e) {
        echo "<p class='fail'>❌ Connection FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>Error Code: " . $e->getCode() . "\n\nThis usually means:\n";
        switch ($e->getCode()) {
            case 1045: echo "→ Wrong username or password. Update DB_USER and DB_PASS in config/db_config.php"; break;
            case 2002: echo "→ Cannot connect to MySQL host '" . DB_HOST . "'. Try '127.0.0.1' instead of 'localhost'."; break;
            case 1049: echo "→ Database '" . DB_NAME . "' does not exist. Create it or visit db_setup.php."; break;
            default:   echo "→ Check your hosting control panel for the correct MySQL host, username, and password.";
        }
        echo "</pre>";
    }
}

/* ── 4. Action checklist ─────────────────────────────────── */
echo "<h3 class='info'>4. What To Do Next</h3>";
echo "<pre>
If Step A failed:  Update DB_HOST, DB_USER, DB_PASS in config/db_config.php
                   (Get these from your hosting cPanel → MySQL Databases)

If Step B shows no matching DB:
                   Visit /api/db_setup.php to auto-create the database &amp; table
                   — OR — create the database manually in cPanel first,
                   then update DB_NAME in config/db_config.php

If Step C/D failed: Visit /api/db_setup.php to create the payments table
</pre>";

echo "<p style='color:#888;margin-top:30px;font-size:12px;'>⚠️ Delete this file (db_test.php) from the server once your issue is resolved.</p>";
?>
</body>
</html>
