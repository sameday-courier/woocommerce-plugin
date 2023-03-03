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

    $serviceId = null;
    foreach ($order->get_data()['shipping_lines'] as $shippingLine) {
        if ($shippingLine->get_method_id() !== 'samedaycourier') {
            continue;
        }

        $serviceId = (int) $shippingLine->get_meta('service_id');
        if ($serviceId) {
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

        $total_weight += round($weight * $qty, 2);
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

	$locker = null;
    $openPackage = get_post_meta($order->get_id(), '_sameday_shipping_open_package_option', true) !== '' ? 'checked' : '';

	$lockerDetailsForm = '';
	if ('' !== $postMetaLocker = get_post_meta($order->get_id(), '_sameday_shipping_locker_id', true)) {
		$lockerDetailsForm = SamedayCourierHelperClass::sanitizeInput($postMetaLocker);
        $locker = json_decode(
	        $lockerDetailsForm,
			true,
			512,
			JSON_THROW_ON_ERROR
        );
	}

	$lockerName = null;
	$lockerAddress = null;

	if (is_int($locker)) {
		// Get locker from local import
		$localLockerSameday = SamedayCourierQueryDb::getLockerSameday($postMetaLocker, $is_testing);
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
  
    $host_country = SamedayCourierHelperClass::getSamedaySettings()['host_country'];
    $username = SamedayCourierHelperClass::getSamedaySettings()['user'];

    $services = '';
    $samedayServices = SamedayCourierQueryDb::getAvailableServices($is_testing);

	$allowLastMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['hide'];
	$allowFirstMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['hide'];
    foreach ($samedayServices as $samedayService) {
        $firstMileId = isServiceEligibleToLockerFirstMile($samedayService->sameday_id);

        $checked = ($serviceId === (int) $samedayService->sameday_id) ? 'selected' : '';
        $allowFirstMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['hide'];
        if($firstMileId === $samedayService->sameday_id){
            $allowFirstMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['show'];
        }

        $allowLastMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['hide'];
        if ($samedayService->sameday_code === SamedayCourierHelperClass::LOCKER_NEXT_DAY_CODE) {
            $allowLastMile = SamedayCourierHelperClass::TOGGLE_HTML_ELEMENT['show'];
        }
        $services .= "<option data-fistMile= '{$allowFirstMile}' data-lastMile='{$allowLastMile}' value='{$samedayService->sameday_id}' {$checked}> {$samedayService->sameday_name} </option>";
    }

    $form = '<div id="sameday-shipping-content-add-awb" style="display: none;">	      
                <h3 style="text-align: center; color: #082d98"> <strong> ' . __("Generate awb", "samedaycourier") . '</strong> </h3>      
                <h3 style="text-align: center; color: #0A246A"> <strong> ' . __("Generate awb", "samedaycourier") . '</strong> </h3>      
                <table>
                    <tbody>       
                         <input type="hidden" form="addAwbForm" name="samedaycourier-order-id" value="' . $order->get_id() . '">
                         <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-repayment"> ' . __("Repayment", "samedaycourier") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="text" onkeypress="return (event.charCode !=8 && event.charCode == 0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))" form="addAwbForm" name="samedaycourier-package-repayment" style="width: 180px; height: 30px;" id="samedaycourier-package-repayment" value="' . $repayment . '">
                                <span>' . __("Payment type: ", "samedaycourier") . $payment_gateway->title . '</span>
                             </td>                             
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-insurance-value"> ' . __("Insured value", "samedaycourier") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-insurance-value" min="0" step="0.1" style="width: 180px; height: 30px;" id="samedaycourier-package-insurance-value" value="0">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-weight"> ' . __("Package Weight", "samedaycourier") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-weight" min="0.1" step="0.1" style="width: 180px; height: 30px;" id="samedaycourier-package-weight" value="' . $total_weight . '">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-length"> ' . __("Package Length", "samedaycourier") . '</label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-length" min="0" step="0.1" style="width: 180px; height: 30px;" id="samedaycourier-package-length" value="">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-height"> ' . __("Package Height", "samedaycourier") . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-height" min="0" step="0.1" style="width: 180px; height: 30px;" id="samedaycourier-package-height" value="">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-width"> ' . __("Package Width", "samedaycourier") . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-width" min="0" step="0.1" style="width: 180px; height: 30px;" id="samedaycourier-package-width" value="">
                             </td>
                        </tr>		                                    
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-pickup-point"> ' . __("Pickup-point", "samedaycourier") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select form="addAwbForm" name="samedaycourier-package-pickup-point" style="width: 180px; height: 30px;" id="samedaycourier-package-pickup-point" >
                                    ' . $pickupPointOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-type"> ' . __("Package type", "samedaycourier") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select form="addAwbForm" name="samedaycourier-package-type" style="width: 180px; height: 30px;" id="samedaycourier-package-type">
                                    ' . $packageTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-awb-payment"> ' . __("Awb payment", "samedaycourier") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select form="addAwbForm" name="samedaycourier-package-awb-payment" style="width: 180px; height: 30px;" id="samedaycourier-package-awb-payment">
                                    ' . $awbPaymentTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-service"> ' . __("Service", "samedaycourier") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select form="addAwbForm" name="samedaycourier-service" style="width: 180px; height: 30px;" id="samedaycourier-service">
                                    ' . $services . '
                                </select>
                                <input type="hidden" form="addAwbForm" name="samedaycourier-service-optional-tax-id" id="samedaycourier-service-optional-tax-id">
                             </td>
                        </tr> ';
                            $form .= '<tr id="LockerFirstMile" class="'.$allowFirstMile.'"><th scope="row" class="titledesc" > 
                                <label for="samedaycourier-locker_first_mile"> ' . __("Personal delivery at locker", "samedaycourier") . '</label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-locker_first_mile" id="samedaycourier-locker_first_mile">
                                <span style="display:block;width:100%">' . __("Check this field if you want to apply for Personal delivery of the package at an easyBox terminal.", "samedaycourier") . '</span>
                                <span style="display:block;width:100%"><a href="https://sameday.ro/easybox#lockers-intro" target="_blank">' . __("Show map", "samedaycourier") . '</a></span>
                                <span class="custom_tooltip"> ' . __("Show locker dimensions", "samedaycourier") . '    <span class="tooltiptext">        <table class="table table-hover"> <tbody style="color: #ffffff"> <tr> <th></th> <th style="text-align: center;">L</th> <th style="text-align: center;">l</th> <th style="text-align: center;">h</th> </tr><tr> <td>Small (cm)</td><td> 47</td><td> 44.5</td><td> 10</td></tr><tr> <td>Medium (cm)</td><td> 47</td><td> 44.5</td><td> 19</td></tr><tr> <td>Large (cm)</td><td> 47</td><td> 44.5</td><td> 39</td></tr> </tbody></table>    </span></span>
                                <tr></td>';
                       
                            $form .=  '<tr id="LockerLastMile" class="'.$allowLastMile.'" style="vertical-align: middle;">
                            	<th scope="row" class="titledesc"> 
                                    <label for="samedaycourier-locker-details"> ' . __("Locker details", "samedaycourier") . ' </label>
                                </th> 
                                <td class="forminp forminp-text">';
                                $form .= "<input type='hidden' form='addAwbForm' id='locker_id' name='locker_id' value='$lockerDetailsForm'>";
                                $form .='  <textarea id="sameday_locker_name" disabled="disabled" style="width: 100%">' . $lockerDetails .' </textarea><br/>
                                    <button class="button-primary" data-username="'.$username.'" data-country="'.$host_country.'" class="button alt sameday_select_locker" type="button" id="select_locker"> ' . __("Change locker", "samedaycourier") . ' </button> 
                                </td>
                            </tr>';
                        
                        $form .= '<tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-open-package-status"> ' . __("Open package", "samedaycourier") . '</label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-open-package-status" id="samedaycourier-open-package-status" '.$openPackage.'>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-observation"> ' . __("Observation", "samedaycourier") . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <textarea form="addAwbForm" name="samedaycourier-package-observation" style="width: 181px; height: 30px;" id="samedaycourier-package-observation" ></textarea>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-client-reference"> ' . __("Client Reference", "samedaycourier") . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="text" form="addAwbForm" name="samedaycourier-client-reference" style="width: 181px; height: 30px;" id="samedaycourier-client-reference" value="' . $order->get_id() . '">
                             	<span>' . __("By default this field is complete with Order ID", "samedaycourier") . '</span>
                             </td>
                        </tr>                  
                        <tr>
                            <th><button class="button-primary" type="submit" value="Submit" form="addAwbForm"> ' . __("Generate Awb", "samedaycourier") . ' </button> </th>
                        </tr>
                    </tbody>
                </table>
            </div>';

    return $form;
}