'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getAddresses, addAddress, deleteAddress, updateProfile, updatePaymentDetails } from '@/lib/db';
import { updatePassword } from '@/lib/auth';

export default function AccountPage() {
    const { user, profile, loading: authLoading } = useAuth();
    const router = useRouter();
    const [activeSection, setActiveSection] = useState('profile');
    const [addresses, setAddresses] = useState([]);
    const [showAddressForm, setShowAddressForm] = useState(false);
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);

    const [profileForm, setProfileForm] = useState({ full_name: '', phone: '' });
    const [passwordForm, setPasswordForm] = useState({ current: '', new: '', confirm: '' });
    const [bankForm, setBankForm] = useState({ account_holder_name: '', account_number: '', ifsc_code: '', bank_name: '', branch_name: '', upi_id: '' });
    const [addressForm, setAddressForm] = useState({ address_line1: '', address_line2: '', city: '', state: '', postal_code: '', country: 'India', is_default: false });

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login'); return; }
        if (profile) {
            setProfileForm({ full_name: profile.full_name || '', phone: profile.phone || '' });
            setBankForm({ account_holder_name: profile.account_holder_name || '', account_number: profile.account_number || '', ifsc_code: profile.ifsc_code || '', bank_name: profile.bank_name || '', branch_name: profile.branch_name || '', upi_id: profile.upi_id || '' });
        }
        getAddresses(user.id).then(setAddresses).catch(console.error).finally(() => setLoading(false));
    }, [user, profile, authLoading, router]);

    const showMsg = (msg, isErr = false) => { isErr ? setError(msg) : setMessage(msg); setTimeout(() => { setMessage(''); setError(''); }, 3000); };

    const handleProfileUpdate = async () => {
        try { await updateProfile(user.id, profileForm); showMsg('Profile updated!'); } catch { showMsg('Failed to update profile.', true); }
    };
    const handlePasswordChange = async () => {
        if (passwordForm.new !== passwordForm.confirm) { showMsg('Passwords do not match.', true); return; }
        try { await updatePassword(passwordForm.new); setPasswordForm({ current: '', new: '', confirm: '' }); showMsg('Password changed!'); } catch (err) { showMsg(err.message || 'Failed.', true); }
    };
    const handleAddAddress = async () => {
        try { const a = await addAddress(user.id, addressForm); setAddresses([...addresses, a]); setShowAddressForm(false); setAddressForm({ address_line1: '', address_line2: '', city: '', state: '', postal_code: '', country: 'India', is_default: false }); showMsg('Address added!'); } catch { showMsg('Failed to add address.', true); }
    };
    const handleDeleteAddress = async (id) => {
        try { await deleteAddress(id); setAddresses(addresses.filter(a => a.id !== id)); showMsg('Address deleted!'); } catch { showMsg('Failed.', true); }
    };
    const handleBankUpdate = async () => {
        try { await updatePaymentDetails(user.id, bankForm); showMsg('Payment details saved!'); } catch { showMsg('Failed.', true); }
    };

    const scrollTo = (id) => { setActiveSection(id); document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' }); };

    if (loading || authLoading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;

    return (
        <div className="container">
            {message && <div className="alert alert-success">{message}</div>}
            {error && <div className="alert alert-error">{error}</div>}
            <div className="dashboard">
                <div className="dashboard-sidebar">
                    <h3>Account Settings</h3>
                    <ul>
                        {[{ id: 'profile', label: 'Profile' }, { id: 'password', label: 'Change Password' }, { id: 'addresses', label: 'Addresses' }, { id: 'payment', label: 'Payment Details' }].map(item => (
                            <li key={item.id}><a href={`#${item.id}`} className={activeSection === item.id ? 'active' : ''} onClick={(e) => { e.preventDefault(); scrollTo(item.id); }}>{item.label}</a></li>
                        ))}
                        <li><Link href="/orders">My Orders</Link></li>
                        <li><Link href="/wishlist">My Wishlist</Link></li>
                    </ul>
                </div>
                <div className="dashboard-content">
                    <section id="profile" className="account-section">
                        <h2>Profile Information</h2>
                        <form onSubmit={e => { e.preventDefault(); handleProfileUpdate(); }}>
                            <div className="form-group"><label>Username</label><input type="text" value={profile?.username || ''} disabled /><small>Cannot be changed</small></div>
                            <div className="form-group"><label>Email</label><input type="email" value={user?.email || ''} disabled /><small>Cannot be changed</small></div>
                            <div className="form-group"><label>Full Name</label><input type="text" value={profileForm.full_name} onChange={e => setProfileForm({ ...profileForm, full_name: e.target.value })} /></div>
                            <div className="form-group"><label>Phone</label><input type="tel" value={profileForm.phone} onChange={e => setProfileForm({ ...profileForm, phone: e.target.value })} /></div>
                            <button type="submit" className="btn">Update Profile</button>
                        </form>
                    </section>
                    <section id="password" className="account-section">
                        <h2>Change Password</h2>
                        <form onSubmit={e => { e.preventDefault(); handlePasswordChange(); }}>
                            <div className="form-row">
                                <div className="form-group"><label>New Password</label><input type="password" value={passwordForm.new} onChange={e => setPasswordForm({ ...passwordForm, new: e.target.value })} /></div>
                                <div className="form-group"><label>Confirm</label><input type="password" value={passwordForm.confirm} onChange={e => setPasswordForm({ ...passwordForm, confirm: e.target.value })} /></div>
                            </div>
                            <button type="submit" className="btn">Change Password</button>
                        </form>
                    </section>
                    <section id="addresses" className="account-section">
                        <h2>Manage Addresses</h2>
                        {addresses.map(addr => (
                            <div key={addr.id} className="address-card" style={{ marginBottom: 10 }}>
                                {addr.is_default && <div className="default-badge">Default</div>}
                                <div className="address-details"><p>{addr.address_line1}</p>{addr.address_line2 && <p>{addr.address_line2}</p>}<p>{addr.city}, {addr.state} {addr.postal_code}</p></div>
                                <div className="address-actions"><button className="btn btn-danger" style={{ fontSize: 13, padding: '6px 12px' }} onClick={() => handleDeleteAddress(addr.id)}>Delete</button></div>
                            </div>
                        ))}
                        <button className="btn" onClick={() => setShowAddressForm(!showAddressForm)}>{showAddressForm ? 'Cancel' : 'Add New Address'}</button>
                        {showAddressForm && (
                            <div style={{ background: 'var(--bg-light)', borderRadius: 8, padding: 20, marginTop: 20 }}>
                                <form onSubmit={e => { e.preventDefault(); handleAddAddress(); }}>
                                    <div className="form-group"><label>Address Line 1 *</label><input type="text" value={addressForm.address_line1} onChange={e => setAddressForm({ ...addressForm, address_line1: e.target.value })} required /></div>
                                    <div className="form-group"><label>Address Line 2</label><input type="text" value={addressForm.address_line2} onChange={e => setAddressForm({ ...addressForm, address_line2: e.target.value })} /></div>
                                    <div className="form-row">
                                        <div className="form-group"><label>City *</label><input type="text" value={addressForm.city} onChange={e => setAddressForm({ ...addressForm, city: e.target.value })} required /></div>
                                        <div className="form-group"><label>State *</label><input type="text" value={addressForm.state} onChange={e => setAddressForm({ ...addressForm, state: e.target.value })} required /></div>
                                    </div>
                                    <div className="form-row">
                                        <div className="form-group"><label>Postal Code *</label><input type="text" value={addressForm.postal_code} onChange={e => setAddressForm({ ...addressForm, postal_code: e.target.value })} required /></div>
                                        <div className="form-group"><label>Country *</label><input type="text" value={addressForm.country} onChange={e => setAddressForm({ ...addressForm, country: e.target.value })} required /></div>
                                    </div>
                                    <div className="form-group"><label><input type="checkbox" checked={addressForm.is_default} onChange={e => setAddressForm({ ...addressForm, is_default: e.target.checked })} /> Set as default</label></div>
                                    <button type="submit" className="btn">Save Address</button>
                                </form>
                            </div>
                        )}
                    </section>
                    <section id="payment" className="account-section">
                        <h2>Payment Details</h2>
                        <form onSubmit={e => { e.preventDefault(); handleBankUpdate(); }}>
                            <div className="form-group"><label>Account Holder Name</label><input type="text" value={bankForm.account_holder_name} onChange={e => setBankForm({ ...bankForm, account_holder_name: e.target.value })} /></div>
                            <div className="form-row">
                                <div className="form-group"><label>Account Number</label><input type="text" value={bankForm.account_number} onChange={e => setBankForm({ ...bankForm, account_number: e.target.value })} /></div>
                                <div className="form-group"><label>IFSC Code</label><input type="text" value={bankForm.ifsc_code} onChange={e => setBankForm({ ...bankForm, ifsc_code: e.target.value })} /></div>
                            </div>
                            <div className="form-row">
                                <div className="form-group"><label>Bank Name</label><input type="text" value={bankForm.bank_name} onChange={e => setBankForm({ ...bankForm, bank_name: e.target.value })} /></div>
                                <div className="form-group"><label>Branch Name</label><input type="text" value={bankForm.branch_name} onChange={e => setBankForm({ ...bankForm, branch_name: e.target.value })} /></div>
                            </div>
                            <div className="form-group"><label>UPI ID</label><input type="text" value={bankForm.upi_id} onChange={e => setBankForm({ ...bankForm, upi_id: e.target.value })} placeholder="example@upi" /></div>
                            <button type="submit" className="btn">Save Payment Details</button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    );
}
