<?php

/**
 * Plugin Name: SamedayCourier Shipping
 * Plugin URI: https://github.com/sameday-courier/woocommerce-plugin
 * Description: SamedayCourier Shipping Method for WooCommerce
 * Version: 1.2.19
 * Author: SamedayCourier
 * Author URI: https://www.sameday.ro/contact
 * License: GPL-3.0+
 * License URI: https://sameday.ro
 * Domain Path: /ro
 * Text Domain: sameday
 */

use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\Types\PackageType;
use Sameday\SamedayClient;

if (! defined( 'ABSPATH' ) ) {
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

function samedaycourier_shipping_method() {
    if (! class_exists('SamedayCourier_Shipping_Method')) {
        class SamedayCourier_Shipping_Method extends WC_Shipping_Method
        {
            const CASH_ON_DELIVERY = 'cod';

	        /**
	         * SamedayCourier_Shipping_Method constructor.
	         *
	         * @param int $instance_id
	         */
            public function __construct($instance_id = 0)
            {
                parent::__construct($instance_id);

                $this->id = 'samedaycourier';
                $this->method_title = __('SamedayCourier', 'samedaycourier');
                $this->method_description = __('Custom Shipping Method for SamedayCourier', 'samedaycourier');

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
            public function calculate_shipping($package = array())
            {
                if ($this->settings['enabled'] === 'no') {
                    return;
                }

                $useEstimatedCost = $this->settings['estimated_cost'];
                $estimatedCostExtraFee = (int) $this->settings['estimated_cost_extra_fee'];
                $lockerMaxItems = (int) $this->settings['locker_max_items'];
                $useLockerMap = $this->settings['lockers_map'] === 'yes';

                $availableServices = $this->getAvailableServices();
                if (!empty($availableServices)) {
                    foreach ( $availableServices as $service ) {
                        if ($service->sameday_code === "LS") {
                            continue;
                        }

                        if ($service->sameday_code === "2H" && SamedayCourierHelperClass::convertStateCodeToName($package['destination']['country'], $package['destination']['state']) !== "București") {
                            continue;
                        }

                        if ($service->sameday_code === "LN" && count(WC()->cart->get_cart()) > $lockerMaxItems) {
                            continue;
                        }

                        $price = $service->price;

                        if (
	                        '' !== $package['destination']['city']
	                        && '' !== $package['destination']['address']
                            && $useEstimatedCost !== 'no'
                        ) {
                            $estimatedCost = $this->getEstimatedCost($package['destination'], $service->sameday_id);

                            if (isset($estimatedCost)) {

                                if (($useEstimatedCost === 'yes') || ($useEstimatedCost === 'btfp' && $service->price < $estimatedCost)) {
                                    $price = $estimatedCost;
                                }

                                if ($estimatedCostExtraFee > 0) {
                                    $price += round($price * ($estimatedCostExtraFee /100), 2);
                                }
                            }
                        }

                        if ($service->price_free !== null && WC()->cart->subtotal > $service->price_free) {
                            $price = 0;
                        }

                        $rate = array(
                            'id' => $this->id . ":" . $service->sameday_id . ":" . $service->sameday_code,
                            'label' => $service->name,
                            'cost' => $price,
                            'meta_data' => array(
                                'service_id' => $service->sameday_id,
                                'service_code' => $service->sameday_code
                            )
                        );

                        if (( $service->sameday_code === "LN" ) && (false === $useLockerMap)) {
                            $this->syncLockers();
                            $rate['lockers'] = SamedayCourierQueryDb::getLockers(SamedayCourierHelperClass::isTesting());
                        }

                        $this->add_rate( $rate );
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
            private function getEstimatedCost($address, $serviceId)
            {
                $pickupPointId = SamedayCourierQueryDb::getDefaultPickupPointId(SamedayCourierHelperClass::isTesting());
                $weight = WC()->cart->get_cart_contents_weight() ?: .1;
                $state = \SamedayCourierHelperClass::convertStateCodeToName($address['country'], $address['state']);
                $city = \SamedayCourierHelperClass::removeAccents($address['city']);

                $optionalServices = SamedayCourierQueryDb::getServiceIdOptionalTaxes($serviceId, SamedayCourierHelperClass::isTesting());
                $serviceTaxIds = array();
                if (WC()->session->get('open_package') === 'yes') {
                    foreach ($optionalServices as $optionalService) {
                        if ($optionalService->getCode() === 'OPCG' && $optionalService->getPackageType()->getType() === PackageType::PARCEL) {
                            $serviceTaxIds[] = $optionalService->getId();
                            break;
                        }
                    }
                }

                // Check if the client has to pay anything as repayment value
                $repaymentAmount = WC()->cart->subtotal;
	            $paymentMethod = WC()->session->get('payment_method');
	            if (isset($paymentMethod) && ($paymentMethod !== self::CASH_ON_DELIVERY)) {
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
                    $serviceTaxIds
                );

                $sameday =  new Sameday\Sameday(
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

            private function getAvailableServices()
            {
                return SamedayCourierQueryDb::getAvailableServices(SamedayCourierHelperClass::isTesting());
            }

            private function init(): void
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
                        'title' => __( 'Username', 'samedaycourier' ) . ' *',
                        'type' => 'text',
                        'description' => __( 'Username', 'samedaycourier' ),
                        'default' => __( '', 'samedaycourier' )
                    ),

                    'password' => array(
                        'title' => __( 'Password', 'samedaycourier' ) . ' *',
                        'type' => 'password',
                        'description' => __( 'Password', 'samedaycourier' ),
                        'default' => __( '', 'samedaycourier' )
                    ),

                    'default_label_format' => array(
                        'title'   => __( 'Default label format', 'samedaycourier' ) . ' *',
                        'default' => 'A4',
                        'type'    => 'select',
                        'options' => [
                            'A4' => __( Sameday\Objects\Types\AwbPdfType::A4, 'samedaycourier' ),
                            'A6' => __( Sameday\Objects\Types\AwbPdfType::A6, 'samedaycourier' ),
                        ],
                        'description' => __('Awb paper format')
                    ),

                    'estimated_cost' => array(
                        'title'   => __( 'Use estimated cost', 'samedaycourier' ) . ' *',
                        'default' => 'no',
                        'type'    => 'select',
                        'options' => [
                            'no' => __( 'Never', 'samedaycourier' ),
                            'yes' => __( 'Always', 'samedaycourier' ),
                            'btfp' => __('If its cost is bigger than fixed price')
                        ],
                        'description' => __('This is the shipping cost calculated by Sameday Api for each service. <br/> 
                            Never* You choose to display only the fixed price that you set for each service<br/>
                            Always* You choose to display only the price estimated by SamedayCourier API<br/>
                            If its cost is bigger than fixed price* You choose to display the cost estimated by 
                            SamedayCourier Api only in the situation that this cost exceed the fixed price set by you for each service.
                        ')
                    ),

                    'estimated_cost_extra_fee' => array(
                        'title' => __('Extra fee', 'samedaycourier'),
                        'type' => 'number',
                        'css' => 'width:100px;',
                        'description' => __('Apply extra fee on estimated cost. This is a % value. <br/> If you don\'t want to add extra fee on estimated cost value, such as T.V.A. leave this field blank or 0', 'samedaycourier'),
                        'custom_attributes' => array(
                            'min' => 0,
                            'onkeypress' => 'return (event.charCode !=8 && event.charCode == 0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))',
                            'data-placeholder' => __('Extra fee', 'samedaycourier')
                        ),
                        'default' => 0
                    ),

                    'open_package_status' => array(
                        'title' => __( 'Open package status', 'samedaycourier' ),
                        'type' => 'checkbox',
                        'description' => __( 'Enable this option if you want to offer your customers the opening of the package at delivery time', 'samedaycourier' ),
                        'default' => 'no'
                    ),

                    'open_package_label' => array(
                        'title' => __( 'Open package label', 'samedaycourier' ),
                        'type' => 'text',
                        'description' => __( 'This appear in checkout page', 'samedaycourier' ),
                        'default' => __( '', 'samedaycourier' )
                    ),

                    'locker_max_items' => array(
	                    'title' => __( 'Locker max. items', 'samedaycourier' ),
	                    'type' => 'number',
	                    'description' => __( 'The maximum amount of items accepted inside the locker', 'samedaycourier' ),
	                    'default' => 1
                    ),

                    'lockers_map' => array(
                        'title'   => __( 'Use locker map', 'samedaycourier' ),
                        'default' => 'no',
                        'type'    => 'select',
                        'options' => [
                            'no' => __( 'No', 'samedaycourier' ),
                            'yes' => __( 'Yes', 'samedaycourier' ),
                        ]
                    ),

                    'is_testing' => array(
	                    'title' => __( 'Env. Mode', 'samedaycourier' ),
	                    'type' => 'select',
	                    'description' => __( 'The value of this field will be appear automatically after you complete the authentication', 'samedaycourier' ),
	                    'default' => 2,
	                    'disabled' => true,
                        'options' => array(
                            SamedayCourierHelperClass::API_PROD => __( 'Prod', 'samedaycourier' ),
                            SamedayCourierHelperClass::API_DEMO => __( 'Demo', 'samedaycourier' ),
                            2 => '',
                        ),
                    ),

                    'host_country' => array(
	                    'title' => __( 'Env. Host Country', 'samedaycourier' ),
	                    'type' => 'select',
	                    'description' => __( 'The value of this field will be appear automatically after you complete the authentication', 'samedaycourier' ),
	                    'default' => 'none',
	                    'disabled' => true,
	                    'options' => array(
		                    SamedayCourierHelperClass::API_HOST_LOCALE_RO => __( SamedayCourierHelperClass::API_HOST_LOCALE_RO, 'samedaycourier' ),
		                    SamedayCourierHelperClass::API_HOST_LOCAL_HU => __( SamedayCourierHelperClass::API_HOST_LOCAL_HU, 'samedaycourier' ),
		                    'none' => '',
	                    ),
                    ),
                );

                // Show on checkout:
                $this->enabled = $this->settings['enabled'] ?? 'yes';
                $this->title = $this->settings['title'] ?? __( 'SamedayCourier', 'samedaycourier' );

                $this->init_settings();

                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ));
            }

            public function process_admin_options()
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

                    return parent::process_admin_options();
                }

	            WC_Admin_Settings::add_error( __( 'Invalid username/password combination provided! Settings have not been changed!'));
            }

            public function admin_options()
            {
                $serviceUrl = admin_url() . 'edit.php?post_type=page&page=sameday_services';
                $pickupPointUrl = admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points';
                $lockerUrl = admin_url() . 'edit.php?post_type=page&page=sameday_lockers';
                $buttons = '<a href="' . $serviceUrl . '" class="button-primary"> Services </a> <a href="' . $pickupPointUrl . '" class="button-primary"> Pickup-point </a> <a href="' . $lockerUrl . '" class="button-primary"> Lockers </a>';

                $adminOptins = parent::admin_options();

                echo $adminOptins . $buttons;
            }
        }
    }
}

// Shipping Method init.
add_action('woocommerce_shipping_init', 'samedaycourier_shipping_method');

function add_samedaycourier_shipping_method( $methods ) {
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
    return (new Sameday())->refreshPickupPoints();
});

