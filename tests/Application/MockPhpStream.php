<?php

namespace Tests\Application;

class MockPhpStream
{
    public $context = '';
    private static string $content = '';

    public static function setContent(string $content): void
    {
        self::$content = $content;
    }

    public function stream_open(string $path, string $mode): bool
    {
        return true;
    }

    public function stream_read(int $count): string
    {
        $data = substr(self::$content, 0, $count);
        self::$content = substr(self::$content, $count);
        return $data;
    }

    public function stream_eof(): bool
    {
        return self::$content === '';
    }

    public function stream_stat(): array
    {
        return [];
    }
}
