<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\GenerateAwbMapper;

final class GenerateAwbRequestFactory
{
    /**
     * @var ShippingDtoFactory $shippingDtoFactory
     */
    private ShippingDtoFactory $shippingDtoFactory;

    /**
     * @var BillingDtoFactory $billingDtoFactory
     */
    private BillingDtoFactory $billingDtoFactory;

    /**
     * @var LockerDtoFactory $lockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @param ShippingDtoFactory|null $shippingDtoFactory
     * @param BillingDtoFactory|null $billingDtoFactory
     * @param LockerDtoFactory|null $lockerDtoFactory
     */
    public function __construct(
        ?ShippingDtoFactory $shippingDtoFactory = null,
        ?BillingDtoFactory $billingDtoFactory = null,
        ?LockerDtoFactory $lockerDtoFactory = null
    ) {
        $this->shippingDtoFactory = $shippingDtoFactory ?? new ShippingDtoFactory();
        $this->billingDtoFactory = $billingDtoFactory ?? new BillingDtoFactory();
        $this->lockerDtoFactory = $lockerDtoFactory ?? LockerDtoFactoryFactory::create();
    }

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self(
            new ShippingDtoFactory(),
            new BillingDtoFactory(),
            LockerDtoFactoryFactory::create()
        );
    }

    /**
     * @param DbHandler $dbHandler
     *
     * @return self
     */
    public static function createWithDbHandler(DbHandler $dbHandler): self
    {
        return new self(
            new ShippingDtoFactory(),
            new BillingDtoFactory(),
            LockerDtoFactoryFactory::create($dbHandler)
        );
    }

    /**
     * @param GenerateAwbMapper $mapper
     *
     * @return GenerateAwbRequest
     */
    public function fromMapper(GenerateAwbMapper $mapper): GenerateAwbRequest
    {
        return new GenerateAwbRequest(
            $mapper->orderId(),
            $mapper->serviceId(),
            $mapper->pickupPointId(),
            $mapper->shippingLines(),
            $this->shippingDtoFactory->fromInput($mapper->shipping()),
            $this->billingDtoFactory->fromInput($mapper->billing()),
            $this->lockerDtoFactory->fromInput($mapper->locker()),
            $mapper->hasOpenPackage(),
            $mapper->hasLockerFirstMile(),
            $mapper->packageType(),
            $mapper->awbPayment(),
            $mapper->insuranceValue(),
            $mapper->repayment(),
            $mapper->clientReference(),
            $mapper->observation(),
            $mapper->packageDimensions()
        );
    }

    /**
     * @return LockerDtoFactory
     */
    public function getLockerDtoFactory(): LockerDtoFactory
    {
        return $this->lockerDtoFactory;
    }
}
