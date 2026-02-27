'use client';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { mockSales, formatPrice } from '@/lib/mockData';

export default function SaleDetailPage() {
    const { id } = useParams();
    const sale = mockSales.find(s => s.sale_id === Number(id)) || mockSales[0];

    return (
        <>
            <div className="dashboard-header"><h2>Sale #{sale.sale_id}</h2><Link href="/seller/sales" className="btn btn-secondary">Back to Sales</Link></div>
            <div className="confirmation-details" style={{ marginBottom: 20 }}>
                <h2 style={{ marginBottom: 15 }}>Sale Details</h2>
                <div className="detail-row"><span>Sneaker</span><span>{sale.sneaker.brand} {sale.sneaker.model}</span></div>
                <div className="detail-row"><span>Order ID</span><span>#{sale.order_id}</span></div>
                <div className="detail-row"><span>Sale Price</span><span>{formatPrice(sale.price)}</span></div>
                <div className="detail-row"><span>Platform Fee (10%)</span><span>-{formatPrice(sale.platform_fee)}</span></div>
                <div className="detail-row"><span>Net Amount</span><span style={{ color: 'var(--accent-color)', fontWeight: 'bold' }}>{formatPrice(sale.net_amount)}</span></div>
                <div className="detail-row"><span>Status</span><span className={`order-status status-${sale.status === 'completed' ? 'delivered' : 'processing'}`}>{sale.status}</span></div>
                <div className="detail-row"><span>Date</span><span>{new Date(sale.created_at).toLocaleDateString()}</span></div>
            </div>
        </>
    );
}
