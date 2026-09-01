<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\CarrierLockerRules;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\LockerDtoRequest;
use SamedayCourier\Shipping\Domain\DTOs\Responses\LockerDtoResponse;
use SamedayCourier\Shipping\Domain\Ports\LockerServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class LockerServiceProvider implements LockerServiceProviderInterface
{
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @param ?SamedayLockerRepository $samedayLockerRepository
     */
    public function __construct(?SamedayLockerRepository $samedayLockerRepository = null)
    {
        $this->samedayLockerRepository = $samedayLockerRepository ?? new SamedayLockerRepository();
    }

    /**
     * @param LockerDtoRequest $lockerRequest
     *
     * @return LockerDtoResponse
     */
    public function getLocker(LockerDtoRequest $lockerRequest): LockerDtoResponse
    {
        $locker = $this->samedayLockerRepository->getLockerSameday($lockerRequest->getLockerId());
        if (null === $locker) {
            return new LockerDtoResponse(null);
        }

        $lockerId = $locker->getLockerId();

        return new LockerDtoResponse(
            new LockerDto(
            $lockerId,
            CarrierLockerRules::resolveOohType($lockerId),
            $locker->getName(),
            $locker->getCounty(),
            $locker->getCity(),
            $locker->getAddress(),
            $locker->getPostalCode()
        ));
    }
}
