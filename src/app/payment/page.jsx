'use client';
import { useState, useEffect } from 'react';
import { useSearchParams } from 'next/navigation';
import { Suspense } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { getOrder, formatPrice } from '@/lib/db';

function PaymentContent() {
    const searchParams = useSearchParams();
    const router = useRouter();
    const { user, loading: authLoading } = useAuth();
    const orderId = searchParams.get('orderId');
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        if (authLoading) return;
        if (!user) { router.push('/login'); return; }
        if (!orderId) { setLoading(false); return; }
        getOrder(orderId).then(setOrder).catch(console.error).finally(() => setLoading(false));
    }, [orderId, user, authLoading, router]);

    const copyUpiId = () => {
        navigator.clipboard.writeText('7208434724@ptsbi');
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    if (loading || authLoading) return <div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>;

    const amount = order ? formatPrice(order.total_amount) : '—';

    return (
        <div className="container">
            <div className="payment-page-container">
                <div className="payment-header">
                    <div className="payment-header-icon">
                        <i className="fas fa-mobile-alt"></i>
                    </div>
                    <h1>Complete Payment via UPI</h1>
                    <p>Scan the QR code below or use the UPI ID to make the payment</p>
                </div>

                <div className="payment-content-grid">
                    <div className="qr-section">
                        <div className="qr-card">
                            <div className="qr-image-wrapper">
                                <Image
                                    src="/images/sajidgpayqr.jpeg"
                                    alt="UPI QR Code - Scan to Pay"
                                    width={280}
                                    height={280}
                                    priority
                                    style={{ borderRadius: 12 }}
                                />
                            </div>
                            <p className="qr-scan-text">Scan with any UPI app</p>
                            <div className="upi-apps">
                                <span><i className="fab fa-google-pay"></i> GPay</span>
                                <span><i className="fas fa-mobile-alt"></i> PhonePe</span>
                                <span><i className="fas fa-wallet"></i> Paytm</span>
                            </div>
                        </div>
                    </div>

                    <div className="payment-details-section">
                        <div className="payment-info-card">
                            <h3>Payment Details</h3>
                            {order && (
                                <>
                                    <div className="detail-row"><span>Order ID</span><span>#{order.id?.slice(0, 8)}</span></div>
                                    <div className="detail-row"><span>Tracking ID</span><span>{order.tracking_id}</span></div>
                                </>
                            )}
                            <div className="detail-row total"><span>Amount to Pay</span><span className="amount-highlight">{amount}</span></div>
                        </div>

                        <div className="upi-id-card">
                            <label>UPI ID</label>
                            <div className="upi-id-copy">
                                <code>7208434724@ptsbi</code>
                                <button className="btn-copy" onClick={copyUpiId}>
                                    <i className={`fas ${copied ? 'fa-check' : 'fa-copy'}`}></i>
                                    {copied ? 'Copied!' : 'Copy'}
                                </button>
                            </div>
                        </div>

                        <div className="payment-steps">
                            <h4>How to Pay</h4>
                            <ol>
                                <li>Open any UPI app (GPay, PhonePe, Paytm)</li>
                                <li>Scan the QR code or enter the UPI ID</li>
                                <li>Enter the amount: <strong>{amount}</strong></li>
                                <li>Complete the payment</li>
                                <li>Click &quot;I&apos;ve Made the Payment&quot; below</li>
                            </ol>
                        </div>

                        <div className="payment-action-buttons">
                            <Link href={order ? `/order-confirmation?orderId=${order.id}` : '/order-confirmation'} className="btn btn-gradient btn-full">
                                <i className="fas fa-check-circle"></i> I&apos;ve Made the Payment
                            </Link>
                            <Link href="/orders" className="btn btn-secondary btn-full" style={{ marginTop: 10 }}>
                                Pay Later
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function PaymentPage() {
    return (
        <Suspense fallback={<div className="container"><p style={{ textAlign: 'center', padding: 60, color: 'var(--text-secondary)' }}>Loading...</p></div>}>
            <PaymentContent />
        </Suspense>
    );
}
