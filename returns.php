<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns Policy - Style Rwanda</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #333; }
        
        .navbar { background: #000; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo a { color: #D4AF37; font-size: 1.8rem; font-weight: 700; text-decoration: none; font-family: 'Playfair Display', serif; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; align-items: center; }
        .nav-menu a { color: #fff; text-decoration: none; font-weight: 500; }
        .nav-menu a:hover { color: #D4AF37; }
        .cart-link { position: relative; }
        .cart-count { position: absolute; top: -8px; right: -12px; background: #D4AF37; color: #000; border-radius: 50%; padding: 2px 6px; font-size: 12px; }
        .nav-toggle { display: none; font-size: 1.5rem; color: #fff; cursor: pointer; }
        
        .container { max-width: 900px; margin: 0 auto; padding: 3rem 2rem; }
        .policy-card { background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .policy-card h1 { color: #000; margin-bottom: 1rem; }
        .policy-card h2 { color: #D4AF37; margin: 1.5rem 0 0.5rem; font-size: 1.2rem; }
        .policy-card p { margin-bottom: 1rem; line-height: 1.6; }
        .policy-card ul { margin-left: 2rem; margin-bottom: 1rem; }
        .policy-card li { margin-bottom: 0.5rem; }
        
        .footer { background: #111; color: #999; padding: 2rem 0; text-align: center; margin-top: 2rem; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; }
            .nav-menu.active { display: flex; }
            .container { padding: 1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo"><a href="/style-rwanda/">Style Rwanda</a></div>
            <div class="nav-toggle" id="navToggle"><i class="fas fa-bars"></i></div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="/style-rwanda/">Home</a></li>
                <li><a href="/style-rwanda/shop.php">Shop</a></li>
                <li><a href="/style-rwanda/returns.php">Returns</a></li>
                <li class="cart-link"><a href="/style-rwanda/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count">0</span></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="policy-card">
            <h1>Returns & Exchange Policy</h1>
            <p>At Style Rwanda, we want you to be completely satisfied with your purchase. If you're not happy with your order, we're here to help.</p>
            
            <h2>Return Period</h2>
            <p>You have <strong>14 days</strong> from the date of delivery to return an item for a refund or exchange.</p>
            
            <h2>Conditions for Returns</h2>
            <ul>
                <li>Items must be unworn, unwashed, and in original condition</li>
                <li>All original tags must be attached</li>
                <li>Items must be returned in original packaging</li>
                <li>Proof of purchase is required</li>
            </ul>
            
            <h2>Non-Returnable Items</h2>
            <ul>
                <li>Sale items (unless damaged)</li>
                <li>Underwear and swimwear</li>
                <li>Accessories (for hygiene reasons)</li>
            </ul>
            
            <h2>How to Return</h2>
            <p>Contact our customer service at <strong>info@style.rw</strong> with your order number and reason for return. We'll provide you with return instructions.</p>
            
            <h2>Refunds</h2>
            <p>Once we receive and inspect your return, we'll process your refund within 5-7 business days. Refunds will be issued to your original payment method.</p>
            
            <h2>Exchange Process</h2>
            <p>For exchanges, please indicate the desired size/color when contacting us. If the item is available, we'll process the exchange immediately.</p>
            
            <h2>Shipping Costs</h2>
            <p>Return shipping costs are the responsibility of the customer unless the item is defective or we made an error.</p>
            
            <h2>Contact Us</h2>
            <p>For any return questions, email us at <strong>returns@style.rw</strong> or call <strong>+250 788 123 456</strong></p>
        </div>
    </div>

    <footer class="footer"><div class="container"><p>&copy; 2025 Style Rwanda. All rights reserved.</p></div></footer>
    <script>document.getElementById('navToggle')?.addEventListener('click',()=>{document.getElementById('navMenu')?.classList.toggle('active');});</script>
</body>
</html>