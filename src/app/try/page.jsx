'use client';
import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';

const steps = [
    { icon: 'fa-shoe-prints', title: 'Pick Your Sneakers', desc: 'Choose up to 3 pairs to try at home. No payment needed upfront — just a refundable ₹500 deposit.' },
    { icon: 'fa-truck', title: 'Delivered to Your Door', desc: 'We deliver your picks within 2-3 days. Try them on in the comfort of your home.' },
    { icon: 'fa-clock', title: '7 Days to Decide', desc: 'Wear them around the house. Check the fit, feel, and style. Take your time — you have 7 full days.' },
    { icon: 'fa-heart', title: 'Keep What You Love', desc: 'Pay only for the pairs you keep. Return the rest for free — we\'ll pick them up from your doorstep.' },
];

const faqs = [
    { q: 'How much does Try Before Buy cost?', a: 'Just a refundable ₹500 deposit. If you return all items, the deposit is fully refunded within 3-5 business days.' },
    { q: 'How many sneakers can I try?', a: 'You can try up to 3 pairs at a time. Once you return or purchase them, you can try more!' },
    { q: 'What if the sneakers don\'t fit?', a: 'No worries! Just request a return from your orders page. We\'ll send a courier to pick them up — completely free.' },
    { q: 'How long do I have to decide?', a: '7 days from delivery. If we don\'t hear back, we\'ll send a gentle reminder. After 10 days, the deposit is charged.' },
    { q: 'Is every sneaker eligible?', a: 'Most sneakers priced between ₹2,000 - ₹30,000 are eligible. Some limited editions and pre-orders may not be available for trial.' },
    { q: 'Which cities is this available in?', a: 'Currently available in Mumbai, Delhi, Bangalore, Hyderabad, Pune, Chennai, and Kolkata. Expanding to more cities soon!' },
];

const testimonials = [
    { name: 'Rahul M.', city: 'Mumbai', text: 'Tried 3 Jordans at home, kept 2. The convenience is unmatched!', rating: 5 },
    { name: 'Priya S.', city: 'Bangalore', text: 'Finally found my perfect Yeezy size without the hassle of returns.', rating: 5 },
    { name: 'Arjun K.', city: 'Delhi', text: 'The 7-day trial period is so generous. Love this feature!', rating: 4 },
];

