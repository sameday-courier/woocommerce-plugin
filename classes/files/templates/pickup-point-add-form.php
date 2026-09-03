<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var string $actionUrl
 * @var string $nonce
 * @var string $countryValue
 * @var string $countryLabel
 */
?>
<form id="sameday-pickup-point-form" method="POST" action="<?php echo esc_url($actionUrl); ?>">
    <input type="hidden" name="action" value="send_pickup_point">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>">
    <div class="sameday-pickup-point-form">
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointCountryDisplay"><?php echo $t('Country'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <input type="hidden" name="pickupPointCountry" value="<?php echo esc_attr($countryValue); ?>">
                <input type="text"
                       id="pickupPointCountryDisplay"
                       class="sameday-pickup-point-form__field--readonly"
                       value="<?php echo esc_attr($countryLabel); ?>"
                       readonly
                       tabindex="-1"
                       aria-readonly="true">
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointCounty"><?php echo $t('County'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <select name="pickupPointCounty" id="pickupPointCounty" required disabled></select>
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointCity"><?php echo $t('City'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <select name="pickupPointCity" id="pickupPointCity" required disabled>
                    <option value=""><?php echo $t('First select a County'); ?></option>
                </select>
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointAddress"><?php echo $t('Address'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <input type="text" name="pickupPointAddress" id="pickupPointAddress" required>
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointDefault"><?php echo $t('Default'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <input type="checkbox"
                       class="sameday-modal-checkbox"
                       name="pickupPointDefault"
                       id="pickupPointDefault"
                       value="1">
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointPostalCode"><?php echo $t('Postal Code'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <input type="number" name="pickupPointPostalCode" id="pickupPointPostalCode" required>
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointAlias"><?php echo $t('Alias'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <input type="text" name="pickupPointAlias" id="pickupPointAlias" required>
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointContactPersonName"><?php echo $t('Contact Person Name'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <input type="text"
                       name="pickupPointContactPersonName"
                       id="pickupPointContactPersonName"
                       required>
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointContactPersonPhone"><?php echo $t('Contact Person Phone'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <input type="number"
                       name="pickupPointContactPersonPhone"
                       id="pickupPointContactPersonPhone"
                       required>
            </div>
        </div>
        <div class="sameday-pickup-point-form__group">
            <label for="pickupPointEmail"><?php echo $t('Email'); ?></label>
            <div class="sameday-pickup-point-form__input">
                <input type="email" name="pickupPointEmail" id="pickupPointEmail" required>
            </div>
        </div>
    </div>
</form>
