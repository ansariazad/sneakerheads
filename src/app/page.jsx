'use client';
import { useState, useEffect, Suspense } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useSearchParams } from 'next/navigation';
import { getSneakers, getBrands } from '@/lib/db';
import { useAuth } from '@/components/AuthProvider';
import SneakerCard from '@/components/SneakerCard';

const showcaseSneakers = [
    { id: 'showcase-1', brand: 'Nike', model: 'Air Jordan 1 Retro High OG', size: 9, price: 16995, featured: true, condition: 'new', images: [{ image_url: '/images/sneakers/nike-jordan1.jpg', display_order: 0 }] },
    { id: 'showcase-2', brand: 'Adidas', model: 'Yeezy Boost 350 V2', size: 10, price: 22999, featured: true, condition: 'new', images: [{ image_url: '/images/sneakers/adidas-yeezy350.jpg', display_order: 0 }] },
    { id: 'showcase-3', brand: 'Nike', model: 'Dunk Low Panda', size: 8, price: 8995, featured: true, condition: 'new', images: [{ image_url: '/images/sneakers/nike-dunk-low.jpg', display_order: 0 }] },
    { id: 'showcase-4', brand: 'Jordan', model: 'Air Jordan 4 Retro', size: 11, price: 19999, featured: true, condition: 'new', images: [{ image_url: '/images/sneakers/jordan4-retro.jpg', display_order: 0 }] },
    { id: 'showcase-5', brand: 'Puma', model: 'RS-X Reinvention', size: 9, price: 7499, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/puma-rsx.jpg', display_order: 0 }] },
    { id: 'showcase-6', brand: 'Nike', model: 'Air Max 90 Infrared', size: 10, price: 12499, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/nike-airmax90.jpg', display_order: 0 }] },
    { id: 'showcase-7', brand: 'New Balance', model: '550 Green', size: 8, price: 10999, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/newbalance-550.jpg', display_order: 0 }] },
    // Real seller-uploaded sneakers
    { id: 'showcase-8', brand: 'Nike', model: 'Air Force 1 Low White', size: 9, price: 7999, featured: true, condition: 'new', images: [{ image_url: '/images/sneakers/seller-sneaker1-top.jpg', display_order: 0 }, { image_url: '/images/sneakers/seller-sneaker1-side.jpg', display_order: 1 }] },
    { id: 'showcase-9', brand: 'Adidas', model: 'Ultraboost 22', size: 10, price: 14999, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/seller-sneaker2-top.jpg', display_order: 0 }, { image_url: '/images/sneakers/seller-sneaker2-side.jpg', display_order: 1 }] },
    { id: 'showcase-10', brand: 'Reebok', model: 'Classic Leather', size: 8, price: 6999, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/seller-sneaker3-top.jpg', display_order: 0 }, { image_url: '/images/sneakers/seller-sneaker3-side.jpg', display_order: 1 }] },
    { id: 'showcase-11', brand: 'Campus', model: 'Running Pro', size: 9, price: 1499, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/seller-sneaker4-top.jpg', display_order: 0 }, { image_url: '/images/sneakers/seller-sneaker4-side.jpg', display_order: 1 }] },
    { id: 'showcase-12', brand: 'Woodland', model: 'Outdoor Sport', size: 10, price: 3499, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/seller-sneaker5-top.jpg', display_order: 0 }, { image_url: '/images/sneakers/seller-sneaker5-side.jpg', display_order: 1 }] },
    { id: 'showcase-13', brand: 'Sparx', model: 'SM-439 Sport', size: 8, price: 999, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/seller-sneaker6-top.jpg', display_order: 0 }, { image_url: '/images/sneakers/seller-sneaker6-side.jpg', display_order: 1 }] },
    { id: 'showcase-14', brand: 'Red Tape', model: 'Walking Shoes', size: 9, price: 2499, featured: false, condition: 'new', images: [{ image_url: '/images/sneakers/seller-sneaker7-top.jpg', display_order: 0 }, { image_url: '/images/sneakers/seller-sneaker7-side.jpg', display_order: 1 }] },
];

const offers = [
    { title: 'First Order Discount', subtitle: 'Get 15% off your first purchase', code: 'FIRST15', icon: 'fa-gift', gradient: 'linear-gradient(135deg, #3b82f6, #8b5cf6)' },
    { title: 'Free Shipping', subtitle: 'On all orders above ₹5,000', code: null, icon: 'fa-truck', gradient: 'linear-gradient(135deg, #06d6a0, #059669)' },
    { title: 'Seller Special', subtitle: 'List your first pair for free', code: 'SELL0', icon: 'fa-store', gradient: 'linear-gradient(135deg, #f59e0b, #ef4444)' },
];

function HomeContent() {
    const { user, profile } = useAuth();
    const searchParams = useSearchParams();
    const [featured, setFeatured] = useState([]);
    const [newArrivals, setNewArrivals] = useState([]);
    const [brands, setBrands] = useState([]);
    const [loading, setLoading] = useState(true);
    const [toast, setToast] = useState(null);

    // Welcome toast
    useEffect(() => {
        const isWelcome = searchParams.get('welcome');
        const isLoggedIn = searchParams.get('loggedin');
        if (isWelcome) {
            const name = profile?.full_name || profile?.username || 'Sneakerhead';
            setToast({ type: 'success', message: `🎉 Welcome to Sneakerheads, ${name}! Your account is ready.` });
            window.history.replaceState({}, '', '/');
        } else if (isLoggedIn) {
            const name = profile?.full_name || profile?.username || '';
            setToast({ type: 'success', message: `👋 Welcome back${name ? ', ' + name : ''}!` });
            window.history.replaceState({}, '', '/');
        }
    }, [searchParams, profile]);

    // Auto-dismiss toast
    useEffect(() => {
        if (toast) {
            const t = setTimeout(() => setToast(null), 5000);
            return () => clearTimeout(t);
        }
    }, [toast]);

    useEffect(() => {
        const load = async () => {
            try { const r = await getSneakers({ featured: true, limit: 4 }); setFeatured(r.data || []); } catch { }
            try { const r = await getSneakers({ limit: 8 }); setNewArrivals(r.data || []); } catch { }
            try { const b = await getBrands(); setBrands((b || []).slice(0, 6)); } catch { }
            setLoading(false);
        };
        load();
    }, []);

    const displayFeatured = featured.length > 0 ? featured : showcaseSneakers.filter(s => s.featured);
    const displayArrivals = newArrivals.length > 0 ? newArrivals : showcaseSneakers;

    return (
        <div className="container">
            {/* Welcome Toast */}
            {toast && (
                <div className={`toast toast-${toast.type}`}>
                    <span>{toast.message}</span>
                    <button onClick={() => setToast(null)} className="toast-close"><i className="fas fa-times"></i></button>
                </div>
            )}
            <div className="hero">
                <div className="hero-content">
                    <h1>Find Your <span>Perfect Pair</span></h1>
                    <p>India&apos;s #1 marketplace for buying and selling 100% authenticated sneakers</p>
                    <div className="hero-buttons">
                        <Link href="/search" className="btn btn-gradient">Shop Now <i className="fas fa-arrow-right" style={{ marginLeft: 6 }}></i></Link>
                        <Link href="/seller/sneakers/add" className="btn btn-secondary">Sell Sneakers</Link>
                    </div>
                </div>
            </div>

            {/* Offers Banner */}
            <div className="offers-section">
                <div className="offers-grid">
                    {offers.map((offer, i) => (
                        <div key={i} className="offer-card" style={{ background: offer.gradient }}>
                            <div className="offer-icon"><i className={`fas ${offer.icon}`}></i></div>
                            <div className="offer-content">
                                <h3>{offer.title}</h3>
                                <p>{offer.subtitle}</p>
                                {offer.code && <span className="offer-code">Code: {offer.code}</span>}
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Category Pills */}
            <div style={{ marginTop: 30, marginBottom: 10 }}>
                <h2 style={{ marginBottom: 16 }}>Shop by Category</h2>
                <div className="category-pills">
                    <Link href="/search" className="category-pill active"><i className="fas fa-fire"></i> All</Link>
                    <Link href="/search?q=men" className="category-pill"><i className="fas fa-male"></i> Men</Link>
                    <Link href="/search?q=women" className="category-pill"><i className="fas fa-female"></i> Women</Link>
                    <Link href="/search?q=kids" className="category-pill"><i className="fas fa-child"></i> Kids</Link>
                    <Link href="/search?brand=Nike" className="category-pill"><i className="fas fa-check"></i> Nike</Link>
                    <Link href="/search?brand=Adidas" className="category-pill"><i className="fas fa-check"></i> Adidas</Link>
                    <Link href="/search?brand=Jordan" className="category-pill"><i className="fas fa-check"></i> Jordan</Link>
                    <Link href="/search?brand=Puma" className="category-pill"><i className="fas fa-check"></i> Puma</Link>
                    <Link href="/search?brand=New Balance" className="category-pill"><i className="fas fa-check"></i> New Balance</Link>
                    <Link href="/search?q=campus" className="category-pill">🇮🇳 Campus</Link>
                    <Link href="/search?q=sparx" className="category-pill">🇮🇳 Sparx</Link>
                    <Link href="/search?q=woodland" className="category-pill">🇮🇳 Woodland</Link>
                </div>
            </div>

            {/* Price Comparison Section — BuyHatke Style */}
            <div style={{ margin: '30px 0', background: 'var(--bg-secondary)', borderRadius: 'var(--radius-lg)', border: '1px solid var(--glass-border)', padding: 24, overflow: 'hidden' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 20 }}>
                    <i className="fas fa-chart-line" style={{ color: 'var(--primary-color)', fontSize: 20 }}></i>
                    <h2 style={{ margin: 0, fontSize: 20 }}>Price Comparison</h2>
                    <span style={{ marginLeft: 'auto', fontSize: 12, color: 'var(--text-secondary)' }}>Powered by Sneakerheads</span>
                </div>
                <div style={{ overflowX: 'auto' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14 }}>
                        <thead>
                            <tr style={{ borderBottom: '1px solid var(--glass-border)' }}>
                                <th style={{ textAlign: 'left', padding: '10px 12px', color: 'var(--text-secondary)', fontWeight: 500 }}>Sneaker</th>
                                <th style={{ textAlign: 'center', padding: '10px 12px', color: 'var(--text-secondary)', fontWeight: 500 }}>Sneakerheads</th>
                                <th style={{ textAlign: 'center', padding: '10px 12px', color: 'var(--text-secondary)', fontWeight: 500 }}>Amazon</th>
                                <th style={{ textAlign: 'center', padding: '10px 12px', color: 'var(--text-secondary)', fontWeight: 500 }}>Flipkart</th>
                                <th style={{ textAlign: 'center', padding: '10px 12px', color: 'var(--text-secondary)', fontWeight: 500 }}>Nike.in</th>
                                <th style={{ textAlign: 'center', padding: '10px 12px', color: 'var(--text-secondary)', fontWeight: 500 }}>You Save</th>
                            </tr>
                        </thead>
                        <tbody>
                            {[
                                { name: 'Jordan 1 Retro High', ours: 16995, amazon: 21999, flipkart: 19999, nike: 18295 },
                                { name: 'Yeezy 350 V2', ours: 22999, amazon: 29999, flipkart: 27499, nike: null },
                                { name: 'Dunk Low Panda', ours: 8995, amazon: 12499, flipkart: 10999, nike: 9695 },
                                { name: 'Air Max 90', ours: 12499, amazon: 15999, flipkart: 14499, nike: 13995 },
                            ].map((row, i) => {
                                const prices = [row.ours, row.amazon, row.flipkart, row.nike].filter(Boolean);
                                const maxPrice = Math.max(...prices);
                                const saved = maxPrice - row.ours;
                                const fmt = (p) => p ? `₹${p.toLocaleString('en-IN')}` : '—';
                                return (
                                    <tr key={i} style={{ borderBottom: '1px solid rgba(255,255,255,0.04)' }}>
                                        <td style={{ padding: '12px', fontWeight: 600 }}>{row.name}</td>
                                        <td style={{ padding: '12px', textAlign: 'center', color: '#06d6a0', fontWeight: 700 }}>{fmt(row.ours)} ✓</td>
                                        <td style={{ padding: '12px', textAlign: 'center', color: 'var(--text-secondary)' }}>{fmt(row.amazon)}</td>
                                        <td style={{ padding: '12px', textAlign: 'center', color: 'var(--text-secondary)' }}>{fmt(row.flipkart)}</td>
                                        <td style={{ padding: '12px', textAlign: 'center', color: 'var(--text-secondary)' }}>{fmt(row.nike)}</td>
                                        <td style={{ padding: '12px', textAlign: 'center' }}>
                                            <span style={{ background: 'rgba(6,214,160,0.12)', color: '#06d6a0', padding: '3px 10px', borderRadius: 50, fontSize: 12, fontWeight: 600 }}>Save {fmt(saved)}</span>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="featured-section">
                <div className="section-header">
                    <h2>🔥 Featured Sneakers</h2>
                    <Link href="/search?featured=true" className="view-all">View All <i className="fas fa-arrow-right"></i></Link>
                </div>
                <div className="grid">
                    {displayFeatured.map(s => <SneakerCard key={s.id} sneaker={s} />)}
                </div>
            </div>

            {/* New Arrivals */}
            <div className="featured-section">
                <div className="section-header">
                    <h2>✨ New Arrivals</h2>
                    <Link href="/search?sort=newest" className="view-all">View All <i className="fas fa-arrow-right"></i></Link>
                </div>
                <div className="grid">
                    {displayArrivals.slice(0, 4).map(s => <SneakerCard key={s.id} sneaker={s} />)}
                </div>
            </div>

            {/* Big Deal Banner */}
            <div className="deal-banner">
                <div className="deal-content">
                    <span className="deal-badge">LIMITED TIME</span>
                    <h2>Up to 40% Off on Premium Sneakers</h2>
                    <p>Shop our exclusive collection of authenticated sneakers at unbeatable prices</p>
                    <Link href="/search" className="btn btn-gradient">Shop the Sale <i className="fas fa-arrow-right" style={{ marginLeft: 6 }}></i></Link>
                </div>
            </div>

            {/* Popular Brands */}
            <div className="brands-section">
                <div className="section-header">
                    <h2>🏆 Popular Brands</h2>
                    <Link href="/search" className="view-all">View All <i className="fas fa-arrow-right"></i></Link>
                </div>
                <div className="brands-grid">
                    {brands.length > 0 ? brands.map(b => (
                        <Link key={b.brand} href={`/search?brand=${encodeURIComponent(b.brand)}`} className="brand-card">
                            <h3>{b.brand}</h3>
                            <p>{b.count} products</p>
                        </Link>
                    )) : ['Nike', 'Adidas', 'Jordan', 'Puma', 'New Balance', 'Reebok'].map(b => (
                        <Link key={b} href={`/search?brand=${encodeURIComponent(b)}`} className="brand-card">
                            <h3>{b}</h3>
                            <p>Shop collection</p>
                        </Link>
                    ))}
                </div>
            </div>

            {/* How It Works */}
            <div className="how-it-works">
                <div className="section-header"><h2>🛡️ How It Works</h2></div>
                <div className="steps-grid">
                    <div className="step-card"><div className="step-icon"><i className="fas fa-search"></i></div><h3>Find</h3><p>Browse our collection of authentic sneakers from verified sellers across India</p></div>
                    <div className="step-card"><div className="step-icon"><i className="fas fa-shield-alt"></i></div><h3>Authenticate</h3><p>Every pair is verified by our experts before it reaches you</p></div>
                    <div className="step-card"><div className="step-icon"><i className="fas fa-box"></i></div><h3>Enjoy</h3><p>Receive your authenticated sneakers with free shipping & easy returns</p></div>
                </div>
            </div>
        </div>
    );
}

export default function HomePage() {
    return (
        <Suspense fallback={<div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>}>
            <HomeContent />
        </Suspense>
    );
}
