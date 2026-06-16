<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Delete product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header('Location: products.php?success=Product deleted');
    exit;
}

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Style Rwanda Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        
        .sidebar { width: 280px; background: #000; color: white; position: fixed; height: 100%; left: 0; top: 0; }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid #222; }
        .sidebar-header h2 { color: #D4AF37; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 15px; padding: 12px 25px; color: #ccc; text-decoration: none; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: #D4AF37; color: #000; }
        .main-content { margin-left: 280px; padding: 25px; }
        
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: white; padding: 15px 25px; border-radius: 12px; }
        .top-bar h1 { font-size: 22px; }
        .btn-add { background: #D4AF37; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 500; }
        
        .alert { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        
        .table-container { background: white; border-radius: 16px; overflow-x: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th { text-align: left; padding: 15px; background: #f8f9fa; font-weight: 600; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: middle; }
        
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .btn-edit { background: #D4AF37; color: #000; padding: 5px 12px; text-decoration: none; border-radius: 5px; font-size: 11px; display: inline-block; margin-right: 5px; }
        .btn-delete { background: #dc3545; color: white; padding: 5px 12px; text-decoration: none; border-radius: 5px; font-size: 11px; display: inline-block; }
        
        @media (max-width: 1024px) { .sidebar { width: 80px; } .sidebar-header h2, .sidebar-nav a span { display: none; } .sidebar-nav a { justify-content: center; } .main-content { margin-left: 80px; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h2>Style Rwanda</h2></div>
        <nav class="sidebar-nav">
            <a href="index.php"><i class="fas fa-tachometer-alt"></i><span> Dashboard</span></a>
            <a href="orders.php"><i class="fas fa-shopping-cart"></i><span> Orders</span></a>
            <a href="products.php" class="active"><i class="fas fa-box"></i><span> Products</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Manage Products</h1>
            <a href="add-product.php" class="btn-add"><i class="fas fa-plus"></i> Add New Product</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><img src="<?php echo $product['image_url']; ?>" class="product-img"></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo $product['category']; ?></td>
                        <td><?php echo number_format($product['price'], 0, ',', '.'); ?> RWF</td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><?php echo $product['is_featured'] ? '✅ Yes' : '❌ No'; ?></td>
                        <td>
                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>