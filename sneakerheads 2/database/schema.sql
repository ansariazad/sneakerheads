-- Create database
CREATE DATABASE IF NOT EXISTS sneakerheads;
USE sneakerheads;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    user_type ENUM('superadmin', 'seller_buyer', 'buyer') NOT NULL DEFAULT 'buyer',
    profile_image VARCHAR(255) DEFAULT 'default.jpg',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Addresses table
CREATE TABLE IF NOT EXISTS addresses (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'India',
    is_default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Sneakers table
CREATE TABLE IF NOT EXISTS sneakers (
    sneaker_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    size FLOAT NOT NULL,
    serial_number VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'sold') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Sneaker images table
CREATE TABLE IF NOT EXISTS sneaker_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    sneaker_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_type ENUM('top', 'bottom', 'side', 'front', 'other') NOT NULL,
    FOREIGN KEY (sneaker_id) REFERENCES sneakers(sneaker_id) ON DELETE CASCADE
);

-- Sneaker videos table
CREATE TABLE IF NOT EXISTS sneaker_videos (
    video_id INT AUTO_INCREMENT PRIMARY KEY,
    sneaker_id INT NOT NULL,
    video_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (sneaker_id) REFERENCES sneakers(sneaker_id) ON DELETE CASCADE
);

-- Purchase bills table
CREATE TABLE IF NOT EXISTS purchase_bills (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    sneaker_id INT NOT NULL,
    bill_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (sneaker_id) REFERENCES sneakers(sneaker_id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sneaker_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (sneaker_id) REFERENCES sneakers(sneaker_id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, sneaker_id)
);

-- Wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sneaker_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (sneaker_id) REFERENCES sneakers(sneaker_id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, sneaker_id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('upi', 'cod') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    order_status ENUM('placed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'placed',
    tracking_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_eta DATE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (address_id) REFERENCES addresses(address_id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    sneaker_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (sneaker_id) REFERENCES sneakers(sneaker_id)
);

-- Payments table (for seller payments)
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    order_item_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    platform_fee DECIMAL(10, 2) NOT NULL,
    net_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('requested', 'processing', 'completed', 'rejected') DEFAULT 'requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(item_id)
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Add password_resets table
CREATE TABLE IF NOT EXISTS password_resets (
  user_id INT NOT NULL,
  token VARCHAR(64) NOT NULL,
  expiry DATETIME NOT NULL,
  PRIMARY KEY (token),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Add bank_details table for payment information
CREATE TABLE IF NOT EXISTS bank_details (
  detail_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  account_holder_name VARCHAR(100),
  account_number VARCHAR(50),
  ifsc_code VARCHAR(20),
  bank_name VARCHAR(100),
  branch_name VARCHAR(100),
  upi_id VARCHAR(50),
  is_default BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert default superadmin
INSERT INTO users (username, email, password, full_name, user_type)
VALUES ('admin', 'admin@sneakerheads.com', '$2y$10$8KzS.AuXKAcH1bxzXQvuO.iiEBRdcK7C/d5mZBNOq0xHHhJsXUeZi', 'Super Admin', 'superadmin');
-- Password is 'admin123' (hashed with bcrypt)