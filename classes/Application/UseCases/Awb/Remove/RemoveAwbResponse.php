<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

if (!defined('ABSPATH')) {
    exit;
}

class RemoveAwbResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @param int $orderId
     * @param string $noticeType
     * @param string|null $noticeMessage
     */
    public function __construct(
        int $orderId,
        string $noticeType,
        ?string $noticeMessage = null
    ) {
        $this->orderId = $orderId;
        $this->noticeType = $noticeType;
        $this->noticeMessage = $noticeMessage;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
