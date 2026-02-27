'use client';
import Link from 'next/link';

export default function Footer() {
    return (
        <footer className="main-footer">
            <div className="container">
                <div className="footer-content">
                    <div className="footer-logo">
                        <h2>Sneakerheads</h2>
                        <p>The ultimate destination for sneaker enthusiasts in India.</p>
                        <div className="footer-social">
                            <a href="#"><i className="fab fa-facebook-f"></i></a>
                            <a href="#"><i className="fab fa-twitter"></i></a>
                            <a href="#"><i className="fab fa-instagram"></i></a>
                            <a href="#"><i className="fab fa-youtube"></i></a>
                        </div>
                    </div>
                    <div className="footer-links">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><Link href="/">Home</Link></li>
                            <li><Link href="/about">About Us</Link></li>
                            <li><Link href="/contact">Contact</Link></li>
                            <li><Link href="/faq">FAQ</Link></li>
                        </ul>
                    </div>
                    <div className="footer-links">
                        <h3>Categories</h3>
                        <ul>
                            <li><Link href="/search?brand=Nike">Nike</Link></li>
                            <li><Link href="/search?brand=Adidas">Adidas</Link></li>
                            <li><Link href="/search?brand=Jordan">Jordan</Link></li>
                            <li><Link href="/search?brand=Puma">Puma</Link></li>
                            <li><Link href="/search?brand=Reebok">Reebok</Link></li>
                            <li><Link href="/search?category=Limited+Edition">Limited Edition</Link></li>
                        </ul>
                    </div>
                    <div className="footer-newsletter">
                        <h3>Subscribe to Our Newsletter</h3>
                        <p>Stay updated with the latest releases and sneaker news.</p>
                        <form onSubmit={(e) => e.preventDefault()}>
                            <input type="email" placeholder="Your email address" required />
                            <button type="submit" className="btn">Subscribe</button>
                        </form>
                    </div>
                </div>
                <div className="footer-bottom">
                    <p>&copy; {new Date().getFullYear()} Sneakerheads. All Rights Reserved.</p>
                    <div className="payment-methods">
                        <i className="fab fa-cc-visa"></i>
                        <i className="fab fa-cc-mastercard"></i>
                        <i className="fab fa-cc-amex"></i>
                        <i className="fab fa-cc-paypal"></i>
                    </div>
                </div>
            </div>
        </footer>
    );
}
