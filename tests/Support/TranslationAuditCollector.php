<?php

namespace FluxErp\Tests\Support;

class TranslationAuditCollector
{
    private static array $missingKeys = [];

    private static array $ignoredKeys = [];

    private static array $ignoredPatterns = [];

    private static string $locale = '';

    private static bool $shutdownRegistered = false;

    public static function record(string $key): void
    {
        if (isset(self::$ignoredKeys[$key])) {
            return;
        }

        foreach (self::$ignoredPatterns as $pattern) {
            if (str_starts_with($key, $pattern)) {
                return;
            }
        }

        self::$missingKeys[$key] = true;
    }

    public static function writeReport(): void
    {
        if (! self::$locale) {
            return;
        }

        $keys = array_keys(self::$missingKeys);
        sort($keys);

        $report = json_encode([
            'locale' => self::$locale,
            'type' => 'runtime',
            'missing_count' => count($keys),
            'missing_keys' => $keys,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $outputFile = dirname(__DIR__, 2) . '/missing-translations-runtime-' . self::$locale . '.json';
        file_put_contents($outputFile, $report);

        fwrite(STDERR, "\n\n=== Translation Audit (Runtime): " . self::$locale . " ===\n");
        fwrite(STDERR, 'Missing keys: ' . count($keys) . "\n");
        fwrite(STDERR, "Report: {$outputFile}\n");

        if (count($keys) > 0 && count($keys) <= 100) {
            fwrite(STDERR, "\nKeys:\n");
            foreach ($keys as $k) {
                fwrite(STDERR, "  - {$k}\n");
            }
        }

        fwrite(STDERR, "\n");
    }

    private static function loadIgnoreList(string $locale): void
    {
        $ignoreFile = dirname(__DIR__, 2) . '/lang/' . $locale . '.translation-ignore';

        if (! file_exists($ignoreFile)) {
            return;
        }

        $lines = file($ignoreFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_ends_with($line, '*')) {
                self::$ignoredPatterns[] = substr($line, 0, -1);
            } else {
                self::$ignoredKeys[$line] = true;
            }
        }
    }

    public static function boot(string $locale): void
    {
        self::$locale = $locale;
        self::loadIgnoreList($locale);

        if (! self::$shutdownRegistered) {
            self::$shutdownRegistered = true;
            register_shutdown_function([self::class, 'writeReport']);
        }
    }
}
