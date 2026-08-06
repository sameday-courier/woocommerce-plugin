<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;


if (!defined( 'ABSPATH')) {
    exit;
}

use JsonException;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services\AwbFormOptionsProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Common\Services\JsonStringHandler;
use SamedayCourier\Shipping\Domain\SamedaySettings;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostMetaHandler;
use WC_Order;

class AwbForm
{
    /**
     * @var SamedayServiceRules $samedayServiceRules
     */
    private SamedayServiceRules $samedayServiceRules;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    public function __construct(
        SamedayServiceRepository $samedayServiceRepository,
        SamedayLockerRepository $samedayLockerRepository,
        SamedayPickupPointRepository $samedayPickupPointRepository
    )
    {
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->samedayPickupPointRepository = $samedayPickupPointRepository;
        $this->samedayServiceRules = new SamedayServiceRules($this->samedayServiceRepository);
    }

    /**
     * @param WC_Order $order
     * @return string
     */
    public function samedaycourierAddAwbForm(WC_Order $order): string
    {
        $postMetaLocker = PostMetaHandler::get(
            $order->get_id(),
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
        );

        $locker = null;
        $lockerDetailsForm = null;
        if (is_int($postMetaLocker)) {
            $locker = $postMetaLocker;
        } else if (is_string($postMetaLocker)) {
            $lockerDetailsForm = JsonStringHandler::fixJson(
                InputSanitizer::sanitizeInput($postMetaLocker)
            );

            try {
                $locker = json_decode($lockerDetailsForm, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {}
        }

        $serviceCode = null;
        foreach ($order->get_data()['shipping_lines'] as $shippingLine) {
            if ($shippingLine->get_method_id() !== SamedayConstants::PLUGIN_NAME) {
                continue;
            }

            if (null !== $serviceCode = $shippingLine->get_meta('service_code')) {
                if ('' !== $postMetaLocker && isset($locker['oohType']) && $locker['oohType'] === '1'
                    && $this->samedayServiceRules->isOohDeliveryOptionByCode($serviceCode)) {
                    $serviceCode = SamedayConstants::OOH_TYPES['1'] ;
                }

                break;
            }
        }

        $total_weight = 0.0;
        $weight = 0.0;
        foreach ($order->get_items() as $v) {
            $_product = wc_get_product($v['product_id']);
            $qty = $v['quantity'];

            if (isset($_product) && $_product !== false) {
                $weight = (float) $_product->get_weight();
            }

            $total_weight += (float) number_format($weight * $qty, 2);
        }
        $total_weight = $total_weight ?: 1;

        $pickupPointOptions = '';
        $pickupPoints = $this->samedayPickupPointRepository->getPickupPoints();
        foreach ($pickupPoints as $pickupPoint) {
            $checked = true === $pickupPoint->getDefaultPickupPoint() ? "selected" : "";
            $pickupPointOptions .= "<option value='{$pickupPoint->getSamedayId()}' {$checked}> {$pickupPoint->getSamedayAlias()} </option>" ;
        }

        $packageTypeOptions = '';
        $packagesType = AwbFormOptionsProvider::getPackageTypeOptions();
        foreach ($packagesType as $packageType) {
            $packageTypeOptions .= "<option value='{$packageType['value']}'>{$packageType['name']}</option>";
        }

        $awbPaymentTypeOptions = '';
        $awbPaymentsType = AwbFormOptionsProvider::getAwbPaymentTypeOptions();
        foreach ($awbPaymentsType as $awbPaymentType) {
            $awbPaymentTypeOptions .= "<option value='{$awbPaymentType['value']}'>{$awbPaymentType['name']}</option>";
        }

        $payment_gateway = wc_get_payment_gateway_by_order($order);
        $repayment = $order->get_total();

        if ($payment_gateway->id !== SamedayConstants::CASH_ON_DELIVERY) {
            $repayment = 0;
        }

        $openPackage = (new WooOpenPackageOrderDataHandler())->isEnabled($order->get_id()) ? 'checked' : '';

        $lockerName = null;
        $lockerAddress = null;

        if (is_int($locker)) {
            // Get locker from local import
            $localLockerSameday = $this->samedayLockerRepository->getLockerSameday((int) $postMetaLocker);
            if (null !== $localLockerSameday) {
                try {
                    $lockerDetailsForm = json_encode([
                        'lockerId' => $localLockerSameday->getLockerId() ?? '',
                        'name' => $localLockerSameday->getName() ?? '',
                        'address' => $localLockerSameday->getAddress() ?? '',
                        'city' => $localLockerSameday->getCity() ?? '',
                        'countyId' => $localLockerSameday->getCounty() ?? '',
                        'postalCode' => $localLockerSameday->getPostalCode() ?? '',
                    ], JSON_THROW_ON_ERROR);
                } catch (JsonException $exception) {
                    $localLockerSameday = null;
                }
            }
            if (null !== $localLockerSameday) {
                $lockerName = $localLockerSameday->getName();
                $lockerAddress = $localLockerSameday->getAddress();
            }
        }

        if (is_array($locker)) {
            $lockerName = $locker['name'];
            $lockerAddress = $locker['address'];
        }

        $lockerDetails = null;
        if (null !== $lockerName && null !== $lockerAddress) {
            $lockerDetails = sprintf('%s - %s', $lockerName, $lockerAddress);
        }

        $username = SamedaySettings::getUser();
        $hostCountry = SamedaySettings::getHostCountry();
        $destCity = $order->get_data()['shipping']['city'] ?? '';
        $destCountry = $order->get_data()['shipping']['country'] ?? '';

        $destCurrency = SamedayConstants::CURRENCY_MAPPER[$destCountry];
        $currency = $order->get_currency() ?? get_woocommerce_currency();
        $currencyWarningMessage = '';
        if ($destCurrency !== $currency
            && $repayment > 0
        ) {
            $message = sprintf(
                'Be aware that the intended currency is %s but the Repayment value is expressed in %s.
             Please consider a conversion !!',
                $destCurrency,
                $currency
            );
            $currencyWarningMessage = "
            <tr>
                <span>
                        <strong style='color: darkred'>"
                . TranslatorHandler::translate($message) . "
                        </strong>
                </span>
            </tr>
        ";
        }

        $samedayServices = $this->samedayServiceRepository->getAvailableServices();

        $allowLastMile = SamedayConstants::TOGGLE_HTML_ELEMENT['hide'];
        $allowFirstMile = SamedayConstants::TOGGLE_HTML_ELEMENT['hide'];
        $servicesOptions = '';
        foreach ($samedayServices as $samedayService) {
            $firstMileId = $this->samedayServiceRules->isEligibleToLockerFirstMile($samedayService)
                ? $samedayService->getSamedayId()
                : 0;

            $checked = ($serviceCode === $samedayService->getSamedayCode()) ? 'selected' : '';
            $allowFirstMile = SamedayConstants::TOGGLE_HTML_ELEMENT['hide'];
            if ($firstMileId === $samedayService->getSamedayId()) {
                $allowFirstMile = SamedayConstants::TOGGLE_HTML_ELEMENT['show'];
            }

            $allowLastMile = SamedayConstants::TOGGLE_HTML_ELEMENT['hide'];
            if ($this->samedayServiceRules->isOohDeliveryOption($samedayService)) {
                $allowLastMile = SamedayConstants::TOGGLE_HTML_ELEMENT['show'];
            }

            $option = sprintf(
                "<option data-fistMile='%s' data-lastMile='%s' value='%s' %s> %s </option>",
                $allowFirstMile,
                $allowLastMile,
                $samedayService->getSamedayId(),
                $checked,
                $samedayService->getSamedayName() ?? '',
            );
            $servicesOptions .= $option;
        }

        $form = '<div id="sameday-shipping-content-add-awb" style="display: none;">	        
                <h3 style="text-align: center; color: #0A246A"> <strong> ' . TranslatorHandler::translate("Generate awb") . '</strong> </h3>      
                <table>
                    <tbody>       
                         <input type="hidden" form="addAwbForm" name="samedaycourier-order-id" id="samedaycourier-order-id" value="' . $order->get_id() . '">
                         <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-repayment"> ' . sprintf("%s (%s)", TranslatorHandler::translate("Repayment"), $currency) .' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="text" onkeypress="return (event.charCode !== 8 && event.charCode === 0 || ( event.charCode === 46 || (event.charCode >= 48 && event.charCode <= 57)))" form="addAwbForm" name="samedaycourier-package-repayment" style="width: 100%; height: 30px;" id="samedaycourier-package-repayment" value="' . $repayment . '">
                                <span>' . TranslatorHandler::translate("Payment type: ") . $payment_gateway->title . '</span>
                             </td>     
                             
                        </tr>
                        '. $currencyWarningMessage . '
                        <tr valign="middle" colspan="4">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-insurance-value"> ' . TranslatorHandler::translate("Insured value") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-insurance-value" min="0" step="0.1" style="width: 100%; height: 30px;" id="samedaycourier-package-insurance-value" value="0">
                             </td>                        
                        </tr>
                        <tr>
                            <th><label>' . TranslatorHandler::translate("Parcels") . '</label></th>
                            <td class="forminp forminp-text">
                                <input readonly type="number" form="addAwbForm" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-length" value="' . TranslatorHandler::translate("1") . '">
                            </td>
                             <td class="forminp forminp-text">
                                <input readonly type="text" form="addAwbForm" min="0" step="0.1" style="height: 30px;" id="sameday-package-weight" value="Calculated Weight: ' . $total_weight . ' ' . OptionsHandler::getOption('woocommerce_weight_unit', 'kg') . '">
                             </td>
                             <td>
                                <button class="sameday_admin_button" id="addParcelButton">+</button>
                             </td>
                        </tr>
                        <tr valign="middle" class="rowPackageDimension">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-weight"> ' . TranslatorHandler::translate("Package Dimensions") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" style="width: 20%;">
                                <input class="samedaycourier-package-weight-class" type="number" form="addAwbForm" name="samedaycourier-package-weight1" min="0.1" step="0.1" style="height: 30px;" id="samedaycourier-package-weight" value="' . $total_weight . '" placeholder="' . TranslatorHandler::translate("Package Weight") . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-length1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-length" placeholder="' . TranslatorHandler::translate("Package Length") . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-height1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-height" placeholder="' . TranslatorHandler::translate("Package Height") . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-width1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-width" placeholder="' . TranslatorHandler::translate("Package Width") . '">
                             </td>
                             <td><button class="sameday_admin_button deleteParcelButton">✖</button></td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-pickup-point"> ' . TranslatorHandler::translate("Pickup-point") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-pickup-point" style="width: 100%; height: 30px;" id="samedaycourier-package-pickup-point" >
                                    ' . $pickupPointOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-type"> ' . TranslatorHandler::translate("Package type") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-type" style="width: 100%; height: 30px;" id="samedaycourier-package-type">
                                    ' . $packageTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-awb-payment"> ' . TranslatorHandler::translate("Awb payment") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-awb-payment" style="width: 100%; height: 30px;" id="samedaycourier-package-awb-payment">
                                    ' . $awbPaymentTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-service"> ' . TranslatorHandler::translate("Service") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-service" style="width: 100%; height: 30px;" id="samedaycourier-service">
                                    ' . $servicesOptions . '
                                </select>
                                <input type="hidden" form="addAwbForm" name="samedaycourier-service-optional-tax-id" id="samedaycourier-service-optional-tax-id">
                             </td>
                        </tr> ';
        $form .= '<tr id="LockerFirstMile" class="'.$allowFirstMile.'"><th scope="row" class="titledesc" > 
                                <label for="samedaycourier-locker_first_mile"> ' . TranslatorHandler::translate("Personal delivery at locker") . '</label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-locker_first_mile" id="samedaycourier-locker_first_mile">
                                <span style="display:block;width:100%">' . TranslatorHandler::translate("Check this field if you want to apply for Personal delivery of the package at an easyBox terminal.") . '</span>
                                <span style="display:block;width:100%"><a href="https://sameday.ro/easybox#lockers-intro" target="_blank">' . TranslatorHandler::translate("Show map") . '</a></span>
                                <span class="custom_tooltip"> ' . TranslatorHandler::translate("Show locker dimensions") . '    <span class="tooltiptext">        <table class="table table-hover"> <tbody style="color: #ffffff"> <tr> <th></th> <th style="text-align: center;">L</th> <th style="text-align: center;">l</th> <th style="text-align: center;">h</th> </tr><tr> <td>Small (cm)</td><td> 47</td><td> 44.5</td><td> 10</td></tr><tr> <td>Medium (cm)</td><td> 47</td><td> 44.5</td><td> 19</td></tr><tr> <td>Large (cm)</td><td> 47</td><td> 44.5</td><td> 39</td></tr> </tbody></table>    </span></span>
                                <tr></td>';

        $form .=  '<tr id="LockerLastMile" class="'.$allowLastMile.'" style="vertical-align: middle;">
                            	<th scope="row" class="titledesc"> 
                                    <label for="samedaycourier-locker-details"> ' . TranslatorHandler::translate("Location details") . ' </label>
                                </th> 
                                <td class="forminp forminp-text">';
        $form .= '<input type="hidden" form="addAwbForm" id="locker" name="locker" value="' . esc_attr((string) $lockerDetailsForm) . '">';
        $form .='  <textarea id="sameday_locker_name" disabled="disabled" style="width: 100%">' . esc_html((string) $lockerDetails) . '</textarea><br/>
                                    <button class="sameday_admin_button"
                                        data-username="' . esc_attr((string) $username) . '"
                                        data-country="' . esc_attr((string) $hostCountry) . '"
                                        data-dest_city="' . esc_attr($destCity) . '"
                                        data-dest_country="' . esc_attr($destCountry) . '"
                                        type="button" 
                                        id="select_locker"> ' . TranslatorHandler::translate("Change location") . ' 
                                    </button> 
                                </td>
                            </tr>';

        $form .= '<tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-open-package-status"> ' . TranslatorHandler::translate("Open package") . '</label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-open-package-status" id="samedaycourier-open-package-status" '.$openPackage.'>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-observation"> ' . TranslatorHandler::translate("Observation") . ' </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <textarea form="addAwbForm" name="samedaycourier-package-observation" style="width: 100%; height: 100px;" id="samedaycourier-package-observation" ></textarea>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-client-reference"> ' . TranslatorHandler::translate("Client Reference") . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="text" form="addAwbForm" name="samedaycourier-client-reference" style="width: 100%; height: 30px;" id="samedaycourier-client-reference" value="' . $order->get_id() . '">
                             	<span>' . TranslatorHandler::translate("By default this field is complete with Order ID") . '</span>
                             </td>
                        </tr>                  
                        <tr>
                            <th><button class="sameday_admin_button" type="submit" value="Submit" form="addAwbForm"> ' . TranslatorHandler::translate("Generate Awb") . ' </button> </th>
                        </tr>
                    </tbody>
                </table>
            </div>';

        return $form;
    }
}
