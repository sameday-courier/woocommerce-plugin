<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Traits;

trait NoticerTrait
{
    /**
     * @var string $noticeType
     */
    protected string $noticeType;

    /**
     * @var string|null $noticeMessage
     */
    protected ?string $noticeMessage;

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
