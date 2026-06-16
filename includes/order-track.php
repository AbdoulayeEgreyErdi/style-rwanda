<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$order_number = isset($_GET['order']) ? sanitizeInput($_GET['order']) : null;
$order = null;

if ($order_number) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$order_number]);
    $order = $stmt->fetch();
}

$page_title = 'Track Order - Style Rwanda';
include 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 3rem auto;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1>Track Your Order</h1>
        <p>Enter your order number to track status</p>
    </div>
    
    <?php if (!$order && !$order_number): ?>
    <form method="GET" class="track-form" style="background: #f9f9f9; padding: 2rem; border-radius: 10px;">
        <div class="form-group">
            <label for="order">Order Number:</label>
            <input type="text" id="order" name="order" placeholder="e.g., ORD-20240101-xxx" required style="width: 100%; padding: 12px;">
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Track Order</button>
    </form>
    <?php elseif ($order): ?>
    <div style="background: #fff; border-radius: 10px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #D4AF37;">Order: <?php echo $order['order_number']; ?></h2>
        
        <div style="margin: 2rem 0;">
            <h3>Order Status</h3>
            <div style="background: #f9f9f9; padding: 1rem; border-radius: 5px;">
                <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                    Payment: <?php echo ucfirst($order['payment_status']); ?>
                </span>
                <span class="status-badge status-<?php echo $order['order_status']; ?>" style="margin-left: 1rem;">
                    Order: <?php echo ucfirst($order['order_status']); ?>
                </span>
            </div>
        </div>
        
        <div style="margin: 2rem 0;">
            <h3>Order Timeline</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 10px; border-left: 3px solid #D4AF37; margin-bottom: 10px;">
                    ✅ Order Placed - <?php echo date('F j, Y g:i a', strtotime($order['created_at'])); ?>
                </li>
                <?php if ($order['payment_status'] == 'verified'): ?>
                <li style="padding: 10px; border-left: 3px solid #D4AF37; margin-bottom: 10px;">
                    ✅ Payment Verified
                </li>
                <?php endif; ?>
                <?php if ($order['order_status'] == 'processing'): ?>
                <li style="padding: 10px; border-left: 3px solid #D4AF37; margin-bottom: 10px;">
                    🔄 Order Processing
                </li>
                <?php endif; ?>
                <?php if ($order['order_status'] == 'shipped'): ?>
                <li style="padding: 10px; border-left: 3px solid #D4AF37; margin-bottom: 10px;">
                    🚚 Order Shipped
                </li>
                <?php endif; ?>
                <?php if ($order['order_status'] == 'delivered'): ?>
                <li style="padding: 10px; border-left: 3px solid #D4AF37; margin-bottom: 10px;">
                    📦 Order Delivered
                </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div style="text-align: center;">
            <a href="/shop.php" class="btn btn-outline">Continue Shopping</a>
        </div>
    </div>
    <?php else: ?>
    <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; text-align: center;">
        <p>Order not found. Please check your order number and try again.</p>
        <a href="/order-track.php" class="btn btn-primary" style="margin-top: 1rem;">Try Again</a>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>