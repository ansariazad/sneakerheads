'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useAuth } from '@/components/AuthProvider';
import { signOut } from '@/lib/auth';
import { getCartCount, getUnreadNotificationCount } from '@/lib/db';

export default function Header() {
    const { user, profile, loading } = useAuth();
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [cartCount, setCartCount] = useState(0);
    const [notificationCount, setNotificationCount] = useState(0);
    const [scrolled, setScrolled] = useState(false);

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

    useEffect(() => {
        const handleScroll = () => setScrolled(window.scrollY > 10);
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const handleSignOut = async () => {
        await signOut();
        setDropdownOpen(false);
        setMobileMenuOpen(false);
        window.location.href = '/';
    };

    return (
        <header className={`main-header${scrolled ? ' scrolled' : ''}`}>
            <div className="header-accent"></div>
            <div className="container">
                <div className="header-content">
                    <div className="logo">
                        <Link href="/">
                            <Image src="/images/sneakerheads-logo.svg" alt="Sneakerheads" width={36} height={36} priority />
                            <span className="logo-text">Sneaker<span className="logo-highlight">heads</span></span>
                        </Link>
                    </div>
                    <div className="search-bar">
                        <form action="/search" method="GET">
                            <i className="fas fa-search search-icon"></i>
                            <input type="text" name="q" placeholder="Search sneakers, brands..." />
                        </form>
                    </div>
                    <nav className={`main-nav${mobileMenuOpen ? ' show' : ''}`}>
                        <ul>
                            <li><Link href="/" onClick={() => setMobileMenuOpen(false)}>Home</Link></li>
                            <li><Link href="/search" onClick={() => setMobileMenuOpen(false)}>Shop</Link></li>
                            <li><Link href="/about" onClick={() => setMobileMenuOpen(false)}>About</Link></li>
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
                                            <i className="fas fa-shopping-bag"></i>
                                            {cartCount > 0 && <span className="badge">{cartCount}</span>}
                                        </Link>
                                    </li>
                                    <li className="user-dropdown">
                                        <div className="dropdown-toggle" onClick={() => setDropdownOpen(!dropdownOpen)}>
                                            <div className="user-avatar"><i className="fas fa-user"></i></div>
                                            <span className="username">{username}</span>
                                            <i className={`fas fa-chevron-down chevron ${dropdownOpen ? 'open' : ''}`}></i>
                                        </div>
                                        <div className={`dropdown-menu${dropdownOpen ? ' show' : ''}`}>
                                            <div className="dropdown-header">
                                                <p className="dropdown-user">{profile?.full_name || username}</p>
                                                <p className="dropdown-email">{user?.email}</p>
                                            </div>
                                            <div className="dropdown-divider"></div>
                                            <Link href="/account" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                <i className="fas fa-user-circle"></i> My Account
                                            </Link>
                                            <Link href="/orders" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                <i className="fas fa-shopping-bag"></i> My Orders
                                            </Link>
                                            <Link href="/wishlist" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                <i className="fas fa-heart"></i> Wishlist
                                            </Link>
                                            {isSeller && (
                                                <Link href="/seller" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                    <i className="fas fa-store"></i> Seller Dashboard
                                                </Link>
                                            )}
                                            {isAdmin && (
                                                <Link href="/admin" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}>
                                                    <i className="fas fa-user-shield"></i> Admin
                                                </Link>
                                            )}
                                            <div className="dropdown-divider"></div>
                                            <a href="#" onClick={(e) => { e.preventDefault(); handleSignOut(); }} className="dropdown-logout">
                                                <i className="fas fa-sign-out-alt"></i> Logout
                                            </a>
                                        </div>
                                    </li>
                                </>
                            ) : !loading ? (
                                <>
                                    <li><Link href="/login" className="nav-link-login" onClick={() => setMobileMenuOpen(false)}>Login</Link></li>
                                    <li><Link href="/register" className="btn btn-gradient btn-sm" onClick={() => setMobileMenuOpen(false)}>Sign Up</Link></li>
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
