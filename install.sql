CREATE DATABASE IF NOT EXISTS lienquan_store
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lienquan_store;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  heroes_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  skins_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  rank_name VARCHAR(80) NOT NULL DEFAULT 'Chưa cập nhật',
  price BIGINT UNSIGNED NOT NULL,
  image_url VARCHAR(500) DEFAULT NULL,
  status ENUM('available','reserved','sold') NOT NULL DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_products_status (status),
  INDEX idx_products_price (price)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(32) NOT NULL UNIQUE,
  product_id INT UNSIGNED NOT NULL,
  buyer_name VARCHAR(100) NOT NULL,
  buyer_email VARCHAR(190) NOT NULL,
  buyer_phone VARCHAR(30) NOT NULL,
  total_amount BIGINT UNSIGNED NOT NULL,
  transfer_note VARCHAR(100) NOT NULL,
  status ENUM('pending','paid','delivered','cancelled') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

INSERT INTO products (title, description, heroes_count, skins_count, rank_name, price, image_url, status)
SELECT 'Tài khoản mẫu • 85 tướng / 120 skin',
       'Đây là sản phẩm minh họa. Hãy thay bằng thông tin và hình ảnh tài khoản mà bạn có quyền chuyển nhượng.',
       85, 120, 'Cao Thủ', 1500000, NULL, 'available'
WHERE NOT EXISTS (SELECT 1 FROM products);
