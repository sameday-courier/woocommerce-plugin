<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var int|string $orderId
 * @var bool $wrapInField
 */
$button = sprintf(
    '<a href="#"
       class="sameday_admin_button button-samll"
       role="button"
       data-sameday-add-awb-order-id="%s">%s</a>',
    esc_attr((string) $orderId),
    esc_html($t('Generate awb'))
);

if ($wrapInField) {
    echo '<p class="form-field form-field-wide wc-customer-user">' . $button . '</p>';
} else {
    echo $button;
}
