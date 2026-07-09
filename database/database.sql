-- =============================================
-- FreshMart Online - Database Schema
-- MySQL 8.x
-- =============================================

CREATE DATABASE IF NOT EXISTS freshmart_db;
USE freshmart_db;

-- =============================================
-- 1. Tabel Users (Admin, Penjual, Pembeli)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'seller', 'buyer') NOT NULL DEFAULT 'buyer',
    phone VARCHAR(20),
    address TEXT,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 2. Tabel Categories
-- =============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 3. Tabel Products
-- =============================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    weight_kg DECIMAL(5,2) DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 4. Tabel Orders
-- =============================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    total_amount DECIMAL(12,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    shipping_address TEXT,
    status ENUM('pending', 'paid', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 5. Tabel Order Items
-- =============================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    seller_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 6. Tabel Payments
-- =============================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('transfer_bca', 'transfer_mandiri', 'transfer_bni', 'cod', 'ewallet') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    proof VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT DEFAULT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 7. Tabel Reviews
-- =============================================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    review_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 8. Tabel Notifications
-- =============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('order', 'payment', 'system', 'promo') DEFAULT 'system',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DATA AWAL: Kategori Produk Segar
-- =============================================
INSERT INTO categories (name, description, icon) VALUES
('Sayuran', 'Berbagai macam sayuran segar', '🥬'),
('Buah-buahan', 'Buah segar pilihan berkualitas', '🍎'),
('Daging & Ikan', 'Daging sapi, ayam, ikan segar', '🥩'),
('Bumbu & Rempah', 'Bumbu dapur dan rempah segar', '🧄'),
('Bahan Pokok', 'Beras, minyak, gula, dan kebutuhan pokok', '🍚'),
('Olahan Susu', 'Susu, yogurt, keju, dan produk olahan', '🥛'),
('Telur & Nabati', 'Telur segar dan protein nabati', '🥚'),
('Minuman Segar', 'Jus buah, minuman segar alami', '🧃');

-- =============================================
-- DATA AWAL: Admin Default
-- Password: admin123 (hashed dengan password_hash)
-- =============================================
INSERT INTO users (name, email, password, role, is_verified) VALUES
('Administrator', 'admin@freshmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- =============================================
-- DATA AWAL: Penjual Sample
-- =============================================
INSERT INTO users (name, email, password, role, phone, address, is_verified) VALUES
('Toko Segar Jaya', 'seller1@freshmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', '081234567890', 'Jakarta Selatan', 1),
('Pasar Buah Nusantara', 'seller2@freshmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', '081298765432', 'Bandung', 1);

-- =============================================
-- DATA AWAL: Pembeli Sample
-- =============================================
INSERT INTO users (name, email, password, role, phone, address, is_verified) VALUES
('Budi Santoso', 'buyer1@freshmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', '081355566677', 'Jl. Merdeka No. 10, Jakarta', 1);

-- =============================================
-- DATA AWAL: Produk Sample
-- =============================================
INSERT INTO products (seller_id, category_id, name, description, price, stock, weight_kg, image) VALUES
(2, 1, 'Tomat Merah Segar', 'Tomat merah segar pilihan dari petani lokal, kaya vitamin C.', 15000.00, 50, 1.00, NULL),
(2, 1, 'Bayam Organik', 'Bayam hijau organik segar, cocok untuk sayur bening dan tumis.', 8000.00, 30, 0.50, NULL),
(2, 2, 'Apel Fuji Premium', 'Apel Fuji import premium, manis dan renyah.', 35000.00, 25, 1.00, NULL),
(2, 2, 'Pisang Cavendish', 'Pisang Cavendish matang sempurna, manis alami.', 25000.00, 40, 1.00, NULL),
(2, 3, 'Daging Sapi Has Dalam', 'Daging sapi has dalam segar, potongan premium.', 120000.00, 15, 0.50, NULL),
(2, 3, 'Ayam Potong Segar', 'Ayam potong segar bersih, siap masak.', 35000.00, 20, 1.00, NULL),
(2, 4, 'Cabai Merah Keriting', 'Cabai merah keriting segar, level pedas sedang.', 35000.00, 25, 0.50, NULL),
(2, 5, 'Beras Premium 5kg', 'Beras premium kualitas terbaik, pulen dan wangi.', 75000.00, 30, 5.00, NULL);
