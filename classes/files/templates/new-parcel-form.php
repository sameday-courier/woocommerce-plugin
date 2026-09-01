<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var int|string $orderId
 */
?>
<div id="sameday-shipping-content-add-new-parcel">
    <table>
        <tbody>
            <input type="hidden"
                   form="addNewParcelForm"
                   name="samedaycourier-order-id"
                   value="<?php echo esc_attr((string) $orderId); ?>">
            <tr valign="middle">
                <th scope="row" class="titledesc">
                    <label for="samedaycourier-parcel-weight">
                        <?php echo $t('Parcel weight'); ?><span style="color: #ff2222"> * </span>
                    </label>
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
                    <label for="samedaycourier-parcel-length">
                        <?php echo $t('Parcel length'); ?>
                    </label>
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
                    <label for="samedaycourier-parcel-height">
                        <?php echo $t('Parcel height'); ?>
                    </label>
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
                    <label for="samedaycourier-parcel-width">
                        <?php echo $t('Parcel width'); ?>
                    </label>
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
                    <label for="samedaycourier-parcel-is-last">
                        <?php echo $t('Is last'); ?>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <select form="addNewParcelForm"
                            name="samedaycourier-parcel-is-last"
                            id="samedaycourier-parcel-is-last">
                        <option value="1"><?php echo $t('Yes'); ?></option>
                        <option value="0"><?php echo $t('No'); ?></option>
                    </select>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" class="titledesc">
                    <label for="samedaycourier-parcel-observation">
                        <?php echo $t('Observation'); ?> <span style="color: #ff2222"> * </span>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <textarea form="addNewParcelForm"
                              name="samedaycourier-parcel-observation"
                              id="samedaycourier-parcel-observation"></textarea>
                </td>
            </tr>
        </tbody>
    </table>
</div>
