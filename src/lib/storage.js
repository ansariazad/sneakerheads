import { supabase } from './supabase';

// ===== UPLOAD SNEAKER IMAGE =====
export async function uploadSneakerImage(file, sneakerId) {
    const ext = file.name.split('.').pop();
    const fileName = `${sneakerId}/${Date.now()}.${ext}`;

    const { data, error } = await supabase.storage
        .from('sneaker-images')
        .upload(fileName, file, { upsert: true });

    if (error) throw error;

    const { data: { publicUrl } } = supabase.storage
        .from('sneaker-images')
        .getPublicUrl(data.path);

    // Save to sneaker_images table
    await supabase.from('sneaker_images').insert({
        sneaker_id: sneakerId,
        image_url: publicUrl,
    });

    return publicUrl;
}

// ===== UPLOAD SNEAKER VIDEO =====
export async function uploadSneakerVideo(file, sneakerId) {
    const ext = file.name.split('.').pop();
    const fileName = `${sneakerId}/${Date.now()}.${ext}`;

    const { data, error } = await supabase.storage
        .from('sneaker-videos')
        .upload(fileName, file, { upsert: true });

    if (error) throw error;

    const { data: { publicUrl } } = supabase.storage
        .from('sneaker-videos')
        .getPublicUrl(data.path);

    await supabase.from('sneaker_videos').insert({
        sneaker_id: sneakerId,
        video_url: publicUrl,
    });

    return publicUrl;
}

// ===== UPLOAD PROFILE IMAGE =====
export async function uploadProfileImage(file, userId) {
    const ext = file.name.split('.').pop();
    const fileName = `${userId}/avatar.${ext}`;

    const { data, error } = await supabase.storage
        .from('profile-images')
        .upload(fileName, file, { upsert: true });

    if (error) throw error;

    const { data: { publicUrl } } = supabase.storage
        .from('profile-images')
        .getPublicUrl(data.path);

    // Update profile
    await supabase.from('profiles').update({ profile_image: publicUrl }).eq('id', userId);

    return publicUrl;
}

// ===== GET IMAGE URL =====
export function getImageUrl(path, bucket = 'sneaker-images') {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    const { data: { publicUrl } } = supabase.storage.from(bucket).getPublicUrl(path);
    return publicUrl;
}
