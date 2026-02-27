'use client';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { getMockSneaker, formatPrice } from '@/lib/mockData';

export default function AdminSneakerDetailPage() {
    const { id } = useParams();
    const sneaker = getMockSneaker(id);

    return (
        <>
            <div className="dashboard-header"><h2>Sneaker Details</h2><Link href="/admin/sneakers" className="btn btn-secondary">Back</Link></div>
            <div className="confirmation-details">
                <div className="detail-row"><span>ID</span><span>#{sneaker.sneaker_id}</span></div>
                <div className="detail-row"><span>Brand</span><span>{sneaker.brand}</span></div>
                <div className="detail-row"><span>Model</span><span>{sneaker.model}</span></div>
                <div className="detail-row"><span>Size</span><span>{sneaker.size} UK</span></div>
                <div className="detail-row"><span>Price</span><span>{formatPrice(sneaker.price)}</span></div>
                <div className="detail-row"><span>Serial Number</span><span>{sneaker.serial_number}</span></div>
                <div className="detail-row"><span>Seller</span><span>{sneaker.seller_username}</span></div>
                <div className="detail-row"><span>Status</span><span className={`order-status status-${sneaker.status}`}>{sneaker.status}</span></div>
                <div className="detail-row"><span>Listed</span><span>{new Date(sneaker.created_at).toLocaleDateString()}</span></div>
            </div>
            <div style={{ marginTop: 20, display: 'flex', gap: 10 }}>
                <button className="btn btn-success">Approve</button>
                <button className="btn btn-danger">Reject</button>
            </div>
        </>
    );
}
