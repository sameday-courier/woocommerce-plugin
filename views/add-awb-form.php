<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

function samedaycourierAddAwbForm($order) {
	$total_weight = 0;
	foreach ($order->get_items() as $k => $v) {
		$_product = wc_get_product($v['product_id']);
		$qty = $v['quantity'];
		$weight = 0;
		if ($_product) {
            $weight = $_product->get_weight();
        }
		$total_weight += round($weight * $qty, 2);
	}
	$total_weight = $total_weight ?: 1;

	$pickupPointOptions = '';
	$samedayOption = get_option('woocommerce_samedaycourier_settings');
	$is_testing = $samedayOption['is_testing'] === 'yes' ? 1 : 0;
	$pickupPoints = SamedayCourierQueryDb::getPickupPoints($is_testing);
	foreach ($pickupPoints as $pickupPoint) {
		$checked = $pickupPoint->default_pickup_point === '1' ? "checked" : "";
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


	$form = '<div id="sameday-shipping-content-add-awb" style="display: none;">			        
	            <h3 style="text-align: center; color: #0A246A"> <strong> ' . __("Generate awb") . '</strong> </h3>				       
		        <table>
                    <tbody>		                    	
                        <input type="hidden" form="addAwbForm" name="samedaycourier-order-id" value="'. $order->id. '">
                         <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-repayment"> ' . __("Repayment") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-repayment" style="width: 180px; height: 30px;" id="samedaycourier-package-repayment" value="' . $order->total . '">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-insurance-value"> ' . __("Insured value") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-insurance-value" min="0" style="width: 180px; height: 30px;" id="samedaycourier-package-insurance-value" value="0">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-weight"> ' . __("Package Weight") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-weight" min="0.1" step="0.1" style="width: 180px; height: 30px;" id="samedaycourier-package-weight" value="' . $total_weight . '">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-length"> ' . __("Package Length") . '</label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-length" min="0" style="width: 180px; height: 30px;" id="samedaycourier-package-length" value="">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-height"> ' . __("Package Height") . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-height" min="0" style="width: 180px; height: 30px;" id="samedaycourier-package-height" value="">
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-width"> ' . __("Package Width") . ' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-width" min="0" style="width: 180px; height: 30px;" id="samedaycourier-package-width" value="">
                             </td>
                        </tr>		                                    
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-pickup-point"> ' . __("Pickup-point") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select form="addAwbForm" name="samedaycourier-package-pickup-point" style="width: 180px; height: 30px;" id="samedaycourier-package-pickup-point" >
                                    ' . $pickupPointOptions . '
								</select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-type"> ' . __("Package type") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select form="addAwbForm" name="samedaycourier-package-type" style="width: 180px; height: 30px;" id="samedaycourier-package-type">
                                    ' . $packageTypeOptions . '
								</select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-awb-payment"> ' . __("Awb payment") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select form="addAwbForm" name="samedaycourier-package-awb-payment" style="width: 180px; height: 30px;" id="samedaycourier-package-awb-payment">
                                    ' . $awbPaymentTypeOptions . '
								</select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-package-observation"> ' . __("Observation") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <textarea form="addAwbForm" name="samedaycourier-package-observation" style="width: 181px; height: 30px;" id="samedaycourier-package-observation" ></textarea>
                             </td>
                        </tr>			                
                        <tr>
                            <th><button class="button-primary" type="submit" value="Submit" form="addAwbForm"> ' . __("Generate Awb") . ' </button> </th>
                        </tr>
                    </tbody>
                </table>		
			</div>';

	return $form;
}
