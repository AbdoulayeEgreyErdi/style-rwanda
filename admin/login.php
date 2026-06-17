<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Clear any existing session
                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['admin_logged_in'] = true;
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Admin user not found';
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
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #000 0%, #1a1a1a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-container { background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .login-container h1 { color: #D4AF37; font-size: 28px; margin-bottom: 5px; }
        .login-container p { color: #888; margin-bottom: 25px; font-size: 14px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #333; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
        .form-group input:focus { outline: none; border-color: #D4AF37; box-shadow: 0 0 0 3px rgba(212,175,55,0.1); }
        .btn { width: 100%; padding: 12px; background: #D4AF37; color: #000; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn:hover { background: #000; color: #D4AF37; transform: translateY(-2px); }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; border-left: 4px solid #dc3545; }
        .demo-info { margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #999; }
        .demo-info strong { color: #333; }
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