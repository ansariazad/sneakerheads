'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import Image from 'next/image';
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
        if (!user) { router.push('/login?redirect=/seller'); return; }
        Promise.all([getSellerStats(user.id), getSellerSales(user.id)])
            .then(([s, sl]) => { setStats(s); setSales(sl.slice(0, 5)); })
            .catch(console.error).finally(() => setLoading(false));
    }, [user, authLoading, router]);

    if (loading || authLoading) return (
        <div className="container" style={{ textAlign: 'center', padding: 80 }}>
            <div className="loading-logo-pulse"><Image src="/images/sneakerheads-logo.svg" alt="Loading" width={50} height={50} priority /></div>
            <p style={{ color: 'var(--text-secondary)', marginTop: 12 }}>Loading dashboard...</p>
        </div>
    );

    return (
        <>
            <div className="dashboard-header">
                <h2>Seller Dashboard</h2>
                <div style={{ display: 'flex', gap: 10 }}>
                    <Link href="/seller/scan" className="btn btn-secondary"><i className="fas fa-camera" style={{ marginRight: 6 }}></i>AI Scan</Link>
                    <Link href="/seller/sneakers/add" className="btn btn-gradient"><i className="fas fa-plus" style={{ marginRight: 6 }}></i>Add Sneaker</Link>
                </div>
            </div>

            {/* Stats */}
            <div className="dashboard-stats">
                <div className="stat-card">
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 40, height: 40, borderRadius: 10, background: 'rgba(59,130,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <i className="fas fa-shoe-prints" style={{ color: 'var(--primary-color)' }}></i>
                        </div>
                        <div><h3 style={{ margin: 0 }}>{stats.sneakerCount}</h3><p style={{ margin: 0 }}>Listed Sneakers</p></div>
                    </div>
                </div>
                <div className="stat-card">
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 40, height: 40, borderRadius: 10, background: 'rgba(6,214,160,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <i className="fas fa-shopping-bag" style={{ color: '#06d6a0' }}></i>
                        </div>
                        <div><h3 style={{ margin: 0 }}>{stats.salesCount}</h3><p style={{ margin: 0 }}>Total Sales</p></div>
                    </div>
                </div>
                <div className="stat-card">
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 40, height: 40, borderRadius: 10, background: 'rgba(139,92,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <i className="fas fa-rupee-sign" style={{ color: '#8b5cf6' }}></i>
                        </div>
                        <div><h3 style={{ margin: 0 }}>{formatPrice(stats.totalRevenue)}</h3><p style={{ margin: 0 }}>Total Revenue</p></div>
                    </div>
                </div>
                <div className="stat-card">
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 40, height: 40, borderRadius: 10, background: 'rgba(245,158,11,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <i className="fas fa-wallet" style={{ color: '#f59e0b' }}></i>
                        </div>
                        <div><h3 style={{ margin: 0 }}>{formatPrice(stats.totalEarnings)}</h3><p style={{ margin: 0 }}>Net Earnings</p></div>
                    </div>
                </div>
            </div>

            {/* Quick Actions */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: 16, marginBottom: 30 }}>
                {[
                    { href: '/seller/sneakers', icon: 'fa-list', label: 'My Listings', desc: 'View & manage sneakers', color: '#3b82f6' },
                    { href: '/seller/sales', icon: 'fa-receipt', label: 'My Sales', desc: 'Track orders & delivery', color: '#06d6a0' },
                    { href: '/seller/payments', icon: 'fa-credit-card', label: 'Payments', desc: 'Earnings & payouts', color: '#8b5cf6' },
                    { href: '/seller/scan', icon: 'fa-camera', label: 'AI Scanner', desc: 'Scan & price sneakers', color: '#f59e0b' },
                ].map((item, i) => (
                    <Link key={i} href={item.href} style={{ background: 'var(--bg-secondary)', border: '1px solid var(--glass-border)', borderRadius: 'var(--radius)', padding: 20, textDecoration: 'none', color: 'inherit', transition: 'var(--transition)' }}>
                        <div style={{ width: 36, height: 36, borderRadius: 8, background: `${item.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 10 }}>
                            <i className={`fas ${item.icon}`} style={{ color: item.color }}></i>
                        </div>
                        <h4 style={{ margin: '0 0 4px' }}>{item.label}</h4>
                        <p style={{ margin: 0, fontSize: 13, color: 'var(--text-secondary)' }}>{item.desc}</p>
                    </Link>
                ))}
            </div>

            {/* How Selling Works */}
            <div style={{ background: 'var(--bg-secondary)', borderRadius: 'var(--radius-lg)', border: '1px solid var(--glass-border)', padding: 24, marginBottom: 30 }}>
                <h3 style={{ marginBottom: 16 }}>How Selling Works</h3>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: 16, textAlign: 'center' }}>
                    {[
                        { step: '1', icon: 'fa-camera', title: 'Upload Photos', desc: '4 angles + video + bill' },
                        { step: '2', icon: 'fa-check-circle', title: 'We Verify', desc: 'Admin approves listing' },
                        { step: '3', icon: 'fa-shopping-cart', title: 'Buyer Orders', desc: 'Someone buys your sneaker' },
                        { step: '4', icon: 'fa-truck', title: 'We Pick Up', desc: 'Courier picks from you' },
                        { step: '5', icon: 'fa-rupee-sign', title: 'Get Paid', desc: 'Money in your bank' },
                    ].map((s, i) => (
                        <div key={i}>
                            <div style={{ width: 40, height: 40, borderRadius: '50%', background: 'rgba(59,130,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 8px', fontSize: 16, fontWeight: 700, color: 'var(--primary-color)' }}>{s.step}</div>
                            <h4 style={{ margin: '0 0 4px', fontSize: 14 }}>{s.title}</h4>
                            <p style={{ margin: 0, fontSize: 12, color: 'var(--text-secondary)' }}>{s.desc}</p>
                        </div>
                    ))}
                </div>
            </div>

            {/* Recent Sales */}
            <h3 style={{ marginBottom: 15 }}>Recent Sales</h3>
            {sales.length > 0 ? (
                <div className="table-container"><table>
                    <thead><tr><th>Sneaker</th><th>Amount</th><th>Platform Fee</th><th>Net</th><th>Status</th></tr></thead>
                    <tbody>{sales.map(s => (
                        <tr key={s.id}>
                            <td>{s.order_item?.sneaker?.brand} {s.order_item?.sneaker?.model}</td>
                            <td>{formatPrice(s.amount)}</td>
                            <td>{formatPrice(s.platform_fee)}</td>
                            <td style={{ color: 'var(--accent-color)', fontWeight: 'bold' }}>{formatPrice(s.net_amount)}</td>
                            <td><span className={`order-status status-${s.status === 'completed' ? 'delivered' : 'processing'}`}>{s.status}</span></td>
                        </tr>
                    ))}</tbody>
                </table></div>
            ) : <p style={{ color: 'var(--text-secondary)', padding: 20, textAlign: 'center', background: 'var(--bg-secondary)', borderRadius: 'var(--radius)' }}>No sales yet. List your first sneaker to get started!</p>}
        </>
    );
}
