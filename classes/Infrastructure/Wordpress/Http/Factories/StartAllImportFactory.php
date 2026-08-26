<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Import\StartAllImport\StartAllImport;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobIdGeneratorServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobStoreServiceProvider;

final class StartAllImportFactory
{
    /**
     * @return StartAllImport
     */
    public static function create(): StartAllImport
    {
        return new StartAllImport(
            new BulkJobStoreServiceProvider(),
            new BulkJobIdGeneratorServiceProvider(),
        );
    }
}
