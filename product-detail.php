<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : null;
$product = getProductBySlug($slug);

if (!$product) {
    header('Location: shop.php');
    exit;
}

$sizes = json_decode($product['sizes'], true) ?: ['S', 'M', 'L', 'XL'];
$colors = json_decode($product['colors'], true) ?: ['Black', 'White'];
$related = getProducts(['category' => $product['category']], 4);

// Generate image variations for different angles (front, back, side)
$image_url = $product['image_url'];
$image_parts = pathinfo($image_url);
$image_front = $image_url;
$image_back = str_replace('.' . $image_parts['extension'], '_back.' . $image_parts['extension'], $image_url);
$image_side = str_replace('.' . $image_parts['extension'], '_side.' . $image_parts['extension'], $image_url);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #333; }
        
        /* Navigation */
        .navbar { background: #000; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .nav-logo a { color: #D4AF37; font-size: 1.8rem; font-weight: 700; text-decoration: none; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; align-items: center; flex-wrap: wrap; }
        .nav-menu a { color: #fff; text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .nav-menu a:hover { color: #D4AF37; }
        .cart-link { position: relative; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #D4AF37; color: #000; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; }
        .nav-toggle { display: none; font-size: 1.5rem; color: #fff; cursor: pointer; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        
        /* Product Gallery Styles */
        .product-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin: 2rem 0; }
        
        .product-gallery {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            position: relative;
        }
        
        .main-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            cursor: zoom-in;
            background: #fff;
        }
        
        .main-image-container img {
            width: 100%;
            height: auto;
            transition: transform 0.3s ease;
            display: block;
        }
        
        .main-image-container:hover img {
            transform: scale(1.5);
        }
        
        .zoom-hint {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 5px;
            pointer-events: none;
            backdrop-filter: blur(5px);
        }
        
        .thumbnail-gallery {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .thumbnail:hover {
            border-color: #D4AF37;
            transform: translateY(-3px);
        }
        
        .thumbnail.active {
            border-color: #D4AF37;
            box-shadow: 0 0 0 2px #D4AF37;
        }
        
        /* Product Info */
        .product-info h1 { font-size: 1.8rem; margin-bottom: 0.5rem; }
        .product-price { font-size: 2rem; color: #D4AF37; font-weight: bold; margin: 1rem 0; }
        .product-description { margin: 1rem 0; line-height: 1.6; color: #555; }
        
        .size-selector, .color-selector { margin: 1rem 0; }
        .size-options, .color-options { display: flex; gap: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap; }
        .size-btn, .color-btn { padding: 10px 20px; border: 1px solid #ddd; background: #fff; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .size-btn.active, .color-btn.active { background: #D4AF37; color: #000; border-color: #D4AF37; }
        .color-btn { width: 45px; height: 45px; border-radius: 50%; }
        
        .quantity-selector { display: flex; align-items: center; gap: 0.5rem; margin: 1rem 0; }
        .qty-btn { width: 40px; height: 40px; border: 1px solid #ddd; background: #fff; cursor: pointer; font-size: 1.2rem; border-radius: 8px; }
        .quantity-input { width: 60px; height: 40px; text-align: center; border: 1px solid #ddd; border-radius: 8px; }
        
        .btn-add-cart { background: #D4AF37; color: #000; padding: 15px 30px; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; width: 100%; margin-top: 1rem; transition: all 0.3s; }
        .btn-add-cart:hover { background: #000; color: #D4AF37; transform: translateY(-2px); }
        
        /* Related Products */
        .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-top: 2rem; }
        .product-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .product-card:hover { transform: translateY(-5px); }
        .product-card img { width: 100%; height: 200px; object-fit: cover; }
        .product-card .info { padding: 1rem; text-align: center; }
        .product-card h3 { font-size: 0.9rem; margin-bottom: 0.5rem; }
        .product-card .price { color: #D4AF37; font-weight: bold; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; background: #28a745; color: white; border-radius: 8px; z-index: 1000; display: none; animation: slideIn 0.3s ease; }
        .toast.error { background: #dc3545; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        
        .footer { background: #111; color: #999; padding: 2rem 0; text-align: center; margin-top: 2rem; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; gap: 1rem; }
            .nav-menu.active { display: flex; }
            .product-detail { grid-template-columns: 1fr; }
            .product-grid { grid-template-columns: repeat(2, 1fr); }
            .thumbnail { width: 60px; height: 60px; }
            .zoom-hint { bottom: 10px; right: 10px; font-size: 9px; }
        }
        
        @media (max-width: 480px) {
            .product-grid { grid-template-columns: 1fr; }
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
                <li><a href="/style-rwanda/account.php"><i class="fas fa-user"></i> Account</a></li>
                <li class="cart-link"><a href="/style-rwanda/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count" id="cartCount"><?php echo getCartCount(); ?></span></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="product-detail">
            <!-- Professional Product Gallery Section -->
            <div class="product-gallery">
                <div class="main-image-container">
                    <img id="mainProductImage" src="<?php echo $image_front; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="zoom-hint">
                        <i class="fas fa-search-plus"></i> Hover to zoom
                    </div>
                </div>
                
                <!-- Thumbnail Gallery for Different Angles -->
                <div class="thumbnail-gallery">
                    <img src="<?php echo $image_front; ?>" class="thumbnail active" onclick="changeImage(this.src)" data-angle="front" alt="Front view">
                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/style-rwanda/' . $image_back)): ?>
                    <img src="<?php echo $image_back; ?>" class="thumbnail" onclick="changeImage(this.src)" data-angle="back" alt="Back view">
                    <?php endif; ?>
                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/style-rwanda/' . $image_side)): ?>
                    <img src="<?php echo $image_side; ?>" class="thumbnail" onclick="changeImage(this.src)" data-angle="side" alt="Side view">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info Section -->
            <div class="product-info">
                <h1><?php echo $product['name']; ?></h1>
                <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                <div class="product-description">
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <div class="size-selector">
                    <strong>Size:</strong>
                    <div class="size-options" id="sizeOptions">
                        <?php foreach ($sizes as $s): ?>
                            <button class="size-btn" data-size="<?php echo $s; ?>"><?php echo $s; ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="color-selector">
                    <strong>Color:</strong>
                    <div class="color-options" id="colorOptions">
                        <?php foreach ($colors as $c): ?>
                            <button class="color-btn" data-color="<?php echo $c; ?>" style="background: <?php echo strtolower($c) == 'black' ? '#1a1a2e' : (strtolower($c) == 'white' ? '#f5f5f5' : '#D4AF37'); ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="quantity-selector">
                    <strong>Quantity:</strong>
                    <button class="qty-btn" id="qtyMinus">-</button>
                    <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    <button class="qty-btn" id="qtyPlus">+</button>
                </div>
                
                <button class="btn-add-cart" id="addToCartBtn" data-id="<?php echo $product['id']; ?>">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($related)): ?>
        <h2>You May Also Like</h2>
        <div class="product-grid">
            <?php foreach ($related as $r): ?>
                <?php if ($r['id'] != $product['id']): ?>
                <div class="product-card">
                    <a href="product-detail.php?slug=<?php echo $r['slug']; ?>">
                        <img src="<?php echo $r['image_url']; ?>" alt="<?php echo $r['name']; ?>">
                        <div class="info">
                            <h3><?php echo $r['name']; ?></h3>
                            <p class="price"><?php echo formatPrice($r['price']); ?></p>
                        </div>
                    </a>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Style Rwanda. All rights reserved.</p>
        </div>
    </footer>

    <div id="toast" class="toast"></div>

    <script>
        // ============================================
        // PRODUCT GALLERY FUNCTIONS
        // ============================================
        
        function changeImage(src) {
            document.getElementById('mainProductImage').src = src;
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
                if (thumb.src === src) {
                    thumb.classList.add('active');
                }
            });
        }
        
        // ============================================
        // ADD TO CART FUNCTIONALITY
        // ============================================
        
        let selectedSize = null;
        let selectedColor = null;
        
        // Size selector
        document.querySelectorAll('.size-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedSize = this.dataset.size;
            });
        });
        
        // Color selector
        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedColor = this.dataset.color;
            });
        });
        
        // Quantity selector
        const qtyInput = document.getElementById('quantity');
        const maxStock = <?php echo $product['stock']; ?>;
        
        document.getElementById('qtyMinus')?.addEventListener('click', () => {
            let val = parseInt(qtyInput.value);
            if (val > 1) qtyInput.value = val - 1;
        });
        
        document.getElementById('qtyPlus')?.addEventListener('click', () => {
            let val = parseInt(qtyInput.value);
            if (val < maxStock) qtyInput.value = val + 1;
        });
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
        
        // Update cart count
        function updateCartCount(count) {
            const cartCountSpan = document.getElementById('cartCount');
            if (cartCountSpan) {
                cartCountSpan.textContent = count;
            }
        }
        
        // Add to cart
        document.getElementById('addToCartBtn')?.addEventListener('click', async function() {
            const productId = this.dataset.id;
            const quantity = parseInt(document.getElementById('quantity').value);
            
            // Validate size
            const sizeOptions = document.querySelectorAll('.size-btn');
            if (sizeOptions.length > 0 && !selectedSize) {
                showToast('Please select a size', 'error');
                return;
            }
            
            // Validate color
            const colorOptions = document.querySelectorAll('.color-btn');
            if (colorOptions.length > 0 && !selectedColor) {
                showToast('Please select a color', 'error');
                return;
            }
            
            const addBtn = document.getElementById('addToCartBtn');
            addBtn.disabled = true;
            addBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            try {
                const response = await fetch('/style-rwanda/api/cart-add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity,
                        size: selectedSize || '',
                        color: selectedColor || ''
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message || 'Product added to cart!', 'success');
                    updateCartCount(data.cart_count);
                    // Animate cart icon
                    const cartIcon = document.querySelector('.cart-link');
                    if (cartIcon) {
                        cartIcon.style.transform = 'scale(1.2)';
                        setTimeout(() => { cartIcon.style.transform = 'scale(1)'; }, 300);
                    }
                } else {
                    showToast(data.error || 'Failed to add to cart', 'error');
                }
            } catch (error) {
                showToast('Network error. Please try again.', 'error');
            } finally {
                addBtn.disabled = false;
                addBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
            }
        });
        
        // Mobile menu toggle
        document.getElementById('navToggle')?.addEventListener('click', () => {
            document.getElementById('navMenu')?.classList.toggle('active');
        });
    </script>
</body>
</html>