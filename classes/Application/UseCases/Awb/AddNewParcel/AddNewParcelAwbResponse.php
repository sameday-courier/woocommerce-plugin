<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class AddNewParcelAwbResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @param string $noticeMessage
     * @param bool $hasError
     * @param int $orderId
     */
    public function __construct(
        string $noticeMessage,
        bool $hasError,
        int $orderId
    ) {
        $this->noticeMessage = $noticeMessage;
        $this->hasError = $hasError;
        $this->orderId = $orderId;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
