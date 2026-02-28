import Image from 'next/image';

export default function Loading() {
    return (
        <div style={{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            minHeight: '60vh',
            gap: 20,
        }}>
            <div className="loading-logo-pulse">
                <Image src="/images/sneakerheads-logo.svg" alt="Loading" width={80} height={80} priority />
            </div>
            <div className="loading-bar">
                <div className="loading-bar-fill"></div>
            </div>
            <p style={{ color: 'var(--text-secondary)', fontSize: 14 }}>Loading awesome sneakers...</p>
        </div>
    );
}
