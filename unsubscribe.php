<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$email = isset($_GET['email']) ? urldecode($_GET['email']) : '';
$message = '';

if ($email) {
    $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed' WHERE email = ?");
    if ($stmt->execute([$email])) {
        $message = '<div class="success">✅ You have been successfully unsubscribed from our newsletter.</div>';
    } else {
        $message = '<div class="error">❌ Email not found or already unsubscribed.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { max-width: 500px; margin: 0 auto; padding: 2rem; }
        .card { background: white; border-radius: 16px; padding: 2rem; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 12px 25px; background: #D4AF37; color: #000; text-decoration: none; border-radius: 8px; margin-top: 20px; }
        h1 { color: #D4AF37; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Style Rwanda</h1>
            <?php echo $message; ?>
            <a href="<?php echo SITE_URL; ?>" class="btn">Continue Shopping</a>
        </div>
    </div>
</body>
</html>