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
     * @param string|null $noticeMessage
     * @param string $noticeType
     * @param array<int, array{id: int, name: string}> $counties
     */
    public function __construct(
        ?string $noticeMessage,
        string $noticeType,
        array $counties = []
    )
    {
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
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
