<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

class NewParcelForm extends AwbForm
{
    public const MODAL_ID = 'sameday-add-new-parcel-modal';

    /**
     * @param $orderId
     *
     * @return string
     */
    public static function addNewParcelForm($orderId): string
    {
        $orderIdValue = esc_attr((string) $orderId);
        $weightLabel = TranslatorHandler::translate('Parcel weight');
        $lengthLabel = TranslatorHandler::translate('Parcel length');
        $heightLabel = TranslatorHandler::translate('Parcel height');
        $widthLabel = TranslatorHandler::translate('Parcel width');
        $isLastLabel = TranslatorHandler::translate('Is last');
        $observationLabel = TranslatorHandler::translate('Observation');
        $yesLabel = TranslatorHandler::translate('Yes');
        $noLabel = TranslatorHandler::translate('No');

        $body = '<div id="sameday-shipping-content-add-new-parcel">
            <table>
                <tbody>
                    <input type="hidden"
                           form="addNewParcelForm"
                           name="samedaycourier-order-id"
                           value="' . $orderIdValue . '">
                    <tr valign="middle">
                        <th scope="row" class="titledesc">
                            <label for="samedaycourier-parcel-weight"> '
            . $weightLabel . '<span style="color: #ff2222"> * </span>  </label>
                        </th>
                        <td class="forminp forminp-text">
                            <input type="number"
                                   form="addNewParcelForm"
                                   name="samedaycourier-parcel-weight"
                                   min="1"
                                   id="samedaycourier-parcel-weight"
                                   value="1">
                        </td>
                    </tr>
                    <tr valign="middle">
                        <th scope="row" class="titledesc">
                            <label for="samedaycourier-parcel-length"> '
            . $lengthLabel . ' </label>
                        </th>
                        <td class="forminp forminp-text">
                            <input type="number"
                                   form="addNewParcelForm"
                                   name="samedaycourier-parcel-length"
                                   min="0"
                                   id="samedaycourier-parcel-length"
                                   value="">
                        </td>
                    </tr>
                    <tr valign="middle">
                        <th scope="row" class="titledesc">
                            <label for="samedaycourier-parcel-height"> '
            . $heightLabel . ' </label>
                        </th>
                        <td class="forminp forminp-text">
                            <input type="number"
                                   form="addNewParcelForm"
                                   name="samedaycourier-parcel-height"
                                   min="0"
                                   id="samedaycourier-parcel-height"
                                   value="">
                        </td>
                    </tr>
                    <tr valign="middle">
                        <th scope="row" class="titledesc">
                            <label for="samedaycourier-parcel-width"> '
            . $widthLabel . ' </label>
                        </th>
                        <td class="forminp forminp-text">
                            <input type="number"
                                   form="addNewParcelForm"
                                   name="samedaycourier-parcel-width"
                                   min="0"
                                   id="samedaycourier-parcel-width"
                                   value="">
                        </td>
                    </tr>
                    <tr valign="middle">
                        <th scope="row" class="titledesc">
                            <label for="samedaycourier-parcel-is-last"> '
            . $isLastLabel . ' </label>
                        </th>
                        <td class="forminp forminp-text">
                            <select form="addNewParcelForm"
                                    name="samedaycourier-parcel-is-last"
                                    id="samedaycourier-parcel-is-last">
                                <option value="1"> ' . $yesLabel . ' </option>
                                <option value="0"> ' . $noLabel . ' </option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="middle">
                        <th scope="row" class="titledesc">
                            <label for="samedaycourier-parcel-observation"> '
            . $observationLabel . ' <span style="color: #ff2222"> * </span>  </label>
                        </th>
                        <td class="forminp forminp-text">
                            <textarea form="addNewParcelForm"
                                      name="samedaycourier-parcel-observation"
                                      id="samedaycourier-parcel-observation"></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>';

        return SamedayAdminModal::render([
            'id' => self::MODAL_ID,
            'title' => TranslatorHandler::translate('Add new parcel'),
            'subtitle' => TranslatorHandler::translate('Fill in the parcel details for this AWB.'),
            'body' => $body,
            'class' => 'sameday-generate-awb-modal sameday-add-new-parcel-modal',
            'confirmLabel' => TranslatorHandler::translate('Add new parcel'),
            'confirmFormId' => 'addNewParcelForm',
        ]);
    }
}
