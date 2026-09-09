<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Domain\Models\CarrierAwb;
use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services\AwbCurrencyWarningProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\GenerateAwbButton;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters\AwbActionsColumnInWcOrderGrid;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use WC_Order;

final class ShowAwbActionsColumnInWcOrderGridAction extends AbstractAction
{
    private const ACTION = 'manage_woocommerce_page_wc-orders_custom_column';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 10;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return ['column', 'order'];
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $column = $args[0] ?? null;
        $order = $args[1] ?? null;

        if (AwbActionsColumnInWcOrderGrid::COLUMN_KEY !== $column) {
            return;
        }

        if (!$order instanceof WC_Order) {
            return;
        }

        echo $this->buildCurrencyWarningMarker($order);

        $awb = (new OrderAwbStoreServiceProvider())->getByOrderId((int) $order->get_id());

        if (null === $awb) {
            echo $this->buildAddAwbContent($order);

            return;
        }

        echo $this->buildColumnContent($awb);
    }

    /**
     * @param WC_Order $order
     *
     * @return string
     */
    private function buildAddAwbContent(WC_Order $order): string
    {
        return GenerateAwbButton::render((int) $order->get_id());
    }

    /**
     * The bulk AWB modal builds its order list from the checked rows, so the warning has to travel with the row.
     *
     * @param WC_Order $order
     *
     * @return string
     */
    private function buildCurrencyWarningMarker(WC_Order $order): string
    {
        $currencyWarning = AwbCurrencyWarningProvider::forOrder($order);

        if (null === $currencyWarning) {
            return '';
        }

        return sprintf(
            '<span hidden data-sameday-currency-warning="%s"></span>',
            esc_attr($currencyWarning)
        );
    }

    /**
     * @param CarrierAwb $awb
     *
     * @return string
     */
    private function buildColumnContent(CarrierAwb $awb): string
    {
        return HtmlHandler::buildHtml('awb-actions-column', [
            'awbNumber' => (string) $awb->getAwbNumber(),
            'orderId' => (string) $awb->getOrderId(),
        ]);
    }
}
