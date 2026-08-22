<?php

namespace App\Controllers;

use App\Models\Product;
use App\Services\ProductService;

class ProductController
{
    public function __construct(
        private Product $product,
        private ProductService $productService
    ) {}

    public function index(): void
    {
        echo json_encode([
            'success' => true,
            'data' => $this->product->all(),
        ]);
    }

    public function show(int $id): void
    {
        $product = $this->product->find($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Product not found',
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function store(): void
    {
        $data = $this->getValidatedData();

        if (is_array($data) && isset($data['error'])) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => $data['error'],
            ]);
            return;
        }

        $id = $this->product->create($data['name'], $data['price'], $data['quantity']);
        $product = $this->product->find($id);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'data' => $product,
            'total' => $this->productService->calculateTotal($data['price'], $data['quantity']),
            'available' => $this->productService->isAvailable($data['quantity']),
        ]);
    }

    public function update(int $id): void
    {
        $existing = $this->product->find($id);

        if (!$existing) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Product not found',
            ]);
            return;
        }

        $data = $this->getValidatedData();

        if (is_array($data) && isset($data['error'])) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => $data['error'],
            ]);
            return;
        }

        $this->product->update($id, $data['name'], $data['price'], $data['quantity']);

        echo json_encode([
            'success' => true,
            'data' => $this->product->find($id),
            'total' => $this->productService->calculateTotal($data['price'], $data['quantity']),
            'available' => $this->productService->isAvailable($data['quantity']),
        ]);
    }

    public function destroy(int $id): void
    {
        $deleted = $this->product->delete($id);

        if (!$deleted) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Product not found',
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Product deleted',
        ]);
    }

    private function getValidatedData(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data) || empty($data['name']) || !isset($data['price'], $data['quantity'])) {
            return ['error' => 'Missing required fields: name, price, quantity'];
        }

        if (!is_string($data['name']) || !is_numeric($data['price']) || !is_numeric($data['quantity'])) {
            return ['error' => 'Invalid field types: name must be a string, price and quantity must be numeric'];
        }

        $name = trim($data['name']);
        $price = (float) $data['price'];
        $quantity = (int) $data['quantity'];

        if ($price < 0 || $quantity < 0) {
            return ['error' => 'Price and quantity must be positive'];
        }

        return [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }
}
