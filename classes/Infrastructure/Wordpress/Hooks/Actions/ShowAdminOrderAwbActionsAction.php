<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbForm;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\NewParcelForm;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\SamedayAdminModal;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderAwbProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\ShowHistoryAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;

final class ShowAdminOrderAwbActionsAction extends AbstractAction
{
    private const ACTION = 'woocommerce_admin_order_data_after_shipping_address';
    private const HISTORY_MODAL_ID = 'sameday-awb-history-modal';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @return string[]|null
     */
    public function getParams(): ?array
    {
        return ['order'];
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $order = $args[0] ?? null;
        if (null === $order) {
            return;
        }

        if (!isset($_GET['action']) || 'edit' !== $_GET['action']) {
            return;
        }

        echo $this->buildHtmlContent($order);
    }

    /**
     * @param mixed $order
     *
     * @return string
     */
    private function buildHtmlContent($order): string
    {
        $generateAwb = sprintf(
            '<p class="form-field form-field-wide wc-customer-user">
                <a href="#" class="sameday_admin_button button-samll" role="button" data-sameday-generate-awb-open="%s"> %s </a>
            </p>',
            esc_attr(AwbForm::MODAL_ID),
            TranslatorHandler::translate('Generate awb')
        );

        $showAwb = sprintf(
            '<p class="form-field form-field-wide wc-customer-user">
                <a href="#" class="sameday_admin_button button-samll" role="button" data-sameday-modal-open="%s"> %s </a>
                <a href="#" class="sameday_admin_button button-samll" role="button" data-sameday-modal-open="%s"> %s </a>
                <input type="hidden" form="showAsPdf" name="order-id" value="%s">
                <button type="submit" form="showAsPdf" formtarget="_blank" class="sameday_admin_button button-samll">%s </button>
            </p>',
            esc_attr(NewParcelForm::MODAL_ID),
            TranslatorHandler::translate('Add new parcel'),
            esc_attr(self::HISTORY_MODAL_ID),
            TranslatorHandler::translate('Awb history'),
            esc_attr((string) $order->get_id()),
            TranslatorHandler::translate('Show as pdf')
        );

        $removeAwb = sprintf(
            '<p class="form-field form-field-wide wc-customer-user">
                <input type="hidden" form="removeAwb" name="order-id" value="%s">
                <button type="submit" form="removeAwb" class="sameday_admin_button button-samll">%s </button>
            </p>',
            esc_attr((string) $order->get_id()),
            TranslatorHandler::translate('Remove Awb')
        );

        $buttons = sprintf(
            '<div class="address">%s</div>',
            $generateAwb
        );

        $awb = (new WooOrderAwbProvider(
            new SamedayAwbRepository(),
        ))->get((int) $order->get_id());

        $newParcelModal = '';
        $historyModal = '';
        $goToEawb = '';

        if (null !== $awb) {
            $buttons = sprintf(
                '<div class="address">%s%s</div>',
                $showAwb,
                $removeAwb
            );

            $awbHistoryTable = (new ShowHistoryAwbController())->render((int) $order->get_id());
            $newParcelModal = NewParcelForm::addNewParcelForm($order->get_id());

            $historyModal = SamedayAdminModal::render([
                'id' => self::HISTORY_MODAL_ID,
                'title' => TranslatorHandler::translate('Awb History'),
                'subtitle' => TranslatorHandler::translate('Parcel status history for this AWB.'),
                'body' => '<div id="sameday-shipping-content-awb-history">' . $awbHistoryTable . '</div>',
                'class' => 'sameday-awb-history-modal',
                'confirmLabel' => null,
                'cancelLabel' => TranslatorHandler::translate('Close'),
            ]);

            $redirectToEawbSite = sprintf(
                '%s/awb?awbOrParcelNumber=%s&tab=allAwbs',
                CarrierConstants::EAWB_INSTANCES[(new CarrierSettingsServiceProvider())->get()->getHostCountry()],
                $awb->getAwbNumber()
            );

            $goToEawb = sprintf(
                '<p class="form-field form-field-wide wc-customer-user">
                    <a href="%s" target="_blank" class="sameday_admin_button button-samll">%s </a>
                </p>',
                esc_url($redirectToEawbSite),
                TranslatorHandler::translate('Sameday eAwb')
            );
        }

        $awbModal = (new AwbForm(
            new SamedayServiceRepository(),
            new SamedayLockerRepository(),
            new SamedayPickupPointRepository()
        ))->samedaycourierAddAwbForm($order);

        return $buttons . $awbModal . $newParcelModal . $historyModal . $goToEawb;
    }
}
