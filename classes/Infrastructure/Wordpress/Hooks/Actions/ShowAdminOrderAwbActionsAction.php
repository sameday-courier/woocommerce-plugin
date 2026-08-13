<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbForm;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\NewParcelForm;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderAwbProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\ShowHistoryAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\WordpressSamedaySettingsProvider;

final class ShowAdminOrderAwbActionsAction extends AbstractAction
{
    private const ACTION = 'woocommerce_admin_order_data_after_shipping_address';

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

        add_thickbox();

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
                <a href="#TB_inline?&width=1000&height=470&inlineId=sameday-shipping-content-add-awb" class="sameday_admin_button button-samll thickbox"> %s </a>
            </p>',
            TranslatorHandler::translate('Generate awb')
        );

        $showAwb = sprintf(
            '<p class="form-field form-field-wide wc-customer-user">
                <a href="#TB_inline?&width=670&height=470&inlineId=sameday-shipping-content-add-new-parcel" class="sameday_admin_button button-samll thickbox"> %s </a>
                <a href="#TB_inline?&width=1024&height=400&inlineId=sameday-shipping-content-awb-history" class="sameday_admin_button button-samll thickbox"> %s </a>
                <input type="hidden" form="showAsPdf" name="order-id" value="%s">
                <button type="submit" form="showAsPdf" formtarget="_blank" class="sameday_admin_button button-samll">%s </button>
            </p>',
            TranslatorHandler::translate('Add new parcel'),
            TranslatorHandler::translate('Awb history'),
            $order->get_id(),
            TranslatorHandler::translate('Show as pdf')
        );

        $removeAwb = sprintf(
            '<p class="form-field form-field-wide wc-customer-user">
                <input type="hidden" form="removeAwb" name="order-id" value="%s">
                <button type="submit" form="removeAwb" class="sameday_admin_button button-samll">%s </button>
            </p>',
            $order->get_id(),
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
            $addNewParcelForm = NewParcelForm::addNewParcelForm($order->get_id());

            $newParcelModal = sprintf(
                '<div id="sameday-shipping-content-add-new-parcel" style="display: none;">%s</div>',
                $addNewParcelForm
            );

            $historyModal = sprintf(
                '<div id="sameday-shipping-content-awb-history" style="display: none;">%s</div>',
                $awbHistoryTable
            );

            $redirectToEawbSite = sprintf(
                '%s/awb?awbOrParcelNumber=%s&tab=allAwbs',
                SamedayConstants::EAWB_INSTANCES[(new WordpressSamedaySettingsProvider())->get()->getHostCountry()],
                $awb->getAwbNumber()
            );

            $goToEawb = sprintf(
                '<p class="form-field form-field-wide wc-customer-user">
                    <a href="%s" target="_blank" class="sameday_admin_button button-samll">%s </a>
                </p>',
                $redirectToEawbSite,
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
