-- ============================================
-- SEED DATA: Sneakers with Real Images
-- Run AFTER supabase-schema.sql
-- Safe to run multiple times (cleans up first)
-- ============================================

-- Clean up any existing seed data first
DELETE FROM sneaker_videos;
DELETE FROM sneaker_images;
DELETE FROM sneakers;

-- ============================================
-- Store seller ID in a variable
-- ============================================
DO $$
DECLARE
  seller UUID;
BEGIN
  SELECT id INTO seller FROM profiles LIMIT 1;
  IF seller IS NULL THEN
    RAISE EXCEPTION 'No user found! Register at least one account first.';
  END IF;

  -- Nike Sneakers
  INSERT INTO sneakers (seller_id, brand, model, size, price, serial_number, description, category, condition, status, featured) VALUES
  (seller, 'Nike', 'Air Jordan 1 Retro High OG', 9, 16995.00, 'NK-AJ1-001', 'The Air Jordan 1 Retro High OG is a timeless classic that started it all. Premium leather upper with iconic Wings logo and Nike Air cushioning.', 'High Tops', 'new', 'approved', true),
  (seller, 'Nike', 'Air Jordan 4 Retro', 10, 19999.00, 'NK-AJ4-002', 'The Air Jordan 4 Retro features visible Air-Sole cushioning, mesh panels, and premium nubuck leather. A streetwear icon since 1989.', 'High Tops', 'new', 'approved', true),
  (seller, 'Nike', 'Air Force 1 Low 07', 8, 8295.00, 'NK-AF1-003', 'The Nike Air Force 1 07 is the basketball OG with legendary Air cushioning, durable leather upper, and clean classic style.', 'Low Tops', 'new', 'approved', false),
  (seller, 'Nike', 'Air Max 90', 9, 12795.00, 'NK-AM90-004', 'The Nike Air Max 90 stays true to its OG roots with the iconic Waffle outsole, visible Max Air cushioning, and vivid color combinations.', 'Running', 'new', 'approved', true),
  (seller, 'Nike', 'Dunk Low Retro', 8.5, 9695.00, 'NK-DL-005', 'Born on the hardwood but taken to the streets. The Nike Dunk Low features a padded low-cut collar and foam midsole for lightweight comfort.', 'Low Tops', 'new', 'approved', false),
  (seller, 'Nike', 'Air Max 97 Silver Bullet', 10, 16995.00, 'NK-AM97-006', 'Inspired by high-speed Japanese bullet trains. Full-length visible Air unit, reflective 3M piping, and sleek streamlined design.', 'Running', 'new', 'approved', false),
  (seller, 'Nike', 'SB Dunk High Pro', 10, 11495.00, 'NK-SBDH-007', 'Built for skateboarding with Zoom Air insoles in the heel and padded tongue and collar for impact protection.', 'Skateboarding', 'new', 'approved', false),
  (seller, 'Nike', 'Blazer Mid 77 Vintage', 9.5, 7995.00, 'NK-BM77-008', 'Throwback vibes meet modern comfort. Crisp leather upper with a classic 70s basketball look.', 'Mid Tops', 'new', 'approved', false);

  -- Adidas Sneakers
  INSERT INTO sneakers (seller_id, brand, model, size, price, serial_number, description, category, condition, status, featured) VALUES
  (seller, 'Adidas', 'Ultraboost 23', 9, 18999.00, 'AD-UB23-001', 'Incredible energy return with full-length Boost midsole technology. Primeknit+ upper adapts to your foot for a sock-like fit.', 'Running', 'new', 'approved', true),
  (seller, 'Adidas', 'Yeezy Boost 350 V2', 10, 24999.00, 'AD-YZ350-002', 'The iconic Yeezy Boost 350 V2 by Kanye West. Primeknit upper, full-length Boost cushioning, and signature translucent side stripe.', 'Limited Edition', 'new', 'approved', true),
  (seller, 'Adidas', 'Stan Smith', 8, 7999.00, 'AD-SS-003', 'The cleanest sneaker in the game. Smooth leather upper, perforated 3-Stripes, and timeless green heel tab.', 'Lifestyle', 'new', 'approved', false),
  (seller, 'Adidas', 'Superstar', 9, 8499.00, 'AD-SUP-004', 'The shell-toe legend. Full-grain leather upper with the signature rubber shell toe cap and serrated 3-Stripes.', 'Lifestyle', 'new', 'approved', false);

  -- New Balance
  INSERT INTO sneakers (seller_id, brand, model, size, price, serial_number, description, category, condition, status, featured) VALUES
  (seller, 'New Balance', '550', 9, 12999.00, 'NB-550-001', 'Retro basketball style reborn. Premium leather upper with perforated detail and vintage-inspired colorways.', 'Basketball', 'new', 'approved', true),
  (seller, 'New Balance', '990v6 Made in USA', 10, 22999.00, 'NB-990-002', 'Handcrafted in the USA. Premium pigskin suede and mesh upper with FuelCell cushioning.', 'Running', 'new', 'approved', true);

  -- Converse & Vans
  INSERT INTO sneakers (seller_id, brand, model, size, price, serial_number, description, category, condition, status, featured) VALUES
  (seller, 'Converse', 'Chuck Taylor All Star High', 8, 4999.00, 'CV-CT-001', 'The most iconic sneaker ever made. Canvas upper with vulcanized rubber sole and All Star ankle patch.', 'High Tops', 'new', 'approved', false),
  (seller, 'Vans', 'Old Skool', 8, 5499.00, 'VN-OS-001', 'The first Vans shoe to feature the iconic jazz stripe. Suede and canvas upper with signature waffle rubber outsole.', 'Skateboarding', 'new', 'approved', false);

  -- Puma & Reebok
  INSERT INTO sneakers (seller_id, brand, model, size, price, serial_number, description, category, condition, status, featured) VALUES
  (seller, 'Puma', 'RS-X Reinvention', 9, 10999.00, 'PM-RSX-001', 'Bold, chunky running style with RS cushioning technology. Mix of mesh and leather materials.', 'Running', 'new', 'approved', false),
  (seller, 'Reebok', 'Classic Leather', 9, 7999.00, 'RB-CL-001', 'Soft garment leather upper with die-cut EVA midsole and high-abrasion rubber outsole. Timeless since 1983.', 'Lifestyle', 'new', 'approved', false);

  -- Jordan & Asics
  INSERT INTO sneakers (seller_id, brand, model, size, price, serial_number, description, category, condition, status, featured) VALUES
  (seller, 'Jordan', 'Air Jordan 11 Retro', 9, 22499.00, 'JD-AJ11-001', 'The Space Jam shoe. Patent leather mudguard, carbon fiber spring plate, and full-length Air-Sole unit.', 'Basketball', 'new', 'approved', true),
  (seller, 'Asics', 'Gel-Kayano 30', 10, 15999.00, 'AS-GK30-001', 'Premium stability running shoe with FF BLAST PLUS cushioning and 4D Guidance System.', 'Running', 'new', 'approved', false);

