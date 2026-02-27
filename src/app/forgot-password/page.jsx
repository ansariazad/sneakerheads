'use client';
import { useState } from 'react';
import Link from 'next/link';
import { resetPassword } from '@/lib/auth';

export default function ForgotPasswordPage() {
    const [email, setEmail] = useState('');
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(''); setSuccess(''); setLoading(true);
        try {
            await resetPassword(email);
            setSuccess('Password reset link sent! Check your email.');
        } catch (err) {
            setError(err.message || 'Failed to send reset link.');
        } finally { setLoading(false); }
    };

    return (
        <div className="container">
            <div className="form-container">
                <h2 className="form-title">Forgot Password</h2>
                <p style={{ textAlign: 'center', color: 'var(--text-secondary)', marginBottom: 20 }}>Enter your email and we&apos;ll send you a reset link.</p>
                {error && <div className="alert alert-error">{error}</div>}
                {success && <div className="alert alert-success">{success}</div>}
                <form onSubmit={handleSubmit}>
                    <div className="form-group">
                        <label htmlFor="email">Email</label>
                        <input type="email" id="email" value={email} onChange={e => setEmail(e.target.value)} required />
                    </div>
                    <div className="form-actions">
                        <button type="submit" className="btn" disabled={loading}>{loading ? 'Sending...' : 'Send Reset Link'}</button>
                        <Link href="/login">Back to Login</Link>
                    </div>
                </form>
            </div>
        </div>
    );
}
