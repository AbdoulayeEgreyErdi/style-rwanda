<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: products.php?success=Product deleted');
    exit;
}

// Get all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .admin-container { display: flex; }
        .admin-sidebar { width: 280px; background: #000; color: white; min-height: 100vh; }
        .sidebar-header { padding: 2rem; text-align: center; border-bottom: 1px solid #333; }
        .sidebar-header h3 { color: #D4AF37; }
        .sidebar-nav a { display: flex; align-items: center; gap: 1rem; padding: 1rem 2rem; color: white; text-decoration: none; }
        .sidebar-nav a:hover { background: #D4AF37; color: #000; }
        .admin-main { flex: 1; padding: 2rem; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .btn-primary { background: #D4AF37; color: #000; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #dc3545; color: white; padding: 0.3rem 0.8rem; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 0.8rem; }
        .btn-edit { background: #D4AF37; color: #000; padding: 0.3rem 0.8rem; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 0.8rem; }
        .data-table { width: 100%; background: white; border-collapse: collapse; border-radius: 10px; overflow: hidden; }
        .data-table th { background: #000; color: white; padding: 1rem; text-align: left; }
        .data-table td { padding: 1rem; border-bottom: 1px solid #eee; }
        .product-image { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        @media (max-width: 768px) {
            .admin-sidebar { width: 80px; }
            .sidebar-header h3, .sidebar-header p, .sidebar-nav a span { display: none; }
            .admin-main { padding: 1rem; }
            .data-table { font-size: 0.7rem; }
            .data-table th, .data-table td { padding: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header"><h3>Style Rwanda</h3><p>Admin Panel</p></div>
            <nav class="sidebar-nav">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a>
                <a href="products.php" class="active"><i class="fas fa-box"></i> <span>Products</span></a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </nav>
        </aside>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Manage Products</h1>
                <a href="add-product.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Product</a>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                        <tr><td colspan="8" style="text-align: center;">No products found</td></tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><img src="<?php echo $product['image_url']; ?>" class="product-image" alt="<?php echo $product['name']; ?>"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $product['category']; ?></td>
                                <td><?php echo formatPrice($product['price']); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td><?php echo $product['is_featured'] ? '✅ Yes' : '❌ No'; ?></td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="?delete=<?php echo $product['id']; ?>" class="btn-danger" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>