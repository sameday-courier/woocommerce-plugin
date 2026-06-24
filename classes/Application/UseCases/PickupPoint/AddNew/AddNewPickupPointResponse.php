<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

if (!defined('ABSPATH')) {
    exit;
}

final class AddNewPickupPointResponse
{
    use NoticerTrait;

    /**
     * @param string|null $noticeMessage
     * @param string $noticeType
     */
    public function __construct(
        ?string $noticeMessage,
        string $noticeType
    )
    {
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
    }
}
