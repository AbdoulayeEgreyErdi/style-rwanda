<?php
// Server Information Page - Style Rwanda
phpinfo();
?><?php
/**
 * Server Information - Style Rwanda
 * Production: Disable this file for security
 */

// Allow access only in development
$environment = getenv('APP_ENV') ?: 'development';
if ($environment === 'production') {
    die('Access denied in production mode');
}

echo "<h1>Style Rwanda - Server Information</h1>";
echo "<hr>";

// PHP Version
echo "<h2>PHP Version</h2>";
echo "<p>" . PHP_VERSION . "</p>";

// Server Software
echo "<h2>Web Server</h2>";
echo "<p>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";

// Extensions
echo "<h2>Loaded Extensions</h2>";
$extensions = get_loaded_extensions();
echo "<ul>";
foreach (array_slice($extensions, 0, 20) as $ext) {
    echo "<li>" . $ext . "</li>";
}
echo "<li>... and " . (count($extensions) - 20) . " more</li>";
echo "</ul>";

// Database Connection Test
echo "<h2>Database Connection</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=style_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✅ Connected to style_db successfully</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $products = $stmt->fetchColumn();
    echo "<p>📦 Products in database: " . $products . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $orders = $stmt->fetchColumn();
    echo "<p>📋 Orders in database: " . $orders . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Database Error: " . $e->getMessage() . "</p>";
}

// Session Info
echo "<h2>Session Status</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

// Server Variables
echo "<h2>Server Variables</h2>";
echo "<ul>";
echo "<li>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</li>";
echo "<li>Server Port: " . ($_SERVER['SERVER_PORT'] ?? 'N/A') . "</li>";
echo "<li>Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "</li>";
echo "<li>Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</li>";
echo "<li>Remote Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</li>";
echo "</ul>";

// Directory Structure Check
echo "<h2>Critical Directories Check</h2>";
$dirs = ['admin', 'api', 'assets', 'includes', 'sql'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color:green'>✅ $dir/ exists</p>";
    } else {
        echo "<p style='color:red'>❌ $dir/ missing</p>";
    }
}

// Critical Files Check
echo "<h2>Critical Files Check</h2>";
$files = [
    'index.php', 'shop.php', 'cart.php', 'checkout.php', 'order-confirmation.php',
    'admin/login.php', 'admin/index.php', 'includes/config.php', 'includes/db.php', 'includes/functions.php'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color:green'>✅ $file</p>";
    } else {
        echo "<p style='color:red'>❌ $file missing</p>";
    }
}

// PHP Configuration
echo "<h2>PHP Configuration</h2>";
echo "<ul>";
echo "<li>max_execution_time: " . ini_get('max_execution_time') . " seconds</li>";
echo "<li>memory_limit: " . ini_get('memory_limit') . "</li>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>display_errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</li>";
echo "</ul>";

// Security Headers Check
echo "<h2>Security Headers</h2>";
$headers = [
    'X-Frame-Options',
    'X-XSS-Protection', 
    'X-Content-Type-Options',
    'Referrer-Policy'
];
foreach ($headers as $header) {
    $value = headers_list();
    $found = false;
    foreach ($value as $h) {
        if (strpos($h, $header) !== false) {
            $found = true;
            break;
        }
    }
    echo "<p>" . ($found ? "✅ $header is set" : "❌ $header not set") . "</p>";
}

echo "<hr>";
echo "<h3>Quick Links</h3>";
echo "<ul>";
echo "<li><a href='/style-rwanda/'>Homepage</a></li>";
echo "<li><a href='/style-rwanda/shop.php'>Shop</a></li>";
echo "<li><a href='/style-rwanda/admin/login.php'>Admin Login</a></li>";
echo "<li><a href='/style-rwanda/test-db.php'>Database Test</a></li>";
echo "<li><a href='/style-rwanda/full-test.php'>Full Diagnostic</a></li>";
echo "<li><a href='/style-rwanda/api/get-products.php'>API Test</a></li>";
echo "</ul>";
?>