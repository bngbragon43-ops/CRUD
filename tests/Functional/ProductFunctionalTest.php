<?php

namespace Tests\Functional;

use App\Database\Database;
use App\Models\Product;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class ProductFunctionalTest extends TestCase
{
    private Product $product;
    private ?int $createdId = null;

    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        $database = new Database();

        $this->product = new Product(
            $database->getConnection()
        );
    }

    protected function tearDown(): void
    {
        if ($this->createdId !== null) {
            $this->product->delete($this->createdId);
            $this->createdId = null;
        }
    }

    public function testCreateAndFindProduct(): void
    {
        $id = $this->product->create(
            'Test Product',
            25000,
            5
        );

        $this->createdId = $id;

        $result = $this->product->find($id);

        $this->assertNotNull($result);

        $this->assertEquals(
            'Test Product',
            $result['name']
        );

        $this->assertEquals(
            '25000.00',
            $result['price']
        );

        $this->assertEquals(
            5,
            (int) $result['quantity']
        );
    }
}