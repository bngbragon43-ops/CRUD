<?php

namespace App\Services;

class ProductService
{
    public function calculateTotal(
        float $price,
        int $quantity
    ): float {
        return $price * $quantity;
    }

    public function isAvailable(int $quantity): bool
    {
        return $quantity > 0;
    }
}