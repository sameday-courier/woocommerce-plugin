<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var string $adminPostUrl
 * @var string $importCitiesNonce
 * @var string $serviceUrl
 * @var string $pickupPointUrl
 * @var string $lockerUrl
 */
?>
<form id="sameday-import-cities-form" action="<?php echo esc_url($adminPostUrl); ?>" method="post" hidden>
    <input type="hidden" name="action" value="import_cities">
    <input type="hidden" name="_wpnonce" value="<?php echo $importCitiesNonce; ?>">
</form>
<div class="sameday-settings-actions">
    <button type="button" id="sameday-all-import-button" class="sameday_admin_button">
        <?php echo $t('Import all'); ?>
    </button>
    <a href="<?php echo esc_url($serviceUrl); ?>" class="sameday_admin_button">
        <?php echo $t('Services'); ?>
    </a>
    <a href="<?php echo esc_url($pickupPointUrl); ?>" class="sameday_admin_button">
        <?php echo $t('Pickup-point'); ?>
    </a>
    <a href="<?php echo esc_url($lockerUrl); ?>" class="sameday_admin_button">
        <?php echo $t('Lockers'); ?>
    </a>
    <button type="submit" form="sameday-import-cities-form" class="sameday_admin_button">
        <?php echo $t('Import Cities'); ?>
    </button>
</div>
<div id="sameday-all-import-overlay"
     class="sameday-all-import-overlay"
     hidden
     role="alertdialog"
     aria-modal="true"
     aria-labelledby="sameday-all-import-title">
    <div class="sameday-all-import-overlay__card">
        <div class="sameday-all-import-overlay__spinner" aria-hidden="true"></div>
        <p id="sameday-all-import-title" class="sameday-all-import-overlay__title">
            <?php echo $t('Importing data'); ?>
        </p>
        <p class="sameday-all-import-overlay__status" data-sameday-all-import-status aria-live="polite"></p>
        <div class="sameday-all-import-overlay__progress" aria-hidden="true">
            <div class="sameday-all-import-overlay__progress-bar" data-sameday-all-import-progress></div>
        </div>
    </div>
</div>
