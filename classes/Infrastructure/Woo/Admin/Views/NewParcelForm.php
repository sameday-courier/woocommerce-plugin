<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

class NewParcelForm extends AwbForm
{
    public const MODAL_ID = 'sameday-add-new-parcel-modal';

    /**
     * @param mixed $orderId
     *
     * @return string
     */
    public static function addNewParcelForm($orderId): string
    {
        $body = HtmlHandler::buildHtml('new-parcel-form', [
            'orderId' => $orderId,
        ]);

        return SamedayAdminModal::render([
            'id' => self::MODAL_ID,
            'title' => TranslatorHandler::translate('Add new parcel'),
            'subtitle' => TranslatorHandler::translate('Fill in the parcel details for this AWB.'),
            'body' => $body,
            'class' => 'sameday-generate-awb-modal sameday-add-new-parcel-modal',
            'confirmLabel' => TranslatorHandler::translate('Add new parcel'),
            'confirmFormId' => 'addNewParcelForm',
        ]);
    }
}
