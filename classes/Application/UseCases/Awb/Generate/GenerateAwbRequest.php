<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Domain\Ports\GenerateAwbServiceProviderInterface;

final class GenerateAwbRequest
{
    private GenerateAwbItem $generateAwbItem;

    private GenerateAwbServiceProviderInterface $generateAwbServiceProvider;

    public function __construct(
        GenerateAwbItem $generateAwbItem,
        GenerateAwbServiceProviderInterface $generateAwbServiceProvider
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->generateAwbServiceProvider = $generateAwbServiceProvider;
    }

    public function getGenerateAwbItem(): GenerateAwbItem
    {
        return $this->generateAwbItem;
    }

    public function getGenerateAwbServiceProvider(): GenerateAwbServiceProviderInterface
    {
        return $this->generateAwbServiceProvider;
    }
}
