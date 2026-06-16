<?php
require_once 'includes/email_simple.php';

$result = sendEmail('egreyerdi66@gmail.com', 'Test from Style Rwanda', '<h1>✅ Success!</h1><p>Your email system is working perfectly.</p>');

if ($result) {
    echo "✅ Email sent successfully! Check your inbox (or spam folder).";
} else {
    echo "❌ Email failed. Check error log.";
}
?>