<?php
/**
 * Swish Transaction Status Poller — LSUC Website
 * ============================================================
 * Called by the modal every 15 seconds to check if a USSD /
 * Push / QR payment has been confirmed by Swish.
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/swish_config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$internal_ref = trim($_GET['internal_ref'] ?? '');
$phone        = trim($_GET['phone']        ?? '');

if (empty($internal_ref)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'internal_ref is required']);
    exit;
}

$pdo = getDbConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// ── Fetch DB record ───────────────────────────────────────────
try {
    $stmt = $pdo->prepare(
        "SELECT swish_trans_link_id, status FROM payments WHERE transaction_reference = ? LIMIT 1"
    );
    $stmt->execute([$internal_ref]);
    $row = $stmt->fetch();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $e->getMessage()]);
    exit;
}

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Payment record not found']);
    exit;
}

// Already confirmed by callback? Return immediately
if ($row['status'] === 'confirmed') {
    echo json_encode(['success' => true, 'payment_status' => 'success', 'source' => 'db_cache']);
    exit;
}

$trans_link_id = $row['swish_trans_link_id'] ?? '';
if (empty($trans_link_id)) {
    echo json_encode(['success' => true, 'payment_status' => 'pending', 'message' => 'Awaiting initiation']);
    exit;
}

// ── Call Swish Check Status API ───────────────────────────────
$url = 'https://swishandroid.swish.co.zm/v3/test/api/web/transactionStatus'
     . '?transactionId=' . urlencode($trans_link_id)
     . '&merchantCode=' . urlencode(SWISH_MERCHANT_CODE)
     . '&accountNo='    . urlencode($phone);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPGET        => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'accessKey: ' . SWISH_ACCESS_KEY,
        'password: '  . SWISH_SECURITY_KEY,
    ],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$raw      = curl_exec($ch);
$curl_err = curl_error($ch);
curl_close($ch);

if ($curl_err) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'cURL error: ' . $curl_err]);
    exit;
}

$decoded = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON from Swish']);
    exit;
}

// ── Interpret status ──────────────────────────────────────────
$swish_status = strtolower($decoded['data']['status'] ?? $decoded['message'] ?? '');

if ($swish_status === 'success') {
    try {
        $pdo->prepare("UPDATE payments SET status = 'confirmed', updated_at = NOW() WHERE transaction_reference = ?")
            ->execute([$internal_ref]);
    } catch (Exception $e) { /* non-fatal */ }

    echo json_encode(['success' => true, 'payment_status' => 'success', 'data' => $decoded['data'] ?? []]);

} elseif (in_array($swish_status, ['failed', 'cancelled', 'rejected'])) {
    try {
        $pdo->prepare("UPDATE payments SET status = 'rejected', updated_at = NOW() WHERE transaction_reference = ?")
            ->execute([$internal_ref]);
    } catch (Exception $e) { /* non-fatal */ }

    echo json_encode(['success' => true, 'payment_status' => 'failed', 'data' => $decoded['data'] ?? []]);

} else {
    echo json_encode(['success' => true, 'payment_status' => 'pending', 'data' => $decoded]);
}
