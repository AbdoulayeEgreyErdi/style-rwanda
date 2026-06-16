<?php
/**
 * Style Rwanda - Production Configuration
 * Security hardened, environment aware
 */

// Environment detection
$environment = getenv('APP_ENV') ?: 'development';
$is_production = $environment === 'production';

// Error reporting - NEVER show errors in production
if ($is_production) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Session security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', $is_production);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Timezone
date_default_timezone_set('Africa/Kigali');

// Base URLs - environment aware
$base_url = $is_production 
    ? (getenv('SITE_URL') ?: 'https://stylerwanda.com')
    : 'http://localhost:8081/style-rwanda';

define('SITE_URL', $base_url);
define('SITE_NAME', 'Style Rwanda');
define('SITE_DESCRIPTION', 'Premium fashion for the modern Rwandan - Discover authentic Rwandan style with contemporary elegance');
define('SITE_KEYWORDS', 'fashion, rwanda, clothing, premium fashion, kigali, style, african fashion');
define('ADMIN_EMAIL', 'egreyerdi66@gmail.com');
define('ADMIN_EMAIL_FROM', 'egreyerdi66@gmail.com');

// ========== PAYMENT & WHATSAPP NUMBERS ==========
define('WHATSAPP_NUMBER', '0788123456');        // WhatsApp number (no +)
define('MTN_MOMO_NUMBER', '0793225375');        // MTN Mobile Money
define('AIRTELL_MONEY_NUMBER', '0788123456');   // Airtel Money

// Security constants
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Cache settings
define('CACHE_ENABLED', !$is_production ? false : true);
define('CACHE_DURATION', 3600); // 1 hour

// CSRF Token Generation
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Security headers for production
if ($is_production) {
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;");
}
?>