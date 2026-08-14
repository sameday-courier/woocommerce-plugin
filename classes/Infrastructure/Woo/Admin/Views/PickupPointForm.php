<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\WordpressSamedaySettingsProvider;

class PickupPointForm
{
    public const ADD_MODAL_ID = 'sameday-pickup-point-modal';
    public const DELETE_MODAL_ID = 'sameday-delete-pickup-point-modal';

    public static function renderModals(): void
    {
        $hostCountry = (new WordpressSamedaySettingsProvider())->get()->getHostCountry();
        $countryValue = SamedayConstants::DEFAULT_COUNTRIES[$hostCountry]['value'];
        $countryLabel = SamedayConstants::DEFAULT_COUNTRIES[$hostCountry]['label'];

        $addBody = sprintf(
            '<form id="sameday-pickup-point-form" method="POST" action="%1$s">
                <input type="hidden" name="action" value="send_pickup_point">
                <input type="hidden" name="_wpnonce" value="%2$s">
                <div class="sameday-pickup-point-form">
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointCountry">%3$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <select name="pickupPointCountry" id="pickupPointCountry">
                                <option value="%4$s">%5$s</option>
                            </select>
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointCounty">%6$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <select name="pickupPointCounty" id="pickupPointCounty" required disabled></select>
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointCity">%7$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <select name="pickupPointCity" id="pickupPointCity" required disabled></select>
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointAddress">%8$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <input type="text" name="pickupPointAddress" id="pickupPointAddress" required>
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointDefault">%9$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <input type="checkbox" class="sameday-modal-checkbox" name="pickupPointDefault" id="pickupPointDefault" value="1">
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointPostalCode">%10$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <input type="number" name="pickupPointPostalCode" id="pickupPointPostalCode" required>
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointAlias">%11$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <input type="text" name="pickupPointAlias" id="pickupPointAlias" required>
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointContactPersonName">%12$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <input type="text" name="pickupPointContactPersonName" id="pickupPointContactPersonName" required>
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointContactPersonPhone">%13$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <input type="number" name="pickupPointContactPersonPhone" id="pickupPointContactPersonPhone" required>
                        </div>
                    </div>
                    <div class="sameday-pickup-point-form__group">
                        <label for="pickupPointEmail">%14$s</label>
                        <div class="sameday-pickup-point-form__input">
                            <input type="email" name="pickupPointEmail" id="pickupPointEmail" required>
                        </div>
                    </div>
                </div>
            </form>',
            esc_url(admin_url('admin-post.php')),
            esc_attr(wp_create_nonce('send_pickup_point')),
            esc_html(TranslatorHandler::translate('Country')),
            esc_attr($countryValue),
            esc_html($countryLabel),
            esc_html(TranslatorHandler::translate('County')),
            esc_html(TranslatorHandler::translate('City')),
            esc_html(TranslatorHandler::translate('Address')),
            esc_html(TranslatorHandler::translate('Default')),
            esc_html(TranslatorHandler::translate('Postal Code')),
            esc_html(TranslatorHandler::translate('Alias')),
            esc_html(TranslatorHandler::translate('Contact Person Name')),
            esc_html(TranslatorHandler::translate('Contact Person Phone')),
            esc_html(TranslatorHandler::translate('Email'))
        );

        $deleteBody = sprintf(
            '<form id="form-deletePickupPoint" method="POST" action="%1$s">
                <input type="hidden" name="sameday_id" id="input-deletePickupPoint">
                <input type="hidden" name="action" value="delete_pickup_point">
                <input type="hidden" name="_wpnonce" value="%2$s">
                <p class="sameday-bulk-awb-modal__summary">%3$s</p>
            </form>',
            esc_url(admin_url('admin-post.php')),
            esc_attr(wp_create_nonce('delete_pickup_point')),
            esc_html(TranslatorHandler::translate('Are you sure you want to delete this pickup point?'))
        );

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
