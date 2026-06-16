<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$email = 'egreyerdi66@gmail.com';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

echo "<h1>Admin Check</h1>";
if ($user) {
    echo "✅ User found!<br>";
    echo "Name: " . $user['name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    echo "Password hash: " . $user['password'] . "<br>";
} else {
    echo "❌ User not found!";
}
?>