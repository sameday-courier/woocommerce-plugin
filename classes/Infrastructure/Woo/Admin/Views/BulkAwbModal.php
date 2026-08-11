<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class BulkAwbModal
{
    public const MODAL_ID = 'sameday-bulk-awb-modal';
    public const BUTTON_ID = 'sameday-bulk-awb-button';

    /**
     * @return string
     */
    public static function render(): string
    {
        $title = TranslatorHandler::translate('AWB Bulk Generation');
        $subtitle = TranslatorHandler::translate('Review selected orders before generating shipment labels.');
        $summaryTemplate = TranslatorHandler::translate('You are about to generate AWB for %s order(s).');
        $disclaimer = TranslatorHandler::translate('Confirm this if you want to start bulk awb processing!');
        $cancel = TranslatorHandler::translate('Cancel');
        $confirm = TranslatorHandler::translate('Confirm');
        $buttonLabel = TranslatorHandler::translate('Sameday Bulk Awb');
        $emptySelection = TranslatorHandler::translate('Please select at least one order.');

        $summaryHtml = sprintf(
            $summaryTemplate,
            '<strong id="sameday-bulk-awb-order-count">0</strong>'
        );

        $button = sprintf(
            '<a id="%1$s" href="#" class="page-title-action sameday_button" style="display:none;" role="button">%2$s</a>',
            esc_attr(self::BUTTON_ID),
            $buttonLabel
        );

        $modal = sprintf(
            '<div id="%1$s" class="sameday-bulk-awb-modal" hidden>
                <div class="sameday-bulk-awb-modal__backdrop" data-sameday-bulk-awb-close></div>
                <div class="sameday-bulk-awb-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="sameday-bulk-awb-title">
                    <div class="sameday-bulk-awb-modal__header">
                        <div class="sameday-bulk-awb-modal__heading">
                            <div class="sameday-bulk-awb-modal__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 3.2 3.8 7.2v9.6L12 20.8l8.2-4V7.2L12 3.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    <path d="M12 12.4 3.8 7.2M12 12.4l8.2-5.2M12 12.4V20.8" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="sameday-bulk-awb-modal__titles">
                                <h2 id="sameday-bulk-awb-title">%2$s</h2>
                                <p>%3$s</p>
                            </div>
                        </div>
                        <button type="button" class="sameday-bulk-awb-modal__close" data-sameday-bulk-awb-close aria-label="%4$s">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="sameday-bulk-awb-modal__body">
                        <p class="sameday-bulk-awb-modal__summary">%5$s</p>
                        <ul id="sameday-bulk-awb-order-list" class="sameday-bulk-awb-modal__orders"></ul>
                        <p id="sameday-bulk-awb-empty" class="sameday-bulk-awb-modal__empty" hidden>%6$s</p>
                        <label class="sameday-bulk-awb-modal__disclaimer" for="sameday-bulk-awb-agree">
                            <input type="checkbox" id="sameday-bulk-awb-agree">
                            <span>%7$s</span>
                        </label>
                    </div>
                    <div class="sameday-bulk-awb-modal__footer">
                        <button type="button" class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--cancel" data-sameday-bulk-awb-close>
                            %8$s
                        </button>
                        <button type="button" id="sameday-bulk-awb-confirm" class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--confirm" disabled>
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
