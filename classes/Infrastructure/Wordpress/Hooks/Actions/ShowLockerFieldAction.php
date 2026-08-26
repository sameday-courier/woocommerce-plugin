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
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

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
     * @param WooShippingMethodProvider|null $wooShippingMethodProvider
     * @param CarrierServiceRules|null $carrierServiceRules
     * @param SessionHandlerInterface|null $sessionHandler
     * @param CarrierSettingsServiceProvider|null $carrierSettingsServiceProvider
     * @param LockerDtoFactory|null $lockerDtoFactory
     */
    public function __construct(
        ?WooShippingMethodProvider $wooShippingMethodProvider = null,
        ?CarrierServiceRules $carrierServiceRules = null,
        ?SessionHandlerInterface $sessionHandler = null,
        ?CarrierSettingsServiceProvider $carrierSettingsServiceProvider = null,
        ?LockerDtoFactory $lockerDtoFactory = null
    ) {
        $this->wooShippingMethodProvider = $wooShippingMethodProvider ?? new WooShippingMethodProvider();
        $this->carrierServiceRules = $carrierServiceRules ?? new CarrierServiceRules(new SamedayServiceRepository());
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
        $this->carrierSettingsServiceProvider = $carrierSettingsServiceProvider ?? new CarrierSettingsServiceProvider();
        $this->lockerDtoFactory = $lockerDtoFactory ?? new LockerDtoFactory();
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
     * @return string|null
     */
    private function buildShipToLabel(?LockerDto $locker): ?string
    {
        if (null === $locker) {
            return null;
        }

        return sprintf(
            '%s <br/> %s',
            esc_html($locker->getName() ?? ''),
            esc_html($locker->getAddress() ?? '')
        );
    }

    /**
     * @param LockerDto|null $locker
     *
     * @return string
     */
    private function buildHtmlContent(?LockerDto $locker): string
    {
        if (null === $locker) {
            return '';
        }

        $shipTo = $this->buildShipToLabel($locker);
        $settings = $this->carrierSettingsServiceProvider->get();
        if ($settings->isLockersMapEnabled()) {
            return HtmlHandler::buildHtml('locker-map-field', [
                'username' => $settings->getUser() ?? '',
                'hostCountry' => $settings->getHostCountry(),
                'shipTo' => $shipTo,
            ]);
        }

        return HtmlHandler::buildHtml('locker-dropdown-field', [
            'lockersByCity' => $this->buildLockersByCity($locker->getLockerId()),
            'shipTo' => $shipTo,
        ]);
    }

    /**
     * @param int|null $selectedLockerId
     *
     * @return array<string, array<int, array{id: int|string, label: string, selected: bool}>>
     */
    private function buildLockersByCity(?int $selectedLockerId = null): array
    {
        $samedayLockerRepository = new SamedayLockerRepository();
        $cities = $samedayLockerRepository->getCitiesWithLockers();
        $lockersByCity = [];

        foreach ($cities as $city) {
            $cityName = $city->getCity();
            if (null === $cityName) {
                continue;
            }

            $cityLabel = $cityName . ' (' . $city->getCounty() . ')';
            $cityLockers = [];

            foreach ($samedayLockerRepository->getLockersByCity($cityName) as $locker) {
                $cityLockers[] = [
                    'id' => $locker->getLockerId(),
                    'label' => $locker->getName() . ' - ' . $locker->getAddress(),
                    'selected' => null !== $selectedLockerId && $selectedLockerId === $locker->getLockerId(),
                ];
            }

            $lockersByCity[$cityLabel] = $cityLockers;
        }

        return $lockersByCity;
    }
}
