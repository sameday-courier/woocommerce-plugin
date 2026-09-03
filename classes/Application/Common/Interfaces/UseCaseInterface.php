<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Interfaces;

/**
 * @template TRequest of RequestInterface
 * @template TResponse of ResponseInterface
 */
interface UseCaseInterface
{
    /**
     * @param TRequest $request
     *
     * @return TResponse
     */
    public function execute(RequestInterface $request): ResponseInterface;
}
