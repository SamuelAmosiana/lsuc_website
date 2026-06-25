<?php
/**
 * Swish Payment Callback Handler — LSUC Website
 * ============================================================
 * Netone posts to this URL after a payment succeeds or fails.
 * Register this URL in the Netone Swish portal:
 *   https://srms.lsc.edu.zm/api/swish_callback.php  ← confirmed in portal
 *
 * Expected payload:
 * {
 *   "txnLinkID":        "20012022135821",
 *   "transid":          "5089143",
 *   "amount":           "700",
 *   "status":           "success" | "failed",
 *   "transactionDate":  "28/09/2021 12:33:07",
 *   "subscriberNumber": "260978905095"
 * }
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/swish_config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Accept JSON body or form POST or GET redirect
$raw_post = file_get_contents('php://input');
$data     = json_decode($raw_post, true);
if (!$data) {
    $data = !empty($_POST) ? $_POST : $_GET;
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'No callback data received']);
    exit;
}

$txn_link_id  = $data['txnLinkID']        ?? $data['transLinkID'] ?? '';
$status       = strtolower($data['status'] ?? '');
$amount       = $data['amount']            ?? 0;
$transid      = $data['transid']           ?? '';
$txn_date     = $data['transactionDate']   ?? '';
$subscriber   = $data['subscriberNumber']  ?? '';

if (empty($txn_link_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'txnLinkID is required']);
    exit;
}

$pdo = getDbConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Find payment by swish_trans_link_id
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE swish_trans_link_id = ? LIMIT 1");
    $stmt->execute([$txn_link_id]);
    $payment = $stmt->fetch();

    // Fallback: match by transaction_reference
    if (!$payment) {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_reference = ? LIMIT 1");
        $stmt->execute([$txn_link_id]);
        $payment = $stmt->fetch();
    }

    if (!$payment) {
        logCb($pdo, $txn_link_id, json_encode($data), 'no_match');
        http_response_code(404);
        echo json_encode(['error' => 'Payment record not found for txnLinkID: ' . $txn_link_id]);
        exit;
    }

    $internal_ref = $payment['transaction_reference'];
    logCb($pdo, $internal_ref, json_encode($data), 'callback_received');

    if ($status === 'success') {
        $pdo->prepare(
            "UPDATE payments SET status = 'confirmed', updated_at = NOW() WHERE transaction_reference = ?"
        )->execute([$internal_ref]);

        logCb($pdo, $internal_ref, json_encode($data), 'confirmed');
        echo json_encode([
            'success'               => true,
            'transaction_reference' => $internal_ref,
            'message'               => 'Payment confirmed',
            'status'                => 'confirmed',
        ]);

    } elseif (in_array($status, ['failed', 'cancelled', 'rejected'])) {
        $pdo->prepare(
            "UPDATE payments SET status = 'rejected', updated_at = NOW() WHERE transaction_reference = ?"
        )->execute([$internal_ref]);

        logCb($pdo, $internal_ref, json_encode($data), 'rejected');
        echo json_encode([
            'success'               => true,
            'transaction_reference' => $internal_ref,
            'message'               => 'Payment rejected',
            'status'                => 'rejected',
        ]);

    } else {
        logCb($pdo, $internal_ref, json_encode($data), 'unknown_status');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown status: ' . $status]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function logCb(PDO $pdo, string $ref, string $raw, string $action): void
{
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS payment_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_reference VARCHAR(100),
            raw_response TEXT,
            action_taken VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $pdo->prepare(
            "INSERT INTO payment_logs (transaction_reference, raw_response, action_taken) VALUES (?, ?, ?)"
        )->execute([$ref, $raw, $action]);
    } catch (Exception $e) { /* non-fatal */ }
}
