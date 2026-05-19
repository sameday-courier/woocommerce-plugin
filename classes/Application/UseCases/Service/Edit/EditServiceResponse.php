<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

if (!defined('ABSPATH')) {
    exit;
}

class EditServiceResponse
{
    /**
     * @var int $serviceId
     */
    private int $serviceId;

    /**
     * @var string $noticeType
     */
    private string $noticeType;

    /**
     * @var string|null $noticeMessage
     */
    private ?string $noticeMessage;

    /**
     * @param int $serviceId
     * @param string $noticeType
     * @param string|null $noticeMessage
     */
    public function __construct(
        int $serviceId,
        string $noticeType,
        ?string $noticeMessage = null
    ) {
        $this->serviceId = $serviceId;
        $this->noticeType = $noticeType;
        $this->noticeMessage = $noticeMessage;
    }

    /**
     * @return int
     */
    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    /**
     * @return string
     */
    public function getNoticeType(): string
    {
        return $this->noticeType;
    }

    /**
     * @return string|null
     */
    public function getNoticeMessage(): ?string
    {
        return $this->noticeMessage;
    }

    /**
     * @return bool
     */
    public function hasNotices(): bool
    {
        return null !== $this->noticeMessage;
    }
}
