<?php
/**
 * ============================================================
 *  Swish / Netone Payment Gateway — Configuration
 *  LSUC Main Website
 * ============================================================
 *  DO NOT commit this file to git (add to .gitignore).
 *  Update values to match your Netone Swish console.
 * ============================================================
 */

// Access Key (Public API Key) — sent in every API request header
if (!defined('SWISH_ACCESS_KEY')) {
    define('SWISH_ACCESS_KEY', 'a26f28eb-3779-4d4b-942d-69158a2b1a16');
}

// Security Key — sent as 'password' header in API requests
if (!defined('SWISH_SECURITY_KEY')) {
    define('SWISH_SECURITY_KEY', 'LUS11358');
}

// Till Code / Till ID — your merchant till identifier
if (!defined('SWISH_TILL_CODE')) {
    define('SWISH_TILL_CODE', '11001301');
}

// Merchant Code — used in the Check Status API
if (!defined('SWISH_MERCHANT_CODE')) {
    define('SWISH_MERCHANT_CODE', '33001132');
}

// Callback / Return URL — registered in the Netone Swish Developer Console
// ROOT LEVEL (not /api/) to bypass SRMS application interception.
// Go to your Swish portal and update the Call-Back URL to:
//   https://srms.lsc.edu.zm/swish_callback.php
if (!defined('SWISH_RETURN_URL')) {
    define('SWISH_RETURN_URL', 'https://srms.lsc.edu.zm/swish_callback.php');
}

// Swish Direct Payment Link (from portal)
if (!defined('SWISH_PAYMENT_LINK')) {
    define('SWISH_PAYMENT_LINK', 'https://www.swish.co.zm:9443/outward/PaymentLink/index?data=U2FsdGVkX1+uYDH0uy0/qjeBfnxTtCUlxDH3ip58Y/l7MsXRK3WCdBs9TYDo7LsfaX4LCZ+qDo2F0+JyTa2fyThG12N9oPa4JkNFh3/Ih+M=');
}

// ── Standard fee amounts (ZMW) ──────────────────────────────
if (!defined('SWISH_FEE_APPLICATION')) {
    define('SWISH_FEE_APPLICATION', 100.00);
}
