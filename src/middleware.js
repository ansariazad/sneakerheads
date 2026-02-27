import { createServerClient } from '@supabase/ssr';
import { NextResponse } from 'next/server';

export async function middleware(request) {
    const { pathname } = request.nextUrl;

    // Define protected routes
    const protectedRoutes = ['/account', '/cart', '/checkout', '/orders', '/wishlist', '/notifications', '/payment', '/order-confirmation'];
    const sellerRoutes = ['/seller'];
    const adminRoutes = ['/admin'];

    const isProtected = protectedRoutes.some(route => pathname.startsWith(route));
    const isSeller = sellerRoutes.some(route => pathname.startsWith(route));
    const isAdmin = adminRoutes.some(route => pathname.startsWith(route));

    if (!isProtected && !isSeller && !isAdmin) {
        return NextResponse.next();
    }

    // Check for Supabase auth
    const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
    const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY;

    if (!supabaseUrl || !supabaseAnonKey) {
        return NextResponse.next();
    }

    let response = NextResponse.next({ request });

    const supabase = createServerClient(supabaseUrl, supabaseAnonKey, {
        cookies: {
            getAll() {
                return request.cookies.getAll();
            },
            setAll(cookiesToSet) {
                cookiesToSet.forEach(({ name, value, options }) => {
                    request.cookies.set(name, value);
                    response.cookies.set(name, value, options);
                });
            },
        },
    });

    const { data: { user } } = await supabase.auth.getUser();

    if (!user && (isProtected || isSeller || isAdmin)) {
        const loginUrl = new URL('/login', request.url);
        loginUrl.searchParams.set('redirect', pathname);
        return NextResponse.redirect(loginUrl);
    }

    return response;
}

export const config = {
    matcher: [
        '/account/:path*',
        '/cart/:path*',
        '/checkout/:path*',
        '/orders/:path*',
        '/wishlist/:path*',
        '/notifications/:path*',
        '/payment/:path*',
        '/order-confirmation/:path*',
        '/seller/:path*',
        '/admin/:path*',
    ],
};
