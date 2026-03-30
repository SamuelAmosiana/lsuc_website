<?php
// Delete FAQ Handler
$faq_id = $_GET['id'] ?? '';

if (!$faq_id) {
    header('Location: ?page=faqs');
    exit;
}

$faqs_file = __DIR__ . '/../data/faqs.json';
$faqs = [];

if (file_exists($faqs_file)) {
    $faqs = json_decode(file_get_contents($faqs_file), true) ?: [];
}

$faq_found = false;
$updated_faqs = array_filter($faqs, function($faq) use ($faq_id, &$faq_found) {
    if ($faq['id'] === $faq_id) {
        $faq_found = true;
        return false;
    }
    return true;
});

if ($faq_found) {
    file_put_contents($faqs_file, json_encode(array_values($updated_faqs), JSON_PRETTY_PRINT));
    logActivity('Deleted FAQ', 'FAQ ID: ' . $faq_id);
    header('Location: ?page=faqs&success=FAQ+deleted+successfully');
} else {
    header('Location: ?page=faqs&error=FAQ+not+found');
}
exit;

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
