'use client';
import { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { getSneaker, getSimilarSneakers, addToCart, addToWishlist, formatPrice } from '@/lib/db';
import { useAuth } from '@/components/AuthProvider';
import SneakerCard from '@/components/SneakerCard';

export default function SneakerDetailPage() {
    const { id } = useParams();
    const router = useRouter();
    const { user } = useAuth();
    const [sneaker, setSneaker] = useState(null);
    const [similar, setSimilar] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedImage, setSelectedImage] = useState(0);
    const [message, setMessage] = useState({ text: '', type: '' });
    const [addingToCart, setAddingToCart] = useState(false);

    useEffect(() => {
        const load = async () => {
            try {
                const s = await getSneaker(id);
                setSneaker(s);
                const sim = await getSimilarSneakers(s);
                setSimilar(sim);
            } catch (err) {
                console.error(err);
            } finally { setLoading(false); }
        };
        load();
    }, [id]);

    const handleAddToCart = async () => {
        if (!user) { window.location.href = `/login?redirect=/sneaker/${id}`; return; }
        setAddingToCart(true);
        try {
            await addToCart(user.id, sneaker.id);
            setMessage({ text: '✅ Added to cart!', type: 'success' });
            setTimeout(() => setMessage({ text: '', type: '' }), 3000);
        } catch (err) {
            setMessage({ text: '❌ Failed to add to cart. Please try again.', type: 'error' });
        } finally { setAddingToCart(false); }
    };

    const handleAddToWishlist = async () => {
        if (!user) { window.location.href = `/login?redirect=/sneaker/${id}`; return; }
        try {
            await addToWishlist(user.id, sneaker.id);
            setMessage({ text: '❤️ Added to wishlist!', type: 'success' });
            setTimeout(() => setMessage({ text: '', type: '' }), 3000);
        } catch (err) { setMessage({ text: 'Failed to add to wishlist.', type: 'error' }); }
    };

    if (loading) return (
        <div className="container" style={{ textAlign: 'center', padding: 80 }}>
            <div className="loading-logo-pulse">
                <Image src="/images/sneakerheads-logo.svg" alt="Loading" width={60} height={60} priority />
            </div>
            <p style={{ color: 'var(--text-secondary)', marginTop: 16 }}>Loading sneaker details...</p>
        </div>
    );
    if (!sneaker) return (
        <div className="container" style={{ textAlign: 'center', padding: 80 }}>
            <i className="fas fa-shoe-prints" style={{ fontSize: 48, color: 'var(--text-secondary)', marginBottom: 16 }}></i>
            <h2>Sneaker not found</h2>
            <p style={{ color: 'var(--text-secondary)', marginBottom: 20 }}>This sneaker may have been sold or removed.</p>
            <Link href="/search" className="btn btn-gradient">Browse Sneakers</Link>
        </div>
    );

    const images = sneaker.images?.length > 0
        ? sneaker.images.sort((a, b) => a.display_order - b.display_order)
        : [{ image_url: '/images/sneakers/nike-jordan1.jpg' }];

    return (
        <div className="container">
            {/* Toast message */}
            {message.text && (
                <div className={`toast toast-${message.type}`}>
                    <span>{message.text}</span>
                    <button onClick={() => setMessage({ text: '', type: '' })} className="toast-close"><i className="fas fa-times"></i></button>
                </div>
            )}
            <div className="sneaker-detail">
                <div className="sneaker-gallery">
                    <div className="main-image">
                        <Image
                            src={images[selectedImage]?.image_url}
                            alt={`${sneaker.brand} ${sneaker.model}`}
                            fill
                            sizes="(max-width: 768px) 100vw, 50vw"
                            style={{ objectFit: 'contain' }}
                            priority
                        />
                    </div>
                    {images.length > 1 && (
                        <div className="thumbnail-grid">
                            {images.map((img, i) => (
                                <div key={i} className={`thumbnail${i === selectedImage ? ' active' : ''}`} onClick={() => setSelectedImage(i)}>
                                    <Image src={img.image_url} alt={`View ${i + 1}`} width={80} height={80} style={{ objectFit: 'cover' }} />
                                </div>
                            ))}
                        </div>
                    )}
                </div>
                <div className="sneaker-info">
                    <div className="card-brand" style={{ fontSize: 14, marginBottom: 4 }}>{sneaker.brand}</div>
                    <h1 className="sneaker-title">{sneaker.brand} {sneaker.model}</h1>
                    <div className="sneaker-price">{formatPrice(sneaker.price)}</div>
                    <div className="sneaker-meta">
                        <div className="meta-item"><span className="meta-label">Brand</span><span>{sneaker.brand}</span></div>
                        <div className="meta-item"><span className="meta-label">Size</span><span>{sneaker.size} UK</span></div>
                        <div className="meta-item"><span className="meta-label">Condition</span><span style={{ textTransform: 'capitalize' }}>{sneaker.condition?.replace('_', ' ')}</span></div>
                        <div className="meta-item"><span className="meta-label">Serial Number</span><span>{sneaker.serial_number}</span></div>
                        <div className="meta-item"><span className="meta-label">Seller</span><span>{sneaker.seller?.username}</span></div>
                    </div>
                    {sneaker.description && <div className="sneaker-description"><h3>Description</h3><p>{sneaker.description}</p></div>}
                    <div className="sneaker-actions">
                        <button className="btn btn-gradient" onClick={handleAddToCart} disabled={addingToCart} style={{ flex: 1 }}>
                            {addingToCart ? <><span className="btn-spinner"></span> Adding...</> : <><i className="fas fa-shopping-bag" style={{ marginRight: 8 }}></i>Add to Cart</>}
                        </button>
                        <button className="btn btn-secondary" onClick={handleAddToWishlist}><i className="fas fa-heart"></i> Wishlist</button>
                    </div>
                    <div className="sneaker-guarantees">
                        <div className="guarantee-item"><i className="fas fa-shield-alt"></i><span>100% Authentic</span></div>
                        <div className="guarantee-item"><i className="fas fa-shipping-fast"></i><span>Free Shipping</span></div>
                        <div className="guarantee-item"><i className="fas fa-undo"></i><span>Easy Returns</span></div>
                    </div>
                </div>
            </div>
            {similar.length > 0 && (
                <div className="featured-section">
                    <div className="section-header"><h2>Similar Sneakers</h2></div>
                    <div className="grid">{similar.map(s => <SneakerCard key={s.id} sneaker={s} />)}</div>
                </div>
            )}
        </div>
    );
}
