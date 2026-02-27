import { supabase } from './supabase';

export const COD_FEE = 49;

export function formatPrice(price) {
    return '₹' + Number(price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ============================================
// SNEAKERS
// ============================================

export async function getSneakers({ brand, category, minPrice, maxPrice, size, condition, sort, featured, search, limit = 20, offset = 0 } = {}) {
    let query = supabase.from('sneakers').select(`
    *, 
    images:sneaker_images(image_url, image_type, display_order)
  `, { count: 'exact' }).eq('status', 'approved');

    if (brand) query = query.eq('brand', brand);
    if (category) query = query.eq('category', category);
    if (condition) query = query.eq('condition', condition);
    if (minPrice) query = query.gte('price', minPrice);
    if (maxPrice) query = query.lte('price', maxPrice);
    if (size) query = query.eq('size', size);
    if (featured) query = query.eq('featured', true);
    if (search) query = query.or(`brand.ilike.%${search}%,model.ilike.%${search}%,description.ilike.%${search}%`);

    if (sort === 'price_low') query = query.order('price', { ascending: true });
    else if (sort === 'price_high') query = query.order('price', { ascending: false });
    else query = query.order('created_at', { ascending: false });

    query = query.range(offset, offset + limit - 1);

    const { data, error, count } = await query;
    if (error) {
        console.error('getSneakers error:', error);
        return { data: [], count: 0 };
    }
    return { data: data || [], count };
}

export async function getSneaker(id) {
    const { data, error } = await supabase.from('sneakers').select(`
    *,
    images:sneaker_images(id, image_url, image_type, display_order),
    videos:sneaker_videos(id, video_url)
  `).eq('id', id).single();
    if (error) throw error;
    // Fetch seller separately
    if (data?.seller_id) {
        const { data: seller } = await supabase.from('profiles').select('id, username, full_name').eq('id', data.seller_id).single();
        data.seller = seller;
    }
    return data;
}

export async function getSimilarSneakers(sneaker, limit = 4) {
    const { data } = await supabase.from('sneakers').select(`*, images:sneaker_images(image_url, display_order)`)
        .eq('status', 'approved').eq('brand', sneaker.brand).neq('id', sneaker.id).limit(limit);
    return data || [];
}

export async function getBrands() {
    const { data } = await supabase.from('sneakers').select('brand').eq('status', 'approved');
    if (!data) return [];
    const counts = {};
    data.forEach(s => { counts[s.brand] = (counts[s.brand] || 0) + 1; });
    return Object.entries(counts).map(([brand, count]) => ({ brand, count })).sort((a, b) => b.count - a.count);
}

export async function getAvailableSizes() {
    const { data } = await supabase.from('sneakers').select('size').eq('status', 'approved');
    if (!data) return [];
    return [...new Set(data.map(s => s.size))].sort((a, b) => a - b);
}

// ============================================
// CART
// ============================================

export async function getCart(userId) {
    const { data, error } = await supabase.from('cart').select(`
    *,
    sneaker:sneakers(*, images:sneaker_images(image_url, display_order))
  `).eq('user_id', userId);
    if (error) throw error;
    return data || [];
}

export async function addToCart(userId, sneakerId) {
    const { data, error } = await supabase.from('cart').upsert({ user_id: userId, sneaker_id: sneakerId }, { onConflict: 'user_id,sneaker_id' }).select();
    if (error) throw error;
    return data;
}

export async function removeFromCart(cartId) {
    const { error } = await supabase.from('cart').delete().eq('id', cartId);
    if (error) throw error;
}

export async function getCartCount(userId) {
    const { count } = await supabase.from('cart').select('*', { count: 'exact', head: true }).eq('user_id', userId);
    return count || 0;
}

// ============================================
// WISHLIST
// ============================================

export async function getWishlist(userId) {
    const { data, error } = await supabase.from('wishlist').select(`
    *,
    sneaker:sneakers(*, images:sneaker_images(image_url, display_order))
  `).eq('user_id', userId);
    if (error) throw error;
    return data || [];
}

export async function addToWishlist(userId, sneakerId) {
    const { data, error } = await supabase.from('wishlist').upsert({ user_id: userId, sneaker_id: sneakerId }, { onConflict: 'user_id,sneaker_id' }).select();
    if (error) throw error;
    return data;
}

export async function removeFromWishlist(wishlistId) {
    const { error } = await supabase.from('wishlist').delete().eq('id', wishlistId);
    if (error) throw error;
}

export async function isInWishlist(userId, sneakerId) {
    const { data } = await supabase.from('wishlist').select('id').eq('user_id', userId).eq('sneaker_id', sneakerId).maybeSingle();
    return !!data;
}

// ============================================
// ORDERS
// ============================================

export async function createOrder(userId, addressId, paymentMethod, cartItems) {
    const codFee = paymentMethod === 'cod' ? COD_FEE : 0;
    const subtotal = cartItems.reduce((sum, item) => sum + Number(item.sneaker.price), 0);
    const totalAmount = subtotal + codFee;
    const trackingId = 'TRK-' + new Date().getFullYear() + '-' + String(Math.floor(Math.random() * 99999)).padStart(5, '0');
    const deliveryEta = new Date(Date.now() + 5 * 86400000).toISOString().split('T')[0];

    // Create order
    const { data: order, error: orderError } = await supabase.from('orders').insert({
        user_id: userId, address_id: addressId, total_amount: totalAmount,
        payment_method: paymentMethod, tracking_id: trackingId, delivery_eta: deliveryEta,
        payment_status: paymentMethod === 'cod' ? 'pending' : 'pending',
    }).select().single();
    if (orderError) throw orderError;

    // Create order items
    const orderItems = cartItems.map(item => ({
        order_id: order.id, sneaker_id: item.sneaker.id, price: item.sneaker.price,
    }));
    const { error: itemsError } = await supabase.from('order_items').insert(orderItems);
    if (itemsError) throw itemsError;

    // Mark sneakers as sold
    const sneakerIds = cartItems.map(item => item.sneaker.id);
    await supabase.from('sneakers').update({ status: 'sold' }).in('id', sneakerIds);

    // Clear cart
    await supabase.from('cart').delete().eq('user_id', userId);

    // Create payment records for sellers
    for (const item of cartItems) {
        const fee = Number(item.sneaker.price) * 0.10;
        await supabase.from('payments').insert({
            seller_id: item.sneaker.seller_id, order_item_id: null,
            amount: item.sneaker.price, platform_fee: fee, net_amount: item.sneaker.price - fee,
        });
    }

    // Notification
    await supabase.from('notifications').insert({
        user_id: userId, message: `Your order has been placed! Tracking ID: ${trackingId}`,
    });

    return order;
}

export async function getOrders(userId) {
    const { data, error } = await supabase.from('orders').select(`
    *,
    items:order_items(*, sneaker:sneakers(brand, model, size, images:sneaker_images(image_url)))
  `).eq('user_id', userId).order('created_at', { ascending: false });
    if (error) throw error;
    return data || [];
}

export async function getOrder(orderId) {
    const { data, error } = await supabase.from('orders').select(`
    *,
    address:addresses(*),
    items:order_items(*, sneaker:sneakers(brand, model, size, price, images:sneaker_images(image_url)))
  `).eq('id', orderId).single();
    if (error) throw error;
    return data;
}

// ============================================
// ADDRESSES
// ============================================

export async function getAddresses(userId) {
    const { data, error } = await supabase.from('addresses').select('*').eq('user_id', userId).order('is_default', { ascending: false });
    if (error) throw error;
    return data || [];
}

export async function addAddress(userId, address) {
    if (address.is_default) {
        await supabase.from('addresses').update({ is_default: false }).eq('user_id', userId);
    }
    const { data, error } = await supabase.from('addresses').insert({ user_id: userId, ...address }).select().single();
    if (error) throw error;
    return data;
}

export async function updateAddress(addressId, updates) {
    const { data, error } = await supabase.from('addresses').update(updates).eq('id', addressId).select().single();
    if (error) throw error;
    return data;
}

export async function deleteAddress(addressId) {
    const { error } = await supabase.from('addresses').delete().eq('id', addressId);
    if (error) throw error;
}

// ============================================
// NOTIFICATIONS
// ============================================

export async function getNotifications(userId) {
    const { data, error } = await supabase.from('notifications').select('*').eq('user_id', userId).order('created_at', { ascending: false });
    if (error) throw error;
    return data || [];
}

export async function markNotificationRead(notificationId) {
    await supabase.from('notifications').update({ is_read: true }).eq('id', notificationId);
}

export async function getUnreadNotificationCount(userId) {
    const { count } = await supabase.from('notifications').select('*', { count: 'exact', head: true }).eq('user_id', userId).eq('is_read', false);
    return count || 0;
}

// ============================================
// PROFILE
// ============================================

export async function updateProfile(userId, updates) {
    const { data, error } = await supabase.from('profiles').update(updates).eq('id', userId).select().single();
    if (error) throw error;
    return data;
}

export async function updatePaymentDetails(userId, details) {
    const { data, error } = await supabase.from('profiles').update(details).eq('id', userId).select().single();
    if (error) throw error;
    return data;
}

// ============================================
// SELLER
// ============================================

export async function getSellerSneakers(sellerId) {
    const { data, error } = await supabase.from('sneakers').select(`*, images:sneaker_images(image_url, display_order)`)
        .eq('seller_id', sellerId).order('created_at', { ascending: false });
    if (error) throw error;
    return data || [];
}

export async function createSneaker(sneakerData) {
    const { data, error } = await supabase.from('sneakers').insert(sneakerData).select().single();
    if (error) throw error;
    return data;
}

export async function updateSneaker(sneakerId, updates) {
    const { data, error } = await supabase.from('sneakers').update(updates).eq('id', sneakerId).select().single();
    if (error) throw error;
    return data;
}

export async function getSellerSales(sellerId) {
    const { data, error } = await supabase.from('payments').select(`
    *,
    order_item:order_items(*, sneaker:sneakers(brand, model, size))
  `).eq('seller_id', sellerId).order('created_at', { ascending: false });
    if (error) throw error;
    return data || [];
}

export async function getSellerStats(sellerId) {
    const sneakers = await getSellerSneakers(sellerId);
    const sales = await getSellerSales(sellerId);
    const totalRevenue = sales.reduce((s, p) => s + Number(p.amount), 0);
    const totalEarnings = sales.reduce((s, p) => s + Number(p.net_amount), 0);
    return { sneakerCount: sneakers.length, salesCount: sales.length, totalRevenue, totalEarnings };
}

export async function requestPayment(paymentId) {
    const { error } = await supabase.from('payments').update({ status: 'requested' }).eq('id', paymentId);
    if (error) throw error;
}

// ============================================
// ADMIN
// ============================================

export async function getAllUsers() {
    const { data, error } = await supabase.from('profiles').select('*').order('created_at', { ascending: false });
    if (error) throw error;
    return data || [];
}

export async function toggleUserActive(userId, isActive) {
    const { error } = await supabase.from('profiles').update({ is_active: isActive }).eq('id', userId);
    if (error) throw error;
}

export async function getAllSneakers() {
    const { data, error } = await supabase.from('sneakers').select(`*, images:sneaker_images(image_url)`)
        .order('created_at', { ascending: false });
    if (error) throw error;
    // Fetch seller usernames
    if (data) {
        const sellerIds = [...new Set(data.map(s => s.seller_id).filter(Boolean))];
        if (sellerIds.length > 0) {
            const { data: sellers } = await supabase.from('profiles').select('id, username').in('id', sellerIds);
            const sellerMap = Object.fromEntries((sellers || []).map(s => [s.id, s]));
            data.forEach(s => { s.seller = sellerMap[s.seller_id] || null; });
        }
    }
    return data || [];
}

export async function updateSneakerStatus(sneakerId, status) {
    const { error } = await supabase.from('sneakers').update({ status }).eq('id', sneakerId);
    if (error) throw error;
}

export async function getAllOrders() {
    const { data, error } = await supabase.from('orders').select(`
    *,
    items:order_items(*, sneaker:sneakers(brand, model))
  `).order('created_at', { ascending: false });
    if (error) throw error;
    // Fetch user usernames
    if (data) {
        const userIds = [...new Set(data.map(o => o.user_id).filter(Boolean))];
        if (userIds.length > 0) {
            const { data: users } = await supabase.from('profiles').select('id, username').in('id', userIds);
            const userMap = Object.fromEntries((users || []).map(u => [u.id, u]));
            data.forEach(o => { o.user = userMap[o.user_id] || null; });
        }
    }
    return data || [];
}

export async function updateOrderStatus(orderId, orderStatus) {
    const { error } = await supabase.from('orders').update({ order_status: orderStatus, updated_at: new Date().toISOString() }).eq('id', orderId);
    if (error) throw error;
}

export async function getAllPayments() {
    const { data, error } = await supabase.from('payments').select('*')
        .order('created_at', { ascending: false });
    if (error) throw error;
    // Fetch seller usernames
    if (data) {
        const sellerIds = [...new Set(data.map(p => p.seller_id).filter(Boolean))];
        if (sellerIds.length > 0) {
            const { data: sellers } = await supabase.from('profiles').select('id, username').in('id', sellerIds);
            const sellerMap = Object.fromEntries((sellers || []).map(s => [s.id, s]));
            data.forEach(p => { p.seller = sellerMap[p.seller_id] || null; });
        }
    }
    return data || [];
}

export async function approvePayment(paymentId) {
    const { error } = await supabase.from('payments').update({ status: 'completed', updated_at: new Date().toISOString() }).eq('id', paymentId);
    if (error) throw error;
}

export async function getAdminStats() {
    const { count: userCount } = await supabase.from('profiles').select('*', { count: 'exact', head: true });
    const { count: sneakerCount } = await supabase.from('sneakers').select('*', { count: 'exact', head: true });
    const { count: orderCount } = await supabase.from('orders').select('*', { count: 'exact', head: true });
    const { data: orders } = await supabase.from('orders').select('total_amount');
    const totalRevenue = (orders || []).reduce((s, o) => s + Number(o.total_amount), 0);
    return { userCount: userCount || 0, sneakerCount: sneakerCount || 0, orderCount: orderCount || 0, totalRevenue };
}
