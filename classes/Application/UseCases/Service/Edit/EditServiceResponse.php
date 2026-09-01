<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class EditServiceResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var int $serviceId
     */
    private int $serviceId;

    /**
     * @param string $noticeMessage
     * @param bool $hasError
     * @param int $serviceId
     */
    public function __construct(
        string $noticeMessage,
        bool $hasError,
        int $serviceId
    ) {
        $this->noticeMessage = $noticeMessage;
        $this->hasError = $hasError;
        $this->serviceId = $serviceId;
    }

    /**
     * @return int
     */
    public function getServiceId(): int
    {
        return $this->serviceId;
    }
}
