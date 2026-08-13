<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class RefreshCityResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @param string|null $noticeMessage
     * @param string $noticeType
     */
    public function __construct(?string $noticeMessage, string $noticeType)
    {
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
    }
}
