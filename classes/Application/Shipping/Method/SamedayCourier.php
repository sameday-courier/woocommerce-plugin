<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Shipping\Method;

if (!defined('ABSPATH')) {
    exit;
}

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\AwbPdfType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbEstimationRequest;
use Sameday\Responses\SamedayPostAwbEstimationResponse;
use Sameday\Sameday;
use Sameday\SamedayClient;
use SamedayCourier\Shipping\Domain\BgnCurrencyConverter;
use SamedayCourier\Shipping\Domain\Models\SamedayLocker;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceSelector;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Utils\Helper;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use WC_Admin_Settings;
use WC_Shipping_Method;

final class SamedayCourier extends WC_Shipping_Method
{
    /**
     * @var SamedayServiceSelector
     */
    private SamedayServiceSelector $samedayServiceSelector;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @var SamedayServiceRules
     */
    private SamedayServiceRules $samedayServiceRules;

    /**
     * SamedayCourier_Shipping_Method constructor.
     *
     * @param int $instance_id
     */
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id = 'samedaycourier';
        $this->method_title = __('SamedayCourier', SamedayConstants::TEXT_DOMAIN);
        $this->method_description = __(
            'Custom Shipping Method for SamedayCourier',
            SamedayConstants::TEXT_DOMAIN
        );

        $this->supports = array(
            'settings',
            'shipping-zones',
            'instance-settings'
        );

        $samedayServiceRepository = new SamedayServiceRepository();
        $this->samedayServiceSelector = new SamedayServiceSelector($samedayServiceRepository);
        $this->samedayPickupPointRepository = new SamedayPickupPointRepository();
        $this->samedayServiceRepository = new SamedayServiceRepository();
        $this->samedayServiceRules = new SamedayServiceRules($samedayServiceRepository);

