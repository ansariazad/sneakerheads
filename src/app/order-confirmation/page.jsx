import Link from 'next/link';
import { formatPrice } from '@/lib/mockData';

export const metadata = { title: 'Order Confirmed - Sneakerheads' };

export default function OrderConfirmationPage() {
    return (
        <div className="container">
            <div className="confirmation-container">
                <div className="confirmation-icon"><i className="fas fa-check-circle"></i></div>
                <h1>Order Placed Successfully!</h1>
                <p>Thank you for your purchase. Your order has been confirmed.</p>
                <div className="confirmation-details">
                    <h2>Order Details</h2>
                    <div className="detail-row"><span>Order ID</span><span>#1004</span></div>
                    <div className="detail-row"><span>Order Date</span><span>{new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>
                    <div className="detail-row"><span>Payment Method</span><span>UPI</span></div>
                    <div className="detail-row"><span>Total Amount</span><span>{formatPrice(25990)}</span></div>
                    <div className="detail-row"><span>Tracking ID</span><span>TRK-2026-004</span></div>
                    <div className="detail-row"><span>Estimated Delivery</span><span>{new Date(Date.now() + 5 * 86400000).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>
                </div>
                <div className="confirmation-actions">
                    <Link href="/orders" className="btn">View My Orders</Link>
                    <Link href="/" className="btn btn-secondary">Continue Shopping</Link>
                </div>
            </div>
        </div>
    );
}
