<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Woo;

if (!defined('ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\AbstractRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use WC_Order;

class WooOrderAddressRepository extends AbstractRepository
{
    private const TABLE_NAME = 'wc_order_addresses';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @param int $orderId
     * @param array<string, string> $address
     *
     * @return bool
     */
    public function updateShippingAddress(int $orderId, array $address): bool
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return false;
        }

        $fieldMap = [
            'first_name' => 'set_shipping_first_name',
            'last_name' => 'set_shipping_last_name',
            'company' => 'set_shipping_company',
            'address_1' => 'set_shipping_address_1',
            'address_2' => 'set_shipping_address_2',
            'city' => 'set_shipping_city',
            'state' => 'set_shipping_state',
            'postcode' => 'set_shipping_postcode',
            'country' => 'set_shipping_country',
            'phone' => 'set_shipping_phone',
        ];

        foreach ($fieldMap as $field => $setter) {
            if (!array_key_exists($field, $address)) {
                continue;
            }

            $order->{$setter}(InputSanitizer::sanitizeInput($address[$field]));
        }

        $order->save();

        return true;
    }
}
