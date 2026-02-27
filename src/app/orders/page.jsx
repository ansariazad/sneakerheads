'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getOrders, formatPrice } from '@/lib/db';

export default function OrdersPage() {
    const { user, loading: authLoading } = useAuth();
    const router = useRouter();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login'); return; }
        getOrders(user.id).then(setOrders).catch(console.error).finally(() => setLoading(false));
    }, [user, authLoading, router]);

    if (loading || authLoading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;

    return (
        <div className="container">
            <h1 className="page-title">My Orders</h1>
            {orders.length > 0 ? (
                <div className="orders-list">
                    {orders.map(order => (
                        <Link key={order.id} href={`/orders/${order.id}`} style={{ textDecoration: 'none', color: 'inherit' }}>
                            <div className="order-card">
                                <div className="order-info">
                                    <h3>Order #{order.id.slice(0, 8)}</h3>
                                    <p>{new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })} • {order.items?.length || 0} item(s)</p>
                                    <p>Total: {formatPrice(order.total_amount)}</p>
                                </div>
                                <span className={`order-status status-${order.order_status}`}>{order.order_status}</span>
                                <i className="fas fa-chevron-right" style={{ color: 'var(--text-secondary)' }}></i>
                            </div>
                        </Link>
                    ))}
                </div>
            ) : (
                <div className="empty-cart">
                    <div className="empty-cart-icon"><i className="fas fa-shopping-bag"></i></div>
                    <h2>No orders yet</h2>
                    <p>Start shopping to see your orders here.</p>
                    <Link href="/" className="btn">Start Shopping</Link>
                </div>
            )}
        </div>
    );
}
