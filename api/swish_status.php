<?php
/**
 * Swish Transaction Status Poller — LSUC Website
 * ============================================================
 * Called by the modal every 15 seconds to check if a USSD /
 * Push / QR payment has been confirmed by Swish.
 *
 * Fix log:
 *  v2 — Check multiple Swish response shapes:
 *       - top-level Status:"COMPLETED"
 *       - top-level status:"success"
 *       - nested data.status
 *       Also falls back to DB-only check (no transLinkId needed).
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/swish_config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

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
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . ($GLOBALS['DB_LAST_ERROR'] ?? 'unknown')]);
    exit;
}

// ── 1. Check DB first — fastest path ─────────────────────────────────────────
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

// Already marked confirmed by callback → return success immediately
if (in_array($row['status'], ['confirmed', 'success'])) {
    echo json_encode(['success' => true, 'payment_status' => 'success', 'source' => 'db_callback']);
    exit;
}

// Already marked rejected → return failed immediately
if (in_array($row['status'], ['rejected', 'failed', 'cancelled'])) {
    echo json_encode(['success' => true, 'payment_status' => 'failed', 'source' => 'db_callback']);
    exit;
}

// ── 2. Ask Swish API directly (callback may have been blocked by SRMS routing) ─
$trans_link_id = $row['swish_trans_link_id'] ?? '';

if (empty($trans_link_id)) {
    // No transLinkId yet — payment still initiating
    echo json_encode(['success' => true, 'payment_status' => 'pending', 'message' => 'Awaiting initiation']);
    exit;
}

// Build the status-check URL
$status_url = 'https://swishandroid.swish.co.zm/v3/test/api/web/transactionStatus'
    . '?transactionId=' . urlencode($trans_link_id)
    . '&merchantCode='  . urlencode(SWISH_MERCHANT_CODE)
    . '&accountNo='     . urlencode($phone);

$ch = curl_init($status_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPGET        => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'accessKey: ' . SWISH_ACCESS_KEY,
        'password: '  . SWISH_SECURITY_KEY,
    ],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => false,   // some VPS setups lack CA bundle
    CURLOPT_SSL_VERIFYHOST => 0,
]);
$raw      = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
curl_close($ch);

// Log the raw Swish response for debugging
error_log("SwishStatus [{$internal_ref}] HTTP:{$http_code} body:" . substr($raw, 0, 500));

if ($curl_err) {
    // cURL failed — fall back to "pending" and let polling continue
    echo json_encode(['success' => true, 'payment_status' => 'pending', 'debug' => 'curl_err: ' . $curl_err]);
    exit;
}

$decoded = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // Swish returned non-JSON — keep polling
    echo json_encode(['success' => true, 'payment_status' => 'pending', 'debug' => 'non_json: ' . substr($raw, 0, 200)]);
    exit;
}

// ── 3. Parse Swish status — handle all known response shapes ─────────────────
//
//  Shape A (Swish v3):  { "Status": "COMPLETED", "responseCode": "200", ... }
//  Shape B (some vers): { "status": "success",   "responseCode": "200", ... }
//  Shape C (nested):    { "data": { "status": "success" }, ... }
//  Shape D (message):   { "message": "Swish is processing...", ... }
//
$raw_status = $decoded['Status']            // Shape A
           ?? $decoded['status']            // Shape B
           ?? ($decoded['data']['Status']   // Shape C-A
               ?? ($decoded['data']['status'] // Shape C-B
                   ?? ($decoded['message']  // Shape D
                       ?? 'pending')));

$swish_status = strtolower(trim((string)$raw_status));

// Map all known "success" values
$success_values = ['completed', 'success', 'successful', 'paid', 'approved'];
$failed_values  = ['failed', 'cancelled', 'rejected', 'declined', 'error', 'expired'];

if (in_array($swish_status, $success_values)) {
    // Mark DB confirmed so next poll returns instantly from DB
    try {
        $pdo->prepare("UPDATE payments SET status = 'confirmed', updated_at = NOW() WHERE transaction_reference = ?")
            ->execute([$internal_ref]);
    } catch (Exception $e) { /* non-fatal */ }

    echo json_encode([
        'success'        => true,
        'payment_status' => 'success',
        'source'         => 'swish_api',
        'swish_status'   => $raw_status,
    ]);

} elseif (in_array($swish_status, $failed_values)) {
    try {
        $pdo->prepare("UPDATE payments SET status = 'rejected', updated_at = NOW() WHERE transaction_reference = ?")
            ->execute([$internal_ref]);
    } catch (Exception $e) { /* non-fatal */ }

    echo json_encode([
        'success'        => true,
        'payment_status' => 'failed',
        'source'         => 'swish_api',
        'swish_status'   => $raw_status,
    ]);

} else {
    // Still processing
    echo json_encode([
        'success'        => true,
        'payment_status' => 'pending',
        'source'         => 'swish_api',
        'swish_status'   => $raw_status,
        'raw'            => $decoded,
    ]);
}
