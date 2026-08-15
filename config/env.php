<?php
/**
 * Minimal .env loader — no external dependency.
 * Reads KEY=VALUE lines from /.env (if present) into getenv()/$_ENV.
 * Lines starting with # are ignored. Missing file is not an error:
 * the app simply falls back to hard-coded local defaults.
 */
function loadEnv(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");

        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

/** Read an env var with a fallback default. */
function env(string $key, $default = null)
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}
