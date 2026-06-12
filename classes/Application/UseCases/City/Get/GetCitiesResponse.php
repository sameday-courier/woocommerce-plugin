<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

if (!defined('ABSPATH')) {
    exit;
}

final class GetCitiesResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var array<int, array{id: int, name: string}> $cities
     */
    private array $cities;

    /**
     * @param string $noticeType
     * @param string|null $noticeMessage
     * @param array<int, array{id: int, name: string}> $cities
     */
    public function __construct(
        string $noticeType,
        ?string $noticeMessage = null,
        array $cities = []
    )
    {
        $this->noticeType = $noticeType;
        $this->noticeMessage = $noticeMessage;
        $this->cities = $cities;
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getCities(): array
    {
        return $this->cities;
    }
}
