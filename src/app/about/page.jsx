import Link from 'next/link';

export const metadata = { title: 'About Us - Sneakerheads' };

export default function AboutPage() {
    return (
        <div className="content-wrapper">
            <div className="container">
                <div className="page-hero about-hero">
                    <div className="page-hero-content">
                        <h1>About Sneakerheads</h1>
                        <p>The ultimate destination for sneaker enthusiasts in India</p>
                    </div>
                </div>

                <section className="about-section">
                    <div className="section-header"><h2>Our Story</h2></div>
                    <div className="about-content">
                        <div className="about-image">
                            <img src="/images/about-story.jpg" alt="Our Story" />
                        </div>
                        <div className="about-text">
                            <h3>From Passion to Platform</h3>
                            <p>Sneakerheads was born out of a deep passion for sneaker culture and the frustration of not having a reliable platform for buying and selling authentic sneakers in India.</p>
                            <p>Founded in 2023 by a group of sneaker enthusiasts, our platform has quickly grown to become India&apos;s most trusted marketplace for premium and limited-edition sneakers.</p>
                            <p>What started as a small community of collectors has evolved into a comprehensive platform that connects sellers and buyers across the country.</p>
                        </div>
                    </div>
                </section>

                <section className="about-section">
                    <div className="section-header"><h2>Our Mission</h2></div>
                    <div className="about-content reverse">
                        <div className="about-text">
                            <h3>Authenticity, Community, and Culture</h3>
                            <p>At Sneakerheads, our mission is to create a trusted ecosystem where sneaker enthusiasts can buy, sell, and celebrate sneaker culture with complete confidence.</p>
                            <ul className="mission-list">
                                <li><i className="fas fa-check-circle"></i> Ensuring 100% authenticity for every sneaker</li>
                                <li><i className="fas fa-check-circle"></i> Building a vibrant community of sneaker lovers</li>
                                <li><i className="fas fa-check-circle"></i> Providing fair pricing and transparent transactions</li>
                                <li><i className="fas fa-check-circle"></i> Educating and promoting sneaker culture in India</li>
                                <li><i className="fas fa-check-circle"></i> Supporting collectors and newcomers alike</li>
                            </ul>
                        </div>
                        <div className="about-image">
                            <img src="/images/about-mission.jpg" alt="Our Mission" />
                        </div>
                    </div>
                </section>

                <section className="about-section">
                    <div className="section-header"><h2>Our Team</h2></div>
                    <p className="team-intro">Meet the passionate sneaker enthusiasts behind Sneakerheads</p>
                    <div className="team-grid">
                        {[
                            { name: 'Azad Ansari', role: 'Co-Founder & CEO', desc: 'Sneaker collector for over 10 years with a passion for Air Jordans' },
                            { name: 'Travis Scott', role: 'COO', desc: 'Sneaker enthusiast with expertise in retail operations' },
                            { name: 'MC STAN', role: 'Head of Authentication', desc: 'Certified sneaker authenticator with an eye for detail' },
                            { name: 'Kendal Jenner', role: 'Community Manager', desc: 'Connecting sneaker enthusiasts across India' },
                        ].map((member, i) => (
                            <div key={i} className="team-member">
                                <div className="team-image">
                                    <img src={`/images/team-${i + 1}.${i === 0 ? 'jpeg' : i === 1 ? 'png' : i === 2 ? 'png' : 'jpg'}`} alt={member.name} />
                                </div>
                                <div className="team-info">
                                    <h3>{member.name}</h3>
                                    <p className="team-role">{member.role}</p>
                                    <p className="team-desc">{member.desc}</p>
                                    <div className="team-social">
                                        <a href="#"><i className="fab fa-instagram"></i></a>
                                        <a href="#"><i className="fab fa-linkedin"></i></a>
                                        <a href="#"><i className="fab fa-twitter"></i></a>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="about-section">
                    <div className="section-header"><h2>Why Choose Sneakerheads</h2></div>
                    <div className="features-grid">
                        {[
                            { icon: 'fa-check-circle', title: '100% Authenticity', desc: 'Rigorous authentication process by our experts' },
                            { icon: 'fa-shield-alt', title: 'Secure Transactions', desc: 'Safe and secure transactions for buyers and sellers' },
                            { icon: 'fa-truck', title: 'Fast Delivery', desc: 'Quick processing and delivery across India' },
                            { icon: 'fa-users', title: 'Community First', desc: 'Building a community of sneaker lovers' },
                            { icon: 'fa-tag', title: 'Fair Pricing', desc: 'Transparent pricing with no hidden fees' },
                            { icon: 'fa-headset', title: '24/7 Support', desc: 'Customer support always available to assist' },
                        ].map((f, i) => (
                            <div key={i} className="feature-card">
                                <div className="feature-icon"><i className={`fas ${f.icon}`}></i></div>
                                <h3>{f.title}</h3>
                                <p>{f.desc}</p>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="about-cta">
                    <div className="cta-content">
                        <h2>Join the Sneakerheads Community</h2>
                        <p>Whether you&apos;re a collector, reseller, or just starting your sneaker journey, there&apos;s a place for you here.</p>
                        <div className="cta-buttons">
                            <Link href="/register" className="btn">Sign Up Now</Link>
                            <Link href="/contact" className="btn btn-secondary">Contact Us</Link>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    );
}
