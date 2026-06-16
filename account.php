<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Include email functions - USE THE CORRECT FILE
if (file_exists('includes/email_simple.php')) {
    require_once 'includes/email_simple.php';
}

$error = '';
$success = '';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered. Please login.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
                $stmt->execute([$name, $email, $phone, $hashed_password]);
            } catch (PDOException $e) {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
                $stmt->execute([$name, $email, $hashed_password]);
            }
            $success = 'Registration successful! Please login.';
            
            // 📧 SEND WELCOME EMAIL
            if (function_exists('sendWelcomeEmail')) {
                $email_sent = sendWelcomeEmail($name, $email);
                if ($email_sent) {
                    $success .= ' A welcome email has been sent to your inbox.';
                }
            }
            
            $active_tab = 'login';
        }
    }
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            if (function_exists('loadCartFromDatabase')) {
                $saved_cart = loadCartFromDatabase($user['id']);
                if (!empty($saved_cart) && empty($_SESSION['cart'])) {
                    $_SESSION['cart'] = $saved_cart;
                }
            }
            
            header('Location: account.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    if (function_exists('saveCartToDatabase') && isset($_SESSION['user_id']) && isset($_SESSION['cart'])) {
        saveCartToDatabase($_SESSION['user_id'], $_SESSION['cart']);
    }
    session_destroy();
    header('Location: account.php');
    exit;
}

$is_logged_in = isset($_SESSION['user_id']);

// Function to get order status badge with icon
function getOrderStatusBadge($status) {
    switch($status) {
        case 'pending':
            return '<span class="status-badge status-pending"><i class="fas fa-clock"></i> Pending</span>';
        case 'processing':
            return '<span class="status-badge status-processing"><i class="fas fa-cogs"></i> Processing</span>';
        case 'shipped':
            return '<span class="status-badge status-shipped"><i class="fas fa-truck"></i> Shipped</span>';
        case 'delivered':
            return '<span class="status-badge status-delivered"><i class="fas fa-check-circle"></i> Delivered</span>';
        case 'cancelled':
            return '<span class="status-badge status-cancelled"><i class="fas fa-times-circle"></i> Cancelled</span>';
        default:
            return '<span class="status-badge status-pending">' . ucfirst($status) . '</span>';
    }
}

