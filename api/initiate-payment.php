<?php
// Ensure we're running on a server that supports the necessary functions
if (!function_exists('curl_init')) {
    die('cURL is not available on this server.');
}

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database configuration
require_once __DIR__ . '/../config/db_config.php';

/**
 * Create database table if it doesn't exist
 */
function createPaymentsTable() {
    try {
        $pdo = getDbConnection();
        if (!$pdo) {
            error_log("Failed to get database connection for table creation");
            return false;
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_reference VARCHAR(255) UNIQUE NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'successful', 'failed') DEFAULT 'pending',
            provider VARCHAR(50) DEFAULT 'MobileMoney',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        return true;
    } catch(PDOException $e) {
        error_log("Database table creation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate phone number format
 */
function validatePhoneNumber($phone) {
    // For Zambian mobile money, typical format is 10 digits starting with 09 or 07
    $pattern = '/^0[7|9]\d{8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Validate amount
 */
function validateAmount($amount) {
    return is_numeric($amount) && $amount > 0 && strlen($amount) <= 10;
}

/**
 * Generate unique transaction reference
 */
function generateTransactionReference() {
    return 'LSUC_' . date('Ymd') . '_' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
}

/**
 * Insert payment record into database
 */
function insertPaymentRecord($phone, $amount, $reference) {
    try {
        $pdo = getDbConnection();
        if (!$pdo) {
            error_log("Failed to get database connection for payment insertion");
            return false;
        }
        
        $stmt = $pdo->prepare("INSERT INTO payments (transaction_reference, phone_number, amount, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$reference, $phone, $amount, 'pending']);
        
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        error_log("Payment insertion error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update payment status
 */
function updatePaymentStatus($reference, $status) {
    try {
        $pdo = getDbConnection();
        if (!$pdo) {
            error_log("Failed to get database connection for payment status update");
            return false;
        }
        
        $stmt = $pdo->prepare("UPDATE payments SET status = ? WHERE transaction_reference = ?");
        $result = $stmt->execute([$status, $reference]);
        
        return $result;
    } catch(PDOException $e) {
        error_log("Payment status update error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if transaction reference already exists
 */
function transactionExists($reference) {
    try {
        $pdo = getDbConnection();
        if (!$pdo) {
            error_log("Failed to get database connection for transaction existence check");
            return false;
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE transaction_reference = ?");
        $stmt->execute([$reference]);
        
        return $stmt->fetchColumn() > 0;
    } catch(PDOException $e) {
        error_log("Transaction existence check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Simulate payment initiation to mobile money provider
 * Note: This is a simulation as actual mobile money API integration requires official credentials
 */
function initiateMobileMoneyPayment($phone, $amount, $reference) {
    // In a real implementation, this would make an API call to the mobile money provider
    // For now, we'll simulate the process and return a success response
    
    // Since we can't make actual API calls without credentials, we'll simulate
    // the process and assume the payment request was sent successfully
    return ['success' => true, 'transaction_id' => $reference];
}

// Create payments table if needed
createPaymentsTable();

// Handle the payment initiation request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $phone = trim($input['phone'] ?? '');
    $amount = trim($input['amount'] ?? '');
    $reference = generateTransactionReference();
    
    // Validate inputs
    if (empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Phone number is required']);
        exit;
    }
    
    if (!validatePhoneNumber($phone)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number format. Use format: 09xxxxxxx or 07xxxxxxx']);
        exit;
    }
    
    if (empty($amount)) {
        echo json_encode(['success' => false, 'message' => 'Amount is required']);
        exit;
    }
    
    if (!validateAmount($amount)) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount. Amount must be a positive number']);
        exit;
    }
    
    // Check if transaction reference already exists (prevent duplicates)
    if (transactionExists($reference)) {
        echo json_encode(['success' => false, 'message' => 'Transaction reference already exists']);
        exit;
    }
    
    // Insert payment record
    $paymentId = insertPaymentRecord($phone, $amount, $reference);
    if (!$paymentId) {
        echo json_encode(['success' => false, 'message' => 'Failed to create payment record']);
        exit;
    }
    
    // Attempt to initiate mobile money payment
    $result = initiateMobileMoneyPayment($phone, $amount, $reference);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Payment request initiated successfully. Check your phone for the payment prompt.',
            'transaction_reference' => $reference,
            'payment_id' => $paymentId
        ]);
    } else {
        // Update status to failed if payment initiation failed
        updatePaymentStatus($reference, 'failed');
        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to initiate payment request'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>