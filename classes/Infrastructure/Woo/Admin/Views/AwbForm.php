<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

if (!defined( 'ABSPATH')) {
    exit;
}

use JsonException;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Utils\Helper;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;

class AwbForm
{
    /**
     * @param $order
     *
     * @return string
     */
    public static function samedaycourierAddAwbForm($order): string
    {
        $postMetaLocker = get_post_meta(
            $order->get_id(),
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
            true
        );

        $locker = null;
        $lockerDetailsForm = null;
        if (is_int($postMetaLocker)) {
            $locker = $postMetaLocker;
        } else if (is_string($postMetaLocker)) {
            $lockerDetailsForm = Helper::fixJson(
                Helper::sanitizeInput($postMetaLocker)
            );

            try {
                $locker = json_decode($lockerDetailsForm, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {}
        }

        $serviceCode = null;
        foreach ($order->get_data()['shipping_lines'] as $shippingLine) {
            if ($shippingLine->get_method_id() !== 'samedaycourier') {
                continue;
            }

            if (null !== $serviceCode = $shippingLine->get_meta('service_code')) {
                if ('' !== $postMetaLocker && isset($locker['oohType']) && $locker['oohType'] === '1' && Helper::isOohDeliveryOption($serviceCode)) {
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
        $pickupPoints = (new SamedayPickupPointRepository())->getPickupPoints();
        foreach ($pickupPoints as $pickupPoint) {
            $checked = true === $pickupPoint->getDefaultPickupPoint() ? "selected" : "";
            $pickupPointOptions .= "<option value='{$pickupPoint->getSamedayId()}' {$checked}> {$pickupPoint->getSamedayAlias()} </option>" ;
        }

        $packageTypeOptions = '';
        $packagesType = Helper::getPackageTypeOptions();
        foreach ($packagesType as $packageType) {
            $packageTypeOptions .= "<option value='{$packageType['value']}'>{$packageType['name']}</option>";
        }

        $awbPaymentTypeOptions = '';
        $awbPaymentsType = Helper::getAwbPaymentTypeOptions();
        foreach ($awbPaymentsType as $awbPaymentType) {
            $awbPaymentTypeOptions .= "<option value='{$awbPaymentType['value']}'>{$awbPaymentType['name']}</option>";
        }

        $payment_gateway = wc_get_payment_gateway_by_order($order);
        $repayment = $order->get_total();

        if ($payment_gateway->id !== SamedayConstants::CASH_ON_DELIVERY) {
            $repayment = 0;
        }

        $openPackage = get_post_meta($order->get_id(), '_sameday_shipping_open_package_option', true) !== '' ? 'checked' : '';

        $lockerName = null;
        $lockerAddress = null;

        if (is_int($locker)) {
            // Get locker from local import
            $localLockerSameday = SamedayLockerRepository::getLockerSameday((int) $postMetaLocker);
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

        $username = OptionsHandler::getSamedayOptions()['user'] ?? null;
        $hostCountry = OptionsHandler::getSamedayOptions()['host_country'] ?? null;
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
                . __($message, SamedayConstants::TEXT_DOMAIN) . "
                        </strong>
                </span>
            </tr>
        ";
        }

        $samedayServices = (new SamedayServiceRepository())->getAvailableServices();
        $samedayServiceRules = new SamedayServiceRules(new SamedayServiceRepository());

        $allowLastMile = SamedayConstants::TOGGLE_HTML_ELEMENT['hide'];
        $allowFirstMile = SamedayConstants::TOGGLE_HTML_ELEMENT['hide'];
        $servicesOptions = '';
        foreach ($samedayServices as $samedayService) {
            $firstMileId = $samedayServiceRules->isEligibleToLockerFirstMile($samedayService)
                ? $samedayService->getSamedayId()
                : 0;

            $checked = ($serviceCode === $samedayService->getSamedayCode()) ? 'selected' : '';
            $allowFirstMile = SamedayConstants::TOGGLE_HTML_ELEMENT['hide'];
            if ($firstMileId === $samedayService->getSamedayId()) {
                $allowFirstMile = SamedayConstants::TOGGLE_HTML_ELEMENT['show'];
            }

            $allowLastMile = SamedayConstants::TOGGLE_HTML_ELEMENT['hide'];
            if (Helper::isOohDeliveryOption($samedayService->getSamedayCode())) {
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
                <h3 style="text-align: center; color: #0A246A"> <strong> ' . __("Generate awb", SamedayConstants::TEXT_DOMAIN) . '</strong> </h3>      
                <table>
                    <tbody>       
                         <input type="hidden" form="addAwbForm" name="samedaycourier-order-id" id="samedaycourier-order-id" value="' . $order->get_id() . '">
                         <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-repayment"> ' . sprintf("%s (%s)", __("Repayment", SamedayConstants::TEXT_DOMAIN), $currency) .' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="text" onkeypress="return (event.charCode !== 8 && event.charCode === 0 || ( event.charCode === 46 || (event.charCode >= 48 && event.charCode <= 57)))" form="addAwbForm" name="samedaycourier-package-repayment" style="width: 100%; height: 30px;" id="samedaycourier-package-repayment" value="' . $repayment . '">
                                <span>' . __("Payment type: ", SamedayConstants::TEXT_DOMAIN) . $payment_gateway->title . '</span>
                             </td>     
                             
                        </tr>
                        '. $currencyWarningMessage . '
                        <tr valign="middle" colspan="4">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-insurance-value"> ' . __("Insured value", SamedayConstants::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-insurance-value" min="0" step="0.1" style="width: 100%; height: 30px;" id="samedaycourier-package-insurance-value" value="0">
                             </td>                        
                        </tr>
                        <tr>
                            <th><label>' . __("Parcels", SamedayConstants::TEXT_DOMAIN) . '</label></th>
                            <td class="forminp forminp-text">
                                <input readonly type="number" form="addAwbForm" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-length" value="' . __("1", SamedayConstants::TEXT_DOMAIN) . '">
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
                                <label for="samedaycourier-package-weight"> ' . __("Package Dimensions", SamedayConstants::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" style="width: 20%;">
                                <input class="samedaycourier-package-weight-class" type="number" form="addAwbForm" name="samedaycourier-package-weight1" min="0.1" step="0.1" style="height: 30px;" id="samedaycourier-package-weight" value="' . $total_weight . '" placeholder="' . __("Package Weight", SamedayConstants::TEXT_DOMAIN) . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-length1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-length" placeholder="' . __("Package Length", SamedayConstants::TEXT_DOMAIN) . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-height1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-height" placeholder="' . __("Package Height", SamedayConstants::TEXT_DOMAIN) . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-width1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-width" placeholder="' . __("Package Width", SamedayConstants::TEXT_DOMAIN) . '">
                             </td>
                             <td><button class="sameday_admin_button deleteParcelButton">✖</button></td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-pickup-point"> ' . __("Pickup-point", SamedayConstants::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-pickup-point" style="width: 100%; height: 30px;" id="samedaycourier-package-pickup-point" >
                                    ' . $pickupPointOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-type"> ' . __("Package type", SamedayConstants::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-type" style="width: 100%; height: 30px;" id="samedaycourier-package-type">
                                    ' . $packageTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-awb-payment"> ' . __("Awb payment", SamedayConstants::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-awb-payment" style="width: 100%; height: 30px;" id="samedaycourier-package-awb-payment">
                                    ' . $awbPaymentTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-service"> ' . __("Service", SamedayConstants::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-service" style="width: 100%; height: 30px;" id="samedaycourier-service">
                                    ' . $servicesOptions . '
                                </select>
                                <input type="hidden" form="addAwbForm" name="samedaycourier-service-optional-tax-id" id="samedaycourier-service-optional-tax-id">
                             </td>
                        </tr> ';
        $form .= '<tr id="LockerFirstMile" class="'.$allowFirstMile.'"><th scope="row" class="titledesc" > 
                                <label for="samedaycourier-locker_first_mile"> ' . __("Personal delivery at locker", SamedayConstants::TEXT_DOMAIN) . '</label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-locker_first_mile" id="samedaycourier-locker_first_mile">
                                <span style="display:block;width:100%">' . __("Check this field if you want to apply for Personal delivery of the package at an easyBox terminal.", SamedayConstants::TEXT_DOMAIN) . '</span>
                                <span style="display:block;width:100%"><a href="https://sameday.ro/easybox#lockers-intro" target="_blank">' . __("Show map", SamedayConstants::TEXT_DOMAIN) . '</a></span>
                                <span class="custom_tooltip"> ' . __("Show locker dimensions", SamedayConstants::TEXT_DOMAIN) . '    <span class="tooltiptext">        <table class="table table-hover"> <tbody style="color: #ffffff"> <tr> <th></th> <th style="text-align: center;">L</th> <th style="text-align: center;">l</th> <th style="text-align: center;">h</th> </tr><tr> <td>Small (cm)</td><td> 47</td><td> 44.5</td><td> 10</td></tr><tr> <td>Medium (cm)</td><td> 47</td><td> 44.5</td><td> 19</td></tr><tr> <td>Large (cm)</td><td> 47</td><td> 44.5</td><td> 39</td></tr> </tbody></table>    </span></span>
                                <tr></td>';

        $form .=  '<tr id="LockerLastMile" class="'.$allowLastMile.'" style="vertical-align: middle;">
                            	<th scope="row" class="titledesc"> 
                                    <label for="samedaycourier-locker-details"> ' . __("Location details", SamedayConstants::TEXT_DOMAIN) . ' </label>
                                </th> 
                                <td class="forminp forminp-text">';
        $form .= "<input type='hidden' form='addAwbForm' id='locker' name='locker' value='$lockerDetailsForm'>";
        $form .='  <textarea id="sameday_locker_name" disabled="disabled" style="width: 100%">' . $lockerDetails .' </textarea><br/>
                                    <button class="sameday_admin_button"
                                        data-username="'.$username.'" 
                                        data-country="'.$hostCountry.'" 
                                        data-dest_city="'.$destCity.'" 
                                        data-dest_country="'.$destCountry.'" 
                                        type="button" 
                                        id="select_locker"> ' . __("Change location", SamedayConstants::TEXT_DOMAIN) . ' 
                                    </button> 
                                </td>
                            </tr>';

        $form .= '<tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-open-package-status"> ' . __("Open package", SamedayConstants::TEXT_DOMAIN) . '</label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-open-package-status" id="samedaycourier-open-package-status" '.$openPackage.'>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-observation"> ' . __("Observation", SamedayConstants::TEXT_DOMAIN) . ' </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <textarea form="addAwbForm" name="samedaycourier-package-observation" style="width: 100%; height: 100px;" id="samedaycourier-package-observation" ></textarea>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-client-reference"> ' . __("Client Reference", SamedayConstants::TEXT_DOMAIN) . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="text" form="addAwbForm" name="samedaycourier-client-reference" style="width: 100%; height: 30px;" id="samedaycourier-client-reference" value="' . $order->get_id() . '">
                             	<span>' . __("By default this field is complete with Order ID", SamedayConstants::TEXT_DOMAIN) . '</span>
                             </td>
                        </tr>                  
                        <tr>
                            <th><button class="sameday_admin_button" type="submit" value="Submit" form="addAwbForm"> ' . __("Generate Awb", SamedayConstants::TEXT_DOMAIN) . ' </button> </th>
                        </tr>
                    </tbody>
                </table>
            </div>
            <script>
                jQuery(document).ready(function() {
                    jQuery("#samedaycourier-package-pickup-point").select2();
                });
            </script>';

        return $form;
    }
}
