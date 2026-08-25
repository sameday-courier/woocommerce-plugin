<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

final class GenerateBulkAwbModal
{
    public const MODAL_ID = 'sameday-generate-bulk-awb-modal';
    public const BUTTON_ID = 'sameday-bulk-awb-button';

    /**
     * @return string
     */
    public static function render(): string
    {
        return BulkAwbModalView::render([
            'mode' => 'generate',
            'modalId' => self::MODAL_ID,
            'buttonId' => self::BUTTON_ID,
            'buttonLabel' => TranslatorHandler::translate('Generate Bulk Awb'),
            'title' => TranslatorHandler::translate('AWB Bulk Generation'),
            'subtitle' => TranslatorHandler::translate('Review selected orders before generating shipment labels.'),
            'summaryTemplate' => TranslatorHandler::translate(
                'You are about to generate Sameday AWB for %s order(s).'
            ),
            'disclaimer' => TranslatorHandler::translate('Confirm this if you want to start bulk awb processing!'),
            'currencyConfirm' => TranslatorHandler::translate(
                'I confirm that I proceed with manual conversion for those orders.'
            ),
            'progressTitle' => TranslatorHandler::translate('Generating AWBs'),
            'startingTitle' => TranslatorHandler::translate('Preparing bulk generation'),
            'startingHint' => TranslatorHandler::translate('Please wait while we prepare your orders…'),
            'reportTitle' => TranslatorHandler::translate('AWB Generation Complete'),
            'reportSubtitle' => TranslatorHandler::translate('Shipment labels processed successfully'),
            'progressLabel' => TranslatorHandler::translate('Generating AWBs'),
            'successLabel' => TranslatorHandler::translate('Generated'),
            'successHint' => TranslatorHandler::translate('Successfully generated'),
            'failedLabel' => TranslatorHandler::translate('Failed'),
            'failedHint' => TranslatorHandler::translate('Generation failed'),
            'ordersLabel' => TranslatorHandler::translate('Orders'),
            'ordersHint' => TranslatorHandler::translate('Total orders selected'),
            'logsTitle' => TranslatorHandler::translate('Generation Logs'),
            'filterSuccess' => TranslatorHandler::translate('Generated'),
        ]);
    }
}
