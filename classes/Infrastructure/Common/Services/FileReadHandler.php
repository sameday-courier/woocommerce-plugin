<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Common\Services;

use Exception;
use JsonException;
use RuntimeException;
use stdClass;

class FileReadHandler
{
    /**
     * @param string $fileName
     *
     * @return stdClass[]|null
     */
    /**
     * @param string $fileName
     *
     * @return ?array
     */
    public static function readJsonFile(string $fileName): ?array
    {
        $fileExt = "json";
        $fileName = str_replace('.json', '', $fileName) . "." . $fileExt;
        $fileContent = self::readFile($fileName);
        if (null === $fileContent) {
            return null;
        }

        try {
            $data = json_decode($fileContent, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return null;
        }

        return $data;
    }

    /**
     * @param string $fileName
     *
     * @return null|string
     */
    /**
     * @param string $fileName
     *
     * @return ?string
     */
    private static function readFile(string $fileName): ?string
    {
        try {
            $contents = file_get_contents(self::buildFilePath($fileName));
        } catch (Exception $exception) {
            return null;
        }

        return $contents;
    }

    /**
     * @param string $fileName
     * @return void
     */
    /**
     * @param string $fileName
     *
     * @return string
     */
    private static function buildFilePath(string $fileName): string
    {
        $filePath = SAMEDAYCOURIER_SHIPPING_PLUGIN_PATH . 'classes/files/' . $fileName;

        if (!file_exists($filePath)) {
            throw new RuntimeException("File not found: " . $filePath);
        }

        return $filePath;
    }
}
