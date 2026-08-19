<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var string $actionUrl
 * @var string $nonce
 */
?>
<form id="form-deletePickupPoint" method="POST" action="<?php echo esc_url($actionUrl); ?>">
    <input type="hidden" name="sameday_id" id="input-deletePickupPoint">
    <input type="hidden" name="action" value="delete_pickup_point">
    <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>">
    <p class="sameday-bulk-awb-modal__summary">
        <?php echo $t('Are you sure you want to delete this pickup point?'); ?>
    </p>
</form>
