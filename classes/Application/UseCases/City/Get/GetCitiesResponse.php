<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class GetCitiesResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var array $cities
     */
    private array $cities;

    /**
     * @param string $noticeMessage
     * @param bool $hasError
     * @param array $cities
     */
    public function __construct(
        string $noticeMessage,
        bool $hasError,
        array $cities = []
    ) {
        $this->noticeMessage = $noticeMessage;
        $this->hasError = $hasError;
        $this->cities = $cities;
    }

    /**
     * @return array
     */
    public function getCities(): array
    {
        return $this->cities;
    }
}
