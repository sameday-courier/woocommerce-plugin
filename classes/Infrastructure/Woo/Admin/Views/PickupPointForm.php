<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;

class PickupPointForm
{
    public const ADD_MODAL_ID = 'sameday-pickup-point-modal';
    public const DELETE_MODAL_ID = 'sameday-delete-pickup-point-modal';

    /**
     * @return void
     */
    public static function renderModals(): void
    {
        $hostCountry = (new CarrierSettingsServiceProvider())->get()->getHostCountry();
        $countryValue = CarrierConstants::DEFAULT_COUNTRIES[$hostCountry]['value'];
        $countryLabel = CarrierConstants::DEFAULT_COUNTRIES[$hostCountry]['label'];
        $actionUrl = admin_url('admin-post.php');

        $addBody = HtmlHandler::buildHtml('pickup-point-add-form', [
            'actionUrl' => $actionUrl,
            'nonce' => wp_create_nonce('send_pickup_point'),
            'countryValue' => $countryValue,
            'countryLabel' => $countryLabel,
        ]);

        $deleteBody = HtmlHandler::buildHtml('pickup-point-delete-form', [
            'actionUrl' => $actionUrl,
            'nonce' => wp_create_nonce('delete_pickup_point'),
        ]);

        echo SamedayAdminModal::render([
            'id' => self::ADD_MODAL_ID,
            'title' => TranslatorHandler::translate('Add New Pickup Point'),
            'subtitle' => TranslatorHandler::translate('Create a new Sameday pickup point.'),
            'body' => $addBody,
            'class' => 'sameday-pickup-point-modal',
            'confirmLabel' => TranslatorHandler::translate('Save'),
            'confirmFormId' => 'sameday-pickup-point-form',
        ]);

        echo SamedayAdminModal::render([
            'id' => self::DELETE_MODAL_ID,
            'title' => TranslatorHandler::translate('Delete Pickup Point'),
            'subtitle' => TranslatorHandler::translate('This action cannot be undone.'),
            'body' => $deleteBody,
            'class' => 'sameday-delete-pickup-point-modal',
            'confirmLabel' => TranslatorHandler::translate('Delete'),
            'confirmFormId' => 'form-deletePickupPoint',
        ]);
    }
}
