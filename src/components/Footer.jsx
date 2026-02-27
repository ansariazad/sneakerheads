'use client';
import Link from 'next/link';
import Image from 'next/image';

export default function Footer() {
    return (
        <footer className="main-footer">
            <div className="footer-accent"></div>
            <div className="container">
                <div className="footer-content">
                    <div className="footer-brand">
                        <div className="footer-logo">
                            <Image src="/images/sneakerheads-logo.svg" alt="Sneakerheads" width={40} height={40} />
                            <span className="logo-text">Sneaker<span className="logo-highlight">heads</span></span>
                        </div>
                        <p className="footer-desc">India&apos;s #1 marketplace for buying and selling authentic sneakers. Every pair authenticated, every transaction secured.</p>
                        <div className="footer-social">
                            <a href="#" aria-label="Instagram"><i className="fab fa-instagram"></i></a>
                            <a href="#" aria-label="Twitter"><i className="fab fa-twitter"></i></a>
                            <a href="#" aria-label="YouTube"><i className="fab fa-youtube"></i></a>
                            <a href="#" aria-label="LinkedIn"><i className="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                    <div className="footer-links">
                        <h3>Shop</h3>
                        <ul>
                            <li><Link href="/search">All Sneakers</Link></li>
                            <li><Link href="/search?brand=Nike">Nike</Link></li>
                            <li><Link href="/search?brand=Adidas">Adidas</Link></li>
                            <li><Link href="/search?brand=Jordan">Jordan</Link></li>
                            <li><Link href="/search?sort=newest">New Arrivals</Link></li>
                        </ul>
                    </div>
                    <div className="footer-links">
                        <h3>Company</h3>
                        <ul>
                            <li><Link href="/about">About Us</Link></li>
                            <li><Link href="/contact">Contact</Link></li>
                            <li><Link href="/faq">FAQ</Link></li>
                            <li><Link href="/seller/sneakers/add">Sell Sneakers</Link></li>
                        </ul>
                    </div>
                    <div className="footer-newsletter">
                        <h3>Stay Updated</h3>
                        <p>Get notified about drops, deals & sneaker news.</p>
                        <form onSubmit={e => e.preventDefault()}>
                            <input type="email" placeholder="Your email" />
                            <button type="submit"><i className="fas fa-arrow-right"></i></button>
                        </form>
                        <div className="footer-trust">
                            <div className="trust-item"><i className="fas fa-shield-alt"></i><span>Secure</span></div>
                            <div className="trust-item"><i className="fas fa-check-circle"></i><span>Authentic</span></div>
                            <div className="trust-item"><i className="fas fa-truck"></i><span>Free Ship</span></div>
                        </div>
                    </div>
                </div>
                <div className="footer-bottom">
                    <p>&copy; {new Date().getFullYear()} Sneakerheads. All rights reserved.</p>
                    <div className="payment-methods">
                        <i className="fab fa-google-pay"></i>
                        <i className="fas fa-mobile-alt"></i>
                        <i className="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </footer>
    );
}
