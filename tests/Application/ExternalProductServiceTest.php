<?php

namespace Tests\Application;

use App\Services\ExternalProductService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;

class ExternalProductServiceTest extends TestCase
{
    private function resolveBaseUri(): string
    {
        return getenv('MOCKSERVER_BASE_URL') ?: 'http://mockserver:1080';
    }

    protected function setUp(): void
    {
        $client = new Client([
            'base_uri' => $this->resolveBaseUri(),
            'http_errors' => false,
            'timeout' => 2,
        ]);

        try {
            $client->get('/mockserver/status');
        } catch (ConnectException $e) {
            $this->markTestSkipped(
                "MockServer not reachable at {$this->resolveBaseUri()}"
            );
        }
    }

    public function testGetProductSuccessfully(): void
    {
        $service = new ExternalProductService();

        $result = $service->getProduct(1);

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status']);
        $this->assertEquals(
            'Laptop',
            $result['data']['name']
        );
    }
}
