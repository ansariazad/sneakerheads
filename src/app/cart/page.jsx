'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getCart, removeFromCart, formatPrice } from '@/lib/db';

export default function CartPage() {
    const { user, loading: authLoading } = useAuth();
    const router = useRouter();
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login'); return; }
        getCart(user.id).then(setItems).catch(console.error).finally(() => setLoading(false));
    }, [user, authLoading, router]);

    const total = items.reduce((sum, item) => sum + Number(item.sneaker?.price || 0), 0);
    const handleRemove = async (cartId) => {
        await removeFromCart(cartId);
        setItems(items.filter(i => i.id !== cartId));
    };

    if (loading || authLoading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;

    const placeholderImg = (s) => s.sneaker?.images?.[0]?.image_url || `https://placehold.co/200x200/2d2d2d/ecf0f1?text=${encodeURIComponent(s.sneaker?.brand || '')}`;

    return (
        <div className="container">
            <h1 className="page-title">Shopping Cart</h1>
            {items.length > 0 ? (
                <div className="cart-container">
                    <div className="cart-items">
                        {items.map(item => (
                            <div key={item.id} className="cart-item">
                                <div className="cart-item-image"><img src={placeholderImg(item)} alt={`${item.sneaker?.brand} ${item.sneaker?.model}`} /></div>
                                <div className="cart-item-details">
                                    <h3>{item.sneaker?.brand} {item.sneaker?.model}</h3>
                                    <p>Size: {item.sneaker?.size} UK</p>
                                </div>
                                <div className="cart-item-price">{formatPrice(item.sneaker?.price)}</div>
                                <div className="cart-item-actions"><button className="btn btn-danger" onClick={() => handleRemove(item.id)}>Remove</button></div>
                            </div>
                        ))}
                    </div>
                    <div className="cart-summary">
                        <h2>Order Summary</h2>
                        <div className="summary-row"><span>Subtotal</span><span>{formatPrice(total)}</span></div>
                        <div className="summary-row"><span>Shipping</span><span>Free</span></div>
                        <div className="summary-row total"><span>Total</span><span>{formatPrice(total)}</span></div>
                        <Link href="/checkout" className="btn btn-success checkout-btn">Proceed to Checkout</Link>
                        <div className="continue-shopping"><Link href="/">Continue Shopping</Link></div>
                    </div>
                </div>
            ) : (
                <div className="empty-cart">
                    <div className="empty-cart-icon"><i className="fas fa-shopping-cart"></i></div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven&apos;t added any sneakers to your cart yet.</p>
                    <Link href="/" className="btn">Start Shopping</Link>
                </div>
            )}
        </div>
    );
}
