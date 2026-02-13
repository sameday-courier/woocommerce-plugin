<?php

/**
 * Plugin Name: SamedayCourier Shipping
 * Plugin URI: https://github.com/sameday-courier/woocommerce-plugin
 * Description: SamedayCourier Shipping Method for WooCommerce
 * Version: 1.11.0
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
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Service\OptionalTaxObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\AwbPdfType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayDeletePickupPointRequest;
use Sameday\Requests\SamedayPostAwbEstimationRequest;
use Sameday\Requests\SamedayPostPickupPointRequest;
use Sameday\Responses\SamedayPostAwbEstimationResponse;
use Sameday\Sameday;
use Sameday\SamedayClient;
use SamedayCourier\Shipping\BgnCurrencyConverter;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\ApiRequestsHandler;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Shipping\Method\SamedayCourier;
use SamedayCourier\Shipping\Infrastructure\Sql\QueryHandler;
use SamedayCourier\Shipping\Infrastructure\Sql\SchemaHandler;
use SamedayCourier\Shipping\Utils\Helper;
use SamedayCourier\Shipping\Woo\Admin\Grid\Locker\LockerInstance;
use SamedayCourier\Shipping\Woo\Admin\Grid\PickupPoint\PickupPointInstance;
use SamedayCourier\Shipping\Woo\Admin\Grid\Service\ServiceInstance;

if (! defined( 'ABSPATH')) {
    exit;
}

/**
 * Check if WooCommerce plugin is enabled
 */
if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )), '')) {
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
        wp_enqueue_script( 'add-awb', plugin_dir_url( __FILE__ ). 'assets/js/add-awb.js', ['jquery']);
        wp_enqueue_style( 'sameday-admin-style', plugin_dir_url( __FILE__ ). 'assets/css/sameday_admin.css' );
        wp_enqueue_style( 'select2-style', plugin_dir_url( __FILE__ ). 'assets/css/select2.css' );
    }
}

// Shipping Method init.
add_filter('woocommerce_shipping_methods', static function (array $methods): array {
    $methods['samedaycourier'] = SamedayCourier::class;

    return $methods;
});

// Plugin settings.
add_action('plugins_loaded', static function () {
    ServiceInstance::get_instance();
    PickupPointInstance::get_instance();
    LockerInstance::get_instance();
});

add_action('admin_post_refresh_services', static function () {
    try {
        return (new ApiRequestsHandler())->refreshSamedayServices();
    } catch (Exception $exception) { return $exception->getMessage(); }
});

add_action('admin_post_refresh_pickup_points', static function () {
    try {
        return (new ApiRequestsHandler())->refreshSamedayPickupPoints();
    } catch (Exception $exception) { return $exception->getMessage(); }
});

add_action('admin_post_refresh_lockers', static function () {
    try {
        return (new ApiRequestsHandler())->refreshSamedayLockers();
    } catch (Exception $exception) { return $exception->getMessage(); }
});

add_action('wp_ajax_all_import', static function (): void {
	try {
		(new ApiRequestsHandler())->refreshSamedayServices();
    } catch (Exception $exception) {
		throw new \RuntimeException($exception->getMessage());
    }

	try {
		(new ApiRequestsHandler())->refreshSamedayPickupPoints();
    } catch (Exception $exception) {
		throw new \RuntimeException($exception->getMessage());
    }

	try {
		(new ApiRequestsHandler())->refreshSamedayLockers();
	} catch (Exception $exception) {
		throw new \RuntimeException($exception->getMessage());
    }

	try {
		(new ApiRequestsHandler())->importCities();
	} catch(Exception $exception) {
		throw new \RuntimeException($exception->getMessage());
	}
});

add_action('wp_ajax_import_cities', static function (): void {
    try {
        (new ApiRequestsHandler())->importCities();
    } catch(Exception $exception) {
	    throw new \RuntimeException($exception->getMessage());
    }
});

add_action('wp_ajax_change_locker', static function() {
    if (null !== $orderId = $_POST['orderId']) {
	    try {
		    Helper::addLockerToOrderData($orderId, $_POST['locker']);
	    } catch (Exception $exception) {}
    }
});