        $this->init();
    }

    /**
     * @param array $package
     * @return void
     *
     * @throws SamedaySDKException
     */
    public function calculate_shipping($package = array()): void
    {
        if ($this->settings['enabled'] === 'no') {
            return;
        }

        $eligibleServices = $this->samedayServiceSelector->getEligibleServices(
            $package['destination']['country'] ?? SamedayConstants::API_HOST_LOCALE_RO
        );

        if (empty($eligibleServices)) {
            return;
        }

        $useEstimatedCost = $this->settings['estimated_cost'];
        $estimatedCostExtraFee = (int) $this->settings['estimated_cost_extra_fee'];
        $useLockerMap = $this->settings['lockers_map'] === 'yes';
        $cartValue = WC()->cart->get_subtotal();

        if (true === Helper::isApplyFreeShippingAfterDiscount()) {
            $cartValue = WC()->cart->get_cart_contents_total();
        }

        $stateName = Helper::convertStateCodeToName(
            $package['destination']['country'],
            $package['destination']['state']
        );

        foreach ($eligibleServices as $service) {
//            if (!$this->samedayServiceRules->isEligibleTo6H($service, $stateName)) {
//                continue;
//            }

            if (Helper::isOohDeliveryOption($service->getSamedayCode())) {
                if (null === $lockerMaxItems = $this->settings['locker_max_items'] ?? null) {
                    $lockerMaxItems = SamedayConstants::DEFAULT_VALUE_LOCKER_MAX_ITEMS;
                }

                if (count(WC()->cart->get_cart()) > ((int) $lockerMaxItems)) {
                    continue;
                }
            }

            $price = $service->getPrice() ?? 0.0;

            if (
                '' !== $package['destination']['city']
                && '' !== $stateName
                && '' !== $package['destination']['address']
                && $useEstimatedCost !== 'no'
            ) {
                $estimatedCost = $this->getEstimatedCost($package['destination'], $service->getSamedayId());
                if ($estimatedCost instanceof SamedayPostAwbEstimationResponse) {
                    $estimatedPrice = $estimatedCost->getCost();
                    $estimatedCurrency = $estimatedCost->getCurrency();
                        if (($useEstimatedCost === 'yes')
                        || ($useEstimatedCost === 'btfp' && ($service->getPrice() ?? 0) < $estimatedPrice)
                    ) {
                        if ($estimatedCostExtraFee > 0) {
                            $estimatedPrice += (float) number_format($price * ($estimatedCostExtraFee /100), 2, '.', '');
                        }
                        $price = $estimatedPrice;

                        // Business logic for Bulgaria Currency Rules
                        $storeCurrency = get_woocommerce_currency();
                        if (($storeCurrency !== $estimatedCurrency)
                            && (Helper::getHostCountry() === SamedayConstants::API_HOST_LOCALE_BG)
                        ) {
                            try {
                                $bgnCurrencyConverter = new BgnCurrencyConverter($storeCurrency, $price);
                                $price = $bgnCurrencyConverter->convert();
                                $currencyConversionLabel = $bgnCurrencyConverter->buildCurrencyConversionLabel(
                                    $service->getName() ?? '',
                                    $price,
                                    $storeCurrency,
                                    number_format($estimatedPrice, 2),
                                    $estimatedCurrency
                                );
                            } catch (Exception $exception) {}
                        }
                    }
                }
            }

            if ($service->getPriceFree() !== null && ($cartValue > $service->getPriceFree())) {
                $price = .0;
            }

            $rate = array(
                'id' => sprintf('%s:%s:%s', $this->id, $service->getSamedayId(), $service->getSamedayCode()),
                'label' => $service->getName() ?? '',
                'cost' => $price,
                'meta_data' => array(
                    'service_id' => $service->getSamedayId(),
                    'service_code' => $service->getSamedayCode()
                )
            );

            if (isset($currencyConversionLabel)) {
                $rate['meta_data']['currency_conversion_label'] = $currencyConversionLabel;
            }

            if ((false === $useLockerMap)
                && ($service->getSamedayCode() === SamedayConstants::LOCKER_NEXT_DAY_CODE)
            ) {
                $this->syncLockers();
                $rate['lockers'] = array_map(
                    static function (SamedayLocker $locker): array {
                        return [
                            'id' => $locker->getId(),
                            'locker_id' => $locker->getLockerId(),
                            'name' => $locker->getName(),
                            'city' => $locker->getCity(),
                            'county' => $locker->getCounty(),
                            'address' => $locker->getAddress(),
                            'lat' => $locker->getLat(),
                            'lng' => $locker->getLng(),
                            'postal_code' => $locker->getPostalCode(),
                            'boxes' => $locker->getBoxes(),
                            'is_testing' => $locker->getIsTesting(),
                        ];
                    },
                    SamedayLockerRepository::getLockers()
                );
            }

            $this->add_rate($rate);
        }
    }

    /**
     * @return void
     * @throws SamedaySDKException
     */
    private function syncLockers(): void
    {
        $time = time();

        $ltSync = $this->settings['sameday_sync_lockers_ts'];

        if ($time > ($ltSync + 86400)) {
            (new RefreshLocker(
                new RefreshLockerRequest(!empty(OptionsHandler::getSamedayOptions()), true)
            ))->execute();
        }
    }

    /**
     * @param $address
     * @param $serviceId
     *
     * @return SamedayPostAwbEstimationResponse|null
     */
    private function getEstimatedCost($address, $serviceId): ?SamedayPostAwbEstimationResponse
    {
        $pickupPointId = $this->samedayPickupPointRepository->getDefaultPickupPointId();
        $weight = Helper::convertWeight(WC()->cart->get_cart_contents_weight()) ?: .1;
        $state = Helper::convertStateCodeToName($address['country'], $address['state']);
        $city = Helper::removeAccents($address['city']);
        $currency = SamedayConstants::CURRENCY_MAPPER[$address['country']];

        $optionalServices = $this->samedayServiceRepository->getServiceIdOptionalTaxes((int) $serviceId);
        $serviceTaxIds = array();
        if (WC()->session->get('open_package') === 'yes') {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::OPEN_PACKAGE_OPTION_CODE
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
        if (isset($paymentMethod) && ($paymentMethod !== SamedayConstants::CASH_ON_DELIVERY)) {
            $repaymentAmount = 0;
        }

        $estimateCostRequest = new SamedayPostAwbEstimationRequest(
            $pickupPointId,
            null,
            new PackageType(
                PackageType::PARCEL
            ),
            [new ParcelDimensionsObject($weight)],
            $serviceId,
            new AwbPaymentType(
                AwbPaymentType::CLIENT
            ),
            new AwbRecipientEntityObject(
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

        try {
            $sameday = new Sameday(SdkInitiator::init());
        } catch (Exception $exception) {
            return null;
        }

        try {
            return $sameday->postAwbEstimation($estimateCostRequest);
        } catch (Exception $exception) {
            return null;
        }
    }

    private function init(): void
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable', SamedayConstants::TEXT_DOMAIN),
                'type' => 'checkbox',
                'description' => __('Enable this shipping.', SamedayConstants::TEXT_DOMAIN),
                'default' => 'yes'
            ),

            'title' => array(
                'title' => __('Title', SamedayConstants::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Title to be display on site', SamedayConstants::TEXT_DOMAIN),
                'default' => __('SamedayCourier Shipping', SamedayConstants::TEXT_DOMAIN)
            ),

            'user' => array(
                'title' => __('Username', SamedayConstants::TEXT_DOMAIN) . ' *',
                'type' => 'text',
                'description' => __('Username', SamedayConstants::TEXT_DOMAIN),
                'default' => __('')
            ),

            'password' => array(
                'title' => __('Password', SamedayConstants::TEXT_DOMAIN) . ' *',
                'type' => 'password',
                'description' => __('Password', SamedayConstants::TEXT_DOMAIN),
                'default' => __('')
            ),

            'default_label_format' => array(
                'title'   => __('Default label format', SamedayConstants::TEXT_DOMAIN) . ' *',
                'default' => 'A4',
                'type'    => 'select',
                'options' => [
                    'A4' => __(AwbPdfType::A4, SamedayConstants::TEXT_DOMAIN),
                    'A6' => __(AwbPdfType::A6, SamedayConstants::TEXT_DOMAIN),
                ],
                'description' => __('Awb paper format', SamedayConstants::TEXT_DOMAIN)
            ),

            'estimated_cost' => array(
                'title'   => __('Use estimated cost', SamedayConstants::TEXT_DOMAIN) . ' *',
                'default' => 'no',
                'type'    => 'select',
                'options' => [
                    'no' => __('Never', SamedayConstants::TEXT_DOMAIN),
                    'yes' => __('Always', SamedayConstants::TEXT_DOMAIN),
                    'btfp' => __('If its cost is bigger than fixed price', SamedayConstants::TEXT_DOMAIN)
                ],
                'description' => __('This is the shipping cost calculated by Sameday Api for each service. <br/> 
                            Never* You choose to display only the fixed price that you set for each service<br/>
                            Always* You choose to display only the price estimated by SamedayCourier API<br/>
                            If its cost is bigger than fixed price* You choose to display the cost estimated by 
                            SamedayCourier Api only in the situation that this cost exceed the fixed price set by you for each service.
                        ', SamedayConstants::TEXT_DOMAIN)
            ),

            'estimated_cost_extra_fee' => array(
                'title' => __('Extra fee', SamedayConstants::TEXT_DOMAIN),
                'type' => 'number',
                'css' => 'width:100px;',
                'description' => __('Apply extra fee on estimated cost. This is a % value. <br/> If you don\'t want to add extra fee on estimated cost value, such as T.V.A. leave this field blank or 0', SamedayConstants::TEXT_DOMAIN),
                'custom_attributes' => array(
                    'min' => 0,
                    'onkeypress' => 'return (event.charCode !=8 && event.charCode == 0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))',
                    'data-placeholder' => __('Extra fee', SamedayConstants::TEXT_DOMAIN)
                ),
                'default' => 0
            ),

            'repayment_tax_label' => array(
                'title' => __('Repayment tax label', SamedayConstants::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Label for repayment tax. This appear in checkout page.', SamedayConstants::TEXT_DOMAIN),
                'default' => __('', SamedayConstants::TEXT_DOMAIN)
            ),

            'repayment_tax' => array(
                'title' => __('Repayment tax', SamedayConstants::TEXT_DOMAIN),
                'type' => 'number',
                'description' => __('Add extra fee on checkout.', SamedayConstants::TEXT_DOMAIN),
                'default' => __('', SamedayConstants::TEXT_DOMAIN)
            ),


            'open_package_status' => array(
                'title' => __('Open package status', SamedayConstants::TEXT_DOMAIN),
                'type' => 'checkbox',
                'description' => __('Enable this option if you want to offer your customers the opening of the package at delivery time.', SamedayConstants::TEXT_DOMAIN),
                'default' => 'no'
            ),

            'discount_free_shipping' => array(
                'title' => __('Free shipping after discount', SamedayConstants::TEXT_DOMAIN),
                'type' => 'checkbox',
                'description' => __(
                    'Enable this option if you want to apply free shipping to be calculated after discount.
                            Otherwise the free shipping will be apply without taking into account the applied discount.
                            This field is relevant if you choose free delivery price option.',
                    SamedayConstants::TEXT_DOMAIN
                ),
                'default' => 'no'
            ),

            'open_package_label' => array(
                'title' => __('Open package label', SamedayConstants::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('This appear in checkout page', SamedayConstants::TEXT_DOMAIN),
                'default' => __('', SamedayConstants::TEXT_DOMAIN)
            ),

            'locker_max_items' => array(
                'title' => __('Locker max. items', SamedayConstants::TEXT_DOMAIN),
                'type' => 'number',
                'description' => __('The maximum amount of items accepted inside the locker', SamedayConstants::TEXT_DOMAIN),
                'default' => SamedayConstants::DEFAULT_VALUE_LOCKER_MAX_ITEMS
            ),

            'lockers_map' => array(
                'title'   => __('Show locker map method', SamedayConstants::TEXT_DOMAIN),
                'default' => 'yes',
                'type'    => 'select',
                'options' => [
                    'no' => __('Drop-down list', SamedayConstants::TEXT_DOMAIN),
                    'yes' => __('Interactive Map', SamedayConstants::TEXT_DOMAIN),
                ]
            ),

            'is_testing' => array(
                'title' => __('Env. Mode', SamedayConstants::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('The value of this field will be appear automatically after you complete the authentication', SamedayConstants::TEXT_DOMAIN),
                'default' => 2,
                'disabled' => true,
                'options' => array(
                    SamedayConstants::API_PROD => __('Prod', SamedayConstants::TEXT_DOMAIN),
                    SamedayConstants::API_DEMO => __('Demo', SamedayConstants::TEXT_DOMAIN),
                    2 => '',
                ),
            ),

            'host_country' => array(
                'title' => __('Env. Host Country', SamedayConstants::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('The value of this field will be appear automatically after you complete the authentication', SamedayConstants::TEXT_DOMAIN),
                'default' => 'none',
                'disabled' => true,
                'options' => array(
                    SamedayConstants::API_HOST_LOCALE_RO => __(SamedayConstants::API_HOST_LOCALE_RO, SamedayConstants::TEXT_DOMAIN),
                    SamedayConstants::API_HOST_LOCALE_HU => __(SamedayConstants::API_HOST_LOCALE_HU, SamedayConstants::TEXT_DOMAIN),
                    SamedayConstants::API_HOST_LOCALE_BG => __(SamedayConstants::API_HOST_LOCALE_BG, SamedayConstants::TEXT_DOMAIN),
                    'none' => '',
                ),
            ),

            'use_nomenclator' => array(
                'title' => __('Use Nomenclator', SamedayConstants::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Use the imported cities during checkout for faster processing', SamedayConstants::TEXT_DOMAIN),
                'default' => 'no',
                'options' => [
                    'no' => __('No', SamedayConstants::TEXT_DOMAIN),
                    'yes' => __('Yes', SamedayConstants::TEXT_DOMAIN),
                ]
            )
        );

        // Show on checkout:
        $this->enabled = $this->settings['enabled'] ?? 'yes';
        $this->title = $this->settings['title'] ?? __('SamedayCourier', SamedayConstants::TEXT_DOMAIN);

        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_after_settings_shipping', array($this, 'renderSettingsActions'));
    }

    public function process_admin_options(): void
    {
        $post_data = $this->get_post_data();

        $isLogged = false;
        $envModes = Helper::getEnvModes();
        foreach ($envModes as $hostCountry => $envModesByHosts) {
            if ($isLogged === true) {
                break;
            }

            foreach ($envModesByHosts as $apiUrl) {
                try {
                    $sameday = SdkInitiator::init(
                        $post_data['woocommerce_samedaycourier_user'],
                        $post_data['woocommerce_samedaycourier_password'],
                        $apiUrl
                    );
                    if ($sameday->login()) {
                        $isTesting = (int) (SamedayConstants::API_DEMO === array_keys($envModesByHosts, $apiUrl)[0]);
                        $post_data['woocommerce_samedaycourier_is_testing'] = $isTesting;
                        $post_data['woocommerce_samedaycourier_host_country'] = $hostCountry;
                        $isLogged = true;

                        // If already exist a token from previews auth, cancel it
                        OptionsHandler::setOption(
                            'woocommerce_samedaycourier_settings_' . SamedayClient::KEY_TOKEN,
                            [SamedayClient::KEY_TOKEN => null]
                        );
                        OptionsHandler::setOption(
                            'woocommerce_samedaycourier_settings_' . SamedayClient::KEY_TOKEN_EXPIRES,
                            [SamedayClient::KEY_TOKEN_EXPIRES => null]
                        );

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

    public function renderSettingsActions(): void
    {
        if (!$this->isCurrentSettingsPage()) {
            return;
        }

        $serviceUrl = admin_url() . 'edit.php?post_type=page&page=sameday_services';
        $pickupPointUrl = admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points';
        $lockerUrl = admin_url() . 'edit.php?post_type=page&page=sameday_lockers';

        echo '
            <form id="sameday-all-import-form" action="' . esc_url(admin_url('admin-post.php')) . '" method="post" hidden>
                <input type="hidden" name="action" value="all_import">
                <input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce('all_import')) . '">
            </form>
            <form id="sameday-import-cities-form" action="' . esc_url(admin_url('admin-post.php')) . '" method="post" hidden>
                <input type="hidden" name="action" value="import_cities">
                <input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce('import_cities')) . '">
            </form>
            <div class="sameday-settings-actions">
                <button type="submit" form="sameday-all-import-form" class="sameday_admin_button">'
                    . esc_html(__('Import all', SamedayConstants::TEXT_DOMAIN)) .
                '</button>
                <a href="' . esc_url($serviceUrl) . '" class="sameday_admin_button">' . esc_html(__('Services', SamedayConstants::TEXT_DOMAIN)) . '</a>
                <a href="' . esc_url($pickupPointUrl) . '" class="sameday_admin_button">' . esc_html(__('Pickup-point', SamedayConstants::TEXT_DOMAIN)) . '</a>
                <a href="' . esc_url($lockerUrl) . '" class="sameday_admin_button">' . esc_html(__('Lockers', SamedayConstants::TEXT_DOMAIN)) . '</a>
                <button type="submit" form="sameday-import-cities-form" class="sameday_admin_button">'
                    . esc_html(__('Import Cities', SamedayConstants::TEXT_DOMAIN)) .
                '</button>
            </div>
            <script>
                (function () {
                    const actions = document.querySelector(".sameday-settings-actions");
                    const submitRow = document.querySelector("#mainform p.submit");
                    if (actions && submitRow) {
                        submitRow.insertAdjacentElement("afterend", actions);
                    }
                })();
            </script>';
    }

    private function isCurrentSettingsPage(): bool
    {
        global $current_section;

        if (isset($current_section) && $this->id === $current_section) {
            return true;
        }

        return isset($_GET['page'], $_GET['tab'], $_GET['section'])
            && 'wc-settings' === $_GET['page']
            && 'shipping' === $_GET['tab']
            && $this->id === $_GET['section'];
    }
}