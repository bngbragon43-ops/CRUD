<?php

$url = $argv[1] ?? '';
$timeoutSeconds = (int) ($argv[2] ?? 30);

if ($url === '') {
    fwrite(STDERR, "Usage: php wait_for_http.php <url> [timeout-seconds]\n");
    exit(2);
}

$deadline = time() + $timeoutSeconds;
$lastError = null;
$context = stream_context_create([
    'http' => ['timeout' => 2, 'ignore_errors' => true],
]);

while (time() < $deadline) {
    $headers = @get_headers($url, false, $context);

    if ($headers !== false && str_contains((string) $headers[0], '200')) {
        echo "Ready: {$url}\n";
        exit(0);
    }

    if (is_array($headers)) {
        $lastError = 'HTTP ' . trim((string) $headers[0]);
    }

    sleep(1);
}

fwrite(STDERR, "Timeout after {$timeoutSeconds}s waiting for {$url}\n");

if ($lastError !== null) {
    fwrite(STDERR, "Last response: {$lastError}\n");
}

$e = error_get_last();
if ($e !== null && isset($e['message'])) {
    fwrite(STDERR, 'Last error: ' . trim($e['message']) . "\n");
}

exit(1);
