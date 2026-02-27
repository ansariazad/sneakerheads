'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getSellerStats, getSellerSales, formatPrice } from '@/lib/db';

export default function SellerDashboardPage() {
    const { user, profile, loading: authLoading } = useAuth();
    const router = useRouter();
    const [stats, setStats] = useState({ sneakerCount: 0, salesCount: 0, totalRevenue: 0, totalEarnings: 0 });
    const [sales, setSales] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login'); return; }
        Promise.all([getSellerStats(user.id), getSellerSales(user.id)])
            .then(([s, sl]) => { setStats(s); setSales(sl.slice(0, 5)); })
            .catch(console.error).finally(() => setLoading(false));
    }, [user, authLoading, router]);

    if (loading || authLoading) return <p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Seller Dashboard</h2><Link href="/seller/sneakers/add" className="btn">Add New Sneaker</Link></div>
            <div className="dashboard-stats">
                <div className="stat-card"><h3>{stats.sneakerCount}</h3><p>Listed Sneakers</p></div>
                <div className="stat-card"><h3>{stats.salesCount}</h3><p>Total Sales</p></div>
                <div className="stat-card"><h3>{formatPrice(stats.totalRevenue)}</h3><p>Total Revenue</p></div>
                <div className="stat-card"><h3>{formatPrice(stats.totalEarnings)}</h3><p>Net Earnings</p></div>
            </div>
            <h3 style={{ marginBottom: 15 }}>Recent Sales</h3>
            {sales.length > 0 ? (
                <div className="table-container"><table>
                    <thead><tr><th>Sneaker</th><th>Amount</th><th>Fee</th><th>Net</th><th>Status</th></tr></thead>
                    <tbody>{sales.map(s => (
                        <tr key={s.id}><td>{s.order_item?.sneaker?.brand} {s.order_item?.sneaker?.model}</td><td>{formatPrice(s.amount)}</td><td>{formatPrice(s.platform_fee)}</td>
                            <td style={{ color: 'var(--accent-color)', fontWeight: 'bold' }}>{formatPrice(s.net_amount)}</td>
                            <td><span className={`order-status status-${s.status === 'completed' ? 'delivered' : 'processing'}`}>{s.status}</span></td></tr>
                    ))}</tbody>
                </table></div>
            ) : <p style={{ color: 'var(--text-secondary)' }}>No sales yet.</p>}
        </>
    );
}
