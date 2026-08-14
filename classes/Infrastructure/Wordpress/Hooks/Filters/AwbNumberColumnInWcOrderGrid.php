<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

final class AwbNumberColumnInWcOrderGrid extends AbstractFilter
{
    private const FILTER = 'manage_woocommerce_page_wc-orders_columns';
    public const COLUMN_KEY = 'sameday_awb_number';

    /**
     * @return string
     */
    public function getFilterName(): string
    {
        return self::FILTER;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 999;
    }

    /**
     * @return string[]|null
     */
    public function getParams(): ?array
    {
        return ['columns'];
    }

    /**
     * @param mixed ...$args
     *
     * @return array
     */
    public function handle(...$args): array
    {
        $columns = $args[0] ?? [];
        if (!is_array($columns)) {
            $columns = [];
        }

        unset($columns[self::COLUMN_KEY]);
        $columns[self::COLUMN_KEY] = TranslatorHandler::translate('Sameday Awb Number');

        return $columns;
    }
}
