<?php
echo "<h1>Style Rwanda - Complete System Test</h1>";

// PHP Version
echo "<h3>PHP Version: " . PHP_VERSION . "</h3>";

// Database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=style_db", "root", "");
    echo "✅ Database: Connected<br>";
    $products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    echo "✅ Products: $products<br>";
    $orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    echo "✅ Orders: $orders<br>";
} catch(Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// Session
session_start();
echo "✅ Session: Active (ID: " . session_id() . ")<br>";

// File check
$files = ['index.php', 'shop.php', 'cart.php', 'checkout.php', 'order-confirmation.php', 'admin/login.php'];
foreach($files as $f) {
    echo (file_exists($f) ? "✅ $f<br>" : "❌ MISSING: $f<br>");
}

echo "<hr><h3>Quick Access:</h3>";
echo "<ul>";
echo "<li><a href='/style-rwanda/'>Home</a></li>";
echo "<li><a href='/style-rwanda/shop.php'>Shop</a></li>";
echo "<li><a href='/style-rwanda/admin/login.php'>Admin</a></li>";
echo "<li><a href='/style-rwanda/api/get-products.php'>API</a></li>";
echo "</ul>";
?>