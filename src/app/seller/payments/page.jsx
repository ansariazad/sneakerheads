'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useAuth } from '@/components/AuthProvider';
import { getSellerSales, requestPayment, formatPrice } from '@/lib/db';

export default function SellerPaymentsPage() {
    const { user } = useAuth();
    const [sales, setSales] = useState([]);
    const [loading, setLoading] = useState(true);
    const [bankDetails, setBankDetails] = useState({ holderName: '', accountNumber: '', ifsc: '', bankName: '', upiId: '' });
    const [bankSaved, setBankSaved] = useState(false);

    useEffect(() => {
        if (!user) return;
        getSellerSales(user.id).then(setSales).catch(console.error).finally(() => setLoading(false));
        // Load saved bank details from localStorage
        const saved = localStorage.getItem('sneakerheads_bank_details');
        if (saved) { setBankDetails(JSON.parse(saved)); setBankSaved(true); }
    }, [user]);

    const totalEarnings = sales.reduce((s, p) => s + Number(p.net_amount || 0), 0);
    const completedEarnings = sales.filter(s => s.status === 'completed').reduce((s, p) => s + Number(p.net_amount || 0), 0);
    const pendingEarnings = totalEarnings - completedEarnings;

    const handleRequest = async (id) => {
        try { await requestPayment(id); setSales(sales.map(s => s.id === id ? { ...s, status: 'requested' } : s)); } catch { }
    };

    const handleSaveBank = (e) => {
        e.preventDefault();
        localStorage.setItem('sneakerheads_bank_details', JSON.stringify(bankDetails));
        setBankSaved(true);
    };

    if (loading) return (
        <div style={{ textAlign: 'center', padding: 60 }}>
            <div className="loading-logo-pulse"><Image src="/images/sneakerheads-logo.svg" alt="Loading" width={50} height={50} priority /></div>
            <p style={{ color: 'var(--text-secondary)', marginTop: 12 }}>Loading payments...</p>
        </div>
    );

    return (
        <>
            <div className="dashboard-header"><h2>Payments & Earnings</h2></div>

            {/* Earnings Summary */}
            <div className="dashboard-stats" style={{ gridTemplateColumns: 'repeat(3, 1fr)' }}>
                <div className="stat-card" style={{ borderLeft: '3px solid #06d6a0' }}>
                    <p style={{ margin: '0 0 4px', fontSize: 13, color: 'var(--text-secondary)' }}>Total Earnings</p>
                    <h3 style={{ margin: 0, color: '#06d6a0' }}>{formatPrice(totalEarnings)}</h3>
                </div>
                <div className="stat-card" style={{ borderLeft: '3px solid #3b82f6' }}>
                    <p style={{ margin: '0 0 4px', fontSize: 13, color: 'var(--text-secondary)' }}>Completed Payouts</p>
                    <h3 style={{ margin: 0, color: '#3b82f6' }}>{formatPrice(completedEarnings)}</h3>
                </div>
                <div className="stat-card" style={{ borderLeft: '3px solid #f59e0b' }}>
                    <p style={{ margin: '0 0 4px', fontSize: 13, color: 'var(--text-secondary)' }}>Pending</p>
                    <h3 style={{ margin: 0, color: '#f59e0b' }}>{formatPrice(pendingEarnings)}</h3>
                </div>
            </div>

            {/* Bank Details Form */}
            <div style={{ background: 'var(--bg-secondary)', borderRadius: 'var(--radius-lg)', border: '1px solid var(--glass-border)', padding: 24, marginBottom: 30 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 16 }}>
                    <i className="fas fa-university" style={{ color: 'var(--primary-color)' }}></i>
                    <h3 style={{ margin: 0 }}>Bank Details</h3>
                    {bankSaved && <span style={{ marginLeft: 'auto', fontSize: 12, color: '#06d6a0' }}><i className="fas fa-check-circle"></i> Saved</span>}
                </div>
                <form onSubmit={handleSaveBank}>
                    <div className="form-row">
                        <div className="form-group">
                            <label>Account Holder Name</label>
                            <input type="text" value={bankDetails.holderName} onChange={e => setBankDetails({ ...bankDetails, holderName: e.target.value })} placeholder="Full name as on bank account" required />
                        </div>
                        <div className="form-group">
                            <label>Bank Name</label>
                            <input type="text" value={bankDetails.bankName} onChange={e => setBankDetails({ ...bankDetails, bankName: e.target.value })} placeholder="e.g. State Bank of India" required />
                        </div>
                    </div>
                    <div className="form-row">
                        <div className="form-group">
                            <label>Account Number</label>
                            <input type="text" value={bankDetails.accountNumber} onChange={e => setBankDetails({ ...bankDetails, accountNumber: e.target.value })} placeholder="Bank account number" required />
                        </div>
                        <div className="form-group">
                            <label>IFSC Code</label>
                            <input type="text" value={bankDetails.ifsc} onChange={e => setBankDetails({ ...bankDetails, ifsc: e.target.value.toUpperCase() })} placeholder="e.g. SBIN0001234" required />
                        </div>
                    </div>
                    <div className="form-group">
                        <label>UPI ID (optional)</label>
                        <input type="text" value={bankDetails.upiId} onChange={e => setBankDetails({ ...bankDetails, upiId: e.target.value })} placeholder="e.g. yourname@upi" />
                    </div>
                    <button type="submit" className="btn btn-gradient" style={{ marginTop: 10 }}>
                        <i className="fas fa-save" style={{ marginRight: 6 }}></i>{bankSaved ? 'Update Details' : 'Save Details'}
                    </button>
                </form>
            </div>

            {/* Payment History */}
            <h3 style={{ marginBottom: 15 }}>Payment History</h3>
            {sales.length > 0 ? (
                <div className="table-container"><table>
                    <thead><tr><th>Sneaker</th><th>Sale Amount</th><th>Platform Fee (10%)</th><th>Net Payout</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>{sales.map(s => {
                        const statusColors = { completed: '#06d6a0', requested: '#f59e0b', processing: '#3b82f6', pending: '#64748b' };
                        return (
                            <tr key={s.id}>
                                <td style={{ fontWeight: 500 }}>{s.order_item?.sneaker?.brand} {s.order_item?.sneaker?.model}</td>
                                <td>{formatPrice(s.amount)}</td>
                                <td style={{ color: 'var(--text-secondary)' }}>{formatPrice(s.platform_fee)}</td>
                                <td style={{ color: '#06d6a0', fontWeight: 'bold' }}>{formatPrice(s.net_amount)}</td>
                                <td>
                                    <span style={{ background: `${statusColors[s.status] || '#64748b'}15`, color: statusColors[s.status] || '#64748b', padding: '3px 10px', borderRadius: 50, fontSize: 12, fontWeight: 600, textTransform: 'capitalize' }}>{s.status}</span>
                                </td>
                                <td>
                                    {s.status === 'pending' && bankSaved && (
                                        <button className="btn btn-gradient" style={{ fontSize: 12, padding: '5px 12px' }} onClick={() => handleRequest(s.id)}>
                                            <i className="fas fa-paper-plane" style={{ marginRight: 4 }}></i>Request Payout
                                        </button>
                                    )}
                                    {s.status === 'pending' && !bankSaved && (
                                        <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>Save bank details first</span>
                                    )}
                                    {s.status === 'completed' && <span style={{ fontSize: 12, color: '#06d6a0' }}><i className="fas fa-check"></i> Paid</span>}
                                    {s.status === 'requested' && <span style={{ fontSize: 12, color: '#f59e0b' }}>Processing...</span>}
                                </td>
                            </tr>
                        );
                    })}</tbody>
                </table></div>
            ) : (
                <div style={{ padding: 30, textAlign: 'center', background: 'var(--bg-secondary)', borderRadius: 'var(--radius)', border: '1px solid var(--glass-border)' }}>
                    <i className="fas fa-wallet" style={{ fontSize: 32, color: 'var(--text-secondary)', marginBottom: 12 }}></i>
                    <p style={{ color: 'var(--text-secondary)' }}>No payments yet. Once your sneakers are sold, payments will appear here.</p>
                    <Link href="/seller/sneakers/add" className="btn btn-gradient" style={{ marginTop: 10 }}>List Your First Sneaker</Link>
                </div>
            )}

            {/* Payment Info */}
            <div style={{ marginTop: 30, padding: 20, background: 'rgba(59,130,246,0.06)', borderRadius: 'var(--radius)', border: '1px solid rgba(59,130,246,0.15)' }}>
                <h4 style={{ marginBottom: 8 }}><i className="fas fa-info-circle" style={{ color: 'var(--primary-color)', marginRight: 6 }}></i>Payment Info</h4>
                <ul style={{ fontSize: 13, color: 'var(--text-secondary)', lineHeight: 2, paddingLeft: 16, margin: 0 }}>
                    <li>Platform fee is <strong>10%</strong> of the sale price</li>
                    <li>Payouts are processed within <strong>3-5 business days</strong> after the buyer confirms delivery</li>
                    <li>Minimum payout amount: <strong>₹500</strong></li>
                    <li>Payments are made to your registered bank account via NEFT/UPI</li>
                </ul>
            </div>
        </>
    );
}
