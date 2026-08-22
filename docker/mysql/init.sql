CREATE DATABASE IF NOT EXISTS php_devops;

USE php_devops;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO products (name, price, quantity)
VALUES
    ('Laptop', 450000, 10),
    ('Smartphone', 150000, 25),
    ('Keyboard', 15000, 50);