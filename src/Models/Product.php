<?php

namespace App\Models;

use PDO;

class Product
{
    public function __construct(private PDO $db)
    {
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
        ]);

        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function create(
        string $name,
        float $price,
        int $quantity
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO products (name, price, quantity) VALUES (:name, :price, :quantity)"
        );
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(
        int $id,
        string $name,
        float $price,
        int $quantity
    ): bool {
        $stmt = $this->db->prepare(
            "UPDATE products SET name = :name, price = :price, quantity = :quantity WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM products WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }
}
