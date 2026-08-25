<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\ValueObject;

use InvalidArgumentException;

final class BulkJobId
{
    private string $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public static function fromString(string $value): self
    {
        $normalized = trim($value);
        if ('' === $normalized) {
            throw new InvalidArgumentException('Bulk job id cannot be empty.');
        }

        return new self($normalized);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @param self $other
     *
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
