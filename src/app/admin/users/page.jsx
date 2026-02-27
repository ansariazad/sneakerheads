'use client';
import { useState, useEffect } from 'react';
import { getAllUsers, toggleUserActive } from '@/lib/db';

export default function AdminUsersPage() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => { getAllUsers().then(setUsers).catch(console.error).finally(() => setLoading(false)); }, []);

    const handleToggle = async (userId, isActive) => {
        await toggleUserActive(userId, !isActive);
        setUsers(users.map(u => u.id === userId ? { ...u, is_active: !isActive } : u));
    };

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Manage Users</h2></div>
            <div className="table-container"><table>
                <thead><tr><th>Username</th><th>Email</th><th>Name</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>{users.map(u => (
                    <tr key={u.id}><td>{u.username}</td><td>{u.email || 'N/A'}</td><td>{u.full_name}</td>
                        <td style={{ textTransform: 'capitalize' }}>{u.user_type?.replace('_', '/')}</td>
                        <td><span className={`order-status ${u.is_active ? 'status-approved' : 'status-rejected'}`}>{u.is_active ? 'Active' : 'Inactive'}</span></td>
                        <td><button className={`btn ${u.is_active ? 'btn-danger' : 'btn-success'}`} style={{ fontSize: 13, padding: '5px 10px' }} onClick={() => handleToggle(u.id, u.is_active)}>{u.is_active ? 'Deactivate' : 'Activate'}</button></td></tr>
                ))}</tbody>
            </table></div>
        </>
    );
}
