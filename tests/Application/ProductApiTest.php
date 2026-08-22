<?php

namespace Tests\Application;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;

class ProductApiTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $baseUri = getenv('API_BASE_URL') ?: 'http://localhost:8000';

        $this->client = new Client([
            'base_uri' => $baseUri,
            'http_errors' => false,
            'timeout' => 5,
        ]);

        try {
            $this->client->get('/api/products');
        } catch (ConnectException $e) {
            $this->markTestSkipped(
                "API server not reachable at {$baseUri}"
            );
        }
    }

    public function testGetProducts(): void
    {
        $response = $this->client->get('/api/products');

        $this->assertEquals(
            200,
            $response->getStatusCode()
        );

        $data = json_decode(
            $response->getBody()->getContents(),
            true
        );

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }

    public function testGetExistingProduct(): void
    {
        $response = $this->client->get('/api/products/1');

        $this->assertEquals(
            200,
            $response->getStatusCode()
        );

        $data = json_decode(
            $response->getBody()->getContents(),
            true
        );

        $this->assertTrue($data['success']);
        $this->assertNotNull($data['data']);
    }

    public function testGetUnknownProduct(): void
    {
        $response = $this->client->get('/api/products/999999');

        $this->assertEquals(
            404,
            $response->getStatusCode()
        );

        $data = json_decode(
            $response->getBody()->getContents(),
            true
        );

        $this->assertFalse($data['success']);
    }
}