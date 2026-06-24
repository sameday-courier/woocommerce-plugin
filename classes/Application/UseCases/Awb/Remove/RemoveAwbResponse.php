<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

if (!defined('ABSPATH')) {
    exit;
}

final class RemoveAwbResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @param int $orderId
     * @param string|null $noticeMessage
     * @param string $noticeType
     */
    public function __construct(
        int $orderId,
        ?string $noticeMessage,
        string $noticeType
    ) {
        $this->orderId = $orderId;
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
