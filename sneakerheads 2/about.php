<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "About Us - Sneakerheads";
require_once 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="container">
        <!-- Hero Section -->
        <div class="page-hero about-hero">
            <div class="page-hero-content">
                <h1>About Sneakerheads</h1>
                <p>The ultimate destination for sneaker enthusiasts in India</p>
            </div>
        </div>

        <!-- Our Story Section -->
        <section class="about-section">
            <div class="section-header">
                <h2>Our Story</h2>
            </div>
            <div class="about-content">
                <div class="about-image">
                    <img src="assets/images/about-story.jpg" alt="Sneakerheads Story">
                </div>
                <div class="about-text">
                    <h3>From Passion to Platform</h3>
                    <p>Sneakerheads was born out of a deep passion for sneaker culture and the frustration of not having a reliable platform for buying and selling authentic sneakers in India.</p>
                    <p>Founded in 2023 by a group of sneaker enthusiasts, our platform has quickly grown to become India's most trusted marketplace for premium and limited-edition sneakers.</p>
                    <p>What started as a small community of collectors has evolved into a comprehensive platform that connects sellers and buyers across the country, ensuring authenticity and fair pricing for every transaction.</p>
                </div>
            </div>
        </section>

        <!-- Our Mission Section -->
        <section class="about-section">
            <div class="section-header">
                <h2>Our Mission</h2>
            </div>
            <div class="about-content reverse">
                <div class="about-text">
                    <h3>Authenticity, Community, and Culture</h3>
                    <p>At Sneakerheads, our mission is to create a trusted ecosystem where sneaker enthusiasts can buy, sell, and celebrate sneaker culture with complete confidence.</p>
                    <p>We're committed to:</p>
                    <ul class="mission-list">
                        <li><i class="fas fa-check-circle"></i> Ensuring 100% authenticity for every sneaker on our platform</li>
                        <li><i class="fas fa-check-circle"></i> Building a vibrant community of sneaker lovers across India</li>
                        <li><i class="fas fa-check-circle"></i> Providing fair pricing and transparent transactions</li>
                        <li><i class="fas fa-check-circle"></i> Educating and promoting sneaker culture in India</li>
                        <li><i class="fas fa-check-circle"></i> Supporting both established collectors and newcomers to the scene</li>
                    </ul>
                </div>
                <div class="about-image">
                    <img src="assets/images/about-mission.jpg" alt="Sneakerheads Mission">
                </div>
            </div>
        </section>

        <!-- Our Team Section -->
        <section class="about-section">
            <div class="section-header">
                <h2>Our Team</h2>
            </div>
            <p class="team-intro">Meet the passionate sneaker enthusiasts behind Sneakerheads</p>
            <div class="team-grid">
                <div class="team-member">
                    <div class="team-image">
                        <img src="assets/images/team-1.jpeg" alt="Team Member">
                    </div>
                    <div class="team-info">
                        <h3>SAJID KHAN</h3>
                        <p class="team-role">Founder & CEO</p>
                        <p class="team-desc">Sneaker collector for over 10 years with a passion for Air Jordans</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-image">
                        <img src="assets/images/team-2.png" alt="Team Member">
                    </div>
                    <div class="team-info">
                        <h3>Travis Scott</h3>
                        <p class="team-role">COO</p>
                        <p class="team-desc">Sneaker enthusiast with expertise in retail operations</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-image">
                        <img src="assets/images/team-3.png" alt="Team Member">
                    </div>
                    <div class="team-info">
                        <h3>MC STAN</h3>
                        <p class="team-role">Head of Authentication</p>
                        <p class="team-desc">Certified sneaker authenticator with an eye for detail</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-image">
                        <img src="assets/images/team-4.jpg" alt="Team Member">
                    </div>
                    <div class="team-info">
                        <h3>Kendal Jenner</h3>
                        <p class="team-role">Community Manager</p>
                        <p class="team-desc">Connecting sneaker enthusiasts across India</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="about-section">
            <div class="section-header">
                <h2>Why Choose Sneakerheads</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>100% Authenticity</h3>
                    <p>Every sneaker on our platform goes through a rigorous authentication process by our experts</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure Transactions</h3>
                    <p>Our platform ensures safe and secure transactions for both buyers and sellers</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Quick processing and delivery across India with real-time tracking</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community First</h3>
                    <p>We're building more than a marketplace - we're creating a community of sneaker lovers</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <h3>Fair Pricing</h3>
                    <p>Transparent pricing with no hidden fees or charges</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our customer support team is always available to assist you</p>
                </div>
            </div>
        </section>

        <!-- Join Us CTA -->
        <section class="about-cta">
            <div class="cta-content">
                <h2>Join the Sneakerheads Community</h2>
                <p>Whether you're a collector, reseller, or just starting your sneaker journey, there's a place for you here.</p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn">Sign Up Now</a>
                    <a href="contact.php" class="btn btn-secondary">Contact Us</a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

