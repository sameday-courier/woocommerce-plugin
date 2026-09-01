<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;
use SamedayCourier\Shipping\Domain\Ports\CarrierShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostMetaHandler;

final class WooSamedayShippingHdAddressParser implements CarrierShippingHdAddressParserInterface
{
    /**
     * @param int $orderId
     *
     * @return array<string,
     */
    public function parse(int $orderId): ?array
    {
        $postMeta = PostMetaHandler::get(
            $orderId,
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
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
