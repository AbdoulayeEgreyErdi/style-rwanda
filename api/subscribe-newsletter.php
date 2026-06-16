<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/email_simple.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if ($existing['status'] == 'active') {
            echo json_encode(['success' => false, 'error' => 'This email is already subscribed']);
            exit;
        } else {
            // Re-subscribe
            $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'active', subscribed_at = NOW() WHERE email = ?");
            $stmt->execute([$email]);
            echo json_encode(['success' => true, 'message' => 'Welcome back! You have been re-subscribed.']);
        }
    } else {
        // New subscription
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        echo json_encode(['success' => true, 'message' => 'Subscribed successfully! Check your email for updates.']);
    }
    
    // Send notification to admin
    if (function_exists('sendNewSubscriberNotification')) {
        sendNewSubscriberNotification($email);
    }
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'error' => 'This email is already subscribed']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
    }
}
?>