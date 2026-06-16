<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    $category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : null;
    $featured = isset($_GET['featured']) ? true : false;
    $new = isset($_GET['new']) ? true : false;
    
    $sql = "SELECT id, name, slug, description, price, category, image_url, sizes, colors, stock, is_featured, is_new FROM products WHERE 1=1";
    $params = [];
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($featured) {
        $sql .= " AND is_featured = 1";
    }
    
    if ($new) {
        $sql .= " AND is_new = 1";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Format prices
    foreach ($products as &$product) {
        $product['price_formatted'] = formatPrice($product['price']);
        $product['sizes'] = json_decode($product['sizes'], true);
        $product['colors'] = json_decode($product['colors'], true);
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($products),
        'products' => $products
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>