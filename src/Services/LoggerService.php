<?php

namespace App\Services;

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('php-devops');

        try {
            $handler = new StreamHandler(
                __DIR__ . '/../../logs/app.log',
                Logger::DEBUG
            );
            $this->logger->pushHandler($handler);
        } catch (\Throwable $e) {
            $this->logger->pushHandler(new NullHandler());
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
}