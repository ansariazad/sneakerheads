import Link from 'next/link';
import { formatPrice } from '@/lib/mockData';
export const metadata = { title: 'Payment - Sneakerheads' };

export default function PaymentPage() {
    return (
        <div className="container">
            <div className="confirmation-container">
                <div className="confirmation-icon" style={{ color: 'var(--primary-color)' }}><i className="fas fa-mobile-alt"></i></div>
                <h1>Complete Payment via UPI</h1>
                <p>Please complete the payment to confirm your order.</p>
                <div className="confirmation-details">
                    <h2>Payment Details</h2>
                    <div className="detail-row"><span>Order ID</span><span>#1004</span></div>
                    <div className="detail-row"><span>Amount</span><span>{formatPrice(25990)}</span></div>
                    <div className="detail-row"><span>UPI ID</span><span>sneakerheads@upi</span></div>
                    <div style={{ textAlign: 'center', marginTop: 20 }}>
                        <div style={{ background: 'var(--bg-light)', borderRadius: 12, padding: 30, display: 'inline-block' }}>
                            <i className="fas fa-qrcode" style={{ fontSize: 120, color: 'var(--text-secondary)' }}></i>
                            <p style={{ color: 'var(--text-secondary)', marginTop: 10 }}>Scan QR Code to Pay</p>
                        </div>
                    </div>
                </div>
                <div className="confirmation-actions">
                    <Link href="/order-confirmation" className="btn btn-success">I&apos;ve Made the Payment</Link>
                    <Link href="/orders" className="btn btn-secondary">Pay Later</Link>
                </div>
            </div>
        </div>
    );
}
