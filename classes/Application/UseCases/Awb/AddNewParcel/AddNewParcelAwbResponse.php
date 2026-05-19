<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

if (!defined('ABSPATH')) {
    exit;
}

class AddNewParcelAwbResponse
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var bool $hasNotices
     */
    private bool $hasNotices;

    /**
     * @var string? $noticeMessage
     */
    private string $noticeMessage;

    /**
     * @var string $noticeType
     */
    private string $noticeType;


    public function __construct(
        int $orderId,
        string $noticeType,
        $noticeMessage = null
    )
    {
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

    /**
     * @return string
     */
    public function getNoticeType(): string
    {
        return $this->noticeType;
    }

    /**
     * @return string
     */
    public function getNoticeMessage(): string
    {
        return $this->noticeMessage;
    }

    /**
     * @return bool
     */
    public function hasNotices(): bool
    {
        return $this->getNoticeMessage() !== null;
    }
}
