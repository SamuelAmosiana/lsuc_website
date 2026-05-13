<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$order = $input['order'] ?? [];

if (empty($order)) {
    echo json_encode(['success' => false, 'error' => 'No order data provided']);
    exit;
}

$faqs_file = __DIR__ . '/../data/faqs.json';
$faqs = [];

if (file_exists($faqs_file)) {
    $faqs = json_decode(file_get_contents($faqs_file), true) ?: [];
}

// Update order based on new arrangement
foreach ($order as $index => $faq_id) {
    foreach ($faqs as &$faq) {
        if ($faq['id'] === $faq_id) {
            $faq['order'] = $index;
            break;
        }
    }
}

file_put_contents($faqs_file, json_encode($faqs, JSON_PRETTY_PRINT));

logActivity('Reordered FAQs', 'Updated FAQ display order');

echo json_encode(['success' => true]);

function logActivity($action, $details = '') {
    $log_file = __DIR__ . '/../data/activity_log.json';
    $logs = [];
    if (file_exists($log_file)) {
        $logs = json_decode(file_get_contents($log_file), true) ?: [];
    }
    $logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'username' => $_SESSION['admin_username'] ?? 'Unknown',
        'action' => $action,
        'details' => $details,
        'status' => 'success',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    $logs = array_slice($logs, -1000);
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}
?>
