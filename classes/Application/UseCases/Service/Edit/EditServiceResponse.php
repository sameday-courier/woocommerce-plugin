<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

if (!defined('ABSPATH')) {
    exit;
}

final class EditServiceResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var int $serviceId
     */
    private int $serviceId;

    /**
     * @param int $serviceId
     * @param string|null $noticeMessage
     * @param string $noticeType
     */
    public function __construct(
        int $serviceId,
        ?string $noticeMessage,
        string $noticeType
    ) {
        $this->serviceId = $serviceId;
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
    }

    /**
     * @return int
     */
    public function getServiceId(): int
    {
        return $this->serviceId;
    }
}
