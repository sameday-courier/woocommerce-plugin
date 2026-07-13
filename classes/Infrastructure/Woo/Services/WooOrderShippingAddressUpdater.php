<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Application\Sql\Repository\Woo\WooOrderAddressRepository;

final class WooOrderShippingAddressUpdater
{
    /**
     * @var WooOrderAddressRepository $wooOrderAddressRepository
     */
    private WooOrderAddressRepository $wooOrderAddressRepository;

    /**
     * @param WooOrderAddressRepository $wooOrderAddressRepository
     */
    public function __construct(
        WooOrderAddressRepository $wooOrderAddressRepository
    )
    {
        $this->wooOrderAddressRepository = $wooOrderAddressRepository;
    }

    /**
     * @param int $orderId
     * @param string $address1
     * @param string $address2
     * @param string $indexName
     * @param string $city
     * @param string $state
     * @param string $postalCode
     * @param string $country
     *
     * @return void
     */
    public function update(
        int $orderId,
        string $address1,
        string $address2,
        string $indexName,
        string $city,
        string $state,
        string $postalCode,
        string $country
    ): void {
        $address1 = str_replace('"', '', InputSanitizer::sanitizeInput($address1));
        $address2 = str_replace('"', '', InputSanitizer::sanitizeInput($address2));

        $addressFieldsMapper = [
            '_shipping_address_1' => $address1,
            '_shipping_address_2' => $address2,
            '_shipping_city' => $city,
            '_shipping_state' => $state,
            '_shipping_postcode' => $postalCode,
            '_shipping_address_index' => sprintf(
                '%s %s %s %s %s %s %s',
                $indexName,
                $address1,
                $address2,
                $city,
                $state,
                $postalCode,
                $country
            ),
        ];

        foreach ($addressFieldsMapper as $key => $value) {
            update_post_meta($orderId, $key, $value, false);
        }

        $this->wooOrderAddressRepository->updateWcOrderAddress(
            $orderId,
            [
                'address_1' => $address1,
                'address_2' => $address2,
                'city' => $city,
                'state' => $state,
                'postcode' => $postalCode,
                'country' => $country,
            ]
        );
    }
}
