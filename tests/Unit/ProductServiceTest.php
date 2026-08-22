<?php

namespace Tests\Unit;

use App\Services\ProductService;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductService $service;

    protected function setUp(): void
    {
        $this->service = new ProductService();
    }

    public function testCalculateTotal(): void
    {
        $result = $this->service->calculateTotal(
            10000,
            3
        );

        $this->assertEquals(30000, $result);
    }

    public function testProductIsAvailable(): void
    {
        $result = $this->service->isAvailable(10);

        $this->assertTrue($result);
    }

    public function testProductIsNotAvailable(): void
    {
        $result = $this->service->isAvailable(0);

        $this->assertFalse($result);
    }
}