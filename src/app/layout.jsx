import { Inter } from 'next/font/google';
import '@fortawesome/fontawesome-free/css/all.min.css';
import './globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { AuthProvider } from '@/components/AuthProvider';

const inter = Inter({ subsets: ['latin'] });

export const metadata = {
    title: 'Sneakerheads - Buy & Sell Authentic Sneakers',
    description: 'The ultimate destination for sneaker enthusiasts in India. Buy and sell authentic sneakers with confidence.',
};

export default function RootLayout({ children }) {
    return (
        <html lang="en">
            <body className={inter.className}>
                <AuthProvider>
                    <Header />
                    <div className="content-wrapper">
                        {children}
                    </div>
                    <Footer />
                </AuthProvider>
            </body>
        </html>
    );
}
