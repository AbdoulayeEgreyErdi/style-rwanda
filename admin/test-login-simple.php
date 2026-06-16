<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$email = 'egreyerdi66@gmail.com';
$password = 'Admin2026';

echo "<h1>Simple Login Test</h1>";

// Check user
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    echo "✅ User found!<br>";
    echo "Name: " . $user['name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    
    if (password_verify($password, $user['password'])) {
        echo "✅ Password is CORRECT!<br>";
        echo "You should be able to login.<br>";
        echo "<a href='login.php'>Go to Login Page</a>";
    } else {
        echo "❌ Password is INCORRECT!<br>";
        echo "Resetting password...<br>";
        
        $new_hash = password_hash('Admin2026', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$new_hash, $email]);
        echo "✅ Password reset to 'Admin2026'<br>";
        echo "<a href='login.php'>Try Login Now</a>";
    }
} else {
    echo "❌ User not found!";
}
?>