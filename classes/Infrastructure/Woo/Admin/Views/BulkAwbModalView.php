<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\SamedayIcon;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

final class BulkAwbModalView
{
    /**
     * @param array $config
     *
     * @return string
     */
    public static function render(array $config): string
    {
        $cancel = TranslatorHandler::translate('Cancel');
        $confirm = TranslatorHandler::translate('Confirm');

        return HtmlHandler::buildHtml('bulk-awb-modal', [
            'buttonId' => $config['buttonId'],
            'modalId' => $config['modalId'],
            'mode' => $config['mode'],
            'buttonLabel' => $config['buttonLabel'],
            'buttonIconHtml' => SamedayIcon::render('sameday-icon', 16),
            'headerIconHtml' => SamedayIcon::render(
                'sameday-bulk-awb-modal__icon-svg sameday-bulk-awb-modal__icon-svg--package',
                26
            ),
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'cancel' => $cancel,
            'confirm' => $confirm,
            'summaryHtml' => sprintf(
                $config['summaryTemplate'],
                '<strong data-sameday-bulk-awb-order-count>0</strong>'
            ),
            'emptySelection' => TranslatorHandler::translate('Please select at least one order.'),
            'disclaimer' => $config['disclaimer'],
            'startingHint' => $config['startingHint'],
            'progressLabel' => $config['progressLabel'],
            'successLabel' => $config['successLabel'],
            'successHint' => $config['successHint'],
            'failedLabel' => $config['failedLabel'],
            'failedHint' => $config['failedHint'],
            'ordersLabel' => $config['ordersLabel'],
            'ordersHint' => $config['ordersHint'],
            'logsTitle' => $config['logsTitle'],
            'filterAll' => TranslatorHandler::translate('All'),
            'filterSuccess' => $config['filterSuccess'],
            'filterFailed' => TranslatorHandler::translate('Failed'),
            'progressTitle' => $config['progressTitle'],
            'startingTitle' => $config['startingTitle'],
            'reportTitle' => $config['reportTitle'],
            'reportSubtitle' => $config['reportSubtitle'],
            'currencyConfirm' => $config['currencyConfirm'] ?? null,
        ]);
    }
}
