<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Shipping\Method;

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
use SamedayCourier\Shipping\Domain\Models\CarrierLocker;
use SamedayCourier\Shipping\Domain\CarrierAwbPdfTypes;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierServiceSelector;
use SamedayCourier\Shipping\Domain\Text\RomanianDiacriticsNormalizer;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\UrlsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\LockerStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Ports\WeightConverterInterface;
use SamedayCourier\Shipping\Domain\Ports\WooCommerceHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooWeightHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use WC_Admin_Settings;
use WC_Shipping_Method;

final class SamedayCourier extends WC_Shipping_Method
{
    /**
     * @var CarrierServiceSelector
     */
    private CarrierServiceSelector $carrierServiceSelector;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @var CarrierServiceRules
     */
    private CarrierServiceRules $carrierServiceRules;

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
     * @var CarrierSettingsProviderInterface
     */
    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    /**
     * SamedayCourier_Shipping_Method constructor.
     *
     * @param int $instance_id
     */
    public function __construct(int $instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id = CarrierConstants::PLUGIN_NAME;
        $this->method_title = TranslatorHandler::translate('SamedayCourier');
        $this->method_description = TranslatorHandler::translate('Custom Shipping Method for SamedayCourier');

        $this->supports = array(
            'settings',
            'shipping-zones',
            'instance-settings'
        );

        $this->carrierSettingsProvider = new CarrierSettingsServiceProvider();
        $samedayServiceRepository = new SamedayServiceRepository();
        $this->carrierServiceSelector = new CarrierServiceSelector(
            $samedayServiceRepository,
            $this->carrierSettingsProvider
        );
        $this->samedayPickupPointRepository = new SamedayPickupPointRepository();
        $this->samedayServiceRepository = new SamedayServiceRepository();
        $this->carrierServiceRules = new CarrierServiceRules($samedayServiceRepository);
        $this->wooCommerceHandler = new WooHandler();
        $this->sessionHandler = new WooSessionHandler($this->wooCommerceHandler);
        $this->weightConverter = new WooWeightHandler();
        $this->stateCodeResolver = new WooStateCodeResolver(new WooCountriesHandler($this->wooCommerceHandler));

        $this->init();
    }

