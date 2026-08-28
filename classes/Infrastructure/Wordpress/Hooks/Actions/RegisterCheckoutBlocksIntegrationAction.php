<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Blocks\CheckoutBlocksIntegration;

final class RegisterCheckoutBlocksIntegrationAction extends AbstractRegisterBlocksIntegrationAction
{
    /**
     * @return string
     */
    protected function getIntegrationName(): string
    {
        return CheckoutBlocksIntegration::NAME;
    }

    /**
     * @return IntegrationInterface
     */
    protected function createIntegration(): IntegrationInterface
    {
        return new CheckoutBlocksIntegration();
    }
}
