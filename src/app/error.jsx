'use client';

export default function GlobalError({ error, reset }) {
    return (
        <div className="container" style={{ textAlign: 'center', padding: '80px 20px' }}>
            <div style={{ fontSize: 60, marginBottom: 20, color: 'var(--error-color)' }}>
                <i className="fas fa-exclamation-triangle"></i>
            </div>
            <h1 style={{ marginBottom: 10 }}>Something went wrong</h1>
            <p style={{ color: 'var(--text-secondary)', marginBottom: 30, maxWidth: 500, margin: '0 auto 30px' }}>
                {error?.message || 'An unexpected error occurred. Please try again.'}
            </p>
            <button className="btn btn-gradient" onClick={reset}>
                <i className="fas fa-redo"></i> Try Again
            </button>
        </div>
    );
}