// Function to get payment status badge
function getPaymentStatusBadge($status) {
    switch($status) {
        case 'pending':
            return '<span class="payment-badge payment-pending"><i class="fas fa-hourglass-half"></i> Pending</span>';
        case 'verified':
            return '<span class="payment-badge payment-verified"><i class="fas fa-check-circle"></i> Verified</span>';
        case 'failed':
            return '<span class="payment-badge payment-failed"><i class="fas fa-times-circle"></i> Failed</span>';
        default:
            return '<span class="payment-badge">' . ucfirst($status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Style Rwanda</title>
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
        
        .container { max-width: 1000px; margin: 0 auto; padding: 2rem; }
        .account-card { background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .tabs { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid #eee; }
        .tab { padding: 0.5rem 1rem; text-decoration: none; color: #666; transition: all 0.3s; }
        .tab.active { color: #D4AF37; border-bottom: 2px solid #D4AF37; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; }
        .form-group input:focus { outline: none; border-color: #D4AF37; }
        .btn-primary { width: 100%; background: #D4AF37; color: #000; padding: 0.75rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 1rem; }
        .error { background: #f8d7da; color: #721c24; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; }
        .success { background: #d4edda; color: #155724; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; }
        .dashboard { text-align: center; }
        .dashboard h2 { color: #000; margin-bottom: 1rem; }
        .dashboard p { margin-bottom: 0.5rem; }
        .logout-btn { background: #dc3545; color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 1rem; }
        
        /* Orders Table Styles */
        .orders-section { margin-top: 2rem; text-align: left; }
        .orders-section h3 { margin-bottom: 1rem; color: #000; border-left: 3px solid #D4AF37; padding-left: 10px; }
        .orders-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .orders-table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; font-size: 13px; color: #555; }
        .orders-table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        .orders-table tr:hover { background: #fafafa; }
        
        /* Status Badges */
        .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #28a745; color: white; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .payment-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .payment-pending { background: #fff3cd; color: #856404; }
        .payment-verified { background: #d4edda; color: #155724; }
        .payment-failed { background: #f8d7da; color: #721c24; }
        
        .btn-sm { background: #D4AF37; color: #000; padding: 5px 12px; border-radius: 5px; text-decoration: none; font-size: 11px; font-weight: 500; display: inline-flex; align-items: center; gap: 5px; }
        .btn-sm:hover { background: #000; color: #D4AF37; }
        
        .no-orders { text-align: center; padding: 2rem; color: #888; }
        .no-orders i { font-size: 3rem; margin-bottom: 1rem; color: #ddd; }
        
        .order-timeline { display: flex; justify-content: space-between; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 10px; flex-wrap: wrap; }
        .timeline-step { text-align: center; flex: 1; position: relative; }
        .timeline-step .step-icon { width: 40px; height: 40px; background: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; color: #666; }
        .timeline-step.active .step-icon { background: #D4AF37; color: #000; }
        .timeline-step.completed .step-icon { background: #28a745; color: white; }
        .timeline-step .step-label { font-size: 10px; color: #666; }
        .timeline-step.active .step-label { color: #D4AF37; font-weight: 600; }
        
        .footer { background: #111; color: #999; padding: 2rem 0; text-align: center; margin-top: 2rem; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; gap: 1rem; }
            .nav-menu.active { display: flex; }
            .container { padding: 1rem; }
            .orders-table th, .orders-table td { padding: 8px; font-size: 11px; }
            .order-timeline { flex-direction: column; gap: 10px; }
            .timeline-step { display: flex; align-items: center; gap: 10px; }
            .timeline-step .step-icon { margin: 0; }
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
        <div class="account-card">
            <?php if ($is_logged_in): ?>
                <div class="dashboard">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                    <p><i class="fas fa-user-tag"></i> <?php echo ucfirst($_SESSION['user_role']); ?></p>
                    <a href="?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <p style="margin-top: 1rem;"><a href="/style-rwanda/admin/" style="color:#D4AF37;">Go to Admin Panel →</a></p>
                    <?php endif; ?>
                    
                    <!-- Orders Section -->
                    <div class="orders-section">
                        <h3><i class="fas fa-shopping-bag"></i> My Orders</h3>
                        <?php
                        // Get orders for this customer
                        $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_email = ? ORDER BY id DESC");
                        $stmt->execute([$_SESSION['user_email']]);
                        $orders = $stmt->fetchAll();
                        ?>
                        
                        <?php if (empty($orders)): ?>
                            <div class="no-orders">
                                <i class="fas fa-box-open"></i>
                                <p>You haven't placed any orders yet.</p>
                                <a href="shop.php" class="btn-sm" style="display: inline-block; margin-top: 1rem; padding: 10px 20px;">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table class="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Payment</th>
                                            <th>Order Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): 
                                            // Get item count for this order
                                            $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                                            $stmt2->execute([$order['id']]);
                                            $item_count = $stmt2->fetch()['count'];
                                        ?>
                                        <tr>
                                            <td><strong>#<?php echo $order['order_number']; ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $item_count; ?> item(s)</td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td><?php echo getPaymentStatusBadge($order['payment_status']); ?></td>
                                            <td><?php echo getOrderStatusBadge($order['order_status']); ?></td>
                                            <td>
                                                <a href="order-track.php?order=<?php echo $order['order_number']; ?>" class="btn-sm">
                                                    <i class="fas fa-eye"></i> Track
                                                </a>
                                            </tr>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="tabs">
                    <a href="?tab=login" class="tab <?php echo $active_tab == 'login' ? 'active' : ''; ?>">Login</a>
                    <a href="?tab=register" class="tab <?php echo $active_tab == 'register' ? 'active' : ''; ?>">Register</a>
                </div>
                
                <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
                <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
                
                <?php if ($active_tab == 'login'): ?>
                    <form method="POST">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required placeholder="your@email.com">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required placeholder="********">
                        </div>
                        <button type="submit" name="login" class="btn-primary">Login</button>
                    </form>
                    
                    <!-- FORGOT PASSWORD LINK - ADDED HERE -->
                    <p style="text-align: center; margin-top: 10px;">
                        <a href="forgot-password.php" style="color: #D4AF37; font-size: 12px;">Forgot Password?</a>
                    </p>
                    
                    <p style="text-align: center; margin-top: 1rem; font-size: 0.8rem;">
                        Don't have an account? <a href="?tab=register" style="color:#D4AF37;">Register here</a>
                    </p>
                <?php else: ?>
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" required placeholder="John Doe">
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" required placeholder="john@example.com">
                        </div>
                        <div class="form-group">
                            <label>Phone Number (Optional)</label>
                            <input type="tel" name="phone" placeholder="0788123456">
                        </div>
                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" required placeholder="Min 6 characters">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="confirm_password" required placeholder="Confirm password">
                        </div>
                        <button type="submit" name="register" class="btn-primary">Create Account</button>
                    </form>
                    <p style="text-align: center; margin-top: 1rem; font-size: 0.8rem;">
                        Already have an account? <a href="?tab=login" style="color:#D4AF37;">Login here</a>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Style Rwanda. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.getElementById('navToggle')?.addEventListener('click', () => {
            document.getElementById('navMenu')?.classList.toggle('active');
        });
    </script>
</body>
</html>