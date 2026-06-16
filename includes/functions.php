<?php
require_once __DIR__ . '/db.php';

// ========== SECURITY FUNCTIONS ==========
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// ========== CART FUNCTIONS ==========
function getCartCount() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}

function getCartTotal() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function calculateShipping($subtotal) {
    return $subtotal >= 100000 ? 0 : 2000;
}

// FIX #2: Order Number Generation with uniqueness check
function generateOrderNumber() {
    global $pdo;
    do {
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)) . '-' . rand(100, 999);
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
        $stmt->execute([$order_number]);
    } while ($stmt->fetch());
    return $order_number;
}

// FIX #3: Cart persistence for logged-in users
function saveCartToDatabase($user_id, $cart) {
    global $pdo;
    $pdo->prepare("DELETE FROM saved_carts WHERE user_id = ?")->execute([$user_id]);
    if (!empty($cart)) {
        $stmt = $pdo->prepare("INSERT INTO saved_carts (user_id, cart_data) VALUES (?, ?)");
        $stmt->execute([$user_id, json_encode($cart)]);
    }
}

function loadCartFromDatabase($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT cart_data FROM saved_carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetchColumn();
    return $cart ? json_decode($cart, true) : [];
}

// ========== FORMATTING FUNCTIONS ==========
function formatPrice($price) {
    return number_format((float)$price, 0, ',', '.') . ' RWF';
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// ========== PRODUCT FUNCTIONS ==========
function getProducts($filters = [], $limit = null, $offset = 0) {
    global $pdo;
    
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    
    if (isset($filters['featured']) && $filters['featured']) {
        $sql .= " AND is_featured = 1";
    }
    
    // FIX #4: Date-based new arrivals (last 30 days OR manually marked)
    if (isset($filters['new']) && $filters['new']) {
        $sql .= " AND (is_new = 1 OR created_at > DATE_SUB(NOW(), INTERVAL 30 DAY))";
    }
    
    if (isset($filters['category']) && $filters['category']) {
        $sql .= " AND category = ?";
        $params[] = $filters['category'];
    }
    
    if (isset($filters['search']) && $filters['search']) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
    }
    
    if (isset($filters['min_price'])) {
        $sql .= " AND price >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (isset($filters['max_price'])) {
        $sql .= " AND price <= ?";
        $params[] = $filters['max_price'];
    }
    
    $sql .= " ORDER BY ";
    switch($filters['sort'] ?? 'newest') {
        case 'price_low': $sql .= "price ASC"; break;
        case 'price_high': $sql .= "price DESC"; break;
        default: $sql .= "created_at DESC";
    }
    
    if ($limit) {
        $sql .= " LIMIT $offset, $limit";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function updateProductStock($product_id, $quantity) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    return $stmt->execute([$quantity, $product_id, $quantity]);
}

// ========== ORDER FUNCTIONS (ADD THESE) ==========
function getOrderById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getOrderItems($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

function getOrderByNumber($order_number) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$order_number]);
    return $stmt->fetch();
}
?>