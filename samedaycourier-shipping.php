<?php

/**
 * Plugin Name: SamedayCourier Shipping
 * Plugin URI: https://github.com/sameday-courier/woocommerce-plugin
 * Description: SamedayCourier Shipping Method for WooCommerce
 * Version: 1.9.1
 * Author: SamedayCourier
 * Author URI: https://www.sameday.ro/contact
 * License: GPL-3.0+
 * License URI: https://sameday.ro
 * Domain Path: /ro
 * Text Domain: sameday
 */

use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PickupPoint\PickupPointContactPersonObject;
use Sameday\Objects\Service\OptionalTaxObject;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayDeletePickupPointRequest;
use Sameday\Requests\SamedayPostPickupPointRequest;
use Sameday\SamedayClient;

if (! defined( 'ABSPATH')) {
    exit;
}

/**
 * Check if WooCommerce plugin is enabled
 */
if (! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )), '')) {
    exit;
}

require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
require_once (plugin_basename('lib/sameday-courier/src/Sameday/autoload.php'));
require_once (plugin_basename('classes/samedaycourier-api.php'));
require_once (plugin_basename('classes/samedaycourier-helper-class.php'));
require_once (plugin_basename('classes/samedaycourier-sameday-class.php'));
require_once (plugin_basename('sql/sameday_create_db.php'));
require_once (plugin_basename('sql/sameday_drop_db.php'));
require_once (plugin_basename('sql/sameday_query_db.php'));
require_once (plugin_basename('classes/samedaycourier-services.php'));
require_once (plugin_basename('classes/samedaycourier-service-instance.php'));
require_once (plugin_basename('classes/samedaycourier-pickuppoints.php'));
require_once (plugin_basename('classes/samedaycourier-pickuppoint-instance.php'));
require_once (plugin_basename('classes/samedaycourier-lockers.php'));
require_once (plugin_basename('classes/samedaycourier-locker-instance.php'));
require_once (plugin_basename('views/add-awb-form.php'));
require_once (plugin_basename('views/awb-history-table.php'));
require_once (plugin_basename('views/add-new-parcel-form.php'));
require_once (plugin_basename('classes/samedaycourier-persistence-data-handler.php'));

