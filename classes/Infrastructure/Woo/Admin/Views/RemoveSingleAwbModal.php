<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

final class RemoveSingleAwbModal
{
    public const MODAL_ID = 'sameday-remove-single-awb-modal';

    /**
     * @return string
     */
    public static function render(): string
    {
        $body = sprintf(
            '<p class="sameday-bulk-awb-modal__summary">%s</p>
            <ul class="sameday-bulk-awb-modal__orders">
                <li><strong>%s:</strong> <span data-sameday-remove-awb-number></span></li>
                <li><strong>%s:</strong> #<span data-sameday-remove-order-id></span></li>
            </ul>
            <label class="sameday-bulk-awb-modal__disclaimer">
                <input type="checkbox"
                       class="sameday-modal-checkbox"
                       data-sameday-remove-awb-agree>
                <span>%s</span>
            </label>',
            esc_html(
                TranslatorHandler::translate('You are about to remove the Sameday AWB for this order.')
            ),
            esc_html(TranslatorHandler::translate('AWB')),
            esc_html(TranslatorHandler::translate('Order')),
            esc_html(TranslatorHandler::translate('Confirm this if you want to remove this AWB!'))
        );

        return SamedayAdminModal::render([
            'id' => self::MODAL_ID,
            'title' => TranslatorHandler::translate('Remove AWB'),
            'subtitle' => TranslatorHandler::translate('Review shipment label before removing.'),
            'body' => $body,
            'class' => 'sameday-remove-single-awb-modal',
            'confirmLabel' => TranslatorHandler::translate('Confirm'),
            'confirmType' => 'button',
        ]);
    }
}
