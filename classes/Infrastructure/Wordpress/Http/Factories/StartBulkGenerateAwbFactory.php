<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate\StartBulkGenerateAwb;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobIdGeneratorServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobStoreServiceProvider;

final class StartBulkGenerateAwbFactory
{
    /**
     * @return StartBulkGenerateAwb
     */
    public static function create(): StartBulkGenerateAwb
    {
        return new StartBulkGenerateAwb(
            new BulkJobStoreServiceProvider(),
            new BulkJobIdGeneratorServiceProvider(),
        );
    }
}
