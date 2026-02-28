'use client';
import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';

// Map of alternate images for showcase sneakers
const altImages = {
    '/images/sneakers/nike-jordan1.jpg': '/images/sneakers/nike-jordan1-alt.jpg',
    '/images/sneakers/adidas-yeezy350.jpg': '/images/sneakers/adidas-yeezy350-alt.jpg',
    '/images/sneakers/nike-dunk-low.jpg': '/images/sneakers/nike-dunk-low-alt.jpg',
    '/images/sneakers/jordan4-retro.jpg': '/images/sneakers/jordan4-retro-alt.jpg',
    '/images/sneakers/nike-airmax90.jpg': '/images/sneakers/nike-airmax90-alt.jpg',
    '/images/sneakers/newbalance-550.jpg': '/images/sneakers/newbalance-550-alt.jpg',
};

export default function SneakerCard({ sneaker }) {
    const imageUrl = sneaker.images?.[0]?.image_url || '/images/sneakers/nike-jordan1.jpg';
    const altImageUrl = sneaker.images?.[1]?.image_url || altImages[imageUrl] || null;
    const isShowcase = sneaker.id?.startsWith?.('showcase-');
    const href = isShowcase ? '/search' : `/sneaker/${sneaker.id}`;
    const [hovered, setHovered] = useState(false);

    const formatPrice = (price) => {
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(price);
    };

    const originalPrice = sneaker.featured ? Math.round(sneaker.price * 1.2) : null;
    const currentImage = hovered && altImageUrl ? altImageUrl : imageUrl;

    return (
        <Link
            href={href}
            className="sneaker-card"
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
        >
            <div className="card-img">
                <Image
                    src={currentImage}
                    alt={`${sneaker.brand} ${sneaker.model}`}
                    fill
                    sizes="(max-width: 768px) 50vw, 25vw"
                    style={{ objectFit: 'cover', transition: 'opacity 0.3s ease' }}
                    loading="lazy"
                />
                {sneaker.featured && <span className="featured-badge">Hot 🔥</span>}
                {sneaker.condition && <span className="condition-badge">{sneaker.condition}</span>}
            </div>
            <div className="card-body">
                <div className="card-brand">{sneaker.brand}</div>
                <h3 className="card-title">{sneaker.model}</h3>
                <p className="card-text">Size: UK {sneaker.size}</p>
                <div className="card-pricing">
                    <span className="card-price">{formatPrice(sneaker.price)}</span>
                    {originalPrice && <span className="card-original-price">{formatPrice(originalPrice)}</span>}
                    {originalPrice && <span className="card-discount">-20%</span>}
                </div>
            </div>
        </Link>
    );
}
