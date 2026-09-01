<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

final class RemoveBulkAwbModal
{
    public const MODAL_ID = 'sameday-remove-bulk-awb-modal';
    public const BUTTON_ID = 'sameday-remove-bulk-awb-button';

    /**
     * @return string
     */
    public static function render(): string
    {
        return BulkAwbModalView::render([
            'mode' => 'remove',
            'modalId' => self::MODAL_ID,
            'buttonId' => self::BUTTON_ID,
            'buttonLabel' => TranslatorHandler::translate('Remove Bulk Awb'),
            'title' => TranslatorHandler::translate('AWB Bulk Removal'),
            'subtitle' => TranslatorHandler::translate('Review selected orders before removing shipment labels.'),
            'summaryTemplate' => TranslatorHandler::translate(
                'You are about to remove Sameday AWB for %s order(s).'
            ),
            'disclaimer' => TranslatorHandler::translate('Confirm this if you want to start bulk awb removal!'),
            'progressTitle' => TranslatorHandler::translate('Removing AWBs'),
            'startingTitle' => TranslatorHandler::translate('Preparing bulk removal'),
            'startingHint' => TranslatorHandler::translate('Please wait while we prepare your orders…'),
            'reportTitle' => TranslatorHandler::translate('AWB Removal Complete'),
            'reportSubtitle' => TranslatorHandler::translate('Shipment labels processed successfully'),
            'progressLabel' => TranslatorHandler::translate('Removing AWBs'),
            'successLabel' => TranslatorHandler::translate('Removed'),
            'successHint' => TranslatorHandler::translate('Successfully removed'),
            'failedLabel' => TranslatorHandler::translate('Failed'),
            'failedHint' => TranslatorHandler::translate('Removal failed'),
            'ordersLabel' => TranslatorHandler::translate('Orders'),
            'ordersHint' => TranslatorHandler::translate('Total orders selected'),
            'logsTitle' => TranslatorHandler::translate('Removal Logs'),
            'filterSuccess' => TranslatorHandler::translate('Removed'),
        ]);
    }
}
