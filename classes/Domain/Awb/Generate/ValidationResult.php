<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate;

if (!defined('ABSPATH')) {
    exit;
}

final class ValidationResult
{
    /**
     * @var string[]
     */
    private array $errors;

    /**
     * @param string[] $errors
     */
    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    public function isValid(): bool
    {
        return [] === $this->errors;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function merge(self $other): self
    {
        return new self(array_merge($this->errors, $other->getErrors()));
    }
}
