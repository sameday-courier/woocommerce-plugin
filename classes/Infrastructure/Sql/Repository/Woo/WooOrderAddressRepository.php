<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Woo;

use SamedayCourier\Shipping\Infrastructure\Sql\Repository\RepositoryInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

class WooOrderAddressRepository implements RepositoryInterface
{
    private const TABLE_NAME = 'order_address';
    public static function getTableName(): string
    {
        return DbHandler::buildTableName(self::TABLE_NAME);
    }

    public static function updateWcOrderAddress(int $oderId, array $address): void
    {
        DbHandler::updateRow(
            self::getTableName(),
            $address,
            [
                'order_id' => $oderId,
                'address_type' => 'shipping',
            ]
        );
    }
}
