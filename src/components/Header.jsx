'use client';
import { useState, useEffect, useCallback, useRef } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { signOut } from '@/lib/auth';
import { getCartCount, getUnreadNotificationCount } from '@/lib/db';

const popularSearches = [
    { label: 'Nike Air Jordan 1', brand: 'Nike' },
    { label: 'Adidas Yeezy 350', brand: 'Adidas' },
    { label: 'Nike Dunk Low', brand: 'Nike' },
    { label: 'Jordan 4 Retro', brand: 'Jordan' },
    { label: 'Nike Air Max 90', brand: 'Nike' },
    { label: 'New Balance 550', brand: 'New Balance' },
    { label: 'Puma RS-X', brand: 'Puma' },
    { label: 'Nike Air Force 1', brand: 'Nike' },
    { label: 'Adidas Ultraboost', brand: 'Adidas' },
    { label: 'Reebok Classic', brand: 'Reebok' },
    { label: 'Converse Chuck Taylor', brand: 'Converse' },
    { label: 'Vans Old Skool', brand: 'Vans' },
];

const brandList = ['Nike', 'Adidas', 'Jordan', 'Puma', 'New Balance', 'Reebok', 'Converse', 'Vans', 'Under Armour', 'Asics'];

export default function Header() {
    const { user, profile, loading } = useAuth();
    const router = useRouter();
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [cartCount, setCartCount] = useState(0);
    const [notificationCount, setNotificationCount] = useState(0);
    const [scrolled, setScrolled] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [suggestions, setSuggestions] = useState([]);
    const [showSuggestions, setShowSuggestions] = useState(false);
    const searchRef = useRef(null);

    const isLoggedIn = !!user;
    const isSeller = profile?.user_type === 'seller_buyer' || profile?.user_type === 'superadmin';
    const isAdmin = profile?.user_type === 'superadmin';
    const username = profile?.username || user?.email?.split('@')[0] || '';

    const refreshCounts = useCallback(async () => {
        if (!user) return;
        try {
            const [cc, nc] = await Promise.all([
                getCartCount(user.id).catch(() => 0),
                getUnreadNotificationCount(user.id).catch(() => 0),
            ]);
            setCartCount(cc);
            setNotificationCount(nc);
        } catch { }
    }, [user]);

    useEffect(() => { refreshCounts(); const i = setInterval(refreshCounts, 30000); return () => clearInterval(i); }, [refreshCounts]);
    useEffect(() => { const f = () => refreshCounts(); window.addEventListener('focus', f); return () => window.removeEventListener('focus', f); }, [refreshCounts]);
    useEffect(() => { const h = () => setScrolled(window.scrollY > 10); window.addEventListener('scroll', h); return () => window.removeEventListener('scroll', h); }, []);

    // Close dropdown on outside click
    useEffect(() => {
        if (!dropdownOpen) return;
        const h = (e) => { if (!e.target.closest('.user-dropdown')) setDropdownOpen(false); };
        document.addEventListener('click', h);
        return () => document.removeEventListener('click', h);
    }, [dropdownOpen]);

    // Close suggestions on outside click
    useEffect(() => {
        const h = (e) => { if (searchRef.current && !searchRef.current.contains(e.target)) setShowSuggestions(false); };
        document.addEventListener('click', h);
        return () => document.removeEventListener('click', h);
    }, []);

    // Filter suggestions based on query
    useEffect(() => {
        if (!searchQuery.trim()) {
            setSuggestions([]);
            return;
        }
        const q = searchQuery.toLowerCase();
        const results = [];

        // Match brands
        brandList.forEach(b => {
            if (b.toLowerCase().includes(q)) results.push({ type: 'brand', label: b, icon: 'fa-tag' });
        });

        // Match models
        popularSearches.forEach(s => {
            if (s.label.toLowerCase().includes(q) && !results.find(r => r.label === s.label)) {
                results.push({ type: 'model', label: s.label, icon: 'fa-shoe-prints', brand: s.brand });
            }
        });

        setSuggestions(results.slice(0, 6));
    }, [searchQuery]);

    const handleSignOut = async () => {
        await signOut();
        setDropdownOpen(false);
        setMobileMenuOpen(false);
        window.location.href = '/';
    };

    const handleSearch = (e) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            router.push(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
            setShowSuggestions(false);
            setMobileMenuOpen(false);
        }
    };

    const handleSuggestionClick = (suggestion) => {
        setShowSuggestions(false);
        if (suggestion.type === 'brand') {
            router.push(`/search?brand=${encodeURIComponent(suggestion.label)}`);
        } else {
            router.push(`/search?q=${encodeURIComponent(suggestion.label)}`);
        }
        setSearchQuery('');
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
                    <div className="search-bar" ref={searchRef}>
                        <form onSubmit={handleSearch}>
                            <i className="fas fa-search search-icon"></i>
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => { setSearchQuery(e.target.value); setShowSuggestions(true); }}
                                onFocus={() => searchQuery.trim() && setShowSuggestions(true)}
                                placeholder="Search sneakers, brands..."
                                autoComplete="off"
                            />
                        </form>
                        {showSuggestions && suggestions.length > 0 && (
                            <div className="search-suggestions">
                                {suggestions.map((s, i) => (
                                    <div key={i} className="suggestion-item" onClick={() => handleSuggestionClick(s)}>
                                        <i className={`fas ${s.icon}`}></i>
                                        <div>
                                            <span className="suggestion-label">{s.label}</span>
                                            {s.brand && s.type === 'model' && <span className="suggestion-brand">{s.brand}</span>}
                                        </div>
                                        <span className="suggestion-type">{s.type === 'brand' ? 'Brand' : 'Model'}</span>
                                    </div>
                                ))}
                                <div className="suggestion-footer" onClick={handleSearch}>
                                    <i className="fas fa-search"></i> Search for &quot;{searchQuery}&quot;
                                </div>
                            </div>
                        )}
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
                                            <Link href="/account" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}><i className="fas fa-user-circle"></i> My Account</Link>
                                            <Link href="/orders" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}><i className="fas fa-shopping-bag"></i> My Orders</Link>
                                            <Link href="/wishlist" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}><i className="fas fa-heart"></i> Wishlist</Link>
                                            {isSeller && <Link href="/seller" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}><i className="fas fa-store"></i> Seller Dashboard</Link>}
                                            {isAdmin && <Link href="/admin" onClick={() => { setDropdownOpen(false); setMobileMenuOpen(false); }}><i className="fas fa-user-shield"></i> Admin</Link>}
                                            <div className="dropdown-divider"></div>
                                            <a href="#" onClick={(e) => { e.preventDefault(); handleSignOut(); }} className="dropdown-logout"><i className="fas fa-sign-out-alt"></i> Logout</a>
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
