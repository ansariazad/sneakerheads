'use client';
import { useState, useEffect } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { getOrder, updateOrderStatus, formatPrice } from '@/lib/db';

export default function AdminOrderDetailPage() {
    const { id } = useParams();
    const [order, setOrder] = useState(null);
    const [status, setStatus] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        getOrder(id).then(o => { setOrder(o); setStatus(o.order_status); }).catch(console.error).finally(() => setLoading(false));
    }, [id]);

    const handleUpdate = async () => {
        await updateOrderStatus(id, status);
        setOrder({ ...order, order_status: status });
    };

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;
    if (!order) return <p style={{ textAlign: 'center', padding: 40 }}>Order not found.</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Order #{order.id.slice(0, 8)}</h2><Link href="/admin/orders" className="btn btn-secondary">Back</Link></div>
            <div className="confirmation-details" style={{ marginBottom: 20 }}>
                <div className="detail-row"><span>Status</span><span className={`order-status status-${order.order_status}`}>{order.order_status}</span></div>
                <div className="detail-row"><span>Payment</span><span>{order.payment_method?.toUpperCase()} ({order.payment_status})</span></div>
                <div className="detail-row"><span>Tracking</span><span>{order.tracking_id || 'N/A'}</span></div>
                <div className="detail-row"><span>Total</span><span>{formatPrice(order.total_amount)}</span></div>
                <div className="detail-row"><span>Date</span><span>{new Date(order.created_at).toLocaleDateString()}</span></div>
            </div>
            <h3 style={{ marginBottom: 15 }}>Items</h3>
            <div className="table-container"><table>
                <thead><tr><th>Sneaker</th><th>Size</th><th>Price</th></tr></thead>
                <tbody>{order.items?.map(item => (
                    <tr key={item.id}><td>{item.sneaker?.brand} {item.sneaker?.model}</td><td>{item.sneaker?.size} UK</td><td>{formatPrice(item.price)}</td></tr>
                ))}</tbody>
            </table></div>
            <div style={{ marginTop: 20, display: 'flex', alignItems: 'center', gap: 10 }}>
                <label>Update Status:</label>
                <select value={status} onChange={e => setStatus(e.target.value)} style={{ width: 'auto' }}>
                    <option value="placed">Placed</option><option value="processing">Processing</option><option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option><option value="cancelled">Cancelled</option>
                </select>
                <button className="btn" onClick={handleUpdate}>Update</button>
            </div>
        </>
    );
}