END $$;

-- ============================================
-- INSERT SNEAKER IMAGES
-- ============================================

-- Nike Air Jordan 1
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ1-001' LIMIT 1), '/uploads/sneakers/67d71bfee112d.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ1-001' LIMIT 1), '/uploads/sneakers/67d71bfee1478.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ1-001' LIMIT 1), '/uploads/sneakers/67d71bfee165d.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ1-001' LIMIT 1), '/uploads/sneakers/67d71bfee33f6.jpg', 'bottom', 3);

-- Nike Air Jordan 4
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ4-002' LIMIT 1), '/uploads/sneakers/67d71e41328ac.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ4-002' LIMIT 1), '/uploads/sneakers/67d71e4132c00.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ4-002' LIMIT 1), '/uploads/sneakers/67d71e4132eea.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ4-002' LIMIT 1), '/uploads/sneakers/67d71e4134ce6.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AJ4-002' LIMIT 1), '/uploads/sneakers/67d71e4134f51.mp4');

-- Nike Air Force 1
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AF1-003' LIMIT 1), '/uploads/sneakers/67d9aae579aaf.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AF1-003' LIMIT 1), '/uploads/sneakers/67d9aae57b597.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AF1-003' LIMIT 1), '/uploads/sneakers/67d9aae57b811.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AF1-003' LIMIT 1), '/uploads/sneakers/67d9aae57cef7.png', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AF1-003' LIMIT 1), '/uploads/sneakers/67d9aae57d1f2.mp4');

