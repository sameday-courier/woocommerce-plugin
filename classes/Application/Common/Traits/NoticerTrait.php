<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Traits;

trait NoticerTrait
{
    /**
     * @var string $noticeMessage
     */
    protected string $noticeMessage;

    /**
     * @var bool $hasError
     */
    protected bool $hasError = false;

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
    public function hasError(): bool
    {
        return $this->hasError;
    }
}
