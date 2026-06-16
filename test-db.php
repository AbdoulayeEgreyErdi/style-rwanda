<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Style Rwanda - Database Test</h1>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=style_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected successfully!<br><br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $products = $stmt->fetch();
    echo "📦 Products: " . $products['total'] . "<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $orders = $stmt->fetch();
    echo "📋 Orders: " . $orders['total'] . "<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE email = 'admin@style.rw'");
    $admin = $stmt->fetch();
    echo "👑 Admin: " . ($admin['total'] > 0 ? "Yes" : "No") . "<br>";
    
    echo "<hr><h3>Quick Links:</h3>";
    echo "<ul>";
    echo "<li><a href='/style-rwanda/'>Homepage</a></li>";
    echo "<li><a href='/style-rwanda/shop.php'>Shop</a></li>";
    echo "<li><a href='/style-rwanda/admin/login.php'>Admin Login</a></li>";
    echo "<li><a href='/style-rwanda/api/get-products.php'>API - Products</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>