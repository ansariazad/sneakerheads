import Link from 'next/link';

export default function AdminLayout({ children }) {
    return (
        <div className="container">
            <div className="dashboard">
                <div className="dashboard-sidebar">
                    <h3>Admin Dashboard</h3>
                    <ul>
                        <li><Link href="/admin">Dashboard</Link></li>
                        <li><Link href="/admin/users">Manage Users</Link></li>
                        <li><Link href="/admin/sneakers">Manage Sneakers</Link></li>
                        <li><Link href="/admin/orders">Manage Orders</Link></li>
                        <li><Link href="/admin/payments">Manage Payments</Link></li>
                        <li><Link href="/">Back to Store</Link></li>
                    </ul>
                </div>
                <div className="dashboard-content">{children}</div>
            </div>
        </div>
    );
}
