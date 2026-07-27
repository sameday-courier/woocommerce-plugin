<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Domain\SamedayConstants;

if (!defined('ABSPATH')) {
    exit;
}

final class WooOrderSamedayShippingMethodProvider
{
    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @param SamedayAwbRepository $samedayAwbRepository
     */
    public function __construct(SamedayAwbRepository $samedayAwbRepository)
    {
        $this->samedayAwbRepository = $samedayAwbRepository;
    }

    /**
     * @param int $orderId
     *
     * @return array<string, mixed>|null
     */
    public function get(int $orderId): ?array
    {
        $data = [];

        $shippingLines = wc_get_order($orderId)->get_data()['shipping_lines'];

        $serviceMethod = null;
        foreach ($shippingLines as $array) {
            $index = array_search($array, $shippingLines, true);
            $serviceMethod = $shippingLines[$index]->get_data()['method_id'];
        }

        if ($serviceMethod !== SamedayConstants::PLUGIN_NAME) {
            return null;
        }

        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null !== $awb && null !== $awb->getAwbNumber()) {
            $data['awb_number'] = $awb->getAwbNumber();
        }

        return $data;
    }
}
