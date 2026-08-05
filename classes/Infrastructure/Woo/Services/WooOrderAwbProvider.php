<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Domain\Models\SamedayAwb;

if (!defined('ABSPATH')) {
    exit;
}

final class WooOrderAwbProvider
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
     * Returns the AWB generated for the given order, or null when none exists.
     *
     * The presence of a persisted AWB is the authoritative signal that a Sameday
     * AWB was generated for the order, independent of the shipping line method_id.
     *
     * @param int $orderId
     *
     * @return SamedayAwb|null
     */
    public function get(int $orderId): ?SamedayAwb
    {
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb || null === $awb->getAwbNumber()) {
            return null;
        }

        return $awb;
    }
}
