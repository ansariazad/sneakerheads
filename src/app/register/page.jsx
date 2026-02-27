'use client';
import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useRouter } from 'next/navigation';
import { signUp } from '@/lib/auth';

export default function RegisterPage() {
    const router = useRouter();
    const [form, setForm] = useState({ username: '', email: '', fullName: '', password: '', confirmPassword: '', userType: 'buyer' });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');
        if (form.password !== form.confirmPassword) { setError('Passwords do not match.'); return; }
        if (form.password.length < 6) { setError('Password must be at least 6 characters.'); return; }

        setLoading(true);
        try {
            await signUp({ email: form.email, password: form.password, username: form.username, fullName: form.fullName, userType: form.userType });
            setSuccess('Registration successful! Please check your email to verify your account.');
        } catch (err) {
            setError(err.message || 'Registration failed.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="auth-page">
            <div className="auth-card">
                <div className="auth-logo">
                    <Image src="/images/sneakerheads-logo.svg" alt="Sneakerheads" width={80} height={80} priority />
                </div>
                <h2 className="auth-title">Create Account</h2>
                <p className="auth-subtitle">Join the Sneakerheads community</p>
                {error && <div className="alert alert-error">{error}</div>}
                {success && <div className="alert alert-success">{success}</div>}
                <form onSubmit={handleSubmit}>
                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="username">Username</label>
                            <input type="text" id="username" name="username" value={form.username} onChange={handleChange} placeholder="sneakerfan" required />
                        </div>
                        <div className="form-group">
                            <label htmlFor="fullName">Full Name</label>
                            <input type="text" id="fullName" name="fullName" value={form.fullName} onChange={handleChange} placeholder="John Doe" required />
                        </div>
                    </div>
                    <div className="form-group">
                        <label htmlFor="email">Email</label>
                        <input type="email" id="email" name="email" value={form.email} onChange={handleChange} placeholder="you@example.com" required />
                    </div>
                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="password">Password</label>
                            <input type="password" id="password" name="password" value={form.password} onChange={handleChange} placeholder="••••••••" required />
                        </div>
                        <div className="form-group">
                            <label htmlFor="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" value={form.confirmPassword} onChange={handleChange} placeholder="••••••••" required />
                        </div>
                    </div>
                    <div className="form-group">
                        <label>Account Type</label>
                        <div className="account-type-cards">
                            <label className={`type-card ${form.userType === 'buyer' ? 'active' : ''}`}>
                                <input type="radio" name="userType" value="buyer" checked={form.userType === 'buyer'} onChange={handleChange} />
                                <i className="fas fa-shopping-bag"></i>
                                <span className="type-label">Buyer</span>
                                <span className="type-desc">Buy sneakers</span>
                            </label>
                            <label className={`type-card ${form.userType === 'seller_buyer' ? 'active' : ''}`}>
                                <input type="radio" name="userType" value="seller_buyer" checked={form.userType === 'seller_buyer'} onChange={handleChange} />
                                <i className="fas fa-store"></i>
                                <span className="type-label">Seller / Buyer</span>
                                <span className="type-desc">Buy & sell sneakers</span>
                            </label>
                        </div>
                    </div>
                    <div className="form-actions">
                        <button type="submit" className="btn btn-gradient btn-full" disabled={loading}>
                            {loading ? <><span className="btn-spinner"></span> Creating Account...</> : 'Create Account'}
                        </button>
                    </div>
                </form>
                <div className="auth-footer">
                    <p>Already have an account? <Link href="/login">Sign In</Link></p>
                </div>
            </div>
        </div>
    );
}
