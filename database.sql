-- E-Commerce project: database schema + sample data
DROP DATABASE IF EXISTS ecommerce_php;

CREATE DATABASE ecommerce_php CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ecommerce_php;

CREATE TABLE
    users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NULL,
        role ENUM ('customer', 'admin') NOT NULL DEFAULT 'customer',
        status ENUM ('active', 'blocked') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB;

CREATE TABLE
    categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(120) NOT NULL UNIQUE,
        description TEXT NULL,
        image_url VARCHAR(255) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB;

CREATE TABLE
    coupons (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        discount_type ENUM ('percent', 'fixed') NOT NULL,
        discount_value DECIMAL(10, 2) NOT NULL,
        min_order DECIMAL(10, 2) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        is_active TINYINT (1) NOT NULL DEFAULT 1
    ) ENGINE = InnoDB;

CREATE TABLE
    products (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id INT UNSIGNED NOT NULL,
        name VARCHAR(150) NOT NULL,
        sku VARCHAR(50) NOT NULL UNIQUE,
        slug VARCHAR(170) NOT NULL UNIQUE,
        description TEXT NULL,
        price DECIMAL(10, 2) NOT NULL,
        discount_price DECIMAL(10, 2) NULL,
        stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
        image_url VARCHAR(255) NULL,
        is_featured TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATETIME NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB;

CREATE TABLE
    cart_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id)  ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products (id)  ON DELETE CASCADE,
        UNIQUE (user_id, product_id)
    ) ENGINE = InnoDB;

    CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    coupon_code VARCHAR(50) NULL,
    shipping_address TEXT NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    payment_method ENUM('cod', 'card') NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX (status)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (user_id,  product_id),
    CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    label VARCHAR(50) NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;



-- ===== Sample data =====

-- Admin account (password: Admin@123)
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Store Admin', 'admin@store.com', '$2y$10$B5nq75qX.xd7TMiauhdgiObtHxT0KyM3WiXD1g.mL9s4SVVxT1JrS', 'admin');

-- Categories (they get ids 1-4 in this order)
INSERT INTO categories (name, slug, description) VALUES
('Electronics', 'electronics', 'Keyboards, mice and gadgets'),
('Audio', 'audio', 'Headphones and speakers'),
('Workspace', 'workspace', 'Docks, hubs and desk setup'),
('Accessories', 'accessories', 'Stands, sleeves and cables');

-- Products
INSERT INTO products (category_id, name, sku, slug, description, price, discount_price, stock_qty, is_featured) VALUES
(1, 'Wireless Mechanical Keyboard', 'KB-001', 'wireless-mechanical-keyboard', 'Compact wireless keyboard with mechanical switches.', 89.00, NULL, 25, 1),
(2, 'Noise-Canceling Headphones', 'HP-001', 'noise-canceling-headphones', 'Over-ear headphones with active noise canceling.', 149.00, 129.00, 15, 1),
(3, '4K USB-C Docking Station', 'DK-001', '4k-usb-c-docking-station', 'One cable for display, power and USB.', 119.00, NULL, 3, 1),
(4, 'Ergonomic Aluminum Stand', 'ST-001', 'ergonomic-aluminum-stand', 'Adjustable laptop stand.', 45.00, NULL, 40, 0);

-- Coupon
INSERT INTO coupons (code, discount_type, discount_value, min_order, expires_at) VALUES
('SAVE10', 'percent', 10, 50, '2027-12-31 23:59:59');