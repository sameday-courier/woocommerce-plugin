<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters\Services;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters\AddPluginRowMetaFilter;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters\RegisterShippingMethodFilter;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters\ShippingMethodFullLabelFilter;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\FilterInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\RegistryHandlerInterface;

if (!defined('ABSPATH')) {
    exit;
}

class FiltersRegisterService implements RegistryHandlerInterface
{
    private const FILTERS = [
        AddPluginRowMetaFilter::class,
        RegisterShippingMethodFilter::class,
        ShippingMethodFullLabelFilter::class,
    ];

    public function register(): void
    {
        foreach (self::FILTERS as $filterClass) {
            $filter = new $filterClass();
            if ($filter instanceof FilterInterface) {
                add_filter(
                    $filter->getFilterName(),
                    static function (...$args) use ($filter) {
                        return $filter->handle(...$args);
                    },
                    $filter->getPriority(),
                    $filter->getAcceptedArgs()
                );
            }
        }
    }
}