add_action('admin_post_refresh_lockers', function () {
    return (new Sameday())->refreshLockers();
});

add_action('admin_post_edit_service', function() {
    return (new Sameday())->editService();
});

add_action('admin_post_add_awb', function (){
    $postFields = SamedayCourierHelperClass::sanitizeInputs($_POST);
    $orderDetails = wc_get_order($postFields['samedaycourier-order-id']);
    if (empty($orderDetails)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    $data = array_merge($postFields, $orderDetails->get_data());
    return (new Sameday())->postAwb($data);
});

add_action('admin_post_remove-awb', function () {
    $awb = SamedayCourierQueryDb::getAwbForOrderId(sanitize_key($_POST['order-id']));
    if (empty($awb)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    return (new Sameday())->removeAwb($awb);
});

add_action('admin_post_show-awb-pdf', function (){
    $orderId = sanitize_key($_POST['order-id']);
    if (!isset($orderId)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    return (new Sameday())->showAwbAsPdf($orderId);
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
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $serviceCode = SamedayCourierHelperClass::parseShippingMethodCode($chosen_methods[0]);

    $service = SamedayCourierQueryDb::getServiceSamedayCode($serviceCode, SamedayCourierHelperClass::isTesting());
    /** @var \Sameday\Objects\Service\OptionalTaxObject[] $optionalTaxes */
    $optionalTaxes = [];
    if ($service) {
        $optionalTaxes = unserialize($service->service_optional_taxes);
        if (!$optionalTaxes) {
            $optionalTaxes = [];
        }
    }

    $taxOpenPackage = 0;
    foreach ($optionalTaxes as $optionalTax) {
        if ($optionalTax->getCode() === 'OPCG') {
            $taxOpenPackage = $optionalTax->getId();
        }
    }

    if (is_checkout()) {
        if ($taxOpenPackage) {
            $isChecked = WC()->session->get('open_package') === 'yes' ? 'checked' : '';
            if (SamedayCourierHelperClass::getSamedaySettings()['open_package_status'] === "yes") {
                ?>
                <tr class="shipping-pickup-store">
                    <th><strong><?php echo __('Open package', 'wc-pickup-store') ?></strong></th>
                    <td>
                        <ul id="shipping_method" class="woocommerce-shipping-methods" style="list-style-type:none;">
                            <li>
                                <input type="checkbox" name="open_package" id="open_package" <?php echo $isChecked; ?> >
                                <label for="open_package"><?php echo SamedayCourierHelperClass::getSamedaySettings()['open_package_label']; ?></label>
                            </li>
                        </ul>
                    </td>
                </tr>
                <?php
            }
        }
    }
}
add_action('woocommerce_review_order_after_shipping', 'wps_sameday_shipping_options_layout');

// Enabling, disabling and refreshing session shipping methods data
add_action( 'woocommerce_checkout_update_order_review', 'refresh_shipping_methods', 10, 1);
function refresh_shipping_methods() {
    foreach (WC()->cart->get_shipping_packages() as $package_key => $package) {
	    $package['package_hash'] = 'wc_ship_' . md5( wp_json_encode($package) . WC_Cache_Helper::get_transient_version('shipping'));
        WC()->session->set( 'shipping_for_package_' . $package_key, $package);
    }

    WC()->cart->calculate_shipping();
}

add_action( 'wp_ajax_woo_get_ajax_data', 'woo_get_ajax_data' );
add_action( 'wp_ajax_nopriv_woo_get_ajax_data', 'woo_get_ajax_data' );
function woo_get_ajax_data() {
    if (isset($_POST['open_package'])) {
	    WC()->session->set('open_package', $_POST['open_package']);
    }

    if (isset($_POST['payment_method'])) {
	    WC()->session->set('payment_method', $_POST['payment_method']);
    }

    die();
}

add_action('woocommerce_before_checkout_form', 'custom_checkout_script');
function custom_checkout_script() {
    ?>
    <script>
        let $ = jQuery;
        $(document).on('change', '#open_package', function () {
            let isChecked = 'no';
            if ($(this).prop('checked')) {
                isChecked = 'yes';
            }

            doAjaxCall({
                'action': 'woo_get_ajax_data',
                'open_package': isChecked
            });
        });

        $('body').on('updated_checkout', function () {
            $('input[name="payment_method"]').change(function () {
                doAjaxCall({
                    'action': 'woo_get_ajax_data',
                    'payment_method': $("input[name='payment_method']:checked").val()
                })
            });
        });

        const doAjaxCall = function (params) {
            $.ajax({
                'type': 'POST',
                'url': woocommerce_params.ajax_url,
                'data': params,
                success: function () {
                    $(document.body).trigger('update_checkout');
                }
            })
        }

        window.onload = function () {
            doAjaxCall(
                {
                    'action': 'woo_get_ajax_data',
                    'payment_method': $("input[name='payment_method']:checked").val()
                }
            );
        }
    </script>
    <?php
}

function set_open_package_option($order_id) {
    if (isset($_POST['open_package'])) {
        update_post_meta($order_id, '_sameday_shipping_open_package_option', sanitize_text_field($_POST['open_package']), true);
    }

    WC()->session->set('open_package', 'no');
}
add_action('woocommerce_checkout_update_order_meta', 'set_open_package_option');

// LOCKER :
function wps_locker_row_layout() {
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $serviceCode = SamedayCourierHelperClass::parseShippingMethodCode($chosen_methods[0]);

    $cities = SamedayCourierQueryDb::getCities(SamedayCourierHelperClass::isTesting());
    $lockers = array();
    foreach ($cities as $city) {
        if (null !== $city->city) {
            $lockers[$city->city . ' (' . $city->county . ')'] = SamedayCourierQueryDb::getLockersByCity($city->city, SamedayCourierHelperClass::isTesting());
        }
    }

    $lockerOptions = '';
    foreach ($lockers as $city => $cityLockers) {
        $optionGroup = "<optgroup label='{$city}' style='font-size: 13px;'></optgroup>";
        $options = '';
        foreach ($cityLockers as $locker) {
            $lockerDetails = "<span>" . $locker->name . ' - ' . $locker->address . "</span>";
            $options .= '<option value="' . $locker->locker_id . '" style="font-size: 9px">' . $lockerDetails . '</option>';
        }

        $lockerOptions .= $optionGroup . $options;
    }

    if ($serviceCode === "LN" && is_checkout()) {
    ?>
        <tr class="shipping-pickup-store">
            <th><strong><?php echo __('Sameday Locker', 'wc-pickup-store') ?></strong></th>
            <td>
                <?php if (( SamedayCourierHelperClass::getSamedaySettings()['lockers_map'] ?? null) === "yes"){ ?>
                    <button type="button" class="button alt sameday_select_locker"  id="select_locker" data-username='<?php echo SamedayCourierHelperClass::getSamedaySettings()['user']; ?>' data-country='<?php echo SamedayCourierHelperClass::getSamedaySettings()['host_country']; ?>' ><?php echo __('Show Locker Map', 'wc-pickup-store') ?></button>
                <?php }else{ ?>
                    <label for="shipping-pickup-store-select"></label>
                    <select name="locker_id" id="shipping-pickup-store-select" style="width: 100%; height: 30px; font-size: 13px">
                        <option value="" style="font-size: 13px"> <strong> <?= __('Select easyBox', 'wc-pickup-store') ?> </strong> </option>
                        <?php echo $lockerOptions; ?>
                    </select>
                <?php } ?>
                <input type="hidden" id="locker_id" name="locker_id">
                <input type="hidden" id="locker_name" name="locker_name">
                <input type="hidden" id="locker_address" name="locker_address">
                <span id="showLockerDetails"></span>
            </td>
        </tr>
    <?php }
}
add_action( 'woocommerce_review_order_after_shipping', 'wps_locker_row_layout');

function add_locker_id_to_order_data( $order_id ) {
    if (isset( $_POST['locker_id'])) {
        $locker_id = $_POST['locker_id'];
        update_post_meta( $order_id, '_sameday_shipping_locker_id',  sanitize_text_field($locker_id), true);
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'add_locker_id_to_order_data');

/**
 ** Add external JS file for Lockers
 **/
add_action('wp_enqueue_scripts', static function() {
	global $wp;
	if (empty($wp->query_vars['order-pay'] ) && !isset($wp->query_vars['order-received'])  && is_checkout()) {
		wp_enqueue_script( 'prod-locker-plugin', 'https://cdn.sameday.ro/locker-plugin/lockerpluginsdk.js');
		wp_enqueue_script( 'lockers_script', plugin_dir_url( __FILE__ ) . 'assets/js/lockers_sync.js');
	}
}, 9999);


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
            border:0px;
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
            SamedayCourierHelperClass::printFlashNotice('success', __("Awb was successfully generated !"), true);
        }
    }

    if (isset($_GET["remove-awb"])) {
        if ($_GET["remove-awb"] === "error") {
            SamedayCourierHelperClass::showFlashNotice('remove_awb_notice');
        }

        if ($_GET["remove-awb"] === "success") {
            SamedayCourierHelperClass::printFlashNotice('success', __("Awb was successfully removed !"), true);
        }
    }

    if (isset($_GET["show-awb"])) {
        if ($_GET["show-awb"] === "error") {
            SamedayCourierHelperClass::printFlashNotice('error', __("Awb invalid !"), true);
        }
    }

    if (isset($_GET["add-new-parcel"])) {
        if ($_GET["add-new-parcel"] === "error") {
            SamedayCourierHelperClass::showFlashNotice('add_new_parcel_notice');
        }

        if ($_GET["add-new-parcel"] === "success") {
            SamedayCourierHelperClass::printFlashNotice('success', __("New parcel has been added to this awb!") , true);
        }
    }

    echo '<form id="addAwbForm" method="POST" action="'.admin_url('admin-post.php').'"><input type="hidden" name="action" value="add_awb"></form>
          <form id="showAsPdf"  method="POST" action="'.admin_url('admin-post.php').'"><input type="hidden" name="action" value="show-awb-pdf"></form>
          <form id="addNewParcelForm"  method="POST" action="'.admin_url('admin-post.php').'"><input type="hidden" name="action" value="add-new-parcel"></form>
          <form id="removeAwb"  method="POST" action="'.admin_url('admin-post.php').'"><input type="hidden" name="action" value="remove-awb"></form>';
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
                <button type="submit" form="showAsPdf" class="button-primary button-samll">'.  __('Show as pdf') . ' </button>
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
            $awbNumber = $awb->awb_number;

            $_goTo_eAWB = '
                <p class="form-field form-field-wide wc-customer-user">
                    <a href="https://eawb.sameday.ro/awb?awbOrParcelNumber='.$awbNumber.'&tab=allAwbs" target="_blank" class="button-secondary button-samll">'.  __('Sameday eAwb') . ' </a>
                </p>
            ';
        }

        $awbModal = samedaycourierAddAwbForm($order);

        echo $buttons . $awbModal . $newParcelModal . $historyModal . $_goTo_eAWB;
    }
});

// Revision order before Submit
add_action('woocommerce_checkout_process', function () {
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $serviceCode = SamedayCourierHelperClass::parseShippingMethodCode($chosen_methods[0]);
    if ($serviceCode === 'LN') {
        if ($_POST['locker_id'] === null || $_POST['locker_id'] === '') {
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
