'use client';
import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthProvider';
import { createSneaker } from '@/lib/db';
import { uploadSneakerImage, uploadSneakerVideo } from '@/lib/storage';

export default function AddSneakerPage() {
    const { user } = useAuth();
    const router = useRouter();
    const [form, setForm] = useState({ brand: '', model: '', size: '', price: '', serial_number: '', description: '', category: '', condition: 'new' });
    const [images, setImages] = useState([]);
    const [video, setVideo] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!user) return;
        setLoading(true); setError('');
        try {
            const sneaker = await createSneaker({ ...form, size: Number(form.size), price: Number(form.price), seller_id: user.id });
            // Upload images
            for (const file of images) {
                await uploadSneakerImage(file, sneaker.id);
            }
            // Upload video
            if (video) { await uploadSneakerVideo(video, sneaker.id); }
            router.push('/seller/sneakers');
        } catch (err) {
            setError(err.message || 'Failed to add sneaker.');
            setLoading(false);
        }
    };

    return (
        <>
            <div className="dashboard-header"><h2>Add New Sneaker</h2></div>
            {error && <div className="alert alert-error">{error}</div>}
            <div className="sneaker-form">
                <form onSubmit={handleSubmit}>
                    <div className="form-row">
                        <div className="form-group"><label>Brand *</label><input type="text" name="brand" value={form.brand} onChange={handleChange} required placeholder="e.g. Nike" /></div>
                        <div className="form-group"><label>Model *</label><input type="text" name="model" value={form.model} onChange={handleChange} required placeholder="e.g. Air Jordan 1 Retro" /></div>
                    </div>
                    <div className="form-row">
                        <div className="form-group"><label>Size (UK) *</label><input type="number" name="size" step="0.5" min="3" max="15" value={form.size} onChange={handleChange} required /></div>
                        <div className="form-group"><label>Price (₹) *</label><input type="number" name="price" min="0" value={form.price} onChange={handleChange} required /></div>
                    </div>
                    <div className="form-group"><label>Serial Number *</label><input type="text" name="serial_number" value={form.serial_number} onChange={handleChange} required placeholder="e.g. NK-AJ1-001" /></div>
                    <div className="form-row">
                        <div className="form-group"><label>Category</label>
                            <select name="category" value={form.category} onChange={handleChange}><option value="">Select</option><option>Running</option><option>Basketball</option><option>Lifestyle</option><option>Skateboarding</option><option>Limited Edition</option><option>High Tops</option><option>Low Tops</option><option>Mid Tops</option><option>Slip-Ons</option><option>Boots</option><option>Training</option><option>Tennis</option><option>Football</option><option>Casual</option></select>
                        </div>
                        <div className="form-group"><label>Condition</label>
                            <select name="condition" value={form.condition} onChange={handleChange}><option value="new">New</option><option value="like_new">Like New</option><option value="good">Good</option><option value="fair">Fair</option></select>
                        </div>
                    </div>
                    <div className="form-group"><label>Description</label><textarea name="description" rows="4" value={form.description} onChange={handleChange} placeholder="Describe the sneaker..."></textarea></div>
                    <div className="form-group">
                        <label>Images (up to 4)</label>
                        <input type="file" accept="image/*" multiple onChange={(e) => setImages(Array.from(e.target.files).slice(0, 4))} />
                        {images.length > 0 && <small>{images.length} file(s) selected</small>}
                    </div>
                    <div className="form-group">
                        <label>Video (optional)</label>
                        <input type="file" accept="video/*" onChange={(e) => setVideo(e.target.files[0])} />
                    </div>
                    <button type="submit" className="btn btn-success" disabled={loading}>{loading ? 'Submitting...' : 'Submit for Review'}</button>
                </form>
            </div>
        </>
    );
}
