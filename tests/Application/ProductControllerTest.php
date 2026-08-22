<?php

namespace Tests\Application;

use App\Controllers\ProductController;
use App\Models\Product;
use App\Services\ProductService;
use PHPUnit\Framework\TestCase;

class ProductControllerTest extends TestCase
{
    private ProductController $controller;
    private Product $productMock;
    private ProductService $serviceMock;
    private bool $inputMocked = false;

    protected function setUp(): void
    {
        $this->productMock = $this->createMock(Product::class);
        $this->serviceMock = $this->createMock(ProductService::class);
        $this->controller = new ProductController($this->productMock, $this->serviceMock);
    }

    protected function tearDown(): void
    {
        $this->resetInput();
    }

    private function setInput(array $data): void
    {
        stream_wrapper_unregister('php');
        $mock = stream_wrapper_register('php', MockPhpStream::class);
        MockPhpStream::setContent(json_encode($data));
        $this->inputMocked = true;
    }

    private function resetInput(): void
    {
        if (!$this->inputMocked) {
            return;
        }

        stream_wrapper_unregister('php');
        stream_wrapper_restore('php');
        $this->inputMocked = false;
    }

    // ── index() ──

    public function testIndexReturnsAllProducts(): void
    {
        $products = [
            ['id' => 1, 'name' => 'Clavier', 'price' => '15000.00', 'quantity' => 10],
            ['id' => 2, 'name' => 'Souris', 'price' => '5000.00', 'quantity' => 25],
        ];

        $this->productMock->method('all')->willReturn($products);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertCount(2, $json['data']);
        $this->assertEquals('Clavier', $json['data'][0]['name']);
    }

    public function testIndexReturnsEmptyArray(): void
    {
        $this->productMock->method('all')->willReturn([]);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertEmpty($json['data']);
    }

    // ── show() ──

    public function testShowReturnsProductWhenFound(): void
    {
        $product = ['id' => 1, 'name' => 'Clavier', 'price' => '15000.00', 'quantity' => 10];
        $this->productMock->method('find')->with(1)->willReturn($product);

        ob_start();
        $this->controller->show(1);
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertEquals('Clavier', $json['data']['name']);
    }

    public function testShowReturns404WhenNotFound(): void
    {
        $this->productMock->method('find')->with(999)->willReturn(null);

        ob_start();
        $this->controller->show(999);
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals('Product not found', $json['message']);
        $this->assertEquals(404, http_response_code());
    }

    // ── store() ──

    public function testStoreCreatesProductSuccessfully(): void
    {
        $this->setInput(['name' => 'Ecran', 'price' => 50000, 'quantity' => 3]);

        $createdProduct = ['id' => 1, 'name' => 'Ecran', 'price' => '50000.00', 'quantity' => 3];
        $this->productMock->expects($this->once())
            ->method('create')
            ->with('Ecran', 50000.0, 3)
            ->willReturn(1);
        $this->productMock->method('find')->with(1)->willReturn($createdProduct);

        $this->serviceMock->method('calculateTotal')->with(50000.0, 3)->willReturn(150000.0);
        $this->serviceMock->method('isAvailable')->with(3)->willReturn(true);

        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $this->resetInput();
        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertEquals('Ecran', $json['data']['name']);
        $this->assertEquals(150000.0, $json['total']);
        $this->assertTrue($json['available']);
    }

    public function testStoreReturns422WhenMissingFields(): void
    {
        $this->setInput(['name' => 'Ecran']);

        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $this->resetInput();
        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals(422, http_response_code());
    }

    public function testStoreReturns422WhenNameIsEmpty(): void
    {
        $this->setInput(['name' => '', 'price' => 100, 'quantity' => 5]);

        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $this->resetInput();
        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals(422, http_response_code());
    }

    public function testStoreReturns422WhenPriceIsNegative(): void
    {
        $this->setInput(['name' => 'Ecran', 'price' => -100, 'quantity' => 5]);

        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $this->resetInput();
        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals(422, http_response_code());
    }

