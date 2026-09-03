<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\FrontPageValidatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\LockerChoicesProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

/**
 * Renders locker map/dropdown on classic WooCommerce checkout
 * (woocommerce_review_order_after_shipping in review-order.php).
 *
 * WooCommerce Blocks checkout never fires this hook — see
 * RegisterCheckoutBlocksIntegrationAction + CheckoutBlocksIntegration.
 */
final class ShowLockerFieldAction extends AbstractAction
{
    private const ACTION = 'woocommerce_review_order_after_shipping';

    /**
     * @var WooShippingMethodProvider $wooShippingMethodProvider
     */
    private WooShippingMethodProvider $wooShippingMethodProvider;

    /**
     * @var SessionHandlerInterface $sessionHandler
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @var CarrierServiceRules $carrierServiceRules
     */
    private CarrierServiceRules $carrierServiceRules;

    /**
     * @var CarrierSettingsServiceProvider $carrierSettingsServiceProvider
     */
    private CarrierSettingsServiceProvider $carrierSettingsServiceProvider;

    /**
     * @var LockerDtoFactory $lockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @var LockerChoicesProvider $lockerChoicesProvider
     */
    private LockerChoicesProvider $lockerChoicesProvider;

    /**
     * @param WooShippingMethodProvider|null $wooShippingMethodProvider
     * @param CarrierServiceRules|null $carrierServiceRules
     * @param SessionHandlerInterface|null $sessionHandler
     * @param CarrierSettingsServiceProvider|null $carrierSettingsServiceProvider
     * @param LockerDtoFactory|null $lockerDtoFactory
     * @param LockerChoicesProvider|null $lockerChoicesProvider
     */
    public function __construct(
        ?WooShippingMethodProvider $wooShippingMethodProvider = null,
        ?CarrierServiceRules $carrierServiceRules = null,
        ?SessionHandlerInterface $sessionHandler = null,
        ?CarrierSettingsServiceProvider $carrierSettingsServiceProvider = null,
        ?LockerDtoFactory $lockerDtoFactory = null,
        ?LockerChoicesProvider $lockerChoicesProvider = null
    ) {
        $this->wooShippingMethodProvider = $wooShippingMethodProvider ?? new WooShippingMethodProvider();
        $this->carrierServiceRules = $carrierServiceRules ?? new CarrierServiceRules(new SamedayServiceRepository());
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
        $this->carrierSettingsServiceProvider = $carrierSettingsServiceProvider ?? new CarrierSettingsServiceProvider();
        $this->lockerDtoFactory = $lockerDtoFactory ?? new LockerDtoFactory();
        $this->lockerChoicesProvider = $lockerChoicesProvider ?? new LockerChoicesProvider();
    }

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $serviceCode = $this->wooShippingMethodProvider->getChosenServiceCode();
        if (!FrontPageValidatorHandler::isCheckoutPage()) {
            return;
        }

        if (false === $this->carrierServiceRules->isOohDeliveryOptionByCode($serviceCode)) {
            return;
        }

        echo $this->buildHtmlContent($this->resolveShipTo());
    }

    /**
     * @return LockerDto|null
     */
    private function resolveShipTo(): ?LockerDto
    {
        return $this->lockerDtoFactory->fromInput(
            $this->sessionHandler->get(CarrierSessionKeys::LOCKER)
        );
    }

    /**
     * @param LockerDto|null $locker
     *
     * @return array{name: string, address: string}|null
     */
    private function buildShipToParts(?LockerDto $locker): ?array
    {
        if (null === $locker) {
            return null;
        }

        return [
            'name' => $locker->getName() ?? '',
            'address' => $locker->getAddress() ?? '',
        ];
    }

    /**
     * @param LockerDto|null $locker
     *
     * @return string
     */
    private function buildHtmlContent(?LockerDto $locker): string
    {
        $shipToParts = $this->buildShipToParts($locker);
        $settings = $this->carrierSettingsServiceProvider->get();
        if ($settings->isLockersMapEnabled()) {
            return HtmlHandler::buildHtml('locker-map-field', [
                'username' => $settings->getUser() ?? '',
                'hostCountry' => $settings->getHostCountry(),
                'shipToParts' => $shipToParts,
            ]);
        }

        $selectedLockerId = null !== $locker ? $locker->getLockerId() : null;

        return HtmlHandler::buildHtml('locker-dropdown-field', [
            'lockersByCity' => $this->lockerChoicesProvider->groupedByCity($selectedLockerId),
            'shipToParts' => $shipToParts,
        ]);
    }
}
