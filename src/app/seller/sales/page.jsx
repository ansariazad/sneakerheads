'use client';
import { useState, useEffect } from 'react';
import { useAuth } from '@/components/AuthProvider';
import { getSellerSales, formatPrice } from '@/lib/db';

export default function SellerSalesPage() {
    const { user } = useAuth();
    const [sales, setSales] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!user) return;
        getSellerSales(user.id).then(setSales).catch(console.error).finally(() => setLoading(false));
    }, [user]);

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>My Sales</h2></div>
            {sales.length > 0 ? (
                <div className="table-container"><table>
                    <thead><tr><th>Sneaker</th><th>Price</th><th>Platform Fee</th><th>Net Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>{sales.map(s => (
                        <tr key={s.id}><td>{s.order_item?.sneaker?.brand} {s.order_item?.sneaker?.model}</td><td>{formatPrice(s.amount)}</td><td>{formatPrice(s.platform_fee)}</td>
                            <td style={{ color: 'var(--accent-color)', fontWeight: 'bold' }}>{formatPrice(s.net_amount)}</td>
                            <td><span className={`order-status status-${s.status === 'completed' ? 'delivered' : 'processing'}`}>{s.status}</span></td>
                            <td>{new Date(s.created_at).toLocaleDateString()}</td></tr>
                    ))}</tbody>
                </table></div>
            ) : <p style={{ color: 'var(--text-secondary)' }}>No sales yet.</p>}
        </>
    );
}
