<?php
require_once 'includes/email_simple.php';

echo "<h1>Testing Email Functions</h1>";

// Test welcome email
$result = sendWelcomeEmail('Test User', 'egreyerdi66@gmail.com');
echo "Welcome Email: " . ($result ? '✅ Sent' : '❌ Failed') . "<br>";

// Test order status update
$order = ['order_number' => 'TEST-001'];
$result = sendOrderStatusUpdateEmail($order, 'pending', 'shipped', 'egreyerdi66@gmail.com', 'Test User');
echo "Status Update Email: " . ($result ? '✅ Sent' : '❌ Failed') . "<br>";
?>