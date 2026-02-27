'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { getSneakers, getBrands } from '@/lib/db';
import { formatPrice } from '@/lib/db';
import SneakerCard from '@/components/SneakerCard';

export default function HomePage() {
    const [featured, setFeatured] = useState([]);
    const [newArrivals, setNewArrivals] = useState([]);
    const [brands, setBrands] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const load = async () => {
            try {
                const featuredRes = await getSneakers({ featured: true, limit: 4 });
                setFeatured(featuredRes.data || []);
            } catch (err) {
                console.error('Error loading featured:', JSON.stringify(err), err?.message, err?.code);
            }
            try {
                const newRes = await getSneakers({ limit: 8 });
                setNewArrivals(newRes.data || []);
            } catch (err) {
                console.error('Error loading new arrivals:', JSON.stringify(err), err?.message);
            }
            try {
                const brandsData = await getBrands();
                setBrands((brandsData || []).slice(0, 6));
            } catch (err) {
                console.error('Error loading brands:', JSON.stringify(err), err?.message);
            }
            setLoading(false);
        };
        load();
    }, []);

    return (
        <div className="container">
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

            <div className="featured-section">
                <div className="section-header">
                    <h2>Featured Sneakers</h2>
                    <Link href="/search?featured=true">View All →</Link>
                </div>
                {loading ? (
                    <p style={{ textAlign: 'center', color: 'var(--text-secondary)', padding: 40 }}>Loading sneakers...</p>
                ) : featured.length > 0 ? (
                    <div className="grid">{featured.map(s => <SneakerCard key={s.id} sneaker={s} />)}</div>
                ) : (
                    <p style={{ textAlign: 'center', color: 'var(--text-secondary)', padding: 40 }}>No featured sneakers yet. Be the first to list one!</p>
                )}
            </div>

            <div className="featured-section">
                <div className="section-header">
                    <h2>New Arrivals</h2>
                    <Link href="/search?sort=newest">View All →</Link>
                </div>
                {!loading && newArrivals.length > 0 ? (
                    <div className="grid">{newArrivals.map(s => <SneakerCard key={s.id} sneaker={s} />)}</div>
                ) : !loading ? (
                    <p style={{ textAlign: 'center', color: 'var(--text-secondary)', padding: 40 }}>No sneakers listed yet.</p>
                ) : null}
            </div>

            <div className="brands-section">
                <div className="section-header">
                    <h2>Popular Brands</h2>
                    <Link href="/search">View All →</Link>
                </div>
                <div className="brands-grid">
                    {brands.map(b => (
                        <Link key={b.brand} href={`/search?brand=${encodeURIComponent(b.brand)}`} className="brand-card fade-in">
                            <h3>{b.brand}</h3>
                            <p>{b.count} products</p>
                        </Link>
                    ))}
                </div>
            </div>

            <div className="how-it-works">
                <div className="section-header"><h2>How It Works</h2></div>
                <div className="steps-grid">
                    <div className="step-card fade-in"><div className="step-icon"><i className="fas fa-search"></i></div><h3>Find</h3><p>Browse our collection of authentic sneakers from top sellers</p></div>
                    <div className="step-card fade-in"><div className="step-icon"><i className="fas fa-shopping-cart"></i></div><h3>Buy</h3><p>Purchase with confidence using our secure payment system</p></div>
                    <div className="step-card fade-in"><div className="step-icon"><i className="fas fa-box"></i></div><h3>Enjoy</h3><p>Receive your authenticated sneakers delivered to your doorstep</p></div>
                </div>
            </div>
        </div>
    );
}
