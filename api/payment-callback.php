<?php
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
 * Update payment status in database
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
 * Get payment details
 */
function getPaymentByReference($reference) {
    try {
        $pdo = getDbConnection();
        if (!$pdo) {
            error_log("Failed to get database connection for payment retrieval");
            return false;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_reference = ?");
        $stmt->execute([$reference]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Payment retrieval error: " . $e->getMessage());
        return false;
    }
}

// Handle payment callback from mobile money provider
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // In a real implementation, the mobile money provider would send specific data
    // This is a simplified version - actual implementation depends on the provider's API
    
    // Extract transaction reference from the callback data
    $transactionReference = $input['transaction_reference'] ?? $input['reference'] ?? '';
    
    if (empty($transactionReference)) {
        echo json_encode(['success' => false, 'message' => 'Missing transaction reference']);
        exit;
    }
    
    // Verify the payment with the mobile money provider
    // This is a simplified verification - real implementation would verify with the provider's API
    $verificationResult = verifyPaymentWithProvider($transactionReference);
    
    if ($verificationResult['verified']) {
        // Update payment status to successful
        $updateResult = updatePaymentStatus($transactionReference, 'successful');
        
        if ($updateResult) {
            // Log successful payment
            error_log("Payment verified and updated: " . $transactionReference);
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment confirmed successfully',
                'transaction_reference' => $transactionReference
            ]);
        } else {
            error_log("Failed to update payment status: " . $transactionReference);
            echo json_encode(['success' => false, 'message' => 'Failed to update payment status']);
        }
    } else {
        // Update payment status to failed
        $updateResult = updatePaymentStatus($transactionReference, 'failed');
        
        if ($updateResult) {
            error_log("Payment verification failed: " . $transactionReference);
            
            echo json_encode([
                'success' => true, // Still return success to acknowledge receipt of callback
                'message' => 'Payment verification failed',
                'transaction_reference' => $transactionReference
            ]);
        } else {
            error_log("Failed to update payment status to failed: " . $transactionReference);
            echo json_encode(['success' => false, 'message' => 'Failed to update payment status']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // For testing purposes, allow GET requests to check payment status
    $transactionReference = $_GET['ref'] ?? '';
    
    if (!empty($transactionReference)) {
        $payment = getPaymentByReference($transactionReference);
        
        if ($payment) {
            echo json_encode([
                'success' => true,
                'payment' => $payment
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Payment not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Transaction reference required'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

/**
 * Verify payment with mobile money provider
 * Note: This is a simulation as actual verification requires official credentials
 */
function verifyPaymentWithProvider($transactionReference) {
    // In a real implementation, this would make an API call to the mobile money provider
    // to verify the transaction status
    
    // For simulation purposes, we'll assume the payment is verified
    // In reality, you would call the provider's API to verify the transaction
    return ['verified' => true, 'details' => null];
}
?>