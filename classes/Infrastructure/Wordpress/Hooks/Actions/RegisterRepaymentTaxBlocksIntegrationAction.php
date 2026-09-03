<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Blocks\RepaymentTaxBlocksIntegration;

final class RegisterRepaymentTaxBlocksIntegrationAction extends AbstractRegisterBlocksIntegrationAction
{
    /**
     * @return string
     */
    protected function getIntegrationName(): string
    {
        return RepaymentTaxBlocksIntegration::NAME;
    }

    /**
     * @return IntegrationInterface
     */
    protected function createIntegration(): IntegrationInterface
    {
        return new RepaymentTaxBlocksIntegration();
    }
}
