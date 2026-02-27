'use client';
import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useRouter } from 'next/navigation';
import { signIn } from '@/lib/auth';

export default function LoginPage() {
    const router = useRouter();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);
        try {
            await signIn({ email, password });
            router.push('/');
            router.refresh();
        } catch (err) {
            setError(err.message || 'Login failed. Please check your credentials.');
        } finally {
            setLoading(false);
        }
    };

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
