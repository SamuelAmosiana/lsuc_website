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
$publish_action = $_POST['publish_action'] ?? 'draft'; // 'draft' or 'publish'
$draft_file = __DIR__ . '/../data/home_hero_draft.json';
$published_file = __DIR__ . '/../data/home_hero.json';

try {
    // Validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    if ($action === 'save') {
        $required = ['heading', 'motto'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        $background_image = $_POST['background_image'] ?? '';
        
        // Handle file upload if present
        if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['hero_image_file'];
            
            // Validate MIME type
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_types)) {
                throw new Exception('Invalid file type. Only JPG, PNG, and WEBP images are allowed.');
            }
            
            // Validate file size (max 5MB)
            $max_size = 5 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                throw new Exception('Image size exceeds 5MB limit.');
            }
            
            // Generate safe unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'hero_bg_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            
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
            
            $background_image = './admin/uploads/' . $filename;
        }
        
        $hero_data = [
            'heading' => sanitize($_POST['heading']),
            'motto' => sanitize($_POST['motto']),
            'description' => sanitize($_POST['description'] ?? ''),
            'background_image' => sanitize($background_image),
            'cta_text' => sanitize($_POST['cta_text'] ?? 'Apply Now'),
            'cta_link' => sanitize($_POST['cta_link'] ?? '#apply'),
            'show_cta' => isset($_POST['show_cta']) && $_POST['show_cta'] === '1',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Save as draft
        file_put_contents($draft_file, json_encode($hero_data, JSON_PRETTY_PRINT));
        
        if ($publish_action === 'publish') {
            // Also copy to published
            file_put_contents($published_file, json_encode($hero_data, JSON_PRETTY_PRINT));
            logActivity('Published Hero Section', 'Homepage hero section published');
            $msg = 'Hero section published successfully!';
        } else {
            logActivity('Saved Hero Section Draft', 'Homepage hero section draft saved');
            $msg = 'Hero section draft saved successfully!';
        }
        
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function sanitize($input) {
    return strip_tags(trim(htmlspecialchars_decode($input, ENT_QUOTES)));
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
