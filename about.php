<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Style Rwanda</title>
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
        
        .hero-about { background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1445205170230-053b83016050?w=1920') center/cover; height: 400px; display: flex; align-items: center; justify-content: center; text-align: center; color: white; }
        .hero-about h1 { font-size: 3rem; font-family: 'Playfair Display', serif; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 3rem 2rem; }
        .about-content { max-width: 800px; margin: 0 auto; text-align: center; }
        .about-content h2 { color: #D4AF37; margin-bottom: 1rem; }
        .about-content p { margin-bottom: 1.5rem; line-height: 1.8; }
        .values-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin: 3rem 0; }
        .value-card { text-align: center; padding: 2rem; background: #f9f9f9; border-radius: 16px; }
        .value-card i { font-size: 2.5rem; color: #D4AF37; margin-bottom: 1rem; }
        .value-card h3 { margin-bottom: 0.5rem; }
        
        .footer { background: #111; color: #999; padding: 2rem 0; text-align: center; margin-top: 2rem; }
        
        @media (max-width: 768px) {
            .nav-toggle { display: block; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background: #000; flex-direction: column; padding: 1rem 0; }
            .nav-menu.active { display: flex; }
            .values-grid { grid-template-columns: 1fr; }
            .hero-about h1 { font-size: 2rem; }
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
                <li><a href="/style-rwanda/about.php">About</a></li>
                <li><a href="/style-rwanda/contact.php">Contact</a></li>
                <li class="cart-link"><a href="/style-rwanda/cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count">0</span></a></li>
            </ul>
        </div>
    </nav>

    <div class="hero-about"><h1>About Style Rwanda</h1></div>

    <div class="container">
        <div class="about-content">
            <h2>Our Story</h2>
            <p>Founded in Kigali, Style Rwanda was born from a passion for fashion and a desire to bring premium quality clothing to the modern Rwandan. We believe that fashion is more than just clothing—it's a form of self-expression and confidence.</p>
            
            <p>Our mission is to provide stylish, high-quality apparel that celebrates Rwandan elegance while embracing contemporary trends. Every piece in our collection is carefully selected to ensure the perfect blend of comfort, durability, and style.</p>
            
            <h2>Our Values</h2>
        </div>
        
        <div class="values-grid">
            <div class="value-card"><i class="fas fa-gem"></i><h3>Quality First</h3><p>Premium materials and craftsmanship</p></div>
            <div class="value-card"><i class="fas fa-heart"></i><h3>Customer Love</h3><p>Your satisfaction is our priority</p></div>
            <div class="value-card"><i class="fas fa-leaf"></i><h3>Sustainable</h3><p>Eco-friendly practices</p></div>
        </div>
    </div>

    <footer class="footer"><div class="container"><p>&copy; 2025 Style Rwanda. All rights reserved.</p></div></footer>
    <script>document.getElementById('navToggle')?.addEventListener('click',()=>{document.getElementById('navMenu')?.classList.toggle('active');});</script>
</body>
</html>