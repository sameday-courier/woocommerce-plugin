<?php

namespace SamedayCourier\Shipping\Infrastructure\Shipping\Method;

use Exception;
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
use SamedayCourier\Shipping\Infrastructure\SamedayApi\ApiRequestsHandler;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Sql\QueryHandler;
use SamedayCourier\Shipping\Utils\Helper;
use WC_Admin_Settings;
use WC_Shipping_Method;

final class SamedayCourier extends WC_Shipping_Method
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
        $this->method_title = __('SamedayCourier', Helper::TEXT_DOMAIN);
        $this->method_description = __(
            'Custom Shipping Method for SamedayCourier',
            Helper::TEXT_DOMAIN
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
        $hostCountry = Helper::getHostCountry();
        $destinationCountry = $package['destination']['country'] ?? Helper::API_HOST_LOCALE_RO;

        $eligibleShippingServices = Helper::ELIGIBLE_SERVICES;
        if ($destinationCountry !== $hostCountry) {
            $eligibleShippingServices = Helper::CROSSBORDER_ELIGIBLE_SERVICES;
        }

        $availableServices = array_filter(
            QueryHandler::getAvailableServices(Helper::isTesting()),
            static function($row) use ($eligibleShippingServices) {
                return in_array(
                    $row->sameday_code,
                    $eligibleShippingServices,
                    true
                );
            }
        );

        $cartValue = WC()->cart->get_subtotal();
        if (true === Helper::isApplyFreeShippingAfterDiscount()) {
            $cartValue = WC()->cart->get_cart_contents_total();
        }

        $stateName = Helper::convertStateCodeToName(
            $package['destination']['country'],
            $package['destination']['state']
        );

        if (empty($availableServices)) {
            return;
        }

        foreach ($availableServices as $service) {
            if ($service->sameday_code === Helper::SAMEDAY_6H_CODE
                && !in_array(
                    Helper::removeAccents($stateName),
                    Helper::ELIGIBLE_TO_6H_SERVICE,
                    true
                )
            ) {
                continue;
            }

            if (Helper::isOohDeliveryOption($service->sameday_code)) {
                if (null === $lockerMaxItems = $this->settings['locker_max_items'] ?? null) {
                    $lockerMaxItems = Helper::DEFAULT_VALUE_LOCKER_MAX_ITEMS;
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
                if ($estimatedCost instanceof SamedayPostAwbEstimationResponse) {
                    $estimatedPrice = $estimatedCost->getCost();
                    $estimatedCurrency = $estimatedCost->getCurrency();
                    if (($useEstimatedCost === 'yes')
                        || ($useEstimatedCost === 'btfp' && $service->price < $estimatedPrice)
                    ) {
                        if ($estimatedCostExtraFee > 0) {
                            $estimatedPrice += (float) number_format($price * ($estimatedCostExtraFee /100), 2, '.', '');
                        }
                        $price = $estimatedPrice;

                        // Business logic for Bulgaria Currency Rules
                        $storeCurrency = get_woocommerce_currency();
                        if (($storeCurrency !== $estimatedCurrency)
                            && (Helper::getHostCountry() === Helper::API_HOST_LOCALE_BG)
                        ) {
                            try {
                                $bgnCurrencyConverter = new BgnCurrencyConverter($storeCurrency, $price);
                                $price = $bgnCurrencyConverter->convert();
                                $currencyConversionLabel = $bgnCurrencyConverter->buildCurrencyConversionLabel(
                                    $service->name,
                                    $price,
                                    $storeCurrency,
                                    $estimatedPrice,
                                    $estimatedCurrency
                                );
                            } catch (Exception $exception) {}
                        }
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

            if (isset($currencyConversionLabel)) {
                $rate['meta_data']['currency_conversion_label'] = $currencyConversionLabel;
            }

            if ((false === $useLockerMap)
                && ($service->sameday_code === Helper::LOCKER_NEXT_DAY_CODE)
            ) {
                $this->syncLockers();
                $rate['lockers'] = QueryHandler::getLockers(Helper::isTesting());
            }

            $this->add_rate($rate);
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
            (new ApiRequestsHandler())->updateLockersList();
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
        $pickupPointId = QueryHandler::getDefaultPickupPointId(Helper::isTesting());
        $weight = Helper::convertWeight(WC()->cart->get_cart_contents_weight()) ?: .1;
        $state = Helper::convertStateCodeToName($address['country'], $address['state']);
        $city = Helper::removeAccents($address['city']);
        $currency = Helper::CURRENCY_MAPPER[$address['country']];

        $optionalServices = QueryHandler::getServiceIdOptionalTaxes(
            $serviceId,
            Helper::isTesting()
        );
        $serviceTaxIds = array();
        if (WC()->session->get('open_package') === 'yes') {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === Helper::OPEN_PACKAGE_OPTION_CODE
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
        if (isset($paymentMethod) && ($paymentMethod !== Helper::CASH_ON_DELIVERY)) {
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
                'title' => __('Enable', Helper::TEXT_DOMAIN),
                'type' => 'checkbox',
                'description' => __('Enable this shipping.', Helper::TEXT_DOMAIN),
                'default' => 'yes'
            ),

            'title' => array(
                'title' => __('Title', Helper::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Title to be display on site', Helper::TEXT_DOMAIN),
                'default' => __('SamedayCourier Shipping', Helper::TEXT_DOMAIN)
            ),

            'user' => array(
                'title' => __('Username', Helper::TEXT_DOMAIN) . ' *',
                'type' => 'text',
                'description' => __('Username', Helper::TEXT_DOMAIN),
                'default' => __('')
            ),

            'password' => array(
                'title' => __('Password', Helper::TEXT_DOMAIN) . ' *',
                'type' => 'password',
                'description' => __('Password', Helper::TEXT_DOMAIN),
                'default' => __('')
            ),

            'default_label_format' => array(
                'title'   => __('Default label format', Helper::TEXT_DOMAIN) . ' *',
                'default' => 'A4',
                'type'    => 'select',
                'options' => [
                    'A4' => __(AwbPdfType::A4, Helper::TEXT_DOMAIN),
                    'A6' => __(AwbPdfType::A6, Helper::TEXT_DOMAIN),
                ],
                'description' => __('Awb paper format', Helper::TEXT_DOMAIN)
            ),

            'estimated_cost' => array(
                'title'   => __('Use estimated cost', Helper::TEXT_DOMAIN) . ' *',
                'default' => 'no',
                'type'    => 'select',
                'options' => [
                    'no' => __('Never', Helper::TEXT_DOMAIN),
                    'yes' => __('Always', Helper::TEXT_DOMAIN),
                    'btfp' => __('If its cost is bigger than fixed price', Helper::TEXT_DOMAIN)
                ],
                'description' => __('This is the shipping cost calculated by Sameday Api for each service. <br/> 
                            Never* You choose to display only the fixed price that you set for each service<br/>
                            Always* You choose to display only the price estimated by SamedayCourier API<br/>
                            If its cost is bigger than fixed price* You choose to display the cost estimated by 
                            SamedayCourier Api only in the situation that this cost exceed the fixed price set by you for each service.
                        ', Helper::TEXT_DOMAIN)
            ),

            'estimated_cost_extra_fee' => array(
                'title' => __('Extra fee', Helper::TEXT_DOMAIN),
                'type' => 'number',
                'css' => 'width:100px;',
                'description' => __('Apply extra fee on estimated cost. This is a % value. <br/> If you don\'t want to add extra fee on estimated cost value, such as T.V.A. leave this field blank or 0', Helper::TEXT_DOMAIN),
                'custom_attributes' => array(
                    'min' => 0,
                    'onkeypress' => 'return (event.charCode !=8 && event.charCode == 0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))',
                    'data-placeholder' => __('Extra fee', Helper::TEXT_DOMAIN)
                ),
                'default' => 0
            ),

            'repayment_tax_label' => array(
                'title' => __('Repayment tax label', Helper::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Label for repayment tax. This appear in checkout page.', Helper::TEXT_DOMAIN),
                'default' => __('', Helper::TEXT_DOMAIN)
            ),

            'repayment_tax' => array(
                'title' => __('Repayment tax', Helper::TEXT_DOMAIN),
                'type' => 'number',
                'description' => __('Add extra fee on checkout.', Helper::TEXT_DOMAIN),
                'default' => __('', Helper::TEXT_DOMAIN)
            ),


            'open_package_status' => array(
                'title' => __('Open package status', Helper::TEXT_DOMAIN),
                'type' => 'checkbox',
                'description' => __('Enable this option if you want to offer your customers the opening of the package at delivery time.', Helper::TEXT_DOMAIN),
                'default' => 'no'
            ),

            'discount_free_shipping' => array(
                'title' => __('Free shipping after discount', Helper::TEXT_DOMAIN),
                'type' => 'checkbox',
                'description' => __(
                    'Enable this option if you want to apply free shipping to be calculated after discount.
                            Otherwise the free shipping will be apply without taking into account the applied discount.
                            This field is relevant if you choose free delivery price option.',
                    Helper::TEXT_DOMAIN
                ),
                'default' => 'no'
            ),

            'open_package_label' => array(
                'title' => __('Open package label', Helper::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('This appear in checkout page', Helper::TEXT_DOMAIN),
                'default' => __('', Helper::TEXT_DOMAIN)
            ),

            'locker_max_items' => array(
                'title' => __('Locker max. items', Helper::TEXT_DOMAIN),
                'type' => 'number',
                'description' => __('The maximum amount of items accepted inside the locker', Helper::TEXT_DOMAIN),
                'default' => Helper::DEFAULT_VALUE_LOCKER_MAX_ITEMS
            ),

            'lockers_map' => array(
                'title'   => __('Show locker map method', Helper::TEXT_DOMAIN),
                'default' => 'yes',
                'type'    => 'select',
                'options' => [
                    'no' => __('Drop-down list', Helper::TEXT_DOMAIN),
                    'yes' => __('Interactive Map', Helper::TEXT_DOMAIN),
                ]
            ),

            'is_testing' => array(
                'title' => __('Env. Mode', Helper::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('The value of this field will be appear automatically after you complete the authentication', Helper::TEXT_DOMAIN),
                'default' => 2,
                'disabled' => true,
                'options' => array(
                    Helper::API_PROD => __('Prod', Helper::TEXT_DOMAIN),
                    Helper::API_DEMO => __('Demo', Helper::TEXT_DOMAIN),
                    2 => '',
                ),
            ),

            'host_country' => array(
                'title' => __('Env. Host Country', Helper::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('The value of this field will be appear automatically after you complete the authentication', Helper::TEXT_DOMAIN),
                'default' => 'none',
                'disabled' => true,
                'options' => array(
                    Helper::API_HOST_LOCALE_RO => __(Helper::API_HOST_LOCALE_RO, Helper::TEXT_DOMAIN),
                    Helper::API_HOST_LOCALE_HU => __(Helper::API_HOST_LOCALE_HU, Helper::TEXT_DOMAIN),
                    Helper::API_HOST_LOCALE_BG => __(Helper::API_HOST_LOCALE_BG, Helper::TEXT_DOMAIN),
                    'none' => '',
                ),
            ),

            'use_nomenclator' => array(
                'title' => __('Use Nomenclator', Helper::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Use the imported cities during checkout for faster processing', Helper::TEXT_DOMAIN),
                'default' => 'no',
                'options' => [
                    'no' => __('No', Helper::TEXT_DOMAIN),
                    'yes' => __('Yes', Helper::TEXT_DOMAIN),
                ]
            )
        );

        // Show on checkout:
        $this->enabled = $this->settings['enabled'] ?? 'yes';
        $this->title = $this->settings['title'] ?? __('SamedayCourier', Helper::TEXT_DOMAIN);

        $this->init_settings();

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
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
                $sameday = SdkInitiator::init(
                    $post_data['woocommerce_samedaycourier_user'],
                    $post_data['woocommerce_samedaycourier_password'],
                    $apiUrl
                );

                try {
                    if ($sameday->login()) {
                        $isTesting = (int) (Helper::API_DEMO === array_keys($envModesByHosts, $apiUrl)[0]);
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
                            <a href="' . $lockerUrl . '" class="button-primary"> '. __('Lockers') .' </a>
                            <button id="import_cities" class="button-primary">Import Cities</button>';

        echo parent::admin_options() . $buttons;
    }
}