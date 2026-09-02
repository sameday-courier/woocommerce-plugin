<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\WooCommerceHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PluginPathHandler;
use WooCommerce;

final class WooHandler implements WooCommerceHandlerInterface
{
    /**
     * @var string|null
     */
    private ?string $pluginVersion = null;

    /**
     * @return WooCommerce
     */
    public function getWC(): object
    {
        return WC();
    }

    /**
     * @return string
     */
    public function getPlatformVersion(): string
    {
        return $this->getWC()->version;
    }

    /**
     * @return string
     */
    public function getPluginMainFile(): string
    {
        return PluginPathHandler::mainFile();
    }

    /**
     * @return string
     */
    public function getPluginVersion(): string
    {
        if (null !== $this->pluginVersion) {
            return $this->pluginVersion;
        }

        $pluginData = get_file_data(
            $this->getPluginMainFile(),
            ['Version' => 'Version'],
            'plugin'
        );

        $this->pluginVersion = $pluginData['Version'] ?? '';

        return $this->pluginVersion;
    }
}
