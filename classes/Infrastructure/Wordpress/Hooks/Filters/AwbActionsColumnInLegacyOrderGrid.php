<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters;

/**
 * Adds the same "Awb actions" column to the legacy (posts-based) shop_order list.
 *
 * WooCommerce renders only one of the two list tables per request, so registering
 * both this filter and the HPOS one is safe.
 */
final class AwbActionsColumnInLegacyOrderGrid extends AwbActionsColumnInWcOrderGrid
{
    private const FILTER = 'manage_edit-shop_order_columns';

    /**
     * @return string
     */
    public function getFilterName(): string
    {
        return self::FILTER;
    }
}
