<?php

$url = $argv[1] ?? '';
$timeoutSeconds = (int) ($argv[2] ?? 30);

if ($url === '') {
    fwrite(STDERR, "Usage: php wait_for_http.php <url> [timeout-seconds]\n");
    exit(2);
}

$deadline = time() + $timeoutSeconds;
$context = stream_context_create([
    'http' => ['timeout' => 2, 'ignore_errors' => true],
]);

while (time() < $deadline) {
    $headers = @get_headers($url, false, $context);

    if ($headers !== false && str_contains((string) $headers[0], '200')) {
        echo "Ready: {$url}\n";
        exit(0);
    }

    sleep(1);
}

fwrite(STDERR, "Timeout after {$timeoutSeconds}s waiting for {$url}\n");
exit(1);