add_action('wp_ajax_change_counties', static function() {
    if (!isset($_POST['countyId'])) {
        return [];
    }
    wp_send_json(Helper::getCities($_POST['countyId'])); die();
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
        $sameday = new Sameday(SdkInitiator::init());
    } catch (SamedaySDKException|Exception $exception) {

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
	        (bool) $formData['default']
        ));

        wp_send_json_success($response->getPickupPointId());
    } catch (SamedayBadRequestException $e) {
        $noticeMessage = Helper::parseAwbErrors($e->getErrors());
        Helper::addFlashNotice('add_awb_notice', $noticeMessage, 'error', true);

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    } catch (Exception $e) {
        Helper::addFlashNotice('add_awb_notice', $e->getMessage(), 'error',true);

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    }

    return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
});

add_action('wp_ajax_delete_pickup_point', static function() {
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
        $sameday = new Sameday(SdkInitiator::init());
    } catch (SamedaySDKException|Exception $e) {
        Helper::addFlashNotice('add_awb_notice', $e->getMessage(), 'error',true);

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    }

    try {
        $response = $sameday->deletePickupPoint(new SamedayDeletePickupPointRequest($sameday_id));
        wp_send_json_success($response);
    } catch (Exception $exception) {

        wp_send_json_error('Failed to delete pickup point: ' . $exception->getMessage(), 500);

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    }

    return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
});

add_action('admin_post_edit_service', static function() {
    return (new ApiRequestsHandler())->editService();
});

