<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// FIX #8: Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// FIX #8: Add to wishlist with duplicate check
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];
    if (!in_array($id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $id;
    }
    header('Location: wishlist.php');
    exit;
}

// Remove from wishlist
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    $_SESSION['wishlist'] = array_filter($_SESSION['wishlist'], function($item) use ($id) {
        return $item != $id;
    });
    header('Location: wishlist.php');
    exit;
}

// Get wishlist products
$wishlist_products = [];
if (!empty($_SESSION['wishlist'])) {
    $placeholders = str_repeat('?,', count($_SESSION['wishlist']) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($_SESSION['wishlist']);
    $wishlist_products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Style Rwanda</title>
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
        .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; }
        .product-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative; }
        .product-card img { width: 100%; height: 250px; object-fit: cover; }
        .product-card .info { padding: 1rem; text-align: center; }
        .product-card h3 { font-size: 1rem; }
        .price { color: #D4AF37; font-weight: bold; margin: 0.5rem 0; }
        .remove-btn { position: absolute; top: 10px; right: 10px; background: white; border: none; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; color: #dc3545; }
        .btn { display: inline-block; padding: 8px 16px; background: #D4AF37; color: #000; text-decoration: none; border-radius: 5px; font-size: 12px; }
        .empty { text-align: center; padding: 3rem; background: white; border-radius: 12px; }
        .footer { background: #111; color: #999; padding: 2rem; text-align: center; margin-top: 2rem; }
        @media (max-width: 768px) { .nav-menu { display: none; } }
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
                <li class="cart-link"><a href="/style-rwanda/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count"><?php echo getCartCount(); ?></span></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>My Wishlist</h1>
        
        <?php if (empty($wishlist_products)): ?>
        <div class="empty">
            <i class="far fa-heart" style="font-size: 4rem; color: #ddd;"></i>
            <p>Your wishlist is empty</p>
            <a href="shop.php" class="btn">Start Shopping</a>
        </div>
        <?php else: ?>
        <div class="wishlist-grid">
            <?php foreach ($wishlist_products as $product): ?>
            <div class="product-card">
                <a href="?remove=<?php echo $product['id']; ?>" class="remove-btn" onclick="return confirm('Remove from wishlist?')"><i class="fas fa-times"></i></a>
                <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                <div class="info">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="price"><?php echo formatPrice($product['price']); ?></p>
                    <a href="product-detail.php?slug=<?php echo $product['slug']; ?>" class="btn">View Product</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <footer class="footer"><div class="container"><p>&copy; 2025 Style Rwanda. All rights reserved.</p></div></footer>
</body>
</html>