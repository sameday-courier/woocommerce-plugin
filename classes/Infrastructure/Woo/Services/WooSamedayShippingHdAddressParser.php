<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;
use SamedayCourier\Shipping\Domain\Ports\SamedayShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;

if (!defined('ABSPATH')) {
    exit;
}

final class WooSamedayShippingHdAddressParser implements SamedayShippingHdAddressParserInterface
{
    /**
     * @param int $orderId
     *
     * @return array<string, mixed>|null
     */
    public function parse(int $orderId): ?array
    {
        $postMeta = get_post_meta(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
            true
        );

        if ('' === $postMeta) {
            return null;
        }

        try {
            $postMeta = json_decode(
                (string) $postMeta,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            return null;
        }

        $fieldsMapping = [
            'first_name',
            'last_name',
            'city',
            'state',
            'country',
            'postcode',
            'address_1',
            'address_2',
            'phone',
            'email',
            'method',
        ];

        $requiredFields = [
            'city',
            'state',
            'country',
            'postcode',
            'address_1',
            'address_2',
        ];

        $fields = [];
        foreach ($fieldsMapping as $field) {
            $fieldValue = $postMeta[sprintf("_shipping_%s", $field)]
                ?? ($postMeta[sprintf("_billing_%s", $field)] ?? null)
            ;

            $fields[$field] = $fieldValue;
        }

        foreach ($requiredFields as $field) {
            if (null === $fields[$field]) {
                $fields = null;
            }
        }

        return $fields;
    }
}
