<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h1>Products in Database</h1>";

try {
    $stmt = $pdo->query("SELECT id, name, price, category, image_url FROM products LIMIT 10");
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo "❌ No products found!";
    } else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th></tr>";
        foreach ($products as $p) {
            echo "<tr>";
            echo "<td>" . $p['id'] . "</td>";
            echo "<td>" . $p['name'] . "</td>";
            echo "<td>" . $p['price'] . "</td>";
            echo "<td>" . $p['category'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p>Total: " . count($products) . " products</p>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>