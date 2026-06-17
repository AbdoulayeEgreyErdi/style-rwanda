<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Include email functions
if (file_exists('includes/email_simple.php')) {
    require_once 'includes/email_simple.php';
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Send email to admin
        $admin_subject = "New Contact Message from " . $name;
        
        $admin_message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
                .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
                .content { padding: 30px; }
                .info-box { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .message-box { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 3px solid #D4AF37; }
                .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .btn { background: #D4AF37; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Style Rwanda</h1>
                </div>
                <div class='content'>
                    <h2>New Contact Message 📧</h2>
                    
                    <div class='info-box'>
                        <p><strong>👤 Name:</strong> " . htmlspecialchars($name) . "</p>
                        <p><strong>📧 Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>📅 Date:</strong> " . date('F j, Y g:i A') . "</p>
                        " . (!empty($subject) ? "<p><strong>📌 Subject:</strong> " . htmlspecialchars($subject) . "</p>" : "") . "
                    </div>
                    
                    <div class='message-box'>
                        <p><strong>💬 Message:</strong></p>
                        <p style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='mailto:" . htmlspecialchars($email) . "' class='btn'>Reply to Customer →</a>
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Style Rwanda. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Send email to admin
        $admin_email_sent = false;
        if (function_exists('sendEmail')) {
            $admin_email_sent = sendEmail(ADMIN_EMAIL, $admin_subject, $admin_message);
        } else {
            // Fallback to mail() function
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . htmlspecialchars($email) . "\r\n";
            $headers .= "Reply-To: " . htmlspecialchars($email) . "\r\n";
            $admin_email_sent = mail(ADMIN_EMAIL, $admin_subject, $admin_message, $headers);
        }
        
        // Send auto-reply to customer
        $reply_subject = "Thank you for contacting Style Rwanda";
        $reply_message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; border: 1px solid #eee; }
                .header { background: #000; color: #D4AF37; padding: 20px; text-align: center; }
                .content { padding: 30px; }
                .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .btn { background: #D4AF37; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Style Rwanda</h1>
                </div>
                <div class='content'>
                    <h2>Thank You for Contacting Us! 🙏</h2>
                    <p>Dear " . htmlspecialchars($name) . ",</p>
                    <p>Thank you for reaching out to Style Rwanda. We have received your message and will get back to you within 24 hours.</p>
                    
                    <div class='info-box' style='background:#f9f9f9; padding:15px; border-radius:8px; margin:15px 0;'>
                        <p><strong>Your Message:</strong></p>
                        <p style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    
                    <p>In the meantime, you can:</p>
                    <ul>
                        <li>📦 Track your orders in your account</li>
                        <li>🛍️ Browse our latest collection</li>
                        <li>📱 Follow us on social media for updates</li>
                    </ul>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='" . SITE_URL . "/shop.php' class='btn'>Continue Shopping →</a>
                    </p>
                    <p>Best regards,<br>The Style Rwanda Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Style Rwanda. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $reply_sent = false;
        if (function_exists('sendEmail')) {
            $reply_sent = sendEmail($email, $reply_subject, $reply_message);
        } else {
            $headers_reply = "MIME-Version: 1.0\r\n";
            $headers_reply .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers_reply .= "From: Style Rwanda <" . ADMIN_EMAIL . ">\r\n";
            $reply_sent = mail($email, $reply_subject, $reply_message, $headers_reply);
        }
        
        if ($admin_email_sent || $reply_sent) {
            $success = "Thank you for your message! We'll get back to you within 24 hours.";
        } else {
            $error = "Message sent but email notification failed. We'll still review your message.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; color: #333; }
        
        .navbar { background: #000; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .nav-logo a { color: #D4AF37; font-size: 1.8rem; font-weight: 700; text-decoration: none; font-family: 'Playfair Display', serif; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; align-items: center; flex-wrap: wrap; }
        .nav-menu a { color: #fff; text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .nav-menu a:hover { color: #D4AF37; }
        .cart-link { position: relative; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #D4AF37; color: #000; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; }
        .nav-toggle { display: none; font-size: 1.5rem; color: #fff; cursor: pointer; }
        
        .page-header { background: linear-gradient(135deg, #000 0%, #1a1a1a 100%); color: white; padding: 60px 0; text-align: center; }
        .page-header h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .page-header p { opacity: 0.8; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        
        .contact-card { background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .contact-card h2 { margin-bottom: 1rem; color: #000; }
        .contact-card h3 { margin: 1.5rem 0 1rem; font-size: 1.1rem; }
        
        .info-item { display: flex; align-items: center; gap: 1rem; padding: 0.8rem 0; border-bottom: 1px solid #eee; }
        .info-item i { width: 30px; color: #D4AF37; font-size: 1.2rem; }
        
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; transition: border-color 0.3s; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #D4AF37; }
        
        .btn { background: #D4AF37; color: #000; padding: 0.8rem 2rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn:hover { background: #000; color: #D4AF37; transform: translateY(-2px); }
        
        .success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .error { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        
        .footer { background: #111; color: #999; padding: 2rem 0; text-align: center; margin-top: 2rem; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; background: #28a745; color: white; border-radius: 8px; z-index: 1000; display: none; }
        .toast.error { background: #dc3545; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; gap: 1rem; }
            .nav-menu.active { display: flex; }
            .contact-grid { grid-template-columns: 1fr; }
            .page-header h1 { font-size: 1.8rem; }
            .container { padding: 1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo"><a href="/">Style Rwanda</a></div>
            <div class="nav-toggle" id="navToggle"><i class="fas fa-bars"></i></div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="/">Home</a></li>
                <li><a href="/shop.php">Shop</a></li>
                <li><a href="/shop.php?new=1">New Arrivals</a></li>
                <li><a href="/contact.php">Contact</a></li>
                <li><a href="/account.php"><i class="fas fa-user"></i> Account</a></li>
                <li class="cart-link"><a href="/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count"><?php echo getCartCount(); ?></span></a></li>
            </ul>
        </div>
    </nav>

    <div class="page-header">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you</p>
    </div>

    <div class="container">
        <div class="contact-grid">
            <div class="contact-card">
                <h2>Send us a message</h2>
                <?php if ($success): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" id="contactForm">
                    <div class="form-group">
                        <label>Your Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Subject (Optional)</label>
                        <input type="text" name="subject">
                    </div>
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" rows="5" required>
                    </div>
                    <button type="submit" class="btn" id="submitBtn">Send Message</button>
                </form>
            </div>
            
            <div class="contact-card">
                <h2>Contact Information</h2>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>KG 123 St, Kigali, Rwanda</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <span>+250 788 123 456</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <span>info@style.rw</span>
                </div>
                <div class="info-item">
                    <i class="fab fa-whatsapp"></i>
                    <span><?php echo WHATSAPP_NUMBER; ?></span>
                </div>
                
                <h3>Store Hours</h3>
                <div class="info-item">
                    <i class="far fa-clock"></i>
                    <span>Monday - Friday: 9am - 6pm</span>
                </div>
                <div class="info-item">
                    <i class="far fa-clock"></i>
                    <span>Saturday: 10am - 4pm</span>
                </div>
                <div class="info-item">
                    <i class="far fa-clock"></i>
                    <span>Sunday: Closed</span>
                </div>
                
                <h3>Follow Us</h3>
                <div class="info-item" style="gap: 1.5rem;">
                    <a href="#" style="color: #D4AF37; font-size: 1.2rem;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: #D4AF37; font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #D4AF37; font-size: 1.2rem;"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Style Rwanda. All rights reserved.</p>
        </div>
    </footer>
    
    <div id="toast" class="toast"></div>

    <script>
        // Mobile menu toggle
        document.getElementById('navToggle')?.addEventListener('click', () => {
            document.getElementById('navMenu')?.classList.toggle('active');
        });
        
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
        
        document.getElementById('contactForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            
            const formData = new FormData(e.target);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            try {
                const response = await fetch('/api/contact-send.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    e.target.reset();
                } else {
                    showToast(result.error, 'error');
                }
            } catch (error) {
                showToast('Network error. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>