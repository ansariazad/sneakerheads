import Link from 'next/link';

export default function SellerLayout({ children }) {
    return (
        <div className="container">
            <div className="dashboard">
                <div className="dashboard-sidebar">
                    <h3>Seller Dashboard</h3>
                    <ul>
                        <li><Link href="/seller">Dashboard</Link></li>
                        <li><Link href="/seller/sneakers">My Sneakers</Link></li>
                        <li><Link href="/seller/sneakers/add">Add Sneaker</Link></li>
                        <li><Link href="/seller/sales">My Sales</Link></li>
                        <li><Link href="/seller/payments">Payments</Link></li>
                        <li><Link href="/account">Account Settings</Link></li>
                        <li><Link href="/">Back to Store</Link></li>
                    </ul>
                </div>
                <div className="dashboard-content">
                    {children}
                </div>
            </div>
        </div>
    );
}
