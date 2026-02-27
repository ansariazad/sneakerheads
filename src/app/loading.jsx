export default function Loading() {
    return (
        <div className="container" style={{ textAlign: 'center', padding: '80px 20px' }}>
            <div className="loading-spinner-container">
                <div className="loading-spinner"></div>
                <p style={{ color: 'var(--text-secondary)', marginTop: 20 }}>Loading...</p>
            </div>
        </div>
    );
}
