<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Load email functions
require_once '../includes/email_simple.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'error' => 'Cart is empty']);
    exit;
}

// Save cart items for email
$cart_items_for_email = $_SESSION['cart'];

// Validate required fields
$required = ['name', 'phone', 'email', 'address', 'city', 'payment_method'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'error' => ucfirst($field) . ' is required']);
        exit;
    }
}

// Verify stock
foreach ($cart_items_for_email as $item) {
    $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'error' => "Product not found"]);
        exit;
    }
    
    if ($product['stock'] < $item['quantity']) {
        echo json_encode(['success' => false, 'error' => "{$product['name']} is out of stock. Only {$product['stock']} left."]);
        exit;
    }
}

$customer_name = sanitizeInput($data['name']);
$customer_phone = sanitizeInput($data['phone']);
$customer_email = sanitizeInput($data['email']);
$delivery_address = sanitizeInput($data['address']);
$city = sanitizeInput($data['city']);
$payment_method = sanitizeInput($data['payment_method']);

$subtotal = getCartTotal();
$shipping = calculateShipping($subtotal);
$total = $subtotal + $shipping;
$order_number = generateOrderNumber();

try {
    $pdo->beginTransaction();
    
    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, delivery_address, city, total_amount, shipping_cost, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$order_number, $customer_name, $customer_phone, $customer_email, $delivery_address, $city, $total, $shipping, $payment_method]);
    $order_id = $pdo->lastInsertId();
    
    // Create order items and update stock
    foreach ($cart_items_for_email as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, size, color) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity'], $item['size'], $item['color']]);
        
        // Update stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    $pdo->commit();
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // ========== SEND ORDER CONFIRMATION EMAIL ==========
    try {
        // Build order data for email
        $order_data = [
            'order_number' => $order_number,
            'total_amount' => $total,
            'shipping_cost' => $shipping,
            'payment_method' => $payment_method,
            'delivery_address' => $delivery_address,
            'city' => $city,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Build order items for email
        $order_items_for_email = [];
        foreach ($cart_items_for_email as $item) {
            $order_items_for_email[] = [
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity']
            ];
        }
        
        // Send email
        if (function_exists('sendOrderConfirmationEmail')) {
            $email_sent = sendOrderConfirmationEmail($order_data, $order_items_for_email, $customer_email, $customer_name);
            if ($email_sent) {
                error_log("✅ Order confirmation email sent to: " . $customer_email);
            } else {
                error_log("❌ Failed to send order confirmation email to: " . $customer_email);
            }
        } else {
            error_log("❌ sendOrderConfirmationEmail function not found!");
        }
        
    } catch (Exception $e) {
        // Don't break checkout if email fails
        error_log("Email error: " . $e->getMessage());
    }
    
    echo json_encode(['success' => true, 'order_id' => $order_id, 'order_number' => $order_number]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Failed to place order: ' . $e->getMessage()]);
}
?>