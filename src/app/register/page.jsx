'use client';
import { useState } from 'react';
import Link from 'next/link';
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
        <div className="container">
            <div className="form-container">
                <h2 className="form-title">Create an Account</h2>
                {error && <div className="alert alert-error">{error}</div>}
                {success && <div className="alert alert-success">{success}</div>}
                <form onSubmit={handleSubmit}>
                    <div className="form-group">
                        <label htmlFor="username">Username</label>
                        <input type="text" id="username" name="username" value={form.username} onChange={handleChange} required />
                    </div>
                    <div className="form-group">
                        <label htmlFor="email">Email</label>
                        <input type="email" id="email" name="email" value={form.email} onChange={handleChange} required />
                    </div>
                    <div className="form-group">
                        <label htmlFor="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" value={form.fullName} onChange={handleChange} required />
                    </div>
                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="password">Password</label>
                            <input type="password" id="password" name="password" value={form.password} onChange={handleChange} required />
                        </div>
                        <div className="form-group">
                            <label htmlFor="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" value={form.confirmPassword} onChange={handleChange} required />
                        </div>
                    </div>
                    <div className="form-group">
                        <label>Account Type</label>
                        <div className="radio-group">
                            <label><input type="radio" name="userType" value="buyer" checked={form.userType === 'buyer'} onChange={handleChange} /> Buyer (I want to buy sneakers)</label>
                            <label><input type="radio" name="userType" value="seller_buyer" checked={form.userType === 'seller_buyer'} onChange={handleChange} /> Seller/Buyer (I want to buy and sell sneakers)</label>
                        </div>
                    </div>
                    <div className="form-actions">
                        <button type="submit" className="btn" disabled={loading}>{loading ? 'Creating Account...' : 'Register'}</button>
                    </div>
                </form>
                <div className="form-footer">
                    <p>Already have an account? <Link href="/login">Login</Link></p>
                </div>
            </div>
        </div>
    );
}
