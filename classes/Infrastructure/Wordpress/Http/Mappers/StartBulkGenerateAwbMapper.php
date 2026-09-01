<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class StartBulkGenerateAwbMapper
{
    public const ORDER_IDS_KEY = 'samedaycourier-order-ids';

    /**
     * @var array $inputParams
     */
    private array $inputParams;

    /**
     * @param array $inputParams
     */
    public function __construct(array $inputParams)
    {
        $this->inputParams = $inputParams;
    }

    /**
     * @return array
     */
    public function orderIds(): array
    {
        $orderIds = $this->inputParams[self::ORDER_IDS_KEY] ?? [];
        if (!is_array($orderIds)) {
            return [];
        }

        return array_values(
            array_filter(
                array_map(
                    static function ($orderId): int {
                        return (int) $orderId;
                    },
                    $orderIds
                ),
                /**
                 * @param int $orderId
                 *
                 * @return bool
                 */
                static function (int $orderId): bool {
                    return $orderId > 0;
                }
            )
        );
    }
}
