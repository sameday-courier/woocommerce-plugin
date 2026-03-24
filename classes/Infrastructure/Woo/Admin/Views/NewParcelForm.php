<?php

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined( 'ABSPATH' )) {
    exit;
}

class NewParcelForm extends AwbForm
{
    /**
     * @param $orderId
     *
     * @return string
     */
    public static function addNewParcelForm($orderId): string
    {
        $form = '<h3 style="text-align: center; color: #0A246A"> <strong> ' . __("Add new parcel", SamedayConstants::TEXT_DOMAIN) . '</strong> </h3>';

        $form .= '<table>
	                <tbody>                    	
	                    <input type="hidden" form="addNewParcelForm" name="samedaycourier-order-id" value="'. $orderId . '">
	                     <tr valign="middle">
	                        <th scope="row" class="titledesc"> 
	                            <label for="samedaycourier-parcel-weight"> ' . __("Parcel weight", SamedayConstants::TEXT_DOMAIN) . '<span style="color: #ff2222"> * </span>  </label>
	                        </th> 
	                        <td class="forminp forminp-text">
	                            <input type="number" form="addNewParcelForm" name="samedaycourier-parcel-weight" style="width: 180px; height: 30px;" min="1" id="samedaycourier-parcel-weight" value="1">
	                         </td>
                         </tr>
                         <tr valign="middle">
	                        <th scope="row" class="titledesc"> 
	                            <label for="samedaycourier-parcel-length"> ' . __("Parcel length", SamedayConstants::TEXT_DOMAIN) . ' </label>
	                        </th> 
	                        <td class="forminp forminp-text">
	                            <input type="number" form="addNewParcelForm" name="samedaycourier-parcel-length" style="width: 180px; height: 30px;" min="0" id="samedaycourier-parcel-length" value="">
	                         </td>
                         </tr>
                         <tr valign="middle">
	                        <th scope="row" class="titledesc"> 
	                            <label for="samedaycourier-parcel-height"> ' . __("Parcel height", SamedayConstants::TEXT_DOMAIN) . ' </label>
	                        </th> 
	                        <td class="forminp forminp-text">
	                            <input type="number" form="addNewParcelForm" name="samedaycourier-parcel-height" style="width: 180px; height: 30px;" min="0" id="samedaycourier-parcel-height" value="">
	                         </td>
                         </tr>
                         <tr valign="middle">
	                        <th scope="row" class="titledesc"> 
	                            <label for="samedaycourier-parcel-width"> ' . __("Parcel width", SamedayConstants::TEXT_DOMAIN) . ' </label>
	                        </th> 
	                        <td class="forminp forminp-text">
	                            <input type="number" form="addNewParcelForm" name="samedaycourier-parcel-width" style="width: 180px; height: 30px;" min="0" id="samedaycourier-parcel-width" value="">
	                         </td>
                         </tr>
                         <tr valign="middle">
	                        <th scope="row" class="titledesc"> 
	                            <label for="samedaycourier-parcel-is-last"> ' . __("Is last", SamedayConstants::TEXT_DOMAIN) . ' </label>
	                        </th> 
	                        <td class="forminp forminp-text">	                            
	                            <select form="addNewParcelForm" name="samedaycourier-parcel-is-last" style="width: 180px; height: 30px;" id="samedaycourier-parcel-is-last" value="">
	                            	<option value="1"> ' . __("Yes", SamedayConstants::TEXT_DOMAIN) . ' </option>
	                            	<option value="0"> ' . __("No", SamedayConstants::TEXT_DOMAIN) . ' </option>
	                            </select>
	                         </td>
                         </tr>
                         <tr valign="middle">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-parcel-observation"> ' . __("Observation", SamedayConstants::TEXT_DOMAIN) . ' <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <textarea form="addNewParcelForm" name="samedaycourier-parcel-observation" style="width: 181px; height: 30px;" id="samedaycourier-parcel-observation" ></textarea>
                             </td>
                        </tr>
                        <tr>
                            <th><button class="button-primary" type="submit" value="Submit" form="addNewParcelForm"> ' . __("Add new parcel", SamedayConstants::TEXT_DOMAIN) . ' </button> </th>
                        </tr>
					</tbody>
				</table>';

        return $form;
    }
}
