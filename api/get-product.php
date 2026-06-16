<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : null;

if ($id > 0) {
    $product = getProductById($id);
} elseif ($slug) {
    $product = getProductBySlug($slug);
} else {
    echo json_encode(['success' => false, 'error' => 'Product ID or slug required']);
    exit;
}

if ($product) {
    $product['price_formatted'] = formatPrice($product['price']);
    $product['sizes'] = json_decode($product['sizes'], true);
    $product['colors'] = json_decode($product['colors'], true);
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'error' => 'Product not found']);
}
?>