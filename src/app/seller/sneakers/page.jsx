'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '@/components/AuthProvider';
import { getSellerSneakers, formatPrice } from '@/lib/db';

export default function SellerSneakersPage() {
    const { user } = useAuth();
    const [sneakers, setSneakers] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!user) return;
        getSellerSneakers(user.id).then(setSneakers).catch(console.error).finally(() => setLoading(false));
    }, [user]);

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>My Sneakers</h2><Link href="/seller/sneakers/add" className="btn">Add New Sneaker</Link></div>
            {sneakers.length > 0 ? (
                <div className="table-container"><table>
                    <thead><tr><th>Sneaker</th><th>Size</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>{sneakers.map(s => (
                        <tr key={s.id}><td>{s.brand} {s.model}</td><td>{s.size} UK</td><td>{formatPrice(s.price)}</td>
                            <td><span className={`order-status status-${s.status}`}>{s.status}</span></td>
                            <td>
                                <Link href={`/seller/sneakers/edit/${s.id}`} className="btn btn-secondary" style={{ fontSize: 13, padding: '5px 10px', marginRight: 5 }}>Edit</Link>
                                <Link href={`/sneaker/${s.id}`} className="btn" style={{ fontSize: 13, padding: '5px 10px' }}>View</Link>
                            </td></tr>
                    ))}</tbody>
                </table></div>
            ) : <p style={{ color: 'var(--text-secondary)' }}>No sneakers listed yet. <Link href="/seller/sneakers/add">Add your first one!</Link></p>}
        </>
    );
}