// Start Shipping Method Class
function samedaycourier_shipping_method(): void
{
    if (!class_exists('SamedayCourier_Shipping_Method')) {
        class SamedayCourier_Shipping_Method extends WC_Shipping_Method
        {
	        /**
	         * SamedayCourier_Shipping_Method constructor.
	         *
	         * @param int $instance_id
	         */
            public function __construct($instance_id = 0)
            {
                parent::__construct($instance_id);

                $this->id = 'samedaycourier';
                $this->method_title = __('SamedayCourier', SamedayCourierHelperClass::TEXT_DOMAIN);
                $this->method_description = __(
                    'Custom Shipping Method for SamedayCourier',
                    SamedayCourierHelperClass::TEXT_DOMAIN
                );

                $this->supports = array(
                    'settings',
                    'shipping-zones',
                    'instance-settings'
                );

                $this->init();
            }

            /**
             * @param array $package
             */
            public function calculate_shipping($package = array()): void
            {
                if ($this->settings['enabled'] === 'no') {
                    return;
                }

                $useEstimatedCost = $this->settings['estimated_cost'];
                $estimatedCostExtraFee = (int) $this->settings['estimated_cost_extra_fee'];
                $useLockerMap = $this->settings['lockers_map'] === 'yes';
                $hostCountry = SamedayCourierHelperClass::getHostCountry();
                $destinationCountry = $package['destination']['country'] ?? SamedayCourierHelperClass::API_HOST_LOCALE_RO;

                $eligibleShippingServices = SamedayCourierHelperClass::ELIGIBLE_SERVICES;
                if ($destinationCountry !== $hostCountry) {
                    $eligibleShippingServices = SamedayCourierHelperClass::CROSSBORDER_ELIGIBLE_SERVICES;
                }

                $availableServices = array_filter(
                    SamedayCourierQueryDb::getAvailableServices(SamedayCourierHelperClass::isTesting()),
                    static function($row) use ($eligibleShippingServices) {
                        return in_array(
                            $row->sameday_code,
                            $eligibleShippingServices,
                            true
                        );
                    }
                );

	            $cartValue = WC()->cart->get_subtotal();
	            if (true === SamedayCourierHelperClass::isApplyFreeShippingAfterDiscount()) {
		            $cartValue = WC()->cart->get_cart_contents_total();
	            }

                $stateName = SamedayCourierHelperClass::convertStateCodeToName(
                    $package['destination']['country'],
                    $package['destination']['state']
                );

                if (!empty($availableServices)) {
                    foreach ($availableServices as $service) {
                        if ($service->sameday_code === SamedayCourierHelperClass::SAMEDAY_6H_CODE
                            && !in_array(
                               SamedayCourierHelperClass::removeAccents($stateName),
                               SamedayCourierHelperClass::ELIGIBLE_TO_6H_SERVICE,
                               true
                            )
                        ) {
                            continue;
                        }

                        if (SamedayCourierHelperClass::isOohDeliveryOption($service->sameday_code)) {
	                        if (null === $lockerMaxItems = $this->settings['locker_max_items'] ?? null) {
                                $lockerMaxItems = SamedayCourierHelperClass::DEFAULT_VALUE_LOCKER_MAX_ITEMS;
                            }

                            if (count(WC()->cart->get_cart()) > ((int) $lockerMaxItems)) {
                                continue;
                            }
                        }

                        $price = $service->price;

                        if (
	                        '' !== $package['destination']['city']
                            && '' !== $stateName
	                        && '' !== $package['destination']['address']
                            && $useEstimatedCost !== 'no'
                        ) {
                            $estimatedCost = $this->getEstimatedCost($package['destination'], $service->sameday_id);

                            if (isset($estimatedCost)) {

                                if (($useEstimatedCost === 'yes') || ($useEstimatedCost === 'btfp' && $service->price < $estimatedCost)) {
                                    $price = $estimatedCost;
                                }

                                if ($estimatedCostExtraFee > 0) {
                                    $price += (float) number_format($price * ($estimatedCostExtraFee /100), 2);
                                }
                            }
                        }

	                    if ($service->price_free !== null && ($cartValue > $service->price_free)) {
		                    $price = .0;
	                    }

                        $rate = array(
                            'id' => sprintf('%s:%s:%s', $this->id, $service->sameday_id, $service->sameday_code),
                            'label' => $service->name,
                            'cost' => $price,
                            'meta_data' => array(
                                'service_id' => $service->sameday_id,
                                'service_code' => $service->sameday_code
                            )
                        );

                        if ((false === $useLockerMap)
                            && ($service->sameday_code === SamedayCourierHelperClass::LOCKER_NEXT_DAY_CODE)
                        ) {
                            $this->syncLockers();
                            $rate['lockers'] = SamedayCourierQueryDb::getLockers(SamedayCourierHelperClass::isTesting());
                        }

                        $this->add_rate($rate);
                    }
                }
            }

            /**
             * @return void
             */
            private function syncLockers(): void
            {
                $time = time();

                $ltSync = $this->settings['sameday_sync_lockers_ts'];

                if ($time > ($ltSync + 86400)) {
                    (new Sameday())->updateLockersList();
                }
            }

            /**
             * @param $address
             * @param $serviceId
             *
             * @return float|null
             */
            private function getEstimatedCost($address, $serviceId): ?float
            {
                $pickupPointId = SamedayCourierQueryDb::getDefaultPickupPointId(SamedayCourierHelperClass::isTesting());
                $weight = SamedayCourierHelperClass::convertWeight(WC()->cart->get_cart_contents_weight()) ?: .1;
                $state = SamedayCourierHelperClass::convertStateCodeToName($address['country'], $address['state']);
                $city = SamedayCourierHelperClass::removeAccents($address['city']);
                $currency = SamedayCourierHelperClass::CURRENCY_MAPPER[$address['country']];

                $optionalServices = SamedayCourierQueryDb::getServiceIdOptionalTaxes(
                        $serviceId,
                        SamedayCourierHelperClass::isTesting()
                );
                $serviceTaxIds = array();
                if (WC()->session->get('open_package') === 'yes') {
                    foreach ($optionalServices as $optionalService) {
                        if ($optionalService->getCode() === SamedayCourierHelperClass::OPEN_PACKAGE_OPTION_CODE
                            && $optionalService->getPackageType()->getType() === PackageType::PARCEL
                        ) {
                            $serviceTaxIds[] = $optionalService->getId();
                            break;
                        }
                    }
                }

                // Check if the client has to pay anything as repayment value
                $repaymentAmount = WC()->cart->subtotal;
	            $paymentMethod = WC()->session->get('payment_method');
	            if (isset($paymentMethod) && ($paymentMethod !== SamedayCourierHelperClass::CASH_ON_DELIVERY)) {
		            $repaymentAmount = 0;
                }

                $estimateCostRequest = new Sameday\Requests\SamedayPostAwbEstimationRequest(
                    $pickupPointId,
                    null,
                    new Sameday\Objects\Types\PackageType(
                        Sameday\Objects\Types\PackageType::PARCEL
                    ),
                    [new ParcelDimensionsObject($weight)],
                    $serviceId,
                    new Sameday\Objects\Types\AwbPaymentType(
                        Sameday\Objects\Types\AwbPaymentType::CLIENT
                    ),
                    new Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject(
                        ucwords(strtolower($city)) !== 'Bucuresti' ? $city : 'Sector 1',
                        $state,
                        ltrim($address['address']) !== '' ? ltrim($address['address']) : '123',
                        null,
                        null,
                        null,
                        null
                    ),
                    0,
	                $repaymentAmount,
                    null,
                    $serviceTaxIds,
                    $currency
                );

                $sameday = new Sameday\Sameday(
                    SamedayCourierApi::initClient(
                        $this->settings['user'],
                        $this->settings['password'],
                        SamedayCourierHelperClass::getApiUrl()
                    )
                );

                try {
	                return $sameday->postAwbEstimation($estimateCostRequest)->getCost();
                } catch (Exception $exception) {
                    return null;
                }
            }

            private function init(): void
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'type' => 'checkbox',
                        'description' => __('Enable this shipping.', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => 'yes'
                    ),

                    'title' => array(
                        'title' => __('Title', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'type' => 'text',
                        'description' => __('Title to be display on site', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => __('SamedayCourier Shipping', SamedayCourierHelperClass::TEXT_DOMAIN)
                    ),

                    'user' => array(
                        'title' => __('Username', SamedayCourierHelperClass::TEXT_DOMAIN) . ' *',
                        'type' => 'text',
                        'description' => __('Username', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => __('')
                    ),

                    'password' => array(
                        'title' => __('Password', SamedayCourierHelperClass::TEXT_DOMAIN) . ' *',
                        'type' => 'password',
                        'description' => __('Password', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => __('')
                    ),

                    'default_label_format' => array(
                        'title'   => __('Default label format', SamedayCourierHelperClass::TEXT_DOMAIN) . ' *',
                        'default' => 'A4',
                        'type'    => 'select',
                        'options' => [
                            'A4' => __(Sameday\Objects\Types\AwbPdfType::A4, SamedayCourierHelperClass::TEXT_DOMAIN),
                            'A6' => __(Sameday\Objects\Types\AwbPdfType::A6, SamedayCourierHelperClass::TEXT_DOMAIN),
                        ],
                        'description' => __('Awb paper format', SamedayCourierHelperClass::TEXT_DOMAIN)
                    ),

                    'estimated_cost' => array(
                        'title'   => __('Use estimated cost', SamedayCourierHelperClass::TEXT_DOMAIN) . ' *',
                        'default' => 'no',
                        'type'    => 'select',
                        'options' => [
                            'no' => __('Never', SamedayCourierHelperClass::TEXT_DOMAIN),
                            'yes' => __('Always', SamedayCourierHelperClass::TEXT_DOMAIN),
                            'btfp' => __('If its cost is bigger than fixed price', SamedayCourierHelperClass::TEXT_DOMAIN)
                        ],
                        'description' => __('This is the shipping cost calculated by Sameday Api for each service. <br/> 
                            Never* You choose to display only the fixed price that you set for each service<br/>
                            Always* You choose to display only the price estimated by SamedayCourier API<br/>
                            If its cost is bigger than fixed price* You choose to display the cost estimated by 
                            SamedayCourier Api only in the situation that this cost exceed the fixed price set by you for each service.
                        ', SamedayCourierHelperClass::TEXT_DOMAIN)
                    ),

                    'estimated_cost_extra_fee' => array(
                        'title' => __('Extra fee', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'type' => 'number',
                        'css' => 'width:100px;',
                        'description' => __('Apply extra fee on estimated cost. This is a % value. <br/> If you don\'t want to add extra fee on estimated cost value, such as T.V.A. leave this field blank or 0', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'custom_attributes' => array(
                            'min' => 0,
                            'onkeypress' => 'return (event.charCode !=8 && event.charCode == 0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))',
                            'data-placeholder' => __('Extra fee', SamedayCourierHelperClass::TEXT_DOMAIN)
                        ),
                        'default' => 0
                    ),

                    'repayment_tax_label' => array(
                        'title' => __('Repayment tax label', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'type' => 'text',
                        'description' => __('Label for repayment tax. This appear in checkout page.', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => __('', SamedayCourierHelperClass::TEXT_DOMAIN)
                    ),

                    'repayment_tax' => array(
                        'title' => __('Repayment tax', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'type' => 'number',
                        'description' => __('Add extra fee on checkout.', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => __('', SamedayCourierHelperClass::TEXT_DOMAIN)
                    ),


                    'open_package_status' => array(
                        'title' => __('Open package status', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'type' => 'checkbox',
                        'description' => __('Enable this option if you want to offer your customers the opening of the package at delivery time.', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => 'no'
                    ),

                    'discount_free_shipping' => array(
                        'title' => __('Free shipping after discount', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'type' => 'checkbox',
                        'description' => __(
                            'Enable this option if you want to apply free shipping to be calculated after discount.
                            Otherwise the free shipping will be apply without taking into account the applied discount.
                            This field is relevant if you choose free delivery price option.',
                            SamedayCourierHelperClass::TEXT_DOMAIN
                        ),
                        'default' => 'no'
                    ),

                    'open_package_label' => array(
                        'title' => __('Open package label', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'type' => 'text',
                        'description' => __('This appear in checkout page', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => __('', SamedayCourierHelperClass::TEXT_DOMAIN)
                    ),

                    'locker_max_items' => array(
	                    'title' => __('Locker max. items', SamedayCourierHelperClass::TEXT_DOMAIN),
	                    'type' => 'number',
	                    'description' => __('The maximum amount of items accepted inside the locker', SamedayCourierHelperClass::TEXT_DOMAIN),
	                    'default' => SamedayCourierHelperClass::DEFAULT_VALUE_LOCKER_MAX_ITEMS
                    ),

                    'lockers_map' => array(
                        'title'   => __('Show locker map method', SamedayCourierHelperClass::TEXT_DOMAIN),
                        'default' => 'no',
                        'type'    => 'select',
                        'options' => [
                            'no' => __('Drop-down list', SamedayCourierHelperClass::TEXT_DOMAIN),
                            'yes' => __('Interactive Map', SamedayCourierHelperClass::TEXT_DOMAIN),
                        ]
                    ),

                    'is_testing' => array(
	                    'title' => __('Env. Mode', SamedayCourierHelperClass::TEXT_DOMAIN),
	                    'type' => 'select',
	                    'description' => __('The value of this field will be appear automatically after you complete the authentication', SamedayCourierHelperClass::TEXT_DOMAIN),
	                    'default' => 2,
	                    'disabled' => true,
                        'options' => array(
                            SamedayCourierHelperClass::API_PROD => __('Prod', SamedayCourierHelperClass::TEXT_DOMAIN),
                            SamedayCourierHelperClass::API_DEMO => __('Demo', SamedayCourierHelperClass::TEXT_DOMAIN),
                            2 => '',
                        ),
                    ),

                    'host_country' => array(
	                    'title' => __('Env. Host Country', SamedayCourierHelperClass::TEXT_DOMAIN),
	                    'type' => 'select',
	                    'description' => __('The value of this field will be appear automatically after you complete the authentication', SamedayCourierHelperClass::TEXT_DOMAIN),
	                    'default' => 'none',
	                    'disabled' => true,
	                    'options' => array(
		                    SamedayCourierHelperClass::API_HOST_LOCALE_RO => __(SamedayCourierHelperClass::API_HOST_LOCALE_RO, SamedayCourierHelperClass::TEXT_DOMAIN),
		                    SamedayCourierHelperClass::API_HOST_LOCALE_HU => __(SamedayCourierHelperClass::API_HOST_LOCALE_HU, SamedayCourierHelperClass::TEXT_DOMAIN),
                            SamedayCourierHelperClass::API_HOST_LOCALE_BG => __(SamedayCourierHelperClass::API_HOST_LOCALE_BG, SamedayCourierHelperClass::TEXT_DOMAIN),
		                    'none' => '',
	                    ),
                    ),
                );

                // Show on checkout:
                $this->enabled = $this->settings['enabled'] ?? 'yes';
                $this->title = $this->settings['title'] ?? __('SamedayCourier', SamedayCourierHelperClass::TEXT_DOMAIN);

                $this->init_settings();

                add_action( 'woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            public function process_admin_options(): void
            {
                $post_data = $this->get_post_data();

                $isLogged = false;
                $envModes = SamedayCourierHelperClass::getEnvModes();
                foreach ($envModes as $hostCountry => $envModesByHosts) {
	                if ($isLogged === true) {
                        break;
                    }

	                foreach ($envModesByHosts as $apiUrl) {
		                $sameday = SamedayCourierApi::initClient(
			                $post_data['woocommerce_samedaycourier_user'],
			                $post_data['woocommerce_samedaycourier_password'],
			                $apiUrl
		                );

		                try {
			                if ($sameday->login()) {
				                $isTesting = (int) (SamedayCourierHelperClass::API_DEMO === array_keys($envModesByHosts, $apiUrl)[0]);
				                $post_data['woocommerce_samedaycourier_is_testing'] = $isTesting;
				                $post_data['woocommerce_samedaycourier_host_country'] = $hostCountry;
				                $isLogged = true;

                                // If already exist a token from previews auth, cancel it
                                update_option('woocommerce_samedaycourier_settings_' . SamedayClient::KEY_TOKEN, [SamedayClient::KEY_TOKEN => null]);
                                update_option('woocommerce_samedaycourier_settings_' . SamedayClient::KEY_TOKEN_EXPIRES, [SamedayClient::KEY_TOKEN_EXPIRES => null]);

                                break;
			                }
		                } catch (Exception $exception) {
                            continue;
                        }
                    }
                }

                if ($isLogged) {
                    $this->set_post_data($post_data);

                    parent::process_admin_options();
                } else {

	                WC_Admin_Settings::add_error( __( 'Invalid username/password combination provided! Settings have not been changed!'));
                }
            }

            public function admin_options(): void
            {
                $serviceUrl = admin_url() . 'edit.php?post_type=page&page=sameday_services';
                $pickupPointUrl = admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points';
                $lockerUrl = admin_url() . 'edit.php?post_type=page&page=sameday_lockers';
                $buttons = '<a class="button-primary" id="import_all"> '. __('Import all') . ' </a> 
                            <a href="' . $serviceUrl . '" class="button-primary"> '. __('Services') .' </a> 
                            <a href="' . $pickupPointUrl . '" class="button-primary"> '. __('Pickup-point') .' </a> 
                            <a href="' . $lockerUrl . '" class="button-primary"> '. __('Lockers') .' </a>';

                echo parent::admin_options() . $buttons;
            }
        }
    }
}
// End of Shipping Method Class

// Add Module Custom Actions
add_action('admin_init','load_lockers_sync');
function load_lockers_sync() {
    global $pagenow;

    $section = $_GET['section'] ?? null;
    if ('samedaycourier' === $section) {
        wp_enqueue_script('jquery');
        wp_enqueue_script( 'lockers-sync-admin', plugin_dir_url( __FILE__ ). 'assets/js/sameday_admin.js', ['jquery']);
        wp_enqueue_script( 'select2-script', plugin_dir_url( __FILE__ ). 'assets/js/select2.js', ['jquery']);
        wp_enqueue_style( 'sameday-admin-style', plugin_dir_url( __FILE__ ). 'assets/css/sameday_admin.css' );
        wp_enqueue_style( 'select2-style', plugin_dir_url( __FILE__ ). 'assets/css/select2.css' );
    }

    if ($pagenow === 'post.php' || $pagenow === 'admin.php') {
        wp_enqueue_script('jquery');
        wp_enqueue_script( 'lockerpluginsdk','https://cdn.sameday.ro/locker-plugin/lockerpluginsdk.js', ['jquery']);
        wp_enqueue_script( 'lockers-sync-admin', plugin_dir_url( __FILE__ ). 'assets/js/lockers_sync_admin.js', ['jquery']);
        wp_enqueue_script( 'select2-script', plugin_dir_url( __FILE__ ). 'assets/js/select2.js', ['jquery']);
        wp_enqueue_style( 'sameday-admin-style', plugin_dir_url( __FILE__ ). 'assets/css/sameday_admin.css' );
        wp_enqueue_style( 'select2-style', plugin_dir_url( __FILE__ ). 'assets/css/select2.css' );
    }
}

// Shipping Method init.
add_action('woocommerce_shipping_init', 'samedaycourier_shipping_method');

function add_samedaycourier_shipping_method($methods) {
    $methods['samedaycourier'] = 'SamedayCourier_Shipping_Method';

    return $methods;
}

add_filter('woocommerce_shipping_methods', 'add_samedaycourier_shipping_method');

// Plugin settings.
add_action('plugins_loaded', function () {
    SamedayCourierServiceInstance::get_instance();
    SamedayCourierPickupPointInstance::get_instance();
    SamedayCourierLockerInstance::get_instance();
});

add_action('admin_post_refresh_services', function () {
    return (new Sameday())->refreshServices();
});

add_action('admin_post_refresh_pickup_points', function () {
    return (new Sameday())->refreshSamedayPickupPoints();
});

add_action('admin_post_refresh_lockers', function () {
    return (new Sameday())->refreshSamedayLockers();
});

add_action('wp_ajax_all_import', static function (): void {
	try {
		(new Sameday())->refreshServices();
    } catch (Exception $exception) {}
	try {
		(new Sameday())->refreshSamedayPickupPoints();
    } catch (Exception $exception) {}
	try {
		(new Sameday())->refreshSamedayLockers();
	} catch (Exception $exception) {}
});

add_action('wp_ajax_change_locker', function() {
    if (null !== $orderId = $_POST['orderId']) {
	    try {
		    SamedayCourierHelperClass::addLockerToOrderData($orderId, $_POST['locker']);
	    } catch (Exception $exception) {}
    }
});

add_action('wp_ajax_change_counties', function() {
    if (!isset($_POST['countyId'])) {
        return [];
    }
    wp_send_json(SamedayCourierHelperClass::getCities($_POST['countyId'])); die();
});

add_action('wp_ajax_send_pickup_point', static function () {
    if (null === $formData = $_POST['data'] ?? null) {
        wp_send_json_error('Invalid data', 400);
        die();
    }
    if (false === wp_verify_nonce($formData['_wpnonce'], 'add-pickup-point')) {
        wp_send_json_error('Forbidden action', 403);
        die();
    }

    $requiredFields = [
        'pickupPointCountry',
        'pickupPointCounty',
        'pickupPointCity',
        'pickupPointAddress',
        'pickupPointPostalCode',
        'pickupPointAlias',
        'pickupPointContactPersonName',
        'pickupPointContactPersonPhone',
    ];

    foreach ($requiredFields as $field) {
        if (empty($formData[$field])) {
            wp_send_json_error("Missing or invalid field: $field", 400);
            die();
        }
    }

    try {
        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
            SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
        ));
    } catch (SamedaySDKException|Exception $exception) {
        error_log($exception->getMessage());
        wp_send_json_error($exception->getMessage(), 500);
        die();
    }

    try {
        $response = $sameday->postPickupPoint(new SamedayPostPickupPointRequest(
            $formData['pickupPointCountry'],
            $formData['pickupPointCounty'],
            $formData['pickupPointCity'],
            $formData['pickupPointAddress'],
            $formData['pickupPointPostalCode'],
            $formData['pickupPointAlias'],
            [new PickupPointContactPersonObject(
                $formData['pickupPointContactPersonName'],
                $formData['pickupPointContactPersonPhone'],
                true
            )],
            (bool) $formData['default'] ?? false
        ));

        wp_send_json_success($response->getPickupPointId());
    } catch (SamedayBadRequestException $e) {
        $noticeMessage = SamedayCourierHelperClass::parseAwbErrors($e->getErrors());
        SamedayCourierHelperClass::addFlashNotice('add_awb_notice', $noticeMessage, 'error', true);

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    } catch (Exception $e) {
        SamedayCourierHelperClass::addFlashNotice('add_awb_notice', $e->getMessage(), 'error',true);

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    }

    return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
});

add_action('wp_ajax_delete_pickup_point', function() {
    $formData = $_POST['data'] ?? [];

    if (false === wp_verify_nonce($formData['_wpnonce'], 'delete-pickup-point')) {
        wp_send_json_error('Forbidden action !', 403);
        die();
    }
    if (null === $sameday_id = $formData['sameday_id'] ?? null) {
        wp_send_json_error('Invalid data format', 400);
        die();
    }

    try {
        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
            SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
        ));
    } catch (SamedaySDKException|Exception $e) {
        SamedayCourierHelperClass::addFlashNotice('add_awb_notice', $e->getMessage(), 'error',true);

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    }

    try {
        $response = $sameday->deletePickupPoint(new SamedayDeletePickupPointRequest($sameday_id));
        wp_send_json_success($response);
    } catch (Exception $exception) {
        error_log('Error in Sameday deletePickupPoint: ' . $exception->getMessage());
        wp_send_json_error('Failed to delete pickup point: ' . $exception->getMessage(), 500);

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    }

    return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
});

add_action('admin_post_edit_service', function() {
    return (new Sameday())->editService();
});

add_action('admin_post_add_awb', function () {
    $postFields = SamedayCourierHelperClass::sanitizeInputs($_POST);
    $orderDetails = wc_get_order($postFields['samedaycourier-order-id']);
    if (empty($orderDetails)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    $data = array_merge($postFields, $orderDetails->get_data());
    
    return (new Sameday())->postAwb($data);
});

add_action('admin_post_remove-awb', function () {
    $awb = SamedayCourierQueryDb::getAwbForOrderId((int) sanitize_key($_POST['order-id']));
    $nonce = $_POST['_wpnonce'];
    if (empty($awb)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    return (new Sameday())->removeAwb($awb, $nonce);
});

add_action('admin_post_show-awb-pdf', function (){
    $orderId = (int) sanitize_key($_POST['order-id']);
	$nonce = $_POST['_wpnonce'];
    if (!isset($orderId)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    return (new Sameday())->showAwbAsPdf($orderId, $nonce);
});

add_action('admin_post_add-new-parcel', function() {
    $postFields = SamedayCourierHelperClass::sanitizeInputs($_POST);
    if (empty($postFields)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    return (new Sameday())->addNewParcel($postFields);
});

// Open Package :
function wps_sameday_shipping_options_layout() {
    // If you are not in Checkout page don't do anything
    if (!is_checkout()) {
        return;
    }

    $service = SamedayCourierQueryDb::getServiceSamedayByCode(
        SamedayCourierHelperClass::getChosenShippingMethodCode(),
        SamedayCourierHelperClass::isTesting()
    );

    /** @var OptionalTaxObject[] $optionalTaxes */
    $optionalTaxes = [];
    if ($service) {
        $optionalTaxes = unserialize($service->service_optional_taxes, ['']);
        if (!$optionalTaxes) {
            $optionalTaxes = [];
        }
    }

    $taxOpenPackage = 0;
    foreach ($optionalTaxes as $optionalTax) {
        if ($optionalTax->getCode() === SamedayCourierHelperClass::OPEN_PACKAGE_OPTION_CODE) {
            $taxOpenPackage = $optionalTax->getId();
        }
    }

    if ($taxOpenPackage
        && SamedayCourierHelperClass::getSamedaySettings()['open_package_status'] === "yes"
    ) {
        ?>
            <tr class="shipping-pickup-store">
                <th></th>
                <td>
                    <ul id="shipping_method" class="woocommerce-shipping-methods" style="list-style-type:none;">
                        <li>
                            <?php
                                woocommerce_form_field('open_package',
                                [
                                    'type' => 'checkbox',
                                    'class' => array('input-checkbox'),
                                    'id' => 'sameday_open_package',
                                    'label' => SamedayCourierHelperClass::getSamedaySettings()['open_package_label'],
                                    'required' => false,
                                ],
                                WC()->session->get('open_package') === 'yes'
                                );
                            ?>
                        </li>
                    </ul>
                </td>
            </tr>
        <?php
    }
}
add_action('woocommerce_review_order_after_shipping', 'wps_sameday_shipping_options_layout');

// Enabling, disabling and refreshing session shipping methods data
add_action( 'woocommerce_checkout_update_order_review', 'refresh_sameday_shipping_methods', 10, 1);
function refresh_sameday_shipping_methods() {
    foreach (WC()->cart->get_shipping_packages() as $package_key => $package) {
	    $package['package_hash'] = 'wc_ship_' . md5( wp_json_encode($package) . WC_Cache_Helper::get_transient_version('shipping'));
        WC()->session->set('shipping_for_package_' . $package_key, $package);
    }

    WC()->cart->calculate_shipping();
}

add_action('wp_ajax_woo_sameday_post_ajax_data', 'woo_sameday_post_ajax_data');
add_action('wp_ajax_nopriv_woo_sameday_post_ajax_data', 'woo_sameday_post_ajax_data');

function woo_sameday_post_ajax_data(): void {
    if (false === wp_verify_nonce($_POST['samedayNonce'], 'sameday-post-data')) {
        die('Invalid Request !');
    }

    if (null !== $locker = $_POST['locker'] ?? null) {
        if (is_array($locker)) {
            WC()->session->set('locker', SamedayCourierHelperClass::sanitizeLocker($locker));
        } else {
            WC()->session->set('locker', (int) $locker);
        }

        return;
    }

    if (null !== $openPackage = $_POST['open_package'] ?? null) {
	    WC()->session->set('open_package', SamedayCourierHelperClass::sanitizeInput($openPackage));

        return;
    }

    if (isset($_POST['payment_method'])) {
	    WC()->session->set('payment_method', SamedayCourierHelperClass::sanitizeInput($_POST['payment_method']));

        return;
    }

    die();
}

add_action('woocommerce_cart_calculate_fees', 'checkout_repayment_tax', 100);
function checkout_repayment_tax() {
  global $woocommerce;

	if (!defined( 'DOING_AJAX') && is_admin()) {
		return;
    }

	$repayment_tax = (int) (SamedayCourierHelperClass::getSamedaySettings()['repayment_tax'] ?? null);

    if ($repayment_tax > 0
        && SamedayCourierHelperClass::CASH_ON_DELIVERY === WC()->session->get('chosen_payment_method')
    ) {
        $repayment_tax_label = SamedayCourierHelperClass::getSamedaySettings()['repayment_tax_label'] ?? __('Repayment tax', SamedayCourierHelperClass::TEXT_DOMAIN);
        $woocommerce->cart->add_fee($repayment_tax_label, $repayment_tax, true, '');
    }
}

// LOCKER :
function wps_locker_row_layout() {
    $serviceCode = SamedayCourierHelperClass::getChosenShippingMethodCode();

    $shipTo = null;
    if (null !== $lockerSession = WC()->session->get('locker')) {
        try {
            $lockerSession = json_decode($lockerSession, false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {}

        $shipTo = sprintf(
                '%s <br/> %s',
            $lockerSession->name ?? '',
            $lockerSession->address ?? ''
        );
    }

    if ((SamedayCourierHelperClass::isOohDeliveryOption($serviceCode)) && is_checkout()) { ?>
        <?php if ((SamedayCourierHelperClass::getSamedaySettings()['lockers_map'] ?? null) === "yes") { ?>
            <tr class="shipping-pickup-store">
                <td><strong><?php echo __('Sameday Locker', SamedayCourierHelperClass::TEXT_DOMAIN) ?></strong></td>
                <th>
                    <button type="button" class="button alt sameday_select_locker"
                        id="select_locker"
                        data-username='<?php echo SamedayCourierHelperClass::getSamedaySettings()['user']; ?>'
                        data-country='<?php echo SamedayCourierHelperClass::getSamedaySettings()['host_country']; ?>'
                    >
                        <?php echo __('Show Locations Map', SamedayCourierHelperClass::TEXT_DOMAIN) ?>
                    </button>
                </th>
            </tr>
            <?php if (null !== $shipTo) { ?>
                <tr id="showSamedayLockerDetailsCheckoutLine" class="shipping-pickup-store">
                    <td><strong> <?= __('Ship to', SamedayCourierHelperClass::TEXT_DOMAIN) ?> </strong></td>
                    <th><span id="showLockerDetails"><?= $shipTo ?></span></th>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <?php
                $cities = SamedayCourierQueryDb::getCities(SamedayCourierHelperClass::isTesting());
                $lockers = array();
                foreach ($cities as $city) {
                    if (null !== $city->city) {
                        $lockers[$city->city . ' (' . $city->county . ')'] = SamedayCourierQueryDb::getLockersByCity(
                            $city->city,
                            SamedayCourierHelperClass::isTesting()
                        );
                    }
                }

                $lockerOptions = '';
                foreach ($lockers as $city => $cityLockers) {
                    $optionGroup = "<optgroup label='$city' style='font-size: 13px;'></optgroup>";
                    $options = '';
                    foreach ($cityLockers as $locker) {
                        $lockerDetails = "<span>" . $locker->name . ' - ' . $locker->address . "</span>";
                        $isSelected = null;
                        if ((int) WC()->session->get('locker') === (int) $locker->locker_id) {
                            $isSelected = "selected='selected'";
                        }
                        $options .= sprintf(
                            "<option value='%s' style='font-size: 9px' %s> %s </option>",
                            $locker->locker_id,
                            $isSelected,
                            $lockerDetails
                        );
                    }

                    $lockerOptions .= $optionGroup . $options;
                }
            ?>
                <tr>
                    <th><label for="shipping-pickup-store-select"></label></th>
                    <td>
                        <select name="locker_id" id="shipping-pickup-store-select" style="width: 100%; height: 25px; font-size: 14px">
                            <option value="" style="font-size: 13px">
                                <?= __('Select easyBox', SamedayCourierHelperClass::TEXT_DOMAIN) ?>
                            </option>
                            <?php echo $lockerOptions; ?>
                        </select>
                    </td>
                </tr>
        <?php } ?>
    <?php }
}
add_action('woocommerce_review_order_after_shipping', 'wps_locker_row_layout');

// When POST Order Form
add_action('woocommerce_blocks_checkout_order_processed', static function ($order): void {

    if (SamedayCourierHelperClass::isOohDeliveryOption(SamedayCourierHelperClass::getChosenShippingMethodCode())) {
        try {
            SamedayCourierHelperClass::addLockerToOrderData(
                $order->get_id(),
                WC()->session->get('locker')
            );
        } catch (Exception $exception) {}
    }

    if ("yes" === WC()->session->get('open_package')) {
        update_post_meta($order->get_id(), '_sameday_shipping_open_package_option', 1, true);
        // After store, remove it from Session
        WC()->session->set('open_package', 'no');
    }
});

add_action('woocommerce_checkout_order_processed', static function ($orderId): void {

    if (SamedayCourierHelperClass::isOohDeliveryOption(SamedayCourierHelperClass::getChosenShippingMethodCode())) {
        try {
            SamedayCourierHelperClass::addLockerToOrderData(
                $orderId,
                WC()->session->get('locker')
            );
        } catch (Exception $exception) {}
    }

    if ("yes" === WC()->session->get('open_package')) {
        update_post_meta($orderId, '_sameday_shipping_open_package_option', 1, true);
        // After store, remove it from Session
        WC()->session->set('open_package', 'no');
    }
});

/**
 ** Add external JS file for Lockers
 **/
add_action(
    'wp_enqueue_scripts',
    static function () {
        global $wp;
        if (empty($wp->query_vars['order-pay'] )
            && !isset($wp->query_vars['order-received'])
            && is_checkout()
        ) {
            wp_enqueue_script(
                'prod-locker-plugin',
                'https://cdn.sameday.ro/locker-plugin/lockerpluginsdk.js'
            );
            wp_enqueue_script(
                'helper',
                plugin_dir_url( __FILE__ ) . 'assets/js/helper.js',
                ['jquery'],
                false,
                true
            );
            wp_enqueue_script(
                'lockers_script',
                plugin_dir_url( __FILE__ ) . 'assets/js/lockers_sync.js',
                ['jquery'],
                false,
                true
            );
            wp_enqueue_script(
                'open_package_script',
                plugin_dir_url( __FILE__ ) . 'assets/js/open_package_script.js',
                ['jquery'],
                false,
                true
            );

            wp_localize_script('helper', 'samedayVars', [
                'samedayNonce' => wp_create_nonce('sameday-post-data'),
            ]);
        }
    },
    99999
);

/**
 ** Order detail styles
 **/
function wps_locker_style() {
    ?>
    <style type="text/css">
        #showLockerDetails{
            font-size: 13px; 
            font-weight: bold;
            line-height: 22px;
        }
        .shipping-pickup-store td .title {
            float: left;
            line-height: 30px;
        }
        .shipping-pickup-store td span.text {
            float: right;
        }
        .shipping-pickup-store td span.description {
            clear: both;
        }
        .shipping-pickup-store td > span:not([class*="select"]) {
            display: block;
            font-size: 11px;
            font-weight: normal;
            line-height: 1.3;
            margin-bottom: 0;
            padding: 6px 0;
            text-align: justify;
        }

        [aria-labelledby="select2-shipping-pickup-store-select-container"]{
            height: 100% !important;
        }
        #locker_name, #locker_address{
            width:100%;
            border:0;
            pointer-events: none;
            resize: none;
        }
        #select2-shipping-pickup-store-select-container{
            word-wrap: break-word !important;
            text-overflow: inherit !important;
            white-space: normal !important;
        }

        #select2-shipping-pickup-store-select-results{
            max-height: 250px;
            overflow-y: auto;
            font-size: 12px;
        }

    </style>
    <?php
}
add_action('wp_head', 'wps_locker_style');
// Locker !

add_action('admin_head', function () {
    if (isset($_GET["add-awb"])){
        if ($_GET["add-awb"] === "error") {
            SamedayCourierHelperClass::showFlashNotice('add_awb_notice');
        }

        if ($_GET["add-awb"] === "success") {
            SamedayCourierHelperClass::printFlashNotice('success', __("Awb was successfully generated !", SamedayCourierHelperClass::TEXT_DOMAIN), true);
        }
    }

    if (isset($_GET["remove-awb"])) {
        if ($_GET["remove-awb"] === "error") {
            SamedayCourierHelperClass::showFlashNotice('remove_awb_notice');
        }

        if ($_GET["remove-awb"] === "success") {
            SamedayCourierHelperClass::printFlashNotice('success', __("Awb was successfully removed !", SamedayCourierHelperClass::TEXT_DOMAIN), true);
        }
    }

    if (isset($_GET["show-awb"])) {
        if ($_GET["show-awb"] === "error") {
            SamedayCourierHelperClass::printFlashNotice('error', __("Awb invalid !", SamedayCourierHelperClass::TEXT_DOMAIN), true);
        }
    }

    if (isset($_GET["add-new-parcel"])) {
        if ($_GET["add-new-parcel"] === "error") {
            SamedayCourierHelperClass::showFlashNotice('add_new_parcel_notice');
        }

        if ($_GET["add-new-parcel"] === "success") {
            SamedayCourierHelperClass::printFlashNotice('success', __("New parcel has been added to this awb!", SamedayCourierHelperClass::TEXT_DOMAIN) , true);
        }
    }

    echo '<form id="addAwbForm" method="POST" action="'.admin_url('admin-post.php').'">
                <input type="hidden" name="action" value="add_awb">
                <input type="hidden" name="_wpnonce" value="'.wp_create_nonce('add-awb').'">
          </form>
          <form id="showAsPdf"  method="POST" action="'.admin_url('admin-post.php').'">
                <input type="hidden" name="action" value="show-awb-pdf">
                <input type="hidden" name="_wpnonce" value="'.wp_create_nonce('show-as-pdf').'">
            </form>
          <form id="addNewParcelForm"  method="POST" action="'.admin_url('admin-post.php').'">
                <input type="hidden" name="action" value="add-new-parcel">
                <input type="hidden" name="_wpnonce" value="'.wp_create_nonce('add-new-parcel').'">
          </form>
          <form id="removeAwb"  method="POST" action="'.admin_url('admin-post.php').'">
                <input type="hidden" name="action" value="remove-awb">
                <input type="hidden" name="_wpnonce" value="'.wp_create_nonce('remove-awb').'"> 
          </form>';
});

add_action( 'woocommerce_admin_order_data_after_shipping_address', function ( $order ) {
    add_thickbox();
    if ($_GET['action'] === 'edit') {

        $_generateAwb = '
            <p class="form-field form-field-wide wc-customer-user">
                <a href="#TB_inline?&width=670&height=470&inlineId=sameday-shipping-content-add-awb" class="button-primary button-samll thickbox"> ' . __('Generate awb') . ' </a>
            </p>';

        $_showAwb = '
            <p class="form-field form-field-wide wc-customer-user">
                <a href="#TB_inline?&width=670&height=470&inlineId=sameday-shipping-content-add-new-parcel" class="button-primary button-samll thickbox"> ' . __('Add new parcel') . ' </a>
                <a href="#TB_inline?&width=1024&height=400&inlineId=sameday-shipping-content-awb-history" class="button-primary button-samll thickbox"> ' . __('Awb history') . ' </a>
                <input type="hidden" form="showAsPdf" name="order-id" value="' . $order->get_id() . '">
                <button type="submit" form="showAsPdf" formtarget="_blank" class="button-primary button-samll">'.  __('Show as pdf', SamedayCourierHelperClass::TEXT_DOMAIN) . ' </button>
            </p>';

        $_removeAwb = '
            <p class="form-field form-field-wide wc-customer-user">
                <input type="hidden" form="removeAwb" name="order-id" value="' . $order->get_id() . '">
                <button type="submit" form="removeAwb" class="button button-samll">'.  __('Remove Awb') . ' </button>
            </p>';

        $buttons = '
                <div class="address">
                    ' . $_generateAwb . '
                </div>';

        $shipping_method_sameday = SamedayCourierHelperClass::getShippingMethodSameday($order->get_id());

        $newParcelModal = '';
        $historyModal = '';
        $_goTo_eAWB = '';

        if (! empty($shipping_method_sameday)) {
            $buttons = '
                <div class="address">
                    ' . $_showAwb . $_removeAwb  .'
                </div>';

            $sameday = new Sameday();
            $awbHistoryTable = $sameday->showAwbHistory($order->get_id());

            $addNewParcelForm = samedaycourierAddNewParcelForm($order->get_id());

            $newParcelModal = '<div id="sameday-shipping-content-add-new-parcel" style="display: none;">
                            ' . $addNewParcelForm . ' 
                           </div>';

            $historyModal = '<div id="sameday-shipping-content-awb-history" style="display: none;">
                            ' . $awbHistoryTable . ' 
                         </div>';

            $awb = SamedayCourierQueryDb::getAwbForOrderId(sanitize_key($order->get_id()));
            $redirectToEawbSite = sprintf(
                    '%s/awb?awbOrParcelNumber=%s&tab=allAwbs',
	            SamedayCourierHelperClass::EAWB_INSTANCES[SamedayCourierHelperClass::getHostCountry()],
	            $awb->awb_number
            );

            $_goTo_eAWB = '
                <p class="form-field form-field-wide wc-customer-user">
                    <a href="' . $redirectToEawbSite . '" target="_blank" class="button-secondary button-samll">'.  __('Sameday eAwb') . ' </a>
                </p>
            ';
        }

        $awbModal = samedaycourierAddAwbForm($order);

        echo $buttons . $awbModal . $newParcelModal . $historyModal . $_goTo_eAWB;
    }
});

// Revision order before Submit
add_action('woocommerce_checkout_process', static function () {
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    if ($chosen_methods !== null) {
        $serviceCode = SamedayCourierHelperClass::parseShippingMethodCode($chosen_methods[0]);
        if (SamedayCourierHelperClass::isOohDeliveryOption($serviceCode) && null === WC()->session->get('locker')) {
            wc_add_notice(__('Please choose your EasyBox Locker !'), 'error');
        }
    }
});

// Insert links to eAWB ::
add_filter('plugin_row_meta', function ($links, $pluginFileName) {
    if (strpos($pluginFileName, basename(__FILE__))) {
        $pathToSettings = admin_url() . 'admin.php?page=wc-settings&tab=shipping&section=samedaycourier';
        $pathToEawb = 'https://eawb.sameday.ro/';
        $links[] = '<a href="'. esc_html__($pathToSettings, 'woocommerce') .'" target="_blank"> '. esc_html__( 'Settings', 'woocommerce' ) .' </a>';
        $links[] = '<a href="'. esc_html__($pathToEawb, 'woocommerce') .'" target="_blank"> '. esc_html__( 'eAWB', 'woocommerce' ) .' </a>';
    }

    return $links;
}, 10, 4);

register_activation_hook( __FILE__, 'samedaycourier_create_db' );
register_uninstall_hook( __FILE__, 'samedaycourier_drop_db');

function enqueue_button_scripts(): void
{
    if (is_checkout()) {
        wp_enqueue_script( 'lockerpluginsdk','https://cdn.sameday.ro/locker-plugin/lockerpluginsdk.js', ['jquery']);
        wp_enqueue_style( 'sameday-admin-style', plugin_dir_url( __FILE__ ). 'assets/css/sameday_front_button.css' );
        wp_enqueue_script( 'custom-checkout-button', plugins_url( 'assets/js/custom-checkout-button.js', __FILE__ ), array( 'jquery' ), time(), true );

        // Localize the script with your dynamic PHP values
        wp_localize_script( 'custom-checkout-button', 'samedayData', array(
            'username' => SamedayCourierHelperClass::getSamedaySettings()['user'] ?? null,
            'country'  => SamedayCourierHelperClass::getSamedaySettings()['host_country'] ?? null,
            'buttonText' => __('Show Locations Map', SamedayCourierHelperClass::TEXT_DOMAIN),
        ));
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_button_scripts' );
