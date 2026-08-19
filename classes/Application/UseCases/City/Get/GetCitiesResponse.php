<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class GetCitiesResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var array<int, array{id: int, name: string}> $cities
     */
    private array $cities;

    /**
     * @param string|null $noticeMessage
     * @param string $noticeType
     * @param array $cities
     */
    public function __construct(
        ?string $noticeMessage,
        string $noticeType,
        array $cities = []
    ) {
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
        $this->cities = $cities;
    }

    /**
     * @return array<int,
     */
    public function getCities(): array
    {
        return $this->cities;
    }
}
