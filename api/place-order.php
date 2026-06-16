<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get POST data
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    $data = $_POST;
}

if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'error' => 'Cart is empty']);
    exit;
}

// Save cart items before clearing (for email)
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
    
    // Send email confirmation (using saved cart items)
    try {
        require_once '../includes/email_simple.php';
        
        // Build order items HTML for email
        $items_html = "";
        foreach ($cart_items_for_email as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $items_html .= "<tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($item['name']) . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>" . $item['quantity'] . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>" . formatPrice($item['price']) . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>" . formatPrice($item_total) . "</td>
            </tr>";
        }
        
        $email_body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
                .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
                .content { padding: 30px; }
                .order-details { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .order-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .order-table th { background: #f0f0f0; padding: 10px; text-align: left; }
                .order-table td { padding: 8px; border-bottom: 1px solid #eee; }
                .total-row { font-weight: bold; border-top: 2px solid #D4AF37; }
                .btn { background: #D4AF37; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . SITE_NAME . "</h1>
                </div>
                <div class='content'>
                    <h2>Thank You for Your Order! 🎉</h2>
                    <p>Dear " . htmlspecialchars($customer_name) . ",</p>
                    <p>Your order has been successfully placed.</p>
                    
                    <div class='order-details'>
                        <p><strong>📦 Order Number:</strong> " . $order_number . "</p>
                        <p><strong>📅 Order Date:</strong> " . date('F j, Y, g:i a') . "</p>
                        <p><strong>💳 Payment Method:</strong> " . $payment_method . "</p>
                        <p><strong>📍 Shipping Address:</strong> " . nl2br(htmlspecialchars($delivery_address)) . ", " . $city . "</p>
                    </div>
                    
                    <h3>Order Items</h3>
                    <table class='order-table'>
                        <thead>
                            <tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>
                        </thead>
                        <tbody>" . $items_html . "</tbody>
                        <tfoot>
                            <tr><td colspan='3' style='text-align: right;'>Subtotal:</td><td style='text-align: right;'>" . formatPrice($subtotal) . "</td></tr>
                            <tr><td colspan='3' style='text-align: right;'>Shipping:</td><td style='text-align: right;'>" . formatPrice($shipping) . "</td></tr>
                            <tr class='total-row'><td colspan='3' style='text-align: right;'><strong>TOTAL:</strong></td><td style='text-align: right;'><strong>" . formatPrice($total) . "</strong></td></tr>
                        </tfoot>
                    </table>
                    
                    <h3>Payment Instructions</h3>
                    <p>Please complete your payment to:</p>
                    <p><strong>" . ($payment_method == 'MTN MoMo' ? 'MTN Mobile Money: ' . MTN_MOMO_NUMBER : 'Airtel Money: ' . AIRTELL_MONEY_NUMBER) . "</strong></p>
                    <p>Amount to pay: <strong>" . formatPrice($total) . "</strong></p>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='" . SITE_URL . "/order-confirmation.php?order_id=" . $order_id . "' class='btn'>View Your Order →</a>
                    </p>
                    <p>We'll notify you once your payment is verified.</p>
                    <p>Thank you for shopping with " . SITE_NAME . "!</p>
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
                    <p>" . SITE_URL . "</p>
                </div>
            </div>
        </body>
        </html>";
        
        sendEmail($customer_email, "Order Confirmation #" . $order_number, $email_body);
    } catch (Exception $e) {
        // Don't break checkout if email fails
        error_log("Email notification failed: " . $e->getMessage());
    }
    
    echo json_encode(['success' => true, 'order_id' => $order_id, 'order_number' => $order_number]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Failed to place order: ' . $e->getMessage()]);
}
?>