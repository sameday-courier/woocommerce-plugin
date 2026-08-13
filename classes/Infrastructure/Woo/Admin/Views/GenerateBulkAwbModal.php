<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\SamedayIcon;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

final class GenerateBulkAwbModal
{
    public const MODAL_ID = 'sameday-generate-bulk-awb-modal';
    public const BUTTON_ID = 'sameday-bulk-awb-button';

    /**
     * @return string
     */
    public static function render(): string
    {
        $title = TranslatorHandler::translate('AWB Bulk Generation');
        $subtitle = TranslatorHandler::translate('Review selected orders before generating shipment labels.');
        $summaryTemplate = TranslatorHandler::translate('You are about to generate Sameday AWB for %s order(s).');
        $disclaimer = TranslatorHandler::translate('Confirm this if you want to start bulk awb processing!');
        $cancel = TranslatorHandler::translate('Cancel');
        $confirm = TranslatorHandler::translate('Confirm');
        $buttonLabel = TranslatorHandler::translate('Generate Bulk Awb');
        $emptySelection = TranslatorHandler::translate('Please select at least one order.');
        $progressTitle = TranslatorHandler::translate('Generating AWBs');
        $startingTitle = TranslatorHandler::translate('Preparing bulk generation');
        $startingHint = TranslatorHandler::translate('Please wait while we prepare your orders…');
        $reportTitle = TranslatorHandler::translate('AWB Generation Complete');
        $reportSubtitle = TranslatorHandler::translate('Shipment labels processed successfully');
        $progressLabel = TranslatorHandler::translate('Generating AWBs');
        $generatedLabel = TranslatorHandler::translate('Generated');
        $generatedHint = TranslatorHandler::translate('Successfully generated');
        $failedLabel = TranslatorHandler::translate('Failed');
        $failedHint = TranslatorHandler::translate('Generation failed');
        $ordersLabel = TranslatorHandler::translate('Orders');
        $ordersHint = TranslatorHandler::translate('Total orders selected');
        $logsTitle = TranslatorHandler::translate('Generation Logs');
        $filterAll = TranslatorHandler::translate('All');
        $filterSuccess = TranslatorHandler::translate('Generated');
        $filterFailed = TranslatorHandler::translate('Failed');

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
            '<div id="%1$s" class="sameday-bulk-awb-modal" hidden data-sameday-bulk-awb-modal data-sameday-bulk-awb-mode="generate">
                <div class="sameday-bulk-awb-modal__backdrop" data-sameday-bulk-awb-close></div>
                <div class="sameday-bulk-awb-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="%1$s-title">
                    <div class="sameday-bulk-awb-modal__header">
                        <div class="sameday-bulk-awb-modal__heading">
                            <div class="sameday-bulk-awb-modal__icon" data-sameday-bulk-awb-header-icon aria-hidden="true">
                                <svg class="sameday-bulk-awb-modal__icon-svg sameday-bulk-awb-modal__icon-svg--package" viewBox="0 0 24 24" width="22" height="22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 3.2 3.8 7.2v9.6L12 20.8l8.2-4V7.2L12 3.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    <path d="M12 12.4 3.8 7.2M12 12.4l8.2-5.2M12 12.4V20.8" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                </svg>
                                <svg class="sameday-bulk-awb-modal__icon-svg sameday-bulk-awb-modal__icon-svg--success" viewBox="0 0 24 24" width="22" height="22" fill="none" xmlns="http://www.w3.org/2000/svg" hidden>
                                    <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="sameday-bulk-awb-modal__titles">
                                <h2 id="%1$s-title" data-sameday-bulk-awb-title>%2$s</h2>
                                <p data-sameday-bulk-awb-subtitle>%3$s</p>
                            </div>
                        </div>
                        <button type="button" class="sameday-bulk-awb-modal__close" data-sameday-bulk-awb-close aria-label="%4$s">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="sameday-bulk-awb-modal__body">
                        <div data-sameday-bulk-awb-step="confirm">
                            <p class="sameday-bulk-awb-modal__summary">%5$s</p>
                            <ul class="sameday-bulk-awb-modal__orders" data-sameday-bulk-awb-order-list></ul>
                            <p class="sameday-bulk-awb-modal__empty" data-sameday-bulk-awb-empty hidden>%6$s</p>
                            <label class="sameday-bulk-awb-modal__disclaimer">
                                <input type="checkbox" data-sameday-bulk-awb-agree>
                                <span>%7$s</span>
                            </label>
                        </div>
                        <div data-sameday-bulk-awb-step="starting" hidden>
                            <div class="sameday-bulk-awb-modal__starting">
                                <div class="sameday-bulk-awb-modal__spinner" aria-hidden="true"></div>
                                <p class="sameday-bulk-awb-modal__starting-text" data-sameday-bulk-awb-starting-text>%10$s</p>
                            </div>
                        </div>
                        <div data-sameday-bulk-awb-step="progress" hidden>
                            <div class="sameday-bulk-awb-modal__progress-block">
                                <div class="sameday-bulk-awb-modal__progress-meta">
                                    <span data-sameday-bulk-awb-progress-label>%11$s</span>
                                    <strong data-sameday-bulk-awb-progress-percent>0%%</strong>
                                </div>
                                <div class="sameday-bulk-awb-modal__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-sameday-bulk-awb-progress-bar>
                                    <div class="sameday-bulk-awb-modal__progress-fill" data-sameday-bulk-awb-progress-fill></div>
                                </div>
                                <p class="sameday-bulk-awb-modal__progress-hint" data-sameday-bulk-awb-progress-text></p>
                            </div>
                        </div>
                        <div data-sameday-bulk-awb-step="report" hidden>
                            <div class="sameday-bulk-awb-modal__progress-block">
                                <div class="sameday-bulk-awb-modal__progress-meta">
                                    <span>%11$s</span>
                                    <strong data-sameday-bulk-awb-report-percent>100%%</strong>
                                </div>
                                <div class="sameday-bulk-awb-modal__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100">
                                    <div class="sameday-bulk-awb-modal__progress-fill" style="width:100%%"></div>
                                </div>
                            </div>
                            <div class="sameday-bulk-awb-modal__stats">
                                <div class="sameday-bulk-awb-modal__stat sameday-bulk-awb-modal__stat--success">
                                    <div class="sameday-bulk-awb-modal__stat-label">
                                        <span class="sameday-bulk-awb-modal__stat-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        <span>%12$s</span>
                                    </div>
                                    <div class="sameday-bulk-awb-modal__stat-value" data-sameday-bulk-awb-stat-success>0</div>
                                    <div class="sameday-bulk-awb-modal__stat-hint">%13$s</div>
                                </div>
                                <div class="sameday-bulk-awb-modal__stat sameday-bulk-awb-modal__stat--error">
                                    <div class="sameday-bulk-awb-modal__stat-label">
                                        <span class="sameday-bulk-awb-modal__stat-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                                        </span>
                                        <span>%14$s</span>
                                    </div>
                                    <div class="sameday-bulk-awb-modal__stat-value" data-sameday-bulk-awb-stat-error>0</div>
                                    <div class="sameday-bulk-awb-modal__stat-hint">%15$s</div>
                                </div>
                                <div class="sameday-bulk-awb-modal__stat sameday-bulk-awb-modal__stat--total">
                                    <div class="sameday-bulk-awb-modal__stat-label">
                                        <span class="sameday-bulk-awb-modal__stat-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M12 3.2 3.8 7.2v9.6L12 20.8l8.2-4V7.2L12 3.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 12.4 3.8 7.2M12 12.4l8.2-5.2M12 12.4V20.8" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                                        </span>
                                        <span>%16$s</span>
                                    </div>
                                    <div class="sameday-bulk-awb-modal__stat-value" data-sameday-bulk-awb-stat-total>0</div>
                                    <div class="sameday-bulk-awb-modal__stat-hint">%17$s</div>
                                </div>
                            </div>
                            <div class="sameday-bulk-awb-modal__logs">
                                <div class="sameday-bulk-awb-modal__logs-header">
                                    <div class="sameday-bulk-awb-modal__logs-title">
                                        <span class="sameday-bulk-awb-modal__logs-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none"><path d="M8 7h8M8 12h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><rect x="4" y="3" width="16" height="18" rx="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                                        </span>
                                        <span>%18$s</span>
                                    </div>
                                    <label class="sameday-bulk-awb-modal__logs-filter">
                                        <select data-sameday-bulk-awb-log-filter>
                                            <option value="all">%19$s</option>
                                            <option value="success">%20$s</option>
                                            <option value="error">%21$s</option>
                                        </select>
                                    </label>
                                </div>
                                <ul class="sameday-bulk-awb-modal__log-list" data-sameday-bulk-awb-report-list></ul>
                            </div>
                        </div>
                    </div>
                    <div class="sameday-bulk-awb-modal__footer" data-sameday-bulk-awb-footer>
                        <button type="button" class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--cancel" data-sameday-bulk-awb-close data-sameday-bulk-awb-cancel>
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
            $confirm,
            $startingHint,
            $progressLabel,
            $generatedLabel,
            $generatedHint,
            $failedLabel,
            $failedHint,
            $ordersLabel,
            $ordersHint,
            $logsTitle,
            $filterAll,
            $filterSuccess,
            $filterFailed
        );

        $modal = str_replace(
            'data-sameday-bulk-awb-mode="generate"',
            sprintf(
                'data-sameday-bulk-awb-mode="generate" data-progress-title="%1$s" data-starting-title="%2$s" data-report-title="%3$s" data-report-subtitle="%4$s" data-confirm-title="%5$s" data-confirm-subtitle="%6$s"',
                esc_attr($progressTitle),
                esc_attr($startingTitle),
                esc_attr($reportTitle),
                esc_attr($reportSubtitle),
                esc_attr($title),
                esc_attr($subtitle)
            ),
            $modal
        );

        return $button . $modal;
    }
}
