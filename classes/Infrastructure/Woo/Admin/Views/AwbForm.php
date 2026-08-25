<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use JsonException;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services\AwbCurrencyWarningProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services\AwbFormOptionsProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostMetaHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\SamedayIcon;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use WC_Order;

class AwbForm
{
    public const MODAL_ID = 'sameday-generate-awb-modal';

    /**
     * @var CarrierServiceRules $carrierServiceRules
     */
    private CarrierServiceRules $carrierServiceRules;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @param SamedayServiceRepository $samedayServiceRepository
     * @param SamedayLockerRepository $samedayLockerRepository
     * @param SamedayPickupPointRepository $samedayPickupPointRepository
     */
    public function __construct(
        SamedayServiceRepository $samedayServiceRepository,
        SamedayLockerRepository $samedayLockerRepository,
        SamedayPickupPointRepository $samedayPickupPointRepository
    ) {
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->samedayPickupPointRepository = $samedayPickupPointRepository;
        $this->carrierServiceRules = new CarrierServiceRules($this->samedayServiceRepository);
    }

    /**
     * @param WC_Order $order
     *
     * @return string
     */
    public function samedaycourierAddAwbForm(WC_Order $order): string
    {
        return HtmlHandler::buildHtml('awb-form', $this->buildTemplateParams($order));
    }

    /**
     * @param WC_Order $order
     *
     * @return array<string, mixed>
     */
    protected function buildTemplateParams(WC_Order $order): array
    {
        $serviceCode = $this->resolveServiceCode($order);
        $totalWeight = $this->resolveTotalWeight($order);
        $paymentGateway = wc_get_payment_gateway_by_order($order);
        $repayment = AwbCurrencyWarningProvider::resolveRepayment($order);
        $lockerData = $this->resolveLockerData($order);
        $settings = (new CarrierSettingsServiceProvider())->get();
        $shipping = $order->get_data()['shipping'] ?? [];
        $destCity = $shipping['city'] ?? '';
        $destCountry = $shipping['country'] ?? '';
        $currency = AwbCurrencyWarningProvider::resolveOrderCurrency($order);
        $servicesContext = $this->buildServicesContext($serviceCode);

        return [
            'modalId' => self::MODAL_ID,
            'iconHtml' => SamedayIcon::render(
                'sameday-bulk-awb-modal__icon-svg sameday-bulk-awb-modal__icon-svg--package',
                26
            ),
            'orderId' => $order->get_id(),
            'repayment' => $repayment,
            'currency' => $currency,
            'paymentGatewayTitle' => $paymentGateway->title,
            'currencyWarning' => AwbCurrencyWarningProvider::forOrder($order),
            'totalWeight' => $totalWeight,
            'calculatedWeightLabel' => 'Calculated Weight: ' . $totalWeight . ' '
                . OptionsHandler::getOption('woocommerce_weight_unit', 'kg'),
            'pickupPoints' => $this->buildPickupPoints(),
            'packageTypes' => AwbFormOptionsProvider::getPackageTypeOptions(),
            'awbPaymentTypes' => AwbFormOptionsProvider::getAwbPaymentTypeOptions(),
            'services' => $servicesContext['services'],
            'allowFirstMile' => $servicesContext['allowFirstMile'],
            'allowLastMile' => $servicesContext['allowLastMile'],
            'lockerDetailsForm' => $lockerData['lockerDetailsForm'],
            'lockerDetails' => $lockerData['lockerDetails'],
            'username' => (string) $settings->getUser(),
            'hostCountry' => (string) $settings->getHostCountry(),
            'destCity' => $destCity,
            'destCountry' => $destCountry,
            'openPackage' => (new WooOpenPackageOrderDataHandler())->isEnabled($order->get_id()),
        ];
    }

    /**
     * @param WC_Order $order
     *
     * @return string|null
     */
    private function resolveServiceCode(WC_Order $order): ?string
    {
        $postMetaLocker = PostMetaHandler::get(
            $order->get_id(),
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
        );
        $lockerDto = (new LockerDtoFactory($this->samedayLockerRepository))->fromInput($postMetaLocker);

        foreach ($order->get_data()['shipping_lines'] as $shippingLine) {
            if ($shippingLine->get_method_id() !== CarrierConstants::PLUGIN_NAME) {
                continue;
            }

            $serviceCode = $shippingLine->get_meta('service_code');
            if (null === $serviceCode) {
                continue;
            }

            if (
                null !== $lockerDto
                && '1' === $lockerDto->getOohType()
                && $this->carrierServiceRules->isOohDeliveryOptionByCode($serviceCode)
            ) {
                return CarrierConstants::OOH_TYPES['1'];
            }

            return $serviceCode;
        }

        return null;
    }

