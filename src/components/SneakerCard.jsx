'use client';
import Link from 'next/link';
import Image from 'next/image';
import { formatPrice } from '@/lib/db';

export default function SneakerCard({ sneaker }) {
    const sortedImages = (sneaker.images || []).sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
    const imageUrl = sortedImages[0]?.image_url || `https://placehold.co/400x300/2d2d2d/3498db?text=${encodeURIComponent(sneaker.brand + ' ' + sneaker.model)}`;

    return (
        <div className="sneaker-card">
            <div className="card-img">
                <Image
                    src={imageUrl}
                    alt={`${sneaker.brand} ${sneaker.model}`}
                    fill
                    sizes="(max-width: 480px) 100vw, (max-width: 768px) 50vw, (max-width: 1024px) 33vw, 25vw"
                    style={{ objectFit: 'cover' }}
                    loading="lazy"
                />
                {sneaker.featured && <span className="featured-badge">Featured</span>}
                {sneaker.condition && sneaker.condition !== 'new' && <span className="condition-badge">{sneaker.condition.replace('_', ' ')}</span>}
            </div>
            <div className="card-body">
                <p className="card-brand">{sneaker.brand}</p>
                <h3 className="card-title">{sneaker.model}</h3>
                <p className="card-text">Size: {sneaker.size} UK</p>
                <div className="card-price">{formatPrice(sneaker.price)}</div>
            </div>
            <div className="card-footer">
                <Link href={`/sneaker/${sneaker.id}`} className="btn">View Details</Link>
            </div>
        </div>
    );
}
