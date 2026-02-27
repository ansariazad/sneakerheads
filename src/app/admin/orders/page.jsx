'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { getAllOrders, updateOrderStatus, formatPrice } from '@/lib/db';

export default function AdminOrdersPage() {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => { getAllOrders().then(setOrders).catch(console.error).finally(() => setLoading(false)); }, []);

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Manage Orders</h2></div>
            <div className="table-container"><table>
                <thead><tr><th>Order</th><th>User</th><th>Items</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>{orders.map(o => (
                    <tr key={o.id}><td>#{o.id.slice(0, 8)}</td><td>{o.user?.username}</td><td>{o.items?.length}</td><td>{formatPrice(o.total_amount)}</td>
                        <td><span className={`order-status status-${o.order_status}`}>{o.order_status}</span></td>
                        <td>{new Date(o.created_at).toLocaleDateString()}</td>
                        <td><Link href={`/admin/orders/${o.id}`} className="btn" style={{ fontSize: 13, padding: '5px 10px' }}>View</Link></td></tr>
                ))}</tbody>
            </table></div>
        </>
    );
}