    /**
     * @param mixed $package
     *
     * @return void
     * @throws SamedaySDKException
     */
    public function calculate_shipping($package = array())
    {
        $settings = $this->carrierSettingsProvider->get();
        if (!$settings->isEnabled()) {
            return;
        }

        $eligibleServices = $this->carrierServiceSelector->getEligibleServices(
            $package['destination']['country'] ?? CarrierConstants::API_HOST_LOCALE_RO
        );

        if (empty($eligibleServices)) {
            return;
        }

        $useEstimatedCost = $settings->getEstimatedCost();
        $estimatedCostExtraFee = $settings->getEstimatedCostExtraFee();
        $useLockerMap = $settings->isLockersMapEnabled();
        $cartValue = $this->wooCommerceHandler->getWC()->cart->get_subtotal();

        if (true === $settings->isDiscountFreeShippingEnabled()) {
            $cartValue = $this->wooCommerceHandler->getWC()->cart->get_cart_contents_total();
        }

        $stateName = $this->stateCodeResolver->resolveNameFromCode(
            $package['destination']['country'],
            $package['destination']['state']
        );

        foreach ($eligibleServices as $service) {
            if (false === $this->carrierServiceRules->isEligibleTo6H($service, $stateName)) {
                continue;
            }

            if ($this->carrierServiceRules->isOohDeliveryOption($service)) {
                $lockerMaxItems = $settings->getLockerMaxItems();

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
                    if (
                        ($useEstimatedCost === 'yes')
                        || ($useEstimatedCost === 'btfp' && ($service->getPrice() ?? 0) < $estimatedPrice)
                    ) {
                        if ($estimatedCostExtraFee > 0) {
                            $extraFee = $price * ($estimatedCostExtraFee / 100);
                            $estimatedPrice += (float) number_format($extraFee, 2, '.', '');
                        }
                        $price = $estimatedPrice;

                        // Business logic for Bulgaria Currency Rules
                        $storeCurrency = get_woocommerce_currency();
                        if (
                            ($storeCurrency !== $estimatedCurrency)
                            && ($settings->getHostCountry() === CarrierConstants::API_HOST_LOCALE_BG)
                        ) {
                            try {
                                $bgnCurrencyConverter = new BgnCurrencyConverter($storeCurrency, (float) $price);
                                $price = $bgnCurrencyConverter->convert();
                                $currencyConversionLabel = $bgnCurrencyConverter->buildCurrencyConversionLabel(
                                    $service->getName() ?? '',
                                    $price,
                                    $storeCurrency,
                                    number_format($estimatedPrice, 2),
                                    $estimatedCurrency
                                );
                            } catch (Exception $exception) {
                            }
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

            if (
                (false === $useLockerMap)
                && ($service->getSamedayCode() === CarrierConstants::LOCKER_NEXT_DAY_CODE)
            ) {
                $this->syncLockers();
                $rate['lockers'] = array_map(
                    /**
                     * @param CarrierLocker $locker
                     *
                     * @return array
                     */
                    static function (CarrierLocker $locker): array {
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

        $ltSync = $this->carrierSettingsProvider->get()->getSamedaySyncLockersTs();

        if ($time > ($ltSync + 86400)) {
            (new RefreshLocker(
                new RefreshLockerRequest(
                    new CourierServiceProvider(),
                    new LockerStoreServiceProvider(),
                    $this->carrierSettingsProvider
                )
            ))->execute();
        }
    }

    /**
     * @param mixed $address
     * @param mixed $serviceId
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
        $currency = CarrierConstants::CURRENCY_MAPPER[$address['country']];

        $optionalServices = $this->samedayServiceRepository->getServiceIdOptionalTaxes((int)$serviceId);
        $serviceTaxIds = array();
        if ('yes' === $this->sessionHandler->get(CarrierSessionKeys::OPEN_PACKAGE)) {
            foreach ($optionalServices as $optionalService) {
                if (
                    $optionalService->getCode() === CarrierConstants::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === PackageType::PARCEL
                ) {
                    $serviceTaxIds[] = $optionalService->getId();
                    break;
                }
            }
        }

        // Check if the client has to pay anything as repayment value
        $repaymentAmount = $this->wooCommerceHandler->getWC()->cart->subtotal;
        $paymentMethod = $this->sessionHandler->get(CarrierSessionKeys::PAYMENT_METHOD);
        if (isset($paymentMethod) && ($paymentMethod !== CarrierConstants::CASH_ON_DELIVERY)) {
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

    /**
     * @return void
     */
    private function init(): void
    {
        $labelFormatOptions = array_map(
            static function ($labelKey) {
                return TranslatorHandler::translate($labelKey);
            },
            CarrierAwbPdfTypes::getLabelKeys()
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
                'title' => TranslatorHandler::translate('Default label format') . ' *',
                'default' => 'A4',
                'type' => 'select',
                'options' => $labelFormatOptions,
                'description' => TranslatorHandler::translate('Awb paper format')
            ),

            'estimated_cost' => array(
                'title' => TranslatorHandler::translate('Use estimated cost') . ' *',
                'default' => 'no',
                'type' => 'select',
                'options' => [
                    'no' => TranslatorHandler::translate('Never'),
                    'yes' => TranslatorHandler::translate('Always'),
                    'btfp' => TranslatorHandler::translate('If its cost is bigger than fixed price')
                ],
                'description' => TranslatorHandler::translate(
                    'This is the shipping cost calculated by Sameday Api for each service. <br/>
                            Never* You choose to display only the fixed price that you set for each service<br/>
                            Always* You choose to display only the price estimated by SamedayCourier API<br/>
                            If its cost is bigger than fixed price* You choose to display the cost
                            estimated by SamedayCourier Api only if this cost exceeds the fixed price
                            set by you for each service.'
                )
            ),

            'estimated_cost_extra_fee' => array(
                'title' => TranslatorHandler::translate('Extra fee'),
                'type' => 'number',
                'css' => 'width:100px;',
                'description' => TranslatorHandler::translate(
                    'Apply extra fee on estimated cost. This is a % value. <br/>'
                    . ' If you don\'t want to add extra fee on estimated cost value,'
                    . ' such as T.V.A. leave this field blank or 0'
                ),
                'custom_attributes' => array(
                    'min' => 0,
                    'onkeypress' => 'return (event.charCode !=8 && event.charCode == 0 ||'
                        . ' ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))',
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
                'description' => TranslatorHandler::translate(
                    'Enable this option if you want to offer your customers'
                    . ' the opening of the package at delivery time.'
                ),
                'default' => 'no'
            ),

            'discount_free_shipping' => array(
                'title' => TranslatorHandler::translate('Free shipping after discount'),
                'type' => 'checkbox',
                'description' => TranslatorHandler::translate(
                    'Enable this option if you want to apply free shipping to be calculated after discount.
                            Otherwise the free shipping will be apply without taking into account the applied discount.
                            This field is relevant if you choose free delivery price option.'
                ),
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
                'default' => CarrierConstants::DEFAULT_VALUE_LOCKER_MAX_ITEMS
            ),

            'lockers_map' => array(
                'title' => TranslatorHandler::translate('Show locker map method'),
                'default' => 'yes',
                'type' => 'select',
                'options' => [
                    'no' => TranslatorHandler::translate('Drop-down list'),
                    'yes' => TranslatorHandler::translate('Interactive Map'),
                ]
            ),

            'is_testing' => array(
                'title' => TranslatorHandler::translate('Env. Mode'),
                'type' => 'select',
                'description' => TranslatorHandler::translate(
                    'The value of this field will be appear automatically after you complete the authentication'
                ),
                'default' => 2,
                'disabled' => true,
                'options' => array(
                    CarrierConstants::API_PROD => TranslatorHandler::translate('Prod'),
                    CarrierConstants::API_DEMO => TranslatorHandler::translate('Demo'),
                    2 => '',
                ),
            ),

            'host_country' => array(
                'title' => TranslatorHandler::translate('Env. Host Country'),
                'type' => 'select',
                'description' => TranslatorHandler::translate(
                    'The value of this field will be appear automatically after you complete the authentication'
                ),
                'default' => 'none',
                'disabled' => true,
                'options' => array(
                    CarrierConstants::API_HOST_LOCALE_RO => TranslatorHandler::translate(
                        CarrierConstants::API_HOST_LOCALE_RO
                    ),
                    CarrierConstants::API_HOST_LOCALE_HU => TranslatorHandler::translate(
                        CarrierConstants::API_HOST_LOCALE_HU
                    ),
                    CarrierConstants::API_HOST_LOCALE_BG => TranslatorHandler::translate(
                        CarrierConstants::API_HOST_LOCALE_BG
                    ),
                    'none' => '',
                ),
            ),

            'use_nomenclator' => array(
                'title' => TranslatorHandler::translate('Use Nomenclator'),
                'type' => 'select',
                'description' => TranslatorHandler::translate(
                    'Use the imported cities during checkout for faster processing'
                ),
                'default' => 'no',
                'options' => [
                    'no' => TranslatorHandler::translate('No'),
                    'yes' => TranslatorHandler::translate('Yes'),
                ]
            )
        );

        // Show on checkout:
        $settings = $this->carrierSettingsProvider->get();
        $this->enabled = $settings->isEnabled() ? 'yes' : 'no';
        $this->title = $settings->getTitle();

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
                        $isTesting = (int)(CarrierConstants::API_DEMO === array_keys($envModesByHosts, $apiUrl)[0]);
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
            WC_Admin_Settings::add_error(
                TranslatorHandler::translate(
                    'Invalid username/password combination provided! Settings have not been changed!'
                )
            );
        }
    }

    /**
     * @return void
     */
    public function renderSettingsActions(): void
    {
        if (!$this->isCurrentSettingsPage()) {
            return;
        }

        $serviceUrl = UrlsHandler::buildEscaped('edit.php', [
            'post_type' => 'page',
            'page' => 'sameday_services',
        ]);
        $pickupPointUrl = UrlsHandler::buildEscaped('edit.php', [
            'post_type' => 'page',
            'page' => 'sameday_pickup_points',
        ]);
        $lockerUrl = UrlsHandler::buildEscaped('edit.php', [
            'post_type' => 'page',
            'page' => 'sameday_lockers',
        ]);
        $adminPostUrl = UrlsHandler::buildEscaped('admin-post.php');

        echo HtmlHandler::buildHtml('settings-actions', [
            'adminPostUrl' => $adminPostUrl,
            'importCitiesNonce' => NonceHandler::createNonce('import_cities'),
            'serviceUrl' => $serviceUrl,
            'pickupPointUrl' => $pickupPointUrl,
            'lockerUrl' => $lockerUrl,
        ]);
    }

    /**
     * @return bool
     */
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
