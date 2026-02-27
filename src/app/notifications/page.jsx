'use client';
import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getNotifications, markNotificationRead } from '@/lib/db';

export default function NotificationsPage() {
    const { user, loading: authLoading } = useAuth();
    const router = useRouter();
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login'); return; }
        getNotifications(user.id).then(setNotifications).catch(console.error).finally(() => setLoading(false));
    }, [user, authLoading, router]);

    const handleMarkRead = async (id) => {
        await markNotificationRead(id);
        setNotifications(notifications.map(n => n.id === id ? { ...n, is_read: true } : n));
    };

    if (loading || authLoading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;

    return (
        <div className="container">
            <h1 className="page-title">Notifications</h1>
            {notifications.length > 0 ? (
                <div className="notifications-list">
                    {notifications.map(n => (
                        <div key={n.id} className={`notification-item${!n.is_read ? ' unread' : ''}`} onClick={() => !n.is_read && handleMarkRead(n.id)} style={{ cursor: !n.is_read ? 'pointer' : 'default' }}>
                            <div className="notification-text">
                                <p>{n.message}</p>
                                <span className="notification-time">{new Date(n.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                            </div>
                            {!n.is_read && <span style={{ width: 10, height: 10, borderRadius: '50%', backgroundColor: 'var(--primary-color)', flexShrink: 0 }}></span>}
                        </div>
                    ))}
                </div>
            ) : (
                <div className="empty-cart">
                    <div className="empty-cart-icon"><i className="fas fa-bell"></i></div>
                    <h2>No notifications</h2>
                    <p>You&apos;re all caught up!</p>
                </div>
            )}
        </div>
    );
}
