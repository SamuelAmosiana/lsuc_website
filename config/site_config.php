<?php
/**
 * ============================================================
 *  LSUC Site Configuration (PHP)
 * ============================================================
 *  To update the institution name in PHP pages and email
 *  templates, change the constant values below.
 *  Include this file at the top of every PHP page:
 *
 *      require_once __DIR__ . '/config/site_config.php';
 *
 *  Then use the constants anywhere in PHP code:
 *
 *      echo SITE_NAME;
 *      echo SITE_SHORT_NAME;
 * ============================================================
 */

if (!defined('SITE_NAME')) {
    define('SITE_NAME',       'Lusaka South University College');
}

if (!defined('SITE_SHORT_NAME')) {
    define('SITE_SHORT_NAME', 'LSUC');
}

if (!defined('SITE_TAGLINE')) {
    define('SITE_TAGLINE',    'Dream, Explore, Acquire');
}

if (!defined('SITE_URL')) {
    define('SITE_URL',        'https://lsc.edu.zm');
}

/**
 * Helper function — returns the full institution name.
 * Identical to getSiteName() in site-config.js for consistency.
 *
 * @return string
 */
function getSiteName(): string {
    return SITE_NAME;
}

/**
 * Helper function — returns the short name / acronym.
 *
 * @return string
 */
function getSiteShortName(): string {
    return SITE_SHORT_NAME;
}

/**
 * Helper function — returns the tagline.
 *
 * @return string
 */
function getSiteTagline(): string {
    return SITE_TAGLINE;
}
