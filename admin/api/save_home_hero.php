<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$hero_file = __DIR__ . '/../data/home_hero.json';

try {
    if ($action === 'save') {
        $required = ['heading', 'motto'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        $hero_data = [
            'heading' => sanitize($_POST['heading']),
            'motto' => sanitize($_POST['motto']),
            'description' => sanitize($_POST['description'] ?? ''),
            'background_image' => sanitize($_POST['background_image'] ?? ''),
            'cta_text' => sanitize($_POST['cta_text'] ?? 'Apply Now'),
            'cta_link' => sanitize($_POST['cta_link'] ?? '#apply'),
            'show_cta' => isset($_POST['show_cta']) && $_POST['show_cta'] === '1',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($hero_file, json_encode($hero_data, JSON_PRETTY_PRINT));
        logActivity('Updated Hero Section', 'Homepage hero section modified');
        
        echo json_encode(['success' => true, 'message' => 'Hero section saved successfully']);
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

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
