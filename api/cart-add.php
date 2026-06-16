<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
$size = isset($data['size']) ? sanitizeInput($data['size']) : '';
$color = isset($data['color']) ? sanitizeInput($data['color']) : '';

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid product or quantity']);
    exit;
}

// Get product details
$stmt = $pdo->prepare("SELECT id, name, price, image_url, stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'error' => 'Product not found']);
    exit;
}

// Check stock
if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'error' => 'Not enough stock. Only ' . $product['stock'] . ' left.']);
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Create unique key for this product (with size and color)
$cart_key = $product_id . '_' . $size . '_' . $color;

// Add or update cart item
if (isset($_SESSION['cart'][$cart_key])) {
    $new_quantity = $_SESSION['cart'][$cart_key]['quantity'] + $quantity;
    if ($new_quantity > $product['stock']) {
        echo json_encode(['success' => false, 'error' => 'Cannot add more than available stock']);
        exit;
    }
    $_SESSION['cart'][$cart_key]['quantity'] = $new_quantity;
} else {
    $_SESSION['cart'][$cart_key] = [
        'product_id' => $product_id,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity,
        'size' => $size,
        'color' => $color,
        'image_url' => $product['image_url']
    ];
}

// Calculate new cart count
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart!',
    'cart_count' => $cart_count,
    'cart_total' => getCartTotal()
]);
?>