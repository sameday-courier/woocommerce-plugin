<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class GetCountiesResponse implements ResponseInterface
{
    use NoticerTrait;

    /**
     * @var array $counties
     */
    private array $counties;

    /**
     * @param string $noticeMessage
     * @param bool $hasError
     * @param array $counties
     */
    public function __construct(
        string $noticeMessage,
        bool $hasError,
        array $counties = []
    ) {
        $this->noticeMessage = $noticeMessage;
        $this->hasError = $hasError;
        $this->counties = $counties;
    }

    /**
     * @return array
     */
    public function getCounties(): array
    {
        return $this->counties;
    }
}
