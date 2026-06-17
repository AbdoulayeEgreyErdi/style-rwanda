<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Check if password matches
            if (password_verify($password, $user['password'])) {
                // Check if user is admin
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Access denied. Not an admin user.';
                }
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #000 0%, #1a1a1a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { background: white; padding: 40px; border-radius: 10px; width: 100%; max-width: 400px; text-align: center; }
        .login-container h1 { color: #D4AF37; margin-bottom: 10px; }
        .login-container p { color: #666; margin-bottom: 20px; font-size: 14px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #D4AF37; }
        .btn { width: 100%; padding: 12px; background: #D4AF37; color: #000; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #000; color: #D4AF37; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; }
        .demo-info { margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Style Rwanda</h1>
        <p>Admin Login</p>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="admin@style.rw" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" value="Admin2026" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="demo-info">
            <strong>Demo Credentials:</strong><br>
            Email: admin@style.rw<br>
            Password: Admin2026
        </div>
    </div>
</body>
</html>