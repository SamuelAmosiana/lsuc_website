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
$draft_file = __DIR__ . '/../data/home_gallery_draft.json';
$published_file = __DIR__ . '/../data/home_gallery.json';

// Load images from draft first, then published, then default empty
$images = [];
if (file_exists($draft_file)) {
    $images = json_decode(file_get_contents($draft_file), true) ?: [];
} elseif (file_exists($published_file)) {
    $images = json_decode(file_get_contents($published_file), true) ?: [];
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
            $result = addImage($images);
            if ($result['success']) {
                file_put_contents($draft_file, json_encode($images, JSON_PRETTY_PRINT));
                logActivity('Saved Gallery Image Draft', 'Added image caption: ' . $_POST['caption']);
            }
            break;
            
        case 'update':
            $result = updateImage($images);
            if ($result['success']) {
                file_put_contents($draft_file, json_encode($images, JSON_PRETTY_PRINT));
                logActivity('Saved Gallery Image Draft', 'Updated image ID: ' . $_POST['id']);
            }
            break;
            
        case 'delete':
            $result = deleteImage($images);
            if ($result['success']) {
                file_put_contents($draft_file, json_encode($images, JSON_PRETTY_PRINT));
                logActivity('Deleted Gallery Image Draft', 'Deleted image ID: ' . $_POST['id']);
            }
            break;
            
        case 'publish':
            // Publish draft to live
            if (file_exists($draft_file)) {
                $draft_content = file_get_contents($draft_file);
                file_put_contents($published_file, $draft_content);
            } else {
                // If draft doesn't exist, serialize current values to published file
                file_put_contents($published_file, json_encode($images, JSON_PRETTY_PRINT));
            }
            logActivity('Published Campus Gallery', 'Homepage campus gallery published');
            $result = ['success' => true, 'message' => 'Campus gallery published live successfully!'];
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    if ($result['success']) {
        echo json_encode([
            'success' => true, 
            'message' => $result['message'] ?? 'Gallery operation completed successfully.'
        ]);
    } else {
        throw new Exception($result['error'] ?? 'Operation failed');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function handleFileUpload() {
    if (isset($_FILES['gallery_image_file']) && $_FILES['gallery_image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['gallery_image_file'];
        
        // Validate MIME type
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WEBP images are allowed.');
        }
        
        // Validate size (max 5MB)
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            throw new Exception('Image size exceeds 5MB limit.');
        }
        
        // Generate secure unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        
        // Ensure uploads directory exists
        $upload_dir = __DIR__ . '/../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Move file
        $dest_path = $upload_dir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
            throw new Exception('Failed to save uploaded image.');
        }
        
        return './admin/uploads/' . $filename;
    }
    return null;
}

function addImage(&$images) {
    $caption = $_POST['caption'] ?? '';
    if (empty($caption)) {
        return ['success' => false, 'error' => 'Caption/Title is required'];
    }
    
    // File upload takes precedence over text path
    $path = handleFileUpload();
    if (empty($path)) {
        $path = $_POST['path'] ?? '';
    }
    
    if (empty($path)) {
        return ['success' => false, 'error' => 'An image path is required or you must upload a file'];
    }
    
    $image = [
        'id' => 'img_' . time() . '_' . bin2hex(random_bytes(4)),
        'path' => sanitize($path),
        'caption' => sanitize($caption),
        'description' => sanitize($_POST['description'] ?? ''),
        'order' => count($images),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $images[] = $image;
    return ['success' => true, 'id' => $image['id'], 'message' => 'Image draft added successfully.'];
}

function updateImage(&$images) {
    $id = $_POST['id'] ?? '';
    $caption = $_POST['caption'] ?? '';
    if (empty($id) || empty($caption)) {
        return ['success' => false, 'error' => 'ID and caption are required'];
    }
    
    // Check if new file is uploaded, otherwise keep existing path
    $path = handleFileUpload();
    if (empty($path)) {
        $path = $_POST['path'] ?? '';
    }
    
    if (empty($path)) {
        return ['success' => false, 'error' => 'An image path or uploaded file is required'];
    }
    
    foreach ($images as &$image) {
        if ($image['id'] === $id) {
            $image['path'] = sanitize($path);
            $image['caption'] = sanitize($caption);
            $image['description'] = sanitize($_POST['description'] ?? '');
            $image['updated_at'] = date('Y-m-d H:i:s');
            return ['success' => true, 'id' => $image['id'], 'message' => 'Image draft updated successfully.'];
        }
    }
    
    return ['success' => false, 'error' => 'Image not found'];
}

function deleteImage(&$images) {
    $id = $_POST['id'] ?? '';
    if (empty($id)) {
        return ['success' => false, 'error' => 'ID is required'];
    }
    
    $initial_count = count($images);
    $images = array_values(array_filter($images, function($i) use ($id) {
        return $i['id'] !== $id;
    }));
    
    if (count($images) < $initial_count) {
        // Re-order remaining elements
        foreach ($images as $index => &$image) {
            $image['order'] = $index;
        }
        return ['success' => true, 'id' => $id, 'message' => 'Image draft deleted successfully.'];
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
