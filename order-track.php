<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$order_number = isset($_GET['order']) ? sanitizeInput($_GET['order']) : null;
$order = null;
$order_items = [];

if ($order_number) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$order_number]);
    $order = $stmt->fetch();
    
    if ($order) {
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order_items = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; color: #333; }
        
        .navbar { background: #000; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo a { color: #D4AF37; font-size: 1.8rem; font-weight: 700; text-decoration: none; font-family: 'Playfair Display', serif; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; align-items: center; }
        .nav-menu a { color: #fff; text-decoration: none; font-weight: 500; }
        .nav-menu a:hover { color: #D4AF37; }
        .cart-link { position: relative; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #D4AF37; color: #000; border-radius: 50%; padding: 2px 6px; font-size: 12px; }
        .nav-toggle { display: none; font-size: 1.5rem; color: #fff; cursor: pointer; }
        
        .container { max-width: 800px; margin: 0 auto; padding: 2rem; }
        .track-card { background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .track-title { font-size: 1.5rem; margin-bottom: 1rem; color: #000; }
        .track-form { display: flex; gap: 1rem; margin: 2rem 0; }
        .track-form input { flex: 1; padding: 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .track-form button { background: #D4AF37; color: #000; border: none; padding: 0 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
        
        .status-steps { display: flex; justify-content: space-between; margin: 2rem 0; position: relative; }
        .status-step { text-align: center; flex: 1; position: relative; z-index: 1; }
        .status-step .circle { width: 40px; height: 40px; background: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem; color: #666; }
        .status-step.active .circle { background: #D4AF37; color: #000; }
        .status-step.completed .circle { background: #28a745; color: white; }
        .status-step .label { font-size: 0.75rem; color: #666; }
        .status-step.active .label { color: #D4AF37; font-weight: 600; }
        
        .order-details { margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee; }
        .detail-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; }
        
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #D4AF37; color: #000; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 1rem; }
        .footer { background: #111; color: #999; padding: 2rem 0; text-align: center; margin-top: 2rem; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; }
            .nav-menu.active { display: flex; }
            .track-form { flex-direction: column; }
            .status-steps { flex-direction: column; gap: 1rem; }
            .container { padding: 1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo"><a href="/style-rwanda/">Style Rwanda</a></div>
            <div class="nav-toggle" id="navToggle"><i class="fas fa-bars"></i></div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="/style-rwanda/">Home</a></li>
                <li><a href="/style-rwanda/shop.php">Shop</a></li>
                <li><a href="/style-rwanda/contact.php">Contact</a></li>
                <li class="cart-link"><a href="/style-rwanda/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count"><?php echo getCartCount(); ?></span></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="track-card">
            <h1 class="track-title">Track Your Order</h1>
            <p>Enter your order number to track status</p>
            
            <form method="GET" class="track-form">
                <input type="text" name="order" placeholder="Enter Order Number (e.g., ORD-TEST-001)" value="<?php echo htmlspecialchars($order_number); ?>" required>
                <button type="submit">Track Order</button>
            </form>
            
            <?php if ($order_number && !$order): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; text-align: center;">Order not found. Please check your order number.</div>
            <?php endif; ?>
            
            <?php if ($order): ?>
                <div class="status-steps">
                    <?php 
                    $statuses = ['pending' => 'Order Placed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
                    $current = $order['order_status'];
                    $found = false;
                    foreach ($statuses as $key => $label):
                        $found = $found || $current == $key;
                    ?>
                    <div class="status-step <?php echo $found ? 'active' : ''; ?> <?php echo $current == $key ? 'active' : ''; ?>">
                        <div class="circle"><i class="fas <?php echo $key=='pending' ? 'fa-clock' : ($key=='processing' ? 'fa-cogs' : ($key=='shipped' ? 'fa-truck' : 'fa-check')); ?>"></i></div>
                        <div class="label"><?php echo $label; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-details">
                    <h3>Order Details</h3>
                    <div class="detail-row"><strong>Order Number:</strong> <span><?php echo $order['order_number']; ?></span></div>
                    <div class="detail-row"><strong>Order Date:</strong> <span><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span></div>
                    <div class="detail-row"><strong>Total Amount:</strong> <span><?php echo formatPrice($order['total_amount']); ?></span></div>
                    <div class="detail-row"><strong>Payment Status:</strong> <span><?php echo ucfirst($order['payment_status']); ?></span></div>
                    <div class="detail-row"><strong>Order Status:</strong> <span><?php echo ucfirst($order['order_status']); ?></span></div>
                    <div class="detail-row"><strong>Delivery Address:</strong> <span><?php echo htmlspecialchars($order['delivery_address']); ?></span></div>
                </div>
                
                <a href="/style-rwanda/shop.php" class="btn">Continue Shopping</a>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer"><div class="container"><p>&copy; 2025 Style Rwanda. All rights reserved.</p></div></footer>
    <script>document.getElementById('navToggle')?.addEventListener('click',()=>{document.getElementById('navMenu')?.classList.toggle('active');});</script>
</body>
</html>