<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove\StartBulkRemoveAwb;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobIdGeneratorServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobStoreServiceProvider;

final class StartBulkRemoveAwbFactory
{
    /**
     * @return StartBulkRemoveAwb
     */
    public static function create(): StartBulkRemoveAwb
    {
        return new StartBulkRemoveAwb(
            new BulkJobStoreServiceProvider(),
            new BulkJobIdGeneratorServiceProvider(),
        );
    }
}
