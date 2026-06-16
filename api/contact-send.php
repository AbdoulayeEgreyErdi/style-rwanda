<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/email_simple.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$name = isset($data['name']) ? sanitizeInput($data['name']) : '';
$email = isset($data['email']) ? sanitizeInput($data['email']) : '';
$subject = isset($data['subject']) ? sanitizeInput($data['subject']) : '';
$message = isset($data['message']) ? sanitizeInput($data['message']) : '';

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Please fill all required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
    exit;
}

// Send email to admin
$admin_subject = "New Contact Message from " . $name;
$admin_message = "
<h2>New Contact Message</h2>
<p><strong>Name:</strong> $name</p>
<p><strong>Email:</strong> $email</p>
<p><strong>Subject:</strong> " . ($subject ?: 'No subject') . "</p>
<p><strong>Message:</strong></p>
<p>" . nl2br(htmlspecialchars($message)) . "</p>
<p><strong>Date:</strong> " . date('F j, Y g:i A') . "</p>
";

$admin_sent = sendEmail(ADMIN_EMAIL, $admin_subject, $admin_message);

// Send auto-reply to customer
$reply_subject = "Thank you for contacting Style Rwanda";
$reply_message = "
<h2>Thank You for Contacting Us!</h2>
<p>Dear $name,</p>
<p>We have received your message and will get back to you within 24 hours.</p>
<p>Thank you for shopping with Style Rwanda!</p>
";

$reply_sent = sendEmail($email, $reply_subject, $reply_message);

if ($admin_sent || $reply_sent) {
    echo json_encode(['success' => true, 'message' => 'Your message has been sent. We\'ll get back to you soon!']);
} else {
    echo json_encode(['success' => false, 'error' => 'Message sent but email notification failed. We will still review your message.']);
}
?>