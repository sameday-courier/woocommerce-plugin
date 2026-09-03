<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

/**
 * Renders the Sameday brand icon as an embedded base64 data-URI.
 */
final class SamedayIcon
{
    private const DEFAULT_RELATIVE_PATH = 'assets/icon.png';

    /**
     * @var array<string, string>
     */
    private static array $dataUriCache = [];

    /**
     * @param string $relativePath
     *
     * @return string
     */
    public static function getDataUri(string $relativePath = self::DEFAULT_RELATIVE_PATH): string
    {
        if (isset(self::$dataUriCache[$relativePath])) {
            return self::$dataUriCache[$relativePath];
        }

        $absolutePath = PluginPathHandler::to($relativePath);
        if (!is_readable($absolutePath)) {
            self::$dataUriCache[$relativePath] = '';

            return '';
        }

        $binary = file_get_contents($absolutePath);
        if (false === $binary || '' === $binary) {
            self::$dataUriCache[$relativePath] = '';

            return '';
        }

        self::$dataUriCache[$relativePath] = 'data:image/png;base64,' . base64_encode($binary);

        return self::$dataUriCache[$relativePath];
    }

    /**
     * @param string $className
     * @param int $size
     *
     * @return string
     */
    /**
     * @param string $className
     * @param int $size
     *
     * @return string
     */
    public static function render(string $className = 'sameday-icon', int $size = 16): string
    {
        $dataUri = self::getDataUri();
        if ('' === $dataUri) {
            return '';
        }

        return sprintf(
            '<img class="%1$s" src="%2$s" alt="" width="%3$d" height="%3$d" decoding="async" />',
            $className,
            $dataUri,
            max(1, $size)
        );
    }
}
