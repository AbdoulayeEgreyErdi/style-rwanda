<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

echo "<h1>Admin Login Test</h1>";

// Check if admin exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['egreyerdi66@gmail.com']);
$user = $stmt->fetch();

if ($user) {
    echo "✅ User found: " . $user['name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    
    // Test password
    $test_password = 'Admin2026';
    if (password_verify($test_password, $user['password'])) {
        echo "✅ Password 'Admin2026' is CORRECT!<br>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        echo "❌ Password 'Admin2026' is INCORRECT!<br>";
        echo "Updating password...<br>";
        
        $new_hash = password_hash('Admin2026', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$new_hash, 'egreyerdi66@gmail.com']);
        echo "✅ Password has been reset to 'Admin2026'<br>";
        echo "<a href='login.php'>Try Login Now</a>";
    }
} else {
    echo "❌ User not found! Creating admin...<br>";
    
    $hash = password_hash('Admin2026', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute(['Administrator', 'egreyerdi66@gmail.com', $hash]);
    echo "✅ Admin user created!<br>";
    echo "Email: egreyerdi66@gmail.com<br>";
    echo "Password: Admin2026<br>";
    echo "<a href='login.php'>Go to Login</a>";
}
?>