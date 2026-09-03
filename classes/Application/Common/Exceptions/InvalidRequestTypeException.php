<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Exceptions;

use InvalidArgumentException;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

final class InvalidRequestTypeException extends InvalidArgumentException
{
    /**
     * @param RequestInterface $request
     * @param string $expectedClass
     */
    public function __construct(RequestInterface $request, string $expectedClass)
    {
        parent::__construct(
            sprintf(
                'Expected instance of %s, got %s',
                $expectedClass,
                get_class($request)
            )
        );
    }
}
