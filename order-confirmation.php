<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: shop.php');
    exit;
}

// Get order details - Using direct query since function might be missing
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: shop.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

// Calculate subtotal
$subtotal = 0;
foreach ($order_items as $item) {
    $subtotal += $item['product_price'] * $item['quantity'];
}

// Clear cart after successful order
unset($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; }
        
        .navbar { background: #000; padding: 1rem 0; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo a { color: #D4AF37; font-size: 1.8rem; text-decoration: none; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; }
        .nav-menu a { color: #fff; text-decoration: none; }
        .cart-link { position: relative; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #D4AF37; border-radius: 50%; padding: 2px 6px; font-size: 12px; }
        
        .container { max-width: 1000px; margin: 0 auto; padding: 2rem; }
        
        .success-banner { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 16px; padding: 2rem; text-align: center; color: white; margin-bottom: 2rem; }
        .success-icon { width: 70px; height: 70px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; }
        
        .order-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .card { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #D4AF37; display: inline-block; }
        .detail-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f0f0f0; }
        .detail-label { font-size: 0.8rem; color: #888; }
        .detail-value { font-size: 0.8rem; font-weight: 600; color: #1a1a2e; }
        
        .payment-box { background: linear-gradient(135deg, #fff8e7 0%, #ffffff 100%); border: 2px solid #D4AF37; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .payment-number { background: #1a1a2e; color: #D4AF37; padding: 0.75rem; border-radius: 8px; text-align: center; font-weight: 600; margin: 0.75rem 0; }
        .amount-box { background: #D4AF37; color: #1a1a2e; padding: 0.75rem; border-radius: 8px; text-align: center; font-weight: 700; font-size: 1.1rem; margin: 0.75rem 0; }
        
        .input-group { margin: 1rem 0; }
        .input-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; }
        .input-group input { width: 100%; padding: 0.7rem; border: 1px solid #ddd; border-radius: 8px; }
        
        .whatsapp-btn { width: 100%; background: #25D366; color: white; border: none; padding: 0.8rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .whatsapp-btn:hover { background: #128C7E; }
        
        .action-buttons { display: flex; gap: 1rem; margin-top: 1rem; }
        .btn-confirm, .btn-reject { flex: 1; padding: 0.7rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-confirm { background: #D4AF37; color: #1a1a2e; }
        .btn-reject { background: #dc3545; color: white; }
        
        .order-table { width: 100%; border-collapse: collapse; }
        .order-table th { text-align: left; padding: 0.6rem; background: #f8f8fa; font-size: 0.75rem; font-weight: 600; }
        .order-table td { padding: 0.6rem; border-bottom: 1px solid #eee; font-size: 0.8rem; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; background: #28a745; color: white; border-radius: 8px; z-index: 1000; display: none; }
        
        .footer { background: #111; color: #999; padding: 2rem 0; text-align: center; margin-top: 2rem; }
        
        @media (max-width: 768px) {
            .order-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo"><a href="/style-rwanda/">Style Rwanda</a></div>
            <ul class="nav-menu">
                <li><a href="/style-rwanda/">Home</a></li>
                <li><a href="/style-rwanda/shop.php">Shop</a></li>
                <li><a href="/style-rwanda/contact.php">Contact</a></li>
                <li class="cart-link"><a href="/style-rwanda/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count">0</span></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Success Banner -->
        <div class="success-banner">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h1>Thank You! Your Order Has Been Placed!</h1>
            <p>Order #<?php echo $order['order_number']; ?> • <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
        </div>

        <!-- Order Grid -->
        <div class="order-grid">
            <!-- Left Column -->
            <div>
                <div class="card">
                    <h3 class="card-title">Order Details</h3>
                    <div class="detail-row"><span class="detail-label">Order ID:</span><span class="detail-value"><?php echo $order['order_number']; ?> <button onclick="copyOrderId()" style="background:none; border:none; color:#D4AF37; cursor:pointer;"><i class="fas fa-copy"></i></button></span></div>
                    <div class="detail-row"><span class="detail-label">Order Date:</span><span class="detail-value"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Payment Method:</span><span class="detail-value"><?php echo $order['payment_method']; ?></span></div>
                    <div class="detail-row"><span class="detail-label">Payment Status:</span><span class="detail-value"><span style="background:#fff3cd; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.7rem;"><?php echo ucfirst($order['payment_status']); ?></span></span></div>
                    <div class="detail-row"><span class="detail-label">Total Amount:</span><span class="detail-value" style="color:#D4AF37; font-weight:700;"><?php echo formatPrice($order['total_amount']); ?></span></div>
                </div>
                
                <div class="card" style="margin-top:1rem;">
                    <h3 class="card-title">Customer Information</h3>
                    <div class="detail-row"><span class="detail-label">Name:</span><span class="detail-value"><?php echo htmlspecialchars($order['customer_name']); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value"><?php echo htmlspecialchars($order['customer_phone']); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Email:</span><span class="detail-value"><?php echo htmlspecialchars($order['customer_email']); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Address:</span><span class="detail-value"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></span></div>
                    <div class="detail-row"><span class="detail-label">City:</span><span class="detail-value"><?php echo htmlspecialchars($order['city']); ?></span></div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div>
                <div class="payment-box">
                    <h3 style="margin-bottom: 0.75rem;">Payment Instructions</h3>
                    <p style="font-size:0.8rem;">Please send payment to the Mobile Money number below:</p>
                    <div class="payment-number"><i class="fas fa-phone"></i> <?php echo $order['payment_method'] == 'MTN MoMo' ? 'MTN Mobile Money: ' . MTN_MOMO_NUMBER : 'Airtel Money: ' . AIRTELL_MONEY_NUMBER; ?></div>
                    <div class="amount-box"><i class="fas fa-money-bill-wave"></i> Amount to Pay: <?php echo formatPrice($order['total_amount']); ?></div>
                    <div class="input-group"><label>Transaction ID</label><input type="text" id="transactionId" placeholder="Enter your transaction ID"></div>
                    <button class="whatsapp-btn" onclick="sendWhatsAppConfirmation()"><i class="fab fa-whatsapp"></i> Confirm Payment via WhatsApp</button>
                    <div class="action-buttons"><button class="btn-confirm" onclick="confirmPayment()">Confirm Payment</button><button class="btn-reject" onclick="rejectPayment()">Reject Payment</button></div>
                </div>
                
                <div class="card">
                    <h3 class="card-title">Order Summary</h3>
                    <?php foreach ($order_items as $item): ?>
                    <div class="detail-row"><span class="detail-label"><?php echo htmlspecialchars($item['product_name']); ?> (<?php echo $item['size'] ?: 'One Size'; ?>) × <?php echo $item['quantity']; ?></span><span class="detail-value"><?php echo formatPrice($item['product_price'] * $item['quantity']); ?></span></div>
                    <?php endforeach; ?>
                    <div class="detail-row" style="border-top:1px solid #eee; margin-top:0.5rem; padding-top:0.5rem;"><span class="detail-label"><strong>Subtotal:</strong></span><span class="detail-value"><?php echo formatPrice($subtotal); ?></span></div>
                    <div class="detail-row"><span class="detail-label"><strong>Delivery Fee:</strong></span><span class="detail-value"><?php echo formatPrice($order['shipping_cost']); ?></span></div>
                    <div class="detail-row" style="border-top:2px solid #D4AF37; margin-top:0.5rem; padding-top:0.5rem;"><span class="detail-label"><strong>TOTAL:</strong></span><span class="detail-value" style="color:#D4AF37; font-weight:700;"><?php echo formatPrice($order['total_amount']); ?></span></div>
                </div>
            </div>
        </div>
        
        <!-- Order Items Table -->
        <div class="card">
            <h3 class="card-title">Order Items</h3>
            <div style="overflow-x: auto;">
                <table class="order-table">
                    <thead><tr><th>Product</th><th>Size</th><th>Color</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr><td><?php echo htmlspecialchars($item['product_name']); ?></td><td><?php echo $item['size'] ?: '-'; ?></td><td><?php echo $item['color'] ?: '-'; ?></td><td><?php echo $item['quantity']; ?></td><td><?php echo formatPrice($item['product_price']); ?></td><td><?php echo formatPrice($item['product_price'] * $item['quantity']); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer"><div class="container"><p>&copy; 2025 Style Rwanda. All rights reserved.</p></div></footer>
    <div id="toast" class="toast"></div>

    <script>
        function showToast(msg, type) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = `toast ${type}`;
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 3000);
        }
        
        function copyOrderId() {
            navigator.clipboard.writeText("<?php echo $order['order_number']; ?>");
            showToast('Order ID copied!', 'success');
        }
        
        function sendWhatsAppConfirmation() {
            const transactionId = document.getElementById('transactionId').value;
            if (!transactionId) { showToast('Please enter transaction ID', 'error'); return; }
            const msg = `*STYLE RWANDA PAYMENT*\nOrder: <?php echo $order['order_number']; ?>\nAmount: <?php echo formatPrice($order['total_amount']); ?>\nTransaction: ${transactionId}`;
            window.open(`https://wa.me/<?php echo WHATSAPP_NUMBER; ?>?text=${encodeURIComponent(msg)}`, '_blank');
            showToast('Opening WhatsApp...', 'success');
        }
        
        function confirmPayment() {
            const tid = document.getElementById('transactionId').value;
            if (!tid) { showToast('Enter transaction ID', 'error'); return; }
            sendWhatsAppConfirmation();
        }
        
        function rejectPayment() {
            if (confirm('Cancel this order?')) showToast('Cancellation requested', 'error');
        }
    </script>
</body>
</html>