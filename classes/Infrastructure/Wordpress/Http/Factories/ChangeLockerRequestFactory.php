<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\UseCases\Locker\Change\ChangeLockerRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\ChangeLockerMapper;

final class ChangeLockerRequestFactory
{
    /**
     * @var LockerDtoFactory $lockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @param LockerDtoFactory|null $lockerDtoFactory
     */
    public function __construct(?LockerDtoFactory $lockerDtoFactory = null)
    {
        $this->lockerDtoFactory = $lockerDtoFactory ?? LockerDtoFactoryFactory::create();
    }

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self(LockerDtoFactoryFactory::create());
    }

    /**
     * @param ChangeLockerMapper $mapper
     *
     * @return ChangeLockerRequest
     */
    public function fromMapper(ChangeLockerMapper $mapper): ChangeLockerRequest
    {
        $rawLocker = $mapper->locker();
        $lockerDto = null;

        if (null !== $rawLocker && '' !== $rawLocker) {
            $lockerDto = $this->lockerDtoFactory->fromInput($rawLocker);
        }

        return new ChangeLockerRequest(
            $mapper->orderId(),
            $lockerDto
        );
    }
}
