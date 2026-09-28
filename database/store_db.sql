CREATE DATABASE IF NOT EXISTS store_db_baru;
USE store_db_baru;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  category VARCHAR(50) NOT NULL DEFAULT "Umum",
  price DECIMAL(12,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, category, price, stock) VALUES
('Kopi Robusta 250g', 'Minuman', 35000.00, 20),
('Buku Catatan Grid A5', 'Alat Tulis', 25000.00, 15);