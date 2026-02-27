'use client';
import { useState, useEffect } from 'react';
import { useAuth } from '@/components/AuthProvider';
import { getSellerSales, requestPayment, formatPrice } from '@/lib/db';

export default function SellerPaymentsPage() {
    const { user } = useAuth();
    const [sales, setSales] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!user) return;
        getSellerSales(user.id).then(setSales).catch(console.error).finally(() => setLoading(false));
    }, [user]);

    const totalEarnings = sales.reduce((s, p) => s + Number(p.net_amount), 0);
    const completedEarnings = sales.filter(s => s.status === 'completed').reduce((s, p) => s + Number(p.net_amount), 0);

    const handleRequest = async (id) => {
        try { await requestPayment(id); setSales(sales.map(s => s.id === id ? { ...s, status: 'requested' } : s)); } catch { }
    };

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Payments</h2></div>
            <div className="dashboard-stats" style={{ gridTemplateColumns: 'repeat(3, 1fr)' }}>
                <div className="stat-card"><h3>{formatPrice(totalEarnings)}</h3><p>Total Earnings</p></div>
                <div className="stat-card"><h3>{formatPrice(completedEarnings)}</h3><p>Completed</p></div>
                <div className="stat-card"><h3>{formatPrice(totalEarnings - completedEarnings)}</h3><p>Pending</p></div>
            </div>
            {sales.length > 0 ? (
                <div className="table-container"><table>
                    <thead><tr><th>Sneaker</th><th>Amount</th><th>Fee</th><th>Net</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>{sales.map(s => (
                        <tr key={s.id}><td>{s.order_item?.sneaker?.brand} {s.order_item?.sneaker?.model}</td><td>{formatPrice(s.amount)}</td><td>{formatPrice(s.platform_fee)}</td>
                            <td style={{ color: 'var(--accent-color)', fontWeight: 'bold' }}>{formatPrice(s.net_amount)}</td>
                            <td><span className={`order-status status-${s.status === 'completed' ? 'delivered' : s.status === 'requested' ? 'pending' : 'processing'}`}>{s.status}</span></td>
                            <td>{s.status === 'pending' && <button className="btn btn-success" style={{ fontSize: 13, padding: '5px 10px' }} onClick={() => handleRequest(s.id)}>Request</button>}</td></tr>
                    ))}</tbody>
                </table></div>
            ) : <p style={{ color: 'var(--text-secondary)' }}>No payments yet.</p>}
        </>
    );
}
