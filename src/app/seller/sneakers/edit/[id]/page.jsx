'use client';
import { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { getSneaker, updateSneaker } from '@/lib/db';

export default function EditSneakerPage() {
    const { id } = useParams();
    const router = useRouter();
    const [form, setForm] = useState({ brand: '', model: '', size: '', price: '', serial_number: '', description: '', condition: 'new' });
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        getSneaker(id).then(s => {
            setForm({ brand: s.brand, model: s.model, size: s.size, price: s.price, serial_number: s.serial_number, description: s.description || '', condition: s.condition || 'new' });
        }).catch(console.error).finally(() => setLoading(false));
    }, [id]);

    const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

    const handleSubmit = async (e) => {
        e.preventDefault(); setSaving(true); setError('');
        try {
            await updateSneaker(id, { ...form, size: Number(form.size), price: Number(form.price) });
            router.push('/seller/sneakers');
        } catch (err) { setError(err.message); setSaving(false); }
    };

    if (loading) return <p style={{ textAlign: 'center', padding: 40, color: 'var(--text-secondary)' }}>Loading...</p>;

    return (
        <>
            <div className="dashboard-header"><h2>Edit Sneaker</h2></div>
            {error && <div className="alert alert-error">{error}</div>}
            <div className="sneaker-form">
                <form onSubmit={handleSubmit}>
                    <div className="form-row">
                        <div className="form-group"><label>Brand *</label><input type="text" name="brand" value={form.brand} onChange={handleChange} required /></div>
                        <div className="form-group"><label>Model *</label><input type="text" name="model" value={form.model} onChange={handleChange} required /></div>
                    </div>
                    <div className="form-row">
                        <div className="form-group"><label>Size (UK) *</label><input type="number" name="size" step="0.5" value={form.size} onChange={handleChange} required /></div>
                        <div className="form-group"><label>Price (₹) *</label><input type="number" name="price" value={form.price} onChange={handleChange} required /></div>
                    </div>
                    <div className="form-group"><label>Serial Number *</label><input type="text" name="serial_number" value={form.serial_number} onChange={handleChange} required /></div>
                    <div className="form-group"><label>Description</label><textarea name="description" rows="4" value={form.description} onChange={handleChange}></textarea></div>
                    <button type="submit" className="btn btn-success" disabled={saving}>{saving ? 'Saving...' : 'Save Changes'}</button>
                </form>
            </div>
        </>
    );
}
