<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = getCartTotal();
$shipping = calculateShipping($subtotal);
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Style Rwanda</title>
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
        
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        
        .cart-layout { display: grid; grid-template-columns: 1fr 350px; gap: 2rem; }
        
        .cart-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; }
        .cart-table th { background: #000; color: white; padding: 1rem; text-align: left; }
        .cart-table td { padding: 1rem; border-bottom: 1px solid #eee; }
        .product-info { display: flex; align-items: center; gap: 1rem; }
        .product-info img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        
        .cart-quantity { display: flex; align-items: center; gap: 0.5rem; }
        .qty-btn { width: 30px; height: 30px; border: 1px solid #ddd; background: #fff; cursor: pointer; border-radius: 5px; }
        .cart-qty-input { width: 50px; text-align: center; padding: 5px; border: 1px solid #ddd; border-radius: 5px; }
        .remove-item { background: none; border: none; color: #dc3545; cursor: pointer; font-size: 1.2rem; }
        
        .cart-summary { background: white; padding: 1.5rem; border-radius: 12px; height: fit-content; }
        .summary-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #eee; }
        .summary-row.total { font-size: 1.2rem; font-weight: bold; color: #D4AF37; border-top: 2px solid #D4AF37; padding-top: 1rem; }
        
        .btn { display: inline-block; padding: 12px; text-align: center; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .btn-primary { background: #D4AF37; color: #000; width: 100%; margin-bottom: 1rem; }
        .btn-outline { border: 2px solid #D4AF37; color: #D4AF37; background: transparent; width: 100%; }
        
        .empty-cart { text-align: center; padding: 3rem; background: white; border-radius: 12px; }
        .empty-cart i { font-size: 4rem; color: #ddd; }
        
        .footer { background: #111; color: #999; padding: 2rem; text-align: center; margin-top: 2rem; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; background: #28a745; color: white; border-radius: 8px; z-index: 1000; display: none; }
        .toast.error { background: #dc3545; }
        
        @media (max-width: 768px) {
            .cart-layout { grid-template-columns: 1fr; }
            .product-info { flex-direction: column; text-align: center; }
            .cart-table th, .cart-table td { padding: 0.5rem; font-size: 0.8rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo"><a href="/">Style Rwanda</a></div>
            <ul class="nav-menu">
                <li><a href="/">Home</a></li>
                <li><a href="/shop.php">Shop</a></li>
                <li><a href="/contact.php">Contact</a></li>
                <li class="cart-link"><a href="/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count"><?php echo getCartCount(); ?></span></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
            <a href="shop.php" class="btn btn-primary" style="width: auto; display: inline-block;">Continue Shopping</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <div>
                <table class="cart-table">
                    <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($cart_items as $key => $item): ?>
                        <tr data-key="<?php echo $key; ?>">
                            <td><div class="product-info"><img src="<?php echo $item['image_url']; ?>"><div><strong><?php echo $item['name']; ?></strong><?php if($item['size']): ?><br><small>Size: <?php echo $item['size']; ?></small><?php endif; ?><?php if($item['color']): ?><br><small>Color: <?php echo $item['color']; ?></small><?php endif; ?></div></div></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><div class="cart-quantity"><button class="qty-btn qty-minus" data-key="<?php echo $key; ?>">-</button><input type="number" value="<?php echo $item['quantity']; ?>" min="1" class="cart-qty-input" data-key="<?php echo $key; ?>"><button class="qty-btn qty-plus" data-key="<?php echo $key; ?>">+</button></div></td>
                            <td class="item-total"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                            <td><button class="remove-item" data-key="<?php echo $key; ?>"><i class="fas fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Subtotal:</span><span id="subtotal"><?php echo formatPrice($subtotal); ?></span></div>
                <div class="summary-row"><span>Shipping:</span><span id="shipping"><?php echo formatPrice($shipping); ?></span></div>
                <div class="summary-row total"><span>Total:</span><span id="total"><?php echo formatPrice($total); ?></span></div>
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
            </div>
        </div>
        <?php endif; ?>
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
        
        async function updateCart(key, quantity) {
            try {
                const res = await fetch('/api/cart-update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ key: key, quantity: quantity })
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    showToast('Error updating cart', 'error');
                }
            } catch (error) {
                showToast('Network error', 'error');
            }
        }
        
        async function removeItem(key) {
            try {
                const res = await fetch('/api/cart-remove.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ key: key })
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    showToast('Error removing item', 'error');
                }
            } catch (error) {
                showToast('Network error', 'error');
            }
        }
        
        document.querySelectorAll('.qty-minus').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.key;
                const input = document.querySelector(`.cart-qty-input[data-key="${key}"]`);
                let qty = parseInt(input.value);
                if (qty > 1) updateCart(key, qty - 1);
            });
        });
        
        document.querySelectorAll('.qty-plus').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.key;
                const input = document.querySelector(`.cart-qty-input[data-key="${key}"]`);
                let qty = parseInt(input.value);
                updateCart(key, qty + 1);
            });
        });
        
        document.querySelectorAll('.cart-qty-input').forEach(input => {
            input.addEventListener('change', () => {
                const key = input.dataset.key;
                let qty = parseInt(input.value);
                if (qty < 1) qty = 1;
                updateCart(key, qty);
            });
        });
        
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', () => {
                if (confirm('Remove this item?')) {
                    removeItem(btn.dataset.key);
                }
            });
        });
    </script>
</body>
</html>