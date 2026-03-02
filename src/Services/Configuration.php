<?php

declare(strict_types=1);

namespace App\Services;

final class Configuration
{
    private static ?array $resolved = null;

    public static function all(): array
    {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        $local = self::loadLocal();
        $remote = self::loadRemote();

        // Local overrides remote for matching keys.
        self::$resolved = array_replace_recursive($remote, $local);
        return self::$resolved;
    }

    public static function get(string $path, mixed $default = null): mixed
    {
        $config = self::all();
        $segments = explode('.', $path);
        $cursor = $config;

        foreach ($segments as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    public static function refresh(): array
    {
        self::$resolved = null;
        return self::all();
    }

    private static function loadLocal(): array
    {
        $file = __DIR__ . '/../../config/app.php';
        $config = require $file;
        return is_array($config) ? $config : [];
    }

    private static function loadRemote(): array
    {
        if (!class_exists('\\Kislay\\Config\\ConfigClient')) {
            return [];
        }

        try {
            $client = new \Kislay\Config\ConfigClient();
            $payload = $client->all();
            if (!is_array($payload) || $payload === []) {
                return [];
            }
            return self::normalizeRemote($payload);
        } catch (\Throwable) {
            return [];
        }
    }

    private static function normalizeRemote(array $payload): array
    {
        $normalized = [];

        foreach ($payload as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $parsed = self::parseRemoteValue($key, $value);

            if (str_contains($key, '.')) {
                self::setDot($normalized, $key, $parsed);
                continue;
            }

            if (is_array($parsed) && array_is_list($parsed) === false) {
                $existing = $normalized[$key] ?? [];
                if (is_array($existing)) {
                    $normalized[$key] = array_replace_recursive($existing, $parsed);
                } else {
                    $normalized[$key] = $parsed;
                }
            } else {
                $normalized[$key] = $parsed;
            }
        }

        return $normalized;
    }

    private static function setDot(array &$target, string $path, mixed $value): void
    {
        $segments = explode('.', $path);
        $ref = &$target;

        foreach ($segments as $index => $segment) {
            if ($segment === '') {
                return;
            }

            $last = ($index === count($segments) - 1);
            if ($last) {
                $ref[$segment] = $value;
                return;
            }

            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }

            $ref = &$ref[$segment];
        }
    }

    private static function parseRemoteValue(string $key, mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        if (self::shouldPreserveStringValue($key)) {
            return $value;
        }

        $lower = strtolower($trimmed);
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }
        if ($lower === 'null') {
            return null;
        }

        if (preg_match('/^-?[0-9]+$/', $trimmed) === 1) {
            return (int) $trimmed;
        }

        if (preg_match('/^-?[0-9]+\.[0-9]+$/', $trimmed) === 1) {
            return (float) $trimmed;
        }

        $first = $trimmed[0];
        if ($first === '{' || $first === '[') {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    private static function shouldPreserveStringValue(string $key): bool
    {
        $normalized = strtolower($key);
        $secretNeedles = [
            'password',
            'passwd',
            'secret',
            'token',
            'apikey',
            'api_key',
            'private',
            'privatekey',
            'private_key',
            'clientsecret',
            'client_secret',
            'accesskey',
            'access_key',
            'cert',
            'pem',
        ];

        foreach ($secretNeedles as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }
}
