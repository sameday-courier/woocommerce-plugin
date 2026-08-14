<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Woo\WooOrderAddressRepository;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressArchiveInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressUpdaterInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedayShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedaySettingsProviderInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Common\Services\JsonStringHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostMetaHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\WordpressSamedaySettingsProvider;
use WC_Order;

final class WooOrderShippingAddressUpdater implements OrderShippingAddressUpdaterInterface
{
    /**
     * @var WooOrderAddressRepository $wooOrderAddressRepository
     */
    private WooOrderAddressRepository $wooOrderAddressRepository;

    /**
     * @var OrderShippingAddressArchiveInterface $wooOrderShippingAddressArchive
     */
    private OrderShippingAddressArchiveInterface $wooOrderShippingAddressArchive;

    /**
     * @var LockerDtoFactory $lockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @var SamedayShippingHdAddressParserInterface $hdAddressParser
     */
    private SamedayShippingHdAddressParserInterface $hdAddressParser;

    /**
     * @var StateCodeResolverInterface $stateCodeResolver
     */
    private StateCodeResolverInterface $stateCodeResolver;

    /**
     * @var SamedaySettingsProviderInterface
     */
    private SamedaySettingsProviderInterface $samedaySettingsProvider;

    /**
     * @param WooOrderAddressRepository $wooOrderAddressRepository
     * @param OrderShippingAddressArchiveInterface $wooOrderShippingAddressArchive
     * @param LockerDtoFactory $lockerDtoFactory
     * @param SamedayShippingHdAddressParserInterface $hdAddressParser
     * @param StateCodeResolverInterface $stateCodeResolver
     * @param SamedaySettingsProviderInterface|null $samedaySettingsProvider
     */
    public function __construct(
        WooOrderAddressRepository $wooOrderAddressRepository,
        OrderShippingAddressArchiveInterface $wooOrderShippingAddressArchive,
        LockerDtoFactory $lockerDtoFactory,
        SamedayShippingHdAddressParserInterface $hdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        ?SamedaySettingsProviderInterface $samedaySettingsProvider = null
    ) {
        $this->wooOrderAddressRepository = $wooOrderAddressRepository;
        $this->wooOrderShippingAddressArchive = $wooOrderShippingAddressArchive;
        $this->lockerDtoFactory = $lockerDtoFactory;
        $this->hdAddressParser = $hdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->samedaySettingsProvider = $samedaySettingsProvider ?? new WordpressSamedaySettingsProvider();
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

        $lockerDto = $this->lockerDtoFactory->fromInput($postMetaLocker);
        if (null === $lockerDto) {
            return null;
        }

        return [
            'name' => (string) ($lockerDto->getName() ?? ''),
            'city' => (string) ($lockerDto->getCity() ?? ''),
            'county' => (string) ($lockerDto->getCounty() ?? ''),
            'address' => (string) ($lockerDto->getAddress() ?? ''),
            'postalCode' => (string) ($lockerDto->getPostalCode() ?? ''),
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
        $hostCountry = $this->samedaySettingsProvider->get()->getHostCountry();
        $country = $hostCountry;

        if ($order instanceof WC_Order) {
            $country = $order->get_shipping_country() ?: $order->get_billing_country() ?: $hostCountry;
        }

        $state = $this->stateCodeResolver->resolveFromName(
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
