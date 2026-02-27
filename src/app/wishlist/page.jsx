'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getWishlist, removeFromWishlist, formatPrice } from '@/lib/db';

export default function WishlistPage() {
    const { user, loading: authLoading } = useAuth();
    const router = useRouter();
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login'); return; }
        getWishlist(user.id).then(setItems).catch(console.error).finally(() => setLoading(false));
    }, [user, authLoading, router]);

    const handleRemove = async (wishlistId) => {
        await removeFromWishlist(wishlistId);
        setItems(items.filter(i => i.id !== wishlistId));
    };

    if (loading || authLoading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;

    return (
        <div className="container">
            <h1 className="page-title">My Wishlist</h1>
            {items.length > 0 ? (
                <div className="wishlist-grid">
                    {items.map(item => {
                        const s = item.sneaker;
                        const img = s?.images?.[0]?.image_url || `https://placehold.co/400x300/2d2d2d/ecf0f1?text=${encodeURIComponent(s?.brand + ' ' + s?.model)}`;
                        return (
                            <div key={item.id} className="wishlist-card sneaker-card">
                                <button className="wishlist-remove" onClick={() => handleRemove(item.id)}><i className="fas fa-times"></i></button>
                                <div className="card-img"><img src={img} alt={`${s?.brand} ${s?.model}`} /></div>
                                <div className="card-body">
                                    <h3 className="card-title">{s?.brand} {s?.model}</h3>
                                    <p className="card-text">Size: {s?.size} UK</p>
                                    <div className="card-price">{formatPrice(s?.price)}</div>
                                </div>
                                <div className="card-footer"><Link href={`/sneaker/${s?.id}`} className="btn">View Details</Link></div>
                            </div>
                        );
                    })}
                </div>
            ) : (
                <div className="empty-cart">
                    <div className="empty-cart-icon"><i className="fas fa-heart"></i></div>
                    <h2>Your wishlist is empty</h2>
                    <p>Save your favorite sneakers to see them here.</p>
                    <Link href="/search" className="btn">Browse Sneakers</Link>
                </div>
            )}
        </div>
    );
}
