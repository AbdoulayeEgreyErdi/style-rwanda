<?php
/**
 * Email System using PHPMailer - English Only
 */

// Ensure constants are defined
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost:8081/style-rwanda');
}
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Style Rwanda');
}
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'egreyerdi66@gmail.com');
}

// Files are directly in the phpmailer folder
require_once __DIR__ . '/../phpmailer/Exception.php';
require_once __DIR__ . '/../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'egreyerdi66@gmail.com';
        $mail->Password   = 'fhfy wfiv evmy avtg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('egreyerdi66@gmail.com', SITE_NAME);
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

// ========== WELCOME EMAIL ==========
function sendWelcomeEmail($name, $email) {
    $subject = "Welcome to Style Rwanda!";
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
            .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
            .content { padding: 30px; }
            .btn { background: #D4AF37; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Style Rwanda</h1>
            </div>
            <div class='content'>
                <h2>Welcome " . htmlspecialchars($name) . "!</h2>
                <p>Thank you for registering with Style Rwanda. We are excited to have you on board!</p>
                <p>You can now:</p>
                <ul>
                    <li>Browse our premium collection</li>
                    <li>Save items to your wishlist</li>
                    <li>Track your orders</li>
                </ul>
                <p style='text-align: center;'>
                    <a href='" . SITE_URL . "/shop.php' class='btn'>Start Shopping →</a>
                </p>
                <p>Best regards,<br>The Style Rwanda Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Style Rwanda. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($email, $subject, $message);
}

// ========== ORDER STATUS UPDATE EMAIL ==========
function sendOrderStatusUpdateEmail($order, $old_status, $new_status, $customer_email, $customer_name) {
    
    $status_messages = [
        'processing' => [
            'subject' => 'Your order is being processed',
            'message' => 'Great news! Your order has been confirmed and is now being processed. We will notify you when it is shipped.'
        ],
        'shipped' => [
            'subject' => 'Your order has been shipped',
            'message' => 'Your order is on the way! You can track your delivery using the button below.'
        ],
        'delivered' => [
            'subject' => 'Your order has been delivered',
            'message' => 'Your order has been delivered. We hope you love your purchase!'
        ],
        'cancelled' => [
            'subject' => 'Your order has been cancelled',
            'message' => 'Your order has been cancelled. If you have any questions, please contact our support team.'
        ]
    ];
    
    $info = $status_messages[$new_status];
    if (!$info) {
        $info = [
            'subject' => 'Your order status has been updated',
            'message' => 'Your order status has been updated to ' . ucfirst($new_status)
        ];
    }
    
    $badge_colors = [
        'processing' => '#17a2b8',
        'shipped' => '#007bff',
        'delivered' => '#28a745',
        'cancelled' => '#dc3545'
    ];
    $badge_color = isset($badge_colors[$new_status]) ? $badge_colors[$new_status] : '#6c757d';
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
            .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
            .content { padding: 30px; }
            .status-box { background: #f0f0f0; padding: 15px; border-radius: 8px; text-align: center; margin: 15px 0; }
            .old-status { color: #999; text-decoration: line-through; }
            .new-status { color: $badge_color; font-weight: bold; font-size: 18px; }
            .arrow { font-size: 20px; margin: 0 10px; }
            .btn { background: #D4AF37; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Style Rwanda</h1>
            </div>
            <div class='content'>
                <h2>Order Status Update</h2>
                <p>Dear " . htmlspecialchars($customer_name) . ",</p>
                
                <div class='status-box'>
                    <span class='old-status'>" . ucfirst($old_status) . "</span>
                    <span class='arrow'>→</span>
                    <span class='new-status'>" . ucfirst($new_status) . "</span>
                </div>
                
                <p>" . $info['message'] . "</p>
                <p><strong>Order Number:</strong> " . $order['order_number'] . "</p>
                
                <p style='text-align: center; margin-top: 20px;'>
                    <a href='" . SITE_URL . "/order-track.php?order=" . $order['order_number'] . "' class='btn'>Track Your Order →</a>
                </p>
                <p>Thank you for shopping with Style Rwanda!</p>
                <p>Best regards,<br>The Style Rwanda Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Style Rwanda. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($customer_email, $info['subject'] . " - Order #" . $order['order_number'], $message);
}

// ========== NEWSLETTER FUNCTIONS ==========

function sendNewSubscriberNotification($subscriber_email) {
    $subject = "New Newsletter Subscriber - Style Rwanda";
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
            .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
            .content { padding: 30px; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Style Rwanda</h1>
            </div>
            <div class='content'>
                <h2>New Newsletter Subscriber! 🎉</h2>
                <p><strong>Email:</strong> " . htmlspecialchars($subscriber_email) . "</p>
                <p><strong>Subscribed on:</strong> " . date('F j, Y g:i A') . "</p>
                <p>You can view all subscribers in the admin panel.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Style Rwanda</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail(ADMIN_EMAIL, $subject, $message);
}

function sendNewProductNotificationToSubscribers($product_name, $product_price, $product_slug, $product_image) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT email FROM newsletter_subscribers WHERE status = 'active'");
    $stmt->execute();
    $subscribers = $stmt->fetchAll();
    
    if (empty($subscribers)) {
        return 0;
    }
    
    $subject = "New Product Alert: " . $product_name . " - Style Rwanda";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
            .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
            .content { padding: 30px; }
            .product-image { text-align: center; margin: 20px 0; }
            .product-image img { max-width: 200px; border-radius: 10px; }
            .btn { background: #D4AF37; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .price { font-size: 24px; color: #D4AF37; font-weight: bold; margin: 10px 0; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Style Rwanda</h1>
            </div>
            <div class='content'>
                <h2>New Arrival! 🆕</h2>
                <p>Check out our latest product:</p>
                
                <div class='product-image'>
                    <img src='" . $product_image . "' alt='" . htmlspecialchars($product_name) . "'>
                </div>
                
                <h3 style='text-align: center;'>" . htmlspecialchars($product_name) . "</h3>
                <div class='price' style='text-align: center;'>" . formatPrice($product_price) . "</div>
                
                <p style='text-align: center; margin-top: 20px;'>
                    <a href='" . SITE_URL . "/product-detail.php?slug=" . $product_slug . "' class='btn'>View Product →</a>
                </p>
                <p>Thank you for being a valued subscriber!</p>
                <p>Best regards,<br>The Style Rwanda Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Style Rwanda. All rights reserved.</p>
                <p><a href='" . SITE_URL . "/unsubscribe.php' style='color: #999;'>Unsubscribe</a></p>
            </div>
        </div>
    </body>
    </html>";
    
    $success_count = 0;
    foreach ($subscribers as $subscriber) {
        $personalized_message = str_replace('unsubscribe.php', 'unsubscribe.php?email=' . urlencode($subscriber['email']), $message);
        if (sendEmail($subscriber['email'], $subject, $personalized_message)) {
            $success_count++;
        }
    }
    
    return $success_count;
}

// ========== PASSWORD RESET FUNCTIONS ==========

function sendPasswordResetEmail($email, $name, $reset_token) {
    $reset_link = SITE_URL . "/reset-password.php?token=" . $reset_token;
    
    $subject = "Password Reset Request - Style Rwanda";
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
            .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
            .content { padding: 30px; }
            .btn { background: #D4AF37; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 15px 0; font-size: 12px; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Style Rwanda</h1>
            </div>
            <div class='content'>
                <h2>Password Reset Request 🔐</h2>
                <p>Dear " . htmlspecialchars($name) . ",</p>
                <p>We received a request to reset your password for your Style Rwanda account.</p>
                
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . $reset_link . "' class='btn'>Reset My Password →</a>
                </p>
                
                <p>If you did not request this, please ignore this email. The link will expire in 1 hour.</p>
                
                <div class='warning'>
                    <strong>⚠️ Security Tip:</strong> Never share this link with anyone.
                </div>
                
                <p>Best regards,<br>The Style Rwanda Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Style Rwanda. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($email, $subject, $message);
}

function sendPasswordResetConfirmationEmail($email, $name) {
    $subject = "Your password has been changed - Style Rwanda";
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
            .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
            .content { padding: 30px; }
            .btn { background: #D4AF37; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Style Rwanda</h1>
            </div>
            <div class='content'>
                <h2>Password Changed Successfully ✅</h2>
                <p>Dear " . htmlspecialchars($name) . ",</p>
                <p>Your password has been successfully changed.</p>
                <p>If you did not make this change, please contact our support team immediately.</p>
                
                <p style='text-align: center; margin-top: 20px;'>
                    <a href='" . SITE_URL . "/account.php' class='btn'>Login to Your Account →</a>
                </p>
                <p>Best regards,<br>The Style Rwanda Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Style Rwanda. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($email, $subject, $message);
}
?>