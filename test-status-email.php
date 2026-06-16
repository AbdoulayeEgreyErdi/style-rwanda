<?php
require_once 'includes/email_simple.php';

$result = sendOrderStatusUpdateEmail(
    ['order_number' => 'TEST-ORDER-001'],
    'pending',
    'shipped',
    'egreyerdi66@gmail.com',  // Change to your email
    'Test Customer'
);

if ($result) {
    echo "✅ Email sent successfully! Check your inbox.";
} else {
    echo "❌ Email failed. Check error log.";
}
?>