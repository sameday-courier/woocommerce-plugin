<?php

/**
 * Plugin Name: SamedayCourier Shipping
 * Plugin URI: https://github.com/sameday-courier/woocommerce-plugin
 * Description: SamedayCourier Shipping Method for WooCommerce
 * Version: 1.0.25
 * Author: SamedayCourier
 * Author URI: https://www.sameday.ro/contact
 * License: GPL-3.0+
 * License URI: https://sameday.ro
 * Domain Path: /ro
 * Text Domain: sameday
 */

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

function samedaycourier_shipping_method() {
    if (! class_exists('SamedayCourier_Shipping_Method')) {
        class SamedayCourier_Shipping_Method extends WC_Shipping_Method
        {
            const CASH_ON_DELIVERY = 'cod';

            /**
             * @var bool
             */
            private $configValidation;

	        /**
	         * SamedayCourier_Shipping_Method constructor.
	         *
	         * @param int $instance_id
	         */
            public function __construct( $instance_id = 0 )
            {
                parent::__construct( $instance_id );

                $this->id = 'samedaycourier';
                $this->method_title = __('SamedayCourier', 'samedaycourier');
                $this->method_description = __('Custom Shipping Method for SamedayCourier', 'samedaycourier');

                $this->configValidation = false;

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
                $estimatedCostExtraFee = (float) $this->settings['estimated_cost_extra_fee'];

                $availableServices = $this->getAvailableServices();
                if (!empty($availableServices)) {
                    foreach ( $availableServices as $service ) {
                        if ($service->sameday_code === "LS") {
                            continue;
                        }

                        if ($service->sameday_code === "2H" && SamedayCourierHelperClass::convertStateCodeToName($package['destination']['country'], $package['destination']['state']) !== "București") {
                            continue;
                        }

                        if ($service->sameday_code === "LN" && count(WC()->cart->get_cart()) > 1) {
                            continue;
                        }

                        $price = $service->price;

                        if ($useEstimatedCost !== 'no') {
                            $estimatedCost = $this->getEstimatedCost($package['destination'], $service->sameday_id);

                            if (isset($estimatedCost)) {

                                if (($useEstimatedCost === 'yes') || ($useEstimatedCost === 'btfp' && $service->price < $estimatedCost)) {
                                    $price = $estimatedCost;
                                }

                                if (isset($estimatedCostExtraFee) && $estimatedCostExtraFee > 0) {
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

                        if ($service->sameday_code === "LN") {
                            $this->syncLockers();
                            $rate['lockers'] = SamedayCourierQueryDb::getLockers($this->isTesting());
                        }

                        $this->add_rate( $rate );
                    }
                }
            }

            /**
             * @return bool
             */
            private function syncLockers()
            {
                $time = time();

                $ltSync = $this->settings['sameday_sync_lockers_ts'];

                if ($time > ($ltSync + 86400)) {
                    $samedayClass = new Sameday();
                    return $samedayClass->refreshLockers();
                }

                return true;
            }

            /**
             * @return int
             */
            private function isTesting()
            {
                return $this->settings['is_testing'] === 'yes' ? 1 : 0;
            }

            /**
             * @param $address
             * @param $serviceId
             *
             * @return float|null
             */
            private function getEstimatedCost($address, $serviceId)
            {
                $pickupPointId = SamedayCourierQueryDb::getDefaultPickupPointId($this->isTesting());
                $weight = WC()->cart->get_cart_contents_weight() ?: .1;
                $state = \SamedayCourierHelperClass::convertStateCodeToName($address['country'], $address['state']);
                $city = \SamedayCourierHelperClass::removeAccents($address['city']);

                $optionalServices = SamedayCourierQueryDb::getServiceIdOptionalTaxes($serviceId, $this->isTesting());
                $serviceTaxIds = array();
                if (WC()->session->get('open_package') === 'yes') {
                    foreach ($optionalServices as $optionalService) {
                        if ($optionalService->getCode() === 'OPCG' && $optionalService->getPackageType()->getType() === \Sameday\Objects\Types\PackageType::PARCEL) {
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
                    [new \Sameday\Objects\ParcelDimensionsObject($weight)],
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
                        $this->isTesting()
                    )
                );

                try {
                    $estimation = $sameday->postAwbEstimation($estimateCostRequest);

                    return $estimation->getCost();
                } catch (\Sameday\Exceptions\SamedayBadRequestException $exception) {
                    return null;
                }
            }

            private function getAvailableServices()
            {
                $services = SamedayCourierQueryDb::getAvailableServices($this->isTesting());

                $availableServices = array();
                foreach ($services as $service) {
                    switch ($service->status) {
                        case 1:
                            $availableServices[] = $service;
                            break;

                        case 2:
                            $working_days = unserialize($service->working_days);

                            $today = \SamedayCourierHelperClass::getDays()[date('w')]['text'];
                            $date_from = mktime((int) $working_days["order_date_{$today}_h_from"], (int) $working_days["order_date_{$today}_m_from"], (int) $working_days["order_date_{$today}_s_from"], date('m'), date('d'), date('Y'));
                            $date_to = mktime((int) $working_days["order_date_{$today}_h_until"], (int) $working_days["order_date_{$today}_m_until"], (int) $working_days["order_date_{$today}_s_until"], date('m'), date('d'), date('Y'));
                            $time = time();

                            if (!isset($working_days["order_date_{$today}_enabled"]) || $time < $date_from || $time > $date_to) {
                                // Not working on this day, or out of available time period.
                                break;
                            }

                            $availableServices[] = $service;
                            break;
                    }
                }

                return $availableServices;
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

                    'is_testing' => array(
                        'title' => __( 'Is testing', 'samedaycourier' ),
                        'type' => 'checkbox',
                        'description' => __( 'Disable this for production mode', 'samedaycourier' ),
                        'default' => 'yes'
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

                $sameday = SamedayCourierApi::initClient(
                    $post_data['woocommerce_samedaycourier_user'],
                    $post_data['woocommerce_samedaycourier_password'],
                    $post_data['woocommerce_samedaycourier_is_testing']
                );


                if (! $this->configValidation ) {
                    $this->configValidation = true;

                    if ( $sameday->login() ) {
                        return parent::process_admin_options();
                    } else {
                        WC_Admin_Settings::add_error( __( 'Invalid username/password combination provided! Settings have not been changed!'));
                    }
                }
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
    $samedayClass = new Sameday();
    return $samedayClass->refreshServices();
});

add_action('admin_post_refresh_pickup_points', function () {
    $samedayClass = new Sameday();
    return $samedayClass->refreshPickupPoints();
});

add_action('admin_post_refresh_lockers', function () {
    $samedayClass = new Sameday();
    return $samedayClass->refreshLockers();
});

add_action('admin_post_edit_service', function() {
    $samedayClass = new Sameday();
    return $samedayClass->editService();
});

add_action('admin_post_add_awb', function (){
    $postFields = SamedayCourierHelperClass::sanitizeInputs($_POST);
    $orderDetails = wc_get_order($postFields['samedaycourier-order-id']);
    if (empty($orderDetails)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    $data = array_merge($postFields, $orderDetails->get_data());
    $samedayClass = new Sameday();
    return $samedayClass->postAwb($data);
});

add_action('admin_post_remove-awb', function () {
    $awb = SamedayCourierQueryDb::getAwbForOrderId(sanitize_key($_POST['order-id']));
    if (empty($awb)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    $samedayClass = new Sameday();
    return $samedayClass->removeAwb($awb);
});

add_action('admin_post_show-awb-pdf', function (){
    $orderId = sanitize_key($_POST['order-id']);
    if (!isset($orderId)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    $samedayClass = new Sameday();
    return $samedayClass->showAwbAsPdf($orderId);
});

add_action('admin_post_add-new-parcel', function() {
    $postFields = SamedayCourierHelperClass::sanitizeInputs($_POST);
    if (empty($postFields)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    $samedayClass = new Sameday();
    return $samedayClass->addNewParcel($postFields);
});

// Open Package :
function wps_sameday_shipping_options_layout() {
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $serviceCode = SamedayCourierHelperClass::parseShippingMethodCode($chosen_methods[0]);
    $is_testing = get_option('woocommerce_samedaycourier_settings')['is_testing'] === 'yes' ? 1 : 0;
    $service = SamedayCourierQueryDb::getServiceSamedayCode($serviceCode, $is_testing);
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
            if (get_option('woocommerce_samedaycourier_settings')['open_package_status'] === "yes") {
                ?>
                <tr class="shipping-pickup-store">
                    <th><strong><?php echo __('Open package', 'wc-pickup-store') ?></strong></th>
                    <td>
                        <ul id="shipping_method" class="woocommerce-shipping-methods" style="list-style-type:none;">
                            <li>
                                <input type="checkbox" name="open_package" id="open_package" <?php echo $isChecked; ?> >
                                <label for="open_package"><?php echo get_option('woocommerce_samedaycourier_settings')['open_package_label']; ?></label>
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
    foreach ( WC()->cart->get_shipping_packages() as $package_key => $package) {
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

add_action( 'wp_footer', 'custom_checkout_script' );
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

    $is_testing = get_option('woocommerce_samedaycourier_settings')['is_testing'] === 'yes' ? 1 : 0;

    $cities = SamedayCourierQueryDb::getCities($is_testing);
    $lockers = array();
    foreach ($cities as $city) {
        if (null !== $city->city) {
            $lockers[$city->city . ' (' . $city->county . ')'] = SamedayCourierQueryDb::getLockersByCity($city->city, $is_testing);
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

    if ( is_checkout() && $serviceCode === "LN") {
    ?>
        <tr class="shipping-pickup-store">
            <th><strong><?php echo __('Sameday Locker', 'wc-pickup-store') ?></strong></th>
            <td>
                <select name="locker_id" id="shipping-pickup-store-select" style="width: 130px; height: 30px; font-size: 13px">
                    <option value="" style="font-size: 13px"> <strong> <?= __('Select easyBox', 'wc-pickup-store') ?> </strong> </option>
                    <?php echo $lockerOptions; ?>
                </select>
            </td>
        </tr>
    <?php }
}
add_action( 'woocommerce_review_order_after_shipping', 'wps_locker_row_layout');

function add_locker_id_to_order_data( $order_id ) {
    if ( isset( $_POST['locker_id'] ) &&  '' != $_POST['locker_id']) {
        $locker_id = $_POST['locker_id'];
        update_post_meta( $order_id, '_sameday_shipping_locker_id',  sanitize_text_field($locker_id), true);
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'add_locker_id_to_order_data');

/**
 ** Order detail styles
 **/
function wps_locker_style() {
    ?>
    <style type="text/css">
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
