export function formatPrice(price) {
    return '₹' + Number(price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export const mockSneakers = [
    { sneaker_id: 1, brand: 'Nike', model: 'Air Jordan 1 Retro High OG', size: 9, serial_number: 'NK-AJ1-001', price: 16995, status: 'approved', featured: 1, image: null, seller_username: 'sneakerking', description: 'The Air Jordan 1 Retro High OG is a timeless classic that started the sneaker revolution. Features premium leather upper with iconic Wings logo and Nike Air cushioning.', created_at: '2025-12-15', seller_id: 2 },
    { sneaker_id: 2, brand: 'Adidas', model: 'Yeezy Boost 350 V2', size: 10, serial_number: 'AD-YZ-002', price: 22999, status: 'approved', featured: 1, image: null, seller_username: 'yeezymaster', description: 'The Adidas Yeezy Boost 350 V2 features Primeknit upper and full-length Boost midsole for ultimate comfort.', created_at: '2025-12-20', seller_id: 3 },
    { sneaker_id: 3, brand: 'Nike', model: 'Dunk Low Panda', size: 8, serial_number: 'NK-DL-003', price: 8995, status: 'approved', featured: 1, image: null, seller_username: 'kickscollector', description: 'Classic black and white colorway on the iconic Dunk Low silhouette. Perfect for everyday wear.', created_at: '2026-01-05', seller_id: 4 },
    { sneaker_id: 4, brand: 'Jordan', model: 'Air Jordan 4 Retro', size: 11, serial_number: 'JD-AJ4-004', price: 19999, status: 'approved', featured: 1, image: null, seller_username: 'sneakerking', description: 'The Air Jordan 4 features visible Air cushioning, mesh panels, and the iconic cage design.', created_at: '2026-01-10', seller_id: 2 },
    { sneaker_id: 5, brand: 'Puma', model: 'RS-X Reinvention', size: 9, serial_number: 'PM-RSX-005', price: 7499, status: 'approved', featured: 0, image: null, seller_username: 'streetwearfan', description: 'Bold, chunky design with RS technology cushioning for a retro-futuristic look.', created_at: '2026-01-15', seller_id: 5 },
    { sneaker_id: 6, brand: 'Nike', model: 'Air Max 90', size: 10, serial_number: 'NK-AM90-006', price: 12499, status: 'approved', featured: 0, image: null, seller_username: 'yeezymaster', description: 'The Air Max 90 stays true to its OG roots with visible Air cushioning and waffle outsole.', created_at: '2026-01-20', seller_id: 3 },
    { sneaker_id: 7, brand: 'New Balance', model: '550', size: 8.5, serial_number: 'NB-550-007', price: 10999, status: 'approved', featured: 0, image: null, seller_username: 'kickscollector', description: 'A retro basketball shoe made for the streets. Clean leather upper with classic NB branding.', created_at: '2026-01-25', seller_id: 4 },
    { sneaker_id: 8, brand: 'Adidas', model: 'Forum Low', size: 9.5, serial_number: 'AD-FL-008', price: 6999, status: 'approved', featured: 0, image: null, seller_username: 'streetwearfan', description: 'Originally from 1984, the Forum Low brings heritage basketball style to everyday outfits.', created_at: '2026-02-01', seller_id: 5 },
];

export const mockBrands = [
    { brand: 'Nike', count: 15 }, { brand: 'Adidas', count: 12 }, { brand: 'Jordan', count: 10 },
    { brand: 'Puma', count: 8 }, { brand: 'New Balance', count: 6 }, { brand: 'Reebok', count: 4 },
];

export const mockCartItems = [
    { cart_id: 1, ...mockSneakers[0] },
    { cart_id: 2, ...mockSneakers[2] },
];

export const mockAddresses = [
    { address_id: 1, address_line1: '123 MG Road', address_line2: 'Near Central Mall', city: 'Mumbai', state: 'Maharashtra', postal_code: '400001', country: 'India', is_default: true },
    { address_id: 2, address_line1: '456 Brigade Road', address_line2: '', city: 'Bangalore', state: 'Karnataka', postal_code: '560001', country: 'India', is_default: false },
];

export const mockOrders = [
    { order_id: 1001, total_amount: 25990, payment_method: 'upi', payment_status: 'completed', order_status: 'delivered', tracking_id: 'TRK-2026-001', created_at: '2026-01-15', delivery_eta: '2026-01-20', items: [mockSneakers[0], mockSneakers[2]] },
    { order_id: 1002, total_amount: 22999, payment_method: 'cod', payment_status: 'pending', order_status: 'shipped', tracking_id: 'TRK-2026-002', created_at: '2026-02-10', delivery_eta: '2026-02-15', items: [mockSneakers[1]] },
    { order_id: 1003, total_amount: 19999, payment_method: 'upi', payment_status: 'completed', order_status: 'processing', tracking_id: 'TRK-2026-003', created_at: '2026-02-20', delivery_eta: '2026-02-25', items: [mockSneakers[3]] },
];

export const mockNotifications = [
    { notification_id: 1, message: 'Your order #1001 has been delivered!', is_read: true, created_at: '2026-01-20 10:30:00' },
    { notification_id: 2, message: 'Your order #1002 has been shipped! Track it with TRK-2026-002', is_read: false, created_at: '2026-02-12 14:00:00' },
    { notification_id: 3, message: 'New arrival: Nike Dunk Low "Panda" is now available!', is_read: false, created_at: '2026-02-15 09:00:00' },
    { notification_id: 4, message: 'Your sneaker Nike Air Jordan 1 has been approved!', is_read: true, created_at: '2026-02-18 11:00:00' },
];

export const mockUser = {
    user_id: 1, username: 'sneakerfan', email: 'user@sneakerheads.com',
    full_name: 'Rahul Sharma', phone: '9876543210', user_type: 'seller_buyer',
    profile_image: null, is_active: true,
};

export const mockBankDetails = {
    account_holder_name: 'Rahul Sharma', account_number: '1234567890',
    ifsc_code: 'SBIN0001234', bank_name: 'State Bank of India',
    branch_name: 'MG Road Branch', upi_id: 'rahul@upi',
};

export const mockSellerSneakers = [
    { ...mockSneakers[0], status: 'approved' },
    { ...mockSneakers[3], status: 'pending' },
    { sneaker_id: 9, brand: 'Nike', model: 'Air Force 1 Low', size: 10, serial_number: 'NK-AF1-009', price: 7999, status: 'approved', image: null, seller_username: 'sneakerfan', description: 'The legend lives on.', created_at: '2026-02-05', seller_id: 1 },
];

export const mockSales = [
    { sale_id: 1, order_id: 1001, sneaker: mockSneakers[0], price: 16995, platform_fee: 1699.50, net_amount: 15295.50, status: 'completed', created_at: '2026-01-15' },
    { sale_id: 2, order_id: 1003, sneaker: mockSneakers[3], price: 19999, platform_fee: 1999.90, net_amount: 17999.10, status: 'processing', created_at: '2026-02-20' },
];

export const mockAdminUsers = [
    { ...mockUser, user_id: 1 },
    { user_id: 2, username: 'sneakerking', email: 'king@sneakerheads.com', full_name: 'Amit Patel', user_type: 'seller_buyer', is_active: true, created_at: '2025-06-01' },
    { user_id: 3, username: 'yeezymaster', email: 'yeezy@sneakerheads.com', full_name: 'Priya Singh', user_type: 'seller_buyer', is_active: true, created_at: '2025-07-15' },
    { user_id: 4, username: 'kickscollector', email: 'kicks@sneakerheads.com', full_name: 'Vikram Rao', user_type: 'buyer', is_active: true, created_at: '2025-08-20' },
    { user_id: 5, username: 'streetwearfan', email: 'street@sneakerheads.com', full_name: 'Ananya Das', user_type: 'buyer', is_active: false, created_at: '2025-09-10' },
];

export const mockPayments = [
    { payment_id: 1, seller_username: 'sneakerking', amount: 16995, platform_fee: 1699.50, net_amount: 15295.50, status: 'completed', created_at: '2026-01-18' },
    { payment_id: 2, seller_username: 'yeezymaster', amount: 22999, platform_fee: 2299.90, net_amount: 20699.10, status: 'requested', created_at: '2026-02-14' },
    { payment_id: 3, seller_username: 'sneakerfan', amount: 19999, platform_fee: 1999.90, net_amount: 17999.10, status: 'processing', created_at: '2026-02-22' },
];

export function getMockSneaker(id) {
    return mockSneakers.find(s => s.sneaker_id === Number(id)) || mockSneakers[0];
}

export const COD_FEE = 49;

export const faqData = [
    {
        category: 'General', items: [
            { q: 'What is Sneakerheads?', a: 'Sneakerheads is India\'s premier marketplace for buying and selling authentic sneakers. We connect sneaker enthusiasts across the country.' },
            { q: 'How does authentication work?', a: 'Every sneaker listed on our platform goes through a rigorous authentication process by our team of experts before being approved for sale.' },
            { q: 'Is there a mobile app available?', a: 'We are currently working on our mobile app. Stay tuned for updates by subscribing to our newsletter!' },
        ]
    },
    {
        category: 'Buying', items: [
            { q: 'How do I place an order?', a: 'Simply browse our collection, add items to your cart, and proceed to checkout. You can pay via UPI or Cash on Delivery.' },
            { q: 'What payment methods are accepted?', a: 'We accept UPI payments (Google Pay, PhonePe, etc.) and Cash on Delivery (COD) with a small additional fee.' },
            { q: 'How long does delivery take?', a: 'Standard delivery takes 3-5 business days across India. You\'ll receive a tracking ID once your order is shipped.' },
        ]
    },
    {
        category: 'Selling', items: [
            { q: 'How do I sell my sneakers?', a: 'Register as a Seller/Buyer account, then list your sneakers with detailed photos, descriptions, and pricing.' },
            { q: 'What fees does Sneakerheads charge?', a: 'We charge a 10% platform fee on successful sales. This covers authentication, payment processing, and platform maintenance.' },
            { q: 'How do I receive my payment?', a: 'Once the buyer receives the sneaker, you can request payment which will be transferred to your registered bank account or UPI.' },
        ]
    },
];
