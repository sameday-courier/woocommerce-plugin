<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use WC_Order_Item;
use WC_Order_Item_Shipping;

final class CarrierServiceFromShippingLineResolver
{
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param ?SamedayServiceRepository $samedayServiceRepository
     */
    public function __construct(?SamedayServiceRepository $samedayServiceRepository = null)
    {
        $this->samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
    }

    /**
     * @param array<int, WC_Order_Item> $shippingLines
     *
     * @return CarrierService|null
     */
    public function resolve(array $shippingLines): ?CarrierService
    {
        foreach ($shippingLines as $shippingLine) {
            if (!$shippingLine instanceof WC_Order_Item_Shipping) {
                continue;
            }

            if (CarrierConstants::PLUGIN_NAME !== $shippingLine->get_method_id()) {
                continue;
            }

            $shippingLineCode = $shippingLine->get_meta(CarrierConstants::META_SERVICE_CODE);
            if (!is_string($shippingLineCode) || '' === $shippingLineCode) {
                continue;
            }

            return $this->samedayServiceRepository->getServiceSamedayByCode($shippingLineCode);
        }

        return null;
    }
}
