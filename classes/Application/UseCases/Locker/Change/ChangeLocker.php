<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use JsonException;
use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;

/**
 * @extends AbstractUseCase<ChangeLockerRequest, ChangeLockerResponse>
 *
 * @method ChangeLockerResponse execute(ChangeLockerRequest $request)
 */
final class ChangeLocker extends AbstractUseCase
{
    /**
     * @var LockerOrderDataHandlerInterface $lockerOrderDataHandler
     */
    private LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    /**
     * @var LockerDtoFactory $lockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @param LockerOrderDataHandlerInterface $lockerOrderDataHandler
     * @param LockerDtoFactory|null $lockerDtoFactory
     */
    public function __construct(
        LockerOrderDataHandlerInterface $lockerOrderDataHandler,
        ?LockerDtoFactory $lockerDtoFactory = null
    ) {
        $this->lockerOrderDataHandler = $lockerOrderDataHandler;
        $this->lockerDtoFactory = $lockerDtoFactory ?? new LockerDtoFactory();
    }

    /**
     * @param ChangeLockerRequest $request
     *
     * @return ChangeLockerResponse
     */
    protected function processAction(RequestInterface $request): ChangeLockerResponse
    {
        $orderId = $request->getOrderId();
        $locker = $request->getLocker();

        if ($orderId <= 0) {
            return new ChangeLockerResponse(
                'Invalid order id.',
                true
            );
        }

        if (null === $locker || '' === $locker) {
            return new ChangeLockerResponse(
                'Locker data is required.',
                true
            );
        }

        $lockerDto = $this->lockerDtoFactory->fromInput($locker);
        if (null === $lockerDto) {
            return new ChangeLockerResponse(
                'Invalid locker data.',
                true
            );
        }

        try {
            $encodedLocker = json_encode(
                $lockerDto->toArray(),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
            );
            $this->lockerOrderDataHandler->add($orderId, $encodedLocker);
        } catch (JsonException $exception) {
            return new ChangeLockerResponse(
                'Unable to store locker data.',
                true
            );
        }

        return new ChangeLockerResponse(
            'Locker successfully updated.',
            false
        );
    }
}
