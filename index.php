<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$featured_products = getProducts(['featured' => true], 4);
$new_products = getProducts(['new' => true], 4);
$shoes_products = getProducts(['category' => 'Shoes'], 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Style Rwanda - Premium Fashion Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #333; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .navbar { background: #000; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo a { color: #D4AF37; font-size: 1.8rem; font-weight: 700; text-decoration: none; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; align-items: center; }
        .nav-menu a { color: #fff; text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .nav-menu a:hover { color: #D4AF37; }
        .cart-link { position: relative; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #D4AF37; color: #000; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; }
        .nav-toggle { display: none; font-size: 1.5rem; color: #fff; cursor: pointer; }
        
        .hero { background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1445205170230-053b83016050?w=1920') center/cover; height: 500px; display: flex; align-items: center; justify-content: center; text-align: center; color: white; }
        .hero-content h1 { font-size: 3rem; margin-bottom: 1rem; }
        .hero-content p { font-size: 1.2rem; margin-bottom: 2rem; }
        
        .btn { display: inline-block; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: all 0.3s; cursor: pointer; border: none; }
        .btn-primary { background: #D4AF37; color: #000; }
        .btn-primary:hover { background: #000; color: #D4AF37; transform: translateY(-2px); }
        .btn-outline { border: 2px solid #D4AF37; color: #D4AF37; background: transparent; }
        .btn-outline:hover { background: #D4AF37; color: #000; }
        
        .section-title { text-align: center; font-size: 2.5rem; margin-bottom: 3rem; }
        .section-title::after { content: ''; display: block; width: 60px; height: 3px; background: #D4AF37; margin: 10px auto 0; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 3rem; }
        .product-card { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .product-card:hover { transform: translateY(-5px); }
        .product-image { height: 280px; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .product-card:hover .product-image img { transform: scale(1.05); }
        .product-info { padding: 1.5rem; text-align: center; }
        .product-info h3 { font-size: 1.1rem; margin-bottom: 0.5rem; }
        .product-price { color: #D4AF37; font-size: 1.2rem; font-weight: bold; margin-bottom: 1rem; }
        
        .newsletter { background: #000; color: white; padding: 4rem 0; text-align: center; margin-top: 2rem; }
        .newsletter-form { display: flex; max-width: 500px; margin: 1rem auto 0; gap: 1rem; }
        .newsletter-form input { flex: 1; padding: 12px; border: none; border-radius: 5px; }
        .newsletter-form button { padding: 12px 30px; background: #D4AF37; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        
        .footer { background: #111; color: #999; padding: 3rem 0 1rem; margin-top: 2rem; }
        .footer-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; }
        .footer-section h3, .footer-section h4 { color: #D4AF37; margin-bottom: 1rem; }
        .social-links { display: flex; gap: 1rem; margin-top: 1rem; }
        .social-links a { color: #fff; font-size: 1.2rem; transition: color 0.3s; }
        .social-links a:hover { color: #D4AF37; }
        .footer-bottom { text-align: center; padding-top: 2rem; margin-top: 2rem; border-top: 1px solid #333; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; background: #333; color: white; border-radius: 5px; z-index: 1000; display: none; }
        .toast.success { background: #28a745; }
        .toast.error { background: #dc3545; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; gap: 1rem; }
            .nav-menu.active { display: flex; }
            .hero-content h1 { font-size: 2rem; }
            .product-grid { grid-template-columns: 1fr; }
            .newsletter-form { flex-direction: column; }
            .section-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo"><a href="/">Style Rwanda</a></div>
            <div class="nav-toggle" id="navToggle"><i class="fas fa-bars"></i></div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="/">Home</a></li>
                <li><a href="/shop.php">Shop</a></li>
                <li><a href="/shop.php?new=1">New Arrivals</a></li>
                <li><a href="/contact.php">Contact</a></li>
                <li><a href="/account.php"><i class="fas fa-user"></i> Account</a></li>
                <li class="cart-link"><a href="/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count" id="cartCount"><?php echo getCartCount(); ?></span></a></li>
            </ul>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Elevate Your Style</h1>
            <p>Discover premium fashion inspired by Rwandan elegance</p>
            <a href="/shop.php" class="btn btn-primary">Shop Now</a>
        </div>
    </section>

    <div class="container">
        <h2 class="section-title">Featured Collection</h2>
        <div class="product-grid">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <div class="product-image"><img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>"></div>
                <div class="product-info">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                    <a href="/product-detail.php?slug=<?php echo $product['slug']; ?>" class="btn btn-outline">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <h2 class="section-title">New Arrivals</h2>
        <div class="product-grid">
            <?php foreach ($new_products as $product): ?>
            <div class="product-card">
                <div class="product-image"><img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>"></div>
                <div class="product-info">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                    <a href="/product-detail.php?slug=<?php echo $product['slug']; ?>" class="btn btn-outline">Shop Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <h2 class="section-title">👟 Shoes Collection</h2>
        <div class="product-grid">
            <?php if (empty($shoes_products)): ?>
                <p style="text-align:center; grid-column:1/-1; color:#666;">No shoes available yet. Check back soon!</p>
            <?php else: ?>
                <?php foreach ($shoes_products as $product): ?>
                <div class="product-card">
                    <div class="product-image"><img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>"></div>
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                        <a href="/product-detail.php?slug=<?php echo $product['slug']; ?>" class="btn btn-outline">Shop Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <section class="newsletter">
        <div class="container">
            <h3>Stay Stylish</h3>
            <p>Subscribe to get exclusive offers and updates</p>
            <form class="newsletter-form" id="newsletterForm">
                <input type="email" id="newsletterEmail" placeholder="Enter your email" required>
                <button type="submit" id="newsletterBtn">Subscribe</button>
            </form>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>Style Rwanda</h3>
                <p>Premium fashion for the modern Rwandan.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/shop.php">Shop</a></li>
                    <li><a href="/about.php">About Us</a></li>
                    <li><a href="/contact.php">Contact</a></li>
                    <li><a href="/returns.php">Returns Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p><i class="fas fa-phone"></i> +250 788 123 456</p>
                <p><i class="fas fa-envelope"></i> info@style.rw</p>
                <p><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Style Rwanda. All rights reserved.</p>
        </div>
    </footer>

    <div id="toast" class="toast"></div>

    <script>
        document.getElementById('navToggle')?.addEventListener('click', () => { 
            document.getElementById('navMenu')?.classList.toggle('active'); 
        });
        
        function showToast(msg, type) { 
            const t = document.getElementById('toast'); 
            t.textContent = msg; 
            t.className = `toast ${type}`; 
            t.style.display = 'block'; 
            setTimeout(() => t.style.display = 'none', 3000); 
        }
        
        document.getElementById('newsletterForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const emailInput = document.getElementById('newsletterEmail');
            const email = emailInput.value.trim();
            const button = document.getElementById('newsletterBtn');
            const originalText = button.textContent;
            
            if (!email) {
                showToast('Please enter your email address', 'error');
                return;
            }
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            
            try {
                const response = await fetch('/api/subscribe-newsletter.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    emailInput.value = '';
                } else {
                    showToast(result.error || 'Subscription failed', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Network error. Please try again.', 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>