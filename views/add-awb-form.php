<?php

if (! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @param $serviceId
 *
 * @return false|mixed
 */
function isServiceEligibleToLockerFirstMile($serviceId) {
    $optionalServices = SamedayCourierQueryDb::getServiceIdOptionalTaxes($serviceId, SamedayCourierHelperClass::isTesting());
     
    foreach ($optionalServices as $optionalService) {
        if ($optionalService->getCode() === SamedayCourierHelperClass::PERSONAL_DELIVERY_OPTION_CODE) {
            return $serviceId;
        }
    }

	return false;
}

/**
 * @throws JsonException
 */
function samedaycourierAddAwbForm($order): string {
    $is_testing = SamedayCourierHelperClass::isTesting();

	$postMetaLocker = get_post_meta(
		$order->get_id(),
		SamedayCourierHelperClass::POST_META_SAMEDAY_SHIPPING_LOCKER,
		true
	);

	$locker = null;
	$lockerDetailsForm = null;
	if (is_int($postMetaLocker)) {
		$locker = $postMetaLocker;
	} else if (is_string($postMetaLocker)) {
		$lockerDetailsForm = SamedayCourierHelperClass::fixJson(
			SamedayCourierHelperClass::sanitizeInput($postMetaLocker)
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
            if (SamedayCourierHelperClass::isOohDeliveryOption($serviceCode) && '' !== $postMetaLocker) {
                if (isset($locker['oohType']) && $locker['oohType'] === '1') {
	                $serviceCode = SamedayCourierHelperClass::OOH_TYPES['1'] ;
                }
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
    $pickupPoints = SamedayCourierQueryDb::getPickupPoints($is_testing);
    foreach ($pickupPoints as $pickupPoint) {
        $checked = $pickupPoint->default_pickup_point === '1' ? "selected" : "";
        $pickupPointOptions .= "<option value='{$pickupPoint->sameday_id}' {$checked}> {$pickupPoint->sameday_alias} </option>" ;
    }

    $packageTypeOptions = '';
    $packagesType = SamedayCourierHelperClass::getPackageTypeOptions();
    foreach ($packagesType as $packageType) {
        $packageTypeOptions .= "<option value='{$packageType['value']}'>{$packageType['name']}</option>";
    }

    $awbPaymentTypeOptions = '';
    $awbPaymentsType = SamedayCourierHelperClass::getAwbPaymentTypeOptions();
    foreach ($awbPaymentsType as $awbPaymentType) {
        $awbPaymentTypeOptions .= "<option value='{$awbPaymentType['value']}'>{$awbPaymentType['name']}</option>";
    }

    $payment_gateway = wc_get_payment_gateway_by_order($order);
    $repayment = $order->get_total();

    if ($payment_gateway->id !== SamedayCourierHelperClass::CASH_ON_DELIVERY) {
        $repayment = 0;
    }

    $openPackage = get_post_meta($order->get_id(), '_sameday_shipping_open_package_option', true) !== '' ? 'checked' : '';

	$lockerName = null;
	$lockerAddress = null;

	if (is_int($locker)) {
		// Get locker from local import
		$localLockerSameday = SamedayCourierQueryDb::getLockerSameday($postMetaLocker, $is_testing);
		$lockerDetailsForm = json_encode([
			'lockerId' => $localLockerSameday->locker_id,
			'name' => $localLockerSameday->name,
			'address' => $localLockerSameday->address,
			'city' => $localLockerSameday->city,
			'countyId' => $localLockerSameday->county,
			'postalCode' => $localLockerSameday->postal_code,
		]);
		if (null !== $localLockerSameday) {
			$lockerName = $localLockerSameday->name;
			$lockerAddress = $localLockerSameday->address;
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

    $username = SamedayCourierHelperClass::getSamedaySettings()['user'] ?? null;
    $hostCountry = SamedayCourierHelperClass::getSamedaySettings()['host_country'] ?? null;
    $destCity = $order->get_data()['shipping']['city'] ?? '';
    $destCountry = $order->get_data()['shipping']['country'] ?? '';

    $destCurrency = SamedayCourierHelperClass::CURRENCY_MAPPER[$destCountry];
    $currency = get_woocommerce_currency();
    $currencyWarningMessage = '';
    if ($destCurrency !== $currency
        && $repayment > 0
    ) {
        $message = sprintf(
            'Be aware that the intended currency is %s but the Repayment value is expressed in %s. Please consider a conversion !!',
            $destCurrency,
            $currency
        );
        $currencyWarningMessage = "
            <tr>
                <span>
                        <strong style='color: darkred'>"
                            . __($message, SamedayCourierHelperClass::TEXT_DOMAIN) . "
                        </strong>
                </span>
            </tr>
        ";
    }

    $samedayServices = SamedayCourierQueryDb::getAvailableServices($is_testing);

	$allowLastMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['hide'];
	$allowFirstMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['hide'];
    $servicesOptions = '';
    foreach ($samedayServices as $samedayService) {
        $firstMileId = isServiceEligibleToLockerFirstMile($samedayService->sameday_id);

        $checked = ($serviceCode === $samedayService->sameday_code) ? 'selected' : '';
        $allowFirstMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['hide'];
        if($firstMileId === $samedayService->sameday_id){
            $allowFirstMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['show'];
        }

        $allowLastMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['hide'];
        if (SamedayCourierHelperClass::isOohDeliveryOption($samedayService->sameday_code)) {
            $allowLastMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['show'];
        }

        $option = sprintf(
            "<option data-fistMile='%s' data-lastMile='%s' value='%s' %s> %s </option>",
            $allowFirstMile,
            $allowLastMile,
            $samedayService->sameday_id,
            $checked,
            $samedayService->sameday_name,
        );
        $servicesOptions .= $option;
    }

    $form = '<div id="sameday-shipping-content-add-awb" style="display: none;">	        
                <h3 style="text-align: center; color: #0A246A"> <strong> ' . __("Generate awb", SamedayCourierHelperClass::TEXT_DOMAIN) . '</strong> </h3>      
                <table>
                    <tbody>       
                         <input type="hidden" form="addAwbForm" name="samedaycourier-order-id" id="samedaycourier-order-id" value="' . $order->get_id() . '">
                         <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-repayment"> ' . sprintf("%s (%s)", __("Repayment", SamedayCourierHelperClass::TEXT_DOMAIN), $currency) .' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="text" onkeypress="return (event.charCode !=8 && event.charCode == 0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))" form="addAwbForm" name="samedaycourier-package-repayment" style="width: 100%; height: 30px;" id="samedaycourier-package-repayment" value="' . $repayment . '">
                                <span>' . __("Payment type: ", SamedayCourierHelperClass::TEXT_DOMAIN) . $payment_gateway->title . '</span>
                             </td>     
                             
                        </tr>
                        '. $currencyWarningMessage . '
                        <tr valign="middle" colspan="4">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-insurance-value"> ' . __("Insured value", SamedayCourierHelperClass::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-insurance-value" min="0" step="0.1" style="width: 100%; height: 30px;" id="samedaycourier-package-insurance-value" value="0">
                             </td>                        
                             
                        </tr>
                        <tr>
                            <th><label>' . __("Parcels", SamedayCourierHelperClass::TEXT_DOMAIN) . '</label></th>
                            <td class="forminp forminp-text">
                                <input readonly type="number" form="addAwbForm" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-length" value="' . __("1", SamedayCourierHelperClass::TEXT_DOMAIN) . '">
                            </td>
                             <td class="forminp forminp-text">
                                <input readonly type="text" form="addAwbForm" min="0" step="0.1" style="height: 30px;" id="sameday-package-weight" value="Calculated Weight: ' . $total_weight . ' ' . get_option('woocommerce_weight_unit') . '">
                             </td>
                             <td>
                                <button class="button-primary" id="addParcelButton">+</button>
                             </td>
                        </tr>
                        <tr valign="middle" class="rowPackageDimension">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-weight"> ' . __("Package Dimensions", SamedayCourierHelperClass::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" style="width: 20%;">
                                <input class="samedaycourier-package-weight-class" type="number" form="addAwbForm" name="samedaycourier-package-weight1" min="0.1" step="0.1" style="height: 30px;" id="samedaycourier-package-weight" value="' . $total_weight . '" placeholder="' . __("Package Weight", SamedayCourierHelperClass::TEXT_DOMAIN) . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-length1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-length" placeholder="' . __("Package Length", SamedayCourierHelperClass::TEXT_DOMAIN) . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-height1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-height" placeholder="' . __("Package Height", SamedayCourierHelperClass::TEXT_DOMAIN) . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-width1" min="0" step="0.1" style="height: 30px;" id="samedaycourier-package-width" placeholder="' . __("Package Width", SamedayCourierHelperClass::TEXT_DOMAIN) . '">
                             </td>
                             <td><button class="deleteParcelButton">✖</button></td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-pickup-point"> ' . __("Pickup-point", SamedayCourierHelperClass::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-pickup-point" style="width: 100%; height: 30px;" id="samedaycourier-package-pickup-point" >
                                    ' . $pickupPointOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-type"> ' . __("Package type", SamedayCourierHelperClass::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-type" style="width: 100%; height: 30px;" id="samedaycourier-package-type">
                                    ' . $packageTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-awb-payment"> ' . __("Awb payment", SamedayCourierHelperClass::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-awb-payment" style="width: 100%; height: 30px;" id="samedaycourier-package-awb-payment">
                                    ' . $awbPaymentTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-service"> ' . __("Service", SamedayCourierHelperClass::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-service" style="width: 100%; height: 30px;" id="samedaycourier-service">
                                    ' . $servicesOptions . '
                                </select>
                                <input type="hidden" form="addAwbForm" name="samedaycourier-service-optional-tax-id" id="samedaycourier-service-optional-tax-id">
                             </td>
                        </tr> ';
                            $form .= '<tr id="LockerFirstMile" class="'.$allowFirstMile.'"><th scope="row" class="titledesc" > 
                                <label for="samedaycourier-locker_first_mile"> ' . __("Personal delivery at locker", SamedayCourierHelperClass::TEXT_DOMAIN) . '</label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-locker_first_mile" id="samedaycourier-locker_first_mile">
                                <span style="display:block;width:100%">' . __("Check this field if you want to apply for Personal delivery of the package at an easyBox terminal.", SamedayCourierHelperClass::TEXT_DOMAIN) . '</span>
                                <span style="display:block;width:100%"><a href="https://sameday.ro/easybox#lockers-intro" target="_blank">' . __("Show map", SamedayCourierHelperClass::TEXT_DOMAIN) . '</a></span>
                                <span class="custom_tooltip"> ' . __("Show locker dimensions", SamedayCourierHelperClass::TEXT_DOMAIN) . '    <span class="tooltiptext">        <table class="table table-hover"> <tbody style="color: #ffffff"> <tr> <th></th> <th style="text-align: center;">L</th> <th style="text-align: center;">l</th> <th style="text-align: center;">h</th> </tr><tr> <td>Small (cm)</td><td> 47</td><td> 44.5</td><td> 10</td></tr><tr> <td>Medium (cm)</td><td> 47</td><td> 44.5</td><td> 19</td></tr><tr> <td>Large (cm)</td><td> 47</td><td> 44.5</td><td> 39</td></tr> </tbody></table>    </span></span>
                                <tr></td>';
                       
                            $form .=  '<tr id="LockerLastMile" class="'.$allowLastMile.'" style="vertical-align: middle;">
                            	<th scope="row" class="titledesc"> 
                                    <label for="samedaycourier-locker-details"> ' . __("Location details", SamedayCourierHelperClass::TEXT_DOMAIN) . ' </label>
                                </th> 
                                <td class="forminp forminp-text">';
                                $form .= "<input type='hidden' form='addAwbForm' id='locker' name='locker' value='$lockerDetailsForm'>";
                                $form .='  <textarea id="sameday_locker_name" disabled="disabled" style="width: 100%">' . $lockerDetails .' </textarea><br/>
                                    <button class="button-primary" 
                                        data-username="'.$username.'" 
                                        data-country="'.$hostCountry.'" 
                                        data-dest_city="'.$destCity.'" 
                                        data-dest_country="'.$destCountry.'" 
                                        class="button alt sameday_select_locker" 
                                        type="button" 
                                        id="select_locker"> ' . __("Change location", SamedayCourierHelperClass::TEXT_DOMAIN) . ' 
                                    </button> 
                                </td>
                            </tr>';
                        
                        $form .= '<tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-open-package-status"> ' . __("Open package", SamedayCourierHelperClass::TEXT_DOMAIN) . '</label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-open-package-status" id="samedaycourier-open-package-status" '.$openPackage.'>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-observation"> ' . __("Observation", SamedayCourierHelperClass::TEXT_DOMAIN) . ' </label>
                            </th> 
                            <td class="forminp forminp-text" colspan="4">
                                <textarea form="addAwbForm" name="samedaycourier-package-observation" style="width: 100%; height: 100px;" id="samedaycourier-package-observation" ></textarea>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-client-reference"> ' . __("Client Reference", SamedayCourierHelperClass::TEXT_DOMAIN) . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="text" form="addAwbForm" name="samedaycourier-client-reference" style="width: 100%; height: 30px;" id="samedaycourier-client-reference" value="' . $order->get_id() . '">
                             	<span>' . __("By default this field is complete with Order ID", SamedayCourierHelperClass::TEXT_DOMAIN) . '</span>
                             </td>
                        </tr>                  
                        <tr>
                            <th><button class="button-primary" type="submit" value="Submit" form="addAwbForm"> ' . __("Generate Awb", SamedayCourierHelperClass::TEXT_DOMAIN) . ' </button> </th>
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