-- Nike Air Max 90
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM90-004' LIMIT 1), '/uploads/sneakers/67d9ac697453c.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM90-004' LIMIT 1), '/uploads/sneakers/67d9ac697498d.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM90-004' LIMIT 1), '/uploads/sneakers/67d9ac6974c2c.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM90-004' LIMIT 1), '/uploads/sneakers/67d9ac6974e3c.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM90-004' LIMIT 1), '/uploads/sneakers/67d9ac6976d8d.mp4');

-- Nike Dunk Low
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-DL-005' LIMIT 1), '/uploads/sneakers/67d9ac89508fc.jpg', 'front', 0);

-- Nike Air Max 97
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM97-006' LIMIT 1), '/uploads/sneakers/67d9ad37ec92a.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM97-006' LIMIT 1), '/uploads/sneakers/67d9ad37ee48e.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM97-006' LIMIT 1), '/uploads/sneakers/67d9ad37ef826.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM97-006' LIMIT 1), '/uploads/sneakers/67d9ad37efa2f.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-AM97-006' LIMIT 1), '/uploads/sneakers/67d9ad37efbb1.mp4');

-- Adidas Ultraboost
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AD-UB23-001' LIMIT 1), '/uploads/sneakers/67d9b25f8b8b5.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'AD-UB23-001' LIMIT 1), '/uploads/sneakers/67d9b25f8d5c8.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'AD-UB23-001' LIMIT 1), '/uploads/sneakers/67d9b25f8d9a1.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'AD-UB23-001' LIMIT 1), '/uploads/sneakers/67d9b25f8db32.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AD-UB23-001' LIMIT 1), '/uploads/sneakers/67d9b25f8f11b.mp4');

-- Adidas Yeezy 350 V2
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AD-YZ350-002' LIMIT 1), '/uploads/sneakers/67d9b3589cc0d.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'AD-YZ350-002' LIMIT 1), '/uploads/sneakers/67d9b3589cf76.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'AD-YZ350-002' LIMIT 1), '/uploads/sneakers/67d9b3589d293.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'AD-YZ350-002' LIMIT 1), '/uploads/sneakers/67d9b3589d682.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AD-YZ350-002' LIMIT 1), '/uploads/sneakers/67d9b3589f3f4.mp4');

-- Adidas Stan Smith
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AD-SS-003' LIMIT 1), '/uploads/sneakers/67dc48ff2f380.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'AD-SS-003' LIMIT 1), '/uploads/sneakers/67dc48ff31346.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'AD-SS-003' LIMIT 1), '/uploads/sneakers/67dc48ff31614.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'AD-SS-003' LIMIT 1), '/uploads/sneakers/67dc48ff318b8.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AD-SS-003' LIMIT 1), '/uploads/sneakers/67dc48ff31b11.mp4');

-- Adidas Superstar
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AD-SUP-004' LIMIT 1), '/uploads/sneakers/67def07aaffe8.jpeg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'AD-SUP-004' LIMIT 1), '/uploads/sneakers/67def07ab089f.jpeg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'AD-SUP-004' LIMIT 1), '/uploads/sneakers/67def07ab0da7.jpeg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'AD-SUP-004' LIMIT 1), '/uploads/sneakers/67def07ab1326.jpeg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AD-SUP-004' LIMIT 1), '/uploads/sneakers/67def07ab190c.mp4');

-- New Balance 550
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NB-550-001' LIMIT 1), '/uploads/sneakers/67def2477d058.jpeg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'NB-550-001' LIMIT 1), '/uploads/sneakers/67def2477d748.jpeg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'NB-550-001' LIMIT 1), '/uploads/sneakers/67def24782143.jpeg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'NB-550-001' LIMIT 1), '/uploads/sneakers/67def247826d5.jpeg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NB-550-001' LIMIT 1), '/uploads/sneakers/67def24782c30.mp4');

-- New Balance 990v6
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NB-990-002' LIMIT 1), '/uploads/sneakers/67def33d3893a.jpeg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'NB-990-002' LIMIT 1), '/uploads/sneakers/67def33d3b29f.jpeg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'NB-990-002' LIMIT 1), '/uploads/sneakers/67def33d3bfdf.jpeg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'NB-990-002' LIMIT 1), '/uploads/sneakers/67def33d3c01e.jpeg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NB-990-002' LIMIT 1), '/uploads/sneakers/67def33d3c241.mp4');

