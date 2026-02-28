'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getOrders, getOrder, formatPrice } from '@/lib/db';
import { generateInvoice } from '@/lib/invoice';

export default function OrdersPage() {
    const { user, loading: authLoading } = useAuth();
    const router = useRouter();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [downloadingId, setDownloadingId] = useState(null);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login?redirect=/orders'); return; }
        getOrders(user.id).then(setOrders).catch(console.error).finally(() => setLoading(false));
    }, [user, authLoading, router]);

    const handleDownloadInvoice = async (orderId) => {
        setDownloadingId(orderId);
        try {
            const order = await getOrder(orderId);
            generateInvoice(order);
        } catch (err) {
            console.error('Invoice error:', err);
            alert('Failed to generate invoice. Please try again.');
        } finally {
            setDownloadingId(null);
        }
    };

    if (loading || authLoading) return (
        <div className="container">
            <div style={{ textAlign: 'center', padding: 80 }}>
                <div className="btn-spinner" style={{ width: 36, height: 36, margin: '0 auto 16px', borderWidth: 3 }}></div>
                <p style={{ color: 'var(--text-secondary)' }}>Loading orders...</p>
            </div>
        </div>
    );

    const statusColors = {
        pending: '#f59e0b',
        confirmed: '#3b82f6',
        shipped: '#8b5cf6',
        delivered: '#06d6a0',
        cancelled: '#ef4444',
    };

    return (
        <div className="container">
            <h1 className="page-title">My Orders</h1>
            {orders.length > 0 ? (
                <div className="orders-list">
                    {orders.map(order => (
                        <div key={order.id} className="order-card">
                            <Link href={`/orders/${order.id}`} style={{ textDecoration: 'none', color: 'inherit', flex: 1 }}>
                                <div className="order-info">
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
                                        <h3 style={{ margin: 0 }}>Order #{order.id.slice(0, 8)}</h3>
                                        <span className="order-status" style={{
                                            background: `${statusColors[order.order_status] || '#64748b'}20`,
                                            color: statusColors[order.order_status] || '#64748b',
                                            padding: '3px 10px',
                                            borderRadius: 50,
                                            fontSize: 12,
                                            fontWeight: 600,
                                            textTransform: 'capitalize',
                                        }}>{order.order_status}</span>
                                    </div>
                                    <p style={{ color: 'var(--text-secondary)', fontSize: 14, marginBottom: 4 }}>
                                        {new Date(order.created_at).toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' })} • {order.items?.length || 0} item(s)
                                    </p>
                                    <p style={{ fontWeight: 600, fontSize: 16 }}>{formatPrice(order.total_amount)}</p>
                                </div>
                            </Link>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                <button
                                    className="btn btn-sm"
                                    onClick={(e) => { e.preventDefault(); handleDownloadInvoice(order.id); }}
                                    disabled={downloadingId === order.id}
                                    style={{ fontSize: 13, padding: '8px 14px', borderRadius: 8, background: 'var(--bg-light)', border: '1px solid var(--glass-border)', color: 'var(--text-color)', whiteSpace: 'nowrap' }}
                                >
                                    {downloadingId === order.id ? (
                                        <span className="btn-spinner" style={{ width: 14, height: 14 }}></span>
                                    ) : (
                                        <><i className="fas fa-file-pdf" style={{ marginRight: 6 }}></i>Invoice</>
                                    )}
                                </button>
                                <Link href={`/orders/${order.id}`}>
                                    <i className="fas fa-chevron-right" style={{ color: 'var(--text-secondary)' }}></i>
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="empty-cart">
                    <div className="empty-cart-icon"><i className="fas fa-shopping-bag"></i></div>
                    <h2>No orders yet</h2>
                    <p>Start shopping to see your orders here.</p>
                    <Link href="/search" className="btn btn-gradient">Browse Sneakers</Link>
                </div>
            )}
        </div>
    );
}
