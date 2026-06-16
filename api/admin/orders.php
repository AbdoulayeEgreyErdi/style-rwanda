<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Load email functions - USE REQUIRED PATH
require_once '../includes/email_simple.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle order status update
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    // Get order details before update
    $stmt = $pdo->prepare("SELECT order_status, customer_email, customer_name, order_number FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order_data = $stmt->fetch();
    $old_status = $order_data['order_status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        
        // SEND EMAIL IF STATUS CHANGED
        if ($old_status != $new_status) {
            $order_info = [
                'order_number' => $order_data['order_number'],
                'id' => $order_id
            ];
            
            // Call email function
            $email_sent = sendOrderStatusUpdateEmail($order_info, $old_status, $new_status, $order_data['customer_email'], $order_data['customer_name']);
            
            // Set success message based on email result
            if ($email_sent) {
                $msg = "Order status updated from " . ucfirst($old_status) . " to " . ucfirst($new_status) . ". Customer has been notified via email.";
            } else {
                $msg = "Order status updated from " . ucfirst($old_status) . " to " . ucfirst($new_status) . ". (Email notification failed)";
            }
            header('Location: orders.php?success=' . urlencode($msg));
        } else {
            header('Location: orders.php?success=Order status unchanged');
        }
        exit;
    } else {
        header('Location: orders.php?error=Failed to update status');
        exit;
    }
}

// Handle payment verification
if (isset($_POST['verify_payment'])) {
    $order_id = (int)$_POST['order_id'];
    $transaction_id = trim($_POST['transaction_id']);
    
    if (empty($transaction_id)) {
        header('Location: orders.php?error=Transaction ID is required');
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT customer_email, customer_name, order_number FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order_data = $stmt->fetch();
    
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'verified', order_status = 'processing', transaction_id = ? WHERE id = ?");
    if ($stmt->execute([$transaction_id, $order_id])) {
        
        // Send email
        $order_info = ['order_number' => $order_data['order_number'], 'id' => $order_id];
        sendOrderStatusUpdateEmail($order_info, 'pending', 'processing', $order_data['customer_email'], $order_data['customer_name']);
        
        header('Location: orders.php?success=Payment verified! Order is now processing. Customer notified.');
        exit;
    } else {
        header('Location: orders.php?error=Failed to verify payment');
        exit;
    }
}

// Handle order rejection
if (isset($_POST['reject_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    $stmt = $pdo->prepare("SELECT customer_email, customer_name, order_number FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order_data = $stmt->fetch();
    
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed', order_status = 'cancelled' WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        
        // Send email
        $order_info = ['order_number' => $order_data['order_number'], 'id' => $order_id];
        sendOrderStatusUpdateEmail($order_info, 'pending', 'cancelled', $order_data['customer_email'], $order_data['customer_name']);
        
        header('Location: orders.php?success=Order rejected and cancelled. Customer notified.');
        exit;
    } else {
        header('Location: orders.php?error=Failed to reject order');
        exit;
    }
}

// Get all orders
$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Style Rwanda</title>
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
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 12px; overflow: hidden; }
        th { background: #000; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .status-pending { background: #ffc107; color: #000; }
        .status-verified { background: #28a745; color: white; }
        .status-processing { background: #17a2b8; color: white; }
        .status-shipped { background: #007bff; color: white; }
        .status-delivered { background: #28a745; color: white; }
        .status-cancelled, .status-failed { background: #dc3545; color: white; }
        .btn-sm { padding: 5px 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin: 2px; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-primary { background: #D4AF37; color: #000; }
        select, input { padding: 5px; border-radius: 5px; border: 1px solid #ddd; }
        @media (max-width: 768px) { 
            .sidebar { width: 80px; } 
            .sidebar h3, .sidebar a span { display: none; } 
            .content { margin-left: 80px; } 
            table { font-size: 11px; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Style Rwanda</h3>
        <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
        <a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a>
        <a href="products.php"><i class="fas fa-box"></i> <span>Products</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </div>
    
    <div class="content">
        <h1>Manage Orders</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Transaction ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr style="<?php echo $order['payment_status'] == 'pending' ? 'background: #fffef5;' : ''; ?>">
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> RWF</td>
                        <td><span class="status-badge status-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                        <td><span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                        <td><?php echo $order['transaction_id'] ? $order['transaction_id'] : '-'; ?></td>
                        <td>
                            <?php if ($order['payment_status'] == 'pending'): ?>
                                <form method="POST" style="display: inline-flex; gap: 5px; align-items: center; flex-wrap: wrap;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="text" name="transaction_id" placeholder="Transaction ID" required style="width: 100px; padding: 4px;">
                                    <button type="submit" name="verify_payment" class="btn-sm btn-success">Verify</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="reject_order" class="btn-sm btn-danger">Reject</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="padding: 4px;">
                                    <option value="">Update Status</option>
                                    <option value="pending" <?php if($order['order_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="processing" <?php if($order['order_status'] == 'processing') echo 'selected'; ?>>Processing</option>
                                    <option value="shipped" <?php if($order['order_status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                                    <option value="delivered" <?php if($order['order_status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                                    <option value="cancelled" <?php if($order['order_status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">No orders found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>