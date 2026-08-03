<?php

declare(strict_types=1);

if (!defined( 'ABSPATH')) {
    exit;
}

/**
 * Plugin Name: SamedayCourier Shipping
 * Plugin URI: https://github.com/sameday-courier/woocommerce-plugin
 * Description: SamedayCourier Shipping Method for WooCommerce
 * Version: 2.0.0
 * Author: SamedayCourier
 * Author URI: https://www.sameday.ro/contact
 * License: GPL-3.0+
 * License URI: https://sameday.ro
 * Domain Path: /ro
 * Text Domain: sameday
 */

use SamedayCourier\Shipping\Application\Sql\PluginHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\RegistryHandler;

/**
 * Check if WooCommerce plugin is enabled
 */
if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )), true)) {
    exit;
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    add_action('admin_notices', static function () {
        echo '<div class="notice notice-error"><p>';
        echo 'SamedayCourier Shipping was not installed because autoloader is missing.';
        echo '</p></div>';
    });

    return;
}

require_once __DIR__ . '/vendor/autoload.php';

define('SAMEDAYCOURIER_SHIPPING_PLUGIN_PATH', plugin_dir_path(__FILE__));

RegistryHandler::register();

register_activation_hook(__FILE__, [PluginHandler::class, 'install']);
register_uninstall_hook(__FILE__, [PluginHandler::class, 'uninstall']);
