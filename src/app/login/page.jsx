'use client';
import { useState, useEffect, Suspense } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useRouter, useSearchParams } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { signIn } from '@/lib/auth';

function LoginContent() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const { user, loading: authLoading } = useAuth();
    const redirectTo = searchParams.get('redirect') || '/';
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    // Auto-redirect if already logged in
    useEffect(() => {
        if (!authLoading && user) {
            window.location.href = redirectTo;
        }
    }, [user, authLoading, redirectTo]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);
        try {
            await signIn({ email, password });
            // Use window.location for a full page reload to sync server/client auth
            const dest = redirectTo === '/' ? '/?loggedin=1' : redirectTo;
            window.location.href = dest;
        } catch (err) {
            setError(err.message || 'Login failed. Please check your credentials.');
        } finally {
            setLoading(false);
        }
    };

    // If auth is loading or user is already logged in, show loading
    if (authLoading || user) {
        return (
            <div className="auth-page">
                <div className="auth-card" style={{ textAlign: 'center', padding: '60px 40px' }}>
                    <div className="btn-spinner" style={{ width: 36, height: 36, margin: '0 auto 16px', borderWidth: 3 }}></div>
                    <p style={{ color: 'var(--text-secondary)' }}>Redirecting...</p>
                </div>
            </div>
        );
    }

    return (
        <div className="auth-page">
            <div className="auth-card">
                <div className="auth-logo">
                    <Image src="/images/sneakerheads-logo.svg" alt="Sneakerheads" width={100} height={100} priority />
                </div>
                <h2 className="auth-title">Welcome Back</h2>
                <p className="auth-subtitle">Sign in to your Sneakerheads account</p>
                {error && <div className="alert alert-error">{error}</div>}
                <form onSubmit={handleSubmit}>
                    <div className="form-group">
                        <label htmlFor="email">Email</label>
                        <input type="email" id="email" value={email} onChange={e => setEmail(e.target.value)} placeholder="you@example.com" required />
                    </div>
                    <div className="form-group">
                        <label htmlFor="password">Password</label>
                        <input type="password" id="password" value={password} onChange={e => setPassword(e.target.value)} placeholder="••••••••" required />
                    </div>
                    <div className="form-actions">
                        <button type="submit" className="btn btn-gradient btn-full" disabled={loading}>
                            {loading ? <><span className="btn-spinner"></span> Signing in...</> : 'Sign In'}
                        </button>
                    </div>
                    <div className="auth-links">
                        <Link href="/forgot-password">Forgot Password?</Link>
                    </div>
                </form>
                <div className="auth-footer">
                    <p>Don&apos;t have an account? <Link href="/register">Create Account</Link></p>
                </div>
            </div>
        </div>
    );
}

export default function LoginPage() {
    return (
        <Suspense fallback={<div className="auth-page"><p style={{ color: 'var(--text-secondary)' }}>Loading...</p></div>}>
            <LoginContent />
        </Suspense>
    );
}
