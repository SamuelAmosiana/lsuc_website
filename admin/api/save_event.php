<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$events_file = __DIR__ . '/../data/events.json';

// Load existing events
$events = [];
if (file_exists($events_file)) {
    $events = json_decode(file_get_contents($events_file), true) ?: [];
}

try {
    switch ($action) {
        case 'add':
            $result = addEvent($events);
            break;
            
        case 'edit':
            $result = editEvent($events);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    if ($result['success']) {
        // Log activity
        logActivity($action === 'add' ? 'Added new event' : 'Updated event', 
                   'Event ID: ' . $result['id']);
        
        echo json_encode([
            'success' => true,
            'message' => $action === 'add' ? 'Event created successfully' : 'Event updated successfully',
            'id' => $result['id']
        ]);
    } else {
        throw new Exception($result['error'] ?? 'Operation failed');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function addEvent(&$events) {
    // Validate required fields
    $required = ['title', 'date', 'category', 'short_description', 'full_description'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'error' => "Field '{$field}' is required"];
        }
    }
    
    // Generate unique ID
    $id = 'evt_' . time() . '_' . bin2hex(random_bytes(4));
    
    // Handle image upload
    $image_data = $_POST['image_base64'] ?? '';
    
    // Create event object
    $event = [
        'id' => $id,
        'title' => sanitize($_POST['title']),
        'date' => $_POST['date'],
        'category' => $_POST['category'],
        'shortDescription' => sanitize($_POST['short_description']),
        'fullDescription' => $_POST['full_description'], // Keep HTML formatting
        'author' => sanitize($_POST['author'] ?? 'Admin'),
        'featured' => isset($_POST['featured']),
        'image' => $image_data,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add to events array
    $events[] = $event;
    
    // Save to file
    saveEvents($events);
    
    return ['success' => true, 'id' => $id];
}

function editEvent(&$events) {
    // Validate required fields
    $required = ['id', 'title', 'date', 'category', 'short_description', 'full_description'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'error' => "Field '{$field}' is required"];
        }
    }
    
    $event_id = $_POST['id'];
    
    // Find event index
    $event_index = -1;
    foreach ($events as $index => $event) {
        if ($event['id'] === $event_id) {
            $event_index = $index;
            break;
        }
    }
    
    if ($event_index === -1) {
        return ['success' => false, 'error' => 'Event not found'];
    }
    
    // Handle image upload
    $new_image = $_POST['image_base64'] ?? '';
    $remove_current = $_POST['remove_current_image'] ?? '0';
    
    // Update event data
    $events[$event_index]['title'] = sanitize($_POST['title']);
    $events[$event_index]['date'] = $_POST['date'];
    $events[$event_index]['category'] = $_POST['category'];
    $events[$event_index]['shortDescription'] = sanitize($_POST['short_description']);
    $events[$event_index]['fullDescription'] = $_POST['full_description'];
    $events[$event_index]['author'] = sanitize($_POST['author'] ?? 'Admin');
    $events[$event_index]['featured'] = isset($_POST['featured']);
    $events[$event_index]['updated_at'] = date('Y-m-d H:i:s');
    
    // Handle image update
    if ($remove_current === '1') {
        $events[$event_index]['image'] = '';
    } elseif (!empty($new_image)) {
        $events[$event_index]['image'] = $new_image;
    }
    
    // Save to file
    saveEvents($events);
    
    return ['success' => true, 'id' => $event_id];
}

function saveEvents(&$events) {
    $events_file = __DIR__ . '/../data/events.json';
    file_put_contents($events_file, json_encode($events, JSON_PRETTY_PRINT));
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
    
    // Keep last 1000 logs
    $logs = array_slice($logs, -1000);
    
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}
?>
