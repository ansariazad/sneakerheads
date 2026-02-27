'use client';

export default function ContactPage() {
    return (
        <div className="container">
            <div className="page-hero">
                <h1>Contact Us</h1>
                <p>We&apos;d love to hear from you</p>
            </div>
            <div className="contact-container">
                <div className="form-container" style={{ maxWidth: '100%' }}>
                    <h2 className="form-title">Send Us a Message</h2>
                    <form onSubmit={(e) => e.preventDefault()}>
                        <div className="form-row">
                            <div className="form-group">
                                <label htmlFor="name">Full Name</label>
                                <input type="text" id="name" required />
                            </div>
                            <div className="form-group">
                                <label htmlFor="email">Email</label>
                                <input type="email" id="email" required />
                            </div>
                        </div>
                        <div className="form-group">
                            <label htmlFor="subject">Subject</label>
                            <input type="text" id="subject" required />
                        </div>
                        <div className="form-group">
                            <label htmlFor="message">Message</label>
                            <textarea id="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" className="btn">Send Message</button>
                    </form>
                </div>
                <div className="contact-info-cards">
                    {[
                        { icon: 'fa-map-marker-alt', title: 'Address', text: '123 Sneaker Street, Mumbai, Maharashtra 400001, India' },
                        { icon: 'fa-phone', title: 'Phone', text: '+91 98765 43210' },
                        { icon: 'fa-envelope', title: 'Email', text: 'support@sneakerheads.com' },
                        { icon: 'fa-clock', title: 'Business Hours', text: 'Mon - Sat: 9:00 AM - 8:00 PM IST' },
                    ].map((card, i) => (
                        <div key={i} className="contact-card">
                            <i className={`fas ${card.icon}`}></i>
                            <div><h3>{card.title}</h3><p>{card.text}</p></div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
