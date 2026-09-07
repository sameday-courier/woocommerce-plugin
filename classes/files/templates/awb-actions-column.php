<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var string $awbNumber
 * @var string $orderId
 */
?>
<span style="display: block"><?php echo esc_html($awbNumber); ?></span>
<button type="button"
        class="sameday-show-awb-pdf button-link wp-menu-image dashicons-before dashicons-admin-page"
        style="display: inline-block"
        title="<?php echo esc_attr($t('Show as PDF')); ?>"
        data-order-id="<?php echo esc_attr($orderId); ?>"
        data-awb-number="<?php echo esc_attr($awbNumber); ?>"></button>
<button type="button"
        class="sameday-remove-awb button-link wp-menu-image dashicons-before dashicons-trash"
        style="display: inline-block; color: #b32d2e;"
        title="<?php echo esc_attr($t('Remove AWB')); ?>"
        data-order-id="<?php echo esc_attr($orderId); ?>"
        data-awb-number="<?php echo esc_attr($awbNumber); ?>"></button>
