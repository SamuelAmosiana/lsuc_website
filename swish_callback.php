<?php
/**
 * Swish Payment Callback — ROOT LEVEL
 * ============================================================
 * This file lives at the website root (NOT inside /api/) to avoid
 * the SRMS application intercepting /api/ POST requests.
 *
 * Register this URL in the Netone Swish portal:
 *   https://srms.lsc.edu.zm/swish_callback.php
 *                          ↑ root level, not /api/
 *
 * Netone posts here after every payment attempt (success or fail).
 */

// Include from the config directory
require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/config/swish_config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Log ALL incoming data for debugging
$raw_post = file_get_contents('php://input');
error_log("SwishCallback RAW: " . $raw_post);
error_log("SwishCallback POST: " . json_encode($_POST));
error_log("SwishCallback GET: "  . json_encode($_GET));

// Accept JSON body, form POST, or GET params
$data = json_decode($raw_post, true);
if (!$data) {
    $data = !empty($_POST) ? $_POST : $_GET;
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'No callback data received']);
    exit;
}

// Extract fields — Swish uses different field names in different versions
$txn_link_id = $data['txnLinkID']        // v1 field name
            ?? $data['transLinkID']       // v2 field name
            ?? $data['transactionId']     // v3 field name
            ?? $data['TxnLinkID']         // capitalised variant
            ?? '';

$status    = strtolower(trim($data['status'] ?? $data['Status'] ?? ''));
$amount    = $data['amount']           ?? $data['Amount']          ?? 0;
$transid   = $data['transid']          ?? $data['transactionId']   ?? '';
$txn_date  = $data['transactionDate']  ?? $data['TransactionDate'] ?? '';
$subscriber= $data['subscriberNumber'] ?? $data['phone']           ?? '';

error_log("SwishCallback parsed: txnLinkID={$txn_link_id} status={$status} amount={$amount}");

if (empty($txn_link_id) && empty($transid)) {
    // Accept even without txnLinkID — log and return OK so Swish doesn't retry endlessly
    error_log("SwishCallback: no txnLinkID or transid in payload — logging and returning 200");
    echo json_encode(['success' => true, 'message' => 'Acknowledged (no txnLinkID)']);
    exit;
}

$pdo = getDbConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Try to find by swish_trans_link_id first
    $payment = null;
    if ($txn_link_id) {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE swish_trans_link_id = ? LIMIT 1");
        $stmt->execute([$txn_link_id]);
        $payment = $stmt->fetch();
    }

    // Fallback: match by transid
    if (!$payment && $transid) {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE swish_trans_link_id = ? LIMIT 1");
        $stmt->execute([$transid]);
        $payment = $stmt->fetch();
    }

    // Log the callback regardless
    logCallback($pdo, $txn_link_id ?: $transid, json_encode($data), 'callback_received');

    if (!$payment) {
        // Return 200 anyway so Swish doesn't keep retrying
        logCallback($pdo, $txn_link_id ?: $transid, json_encode($data), 'no_match');
        echo json_encode(['success' => true, 'message' => 'Acknowledged (no matching record)']);
        exit;
    }

    $internal_ref = $payment['transaction_reference'];

    // Normalise status — "COMPLETED" / "completed" / "success" all mean paid
    $success_values = ['completed', 'success', 'successful', 'paid', 'approved'];
    $failed_values  = ['failed', 'cancelled', 'rejected', 'declined', 'error'];

    if (in_array($status, $success_values)) {
        $pdo->prepare("UPDATE payments SET status = 'confirmed', updated_at = NOW() WHERE transaction_reference = ?")
            ->execute([$internal_ref]);
        logCallback($pdo, $internal_ref, json_encode($data), 'confirmed');

        echo json_encode([
            'success'               => true,
            'transaction_reference' => $internal_ref,
            'message'               => 'Payment confirmed',
            'status'                => 'confirmed',
        ]);

    } elseif (in_array($status, $failed_values)) {
        $pdo->prepare("UPDATE payments SET status = 'rejected', updated_at = NOW() WHERE transaction_reference = ?")
            ->execute([$internal_ref]);
        logCallback($pdo, $internal_ref, json_encode($data), 'rejected');

        echo json_encode([
            'success'               => true,
            'transaction_reference' => $internal_ref,
            'message'               => 'Payment rejected',
            'status'                => 'rejected',
        ]);

    } else {
        logCallback($pdo, $internal_ref, json_encode($data), 'unknown_status_' . $status);
        echo json_encode(['success' => true, 'message' => 'Acknowledged (status: ' . $status . ')']);
    }

} catch (Exception $e) {
    error_log("SwishCallback exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function logCallback(PDO $pdo, string $ref, string $raw, string $action): void
{
    try {
        $pdo->prepare(
            "INSERT INTO payment_logs (transaction_reference, raw_response, action_taken) VALUES (?, ?, ?)"
        )->execute([$ref, $raw, $action]);
    } catch (Exception $e) { /* non-fatal */ }
}
