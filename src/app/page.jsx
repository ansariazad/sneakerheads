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

            {/* Featured Sneakers */}
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
