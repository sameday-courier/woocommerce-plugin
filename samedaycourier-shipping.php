<?php

/**
 * Plugin Name: SamedayCourier Shipping
 * Plugin URI: http://sameday.ro
 * Description: SamedayCourier Shipping Method for WooCommerce
 * Version: 1.0.1
 * Author: SamedayCourier
 * Author URI: http://sameday.ro
 * License: GPL-3.0+
 * License URI: http://sameday.ro
 * Domain Path: /ro
 * Text Domain: sameday
 */

if (! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WooCommerce plugin is enabled
 */
if (! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), '') ) {
	exit;
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
require_once ( plugin_basename('lib/sameday-courier/src/Sameday/autoload.php') );
require_once ( plugin_basename('sql/sameday_create_db.php') );
require_once ( plugin_basename('sql/sameday_drop_db.php') );
require_once ( plugin_basename('sql/sameday_query_db.php') );
require_once ( plugin_basename('functions/functions.php') );

function samedaycourier_shipping_method() {

	if (class_exists('SamedayCourier_Shipping_Method')) {
		return true;
	}

	class SamedayCourier_Shipping_Method extends WC_Shipping_Method
	{
		private $configValidation;

		public function __construct( $instance_id = 0 )
		{
			parent::__construct( $instance_id );

			$this->id = 'samedaycourier';
			$this->method_title = __('SamedayCourier', 'samedaycourier');
			$this->method_description = __('Custom Shipping Method for SamedayCourier', 'samedaycourier');
			$this->configValidation = false;

			$this->init();
		}

		public function calculate_shipping( $package = array() )
		{
			$rate_1 = array(
				'id' => $this->id . '_1',
				'label' => $this->title . 1,
				'cost' => 19
			);

			$rate_2 = array(
				'id' => $this->id . '_2',
				'label' => $this->title . 2,
				'cost' => 30
			);

			if ($this->settings['enabled'] === 'no') {
				return;
			}

			// $availableServices = $this->getAvailableSerives();
			// foreach ( $availableServices as $service ) { $this->add_rate( $rate_1 ); }

			$this->add_rate( $rate_1 );

			$this->add_rate( $rate_2 );
		}

		private function init()
		{
			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'Enable', 'samedaycourier' ),
					'type' => 'checkbox',
					'description' => __( 'Enable this shipping.', 'samedaycourier' ),
					'default' => 'yes'
				),

				'title' => array(
					'title' => __( 'Title', 'samedaycourier' ),
					'type' => 'text',
					'description' => __( 'Title to be display on site', 'samedaycourier' ),
					'default' => __( 'SamedayCourier Shipping', 'samedaycourier' )
				),

				'user' => array(
					'title' => __( 'Username', 'samedaycourier' ),
					'type' => 'text',
					'description' => __( 'Username', 'samedaycourier' ),
					'default' => __( '', 'samedaycourier' )
				),

				'password' => array(
					'title' => __( 'Password', 'samedaycourier' ),
					'type' => 'password',
					'description' => __( 'Password', 'samedaycourier' ),
					'default' => __( '', 'samedaycourier' )
				),

				'is_testing' => array(
					'title' => __( 'Is testing', 'samedaycourier' ),
					'type' => 'checkbox',
					'description' => __( 'Disable this for production mode', 'samedaycourier' ),
					'default' => 'yes'
				),

				'estimated_cost' => array(
					'title' => __( 'Use estimated cost', 'samedaycourier' ),
					'type' => 'checkbox',
					'description' => __( 'This will show shipping cost calculated by Sameday Api for each service and show it on checkout page', 'samedaycourier' ),
					'default' => 'no'
				),
			);

			// Show on checkout:
			$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
			$this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'SamedayCourier', 'samedaycourier' );

			$this->init_settings();

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ));
		}

		public function process_admin_options()
		{
			$post_data = $this->get_post_data();

			$sameday = $this->initClient(
				$post_data['woocommerce_samedaycourier_user'],
				$post_data['woocommerce_samedaycourier_password'],
				$post_data['woocommerce_samedaycourier_is_testing']
			);

			if (! $this->configValidation ) {
				$this->configValidation = true;

				if ( $sameday->login() ) {
					return parent::process_admin_options();
				}
			}

			return false;
		}

		/**
		 * @param null $username
		 * @param null $password
		 * @param null $testing
		 *
		 * @return \Sameday\SamedayClient
		 */
		private function initClient( $username = null, $password = null, $testing = null )
		{
			if ($username === null && $password === null && $testing === null) {
				$username = $this->settings['username'];
				$password = $this->settings['password'];
				$testing = $this->settings['is_testing'];
			}

			return new \Sameday\SamedayClient(
				$username,
				$password,
				$testing ? 'https://sameday-api.demo.zitec.com' : 'https://api.sameday.ro',
				'WOOCOMMERCE ' . WC()->version,
				WC()->version
			);
		}
	}

	return false;
}



add_action( 'woocommerce_shipping_init', 'samedaycourier_shipping_method' );

function add_samedaycourier_shipping_method( $methods ) {
	$methods['samedaycourier'] = 'SamedayCourier_Shipping_Method';

	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_samedaycourier_shipping_method' );

// SamedayCourier Menu
add_action('admin_menu', 'register_submenu_sameday_courier');
function register_submenu_sameday_courier() {
	add_submenu_page( 'plugins.php', 'SamedayCourier', 'Sameday Services', 'manage_options', 'samedaycourier-services', 'samedaycourier_services' );
	add_submenu_page( 'plugins.php', 'SamedayCourier', 'Sameday Pickup Points', 'manage_options', 'samedaycourier-pickup-points', 'samedaycourier_pickup_points' );
}

function samedaycourier_services() {
	$services = getServices();
	$table = generateServiceTable( $services );

	echo $table;
}

function samedaycourier_pickup_points() {
	$pickupPoints = getPickupPoints();
	$table = generatePickupPointTable( $pickupPoints );

	echo $table;
}

register_activation_hook( __FILE__, 'samedaycourier_create_db' );

register_uninstall_hook( __FILE__, 'samedaycourier_drop_db');



