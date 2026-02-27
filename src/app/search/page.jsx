'use client';
import { useState, useEffect, Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import SneakerCard from '@/components/SneakerCard';
import { getSneakers, getBrands, getAvailableSizes } from '@/lib/db';

function SearchContent() {
    const searchParams = useSearchParams();
    const [sneakers, setSneakers] = useState([]);
    const [brands, setBrands] = useState([]);
    const [sizes, setSizes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [totalCount, setTotalCount] = useState(0);

    const [filters, setFilters] = useState({
        search: searchParams.get('q') || '',
        brand: searchParams.get('brand') || '',
        minPrice: searchParams.get('minPrice') || '',
        maxPrice: searchParams.get('maxPrice') || '',
        size: searchParams.get('size') || '',
        sort: searchParams.get('sort') || 'newest',
    });

    useEffect(() => {
        getBrands().then(setBrands).catch(() => { });
        getAvailableSizes().then(setSizes).catch(() => { });
    }, []);

    useEffect(() => {
        const load = async () => {
            setLoading(true);
            try {
                const result = await getSneakers({
                    search: filters.search || undefined,
                    brand: filters.brand || undefined,
                    minPrice: filters.minPrice || undefined,
                    maxPrice: filters.maxPrice || undefined,
                    size: filters.size || undefined,
                    sort: filters.sort,
                    limit: 20,
                });
                setSneakers(result.data);
                setTotalCount(result.count);
            } catch (err) {
                console.error('Search error:', err);
            } finally { setLoading(false); }
        };
        load();
    }, [filters]);

    const handleFilterChange = (key, value) => setFilters(f => ({ ...f, [key]: value }));

    return (
        <div className="container">
            <h1 className="page-title">
                {filters.search ? `Search Results for "${filters.search}"` : filters.brand ? `${filters.brand} Sneakers` : 'All Sneakers'}
                {!loading && <span style={{ fontSize: 16, color: 'var(--text-secondary)', fontWeight: 400 }}> ({totalCount} found)</span>}
            </h1>
            <div className="search-container">
                <div className="search-filters">
                    <div className="filter-group">
                        <label>Search</label>
                        <input type="text" value={filters.search} onChange={e => handleFilterChange('search', e.target.value)} placeholder="Search sneakers..." />
                    </div>
                    <div className="filter-group">
                        <label>Brand</label>
                        <select value={filters.brand} onChange={e => handleFilterChange('brand', e.target.value)}>
                            <option value="">All Brands</option>
                            {brands.map(b => <option key={b.brand} value={b.brand}>{b.brand} ({b.count})</option>)}
                        </select>
                    </div>
                    <div className="filter-group">
                        <label>Min Price (₹)</label>
                        <input type="number" value={filters.minPrice} onChange={e => handleFilterChange('minPrice', e.target.value)} placeholder="0" />
                    </div>
                    <div className="filter-group">
                        <label>Max Price (₹)</label>
                        <input type="number" value={filters.maxPrice} onChange={e => handleFilterChange('maxPrice', e.target.value)} placeholder="Any" />
                    </div>
                    <div className="filter-group">
                        <label>Size (UK)</label>
                        <select value={filters.size} onChange={e => handleFilterChange('size', e.target.value)}>
                            <option value="">All Sizes</option>
                            {sizes.map(s => <option key={s} value={s}>{s} UK</option>)}
                        </select>
                    </div>
                    <div className="filter-group">
                        <label>Sort By</label>
                        <select value={filters.sort} onChange={e => handleFilterChange('sort', e.target.value)}>
                            <option value="newest">Newest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                        </select>
                    </div>
                </div>
                <div className="search-results">
                    {loading ? (
                        <p style={{ textAlign: 'center', color: 'var(--text-secondary)', padding: 40 }}>Loading...</p>
                    ) : sneakers.length > 0 ? (
                        <div className="grid">{sneakers.map(s => <SneakerCard key={s.id} sneaker={s} />)}</div>
                    ) : (
                        <div className="no-results"><div className="no-results-icon"><i className="fas fa-search"></i></div><h2>No sneakers found</h2><p>Try adjusting your filters or search term.</p></div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default function SearchPage() {
    return (
        <Suspense fallback={<div className="container"><p style={{ textAlign: 'center', color: 'var(--text-secondary)', padding: 40 }}>Loading...</p></div>}>
            <SearchContent />
        </Suspense>
    );
}
