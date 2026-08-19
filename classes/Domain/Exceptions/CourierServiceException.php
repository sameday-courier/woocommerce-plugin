<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Exceptions;

use RuntimeException;
use Throwable;

final class CourierServiceException extends RuntimeException
{
    /**
     * @param string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
