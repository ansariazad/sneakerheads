'use client';
import { useState, useRef } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useAuth } from '@/components/AuthProvider';

// Mock AI recognition database
const sneakerDB = [
    { brand: 'Nike', model: 'Air Jordan 1 Retro High OG', price: 16995, category: 'Basketball' },
    { brand: 'Adidas', model: 'Yeezy Boost 350 V2', price: 22999, category: 'Lifestyle' },
    { brand: 'Nike', model: 'Dunk Low', price: 8995, category: 'Lifestyle' },
    { brand: 'Nike', model: 'Air Max 90', price: 12499, category: 'Running' },
    { brand: 'Jordan', model: 'Air Jordan 4 Retro', price: 19999, category: 'Basketball' },
    { brand: 'New Balance', model: '550', price: 10999, category: 'Lifestyle' },
    { brand: 'Puma', model: 'RS-X Reinvention', price: 7499, category: 'Running' },
    { brand: 'Nike', model: 'Air Force 1 Low', price: 7999, category: 'Lifestyle' },
    { brand: 'Adidas', model: 'Ultraboost 22', price: 14999, category: 'Running' },
    { brand: 'Converse', model: 'Chuck Taylor All Star', price: 4999, category: 'Classic' },
    { brand: 'Vans', model: 'Old Skool', price: 4499, category: 'Skate' },
    { brand: 'Reebok', model: 'Classic Leather', price: 6999, category: 'Classic' },
    { brand: 'Campus', model: 'First Running Shoes', price: 1499, category: 'Running' },
    { brand: 'Sparx', model: 'SM-439 Sport', price: 999, category: 'Running' },
    { brand: 'Red Tape', model: 'Walking Shoes', price: 2499, category: 'Walking' },
    { brand: 'Woodland', model: 'Outdoor Shoes', price: 3499, category: 'Outdoor' },
];

