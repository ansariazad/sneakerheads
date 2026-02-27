'use client';
import { useState, useEffect } from 'react';
import { getAllPayments, approvePayment, formatPrice } from '@/lib/db';

export default function AdminPaymentsPage() {
    const [payments, setPayments] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => { getAllPayments().then(setPayments).catch(console.error).finally(() => setLoading(false)); }, []);

    const handleApprove = async (id) => {
        await approvePayment(id);
        setPayments(payments.map(p => p.id === id ? { ...p, status: 'completed' } : p));
    };

    const totalRevenue = payments.reduce((s, p) => s + Number(p.amount), 0);
    const totalFees = payments.reduce((s, p) => s + Number(p.platform_fee), 0);
    const totalPayouts = payments.reduce((s, p) => s + Number(p.net_amount), 0);

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Manage Payments</h2></div>
            <div className="dashboard-stats" style={{ gridTemplateColumns: 'repeat(3, 1fr)' }}>
                <div className="stat-card"><h3>{formatPrice(totalRevenue)}</h3><p>Total Revenue</p></div>
                <div className="stat-card"><h3>{formatPrice(totalFees)}</h3><p>Platform Fees</p></div>
                <div className="stat-card"><h3>{formatPrice(totalPayouts)}</h3><p>Seller Payouts</p></div>
            </div>
            {payments.length > 0 ? (
                <div className="table-container"><table>
                    <thead><tr><th>Seller</th><th>Amount</th><th>Fee</th><th>Net</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>{payments.map(p => (
                        <tr key={p.id}><td>{p.seller?.username}</td><td>{formatPrice(p.amount)}</td><td>{formatPrice(p.platform_fee)}</td>
                            <td>{formatPrice(p.net_amount)}</td>
                            <td><span className={`order-status status-${p.status === 'completed' ? 'delivered' : p.status === 'requested' ? 'pending' : 'processing'}`}>{p.status}</span></td>
                            <td>{p.status !== 'completed' && <button className="btn btn-success" style={{ fontSize: 13, padding: '5px 10px' }} onClick={() => handleApprove(p.id)}>Approve</button>}</td></tr>
                    ))}</tbody>
                </table></div>
            ) : <p style={{ color: 'var(--text-secondary)' }}>No payments yet.</p>}
        </>
    );
}
