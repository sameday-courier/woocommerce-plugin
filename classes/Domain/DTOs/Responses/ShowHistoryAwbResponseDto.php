<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class ShowHistoryAwbResponseDto
{
    private int $orderId;

    private bool $success;

    /**
     * @var array $packages
     */
    private array $packages;

    /**
     * @var string[] $errors
     */
    private array $errors;

    /**
     * @param int $orderId
     * @param bool $success
     * @param array $packages
     * @param string[] $errors
     */
    public function __construct(
        int $orderId,
        bool $success,
        array $packages,
        array $errors = []
    ) {
        $this->orderId = $orderId;
        $this->success = $success;
        $this->packages = $packages;
        $this->errors = $errors;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
