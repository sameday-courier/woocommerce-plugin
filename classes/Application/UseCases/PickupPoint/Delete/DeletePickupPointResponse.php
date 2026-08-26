<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class DeletePickupPointResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @param string $noticeMessage
     * @param bool $hasError
     */
    public function __construct(string $noticeMessage, bool $hasError)
    {
        $this->noticeMessage = $noticeMessage;
        $this->hasError = $hasError;
    }
}
