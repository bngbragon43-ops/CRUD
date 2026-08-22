<?php

namespace App\Services;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('php-devops');
        $this->logger->pushHandler($this->resolveHandler());
    }

    private function resolveHandler(): HandlerInterface
    {
        $path = __DIR__ . '/../../logs/app.log';

        try {
            $dir = dirname($path);

            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                return new NullHandler();
            }

            $handle = @fopen($path, 'a');

            if ($handle === false) {
                return new NullHandler();
            }

            fclose($handle);

            return new StreamHandler($path, Logger::DEBUG);
        } catch (\Throwable $e) {
            return new NullHandler();
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->write(fn () => $this->logger->info($message, $context));
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write(fn () => $this->logger->warning($message, $context));
    }

    public function error(string $message, array $context = []): void
    {
        $this->write(fn () => $this->logger->error($message, $context));
    }

    private function write(callable $operation): void
    {
        try {
            $operation();
        } catch (\Throwable $e) {
        }
    }
}