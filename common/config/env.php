<?php

$envFile = __DIR__ . '/../../.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comment lines
        if (strpos(ltrim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($key, $value) = array_map('trim', explode('=', $line, 2));
            putenv("$key=$value");
        }
    }
} else {
    // .env not found — fall back to runtime environment variables (e.g. Docker / OS env).
    // Values already set via putenv/getenv from the host are still available; no action needed.
    error_log('[ILDIS] WARNING: .env file not found at ' . $envFile . '. Falling back to runtime environment variables.');
}
