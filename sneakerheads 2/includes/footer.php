<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-logo">
                <h2>Sneakerheads</h2>
                <p>The ultimate destination for sneaker enthusiasts in India.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Categories</h3>
                <ul>
                    <li><a href="search.php?brand=Nike">Nike</a></li>
                    <li><a href="search.php?brand=Adidas">Adidas</a></li>
                    <li><a href="search.php?brand=Jordan">Jordan</a></li>
                    <li><a href="search.php?brand=Puma">Puma</a></li>
                    <li><a href="search.php?brand=Reebok">Reebok</a></li>
                    <li><a href="search.php?category=Limited+Edition">Limited Edition</a></li>
                </ul>
            </div>
            <div class="footer-newsletter">
                <h3>Subscribe to Our Newsletter</h3>
                <p>Stay updated with the latest releases and sneaker news.</p>
                
                <div id="newsletter-response"></div>
                
                <form id="newsletter-form" class="newsletter-form">
                    <input type="email" name="newsletter_email" placeholder="Your email address" required>
                    <button type="submit" class="btn">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Sneakerheads. All Rights Reserved.</p>
            <div class="payment-methods">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-amex"></i>
                <i class="fab fa-cc-paypal"></i>
            </div>
        </div>
    </div>
</footer>

<!-- Newsletter AJAX Subscription -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.getElementById('newsletter-form');
    const newsletterResponse = document.getElementById('newsletter-response');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[name="newsletter_email"]').value;
            
            // Show loading state
            newsletterResponse.innerHTML = '<div class="alert alert-info">Processing your subscription...</div>';
            
            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'process_newsletter.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success) {
                            newsletterResponse.innerHTML = `<div class="alert alert-success">${response.message}</div>`;
                            newsletterForm.reset();
                        } else {
                            newsletterResponse.innerHTML = `<div class="alert alert-error">${response.message}</div>`;
                        }
                    } catch (e) {
                        newsletterResponse.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
                    }
                } else {
                    newsletterResponse.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
                }
            };
            
            xhr.onerror = function() {
                newsletterResponse.innerHTML = '<div class="alert alert-error">Network error. Please check your connection.</div>';
            };
            
            xhr.send(`email=${encodeURIComponent(email)}`);
        });
    }
});
</script>

<!-- Main JS -->
<script src="assets/js/main.js"></script>
</body>
</html>

