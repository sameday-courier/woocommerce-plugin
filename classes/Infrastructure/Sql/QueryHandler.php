<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class QueryHandler
 */
class QueryHandler
{
    public static function updateWcOrderAddress(int $oderId, array $address): void
    {
        DbHandler::updateRow(
            DbHandler::buildTableName('wc_order_addresses'),
            $address,
            [
                'order_id' => $oderId,
                'address_type' => 'shipping',
            ]
        );
    }
}

