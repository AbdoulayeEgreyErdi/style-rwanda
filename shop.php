<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

$filters = ['category' => $category, 'search' => $search, 'sort' => $sort];
$products = getProducts($filters, $per_page, $offset);

// Get total count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE 1=1" . ($category ? " AND category = ?" : "") . ($search ? " AND (name LIKE ? OR description LIKE ?)" : ""));
$params = [];
if ($category) $params[] = $category;
if ($search) { $params[] = "%$search%"; $params[] = "%$search%"; }
$stmt->execute($params);
$total_products = $stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Get categories from database
$categories = $pdo->query("SELECT DISTINCT category FROM products")->fetchAll();

// Manually ensure Shoes category is included
$hasShoes = false;
foreach ($categories as $cat) {
    if ($cat['category'] == 'Shoes') {
        $hasShoes = true;
        break;
    }
}
if (!$hasShoes) {
    $categories[] = ['category' => 'Shoes'];
}

// Sort categories to put Shoes in correct order
usort($categories, function($a, $b) {
    $order = ['Men', 'Women', 'Shoes', 'Footwear', 'Accessories'];
    $posA = array_search($a['category'], $order);
    $posB = array_search($b['category'], $order);
    if ($posA === false) $posA = 999;
    if ($posB === false) $posB = 999;
    return $posA - $posB;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .navbar { background: #000; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo a { color: #D4AF37; font-size: 1.8rem; font-weight: 700; text-decoration: none; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; align-items: center; }
        .nav-menu a { color: #fff; text-decoration: none; font-weight: 500; }
        .nav-menu a:hover { color: #D4AF37; }
        .cart-link { position: relative; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #D4AF37; color: #000; border-radius: 50%; padding: 2px 6px; font-size: 12px; }
        .nav-toggle { display: none; font-size: 1.5rem; color: #fff; cursor: pointer; }
        
        .shop-header { background: #f5f5f5; padding: 3rem 0; text-align: center; }
        .shop-header h1 { font-size: 2.5rem; }
        
        .shop-layout { display: grid; grid-template-columns: 280px 1fr; gap: 2rem; margin: 2rem 0; }
        .shop-sidebar { background: #f9f9f9; padding: 1.5rem; border-radius: 10px; height: fit-content; position: sticky; top: 100px; }
        .filter-section { margin-bottom: 2rem; }
        .filter-section h3 { margin-bottom: 1rem; font-size: 1.1rem; }
        .filter-section ul { list-style: none; }
        .filter-section ul li { margin-bottom: 0.5rem; }
        .filter-section ul li a { color: #666; text-decoration: none; }
        .filter-section ul li a:hover { color: #D4AF37; }
        .price-inputs { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
        .price-inputs input { flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; }
        .product-card { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .product-card:hover { transform: translateY(-5px); }
        .product-image { height: 250px; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-info { padding: 1rem; text-align: center; }
        .product-info h3 { font-size: 1rem; margin-bottom: 0.5rem; }
        .product-price { color: #D4AF37; font-size: 1.1rem; font-weight: bold; margin-bottom: 0.5rem; }
        
        .btn { display: inline-block; padding: 8px 20px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn-primary { background: #D4AF37; color: #000; }
        .btn-primary:hover { background: #000; color: #D4AF37; }
        .btn-outline { border: 2px solid #D4AF37; color: #D4AF37; background: transparent; font-size: 0.8rem; padding: 5px 15px; }
        .btn-outline:hover { background: #D4AF37; color: #000; }
        
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin: 2rem 0; }
        .pagination a { padding: 8px 15px; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 5px; }
        .pagination a:hover, .pagination a.active { background: #D4AF37; color: #000; border-color: #D4AF37; }
        
        /* Category icon styling */
        .category-icon { margin-right: 8px; }
        .category-shoes { color: #D4AF37; }
        
        .footer { background: #111; color: #999; padding: 2rem 0 1rem; margin-top: 2rem; text-align: center; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; }
            .nav-menu.active { display: flex; }
            .shop-layout { grid-template-columns: 1fr; }
            .shop-sidebar { position: static; }
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
                <li class="cart-link"><a href="/style-rwanda/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count"><?php echo getCartCount(); ?></span></a></li>
            </ul>
        </div>
    </nav>

    <div class="shop-header"><div class="container"><h1>Our Collection</h1><p>Discover premium fashion pieces</p></div></div>

    <div class="container">
        <div class="shop-layout">
            <aside class="shop-sidebar">
                <div class="filter-section"><h3>Search</h3><form method="GET"><input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></form></div>
                
                <div class="filter-section">
                    <h3>Categories</h3>
                    <ul>
                        <li><a href="?"><i class="fas fa-th-large category-icon"></i> All</a></li>
                        <?php foreach($categories as $cat): ?>
                            <?php if($cat['category'] == 'Shoes'): ?>
                                <li><a href="?category=<?php echo urlencode($cat['category']); ?>"><i class="fas fa-shoe-prints category-icon category-shoes"></i> 👟 <?php echo $cat['category']; ?></a></li>
                            <?php elseif($cat['category'] == 'Men'): ?>
                                <li><a href="?category=<?php echo urlencode($cat['category']); ?>"><i class="fas fa-male category-icon"></i> <?php echo $cat['category']; ?></a></li>
                            <?php elseif($cat['category'] == 'Women'): ?>
                                <li><a href="?category=<?php echo urlencode($cat['category']); ?>"><i class="fas fa-female category-icon"></i> <?php echo $cat['category']; ?></a></li>
                            <?php elseif($cat['category'] == 'Accessories'): ?>
                                <li><a href="?category=<?php echo urlencode($cat['category']); ?>"><i class="fas fa-gem category-icon"></i> <?php echo $cat['category']; ?></a></li>
                            <?php else: ?>
                                <li><a href="?category=<?php echo urlencode($cat['category']); ?>"><?php echo $cat['category']; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
            <div>
                <div class="product-grid">
                    <?php if (empty($products)): ?><p style="text-align:center; grid-column:1/-1;">No products found.</p><?php endif; ?>
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image"><img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>"></div>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                            <a href="/style-rwanda/product-detail.php?slug=<?php echo $product['slug']; ?>" class="btn btn-outline">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($total_pages > 1): ?>
                <div class="pagination"><?php for($i=1;$i<=$total_pages;$i++): ?><a href="?page=<?php echo $i; ?><?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="<?php echo $page==$i?'active':''; ?>"><?php echo $i; ?></a><?php endfor; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer"><div class="container"><p>&copy; 2025 Style Rwanda. All rights reserved.</p></div></footer>

    <script>document.getElementById('navToggle')?.addEventListener('click', () => { document.getElementById('navMenu')?.classList.toggle('active'); });</script>
</body>
</html>