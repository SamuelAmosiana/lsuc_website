<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$gallery_file = __DIR__ . '/../data/home_gallery.json';

$images = [];
if (file_exists($gallery_file)) {
    $images = json_decode(file_get_contents($gallery_file), true) ?: [];
}

try {
    switch ($action) {
        case 'add':
            $result = addImage($images);
            break;
        case 'update':
            $result = updateImage($images);
            break;
        case 'delete':
            $result = deleteImage($images);
            break;
        default:
            throw new Exception('Invalid action');
    }
    
    if ($result['success']) {
        file_put_contents($gallery_file, json_encode($images, JSON_PRETTY_PRINT));
        logActivity(ucfirst($action) . ' Gallery Image', 'Image ID: ' . ($result['id'] ?? 'Unknown'));
        echo json_encode(['success' => true, 'message' => 'Image ' . ($action === 'add' ? 'added' : ($action === 'update' ? 'updated' : 'deleted')) . ' successfully']);
    } else {
        throw new Exception($result['error'] ?? 'Operation failed');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function addImage(&$images) {
    if (empty($_POST['path']) || empty($_POST['caption'])) {
        return ['success' => false, 'error' => 'Image path and caption are required'];
    }
    
    $image = [
        'id' => 'img_' . time() . '_' . bin2hex(random_bytes(4)),
        'path' => sanitize($_POST['path']),
        'caption' => sanitize($_POST['caption']),
        'description' => sanitize($_POST['description'] ?? ''),
        'order' => count($images),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $images[] = $image;
    return ['success' => true, 'id' => $image['id']];
}

function updateImage(&$images) {
    if (empty($_POST['id']) || empty($_POST['path']) || empty($_POST['caption'])) {
        return ['success' => false, 'error' => 'ID, path and caption are required'];
    }
    
    foreach ($images as &$image) {
        if ($image['id'] === $_POST['id']) {
            $image['path'] = sanitize($_POST['path']);
            $image['caption'] = sanitize($_POST['caption']);
            $image['description'] = sanitize($_POST['description'] ?? '');
            $image['updated_at'] = date('Y-m-d H:i:s');
            return ['success' => true, 'id' => $image['id']];
        }
    }
    
    return ['success' => false, 'error' => 'Image not found'];
}

function deleteImage(&$images) {
    if (empty($_POST['id'])) {
        return ['success' => false, 'error' => 'ID is required'];
    }
    
    $initial_count = count($images);
    $images = array_filter($images, function($i) {
        return $i['id'] !== $_POST['id'];
    });
    
    if (count($images) < $initial_count) {
        return ['success' => true, 'id' => $_POST['id']];
    }
    
    return ['success' => false, 'error' => 'Image not found'];
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
