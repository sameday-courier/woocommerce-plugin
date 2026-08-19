<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Common\Services;

use RuntimeException;

/**
 * Renders PHP HTML views from classes/files/html.
 *
 * Params become local variables in the template (WooCommerce-style extract + include).
 * Templates may call HtmlHandler::buildHtml() to include partials.
 */
final class HtmlHandler
{
    private const FILE_EXT = 'php';

    /**
     * @param string $htmlFileName
     * @param array<string, mixed> $params
     *
     * @return string
     */
    /**
     * @param string $htmlFileName
     * @param array $params
     *
     * @return string
     */
    public static function buildHtml(string $htmlFileName, array $params = []): string
    {
        $templatePath = self::resolveTemplatePath($htmlFileName);

        /**
         * @param string $samedayTemplatePath
         * @param array $samedayTemplateData
         *
         * @return string
         */
        $render = static function (string $samedayTemplatePath, array $samedayTemplateData): string {
            extract($samedayTemplateData, EXTR_SKIP);

            ob_start();
            include $samedayTemplatePath;
            $html = ob_get_clean();

            return false === $html ? '' : $html;
        };

        return $render($templatePath, $params);
    }

    /**
     * @param string $htmlFileName
     *
     * @return string
     */
    /**
     * @param string $htmlFileName
     *
     * @return string
     */
    private static function resolveTemplatePath(string $htmlFileName): string
    {
        $fileName = str_replace(['.php', '.html'], '', $htmlFileName) . '.' . self::FILE_EXT;
        $filePath = SAMEDAYCOURIER_SHIPPING_PLUGIN_PATH . 'classes/files/html/' . $fileName;

        if (!is_readable($filePath)) {
            throw new RuntimeException('HTML template not found: ' . $filePath);
        }

        return $filePath;
    }
}
