'use client';
import { useState, useEffect } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { getOrder, formatPrice } from '@/lib/db';

export default function OrderDetailPage() {
    const { id } = useParams();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        getOrder(id).then(setOrder).catch(console.error).finally(() => setLoading(false));
    }, [id]);

    if (loading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;
    if (!order) return <div className="container"><p style={{ textAlign: 'center', padding: 60 }}>Order not found.</p></div>;

    return (
        <div className="container">
            <h1 className="page-title">Order #{order.id.slice(0, 8)}</h1>
            <div className="checkout-container">
                <div className="checkout-form">
                    <section className="checkout-section">
                        <h2>Order Items</h2>
                        {order.items?.map(item => (
                            <div key={item.id} className="order-item" style={{ marginBottom: 15 }}>
                                <div className="order-item-details">
                                    <h3>{item.sneaker?.brand} {item.sneaker?.model}</h3>
                                    <p>Size: {item.sneaker?.size} UK</p>
                                </div>
                                <div className="order-item-price">{formatPrice(item.price)}</div>
                            </div>
                        ))}
                    </section>
                    {order.address && (
                        <section className="checkout-section">
                            <h2>Shipping Address</h2>
                            <div className="address-card"><div className="address-details">
                                <p>{order.address.address_line1}</p>
                                {order.address.address_line2 && <p>{order.address.address_line2}</p>}
                                <p>{order.address.city}, {order.address.state} {order.address.postal_code}</p>
                                <p>{order.address.country}</p>
                            </div></div>
                        </section>
                    )}
                </div>
                <div className="order-summary">
                    <h2>Order Summary</h2>
                    <div className="detail-row"><span style={{ color: 'var(--text-secondary)' }}>Status</span><span className={`order-status status-${order.order_status}`}>{order.order_status}</span></div>
                    <div className="detail-row"><span style={{ color: 'var(--text-secondary)' }}>Tracking ID</span><span>{order.tracking_id || 'N/A'}</span></div>
                    <div className="detail-row"><span style={{ color: 'var(--text-secondary)' }}>Payment</span><span>{order.payment_method?.toUpperCase()}</span></div>
                    <div className="detail-row"><span style={{ color: 'var(--text-secondary)' }}>Payment Status</span><span>{order.payment_status}</span></div>
                    <div className="detail-row"><span style={{ color: 'var(--text-secondary)' }}>Order Date</span><span>{new Date(order.created_at).toLocaleDateString()}</span></div>
                    {order.delivery_eta && <div className="detail-row"><span style={{ color: 'var(--text-secondary)' }}>Delivery ETA</span><span>{new Date(order.delivery_eta).toLocaleDateString()}</span></div>}
                    <div className="summary-row total"><span>Total</span><span>{formatPrice(order.total_amount)}</span></div>
                    <Link href="/orders" className="btn btn-secondary" style={{ width: '100%', textAlign: 'center', marginTop: 15, display: 'block' }}>Back to Orders</Link>
                </div>
            </div>
        </div>
    );
}
