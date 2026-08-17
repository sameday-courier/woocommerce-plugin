<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories;

use Sameday\Objects\ParcelDimensionsObject;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderAwbProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressArchive;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostAwbGenerationServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Woo\WooOrderAddressRepository;

final class AwbRequestFactory
{
    private ParcelDimensionsFactory $parcelDimensionsFactory;

    /**
     * @param ParcelDimensionsFactory|null $parcelDimensionsFactory
     */
    public function __construct(
        ?ParcelDimensionsFactory $parcelDimensionsFactory = null
    ) {
        $this->parcelDimensionsFactory = $parcelDimensionsFactory ?? new ParcelDimensionsFactory();
    }

    /**
     * @param ParcelDimensionsObject[]|null $parcelsDimensions
     */
    public function create(
        GenerateAwbItem $generateAwbItem,
        ?array $parcelsDimensions = null
    ): GenerateAwbRequest {
        $dbHandler = new DbHandler();
        $hdAddressParser = new WooSamedayShippingHdAddressParser();
        $stateCodeResolver = new WooStateCodeResolver(new WooCountriesHandler());
        $lockerDtoFactory = new LockerDtoFactory(new SamedayLockerRepository($dbHandler));
        $samedayAwbRepository = new SamedayAwbRepository($dbHandler);
        $samedayServiceRepository = new SamedayServiceRepository($dbHandler);
        $carrierServiceRules = new CarrierServiceRules($samedayServiceRepository);

        if (null === $parcelsDimensions) {
            $parcelsDimensions = $this->parcelDimensionsFactory->fromList(
                $generateAwbItem->getPackageDimensions()
            );
        }

        return new GenerateAwbRequest(
            $generateAwbItem,
            new CourierServiceProvider(),
            new SamedayPickupPointRepository($dbHandler),
            $samedayServiceRepository,
            new PostAwbGenerationServiceProvider(
                $dbHandler,
                new WooOrderShippingAddressUpdater(
                    new WooOrderAddressRepository($dbHandler),
                    new WooOrderShippingAddressArchive(),
                    $lockerDtoFactory,
                    $hdAddressParser,
                    $stateCodeResolver,
                ),
                $samedayAwbRepository
            ),
            $hdAddressParser,
            $stateCodeResolver,
            $parcelsDimensions,
            $lockerDtoFactory,
            new ShippingDtoFactory(),
            new BillingDtoFactory(),
            new GenerateAwbValidator(),
            $carrierServiceRules,
            new WooOrderAwbProvider($samedayAwbRepository),
            new SamedayCityRepository($dbHandler),
        );
    }
}
