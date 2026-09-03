<?php

declare(strict_types=1);

/**
 * Minimal PSR-4 autoloader for plugin PHPStan custom rules.
 *
 * Loaded early via PHPStan --autoload-file (see phpstan/run.sh and .github/workflows/phpstan.yml).
 * We deliberately avoid requiring the WordPress-level tooling vendor/autoload.php here,
 * because that would re-run Composer "files" autoloads (e.g. phpstan/phpstan/bootstrap.php)
 * inside the already-booted PHPStan runtime.
 */

spl_autoload_register(static function (string $class): void {
    $prefix = 'SamedayCourier\\PHPStan\\';
    $prefixLength = strlen($prefix);

    if (strncmp($class, $prefix, $prefixLength) !== 0) {
        return;
    }

    $relativeClass = substr($class, $prefixLength);
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
