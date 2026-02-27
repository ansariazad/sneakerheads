'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { getAllSneakers, updateSneakerStatus, formatPrice } from '@/lib/db';

export default function AdminSneakersPage() {
    const [sneakers, setSneakers] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => { getAllSneakers().then(setSneakers).catch(console.error).finally(() => setLoading(false)); }, []);

    const handleStatus = async (id, status) => {
        await updateSneakerStatus(id, status);
        setSneakers(sneakers.map(s => s.id === id ? { ...s, status } : s));
    };

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Manage Sneakers</h2></div>
            <div className="table-container"><table>
                <thead><tr><th>Sneaker</th><th>Size</th><th>Price</th><th>Seller</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>{sneakers.map(s => (
                    <tr key={s.id}><td>{s.brand} {s.model}</td><td>{s.size} UK</td><td>{formatPrice(s.price)}</td><td>{s.seller?.username}</td>
                        <td><span className={`order-status status-${s.status}`}>{s.status}</span></td>
                        <td>
                            {s.status === 'pending' && <>
                                <button className="btn btn-success" style={{ fontSize: 13, padding: '5px 10px', marginRight: 5 }} onClick={() => handleStatus(s.id, 'approved')}>Approve</button>
                                <button className="btn btn-danger" style={{ fontSize: 13, padding: '5px 10px' }} onClick={() => handleStatus(s.id, 'rejected')}>Reject</button>
                            </>}
                            {s.status !== 'pending' && <Link href={`/sneaker/${s.id}`} className="btn" style={{ fontSize: 13, padding: '5px 10px' }}>View</Link>}
                        </td></tr>
                ))}</tbody>
            </table></div>
        </>
    );
}
