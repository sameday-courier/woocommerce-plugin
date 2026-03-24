<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Woo;

use AbstractRepository;

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
