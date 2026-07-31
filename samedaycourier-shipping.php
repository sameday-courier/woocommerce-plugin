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

use Sameday\Objects\Service\OptionalTaxObject;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Shipping\Method\SamedayCourier;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Woo\WooOrderAddressRepository;
use SamedayCourier\Shipping\Application\Sql\PluginHandler;
use SamedayCourier\Shipping\Domain\SamedaySettings;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\ShowHistoryAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Services\ControllersRegisterService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderPostMetaUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderSamedayShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbForm;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Locker\LockerInstance;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\PickupPoint\PickupPointInstance;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Service\ServiceInstance;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\NewParcelForm;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\RegistryHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CssStylesheetsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\JsScriptsHandler;

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

// Shipping Method init.
add_filter('woocommerce_shipping_methods', static function (array $methods): array {
    $methods[SamedayConstants::PLUGIN_NAME] = SamedayCourier::class;

    return $methods;
});
RegistryHandler::register();

// Open Package :
function wps_sameday_shipping_options_layout() {
    // If you are not in Checkout page don't do anything
    if (!is_checkout()) {
        return;
    }

    $samedayServiceRepository = new SamedayServiceRepository();
    $service = $samedayServiceRepository->getServiceSamedayByCode(WooShippingMethodProvider::getChosenServiceCode());

    /** @var OptionalTaxObject[] $optionalTaxes */
    $optionalTaxes = [];
    if (null !== $service && null !== $service->getServiceOptionalTaxes() && '' !== $service->getServiceOptionalTaxes()) {
        $optionalTaxes = unserialize($service->getServiceOptionalTaxes(), ['']);
        if (!$optionalTaxes) {
            $optionalTaxes = [];
        }
    }

    $taxOpenPackage = 0;
    foreach ($optionalTaxes as $optionalTax) {
        if ($optionalTax->getCode() === SamedayConstants::OPEN_PACKAGE_OPTION_CODE) {
            $taxOpenPackage = $optionalTax->getId();
        }
    }

    if ($taxOpenPackage
        && SamedaySettings::isOpenPackageStatusEnabled()
    ) {
        ?>
            <tr class="shipping-pickup-store">
                <th></th>
                <td>
                    <ul id="shipping_method" class="woocommerce-shipping-methods sameday-shipping-methods">
                        <li>
                            <?php
                                woocommerce_form_field('open_package',
                                [
                                    'type' => 'checkbox',
                                    'class' => array('input-checkbox'),
                                    'id' => 'sameday_open_package',
                                    'label' => SamedaySettings::getOpenPackageLabel(),
                                    'required' => false,
                                ],
                                'yes' === WooSessionHandler::get(SamedaySessionKeys::OPEN_PACKAGE)
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
    foreach (WooHandler::getWC()->cart->get_shipping_packages() as $package_key => $package) {
	    $package['package_hash'] = 'wc_ship_' . md5( wp_json_encode($package) . WC_Cache_Helper::get_transient_version('shipping'));
        WooSessionHandler::set(SamedaySessionKeys::shippingForPackage((int) $package_key), $package);
    }

    WooHandler::getWC()->cart->calculate_shipping();
}

add_action('woocommerce_cart_calculate_fees', 'checkout_repayment_tax', 100);
function checkout_repayment_tax() {
  global $woocommerce;
	if (!defined( 'DOING_AJAX') && is_admin()) {
		return;
    }

	$repayment_tax = SamedaySettings::getRepaymentTax() ?? 0;

    if ($repayment_tax > 0
        && SamedayConstants::CASH_ON_DELIVERY === WooSessionHandler::get(SamedaySessionKeys::CHOSEN_PAYMENT_METHOD)
    ) {
        $repayment_tax_label = SamedaySettings::getRepaymentTaxLabel() ?? TranslatorHandler::translate('Repayment tax');
        $woocommerce->cart->add_fee($repayment_tax_label, $repayment_tax, true, '');
    }
}

// LOCKER :
function wps_locker_row_layout() {
    $serviceCode = WooShippingMethodProvider::getChosenServiceCode();

    $shipTo = null;
    if (null !== $lockerSession = WooSessionHandler::get(SamedaySessionKeys::LOCKER)) {
        try {
            $lockerSession = json_decode($lockerSession, false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {}

        $shipTo = sprintf(
                '%s <br/> %s',
            esc_html($lockerSession->name ?? ''),
            esc_html($lockerSession->address ?? '')
        );
    }

    if ((new SamedayServiceRules(new SamedayServiceRepository()))->isOohDeliveryOptionByCode($serviceCode) && is_checkout()) { ?>
        <?php if (SamedaySettings::isLockersMapEnabled()) { ?>
            <tr class="shipping-pickup-store">
                <td><strong><?php echo TranslatorHandler::translate('Sameday Locker') ?></strong></td>
                <th>
                    <button type="button" class="button alt sameday_select_locker"
                        id="select_locker"
                        data-username='<?php echo esc_attr(SamedaySettings::getUser() ?? ''); ?>'
                        data-country='<?php echo esc_attr(SamedaySettings::getHostCountry()); ?>'
                    >
                        <?php echo TranslatorHandler::translate('Show Locations Map') ?>
                    </button>
                </th>
            </tr>
            <?php if (null !== $shipTo) { ?>
                <tr id="showSamedayLockerDetailsCheckoutLine" class="shipping-pickup-store">
                    <td><strong> <?= TranslatorHandler::translate('Ship to') ?> </strong></td>
                    <th><span id="showLockerDetails"><?php echo wp_kses_post($shipTo); ?></span></th>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <?php
                $samedayLockerRepository = new SamedayLockerRepository();
                $cities = $samedayLockerRepository->getCitiesWithLockers();
                $lockers = array();
                foreach ($cities as $city) {
                    if (null !== $city->getCity()) {
                        $lockers[$city->getCity() . ' (' . $city->getCounty() . ')'] = $samedayLockerRepository->getLockersByCity(
                            (string) $city->getCity()
                        );
                    }
                }

                $lockerOptions = '';
                foreach ($lockers as $city => $cityLockers) {
                    $optionGroup = '<optgroup label="' . esc_attr($city) . '" class="sameday-locker-optgroup"></optgroup>';
                    $options = '';
                    foreach ($cityLockers as $locker) {
                        $lockerDetails = esc_html($locker->getName() . ' - ' . $locker->getAddress());
                        $isSelected = null;
                        if ((int) WooSessionHandler::get(SamedaySessionKeys::LOCKER) === (int) $locker->getLockerId()) {
                            $isSelected = "selected='selected'";
                        }
                        $options .= sprintf(
                            '<option value="%s" class="sameday-locker-option" %s> %s </option>',
                            esc_attr((string) $locker->getLockerId()),
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
                        <select name="locker_id" id="shipping-pickup-store-select">
                            <option value="" class="sameday-locker-placeholder">
                                <?= TranslatorHandler::translate('Select easyBox') ?>
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
    if ((new SamedayServiceRules(new SamedayServiceRepository()))->isOohDeliveryOptionByCode(WooShippingMethodProvider::getChosenServiceCode())) {
        try {
            (new WooLockerOrderDataHandler(
                new WooLockerOrderPostMetaUpdater(
                    new SamedayLockerRepository(),
                    new WooOrderShippingAddressUpdater(
                        new WooOrderAddressRepository(),
                    ),
                ),
            ))->add(
                $order->get_id(),
                WooSessionHandler::get(SamedaySessionKeys::LOCKER)
            );
        } catch (Exception $exception) {}
    }

    WooOpenPackageOrderDataHandler::saveFromSession($order->get_id());
});

add_action('woocommerce_checkout_order_processed', static function ($orderId): void {
    if ((new SamedayServiceRules(new SamedayServiceRepository()))->isOohDeliveryOptionByCode(WooShippingMethodProvider::getChosenServiceCode())) {
        try {
            (new WooLockerOrderDataHandler(
                new WooLockerOrderPostMetaUpdater(
                    new SamedayLockerRepository(),
                    new WooOrderShippingAddressUpdater(
                        new WooOrderAddressRepository(),
                    ),
                ),
            ))->add(
                $orderId,
                WooSessionHandler::get(SamedaySessionKeys::LOCKER)
            );
        } catch (Exception $exception) {}
    }

    WooOpenPackageOrderDataHandler::saveFromSession($orderId);
});

add_action('admin_head', static function () {
    echo '<form id="addAwbForm" method="POST" action="'.admin_url('admin-post.php').'">
                <input type="hidden" name="action" value="add-awb">
                <input type="hidden" name="_wpnonce" value="'.wp_create_nonce('add-awb').'">
          </form>
          <form id="showAsPdf"  method="POST" action="'.admin_url('admin-post.php').'">
                <input type="hidden" name="action" value="show-as-pdf">
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
    if (isset($_GET['action']) && 'edit' === $_GET['action']) {
        $_generateAwb = '
            <p class="form-field form-field-wide wc-customer-user">
                <a href="#TB_inline?&width=1000&height=470&inlineId=sameday-shipping-content-add-awb" class="sameday_admin_button button-samll thickbox"> ' . TranslatorHandler::translate('Generate awb') . ' </a>
            </p>';

        $_showAwb = '
            <p class="form-field form-field-wide wc-customer-user">
                <a href="#TB_inline?&width=670&height=470&inlineId=sameday-shipping-content-add-new-parcel" class="sameday_admin_button button-samll thickbox"> ' . TranslatorHandler::translate('Add new parcel') . ' </a>
                <a href="#TB_inline?&width=1024&height=400&inlineId=sameday-shipping-content-awb-history" class="sameday_admin_button button-samll thickbox"> ' . TranslatorHandler::translate('Awb history') . ' </a>
                <input type="hidden" form="showAsPdf" name="order-id" value="' . $order->get_id() . '">
                <button type="submit" form="showAsPdf" formtarget="_blank" class="sameday_admin_button button-samll">'.  TranslatorHandler::translate('Show as pdf') . ' </button>
            </p>';

        $_removeAwb = '
            <p class="form-field form-field-wide wc-customer-user">
                <input type="hidden" form="removeAwb" name="order-id" value="' . $order->get_id() . '">
                <button type="submit" form="removeAwb" class="sameday_admin_button button-samll">'.  TranslatorHandler::translate('Remove Awb') . ' </button>
            </p>';

        $buttons = '
                <div class="address">
                    ' . $_generateAwb . '
                </div>';

        $shipping_method_sameday = (new WooOrderSamedayShippingMethodProvider(
            new SamedayAwbRepository(),
        ))->get($order->get_id());

        $newParcelModal = '';
        $historyModal = '';
        $_goTo_eAWB = '';

        if (! empty($shipping_method_sameday)) {
            $buttons = '
                <div class="address">
                    ' . $_showAwb . $_removeAwb  .'
                </div>';

            $awbHistoryTable = (new ShowHistoryAwbController())->render((int) $order->get_id());

            $addNewParcelForm = NewParcelForm::addNewParcelForm($order->get_id());

            $newParcelModal = '<div id="sameday-shipping-content-add-new-parcel" style="display: none;">
                            ' . $addNewParcelForm . ' 
                           </div>';

            $historyModal = '<div id="sameday-shipping-content-awb-history" style="display: none;">
                            ' . $awbHistoryTable . ' 
                         </div>';
            $samedayAwbRepository = new SamedayAwbRepository();
            $awb = $samedayAwbRepository->getAwbForOrderId((int) sanitize_key($order->get_id()));
            if (null !== $awb && null !== $awb->getAwbNumber()) {
                $redirectToEawbSite = sprintf(
                        '%s/awb?awbOrParcelNumber=%s&tab=allAwbs',
                        SamedayConstants::EAWB_INSTANCES[SamedaySettings::getHostCountry()],
                        $awb->getAwbNumber()
                );

                $_goTo_eAWB = '
                    <p class="form-field form-field-wide wc-customer-user">
                        <a href="' . $redirectToEawbSite . '" target="_blank" class="sameday_admin_button button-samll">'.  TranslatorHandler::translate('Sameday eAwb') . ' </a>
                    </p>
                ';
            }
        }

        $awbModal = (new AwbForm(
            new SamedayServiceRepository(),
            new SamedayLockerRepository(),
            new SamedayPickupPointRepository()
        ))->samedaycourierAddAwbForm($order);

        echo $buttons . $awbModal . $newParcelModal . $historyModal . $_goTo_eAWB;
    }
});

// Revision order before Submit
add_action('woocommerce_checkout_process', static function () {
    $serviceCode = WooShippingMethodProvider::getChosenServiceCode();
    if ('' !== $serviceCode) {
        $samedayServiceRules = new SamedayServiceRules(new SamedayServiceRepository());
        $isOOhDelivery = $samedayServiceRules->isOohDeliveryOptionByCode($serviceCode);
        $isOOhButUserNotSelectLocker = $isOOhDelivery && (null === WooSessionHandler::get(SamedaySessionKeys::LOCKER));
        if ($isOOhButUserNotSelectLocker) {
            wc_add_notice(TranslatorHandler::translate('Please choose your EasyBox Locker !'), 'error');
        }
    }
});

// Insert links to eAWB ::
add_filter('plugin_row_meta', static function ($links, $pluginFileName) {
    if (strpos($pluginFileName, basename(__FILE__))) {
        $pathToSettings = admin_url() . 'admin.php?page=wc-settings&tab=shipping&section=samedaycourier';
        $pathToEawb = 'https://eawb.sameday.ro/';
        $links[] = '<a href="'. esc_html__($pathToSettings, 'woocommerce') .'" target="_blank"> '. esc_html__( 'Settings', 'woocommerce' ) .' </a>';
        $links[] = '<a href="'. esc_html__($pathToEawb, 'woocommerce') .'" target="_blank"> '. esc_html__( 'eAWB', 'woocommerce' ) .' </a>';
    }

    return $links;
}, 10, 4);

register_activation_hook(__FILE__, [PluginHandler::class, 'install']);
register_uninstall_hook(__FILE__, [PluginHandler::class, 'uninstall']);

add_filter('woocommerce_cart_shipping_method_full_label', static function ($label, $method) {
    return $method->get_meta_data()['currency_conversion_label'] ?? $label;
}, 10, 2);