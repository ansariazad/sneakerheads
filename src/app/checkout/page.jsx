'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getCart, getAddresses, addAddress, createOrder, formatPrice, COD_FEE } from '@/lib/db';

export default function CheckoutPage() {
    const { user, loading: authLoading } = useAuth();
    const router = useRouter();
    const [cartItems, setCartItems] = useState([]);
    const [addresses, setAddresses] = useState([]);
    const [paymentMethod, setPaymentMethod] = useState('upi');
    const [selectedAddress, setSelectedAddress] = useState('');
    const [loading, setLoading] = useState(true);
    const [placing, setPlacing] = useState(false);
    const [error, setError] = useState('');
    const [showAddressForm, setShowAddressForm] = useState(false);
    const [addressForm, setAddressForm] = useState({
        address_line1: '', address_line2: '', city: '', state: '', postal_code: '', country: 'India', is_default: true
    });
    const [savingAddress, setSavingAddress] = useState(false);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login'); return; }
        Promise.all([getCart(user.id), getAddresses(user.id)])
            .then(([cart, addrs]) => {
                setCartItems(cart);
                setAddresses(addrs);
                if (addrs.length > 0) setSelectedAddress(addrs[0].id);
                else setShowAddressForm(true);
            }).catch(console.error).finally(() => setLoading(false));
    }, [user, authLoading, router]);

    const total = cartItems.reduce((sum, item) => sum + Number(item.sneaker?.price || 0), 0);
    const finalTotal = paymentMethod === 'cod' ? total + COD_FEE : total;

    const handleAddAddress = async (e) => {
        e.preventDefault();
        setSavingAddress(true);
        try {
            const newAddr = await addAddress(user.id, addressForm);
            setAddresses([...addresses, newAddr]);
            setSelectedAddress(newAddr.id);
            setShowAddressForm(false);
            setAddressForm({ address_line1: '', address_line2: '', city: '', state: '', postal_code: '', country: 'India', is_default: true });
        } catch (err) {
            setError('Failed to save address. Please try again.');
        } finally {
            setSavingAddress(false);
        }
    };

    const handlePlaceOrder = async () => {
        if (!selectedAddress) { setError('Please add a shipping address first.'); return; }
        if (cartItems.length === 0) { setError('Your cart is empty.'); return; }
        setPlacing(true); setError('');
        try {
            const order = await createOrder(user.id, selectedAddress, paymentMethod, cartItems);
            if (paymentMethod === 'upi') {
                router.push(`/payment?orderId=${order.id}`);
            } else {
                router.push(`/order-confirmation?orderId=${order.id}`);
            }
        } catch (err) {
            setError(err.message || 'Failed to place order. Please try again.');
            setPlacing(false);
        }
    };

    if (loading || authLoading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;

    if (cartItems.length === 0) {
        return (
            <div className="container">
                <div className="empty-cart">
                    <div className="empty-cart-icon"><i className="fas fa-shopping-cart"></i></div>
                    <h2>Your cart is empty</h2>
                    <p>Add some sneakers to your cart before checking out.</p>
                    <Link href="/search" className="btn">Browse Sneakers</Link>
                </div>
            </div>
        );
    }

    return (
        <div className="container">
            <h1 className="page-title">Checkout</h1>
            {error && <div className="alert alert-error">{error}</div>}
            <div className="checkout-container">
                <div className="checkout-form">
                    <section className="checkout-section">
                        <h2>Shipping Address</h2>
                        {addresses.length > 0 && (
                            <div className="address-selection">
                                {addresses.map(addr => (
                                    <div key={addr.id} className="address-option">
                                        <label>
                                            <input type="radio" name="address_id" value={addr.id} checked={selectedAddress === addr.id} onChange={() => setSelectedAddress(addr.id)} />
                                            <div className="address-card-select">
                                                {addr.is_default && <div className="default-badge">Default</div>}
                                                <div className="address-details">
                                                    <p>{addr.address_line1}</p>
                                                    {addr.address_line2 && <p>{addr.address_line2}</p>}
                                                    <p>{addr.city}, {addr.state} {addr.postal_code}</p>
                                                    <p>{addr.country}</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                ))}
                            </div>
                        )}
                        {!showAddressForm && (
                            <button className="btn btn-secondary" style={{ marginTop: 15 }} onClick={() => setShowAddressForm(true)}>
                                <i className="fas fa-plus"></i> Add New Address
                            </button>
                        )}
                        {showAddressForm && (
                            <div style={{ background: 'var(--bg-light)', borderRadius: 8, padding: 20, marginTop: 15 }}>
                                <h3 style={{ marginBottom: 15 }}>Add Shipping Address</h3>
                                <form onSubmit={handleAddAddress}>
                                    <div className="form-group">
                                        <label>Address Line 1 *</label>
                                        <input type="text" value={addressForm.address_line1} onChange={e => setAddressForm({ ...addressForm, address_line1: e.target.value })} placeholder="House/Flat No, Street" required />
                                    </div>
                                    <div className="form-group">
                                        <label>Address Line 2</label>
                                        <input type="text" value={addressForm.address_line2} onChange={e => setAddressForm({ ...addressForm, address_line2: e.target.value })} placeholder="Landmark, Area (optional)" />
                                    </div>
                                    <div className="form-row">
                                        <div className="form-group">
                                            <label>City *</label>
                                            <input type="text" value={addressForm.city} onChange={e => setAddressForm({ ...addressForm, city: e.target.value })} placeholder="Mumbai" required />
                                        </div>
                                        <div className="form-group">
                                            <label>State *</label>
                                            <input type="text" value={addressForm.state} onChange={e => setAddressForm({ ...addressForm, state: e.target.value })} placeholder="Maharashtra" required />
                                        </div>
                                    </div>
                                    <div className="form-row">
                                        <div className="form-group">
                                            <label>Postal Code *</label>
                                            <input type="text" value={addressForm.postal_code} onChange={e => setAddressForm({ ...addressForm, postal_code: e.target.value })} placeholder="400001" required />
                                        </div>
                                        <div className="form-group">
                                            <label>Country *</label>
                                            <input type="text" value={addressForm.country} onChange={e => setAddressForm({ ...addressForm, country: e.target.value })} required />
                                        </div>
                                    </div>
                                    <div style={{ display: 'flex', gap: 10 }}>
                                        <button type="submit" className="btn btn-gradient" disabled={savingAddress}>
                                            {savingAddress ? 'Saving...' : 'Save Address'}
                                        </button>
                                        {addresses.length > 0 && (
                                            <button type="button" className="btn btn-secondary" onClick={() => setShowAddressForm(false)}>Cancel</button>
                                        )}
                                    </div>
                                </form>
                            </div>
                        )}
                    </section>
                    <section className="checkout-section">
                        <h2>Payment Method</h2>
                        <div className="payment-methods-list">
                            <div className="payment-option">
                                <label>
                                    <input type="radio" name="payment_method" value="upi" checked={paymentMethod === 'upi'} onChange={() => setPaymentMethod('upi')} />
                                    <div className="payment-card-select">
                                        <div className="payment-icon"><i className="fas fa-mobile-alt"></i></div>
                                        <div className="payment-details"><h3>Pay with UPI</h3><p>Google Pay, PhonePe, Paytm</p></div>
                                    </div>
                                </label>
                            </div>
                            <div className="payment-option">
                                <label>
                                    <input type="radio" name="payment_method" value="cod" checked={paymentMethod === 'cod'} onChange={() => setPaymentMethod('cod')} />
                                    <div className="payment-card-select">
                                        <div className="payment-icon"><i className="fas fa-money-bill-wave"></i></div>
                                        <div className="payment-details"><h3>Cash on Delivery</h3><p>Additional fee of ₹{COD_FEE} applies</p></div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </section>
                    <div className="checkout-actions">
                        <button className="btn btn-gradient" onClick={handlePlaceOrder} disabled={placing || !selectedAddress}>
                            {placing ? <><span className="btn-spinner"></span> Placing Order...</> : 'Place Order'}
                        </button>
                        <Link href="/cart" className="btn btn-secondary">Back to Cart</Link>
                    </div>
                </div>
                <div className="order-summary">
                    <h2>Order Summary</h2>
                    <div className="order-items">
                        {cartItems.map(item => (
                            <div key={item.id} className="order-item">
                                <div className="order-item-details">
                                    <h3>{item.sneaker?.brand} {item.sneaker?.model}</h3>
                                    <p>Size: {item.sneaker?.size} UK</p>
                                </div>
                                <div className="order-item-price">{formatPrice(item.sneaker?.price)}</div>
                            </div>
                        ))}
                    </div>
                    <div className="order-totals">
                        <div className="summary-row"><span>Subtotal</span><span>{formatPrice(total)}</span></div>
                        <div className="summary-row"><span>Shipping</span><span>Free</span></div>
                        {paymentMethod === 'cod' && <div className="summary-row"><span>COD Fee</span><span>₹{COD_FEE}</span></div>}
                        <div className="summary-row total"><span>Total</span><span>{formatPrice(finalTotal)}</span></div>
                    </div>
                </div>
            </div>
        </div>
    );
}
