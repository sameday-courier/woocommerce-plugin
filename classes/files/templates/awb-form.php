<?php

declare(strict_types=1);

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
 */
?>
<div id="<?php echo $modalId; ?>"
     class="sameday-bulk-awb-modal sameday-generate-awb-modal"
     hidden
     data-sameday-generate-awb-modal>
    <div class="sameday-bulk-awb-modal__backdrop" data-sameday-generate-awb-close></div>
    <div class="sameday-bulk-awb-modal__dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="<?php echo $modalId; ?>-title">
        <div class="sameday-bulk-awb-modal__header">
            <div class="sameday-bulk-awb-modal__heading">
                <div class="sameday-bulk-awb-modal__icon" aria-hidden="true">
                    <?php echo $iconHtml; ?>
                </div>
                <div class="sameday-bulk-awb-modal__titles">
                    <h2 id="<?php echo $modalId; ?>-title"><?php echo $t('Generate awb'); ?></h2>
                    <p><?php echo $t('Configure shipment details before generating the AWB.'); ?></p>
                </div>
            </div>
            <button type="button"
                    class="sameday-bulk-awb-modal__close"
                    data-sameday-generate-awb-close
                    aria-label="<?php echo $t('Cancel'); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="sameday-bulk-awb-modal__body">
            <div id="sameday-shipping-content-add-awb">
                <table>
                    <tbody>
                        <input type="hidden"
                               form="addAwbForm"
                               name="samedaycourier-order-id"
                               id="samedaycourier-order-id"
                               value="<?php echo esc_attr((string) $orderId); ?>">
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-repayment">
                                    <?php echo sprintf('%s (%s)', $t('Repayment'), esc_html($currency)); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="text"
                                       onkeypress="return (event.charCode !== 8 && event.charCode === 0 || ( event.charCode === 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                                       form="addAwbForm"
                                       name="samedaycourier-package-repayment"
                                       id="samedaycourier-package-repayment"
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
                                <label for="samedaycourier-package-insurance-value">
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
                                       id="samedaycourier-package-insurance-value"
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
                                       id="samedaycourier-package-length"
                                       value="<?php echo $t('1'); ?>">
                            </td>
                            <td class="forminp forminp-text">
                                <input readonly
                                       type="text"
                                       form="addAwbForm"
                                       min="0"
                                       step="0.1"
                                       id="sameday-package-weight"
                                       value="<?php echo $calculatedWeightLabel; ?>">
                            </td>
                            <td>
                                <button type="button" class="sameday_admin_button" id="addParcelButton">+</button>
                            </td>
                        </tr>
                        <tr valign="middle" class="rowPackageDimension">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-weight">
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
                                       id="samedaycourier-package-weight"
                                       value="<?php echo esc_attr((string) $totalWeight); ?>"
                                       placeholder="<?php echo $t('Package Weight'); ?>">
                            </td>
                            <td class="forminp forminp-text">
                                <input type="number"
                                       form="addAwbForm"
                                       name="samedaycourier-package-dimensions[1][length]"
                                       min="0"
                                       step="0.1"
                                       id="samedaycourier-package-length"
                                       placeholder="<?php echo $t('Package Length'); ?>">
                            </td>
                            <td class="forminp forminp-text">
                                <input type="number"
                                       form="addAwbForm"
                                       name="samedaycourier-package-dimensions[1][height]"
                                       min="0"
                                       step="0.1"
                                       id="samedaycourier-package-height"
                                       placeholder="<?php echo $t('Package Height'); ?>">
                            </td>
                            <td class="forminp forminp-text">
                                <input type="number"
                                       form="addAwbForm"
                                       name="samedaycourier-package-dimensions[1][width]"
                                       min="0"
                                       step="0.1"
                                       id="samedaycourier-package-width"
                                       placeholder="<?php echo $t('Package Width'); ?>">
                            </td>
                            <td>
                                <button type="button" class="sameday_admin_button deleteParcelButton">✖</button>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-pickup-point">
                                    <?php echo $t('Pickup-point'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm"
                                        name="samedaycourier-package-pickup-point"
                                        id="samedaycourier-package-pickup-point">
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
                                <label for="samedaycourier-package-type">
                                    <?php echo $t('Package type'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm"
                                        name="samedaycourier-package-type"
                                        id="samedaycourier-package-type">
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
                                <label for="samedaycourier-package-awb-payment">
                                    <?php echo $t('Awb payment'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm"
                                        name="samedaycourier-package-awb-payment"
                                        id="samedaycourier-package-awb-payment">
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
                                <label for="samedaycourier-service">
                                    <?php echo $t('Service'); ?>
                                    <span style="color: #ff2222"> * </span>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm"
                                        name="samedaycourier-service"
                                        id="samedaycourier-service">
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
                                       id="samedaycourier-service-optional-tax-id">
                            </td>
                        </tr>
                        <tr id="LockerFirstMile" class="<?php echo $allowFirstMile; ?>">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-locker_first_mile">
                                    <?php echo $t('Personal delivery at locker'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox"
                                       form="addAwbForm"
                                       name="samedaycourier-locker_first_mile"
                                       id="samedaycourier-locker_first_mile"
                                       class="sameday-modal-checkbox">
                                <span style="display:block;width:100%">
                                    <?php echo $t('Check this field if you want to apply for Personal delivery of the package at an easyBox terminal.'); ?>
                                </span>
                                <span style="display:block;width:100%">
                                    <a href="https://sameday.ro/easybox#lockers-intro" target="_blank">
                                        <?php echo $t('Show map'); ?>
                                    </a>
                                </span>
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
                            </td>
                        </tr>
                        <tr id="LockerLastMile"
                            class="<?php echo $allowLastMile; ?>"
                            style="vertical-align: middle;">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-locker-details">
                                    <?php echo $t('Location details'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="hidden"
                                       form="addAwbForm"
                                       id="locker"
                                       name="locker"
                                       value="<?php echo esc_attr($lockerDetailsForm); ?>">
                                <label for="sameday_locker_name"></label><textarea id="sameday_locker_name" disabled="disabled"><?php echo esc_textarea($lockerDetails); ?></textarea><br/>
                                <button class="sameday_admin_button"
                                        data-username="<?php echo esc_attr($username); ?>"
                                        data-country="<?php echo esc_attr($hostCountry); ?>"
                                        data-dest_city="<?php echo esc_attr($destCity); ?>"
                                        data-dest_country="<?php echo esc_attr($destCountry); ?>"
                                        type="button"
                                        id="select_locker">
                                    <?php echo $t('Change location'); ?>
                                </button>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-open-package-status">
                                    <?php echo $t('Open package'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox"
                                       form="addAwbForm"
                                       name="samedaycourier-open-package-status"
                                       id="samedaycourier-open-package-status"
                                       class="sameday-modal-checkbox"
                                    <?php checked($openPackage); ?>>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-observation">
                                    <?php echo $t('Observation'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <textarea form="addAwbForm"
                                          name="samedaycourier-package-observation"
                                          id="samedaycourier-package-observation"></textarea>
                            </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-client-reference">
                                    <?php echo $t('Client Reference'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="text"
                                       form="addAwbForm"
                                       name="samedaycourier-client-reference"
                                       id="samedaycourier-client-reference"
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