add_action('admin_post_add_awb', static function () {
    $postFields = Helper::sanitizeInputs($_POST);
    $orderDetails = wc_get_order($postFields['samedaycourier-order-id']);
    if (empty($orderDetails)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    $data = array_merge($postFields, $orderDetails->get_data());
    
    try {
        return (new ApiRequestsHandler())->postAwb($data);
    } catch (Exception $e) {
        Helper::addFlashNotice('add_awb_notice', $e->getMessage(), 'error',true);
    };
});

add_action('admin_post_remove-awb', static function () {
    $awb = QueryHandler::getAwbForOrderId((int) sanitize_key($_POST['order-id']));
    $nonce = $_POST['_wpnonce'];
    if (empty($awb)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    try {
        return (new ApiRequestsHandler())->removeAwb($awb, $nonce);
    } catch (Exception $e) {
        Helper::addFlashNotice('add_awb_notice', $e->getMessage(), 'error',true);

        return wp_redirect(admin_url() . '/index.php');
    }
});

add_action('admin_post_show-awb-pdf', static function (){
    $orderId = (int) sanitize_key($_POST['order-id']);
	$nonce = $_POST['_wpnonce'];
    if (!isset($orderId)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    try {
        return (new ApiRequestsHandler())->showAwbAsPdf($orderId, $nonce);
    } catch (Exception $exception) {
        Helper::addFlashNotice('add_awb_notice', $exception->getMessage(), 'error',true);

        return wp_redirect(admin_url() . '/index.php');
    }
});

add_action('admin_post_add-new-parcel', function() {
    $postFields = Helper::sanitizeInputs($_POST);
    if (empty($postFields)) {
        return wp_redirect(admin_url() . '/index.php');
    }

    try {
        return (new ApiRequestsHandler())->addNewParcel($postFields);
    } catch (Exception $exception) {
        Helper::addFlashNotice('add_awb_notice', $exception->getMessage(), 'error',true);

        return wp_redirect(admin_url() . '/index.php');
    }
});

// Open Package :
function wps_sameday_shipping_options_layout() {
    // If you are not in Checkout page don't do anything
    if (!is_checkout()) {
        return;
    }

    $service = QueryHandler::getServiceSamedayByCode(
        Helper::getChosenShippingMethodCode(),
        Helper::isTesting()
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
        if ($optionalTax->getCode() === Helper::OPEN_PACKAGE_OPTION_CODE) {
            $taxOpenPackage = $optionalTax->getId();
        }
    }

    if ($taxOpenPackage
        && Helper::getSamedaySettings()['open_package_status'] === "yes"
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
                                    'label' => Helper::getSamedaySettings()['open_package_label'],
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
            WC()->session->set('locker', Helper::sanitizeLocker($locker));
        } else {
            WC()->session->set('locker', (int) $locker);
        }

        return;
    }

    if (null !== $openPackage = $_POST['open_package'] ?? null) {
	    WC()->session->set('open_package', Helper::sanitizeInput($openPackage));

        return;
    }

    if (isset($_POST['payment_method'])) {
	    WC()->session->set('payment_method', Helper::sanitizeInput($_POST['payment_method']));

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

	$repayment_tax = (int) (Helper::getSamedaySettings()['repayment_tax'] ?? null);

    if ($repayment_tax > 0
        && Helper::CASH_ON_DELIVERY === WC()->session->get('chosen_payment_method')
    ) {
        $repayment_tax_label = Helper::getSamedaySettings()['repayment_tax_label'] ?? __('Repayment tax', Helper::TEXT_DOMAIN);
        $woocommerce->cart->add_fee($repayment_tax_label, $repayment_tax, true, '');
    }
}

// LOCKER :
function wps_locker_row_layout() {
    $serviceCode = Helper::getChosenShippingMethodCode();

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

    if ((Helper::isOohDeliveryOption($serviceCode)) && is_checkout()) { ?>
        <?php if ((Helper::getSamedaySettings()['lockers_map'] ?? null) === "yes") { ?>
            <tr class="shipping-pickup-store">
                <td><strong><?php echo __('Sameday Locker', Helper::TEXT_DOMAIN) ?></strong></td>
                <th>
                    <button type="button" class="button alt sameday_select_locker"
                        id="select_locker"
                        data-username='<?php echo Helper::getSamedaySettings()['user']; ?>'
                        data-country='<?php echo Helper::getSamedaySettings()['host_country']; ?>'
                    >
                        <?php echo __('Show Locations Map', Helper::TEXT_DOMAIN) ?>
                    </button>
                </th>
            </tr>
            <?php if (null !== $shipTo) { ?>
                <tr id="showSamedayLockerDetailsCheckoutLine" class="shipping-pickup-store">
                    <td><strong> <?= __('Ship to', Helper::TEXT_DOMAIN) ?> </strong></td>
                    <th><span id="showLockerDetails"><?= $shipTo ?></span></th>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <?php
                $cities = QueryHandler::getCitiesWithLockers(Helper::isTesting());
                $lockers = array();
                foreach ($cities as $city) {
                    if (null !== $city->city) {
                        $lockers[$city->city . ' (' . $city->county . ')'] = QueryHandler::getLockersByCity(
                            $city->city,
                            Helper::isTesting()
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
                                <?= __('Select easyBox', Helper::TEXT_DOMAIN) ?>
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

    if (Helper::isOohDeliveryOption(Helper::getChosenShippingMethodCode())) {
        try {
            Helper::addLockerToOrderData(
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

    if (Helper::isOohDeliveryOption(Helper::getChosenShippingMethodCode())) {
        try {
            Helper::addLockerToOrderData(
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
            Helper::showFlashNotice('add_awb_notice');
        }

        if ($_GET["add-awb"] === "success") {
            Helper::printFlashNotice('success', __("Awb was successfully generated !", Helper::TEXT_DOMAIN), true);
        }
    }

    if (isset($_GET["remove-awb"])) {
        if ($_GET["remove-awb"] === "error") {
            Helper::showFlashNotice('remove_awb_notice');
        }

        if ($_GET["remove-awb"] === "success") {
            Helper::printFlashNotice('success', __("Awb was successfully removed !", Helper::TEXT_DOMAIN), true);
        }
    }

    if (isset($_GET["show-awb"]) && $_GET["show-awb"] === "error") {
        Helper::printFlashNotice('error', __("Awb invalid !", Helper::TEXT_DOMAIN), true);
    }

    if (isset($_GET["add-new-parcel"])) {
        if ($_GET["add-new-parcel"] === "error") {
            Helper::showFlashNotice('add_new_parcel_notice');
        }

        if ($_GET["add-new-parcel"] === "success") {
            Helper::printFlashNotice('success', __("New parcel has been added to this awb!", Helper::TEXT_DOMAIN) , true);
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

add_action( 'woocommerce_admin_order_data_after_shipping_address', static function ( $order ) {
    add_thickbox();
    if ($_GET['action'] === 'edit') {
        $_generateAwb = '
            <p class="form-field form-field-wide wc-customer-user">
                <a href="#TB_inline?&width=1000&height=470&inlineId=sameday-shipping-content-add-awb" class="button-primary button-samll thickbox"> ' . __('Generate awb') . ' </a>
            </p>';

        $_showAwb = '
            <p class="form-field form-field-wide wc-customer-user">
                <a href="#TB_inline?&width=670&height=470&inlineId=sameday-shipping-content-add-new-parcel" class="button-primary button-samll thickbox"> ' . __('Add new parcel') . ' </a>
                <a href="#TB_inline?&width=1024&height=400&inlineId=sameday-shipping-content-awb-history" class="button-primary button-samll thickbox"> ' . __('Awb history') . ' </a>
                <input type="hidden" form="showAsPdf" name="order-id" value="' . $order->get_id() . '">
                <button type="submit" form="showAsPdf" formtarget="_blank" class="button-primary button-samll">'.  __('Show as pdf', Helper::TEXT_DOMAIN) . ' </button>
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

        $shipping_method_sameday = Helper::getShippingMethodSameday($order->get_id());

        $newParcelModal = '';
        $historyModal = '';
        $_goTo_eAWB = '';

        if (! empty($shipping_method_sameday)) {
            $buttons = '
                <div class="address">
                    ' . $_showAwb . $_removeAwb  .'
                </div>';

            $sameday = new ApiRequestsHandler();
            $awbHistoryTable = $sameday->showAwbHistory($order->get_id());

            $addNewParcelForm = samedaycourierAddNewParcelForm($order->get_id());

            $newParcelModal = '<div id="sameday-shipping-content-add-new-parcel" style="display: none;">
                            ' . $addNewParcelForm . ' 
                           </div>';

            $historyModal = '<div id="sameday-shipping-content-awb-history" style="display: none;">
                            ' . $awbHistoryTable . ' 
                         </div>';

            $awb = QueryHandler::getAwbForOrderId(sanitize_key($order->get_id()));
            $redirectToEawbSite = sprintf(
                    '%s/awb?awbOrParcelNumber=%s&tab=allAwbs',
                    Helper::EAWB_INSTANCES[Helper::getHostCountry()],
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
        $serviceCode = Helper::parseShippingMethodCode($chosen_methods[0]);
        if (Helper::isOohDeliveryOption($serviceCode) && null === WC()->session->get('locker')) {
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

register_activation_hook(__FILE__, [SchemaHandler::class, 'install']);
register_uninstall_hook(__FILE__, [SchemaHandler::class, 'uninstall']);

function enqueue_button_scripts(): void
{
    if (is_checkout()) {
        wp_enqueue_script( 'lockerpluginsdk','https://cdn.sameday.ro/locker-plugin/lockerpluginsdk.js', ['jquery']);
        wp_enqueue_style( 'sameday-admin-style', plugin_dir_url( __FILE__ ). 'assets/css/sameday_front_button.css' );
        wp_enqueue_script( 'custom-checkout-button', plugins_url( 'assets/js/custom-checkout-button.js', __FILE__ ), array( 'jquery' ), time(), true );

        if (Helper::isUseSamedayNomenclator()) {
	        wp_enqueue_script('county-city-handle',
                plugins_url( 'assets/js/county-city-handle.js', __FILE__ ),
                [
                    'jquery',
                    'select2'
                ]
            );
	        wp_localize_script('county-city-handle',
                'samedayCourierData',
                [
		            'cities' => QueryHandler::getCachedCities(),
                ]
            );
        }

        // Localize the script with your dynamic PHP values
        wp_localize_script( 'custom-checkout-button', 'samedayData', array(
            'username' => Helper::getSamedaySettings()['user'] ?? null,
            'country'  => Helper::getSamedaySettings()['host_country'] ?? null,
            'buttonText' => __('Show Locations Map', Helper::TEXT_DOMAIN),
        ));
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_button_scripts');

add_filter('woocommerce_cart_shipping_method_full_label', static function ($label, $method) {
    return $method->get_meta_data()['currency_conversion_label'] ?? $label;
}, 10, 2);