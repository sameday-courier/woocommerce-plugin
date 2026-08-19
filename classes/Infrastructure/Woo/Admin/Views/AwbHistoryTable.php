<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Domain\Models\CarrierPackage;
use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;

class AwbHistoryTable
{
    /**
     * @param mixed $packages
     *
     * @return string
     */
    public static function addAwbHistoryTable($packages): string
    {
        return HtmlHandler::buildHtml('awb-history-table', [
            'packages' => self::normalizePackages($packages),
        ]);
    }

    /**
     * @param mixed $packages
     *
     * @return array<int, array{
     *     awbNumber: string,
     *     weight: mixed,
     *     delivered: bool,
     *     deliveryAttempts: mixed,
     *     pickedUp: bool,
     *     pickedUpAt: string,
     *     history: array<int, array{
     *         name: string,
     *         label: string,
     *         state: string,
     *         date: string,
     *         county: string,
     *         transitLocation: string,
     *         reason: string
     *     }>
     * }>
     */
    private static function normalizePackages($packages): array
    {
        $normalized = [];

        foreach ($packages as $package) {
            $summarySerialized = $package instanceof CarrierPackage
                ? ($package->getSummary() ?? '')
                : ($package['summary'] ?? '');
            $historySerialized = $package instanceof CarrierPackage
                ? ($package->getHistory() ?? '')
                : ($package['history'] ?? '');
            $summary = unserialize($summarySerialized, ['']);
            $packageHistory = unserialize($historySerialized, ['']);

            $history = [];
            if (!empty($packageHistory)) {
                foreach ($packageHistory as $historyItem) {
                    $history[] = [
                        'name' => $historyItem->getName(),
                        'label' => $historyItem->getLabel(),
                        'state' => $historyItem->getState(),
                        'date' => $historyItem->getDate()->format('Y-m-d H:i:s'),
                        'county' => $historyItem->getCounty(),
                        'transitLocation' => $historyItem->getTransitLocation(),
                        'reason' => $historyItem->getReason(),
                    ];
                }
            }

            $normalized[] = [
                'awbNumber' => (string) $summary->getParcelAwbNumber(),
                'weight' => $summary->getParcelWeight(),
                'delivered' => $summary->isDelivered(),
                'deliveryAttempts' => $summary->getDeliveryAttempts(),
                'pickedUp' => $summary->isPickedUp(),
                'pickedUpAt' => $summary->getPickedUpAt()
                    ? $summary->getPickedUpAt()->format('Y-m-d H:i:s')
                    : '',
                'history' => $history,
            ];
        }

        return $normalized;
    }
}
