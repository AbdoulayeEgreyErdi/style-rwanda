<?php
require_once 'includes/config.php';

// Simple mail test
$to = 'egreyerdi66@gmail.com';
$subject = 'Test Email from Style Rwanda';
$message = '<h1>Test Successful!</h1><p>Your email system is working.</p>';
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Style Rwanda <noreply@stylerwanda.com>\r\n";

$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "✅ Email sent to $to! Check your inbox/spam.";
} else {
    echo "❌ Email failed. XAMPP needs SMTP configuration.";
}
?>