'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { getAdminStats, getAllOrders, formatPrice } from '@/lib/db';

export default function AdminDashboardPage() {
    const [stats, setStats] = useState({ userCount: 0, sneakerCount: 0, orderCount: 0, totalRevenue: 0 });
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        Promise.all([getAdminStats(), getAllOrders()])
            .then(([s, o]) => { setStats(s); setOrders(o.slice(0, 5)); })
            .catch(console.error).finally(() => setLoading(false));
    }, []);

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Admin Dashboard</h2></div>
            <div className="dashboard-stats">
                <div className="stat-card"><h3>{stats.userCount}</h3><p>Total Users</p></div>
                <div className="stat-card"><h3>{stats.sneakerCount}</h3><p>Total Sneakers</p></div>
                <div className="stat-card"><h3>{stats.orderCount}</h3><p>Total Orders</p></div>
                <div className="stat-card"><h3>{formatPrice(stats.totalRevenue)}</h3><p>Total Revenue</p></div>
            </div>
            <h3 style={{ marginBottom: 15 }}>Recent Orders</h3>
            {orders.length > 0 ? (
                <div className="table-container"><table>
                    <thead><tr><th>Order</th><th>User</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>{orders.map(o => (
                        <tr key={o.id}><td>#{o.id.slice(0, 8)}</td><td>{o.user?.username}</td><td>{formatPrice(o.total_amount)}</td>
                            <td><span className={`order-status status-${o.order_status}`}>{o.order_status}</span></td>
                            <td>{new Date(o.created_at).toLocaleDateString()}</td>
                            <td><Link href={`/admin/orders/${o.id}`} className="btn" style={{ fontSize: 13, padding: '5px 10px' }}>View</Link></td></tr>
                    ))}</tbody>
                </table></div>
            ) : <p style={{ color: 'var(--text-secondary)' }}>No orders yet.</p>}
        </>
    );
}
