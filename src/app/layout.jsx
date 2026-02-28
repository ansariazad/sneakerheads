import { Outfit } from 'next/font/google';
import '@fortawesome/fontawesome-free/css/all.min.css';
import './globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import ChatBot from '@/components/ChatBot';
import { AuthProvider } from '@/components/AuthProvider';

const outfit = Outfit({ subsets: ['latin'], weight: ['300', '400', '500', '600', '700', '800'] });

export const metadata = {
    title: 'Sneakerheads - Buy & Sell Authentic Sneakers in India',
    description: 'India\'s #1 marketplace for buying and selling authentic sneakers. Shop Nike, Adidas, Jordan & more with confidence.',
    keywords: 'sneakers, shoes, buy sneakers, sell sneakers, authentic, Nike, Adidas, Jordan, India',
    openGraph: {
        title: 'Sneakerheads - Buy & Sell Authentic Sneakers',
        description: 'India\'s premier marketplace for authentic sneakers.',
        type: 'website',
    },
};

export default function RootLayout({ children }) {
    return (
        <html lang="en">
            <body className={outfit.className}>
                <AuthProvider>
                    <Header />
                    <div className="content-wrapper">
                        {children}
                    </div>
                    <Footer />
                    <ChatBot />
                </AuthProvider>
            </body>
        </html>
    );
}