    public function testStoreReturns422WhenQuantityIsNegative(): void
    {
        $this->setInput(['name' => 'Ecran', 'price' => 100, 'quantity' => -1]);

        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $this->resetInput();
        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals(422, http_response_code());
    }

    public function testStoreTrimsNameWhitespace(): void
    {
        $this->setInput(['name' => '  Ecran  ', 'price' => 50000, 'quantity' => 3]);

        $this->productMock->expects($this->once())
            ->method('create')
            ->with('Ecran', 50000.0, 3)
            ->willReturn(1);
        $this->productMock->method('find')->willReturn([]);

        $this->serviceMock->method('calculateTotal')->willReturn(150000.0);
        $this->serviceMock->method('isAvailable')->willReturn(true);

        ob_start();
        $this->controller->store();
        ob_get_clean();

        $this->resetInput();
    }

    // ── update() ──

    public function testUpdateModifiesProductSuccessfully(): void
    {
        $existing = ['id' => 1, 'name' => 'Clavier', 'price' => '15000.00', 'quantity' => 10];
        $updated = ['id' => 1, 'name' => 'Clavier Mecanique', 'price' => '20000.00', 'quantity' => 5];

        $callCount = 0;
        $this->productMock->method('find')->willReturnCallback(function (int $id) use (&$callCount, $existing, $updated) {
            $callCount++;
            return $callCount === 1 ? $existing : $updated;
        });

        $this->productMock->expects($this->once())
            ->method('update')
            ->with(1, 'Clavier Mecanique', 20000.0, 5)
            ->willReturn(true);

        $this->serviceMock->method('calculateTotal')->with(20000.0, 5)->willReturn(100000.0);
        $this->serviceMock->method('isAvailable')->with(5)->willReturn(true);

        $this->setInput(['name' => 'Clavier Mecanique', 'price' => 20000, 'quantity' => 5]);

        ob_start();
        $this->controller->update(1);
        $output = ob_get_clean();

        $this->resetInput();
        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertEquals('Clavier Mecanique', $json['data']['name']);
        $this->assertEquals(100000.0, $json['total']);
    }

    public function testUpdateReturns404WhenProductNotFound(): void
    {
        $this->productMock->method('find')->with(999)->willReturn(null);

        ob_start();
        $this->controller->update(999);
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals('Product not found', $json['message']);
        $this->assertEquals(404, http_response_code());
    }

    public function testUpdateReturns422WhenMissingFields(): void
    {
        $this->productMock->method('find')->willReturn(['id' => 1]);
        $this->setInput(['name' => '']);

        ob_start();
        $this->controller->update(1);
        $output = ob_get_clean();

        $this->resetInput();
        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals(422, http_response_code());
    }

    // ── destroy() ──

    public function testDestroyDeletesProductSuccessfully(): void
    {
        $this->productMock->method('delete')->with(1)->willReturn(true);

        ob_start();
        $this->controller->destroy(1);
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertEquals('Product deleted', $json['message']);
    }

    public function testDestroyReturns404WhenProductNotFound(): void
    {
        $this->productMock->method('delete')->with(999)->willReturn(false);

        ob_start();
        $this->controller->destroy(999);
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals('Product not found', $json['message']);
        $this->assertEquals(404, http_response_code());
    }

    // ── store() with zero quantity ──

    public function testStoreWithZeroQuantityMarkedUnavailable(): void
    {
        $this->setInput(['name' => 'Clavier', 'price' => 100, 'quantity' => 0]);

        $this->productMock->method('create')->willReturn(1);
        $this->productMock->method('find')->willReturn([
            'id' => 1, 'name' => 'Clavier', 'price' => '100.00', 'quantity' => 0,
        ]);

        $this->serviceMock->method('calculateTotal')->willReturn(0.0);
        $this->serviceMock->method('isAvailable')->with(0)->willReturn(false);

        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $this->resetInput();
        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertFalse($json['available']);
    }
}
