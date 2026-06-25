<?php
/**
 * Swish Payment Initiation Endpoint — LSUC Website
 * ============================================================
 * Receives AJAX from the Swish payment modal and forwards the
 * request to the appropriate Swish API (USSD / Push / QR / Card).
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/swish_config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── Input ─────────────────────────────────────────────────────
$method       = trim($_POST['method']       ?? '');   // ussd | push | qr | card
$phone        = trim($_POST['phone']        ?? '');
$amount       = trim($_POST['amount']       ?? '');
$internal_ref = trim($_POST['internal_ref'] ?? '');
$context      = trim($_POST['context']      ?? 'LSUC Payment');

$allowed = ['ussd', 'push', 'qr', 'card'];
if (!in_array($method, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment method']);
    exit;
}
if (empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Phone number is required']);
    exit;
}
if (empty($amount) || !is_numeric($amount) || (float)$amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'A valid amount is required']);
    exit;
}
if (empty($internal_ref)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Internal reference is required']);
    exit;
}

$pdo = getDbConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// ── Verify payment record exists ──────────────────────────────
try {
    $stmt = $pdo->prepare("SELECT id FROM payments WHERE transaction_reference = ? LIMIT 1");
    $stmt->execute([$internal_ref]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment record not found']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $e->getMessage()]);
    exit;
}

// ── Build & fire the Swish API call ──────────────────────────
$access_key   = SWISH_ACCESS_KEY;
$security_key = SWISH_SECURITY_KEY;
$till_code    = SWISH_TILL_CODE;
$return_url   = SWISH_RETURN_URL;

$swish_response = null;
$swish_error    = null;

switch ($method) {
    case 'ussd':
        $url     = 'https://swishandroid.swish.co.zm/test/v2/api/web/sendUSSDNotification';
        $payload = [
            'subscriberNumber' => $phone,
            'tillCode'         => $till_code,
            'totalAmount'      => (string)(float)$amount,
            'mpin'             => '',
            'remark1'          => $context,
            'returnUrl'        => $return_url,
        ];
        $swish_response = swishPost($url, $access_key, $security_key, $payload, $swish_error);
        break;

    case 'push':
        $url     = 'https://swishandroid.swish.co.zm/v2/api/web/initiateprompt';
        $payload = [
            'tillCode'         => $till_code,
            'totalAmount'      => (string)(float)$amount,
            'subscriberNumber' => $phone,
            'returnUrl'        => $return_url,
        ];
        $swish_response = swishPost($url, $access_key, $security_key, $payload, $swish_error);
        break;

    case 'qr':
        $url     = 'https://swishandroid.swish.co.zm/v2/api/web/paymentInstance';
        $payload = [
            'tillCode'         => $till_code,
            'totalAmount'      => (string)(float)$amount,
            'subscriberNumber' => $phone,
            'returnUrl'        => $return_url,
        ];
        $swish_response = swishPost($url, $access_key, $security_key, $payload, $swish_error);
        break;

    case 'card':
        $url     = 'https://swishandroid.swish.co.zm/v2/api/web/initiateCardPayment';
        $payload = [
            'subscriberNumber' => $phone,
            'tillCode'         => $till_code,
            'totalAmount'      => (string)(float)$amount,
            'returnUrl'        => $return_url,
        ];
        $swish_response = swishPost($url, $access_key, $security_key, $payload, $swish_error);
        break;
}

if ($swish_error) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Swish API error: ' . $swish_error]);
    exit;
}

$response_code = $swish_response['responseCode'] ?? null;
if ($response_code !== '200' && $response_code !== 200) {
    $msg = $swish_response['message'] ?? 'Unknown error from Swish';
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => $msg, 'swish_response' => $swish_response]);
    exit;
}

// ── Save transLinkID to DB ────────────────────────────────────
$trans_link_id = (string)($swish_response['transLinkID'] ?? '');
try {
    $pdo->prepare(
        "UPDATE payments SET swish_trans_link_id = ?, swish_method = ?, updated_at = NOW()
         WHERE transaction_reference = ?"
    )->execute([$trans_link_id, $method, $internal_ref]);
} catch (Exception $e) { /* non-fatal */ }

// ── Build reply ───────────────────────────────────────────────
$reply = [
    'success'     => true,
    'method'      => $method,
    'transLinkID' => $trans_link_id,
    'message'     => $swish_response['message'] ?? 'Payment initiated',
];

if ($method === 'qr' && !empty($swish_response['imageInBase64'])) {
    $reply['qrImage'] = $swish_response['imageInBase64'];
}
if ($method === 'card' && !empty($swish_response['paymentUrl'])) {
    $reply['paymentUrl'] = $swish_response['paymentUrl'];
}

echo json_encode($reply);

// ─────────────────────────────────────────────────────────────

function swishPost(string $url, string $access_key, string $security_key, array $payload, &$error): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'accessKey: ' . $access_key,
            'password: '  . $security_key,
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $raw      = curl_exec($ch);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($curl_err) { $error = $curl_err; return null; }

    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = 'Invalid JSON from Swish: ' . substr($raw, 0, 200);
        return null;
    }
    return $decoded;
}
