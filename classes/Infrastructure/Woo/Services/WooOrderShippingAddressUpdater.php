<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use Exception;
use JsonException;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Woo\WooOrderAddressRepository;
use SamedayCourier\Shipping\Domain\Ports\SamedayShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySettings;
use SamedayCourier\Shipping\Infrastructure\Common\Services\JsonStringHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostMetaHandler;
use WC_Order;

if (!defined('ABSPATH')) {
    exit;
}

final class WooOrderShippingAddressUpdater
{
    /**
     * @var WooOrderAddressRepository $wooOrderAddressRepository
     */
    private WooOrderAddressRepository $wooOrderAddressRepository;

    /**
     * @var WooOrderShippingAddressArchive $wooOrderShippingAddressArchive
     */
    private WooOrderShippingAddressArchive $wooOrderShippingAddressArchive;

    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var SamedayShippingHdAddressParserInterface $hdAddressParser
     */
    private SamedayShippingHdAddressParserInterface $hdAddressParser;

    /**
     * @param WooOrderAddressRepository $wooOrderAddressRepository
     * @param WooOrderShippingAddressArchive $wooOrderShippingAddressArchive
     * @param SamedayLockerRepository $samedayLockerRepository
     * @param SamedayShippingHdAddressParserInterface $hdAddressParser
     */
    public function __construct(
        WooOrderAddressRepository $wooOrderAddressRepository,
        WooOrderShippingAddressArchive $wooOrderShippingAddressArchive,
        SamedayLockerRepository $samedayLockerRepository,
        SamedayShippingHdAddressParserInterface $hdAddressParser
    ) {
        $this->wooOrderAddressRepository = $wooOrderAddressRepository;
        $this->wooOrderShippingAddressArchive = $wooOrderShippingAddressArchive;
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->hdAddressParser = $hdAddressParser;
    }

    /**
     * @param int $orderId
     *
     * @return void
     *
     * @throws JsonException
     */
    public function activateOutOfHome(int $orderId): void
    {
        $this->wooOrderShippingAddressArchive->ensureHomeDeliverySnapshot($orderId);

        $lockerFields = $this->resolveLockerFields($orderId);
        if (null === $lockerFields) {
            return;
        }

        $this->applyLockerFields($orderId, $lockerFields);
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function activateHomeDelivery(int $orderId): void
    {
        $fields = $this->hdAddressParser->parse($orderId);
        if (null === $fields) {
            return;
        }

        $this->wooOrderAddressRepository->updateShippingAddress(
            $orderId,
            [
                'first_name' => $fields['first_name'] ?? '',
                'last_name' => $fields['last_name'] ?? '',
                'address_1' => $fields['address_1'] ?? '',
                'address_2' => $fields['address_2'] ?? '',
                'city' => $fields['city'] ?? '',
                'state' => $fields['state'] ?? '',
                'postcode' => $fields['postcode'] ?? '',
                'country' => $fields['country'] ?? '',
                'phone' => $fields['phone'] ?? '',
            ]
        );
    }

    /**
     * @param int $orderId
     *
     * @return array<string, string>|null
     */
    private function resolveLockerFields(int $orderId): ?array
    {
        $postMetaLocker = JsonStringHandler::fixJson(
            InputSanitizer::sanitizeInput(
                (string) PostMetaHandler::get(
                    $orderId,
                    SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
                    true
                )
            )
        );

        if ('' === $postMetaLocker) {
            return null;
        }

        try {
            $lockerFields = json_decode($postMetaLocker, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            $lockerFields = null;
        }

        if (!is_array($lockerFields)) {
            $lockerModel = $this->samedayLockerRepository->getLockerSameday((int) $postMetaLocker);
            if (null === $lockerModel) {
                return null;
            }

            return [
                'name' => $lockerModel->getName(),
                'city' => $lockerModel->getCity(),
                'county' => $lockerModel->getCounty(),
                'address' => $lockerModel->getAddress(),
                'postalCode' => $lockerModel->getPostalCode(),
            ];
        }

        if (!isset($lockerFields['name']) && !isset($lockerFields['city']) && !isset($lockerFields['county'])) {
            $lockerModel = $this->samedayLockerRepository->getLockerSameday((int) $postMetaLocker);
            if (null === $lockerModel) {
                return null;
            }

            return [
                'name' => $lockerModel->getName(),
                'city' => $lockerModel->getCity(),
                'county' => $lockerModel->getCounty(),
                'address' => $lockerModel->getAddress(),
                'postalCode' => $lockerModel->getPostalCode(),
            ];
        }

        return [
            'name' => (string) ($lockerFields['name'] ?? ''),
            'city' => (string) ($lockerFields['city'] ?? ''),
            'county' => (string) ($lockerFields['county'] ?? ''),
            'address' => (string) ($lockerFields['address'] ?? ''),
            'postalCode' => (string) ($lockerFields['postalCode'] ?? ''),
        ];
    }

    /**
     * @param int $orderId
     * @param array<string, string> $lockerFields
     *
     * @return void
     */
    private function applyLockerFields(int $orderId, array $lockerFields): void
    {
        $order = wc_get_order($orderId);
        $hostCountry = SamedaySettings::getHostCountry();
        $country = $hostCountry;

        if ($order instanceof WC_Order) {
            $country = $order->get_shipping_country() ?: $order->get_billing_country() ?: $hostCountry;
        }

        $state = WooStateCodeResolver::resolveFromName(
            $country,
            $lockerFields['county']
        );

        $this->wooOrderAddressRepository->updateShippingAddress(
            $orderId,
            [
                'address_1' => str_replace('"', '', InputSanitizer::sanitizeInput($lockerFields['address'])),
                'address_2' => str_replace('"', '', InputSanitizer::sanitizeInput($lockerFields['name'])),
                'city' => $lockerFields['city'],
                'state' => $state,
                'postcode' => $lockerFields['postalCode'],
                'country' => $country,
            ]
        );
    }
}
