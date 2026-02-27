'use client';
import { useState, useEffect } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { getSneaker, getSimilarSneakers, addToCart, addToWishlist, formatPrice } from '@/lib/db';
import { useAuth } from '@/components/AuthProvider';
import SneakerCard from '@/components/SneakerCard';

export default function SneakerDetailPage() {
    const { id } = useParams();
    const { user } = useAuth();
    const [sneaker, setSneaker] = useState(null);
    const [similar, setSimilar] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedImage, setSelectedImage] = useState(0);
    const [message, setMessage] = useState('');

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
        if (!user) { setMessage('Please login to add to cart.'); return; }
        try {
            await addToCart(user.id, sneaker.id);
            setMessage('Added to cart!');
            setTimeout(() => setMessage(''), 3000);
        } catch (err) { setMessage('Failed to add to cart.'); }
    };

    const handleAddToWishlist = async () => {
        if (!user) { setMessage('Please login to add to wishlist.'); return; }
        try {
            await addToWishlist(user.id, sneaker.id);
            setMessage('Added to wishlist!');
            setTimeout(() => setMessage(''), 3000);
        } catch (err) { setMessage('Failed to add to wishlist.'); }
    };

    if (loading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;
    if (!sneaker) return <div className="container"><p style={{ textAlign: 'center', padding: 60 }}>Sneaker not found.</p></div>;

    const images = sneaker.images?.length > 0
        ? sneaker.images.sort((a, b) => a.display_order - b.display_order)
        : [{ image_url: `https://placehold.co/600x400/2d2d2d/ecf0f1?text=${encodeURIComponent(sneaker.brand + ' ' + sneaker.model)}` }];

    return (
        <div className="container">
            {message && <div className="alert alert-success" style={{ marginBottom: 15 }}>{message}</div>}
            <div className="sneaker-detail">
                <div className="sneaker-gallery">
                    <div className="main-image">
                        <img src={images[selectedImage]?.image_url} alt={`${sneaker.brand} ${sneaker.model}`} />
                    </div>
                    {images.length > 1 && (
                        <div className="thumbnail-grid">
                            {images.map((img, i) => (
                                <div key={i} className={`thumbnail${i === selectedImage ? ' active' : ''}`} onClick={() => setSelectedImage(i)}>
                                    <img src={img.image_url} alt={`View ${i + 1}`} />
                                </div>
                            ))}
                        </div>
                    )}
                    {sneaker.videos?.length > 0 && (
                        <div className="video-section" style={{ marginTop: 15 }}>
                            <video src={sneaker.videos[0].video_url} controls style={{ width: '100%', borderRadius: 8 }} />
                        </div>
                    )}
                </div>
                <div className="sneaker-info">
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
                        <button className="btn btn-success" onClick={handleAddToCart}>Add to Cart</button>
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
