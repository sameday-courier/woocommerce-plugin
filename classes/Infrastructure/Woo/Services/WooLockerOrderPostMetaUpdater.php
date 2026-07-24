<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

use Exception;
use JsonException;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Common\Services\JsonStringHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySettings;

final class WooLockerOrderPostMetaUpdater
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
     */
    private WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater;

    /**
     * @param SamedayLockerRepository $samedayLockerRepository
     * @param WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
     */
    public function __construct(
        SamedayLockerRepository $samedayLockerRepository,
        WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
    ) {
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->wooOrderShippingAddressUpdater = $wooOrderShippingAddressUpdater;
    }

    /**
     * @param int $orderId
     *
     * @return void
     *
     * @throws JsonException
     */
    public function update(int $orderId): void
    {
        $postMetaLocker = JsonStringHandler::fixJson(
            InputSanitizer::sanitizeInput(
                (string) get_post_meta(
                    $orderId,
                    SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
                    true
                )
            )
        );

        try {
            $lockerFields = json_decode($postMetaLocker, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return;
        }

        // If you don't use lockerMap but dropdown option
        if (!isset($lockerFields['name']) && !isset($lockerFields['city']) && !isset($lockerFields['county'])) {
            $lockerModel = $this->samedayLockerRepository->getLockerSameday((int) $postMetaLocker);

            if (null === $lockerModel) {
                return;
            }

            $lockerFields = [
                'name' => $lockerModel->getName(),
                'city' => $lockerModel->getCity(),
                'county' => $lockerModel->getCounty(),
                'address' => $lockerModel->getAddress(),
                'postalCode' => $lockerModel->getPostalCode(),
            ];
        }

        $postsMeta = InputSanitizer::sanitizeInputs($_POST);

        $shippingInputs = [];
        foreach ($postsMeta as $key => $value) {
            if (true === (bool) strpos("_" . $key, 'billing')) {
                $shippingInputs[sprintf("_%s", $key)] = $value ?? '';
            }
            if (true === (bool) strpos("_" . $key, 'shipping')) {
                $shippingInputs[sprintf("_%s", $key)] = $value ?? '';
            }
        }

        $hostCountry = SamedaySettings::getHostCountry();
        $country = $shippingInputs['shipping_country'] ?? $postsMeta['billing_country'] ?? $hostCountry;
        $firstName = $shippingInputs['shipping_first_name'] ?? $postsMeta['billing_first_name'] ?? '';
        $state = WooStateCodeResolver::resolveFromName(
            $country,
            $lockerFields['county']
        );

        $this->wooOrderShippingAddressUpdater->update(
            $orderId,
            $lockerFields['address'],
            $lockerFields['name'],
            $firstName,
            $lockerFields['city'],
            $state,
            $lockerFields['postalCode'],
            $country
        );

        if ('' === get_post_meta(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
            true
        )) {
            // Save HD Address
            update_post_meta(
                $orderId,
                SamedayConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
                json_encode($shippingInputs, JSON_THROW_ON_ERROR),
                false
            );
        }
    }
}
