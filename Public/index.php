<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database\Database;
use App\Models\Product;
use App\Services\LoggerService;
use App\Services\ProductService;
use App\Controllers\ProductController;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$logger = new LoggerService();

header('Content-Type: application/json');

try {
    $database = new Database();
    $productModel = new Product($database->getConnection());
    $productService = new ProductService();
    $productController = new ProductController($productModel, $productService);

    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    if (!is_string($uri) || $uri === '') {
        $uri = '/';
    }

    if ($method === 'GET' && $uri === '/api/products') {
        $productController->index();
    } elseif (
        $method === 'GET' &&
        preg_match('#^/api/products/(\d+)$#', $uri, $matches)
    ) {
        $productController->show((int) $matches[1]);
    } elseif ($method === 'POST' && $uri === '/api/products') {
        $productController->store();
    } elseif (
        $method === 'PUT' &&
        preg_match('#^/api/products/(\d+)$#', $uri, $matches)
    ) {
        $productController->update((int) $matches[1]);
    } elseif (
        $method === 'DELETE' &&
        preg_match('#^/api/products/(\d+)$#', $uri, $matches)
    ) {
        $productController->destroy((int) $matches[1]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Route not found',
        ]);
    }
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
    ]);
}
