<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\SamedayIcon;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class RemoveBulkAwbModal
{
    public const MODAL_ID = 'sameday-remove-bulk-awb-modal';
    public const BUTTON_ID = 'sameday-remove-bulk-awb-button';

    /**
     * @return string
     */
    public static function render(): string
    {
        $title = TranslatorHandler::translate('AWB Bulk Removal');
        $subtitle = TranslatorHandler::translate('Review selected orders before removing shipment labels.');
        $summaryTemplate = TranslatorHandler::translate('You are about to remove Sameday AWB for %s order(s).');
        $disclaimer = TranslatorHandler::translate('Confirm this if you want to start bulk awb removal!');
        $cancel = TranslatorHandler::translate('Cancel');
        $confirm = TranslatorHandler::translate('Confirm');
        $buttonLabel = TranslatorHandler::translate('Remove Bulk Awb');
        $emptySelection = TranslatorHandler::translate('Please select at least one order.');

        $summaryHtml = sprintf(
            $summaryTemplate,
            '<strong data-sameday-bulk-awb-order-count>0</strong>'
        );

        $button = sprintf(
            '<a id="%1$s" href="#" class="page-title-action sameday_button" style="display:none;" role="button" data-sameday-bulk-awb-open="%2$s">%3$s%4$s</a>',
            esc_attr(self::BUTTON_ID),
            esc_attr(self::MODAL_ID),
            SamedayIcon::render('sameday-icon', 16),
            $buttonLabel
        );

        $modal = sprintf(
            '<div id="%1$s" class="sameday-bulk-awb-modal" hidden data-sameday-bulk-awb-modal>
                <div class="sameday-bulk-awb-modal__backdrop" data-sameday-bulk-awb-close></div>
                <div class="sameday-bulk-awb-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="%1$s-title">
                    <div class="sameday-bulk-awb-modal__header">
                        <div class="sameday-bulk-awb-modal__heading">
                            <div class="sameday-bulk-awb-modal__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.5 7h15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="M9.5 7V5.8c0-.7.6-1.3 1.3-1.3h2.4c.7 0 1.3.6 1.3 1.3V7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="M18.2 7l-.7 11.2c-.1 1-.9 1.8-1.9 1.8H8.4c-1 0-1.8-.8-1.9-1.8L5.8 7" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    <path d="M10 11v5.5M14 11v5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="sameday-bulk-awb-modal__titles">
                                <h2 id="%1$s-title">%2$s</h2>
                                <p>%3$s</p>
                            </div>
                        </div>
                        <button type="button" class="sameday-bulk-awb-modal__close" data-sameday-bulk-awb-close aria-label="%4$s">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="sameday-bulk-awb-modal__body">
                        <p class="sameday-bulk-awb-modal__summary">%5$s</p>
                        <ul class="sameday-bulk-awb-modal__orders" data-sameday-bulk-awb-order-list></ul>
                        <p class="sameday-bulk-awb-modal__empty" data-sameday-bulk-awb-empty hidden>%6$s</p>
                        <label class="sameday-bulk-awb-modal__disclaimer">
                            <input type="checkbox" data-sameday-bulk-awb-agree>
                            <span>%7$s</span>
                        </label>
                    </div>
                    <div class="sameday-bulk-awb-modal__footer">
                        <button type="button" class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--cancel" data-sameday-bulk-awb-close>
                            %8$s
                        </button>
                        <button type="button" class="sameday_button sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--confirm" data-sameday-bulk-awb-confirm disabled>
                            %9$s
                        </button>
                    </div>
                </div>
            </div>',
            esc_attr(self::MODAL_ID),
            $title,
            $subtitle,
            esc_attr($cancel),
            $summaryHtml,
            $emptySelection,
            $disclaimer,
            $cancel,
            $confirm
        );

        return $button . $modal;
    }
}
