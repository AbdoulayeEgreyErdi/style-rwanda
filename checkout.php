<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart_items = $_SESSION['cart'];
$subtotal = getCartTotal();
$shipping = calculateShipping($subtotal);
$total = $subtotal + $shipping;
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; }
        
        .navbar { background: #000; padding: 1rem 0; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .nav-logo a { color: #D4AF37; font-size: 1.8rem; text-decoration: none; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; align-items: center; flex-wrap: wrap; }
        .nav-menu a { color: #fff; text-decoration: none; }
        .cart-link { position: relative; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #D4AF37; border-radius: 50%; padding: 2px 6px; font-size: 12px; }
        .nav-toggle { display: none; font-size: 1.5rem; color: #fff; cursor: pointer; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .checkout-layout { display: grid; grid-template-columns: 1fr 380px; gap: 2rem; }
        
        .checkout-form { background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .checkout-form h3 { font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 2px solid #D4AF37; display: inline-block; padding-bottom: 5px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 0.85rem; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
        
        .payment-option { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 0.5rem; cursor: pointer; }
        .payment-option:hover { border-color: #D4AF37; background: #f9f9f9; }
        .payment-option input { width: auto; margin-right: 5px; }
        
        .order-summary { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 100px; }
        .summary-item { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #eee; font-size: 0.85rem; }
        .summary-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #eee; }
        .summary-row.total { font-size: 1.2rem; font-weight: bold; color: #D4AF37; border-top: 2px solid #D4AF37; border-bottom: none; padding-top: 1rem; margin-top: 0.5rem; }
        
        .btn-primary { width: 100%; background: #D4AF37; color: #000; padding: 1rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 1rem; transition: all 0.3s; }
        .btn-primary:hover { background: #000; color: #D4AF37; transform: translateY(-2px); }
        
        .footer { background: #111; color: #999; padding: 2rem 0; text-align: center; margin-top: 2rem; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; background: #28a745; color: white; border-radius: 8px; z-index: 1000; display: none; }
        .toast.error { background: #dc3545; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; gap: 1rem; }
            .nav-menu.active { display: flex; }
            .checkout-layout { grid-template-columns: 1fr; }
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
                <li><a href="/style-rwanda/shop.php?new=1">New Arrivals</a></li>
                <li><a href="/style-rwanda/contact.php">Contact</a></li>
                <li><a href="/style-rwanda/account.php">Account</a></li>
                <li class="cart-link"><a href="/style-rwanda/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count"><?php echo getCartCount(); ?></span></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Checkout</h1>
        
        <div class="checkout-layout">
            <form class="checkout-form" id="checkoutForm">
                <h3>Shipping Information</h3>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" id="name" required>
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" id="phone" required placeholder="0788123456">
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label>Delivery Address *</label>
                    <textarea name="address" id="address" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" id="city" required>
                </div>
                
                <h3>Payment Method</h3>
                <div class="payment-methods">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="MTN MoMo" required>
                        <span>MTN Mobile Money</span>
                        <small style="margin-left: auto;">Number: <?php echo MTN_MOMO_NUMBER; ?></small>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Airtel Money" required>
                        <span>Airtel Money</span>
                        <small style="margin-left: auto;">Number: <?php echo AIRTELL_MONEY_NUMBER; ?></small>
                    </label>
                </div>
                
                <button type="submit" class="btn-primary" id="placeOrderBtn">Place Order</button>
            </form>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                <?php foreach ($cart_items as $item): ?>
                <div class="summary-item">
                    <span><?php echo $item['name']; ?> x<?php echo $item['quantity']; ?></span>
                    <span><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                </div>
                <?php endforeach; ?>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span><?php echo formatPrice($shipping); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Style Rwanda. All rights reserved.</p>
        </div>
    </footer>
    
    <div id="toast" class="toast"></div>

    <script>
        document.getElementById('navToggle')?.addEventListener('click', () => {
            document.getElementById('navMenu')?.classList.toggle('active');
        });
        
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
        
        document.getElementById('checkoutForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Basic validation
            if (!data.name || !data.phone || !data.email || !data.address || !data.city || !data.payment_method) {
                showToast('Please fill all fields', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('placeOrderBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            try {
                const response = await fetch('/style-rwanda/api/place-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Order placed successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/style-rwanda/order-confirmation.php?order_id=' + result.order_id;
                    }, 1500);
                } else {
                    showToast(result.error || 'Error placing order', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Place Order';
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Network error. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Place Order';
            }
        });
    </script>
</body>
</html>