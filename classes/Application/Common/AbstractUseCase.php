<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common;

use SamedayCourier\Shipping\Application\Common\Exceptions\InvalidRequestTypeException;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Interfaces\UseCaseInterface;

/**
 * @template TRequest of RequestInterface
 * @template TResponse of ResponseInterface
 *
 * @implements UseCaseInterface<TRequest, TResponse>
 */
abstract class AbstractUseCase implements UseCaseInterface
{
    /**
     * @param TRequest $request
     *
     * @return TResponse
     *
     * @throws InvalidRequestTypeException
     */
    final public function execute(RequestInterface $request): ResponseInterface
    {
        $this->validateRequest($request);

        return $this->processAction($request);
    }

    /**
     * @param TRequest $request
     *
     * @return TResponse
     */
    abstract protected function processAction(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     *
     * @return void
     *
     * @throws InvalidRequestTypeException
     */
    private function validateRequest(RequestInterface $request): void
    {
        $expectedRequestClass = static::class . 'Request';

        if (!$request instanceof $expectedRequestClass) {
            throw new InvalidRequestTypeException($request, $expectedRequestClass);
        }
    }
}