-- Converse Chuck Taylor
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'CV-CT-001' LIMIT 1), '/uploads/sneakers/67def3f00d1be.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'CV-CT-001' LIMIT 1), '/uploads/sneakers/67def3f00d90f.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'CV-CT-001' LIMIT 1), '/uploads/sneakers/67def3f00dfa2.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'CV-CT-001' LIMIT 1), '/uploads/sneakers/67def3f00e08e.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'CV-CT-001' LIMIT 1), '/uploads/sneakers/67def3f0102b1.mp4');

-- Vans Old Skool
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'VN-OS-001' LIMIT 1), '/uploads/sneakers/67def4b872701.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'VN-OS-001' LIMIT 1), '/uploads/sneakers/67def4b872ea2.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'VN-OS-001' LIMIT 1), '/uploads/sneakers/67def4b873539.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'VN-OS-001' LIMIT 1), '/uploads/sneakers/67def4b87371c.jpg', 'bottom', 3);

-- Puma RS-X
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'PM-RSX-001' LIMIT 1), '/uploads/sneakers/67def52a10327.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'PM-RSX-001' LIMIT 1), '/uploads/sneakers/67def52a109b5.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'PM-RSX-001' LIMIT 1), '/uploads/sneakers/67def52a10dcf.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'PM-RSX-001' LIMIT 1), '/uploads/sneakers/67def52a10f1a.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'PM-RSX-001' LIMIT 1), '/uploads/sneakers/67def52a1345a.mp4');

-- Reebok Classic
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'RB-CL-001' LIMIT 1), '/uploads/sneakers/67def59689c2f.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'RB-CL-001' LIMIT 1), '/uploads/sneakers/67def5968bb7a.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'RB-CL-001' LIMIT 1), '/uploads/sneakers/67def5968bf08.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'RB-CL-001' LIMIT 1), '/uploads/sneakers/67def5968c0d2.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'RB-CL-001' LIMIT 1), '/uploads/sneakers/67def5968c9f1.mp4');

-- Jordan 11 Retro
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'JD-AJ11-001' LIMIT 1), '/uploads/sneakers/67def9e68e548.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'JD-AJ11-001' LIMIT 1), '/uploads/sneakers/67def9e68ecbd.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'JD-AJ11-001' LIMIT 1), '/uploads/sneakers/67def9e68f225.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'JD-AJ11-001' LIMIT 1), '/uploads/sneakers/67def9e68f411.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'JD-AJ11-001' LIMIT 1), '/uploads/sneakers/67def9e68ff85.mp4');

-- Nike SB Dunk High
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-SBDH-007' LIMIT 1), '/uploads/sneakers/67defa6c4a4e4.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'NK-SBDH-007' LIMIT 1), '/uploads/sneakers/67defa6c4beb9.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'NK-SBDH-007' LIMIT 1), '/uploads/sneakers/67defa6c4c7da.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'NK-SBDH-007' LIMIT 1), '/uploads/sneakers/67defa6c4ca37.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-SBDH-007' LIMIT 1), '/uploads/sneakers/67defa6c4e31b.mp4');

-- Asics Gel-Kayano 30
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AS-GK30-001' LIMIT 1), '/uploads/sneakers/67dfab8d28217.jpg', 'front', 0),
((SELECT id FROM sneakers WHERE serial_number = 'AS-GK30-001' LIMIT 1), '/uploads/sneakers/67dfab8d288fd.jpg', 'side', 1),
((SELECT id FROM sneakers WHERE serial_number = 'AS-GK30-001' LIMIT 1), '/uploads/sneakers/67dfab8d28d65.jpg', 'top', 2),
((SELECT id FROM sneakers WHERE serial_number = 'AS-GK30-001' LIMIT 1), '/uploads/sneakers/67dfab8d28ee3.jpg', 'bottom', 3);
INSERT INTO sneaker_videos (sneaker_id, video_url) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'AS-GK30-001' LIMIT 1), '/uploads/sneakers/67dfab8d29bc4.mp4');

-- Nike Blazer (no original images, use placeholder)
INSERT INTO sneaker_images (sneaker_id, image_url, image_type, display_order) VALUES
((SELECT id FROM sneakers WHERE serial_number = 'NK-BM77-008' LIMIT 1), 'https://placehold.co/600x400/2d2d2d/3498db?text=Nike+Blazer+Mid+77', 'front', 0);
