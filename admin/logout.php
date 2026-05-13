<?php
session_start();

// Log logout activity
if (isset($_SESSION['admin_username'])) {
    $username = $_SESSION['admin_username'];
    
    $log_file = __DIR__ . '/data/activity_log.json';
    $logs = [];
    
    if (file_exists($log_file)) {
        $logs = json_decode(file_get_contents($log_file), true) ?: [];
    }
    
    $logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'username' => $username,
        'action' => 'Logout',
        'status' => 'success',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}

// Destroy session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['lsuc_admin_remember'])) {
    setcookie('lsuc_admin_remember', '', time() - 3600, '/');
}

// Redirect to login
header('Location: index.php');
exit;
?>
