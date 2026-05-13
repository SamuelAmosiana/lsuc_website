<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$faqs_file = __DIR__ . '/../data/faqs.json';

$faqs = [];
if (file_exists($faqs_file)) {
    $faqs = json_decode(file_get_contents($faqs_file), true) ?: [];
}

try {
    switch ($action) {
        case 'add':
            $result = addFaq($faqs);
            break;
        case 'edit':
            $result = editFaq($faqs);
            break;
        default:
            throw new Exception('Invalid action');
    }
    
    if ($result['success']) {
        logActivity($action === 'add' ? 'Added new FAQ' : 'Updated FAQ', 'FAQ ID: ' . $result['id']);
        echo json_encode([
            'success' => true,
            'message' => $action === 'add' ? 'FAQ created successfully' : 'FAQ updated successfully',
            'id' => $result['id']
        ]);
    } else {
        throw new Exception($result['error'] ?? 'Operation failed');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function addFaq(&$faqs) {
    $required = ['question', 'answer'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'error' => "Field '{$field}' is required"];
        }
    }
    
    $id = 'faq_' . time() . '_' . bin2hex(random_bytes(4));
    
    $faq = [
        'id' => $id,
        'question' => sanitize($_POST['question']),
        'answer' => $_POST['answer'], // Keep HTML formatting
        'category' => sanitize($_POST['category'] ?? 'General'),
        'featured' => isset($_POST['featured']),
        'order' => count($faqs),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $faqs[] = $faq;
    saveFaqs($faqs);
    
    return ['success' => true, 'id' => $id];
}

function editFaq(&$faqs) {
    $required = ['id', 'question', 'answer'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'error' => "Field '{$field}' is required"];
        }
    }
    
    $faq_id = $_POST['id'];
    $faq_index = -1;
    
    foreach ($faqs as $index => $faq) {
        if ($faq['id'] === $faq_id) {
            $faq_index = $index;
            break;
        }
    }
    
    if ($faq_index === -1) {
        return ['success' => false, 'error' => 'FAQ not found'];
    }
    
    $faqs[$faq_index]['question'] = sanitize($_POST['question']);
    $faqs[$faq_index]['answer'] = $_POST['answer'];
    $faqs[$faq_index]['category'] = sanitize($_POST['category'] ?? 'General');
    $faqs[$faq_index]['featured'] = isset($_POST['featured']);
    $faqs[$faq_index]['updated_at'] = date('Y-m-d H:i:s');
    
    saveFaqs($faqs);
    
    return ['success' => true, 'id' => $faq_id];
}

function saveFaqs(&$faqs) {
    file_put_contents(__DIR__ . '/../data/faqs.json', json_encode($faqs, JSON_PRETTY_PRINT));
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
