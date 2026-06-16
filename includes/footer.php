<?php
/**
 * Footer Template - Style Rwanda
 */

// End output buffering
ob_end_flush();
?>
    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Premium fashion for the modern Rwandan.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/shop.php">Shop</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/returns.php">Returns Policy</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>My Account</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/order-track.php">Track Order</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/wishlist.php">Wishlist</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> +250 788 123 456</p>
                    <p><i class="fas fa-envelope"></i> info@style.rw</p>
                    <p><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript for mobile navigation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.getElementById('navToggle');
            const navMenu = document.getElementById('navMenu');
            if (navToggle && navMenu) {
                navToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>