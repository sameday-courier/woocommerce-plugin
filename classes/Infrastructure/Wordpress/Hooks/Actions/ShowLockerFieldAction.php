<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Exception;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
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
        $serviceCode = (new WooShippingMethodProvider())->getChosenServiceCode();
        if (!FrontPageValidatorHandler::isCheckoutPage()) {
            return;
        }

        $carrierServiceRules = new CarrierServiceRules(new SamedayServiceRepository());

        if (false === $carrierServiceRules->isOohDeliveryOptionByCode($serviceCode)) {
            return;
        }

        echo $this->buildHtmlContent($this->resolveShipTo());
    }

    /**
     * @return string|null
     */
    private function resolveShipTo(): ?string
    {
        $lockerSession = (new WooSessionHandler())->get(CarrierSessionKeys::LOCKER);
        if (null === $lockerSession) {
            return null;
        }

        try {
            $lockerSession = json_decode($lockerSession, false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return null;
        }

        return sprintf(
            '%s <br/> %s',
            esc_html($lockerSession->name ?? ''),
            esc_html($lockerSession->address ?? '')
        );
    }

    /**
     * @param string|null $shipTo
     *
     * @return string
     */
    private function buildHtmlContent(?string $shipTo): string
    {
        $settings = (new CarrierSettingsServiceProvider())->get();
        if ($settings->isLockersMapEnabled()) {
            return HtmlHandler::buildHtml('locker-map-field', [
                'username' => (string) ($settings->getUser() ?? ''),
                'hostCountry' => (string) $settings->getHostCountry(),
                'shipTo' => $shipTo,
            ]);
        }

        return HtmlHandler::buildHtml('locker-dropdown-field', [
            'lockersByCity' => $this->buildLockersByCity(),
        ]);
    }

    /**
     * @return array<string, array<int, array{id: int|string, label: string, selected: bool}>>
     */
    private function buildLockersByCity(): array
    {
        $samedayLockerRepository = new SamedayLockerRepository();
        $cities = $samedayLockerRepository->getCitiesWithLockers();
        $selectedLockerId = (int) (new WooSessionHandler())->get(CarrierSessionKeys::LOCKER);
        $lockersByCity = [];

        foreach ($cities as $city) {
            if (null === $city->getCity()) {
                continue;
            }

            $cityLabel = $city->getCity() . ' (' . $city->getCounty() . ')';
            $cityLockers = [];

            foreach ($samedayLockerRepository->getLockersByCity((string) $city->getCity()) as $locker) {
                $cityLockers[] = [
                    'id' => $locker->getLockerId(),
                    'label' => $locker->getName() . ' - ' . $locker->getAddress(),
                    'selected' => $selectedLockerId === (int) $locker->getLockerId(),
                ];
            }

            $lockersByCity[$cityLabel] = $cityLockers;
        }

        return $lockersByCity;
    }
}
