'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getCart, removeFromCart, formatPrice } from '@/lib/db';

export default function CartPage() {
    const { user, loading: authLoading } = useAuth();
    const router = useRouter();
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [removing, setRemoving] = useState(null);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login?redirect=/cart'); return; }
        loadCart();
    }, [user, authLoading, router]);

    const loadCart = async () => {
        if (!user) return;
        setLoading(true);
        setError('');
        try {
            const data = await getCart(user.id);
            setItems(data);
        } catch (err) {
            console.error('Cart load error:', err);
            setError('Failed to load cart. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const total = items.reduce((sum, item) => sum + Number(item.sneaker?.price || 0), 0);

    const handleRemove = async (cartId) => {
        setRemoving(cartId);
        try {
            await removeFromCart(cartId);
            setItems(items.filter(i => i.id !== cartId));
        } catch (err) {
            setError('Failed to remove item. Please try again.');
        } finally {
            setRemoving(null);
        }
    };

    if (authLoading) return (
        <div className="container">
            <div style={{ textAlign: 'center', padding: 80 }}>
                <div className="btn-spinner" style={{ width: 36, height: 36, margin: '0 auto 16px', borderWidth: 3 }}></div>
                <p style={{ color: 'var(--text-secondary)' }}>Loading cart...</p>
            </div>
        </div>
    );

    if (loading) return (
        <div className="container">
            <h1 className="page-title">Shopping Cart</h1>
            <div style={{ textAlign: 'center', padding: 60 }}>
                <div className="btn-spinner" style={{ width: 30, height: 30, margin: '0 auto 12px', borderWidth: 3 }}></div>
                <p style={{ color: 'var(--text-secondary)' }}>Loading your cart...</p>
            </div>
        </div>
    );

    const getImage = (item) => {
        const url = item.sneaker?.images?.[0]?.image_url;
        if (url) return url;
        return `/images/sneakers/nike-jordan1.jpg`;
    };

    return (
        <div className="container">
            <h1 className="page-title">Shopping Cart</h1>
            {error && <div className="alert alert-error"><i className="fas fa-exclamation-circle"></i> {error} <button onClick={loadCart} style={{ marginLeft: 10, textDecoration: 'underline', background: 'none', border: 'none', color: 'inherit', cursor: 'pointer' }}>Retry</button></div>}
            {items.length > 0 ? (
                <div className="cart-container">
                    <div className="cart-items">
                        {items.map(item => (
                            <div key={item.id} className="cart-item">
                                <div className="cart-item-image">
                                    <Image src={getImage(item)} alt={`${item.sneaker?.brand} ${item.sneaker?.model}`} width={120} height={120} style={{ objectFit: 'cover', borderRadius: 8 }} />
                                </div>
                                <div className="cart-item-details">
                                    <h3>{item.sneaker?.brand} {item.sneaker?.model}</h3>
                                    <p>Size: {item.sneaker?.size} UK</p>
                                    <p className="cart-item-condition">{item.sneaker?.condition || 'New'}</p>
                                </div>
                                <div className="cart-item-price">{formatPrice(item.sneaker?.price)}</div>
                                <div className="cart-item-actions">
                                    <button className="btn btn-danger" onClick={() => handleRemove(item.id)} disabled={removing === item.id}>
                                        {removing === item.id ? <span className="btn-spinner"></span> : <><i className="fas fa-trash-alt"></i> Remove</>}
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                    <div className="cart-summary">
                        <h2>Order Summary</h2>
                        <div className="summary-row"><span>Subtotal ({items.length} item{items.length > 1 ? 's' : ''})</span><span>{formatPrice(total)}</span></div>
                        <div className="summary-row"><span>Shipping</span><span style={{ color: 'var(--accent-color)' }}>Free</span></div>
                        <div className="summary-row total"><span>Total</span><span>{formatPrice(total)}</span></div>
                        <Link href="/checkout" className="btn btn-gradient checkout-btn" style={{ marginTop: 15 }}>
                            <i className="fas fa-lock" style={{ marginRight: 8 }}></i>Proceed to Checkout
                        </Link>
                        <div className="continue-shopping"><Link href="/search">← Continue Shopping</Link></div>
                    </div>
                </div>
            ) : (
                <div className="empty-cart">
                    <div className="empty-cart-icon"><i className="fas fa-shopping-bag"></i></div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven&apos;t added any sneakers to your cart yet.</p>
                    <Link href="/search" className="btn btn-gradient">Browse Sneakers</Link>
                </div>
            )}
        </div>
    );
}
