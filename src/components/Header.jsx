'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '@/components/AuthProvider';
import { signOut } from '@/lib/auth';
import { getCartCount, getUnreadNotificationCount } from '@/lib/db';

export default function Header() {
    const { user, profile, loading } = useAuth();
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [cartCount, setCartCount] = useState(0);
    const [notificationCount, setNotificationCount] = useState(0);

    const isLoggedIn = !!user;
    const isSeller = profile?.user_type === 'seller_buyer' || profile?.user_type === 'superadmin';
    const isAdmin = profile?.user_type === 'superadmin';
    const username = profile?.username || user?.email?.split('@')[0] || '';

    useEffect(() => {
        if (user) {
            getCartCount(user.id).then(setCartCount).catch(() => { });
            getUnreadNotificationCount(user.id).then(setNotificationCount).catch(() => { });
        }
    }, [user]);

    const handleSignOut = async () => {
        await signOut();
        setDropdownOpen(false);
        setMobileMenuOpen(false);
        window.location.href = '/';
    };

    return (
        <header className="main-header">
            <div className="container">
                <div className="header-content">
                    <div className="logo">
                        <Link href="/"><h1>Sneakerheads</h1></Link>
                    </div>
                    <div className="search-bar">
                        <form action="/search" method="GET">
                            <input type="text" name="q" placeholder="Search for sneakers..." />
                            <button type="submit"><i className="fas fa-search"></i></button>
                        </form>
                    </div>
                    <nav className={`main-nav${mobileMenuOpen ? ' show' : ''}`}>
                        <ul>
                            <li><Link href="/" onClick={() => setMobileMenuOpen(false)}>Home</Link></li>
                            {!loading && isLoggedIn ? (
                                <>
                                    <li className="notification-icon">
                                        <Link href="/notifications" onClick={() => setMobileMenuOpen(false)}>
                                            <i className="fas fa-bell"></i>
                                            {notificationCount > 0 && <span className="badge">{notificationCount}</span>}
                                        </Link>
                                    </li>
                                    <li className="cart-icon">
                                        <Link href="/cart" onClick={() => setMobileMenuOpen(false)}>
                                            <i className="fas fa-shopping-cart"></i>
                                            {cartCount > 0 && <span className="badge">{cartCount}</span>}
                                        </Link>
                                    </li>
                                    <li className="user-dropdown">
                                        <div className="dropdown-toggle" onClick={() => setDropdownOpen(!dropdownOpen)}>
                                            <i className="fas fa-user"></i>
                                            <span className="username">{username}</span>
                                            <i className="fas fa-chevron-down"></i>
                                        </div>
                                        <div className={`dropdown-menu${dropdownOpen ? ' show' : ''}`}>
                                            <Link href="/account" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                <i className="fas fa-user-circle"></i> My Account
                                            </Link>
                                            <Link href="/orders" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                <i className="fas fa-shopping-bag"></i> My Orders
                                            </Link>
                                            <Link href="/wishlist" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                <i className="fas fa-heart"></i> My Wishlist
                                            </Link>
                                            {isSeller && (
                                                <Link href="/seller" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                    <i className="fas fa-store"></i> Seller Dashboard
                                                </Link>
                                            )}
                                            {isAdmin && (
                                                <Link href="/admin" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                    <i className="fas fa-user-shield"></i> Admin Dashboard
                                                </Link>
                                            )}
                                            <a href="#" onClick={(e) => { e.preventDefault(); handleSignOut(); }}>
                                                <i className="fas fa-sign-out-alt"></i> Logout
                                            </a>
                                        </div>
                                    </li>
                                </>
                            ) : !loading ? (
                                <>
                                    <li><Link href="/login" onClick={() => setMobileMenuOpen(false)}>Login</Link></li>
                                    <li><Link href="/register" onClick={() => setMobileMenuOpen(false)}>Register</Link></li>
                                </>
                            ) : null}
                        </ul>
                    </nav>
                    <button className={`burger-menu${mobileMenuOpen ? ' active' : ''}`} onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </header>
    );
}
