<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Get statistics
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_payments = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'verified'")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// FIX: Get user counts
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// FIX #5: Low stock alert
$low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 5 AND stock > 0")->fetchColumn();
$out_of_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0")->fetchColumn();

$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .sidebar { width: 280px; background: #000; color: white; position: fixed; height: 100%; padding: 20px; }
        .sidebar h3 { color: #D4AF37; margin-bottom: 30px; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 12px; margin: 5px 0; border-radius: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #D4AF37; color: #000; }
        .content { margin-left: 280px; padding: 25px; }
        .stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 16px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { font-size: 28px; color: #D4AF37; }
        .stat-card p { color: #666; font-size: 13px; margin-top: 5px; }
        .stat-card small { font-size: 11px; color: #999; }
        .alert-warning { background: #fff3cd; color: #856404; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 12px; overflow: hidden; }
        th { background: #000; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .btn-sm { display: inline-block; padding: 5px 10px; background: #D4AF37; color: #000; text-decoration: none; border-radius: 5px; font-size: 12px; }
        @media (max-width: 1024px) { .stats { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { 
            .sidebar { width: 80px; } 
            .sidebar h3, .sidebar a span { display: none; } 
            .content { margin-left: 80px; } 
            .stats { grid-template-columns: repeat(2, 1fr); } 
        }
        @media (max-width: 480px) { .stats { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Style Rwanda</h3>
        <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
        <a href="orders.php"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a>
        <a href="products.php"><i class="fas fa-box"></i> <span>Products</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </div>
    
    <div class="content">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo $_SESSION['admin_name']; ?></p>
        
        <!-- Low Stock Alert -->
        <?php if ($low_stock > 0): ?>
        <div class="alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            ⚠️ <strong>Low Stock Alert:</strong> <?php echo $low_stock; ?> product(s) have low stock (less than 5 units)!
        </div>
        <?php endif; ?>
        
        <?php if ($out_of_stock > 0): ?>
        <div class="alert-warning">
            <i class="fas fa-times-circle"></i> 
            ❌ <strong>Out of Stock:</strong> <?php echo $out_of_stock; ?> product(s) are out of stock!
        </div>
        <?php endif; ?>
        
        <!-- Stats Cards - Now with 5 cards including Users -->
        <div class="stats">
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <h3><?php echo $total_orders; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h3><?php echo $pending_payments; ?></h3>
                <p>Pending Payments</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <h3><?php echo number_format($total_revenue ?? 0, 0, ',', '.'); ?> RWF</h3>
                <p>Revenue</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-box"></i>
                <h3><?php echo $total_products; ?></h3>
                <p>Products</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_customers; ?></h3>
                <p>Customers</p>
                <small><?php echo $total_users; ?> total users (<?php echo $total_admins; ?> admin)</small>
            </div>
        </div>
        
        <h2>Recent Orders</h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> RWF</td>
                        <td>
                            <span style="background: <?php echo $order['payment_status'] == 'pending' ? '#ffc107' : ($order['payment_status'] == 'verified' ? '#28a745' : '#dc3545'); ?>; color: <?php echo $order['payment_status'] == 'pending' ? '#000' : '#fff'; ?>; padding: 4px 12px; border-radius: 20px; font-size: 11px;">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span style="background: <?php echo $order['order_status'] == 'delivered' ? '#28a745' : ($order['order_status'] == 'cancelled' ? '#dc3545' : '#17a2b8'); ?>; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 11px;">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </td>
                        <td><a href="orders.php" class="btn-sm">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_orders)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">No orders yet</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>