    /**
     * @param WC_Order $order
     *
     * @return float
     */
    private function resolveTotalWeight(WC_Order $order): float
    {
        $totalWeight = 0.0;

        foreach ($order->get_items() as $item) {
            $product = wc_get_product($item['product_id']);
            $weight = 0.0;

            if (isset($product) && false !== $product) {
                $weight = (float) $product->get_weight();
            }

            $totalWeight += (float) number_format($weight * $item['quantity'], 2);
        }

        return $totalWeight ?: 1.0;
    }

    /**
     * @param WC_Order $order
     *
     * @return array{lockerDetailsForm: string, lockerDetails: string}
     */
    private function resolveLockerData(WC_Order $order): array
    {
        $postMetaLocker = PostMetaHandler::get(
            $order->get_id(),
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
        );
        $lockerDto = (new LockerDtoFactory($this->samedayLockerRepository))->fromInput($postMetaLocker);

        if (null === $lockerDto) {
            return [
                'lockerDetailsForm' => '',
                'lockerDetails' => '',
            ];
        }

        try {
            return [
                'lockerDetailsForm' => json_encode($lockerDto->toArray(), JSON_THROW_ON_ERROR),
                'lockerDetails' => sprintf('%s - %s', $lockerDto->getName(), $lockerDto->getAddress()),
            ];
        } catch (JsonException $exception) {
            return [
                'lockerDetailsForm' => '',
                'lockerDetails' => '',
            ];
        }
    }

    /**
     * @return array<int, array{id: int|string, alias: string, selected: bool}>
     */
    private function buildPickupPoints(): array
    {
        $pickupPoints = [];

        foreach ($this->samedayPickupPointRepository->getPickupPoints() as $pickupPoint) {
            $pickupPoints[] = [
                'id' => $pickupPoint->getSamedayId(),
                'alias' => (string) $pickupPoint->getSamedayAlias(),
                'selected' => true === $pickupPoint->getDefaultPickupPoint(),
            ];
        }

        return $pickupPoints;
    }

    /**
     * @param string|null $serviceCode
     *
     * @return array{
     *     services: array<int, array{
     *         id: int|string,
     *         name: string,
     *         selected: bool,
     *         firstMile: string,
     *         lastMile: string
     *     }>,
     *     allowFirstMile: string,
     *     allowLastMile: string
     * }
     */
    private function buildServicesContext(?string $serviceCode): array
    {
        $allowLastMile = CarrierConstants::TOGGLE_HTML_ELEMENT['hide'];
        $allowFirstMile = CarrierConstants::TOGGLE_HTML_ELEMENT['hide'];
        $services = [];

        foreach ($this->samedayServiceRepository->getAvailableServices() as $carrierService) {
            $firstMileId = $this->carrierServiceRules->isEligibleToLockerFirstMile($carrierService)
                ? $carrierService->getSamedayId()
                : 0;
            $selected = $serviceCode === $carrierService->getSamedayCode();
            $optionFirstMile = CarrierConstants::TOGGLE_HTML_ELEMENT['hide'];

            if ($firstMileId === $carrierService->getSamedayId()) {
                $optionFirstMile = CarrierConstants::TOGGLE_HTML_ELEMENT['show'];
            }

            $optionLastMile = CarrierConstants::TOGGLE_HTML_ELEMENT['hide'];
            if ($this->carrierServiceRules->isOohDeliveryOption($carrierService)) {
                $optionLastMile = CarrierConstants::TOGGLE_HTML_ELEMENT['show'];
            }

            if ($selected) {
                $allowFirstMile = $optionFirstMile;
                $allowLastMile = $optionLastMile;
            }

            $services[] = [
                'id' => $carrierService->getSamedayId(),
                'name' => (string) ($carrierService->getSamedayName() ?? ''),
                'selected' => $selected,
                'firstMile' => $optionFirstMile,
                'lastMile' => $optionLastMile,
            ];
        }

        return [
            'services' => $services,
            'allowFirstMile' => $allowFirstMile,
            'allowLastMile' => $allowLastMile,
        ];
    }
}
