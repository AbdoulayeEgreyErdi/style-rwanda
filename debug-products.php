<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h1>Product Debug</h1>";

// Check database connection
try {
    $pdo->query("SELECT 1");
    echo "✅ Database connected<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// Check if products table exists
try {
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
    if ($stmt->fetch()) {
        echo "✅ Products table exists<br>";
    } else {
        echo "❌ Products table does NOT exist!<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
}

// Count products
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();
    echo "📦 Total products: " . $count . "<br>";
} catch (Exception $e) {
    echo "❌ Error counting: " . $e->getMessage() . "<br>";
}

// Show all products
try {
    $stmt = $pdo->query("SELECT id, name, price, category, is_featured FROM products LIMIT 10");
    $products = $stmt->fetchAll();
    if (empty($products)) {
        echo "❌ No products found in database<br>";
    } else {
        echo "<h3>Products in database:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Featured</th></tr>";
        foreach ($products as $p) {
            echo "<tr>";
            echo "<td>" . $p['id'] . "</td>";
            echo "<td>" . $p['name'] . "</td>";
            echo "<td>" . $p['price'] . "</td>";
            echo "<td>" . $p['category'] . "</td>";
            echo "<td>" . ($p['is_featured'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ Error showing products: " . $e->getMessage() . "<br>";
}

// Test getProducts function
echo "<h3>Testing getProducts() function:</h3>";
try {
    $products = getProducts([], 5);
    if (empty($products)) {
        echo "❌ getProducts() returned empty array<br>";
    } else {
        echo "✅ getProducts() returned " . count($products) . " products<br>";
        echo "<pre>";
        print_r($products);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "❌ getProducts() error: " . $e->getMessage() . "<br>";
}
?>