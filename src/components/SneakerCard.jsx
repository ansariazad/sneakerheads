'use client';
import Link from 'next/link';
import Image from 'next/image';

export default function SneakerCard({ sneaker }) {
    const imageUrl = sneaker.images?.[0]?.image_url || `/images/sneakers/nike-jordan1.png`;
    const isShowcase = sneaker.id?.startsWith?.('showcase-');
    const href = isShowcase ? '/search' : `/sneaker/${sneaker.id}`;
    const isExternal = imageUrl.startsWith('http');

    const formatPrice = (price) => {
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(price);
    };

    const originalPrice = sneaker.featured ? Math.round(sneaker.price * 1.2) : null;

    return (
        <Link href={href} className="sneaker-card">
            <div className="card-img">
                {isExternal ? (
                    <Image src={imageUrl} alt={`${sneaker.brand} ${sneaker.model}`} fill sizes="(max-width: 768px) 50vw, 25vw" style={{ objectFit: 'cover' }} loading="lazy" />
                ) : (
                    <Image src={imageUrl} alt={`${sneaker.brand} ${sneaker.model}`} fill sizes="(max-width: 768px) 50vw, 25vw" style={{ objectFit: 'cover' }} loading="lazy" />
                )}
                {sneaker.featured && <span className="featured-badge">Hot 🔥</span>}
                {sneaker.condition && <span className="condition-badge">{sneaker.condition}</span>}
            </div>
            <div className="card-body">
                <div className="card-brand">{sneaker.brand}</div>
                <h3 className="card-title">{sneaker.model}</h3>
                <p className="card-text">Size: {sneaker.size} UK</p>
                <div className="card-pricing">
                    <span className="card-price">{formatPrice(sneaker.price)}</span>
                    {originalPrice && <span className="card-original-price">{formatPrice(originalPrice)}</span>}
                    {originalPrice && <span className="card-discount">-20%</span>}
                </div>
            </div>
        </Link>
    );
}
