<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

if (!defined('ABSPATH')) {
    exit;
}

final class GetCountiesResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var array<int, array{id: int, name: string}> $counties
     */
    private array $counties;

    /**
     * @param string $noticeType
     * @param string|null $noticeMessage
     * @param array<int, array{id: int, name: string}> $counties
     */
    public function __construct(
        string $noticeType,
        ?string $noticeMessage = null,
        array $counties = []
    )
    {
        $this->noticeType = $noticeType;
        $this->noticeMessage = $noticeMessage;
        $this->counties = $counties;
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getCounties(): array
    {
        return $this->counties;
    }
}
