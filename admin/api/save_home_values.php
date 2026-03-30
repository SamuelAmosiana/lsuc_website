<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$values_file = __DIR__ . '/../data/home_values.json';

$values = [];
if (file_exists($values_file)) {
    $values = json_decode(file_get_contents($values_file), true) ?: [];
}

try {
    switch ($action) {
        case 'add':
            $result = addValue($values);
            break;
        case 'update':
            $result = updateValue($values);
            break;
        case 'delete':
            $result = deleteValue($values);
            break;
        default:
            throw new Exception('Invalid action');
    }
    
    if ($result['success']) {
        file_put_contents($values_file, json_encode($values, JSON_PRETTY_PRINT));
        logActivity(ucfirst($action) . ' Core Value', 'Value ID: ' . ($result['id'] ?? 'Unknown'));
        echo json_encode(['success' => true, 'message' => 'Core value ' . ($action === 'add' ? 'added' : ($action === 'update' ? 'updated' : 'deleted')) . ' successfully']);
    } else {
        throw new Exception($result['error'] ?? 'Operation failed');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function addValue(&$values) {
    if (empty($_POST['title']) || empty($_POST['description'])) {
        return ['success' => false, 'error' => 'Title and description are required'];
    }
    
    $value = [
        'id' => 'val_' . time() . '_' . bin2hex(random_bytes(4)),
        'title' => sanitize($_POST['title']),
        'description' => sanitize($_POST['description']),
        'icon' => sanitize($_POST['icon'] ?? 'fa-star'),
        'order' => count($values),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $values[] = $value;
    return ['success' => true, 'id' => $value['id']];
}

function updateValue(&$values) {
    if (empty($_POST['id']) || empty($_POST['title']) || empty($_POST['description'])) {
        return ['success' => false, 'error' => 'ID, title and description are required'];
    }
    
    foreach ($values as &$value) {
        if ($value['id'] === $_POST['id']) {
            $value['title'] = sanitize($_POST['title']);
            $value['description'] = sanitize($_POST['description']);
            $value['icon'] = sanitize($_POST['icon'] ?? 'fa-star');
            $value['updated_at'] = date('Y-m-d H:i:s');
            return ['success' => true, 'id' => $value['id']];
        }
    }
    
    return ['success' => false, 'error' => 'Value not found'];
}

function deleteValue(&$values) {
    if (empty($_POST['id'])) {
        return ['success' => false, 'error' => 'ID is required'];
    }
    
    $initial_count = count($values);
    $values = array_filter($values, function($v) {
        return $v['id'] !== $_POST['id'];
    });
    
    if (count($values) < $initial_count) {
        return ['success' => true, 'id' => $_POST['id']];
    }
    
    return ['success' => false, 'error' => 'Value not found'];
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
