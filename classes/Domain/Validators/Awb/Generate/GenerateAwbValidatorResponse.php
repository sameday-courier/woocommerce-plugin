<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Validators\Awb\Generate;

class GenerateAwbValidatorResponse
{
    /**
     * @var array $errors
     */
    public array $errors = [];

    /**
     * @param string $key
     * @param string $error
     *
     * @return $this
     */
    public function setErrors(string $key, string $error): self
    {
        $this->errors[$key] = $error;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return implode("\n", $this->errors);
    }
}
