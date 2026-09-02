<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;

abstract class AbstractRegisterBlocksIntegrationAction extends AbstractAction
{
    private const ACTION = 'woocommerce_blocks_checkout_block_registration';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return ['integration_registry'];
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (!interface_exists(IntegrationInterface::class)) {
            return;
        }

        $registry = $args[0] ?? null;
        if (!$registry instanceof IntegrationRegistry) {
            return;
        }

        if ($registry->is_registered($this->getIntegrationName())) {
            return;
        }

        $registry->register($this->createIntegration());
    }

    /**
     * @return string
     */
    abstract protected function getIntegrationName(): string;

    /**
     * @return IntegrationInterface
     */
    abstract protected function createIntegration(): IntegrationInterface;
}
