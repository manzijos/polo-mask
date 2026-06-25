-- Database migration script for polo mask e-shopping
-- Create database and required tables with seed data.

CREATE DATABASE IF NOT EXISTS `polo_mask_e_shopping` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `polo_mask_e_shopping`;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','seller','customer') NOT NULL DEFAULT 'customer',
    location VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    photo_path VARCHAR(255) DEFAULT NULL,
    document_path VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO users (username, password_hash, role, location)
VALUES
('admin', '$2y$12$7HkzLFvf/8P1nMybywyfheMHNEUnDY3BxqCAqS8Vfu/SDksIreQXS', 'admin', 'Campus HQ'),
('seller01', '$2y$12$oc8uu5bDFBFY6d0sbR922eiejn9mnXdU3baug04AItaeHCWeUZz1W', 'seller', 'Department A'),
('customer01', '$2y$12$KrJPj.jmBbH3fY9dTaIzXCS3Ax/aWpEoXDjj1W', 'customer', 'Student Housing');

INSERT IGNORE INTO products (seller_id, title, description, price, photo_path, document_path, location)
VALUES
(2, 'Premium Campus Backpack', 'A smart, durable campus backpack with laptop protection and multiple organization pockets.', 59.99, 'assets/images/backpack.jpg', 'assets/docs/backpack-guide.pdf', 'University Bookstore'),
(2, 'Eco-Friendly Notebook Set', 'A premium notebook pack crafted for campus notes, lecture planning, and sustainable living.', 24.50, 'assets/images/notebook.jpg', 'assets/docs/notebook-specs.pdf', 'Campus Store');

INSERT IGNORE INTO comments (product_id, customer_id, comment_text, rating)
VALUES
(1, 3, 'Excellent build quality and plenty of compartments. Ideal for campus life.', 5),
(2, 3, 'The paper is smooth and the design feels high-end. Great value.', 4);