export default function ScanPage() {
    const { user } = useAuth();
    const [preview, setPreview] = useState(null);
    const [scanning, setScanning] = useState(false);
    const [result, setResult] = useState(null);
    const [progress, setProgress] = useState(0);
    const fileRef = useRef(null);

    const handleFileSelect = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (ev) => setPreview(ev.target.result);
        reader.readAsDataURL(file);
        setResult(null);
    };

    const handleScan = () => {
        if (!preview) return;
        setScanning(true);
        setProgress(0);
        setResult(null);

        // Simulate AI scanning with progress
        const steps = [
            { p: 15, label: 'Analyzing image...' },
            { p: 35, label: 'Detecting brand logo...' },
            { p: 55, label: 'Identifying model...' },
            { p: 75, label: 'Checking database...' },
            { p: 90, label: 'Estimating price...' },
            { p: 100, label: 'Complete!' },
        ];

        steps.forEach((step, i) => {
            setTimeout(() => {
                setProgress(step.p);
                if (i === steps.length - 1) {
                    // Pick a random sneaker from DB
                    const match = sneakerDB[Math.floor(Math.random() * sneakerDB.length)];
                    const confidence = 85 + Math.floor(Math.random() * 14);
                    const priceRange = {
                        low: Math.round(match.price * 0.8),
                        high: Math.round(match.price * 1.1),
                        suggested: match.price,
                    };
                    setResult({ ...match, confidence, priceRange });
                    setScanning(false);
                }
            }, (i + 1) * 700);
        });
    };

    const formatPrice = (p) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(p);

    return (
        <div className="container">
            <div style={{ textAlign: 'center', marginBottom: 40 }}>
                <h1 className="page-title" style={{ marginBottom: 8 }}>
                    <i className="fas fa-camera" style={{ marginRight: 10, color: 'var(--primary-color)' }}></i>
                    AI Sneaker Scanner
                </h1>
                <p style={{ color: 'var(--text-secondary)', maxWidth: 500, margin: '0 auto' }}>
                    Upload a photo of your sneakers and our AI will auto-detect the brand, model, and suggest a selling price.
                </p>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: preview ? '1fr 1fr' : '1fr', gap: 30, maxWidth: 900, margin: '0 auto' }}>
                {/* Upload Area */}
                <div className="scan-upload-card" onClick={() => fileRef.current?.click()}>
                    {preview ? (
                        <div style={{ position: 'relative', width: '100%', aspectRatio: '1', borderRadius: 12, overflow: 'hidden' }}>
                            <Image src={preview} alt="Sneaker preview" fill style={{ objectFit: 'contain' }} />
                        </div>
                    ) : (
                        <div className="scan-upload-placeholder">
                            <i className="fas fa-cloud-upload-alt" style={{ fontSize: 48, color: 'var(--primary-color)', marginBottom: 16 }}></i>
                            <h3>Upload Sneaker Photo</h3>
                            <p>Click or drag & drop an image</p>
                            <p style={{ fontSize: 12, color: 'var(--text-secondary)', marginTop: 8 }}>JPG, PNG up to 10MB</p>
                        </div>
                    )}
                    <input ref={fileRef} type="file" accept="image/*" onChange={handleFileSelect} style={{ display: 'none' }} />
                </div>

                {/* Result Area */}
                {preview && (
                    <div>
                        {!result && !scanning && (
                            <div style={{ textAlign: 'center' }}>
                                <button className="btn btn-gradient btn-full" onClick={handleScan} style={{ marginBottom: 20, padding: '14px 30px', fontSize: 16 }}>
                                    <i className="fas fa-magic" style={{ marginRight: 10 }}></i>Scan with AI
                                </button>
                                <p style={{ color: 'var(--text-secondary)', fontSize: 13 }}>Our AI will analyze the image and identify the sneaker</p>
                            </div>
                        )}

                        {scanning && (
                            <div className="scan-progress-card">
                                <div className="loading-logo-pulse" style={{ textAlign: 'center', marginBottom: 20 }}>
                                    <i className="fas fa-brain" style={{ fontSize: 36, color: 'var(--primary-color)' }}></i>
                                </div>
                                <p style={{ textAlign: 'center', marginBottom: 12, fontWeight: 500 }}>Analyzing sneaker...</p>
                                <div style={{ background: 'var(--bg-light)', borderRadius: 10, overflow: 'hidden', height: 6 }}>
                                    <div style={{ width: `${progress}%`, height: '100%', background: 'var(--gradient)', borderRadius: 10, transition: 'width 0.5s ease' }}></div>
                                </div>
                                <p style={{ textAlign: 'center', marginTop: 8, fontSize: 13, color: 'var(--text-secondary)' }}>{progress}%</p>
                            </div>
                        )}

                        {result && (
                            <div className="scan-result-card">
                                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 16 }}>
                                    <i className="fas fa-check-circle" style={{ color: '#06d6a0', fontSize: 20 }}></i>
                                    <h3 style={{ margin: 0 }}>Sneaker Identified!</h3>
                                    <span style={{ marginLeft: 'auto', background: 'rgba(6,214,160,0.15)', color: '#06d6a0', padding: '3px 10px', borderRadius: 50, fontSize: 12, fontWeight: 600 }}>
                                        {result.confidence}% match
                                    </span>
                                </div>

                                <div className="scan-result-details">
                                    <div className="detail-row"><span>Brand</span><span style={{ fontWeight: 600 }}>{result.brand}</span></div>
                                    <div className="detail-row"><span>Model</span><span style={{ fontWeight: 600 }}>{result.model}</span></div>
                                    <div className="detail-row"><span>Category</span><span>{result.category}</span></div>
                                    <div style={{ marginTop: 16, padding: 16, background: 'rgba(59,130,246,0.08)', borderRadius: 12, border: '1px solid rgba(59,130,246,0.2)' }}>
                                        <p style={{ fontSize: 12, color: 'var(--text-secondary)', marginBottom: 6 }}>Suggested Price Range</p>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                            <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{formatPrice(result.priceRange.low)}</span>
                                            <div style={{ flex: 1, height: 4, background: 'var(--bg-light)', borderRadius: 4, position: 'relative' }}>
                                                <div style={{ position: 'absolute', left: '50%', transform: 'translateX(-50%)', top: -8, width: 20, height: 20, borderRadius: '50%', background: 'var(--gradient)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                    <i className="fas fa-caret-down" style={{ color: 'white', fontSize: 10 }}></i>
                                                </div>
                                            </div>
                                            <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{formatPrice(result.priceRange.high)}</span>
                                        </div>
                                        <p style={{ textAlign: 'center', fontWeight: 700, fontSize: 20, marginTop: 12, color: 'var(--primary-color)' }}>
                                            {formatPrice(result.priceRange.suggested)}
                                        </p>
                                        <p style={{ textAlign: 'center', fontSize: 11, color: 'var(--text-secondary)' }}>Recommended selling price</p>
                                    </div>
                                </div>

                                <div style={{ display: 'flex', gap: 10, marginTop: 20 }}>
                                    <Link href="/seller/sneakers/add" className="btn btn-gradient" style={{ flex: 1, textAlign: 'center' }}>
                                        <i className="fas fa-plus" style={{ marginRight: 6 }}></i>List for Sale
                                    </Link>
                                    <button className="btn btn-secondary" onClick={() => { setResult(null); setPreview(null); }}>
                                        <i className="fas fa-redo"></i> Scan Another
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* How it works */}
            <div style={{ maxWidth: 700, margin: '60px auto 0', textAlign: 'center' }}>
                <h2 style={{ marginBottom: 30 }}>How It Works</h2>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 20 }}>
                    {[
                        { icon: 'fa-camera', title: 'Upload Photo', desc: 'Take a clear photo of your sneakers' },
                        { icon: 'fa-brain', title: 'AI Scans', desc: 'Our AI identifies brand, model & condition' },
                        { icon: 'fa-tags', title: 'Get Price', desc: 'Get a suggested price and list for sale' },
                    ].map((step, i) => (
                        <div key={i} style={{ padding: 20 }}>
                            <div style={{ width: 50, height: 50, borderRadius: '50%', background: 'rgba(59,130,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 12px' }}>
                                <i className={`fas ${step.icon}`} style={{ color: 'var(--primary-color)', fontSize: 20 }}></i>
                            </div>
                            <h4>{step.title}</h4>
                            <p style={{ color: 'var(--text-secondary)', fontSize: 13 }}>{step.desc}</p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
