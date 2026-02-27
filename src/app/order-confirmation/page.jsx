'use client';
import { useState, useEffect, Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import { useAuth } from '@/components/AuthProvider';
import { getOrder, formatPrice } from '@/lib/db';
import { generateInvoice } from '@/lib/invoice';

function OrderConfirmationContent() {
    const searchParams = useSearchParams();
    const { user, loading: authLoading } = useAuth();
    const orderId = searchParams.get('orderId');
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (authLoading) return;
        if (!orderId) { setLoading(false); return; }
        getOrder(orderId).then(setOrder).catch(console.error).finally(() => setLoading(false));
    }, [orderId, authLoading]);

    if (loading || authLoading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;

    return (
        <div className="container">
            <div className="confirmation-container">
                <div className="confirmation-icon"><i className="fas fa-check-circle"></i></div>
                <h1>Order Placed Successfully!</h1>
                <p>Thank you for your purchase. Your order has been confirmed.</p>
                <div className="confirmation-details">
                    <h2>Order Details</h2>
                    {order ? (
                        <>
                            <div className="detail-row"><span>Order ID</span><span>#{order.id?.slice(0, 8)}</span></div>
                            <div className="detail-row"><span>Order Date</span><span>{new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>
                            <div className="detail-row"><span>Payment Method</span><span style={{ textTransform: 'uppercase' }}>{order.payment_method}</span></div>
                            <div className="detail-row"><span>Total Amount</span><span>{formatPrice(order.total_amount)}</span></div>
                            <div className="detail-row"><span>Tracking ID</span><span>{order.tracking_id}</span></div>
                            <div className="detail-row"><span>Estimated Delivery</span><span>{order.delivery_eta ? new Date(order.delivery_eta).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '3-5 business days'}</span></div>
                            {order.items?.length > 0 && (
                                <div style={{ marginTop: 20, borderTop: '1px solid var(--border-color)', paddingTop: 15 }}>
                                    <h3 style={{ marginBottom: 10 }}>Items</h3>
                                    {order.items.map((item, i) => (
                                        <div key={i} className="detail-row">
                                            <span>{item.sneaker?.brand} {item.sneaker?.model} (Size {item.sneaker?.size})</span>
                                            <span>{formatPrice(item.price)}</span>
                                        </div>
                                    ))}
                                </div>
                            )}
                            {order.payment_method === 'upi' && order.payment_status !== 'completed' && (
                                <div style={{ marginTop: 15, textAlign: 'center' }}>
                                    <Link href={`/payment?orderId=${order.id}`} className="btn btn-gradient">
                                        <i className="fas fa-mobile-alt"></i> Complete UPI Payment
                                    </Link>
                                </div>
                            )}
                        </>
                    ) : (
                        <>
                            <div className="detail-row"><span>Order Date</span><span>{new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>
                            <div className="detail-row"><span>Estimated Delivery</span><span>{new Date(Date.now() + 5 * 86400000).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>
                        </>
                    )}
                </div>
                <div className="confirmation-actions">
                    {order && <button className="btn btn-gradient" onClick={() => generateInvoice(order)}><i className="fas fa-file-pdf" style={{ marginRight: 8 }}></i>Download Invoice</button>}
                    <Link href="/orders" className="btn">View My Orders</Link>
                    <Link href="/" className="btn btn-secondary">Continue Shopping</Link>
                </div>
            </div>
        </div>
    );
}

export default function OrderConfirmationPage() {
    return (
        <Suspense fallback={<div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>}>
            <OrderConfirmationContent />
        </Suspense>
    );
}
