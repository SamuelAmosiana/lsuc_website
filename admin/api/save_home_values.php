<?php
session_start();

// Authenticate administrator
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$draft_file = __DIR__ . '/../data/home_values_draft.json';
$published_file = __DIR__ . '/../data/home_values.json';

// Load values from draft first, then published, then default empty
$values = [];
if (file_exists($draft_file)) {
    $values = json_decode(file_get_contents($draft_file), true) ?: [];
} elseif (file_exists($published_file)) {
    $values = json_decode(file_get_contents($published_file), true) ?: [];
}

try {
    // Validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    $result = ['success' => false];
    
    switch ($action) {
        case 'add':
            $result = addValue($values);
            if ($result['success']) {
                file_put_contents($draft_file, json_encode($values, JSON_PRETTY_PRINT));
                logActivity('Saved Core Value Draft', 'Added Core Value: ' . $_POST['title']);
            }
            break;
            
        case 'update':
            $result = updateValue($values);
            if ($result['success']) {
                file_put_contents($draft_file, json_encode($values, JSON_PRETTY_PRINT));
                logActivity('Saved Core Value Draft', 'Updated Core Value ID: ' . $_POST['id']);
            }
            break;
            
        case 'delete':
            $result = deleteValue($values);
            if ($result['success']) {
                file_put_contents($draft_file, json_encode($values, JSON_PRETTY_PRINT));
                logActivity('Deleted Core Value Draft', 'Deleted Core Value ID: ' . $_POST['id']);
            }
            break;
            
        case 'publish':
            // Publish draft to live
            if (file_exists($draft_file)) {
                $draft_content = file_get_contents($draft_file);
                file_put_contents($published_file, $draft_content);
            } else {
                // If draft doesn't exist, serialize current values to published file
                file_put_contents($published_file, json_encode($values, JSON_PRETTY_PRINT));
            }
            logActivity('Published Core Values', 'Homepage core values published');
            $result = ['success' => true, 'message' => 'Core values published live successfully!'];
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    if ($result['success']) {
        echo json_encode([
            'success' => true, 
            'message' => $result['message'] ?? 'Core value operation completed successfully.'
        ]);
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
    return ['success' => true, 'id' => $value['id'], 'message' => 'Core value draft added successfully.'];
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
            return ['success' => true, 'id' => $value['id'], 'message' => 'Core value draft updated successfully.'];
        }
    }
    
    return ['success' => false, 'error' => 'Value not found'];
}

function deleteValue(&$values) {
    if (empty($_POST['id'])) {
        return ['success' => false, 'error' => 'ID is required'];
    }
    
    $initial_count = count($values);
    $values = array_values(array_filter($values, function($v) {
        return $v['id'] !== $_POST['id'];
    }));
    
    if (count($values) < $initial_count) {
        // Re-order remaining elements
        foreach ($values as $index => &$value) {
            $value['order'] = $index;
        }
        return ['success' => true, 'id' => $_POST['id'], 'message' => 'Core value draft deleted successfully.'];
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
