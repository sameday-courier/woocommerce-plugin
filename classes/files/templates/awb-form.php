<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var string $modalId
 * @var string $iconHtml
 * @var int|string $orderId
 * @var float|int|string $repayment
 * @var string $currency
 * @var string $paymentGatewayTitle
 * @var string|null $currencyWarning
 * @var float|int|string $totalWeight
 * @var string $calculatedWeightLabel
 * @var array<int, array{id: int|string, alias: string, selected: bool}> $pickupPoints
 * @var array<int, array{name: string, value: int}> $packageTypes
 * @var array<int, array{name: string, value: int}> $awbPaymentTypes
 * @var array<int, array{
 *     id: int|string,
 *     name: string,
 *     selected: bool,
 *     firstMile: string,
 *     lastMile: string
 * }> $services
 * @var string $allowFirstMile
 * @var string $allowLastMile
 * @var string $lockerDetailsForm
 * @var string $lockerDetails
 * @var string $username
 * @var string $hostCountry
 * @var string $destCity
 * @var string $destCountry
 * @var bool $openPackage
 * @var string $fieldIdSuffix
 * @var string $title
 * @var string $subtitle
 * @var string $cancelLabel
 */
$fieldId = static function (string $base) use ($fieldIdSuffix): string {
    return $base . $fieldIdSuffix;
};
?>
<div id="<?php echo esc_attr($modalId); ?>"
     class="sameday-bulk-awb-modal sameday-generate-awb-modal"
     hidden
     data-sameday-generate-awb-modal>
    <div class="sameday-bulk-awb-modal__backdrop" data-sameday-generate-awb-close></div>
    <div class="sameday-bulk-awb-modal__dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="<?php echo esc_attr($modalId); ?>-title">
        <?php echo HtmlHandler::buildHtml('awb-form-modal-header', [
            'modalId' => $modalId,
            'iconHtml' => $iconHtml,
            'title' => $title,
            'subtitle' => $subtitle,
            'cancelLabel' => $cancelLabel,
        ]); ?>
        <div class="sameday-bulk-awb-modal__body">
            <div id="<?php echo esc_attr($fieldId('sameday-shipping-content-add-awb')); ?>">
                <table>
                    <tbody>
                        <input type="hidden"
                               form="addAwbForm"
                               name="samedaycourier-order-id"
                               id="<?php echo esc_attr($fieldId('samedaycourier-order-id')); ?>"
                               value="<?php echo esc_attr((string) $orderId); ?>">
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-package-repayment')); ?>">
                                    <?php echo sprintf('%s (%s)', $t('Repayment'), esc_html($currency)); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="text"
                                       onkeypress="return (event.charCode !== 8 && event.charCode === 0 || ( event.charCode === 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                                       form="addAwbForm"
                                       name="samedaycourier-package-repayment"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-package-repayment')); ?>"
                                       value="<?php echo esc_attr((string) $repayment); ?>">
                                <span><?php echo $t('Payment type: '); ?><?php echo esc_html($paymentGatewayTitle); ?></span>
                            </td>
                        </tr>
                        <?php if (null !== $currencyWarning) : ?>
                            <tr>
                                <span>
                                    <strong style="color: darkred"><?php echo $currencyWarning; ?></strong>
                                </span>
                            </tr>
                        <?php endif; ?>
                        <tr valign="middle" colspan="4">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-package-insurance-value')); ?>">
                                    <?php echo $t('Insured value'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="number"
                                       form="addAwbForm"
                                       name="samedaycourier-package-insurance-value"
                                       min="0"
                                       step="0.1"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-package-insurance-value')); ?>"
                                       value="0">
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php echo $t('Parcels'); ?></label></th>
                            <td class="forminp forminp-text">
                                <input readonly
                                       type="number"
                                       form="addAwbForm"
                                       min="0"
                                       step="0.1"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-parcel-count')); ?>"
                                       class="sameday-parcel-count-field"
                                       value="<?php echo $t('1'); ?>">
                            </td>
                            <td class="forminp forminp-text">
                                <input readonly
                                       type="text"
                                       form="addAwbForm"
                                       min="0"
                                       step="0.1"
                                       id="<?php echo esc_attr($fieldId('sameday-package-weight')); ?>"
                                       class="sameday-calculated-weight-field"
                                       value="<?php echo $calculatedWeightLabel; ?>">
                            </td>
                            <td>
                                <button type="button"
                                        class="sameday_admin_button sameday-add-parcel-button">+</button>
                            </td>
                        </tr>
                        <tr valign="middle" class="rowPackageDimension">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-package-weight')); ?>">
                                    <?php echo $t('Package Dimensions'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input class="samedaycourier-package-weight-class"
                                       type="number"
                                       form="addAwbForm"
                                       name="samedaycourier-package-dimensions[1][weight]"
                                       min="0.1"
                                       step="0.1"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-package-weight')); ?>"
                                       value="<?php echo esc_attr((string) $totalWeight); ?>"
                                       placeholder="<?php echo $t('Package Weight'); ?>">
                            </td>
                            <td class="forminp forminp-text">
                                <input type="number"
                                       form="addAwbForm"
                                       name="samedaycourier-package-dimensions[1][length]"
                                       min="0"
                                       step="0.1"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-package-length')); ?>"
                                       placeholder="<?php echo $t('Package Length'); ?>">
                            </td>
                            <td class="forminp forminp-text">
                                <input type="number"
                                       form="addAwbForm"
                                       name="samedaycourier-package-dimensions[1][height]"
                                       min="0"
                                       step="0.1"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-package-height')); ?>"
                                       placeholder="<?php echo $t('Package Height'); ?>">
                            </td>
                            <td class="forminp forminp-text">
                                <input type="number"
                                       form="addAwbForm"
                                       name="samedaycourier-package-dimensions[1][width]"
                                       min="0"
                                       step="0.1"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-package-width')); ?>"
                                       placeholder="<?php echo $t('Package Width'); ?>">
                            </td>
                            <td>
                                <button type="button" class="sameday_admin_button deleteParcelButton">✖</button>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-package-pickup-point')); ?>">
                                    <?php echo $t('Pickup-point'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm"
                                        name="samedaycourier-package-pickup-point"
                                        id="<?php echo esc_attr($fieldId('samedaycourier-package-pickup-point')); ?>">
                                    <?php foreach ($pickupPoints as $pickupPoint) : ?>
                                        <option value="<?php echo esc_attr((string) $pickupPoint['id']); ?>"
                                            <?php selected($pickupPoint['selected'], true); ?>>
                                            <?php echo esc_html((string) $pickupPoint['alias']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-package-type')); ?>">
                                    <?php echo $t('Package type'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm"
                                        name="samedaycourier-package-type"
                                        id="<?php echo esc_attr($fieldId('samedaycourier-package-type')); ?>">
                                    <?php foreach ($packageTypes as $packageType) : ?>
                                        <option value="<?php echo esc_attr((string) $packageType['value']); ?>">
                                            <?php echo (string) $packageType['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-package-awb-payment')); ?>">
                                    <?php echo $t('Awb payment'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm"
                                        name="samedaycourier-package-awb-payment"
                                        id="<?php echo esc_attr($fieldId('samedaycourier-package-awb-payment')); ?>">
                                    <?php foreach ($awbPaymentTypes as $awbPaymentType) : ?>
                                        <option value="<?php echo esc_attr((string) $awbPaymentType['value']); ?>">
                                            <?php echo (string) $awbPaymentType['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-service')); ?>">
                                    <?php echo $t('Service'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm"
                                        name="samedaycourier-service"
                                        id="<?php echo esc_attr($fieldId('samedaycourier-service')); ?>">
                                    <?php foreach ($services as $service) : ?>
                                        <option data-fistMile="<?php echo $service['firstMile']; ?>"
                                                data-lastMile="<?php echo $service['lastMile']; ?>"
                                                value="<?php echo esc_attr((string) $service['id']); ?>"
                                            <?php selected($service['selected'], true); ?>>
                                            <?php echo esc_html((string) $service['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden"
                                       form="addAwbForm"
                                       name="samedaycourier-service-optional-tax-id"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-service-optional-tax-id')); ?>">
                            </td>
                        </tr>
                        <tr id="<?php echo esc_attr($fieldId('LockerFirstMile')); ?>"
                            class="<?php echo $allowFirstMile; ?>"
                            valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-locker_first_mile')); ?>">
                                    <?php echo $t('Personal delivery at locker'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text sameday-locker-first-mile-field" colspan="4">
                                <div class="sameday-locker-first-mile-field__control">
                                    <input type="checkbox"
                                           form="addAwbForm"
                                           name="samedaycourier-locker_first_mile"
                                           id="<?php echo esc_attr($fieldId('samedaycourier-locker_first_mile')); ?>"
                                           class="sameday-modal-checkbox">
                                </div>
                                <p class="sameday-locker-first-mile-field__description">
                                    <?php echo $t('Check this field if you want to apply for Personal delivery of the package at an easyBox terminal.'); ?>
                                </p>
                                <div class="sameday-locker-first-mile-field__meta">
                                    <a href="https://sameday.ro/easybox#lockers-intro" target="_blank">
                                        <?php echo $t('Show map'); ?>
                                    </a>
                                    <span class="sameday-custom-tooltip">
                                        <?php echo $t('Show locker dimensions'); ?>
                                        <span class="sameday-tooltiptext">
                                            <table class="table table-hover">
                                                <tbody>
                                                    <tr><th></th><th>L</th><th>l</th><th>h</th></tr>
                                                    <tr><td>Small (cm)</td><td>47</td><td>44.5</td><td>10</td></tr>
                                                    <tr><td>Medium (cm)</td><td>47</td><td>44.5</td><td>19</td></tr>
                                                    <tr><td>Large (cm)</td><td>47</td><td>44.5</td><td>39</td></tr>
                                                </tbody>
                                            </table>
                                        </span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr id="<?php echo esc_attr($fieldId('LockerLastMile')); ?>"
                            class="<?php echo $allowLastMile; ?>"
                            style="vertical-align: middle;">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-locker-details')); ?>">
                                    <?php echo $t('Location details'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="hidden"
                                       form="addAwbForm"
                                       class="sameday-locker-value-field"
                                       id="<?php echo esc_attr($fieldId('locker')); ?>"
                                       name="locker"
                                       value="<?php echo esc_attr($lockerDetailsForm); ?>">
                                <label for="<?php echo esc_attr($fieldId('sameday_locker_name')); ?>"></label><textarea id="<?php echo esc_attr($fieldId('sameday_locker_name')); ?>" class="sameday-locker-name-field" disabled="disabled"><?php echo esc_textarea($lockerDetails); ?></textarea><br/>
                                <button class="sameday_admin_button sameday-select-locker-button"
                                        data-username="<?php echo esc_attr($username); ?>"
                                        data-country="<?php echo esc_attr($hostCountry); ?>"
                                        data-dest_city="<?php echo esc_attr($destCity); ?>"
                                        data-dest_country="<?php echo esc_attr($destCountry); ?>"
                                        type="button">
                                    <?php echo $t('Change location'); ?>
                                </button>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-open-package-status')); ?>">
                                    <?php echo $t('Open package'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox"
                                       form="addAwbForm"
                                       name="samedaycourier-open-package-status"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-open-package-status')); ?>"
                                       class="sameday-modal-checkbox"
                                    <?php checked($openPackage); ?>>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-package-observation')); ?>">
                                    <?php echo $t('Observation'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <textarea form="addAwbForm"
                                          name="samedaycourier-package-observation"
                                          id="<?php echo esc_attr($fieldId('samedaycourier-package-observation')); ?>"></textarea>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr($fieldId('samedaycourier-client-reference')); ?>">
                                    <?php echo $t('Client Reference'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="text"
                                       form="addAwbForm"
                                       name="samedaycourier-client-reference"
                                       id="<?php echo esc_attr($fieldId('samedaycourier-client-reference')); ?>"
                                       value="<?php echo esc_attr((string) $orderId); ?>">
                                <span><?php echo $t('By default this field is complete with Order ID'); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="sameday-bulk-awb-modal__footer">
            <button type="button"
                    class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--cancel"
                    data-sameday-generate-awb-close>
                <?php echo $t('Cancel'); ?>
            </button>
            <button type="submit"
                    form="addAwbForm"
                    value="Submit"
                    class="sameday_button sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--confirm">
                <?php echo $t('Generate Awb'); ?>
            </button>
        </div>
    </div>
</div>
