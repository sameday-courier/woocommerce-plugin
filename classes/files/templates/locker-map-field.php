<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var string $username
 * @var string $hostCountry
 * @var string|null $shipTo
 */
?>
<tr class="shipping-pickup-store">
    <td><strong><?php echo $t('Sameday Locker'); ?></strong></td>
    <th>
        <button type="button" class="button alt sameday_select_locker"
                id="select_locker"
                data-username="<?php echo esc_attr($username); ?>"
                data-country="<?php echo esc_attr($hostCountry); ?>"
        >
            <?php echo $t('Show Locations Map'); ?>
        </button>
    </th>
</tr>
<?php if (null !== $shipTo) : ?>
    <tr id="showSamedayLockerDetailsCheckoutLine" class="shipping-pickup-store">
        <td><strong><?php echo $t('Ship to'); ?></strong></td>
        <th><span id="showLockerDetails"><?php echo wp_kses_post($shipTo); ?></span></th>
    </tr>
<?php endif; ?>
