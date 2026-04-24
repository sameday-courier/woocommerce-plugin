<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Sql\Repository\Woo;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Application\Sql\Repository\AbstractRepository;

class WooOrderAddressRepository extends AbstractRepository
{
    private const TABLE_NAME = 'order_address';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @param int $oderId
     * @param array $address
     *
     * @return void
     */
    public function updateWcOrderAddress(int $oderId, array $address): void
    {
        $this->dbHandler->updateRow(
            $this->getTableName(),
            $address,
            [
                'order_id' => $oderId,
                'address_type' => 'shipping',
            ]
        );
    }
}