export default function TryBeforeBuyPage() {
    const [openFaq, setOpenFaq] = useState(null);

    return (
        <div className="container">
            {/* Hero */}
            <div style={{ textAlign: 'center', padding: '60px 0 40px', position: 'relative' }}>
                <div style={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', width: 300, height: 300, background: 'radial-gradient(circle, rgba(59,130,246,0.12) 0%, transparent 70%)', borderRadius: '50%', zIndex: 0 }}></div>
                <div style={{ position: 'relative', zIndex: 1 }}>
                    <span style={{ background: 'var(--gradient)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', fontSize: 14, fontWeight: 600, letterSpacing: 2, textTransform: 'uppercase' }}>New Feature</span>
                    <h1 style={{ fontSize: 48, marginTop: 10, marginBottom: 16, lineHeight: 1.2 }}>
                        Try Before You <span style={{ background: 'var(--gradient)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}>Buy</span>
                    </h1>
                    <p style={{ color: 'var(--text-secondary)', fontSize: 18, maxWidth: 500, margin: '0 auto 30px' }}>
                        Try up to 3 pairs at home. Keep what you love, return the rest — for free.
                    </p>
                    <div style={{ display: 'flex', gap: 12, justifyContent: 'center', flexWrap: 'wrap' }}>
                        <Link href="/search" className="btn btn-gradient" style={{ padding: '14px 32px', fontSize: 16 }}>
                            <i className="fas fa-shoe-prints" style={{ marginRight: 8 }}></i>Start Trying
                        </Link>
                        <a href="#how-it-works" className="btn btn-secondary" style={{ padding: '14px 32px', fontSize: 16 }}>
                            How It Works
                        </a>
                    </div>
                </div>
            </div>

            {/* Trust Badges */}
            <div style={{ display: 'flex', justifyContent: 'center', gap: 30, flexWrap: 'wrap', marginBottom: 50 }}>
                {[
                    { icon: 'fa-shield-alt', text: '100% Authentic' },
                    { icon: 'fa-undo', text: 'Free Returns' },
                    { icon: 'fa-rupee-sign', text: '₹500 Refundable Deposit' },
                    { icon: 'fa-calendar', text: '7-Day Trial' },
                ].map((b, i) => (
                    <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 14, color: 'var(--text-secondary)' }}>
                        <i className={`fas ${b.icon}`} style={{ color: 'var(--accent-color)' }}></i>
                        <span>{b.text}</span>
                    </div>
                ))}
            </div>

            {/* How It Works */}
            <div id="how-it-works" style={{ marginBottom: 60 }}>
                <h2 style={{ textAlign: 'center', marginBottom: 40 }}>How It Works</h2>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: 24 }}>
                    {steps.map((step, i) => (
                        <div key={i} style={{ background: 'var(--bg-secondary)', border: '1px solid var(--glass-border)', borderRadius: 'var(--radius-lg)', padding: 28, textAlign: 'center', position: 'relative' }}>
                            <div style={{ position: 'absolute', top: 12, left: 16, fontSize: 48, fontWeight: 800, color: 'rgba(59,130,246,0.08)' }}>{i + 1}</div>
                            <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'rgba(59,130,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                                <i className={`fas ${step.icon}`} style={{ fontSize: 22, color: 'var(--primary-color)' }}></i>
                            </div>
                            <h3 style={{ marginBottom: 8 }}>{step.title}</h3>
                            <p style={{ color: 'var(--text-secondary)', fontSize: 14, lineHeight: 1.6 }}>{step.desc}</p>
                        </div>
                    ))}
                </div>
            </div>

            {/* Pricing Card */}
            <div style={{ maxWidth: 500, margin: '0 auto 60px', background: 'var(--bg-secondary)', border: '1px solid var(--glass-border)', borderRadius: 'var(--radius-xl)', padding: 36, textAlign: 'center' }}>
                <h2 style={{ marginBottom: 8 }}>Simple Pricing</h2>
                <p style={{ color: 'var(--text-secondary)', marginBottom: 24 }}>No hidden fees. Ever.</p>
                <div style={{ display: 'grid', gap: 12 }}>
                    {[
                        { label: 'Refundable Deposit', value: '₹500', desc: 'Returned if you send back all items' },
                        { label: 'Trial Period', value: '7 Days', desc: 'From the day of delivery' },
                        { label: 'Return Pickup', value: 'FREE', desc: 'We come to your door' },
                        { label: 'Pairs per Trial', value: 'Up to 3', desc: 'Choose your favorites' },
                    ].map((item, i) => (
                        <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '12px 0', borderBottom: i < 3 ? '1px solid rgba(255,255,255,0.04)' : 'none' }}>
                            <div style={{ textAlign: 'left' }}>
                                <p style={{ margin: 0, fontWeight: 500, fontSize: 14 }}>{item.label}</p>
                                <p style={{ margin: 0, fontSize: 12, color: 'var(--text-secondary)' }}>{item.desc}</p>
                            </div>
                            <span style={{ fontWeight: 700, color: 'var(--primary-color)', fontSize: 16 }}>{item.value}</span>
                        </div>
                    ))}
                </div>
                <Link href="/search" className="btn btn-gradient btn-full" style={{ marginTop: 24 }}>Browse Eligible Sneakers</Link>
            </div>

            {/* Testimonials */}
            <div style={{ marginBottom: 60 }}>
                <h2 style={{ textAlign: 'center', marginBottom: 30 }}>What Sneakerheads Say</h2>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: 20 }}>
                    {testimonials.map((t, i) => (
                        <div key={i} style={{ background: 'var(--bg-secondary)', border: '1px solid var(--glass-border)', borderRadius: 'var(--radius)', padding: 24 }}>
                            <div style={{ marginBottom: 10 }}>
                                {[...Array(5)].map((_, j) => (
                                    <i key={j} className={`fas fa-star`} style={{ color: j < t.rating ? '#f59e0b' : 'var(--bg-light)', fontSize: 14, marginRight: 2 }}></i>
                                ))}
                            </div>
                            <p style={{ fontSize: 14, lineHeight: 1.6, marginBottom: 12 }}>&ldquo;{t.text}&rdquo;</p>
                            <p style={{ margin: 0, fontSize: 13, fontWeight: 600 }}>{t.name} <span style={{ color: 'var(--text-secondary)', fontWeight: 400 }}>• {t.city}</span></p>
                        </div>
                    ))}
                </div>
            </div>

            {/* FAQ */}
            <div style={{ maxWidth: 700, margin: '0 auto 60px' }}>
                <h2 style={{ textAlign: 'center', marginBottom: 30 }}>Frequently Asked Questions</h2>
                {faqs.map((faq, i) => (
                    <div key={i} style={{ borderBottom: '1px solid var(--glass-border)', marginBottom: 0 }}>
                        <button
                            onClick={() => setOpenFaq(openFaq === i ? null : i)}
                            style={{ width: '100%', display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '16px 0', background: 'none', border: 'none', color: 'var(--text-color)', cursor: 'pointer', fontSize: 15, fontWeight: 500, textAlign: 'left' }}
                        >
                            {faq.q}
                            <i className={`fas fa-chevron-${openFaq === i ? 'up' : 'down'}`} style={{ color: 'var(--text-secondary)', fontSize: 12 }}></i>
                        </button>
                        {openFaq === i && (
                            <p style={{ padding: '0 0 16px', fontSize: 14, color: 'var(--text-secondary)', lineHeight: 1.6, margin: 0 }}>{faq.a}</p>
                        )}
                    </div>
                ))}
            </div>

            {/* CTA */}
            <div style={{ textAlign: 'center', padding: '40px 0 60px', background: 'var(--bg-secondary)', borderRadius: 'var(--radius-xl)', border: '1px solid var(--glass-border)', marginBottom: 40 }}>
                <h2 style={{ marginBottom: 12 }}>Ready to Try?</h2>
                <p style={{ color: 'var(--text-secondary)', marginBottom: 24, maxWidth: 400, margin: '0 auto 24px' }}>
                    No risk. No commitment. Just sneakers at your doorstep.
                </p>
                <Link href="/search" className="btn btn-gradient" style={{ padding: '14px 40px', fontSize: 16 }}>
                    <i className="fas fa-shoe-prints" style={{ marginRight: 8 }}></i>Start Trying Now
                </Link>
            </div>
        </div>
    );
}
