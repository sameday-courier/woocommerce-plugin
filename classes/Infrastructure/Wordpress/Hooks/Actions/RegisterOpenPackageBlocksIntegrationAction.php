<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Blocks\OpenPackageBlocksIntegration;

final class RegisterOpenPackageBlocksIntegrationAction extends AbstractRegisterBlocksIntegrationAction
{
    /**
     * @return string
     */
    protected function getIntegrationName(): string
    {
        return OpenPackageBlocksIntegration::NAME;
    }

    /**
     * @return IntegrationInterface
     */
    protected function createIntegration(): IntegrationInterface
    {
        return new OpenPackageBlocksIntegration();
    }
}
