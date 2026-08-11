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
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbEstimationRequest;
use Sameday\Responses\SamedayPostAwbEstimationResponse;
use Sameday\Sameday;
use Sameday\SamedayClient;
use SamedayCourier\Shipping\Domain\BgnCurrencyConverter;
use SamedayCourier\Shipping\Domain\Models\SamedayLocker;
use SamedayCourier\Shipping\Domain\SamedayAwbPdfTypes;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceSelector;
use SamedayCourier\Shipping\Domain\ValueObject\Address\County;
use SamedayCourier\Shipping\Domain\Text\RomanianDiacriticsNormalizer;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Domain\SamedaySettings;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\UrlBuilder;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Ports\WeightConverterInterface;
use SamedayCourier\Shipping\Domain\Ports\WooCommerceHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooWeightHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
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
     * @var StateCodeResolverInterface
     */
    private StateCodeResolverInterface $stateCodeResolver;

    /**
     * @var WooCommerceHandlerInterface
     */
    private WooCommerceHandlerInterface $wooCommerceHandler;

    /**
     * @var SessionHandlerInterface
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @var WeightConverterInterface
     */
    private WeightConverterInterface $weightConverter;

    /**
     * SamedayCourier_Shipping_Method constructor.
     *
     * @param int $instance_id
     */
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id = SamedayConstants::PLUGIN_NAME;
        $this->method_title = TranslatorHandler::translate('SamedayCourier');
        $this->method_description = TranslatorHandler::translate('Custom Shipping Method for SamedayCourier');

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
        $this->wooCommerceHandler = new WooHandler();
        $this->sessionHandler = new WooSessionHandler($this->wooCommerceHandler);
        $this->weightConverter = new WooWeightHandler();
        $this->stateCodeResolver = new WooStateCodeResolver(new WooCountriesHandler($this->wooCommerceHandler));

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
        if (!SamedaySettings::isEnabled()) {
            return;
        }

        $eligibleServices = $this->samedayServiceSelector->getEligibleServices(
            $package['destination']['country'] ?? SamedayConstants::API_HOST_LOCALE_RO
        );

        if (empty($eligibleServices)) {
            return;
        }

        $useEstimatedCost = SamedaySettings::getEstimatedCost();
        $estimatedCostExtraFee = SamedaySettings::getEstimatedCostExtraFee();
        $useLockerMap = SamedaySettings::isLockersMapEnabled();
        $cartValue = $this->wooCommerceHandler->getWC()->cart->get_subtotal();

        if (true === SamedaySettings::isDiscountFreeShippingEnabled()) {
            $cartValue = $this->wooCommerceHandler->getWC()->cart->get_cart_contents_total();
        }

        $stateName = $this->stateCodeResolver->resolveNameFromCode(
            $package['destination']['country'],
            $package['destination']['state']
        );

        foreach ($eligibleServices as $service) {
            if (false === $this->samedayServiceRules->isEligibleTo6H($service, $stateName)) {
                continue;
            }

            if ($this->samedayServiceRules->isOohDeliveryOption($service)) {
                $lockerMaxItems = SamedaySettings::getLockerMaxItems();

                if (count($this->wooCommerceHandler->getWC()->cart->get_cart()) > $lockerMaxItems) {
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
                            && (SamedaySettings::getHostCountry() === SamedayConstants::API_HOST_LOCALE_BG)
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
                    (new SamedayLockerRepository())->getLockers()
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

        $ltSync = SamedaySettings::getSamedaySyncLockersTs();

        if ($time > ($ltSync + 86400)) {
            (new RefreshLocker(
                new RefreshLockerRequest(
                    new SamedayLockerRepository(),
                    new Sameday(SdkInitiator::init())
                ))
            )->execute();
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
        $weight = $this->weightConverter->convert(
            $this->wooCommerceHandler->getWC()->cart->get_cart_contents_weight()
        ) ?: .1;
        $state = $this->stateCodeResolver->resolveNameFromCode(
            $address['country'],
            $address['state']
        );
        $city = RomanianDiacriticsNormalizer::normalize($address['city']);
        $currency = SamedayConstants::CURRENCY_MAPPER[$address['country']];

        $optionalServices = $this->samedayServiceRepository->getServiceIdOptionalTaxes((int) $serviceId);
        $serviceTaxIds = array();
        if ('yes' === $this->sessionHandler->get(SamedaySessionKeys::OPEN_PACKAGE)) {
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
        $repaymentAmount = $this->wooCommerceHandler->getWC()->cart->subtotal;
        $paymentMethod = $this->sessionHandler->get(SamedaySessionKeys::PAYMENT_METHOD);
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
        $labelFormatOptions = array_map(
            static function ($labelKey) {
                return TranslatorHandler::translate($labelKey);
            },
            SamedayAwbPdfTypes::getLabelKeys()
        );

        $this->form_fields = array(
            'enabled' => array(
                'title' => TranslatorHandler::translate('Enable'),
                'type' => 'checkbox',
                'description' => TranslatorHandler::translate('Enable this shipping.'),
                'default' => 'yes'
            ),

            'title' => array(
                'title' => TranslatorHandler::translate('Title'),
                'type' => 'text',
                'description' => TranslatorHandler::translate('Title to be display on site'),
                'default' => TranslatorHandler::translate('SamedayCourier Shipping')
            ),

            'user' => array(
                'title' => TranslatorHandler::translate('Username') . ' *',
                'type' => 'text',
                'description' => TranslatorHandler::translate('Username'),
                'default' => TranslatorHandler::translate('')
            ),

            'password' => array(
                'title' => TranslatorHandler::translate('Password') . ' *',
                'type' => 'password',
                'description' => TranslatorHandler::translate('Password'),
                'default' => TranslatorHandler::translate('')
            ),

            'default_label_format' => array(
                'title'   => TranslatorHandler::translate('Default label format') . ' *',
                'default' => 'A4',
                'type'    => 'select',
                'options' => $labelFormatOptions,
                'description' => TranslatorHandler::translate('Awb paper format')
            ),

            'estimated_cost' => array(
                'title'   => TranslatorHandler::translate('Use estimated cost') . ' *',
                'default' => 'no',
                'type'    => 'select',
                'options' => [
                    'no' => TranslatorHandler::translate('Never'),
                    'yes' => TranslatorHandler::translate('Always'),
                    'btfp' => TranslatorHandler::translate('If its cost is bigger than fixed price')
                ],
                'description' => TranslatorHandler::translate('This is the shipping cost calculated by Sameday Api for each service. <br/> 
                            Never* You choose to display only the fixed price that you set for each service<br/>
                            Always* You choose to display only the price estimated by SamedayCourier API<br/>
                            If its cost is bigger than fixed price* You choose to display the cost estimated by 
                            SamedayCourier Api only in the situation that this cost exceed the fixed price set by you for each service.
                        ')
            ),

            'estimated_cost_extra_fee' => array(
                'title' => TranslatorHandler::translate('Extra fee'),
                'type' => 'number',
                'css' => 'width:100px;',
                'description' => TranslatorHandler::translate('Apply extra fee on estimated cost. This is a % value. <br/> If you don\'t want to add extra fee on estimated cost value, such as T.V.A. leave this field blank or 0'),
                'custom_attributes' => array(
                    'min' => 0,
                    'onkeypress' => 'return (event.charCode !=8 && event.charCode == 0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))',
                    'data-placeholder' => TranslatorHandler::translate('Extra fee')
                ),
                'default' => 0
            ),

            'repayment_tax_label' => array(
                'title' => TranslatorHandler::translate('Repayment tax label'),
                'type' => 'text',
                'description' => TranslatorHandler::translate('Label for repayment tax. This appear in checkout page.'),
                'default' => TranslatorHandler::translate('')
            ),

            'repayment_tax' => array(
                'title' => TranslatorHandler::translate('Repayment tax'),
                'type' => 'number',
                'description' => TranslatorHandler::translate('Add extra fee on checkout.'),
                'default' => TranslatorHandler::translate('')
            ),


            'open_package_status' => array(
                'title' => TranslatorHandler::translate('Open package status'),
                'type' => 'checkbox',
                'description' => TranslatorHandler::translate('Enable this option if you want to offer your customers the opening of the package at delivery time.'),
                'default' => 'no'
            ),

            'discount_free_shipping' => array(
                'title' => TranslatorHandler::translate('Free shipping after discount'),
                'type' => 'checkbox',
                'description' => TranslatorHandler::translate('Enable this option if you want to apply free shipping to be calculated after discount.
                            Otherwise the free shipping will be apply without taking into account the applied discount.
                            This field is relevant if you choose free delivery price option.'),
                'default' => 'no'
            ),

            'open_package_label' => array(
                'title' => TranslatorHandler::translate('Open package label'),
                'type' => 'text',
                'description' => TranslatorHandler::translate('This appear in checkout page'),
                'default' => TranslatorHandler::translate('')
            ),

            'locker_max_items' => array(
                'title' => TranslatorHandler::translate('Locker max. items'),
                'type' => 'number',
                'description' => TranslatorHandler::translate('The maximum amount of items accepted inside the locker'),
                'default' => SamedayConstants::DEFAULT_VALUE_LOCKER_MAX_ITEMS
            ),

            'lockers_map' => array(
                'title'   => TranslatorHandler::translate('Show locker map method'),
                'default' => 'yes',
                'type'    => 'select',
                'options' => [
                    'no' => TranslatorHandler::translate('Drop-down list'),
                    'yes' => TranslatorHandler::translate('Interactive Map'),
                ]
            ),

            'is_testing' => array(
                'title' => TranslatorHandler::translate('Env. Mode'),
                'type' => 'select',
                'description' => TranslatorHandler::translate('The value of this field will be appear automatically after you complete the authentication'),
                'default' => 2,
                'disabled' => true,
                'options' => array(
                    SamedayConstants::API_PROD => TranslatorHandler::translate('Prod'),
                    SamedayConstants::API_DEMO => TranslatorHandler::translate('Demo'),
                    2 => '',
                ),
            ),

            'host_country' => array(
                'title' => TranslatorHandler::translate('Env. Host Country'),
                'type' => 'select',
                'description' => TranslatorHandler::translate('The value of this field will be appear automatically after you complete the authentication'),
                'default' => 'none',
                'disabled' => true,
                'options' => array(
                    SamedayConstants::API_HOST_LOCALE_RO => TranslatorHandler::translate(SamedayConstants::API_HOST_LOCALE_RO),
                    SamedayConstants::API_HOST_LOCALE_HU => TranslatorHandler::translate(SamedayConstants::API_HOST_LOCALE_HU),
                    SamedayConstants::API_HOST_LOCALE_BG => TranslatorHandler::translate(SamedayConstants::API_HOST_LOCALE_BG),
                    'none' => '',
                ),
            ),

            'use_nomenclator' => array(
                'title' => TranslatorHandler::translate('Use Nomenclator'),
                'type' => 'select',
                'description' => TranslatorHandler::translate('Use the imported cities during checkout for faster processing'),
                'default' => 'no',
                'options' => [
                    'no' => TranslatorHandler::translate('No'),
                    'yes' => TranslatorHandler::translate('Yes'),
                ]
            )
        );

        // Show on checkout:
        $this->enabled = SamedaySettings::isEnabled() ? 'yes' : 'no';
        $this->title = SamedaySettings::getTitle();

        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_after_settings_shipping', array($this, 'renderSettingsActions'));
    }

    /**
     * @return void
     */
    public function process_admin_options(): void
    {
        $post_data = $this->get_post_data();

        $isLogged = false;
        $envModes = SdkInitiator::getEnvModes();
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
            WC_Admin_Settings::add_error( TranslatorHandler::translate('Invalid username/password combination provided! Settings have not been changed!'));
        }
    }

    public function renderSettingsActions(): void
    {
        if (!$this->isCurrentSettingsPage()) {
            return;
        }

        $serviceUrl = UrlBuilder::buildEscaped('edit.php', [
            'post_type' => 'page',
            'page' => 'sameday_services',
        ]);
        $pickupPointUrl = UrlBuilder::buildEscaped('edit.php', [
            'post_type' => 'page',
            'page' => 'sameday_pickup_points',
        ]);
        $lockerUrl = UrlBuilder::buildEscaped('edit.php', [
            'post_type' => 'page',
            'page' => 'sameday_lockers',
        ]);
        $adminPostUrl = UrlBuilder::buildEscaped('admin-post.php');

        echo '
            <form id="sameday-all-import-form" action="' . $adminPostUrl . '" method="post" hidden>
                <input type="hidden" name="action" value="all_import">
                <input type="hidden" name="_wpnonce" value="' . NonceHandler::createNonce('all_import') . '">
            </form>
            <form id="sameday-import-cities-form" action="' . $adminPostUrl . '" method="post" hidden>
                <input type="hidden" name="action" value="import_cities">
                <input type="hidden" name="_wpnonce" value="' . NonceHandler::createNonce('import_cities') . '">
            </form>
            <div class="sameday-settings-actions">
                <button type="submit" form="sameday-all-import-form" class="sameday_admin_button">'
                    . TranslatorHandler::translate('Import all') .
                '</button>
                <a href="' . $serviceUrl . '" class="sameday_admin_button">' . TranslatorHandler::translate('Services') . '</a>
                <a href="' . $pickupPointUrl . '" class="sameday_admin_button">' . TranslatorHandler::translate('Pickup-point') . '</a>
                <a href="' . $lockerUrl . '" class="sameday_admin_button">' . TranslatorHandler::translate('Lockers') . '</a>
                <button type="submit" form="sameday-import-cities-form" class="sameday_admin_button">'
                    . TranslatorHandler::translate('Import Cities') .
                '</button>
            </div>';
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