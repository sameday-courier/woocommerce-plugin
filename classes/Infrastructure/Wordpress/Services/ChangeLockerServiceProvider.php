<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Exception;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ChangeLockerRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\ChangeLockerResponseDto;
use SamedayCourier\Shipping\Domain\Ports\ChangeLockerServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderDataHandler;

final class ChangeLockerServiceProvider implements ChangeLockerServiceProviderInterface
{
    private LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    public function __construct(
        ?LockerOrderDataHandlerInterface $lockerOrderDataHandler = null
    ) {
        $this->lockerOrderDataHandler = $lockerOrderDataHandler ?? new WooLockerOrderDataHandler();
    }

    /**
     * @param ChangeLockerRequestDto $changeLockerRequestDto
     *
     * @return ChangeLockerResponseDto
     */
    public function change(ChangeLockerRequestDto $changeLockerRequestDto): ChangeLockerResponseDto
    {
        $orderId = $changeLockerRequestDto->getOrderId();
        $locker = $changeLockerRequestDto->getLocker();

        if ($orderId <= 0) {
            return new ChangeLockerResponseDto(
                false,
                'Invalid order id.'
            );
        }

        if (null === $locker || '' === $locker) {
            return new ChangeLockerResponseDto(
                false,
                'Locker data is required.'
            );
        }

        try {
            $this->lockerOrderDataHandler->add($orderId, $locker);
        } catch (Exception $exception) {
            return new ChangeLockerResponseDto(
                false,
                $exception->getMessage()
            );
        }

        return new ChangeLockerResponseDto(
            true,
            'Locker successfully updated.'
        );
    }
}
