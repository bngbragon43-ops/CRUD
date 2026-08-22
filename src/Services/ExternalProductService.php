<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ExternalProductService
{
    private Client $client;

    public function __construct(?string $baseUri = null)
    {
        $this->client = new Client([
            'base_uri' => $baseUri ?? (getenv('MOCKSERVER_BASE_URL') ?: 'http://mockserver:1080'),
            'timeout' => 5,
        ]);
    }

    public function getProduct(int $id): array
    {
        try {
            $response = $this->client->get(
                "/external/products/{$id}"
            );

            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'data' => json_decode(
                    $response->getBody()->getContents(),
                    true
                )
            ];

        } catch (RequestException $e) {

            $status = $e->getResponse()
                ? $e->getResponse()->getStatusCode()
                : 500;

            return [
                'success' => false,
                'status' => $status,
                'message' => 'External service error'
            ];
        }
    }
}