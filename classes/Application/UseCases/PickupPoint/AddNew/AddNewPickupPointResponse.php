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
     * @param string $noticeType
     * @param string|null $noticeMessage
     */
    public function __construct(
        string $noticeType,
        ?string $noticeMessage = null
    )
    {
        $this->noticeType = $noticeType;
        $this->noticeMessage = $noticeMessage;
    }
}
