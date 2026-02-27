import { supabase } from './supabase';

// ===== SIGN UP =====
export async function signUp({ email, password, username, fullName, userType }) {
    const { data, error } = await supabase.auth.signUp({
        email,
        password,
        options: {
            data: {
                username,
                full_name: fullName,
                user_type: userType || 'buyer',
            },
        },
    });
    if (error) throw error;
    return data;
}

// ===== SIGN IN =====
export async function signIn({ email, password }) {
    const { data, error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) throw error;
    return data;
}

// ===== SIGN OUT =====
export async function signOut() {
    const { error } = await supabase.auth.signOut();
    if (error) throw error;
}

// ===== GET CURRENT USER =====
export async function getUser() {
    const { data: { user }, error } = await supabase.auth.getUser();
    if (error || !user) return null;

    // Fetch profile data
    const { data: profile } = await supabase
        .from('profiles')
        .select('*')
        .eq('id', user.id)
        .single();

    return { ...user, profile };
}

// ===== GET SESSION =====
export async function getSession() {
    const { data: { session } } = await supabase.auth.getSession();
    return session;
}

// ===== RESET PASSWORD =====
export async function resetPassword(email) {
    const { data, error } = await supabase.auth.resetPasswordForEmail(email, {
        redirectTo: `${window.location.origin}/reset-password`,
    });
    if (error) throw error;
    return data;
}

// ===== UPDATE PASSWORD =====
export async function updatePassword(newPassword) {
    const { data, error } = await supabase.auth.updateUser({ password: newPassword });
    if (error) throw error;
    return data;
}

// ===== AUTH STATE LISTENER =====
export function onAuthStateChange(callback) {
    return supabase.auth.onAuthStateChange(callback);
}
