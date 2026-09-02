<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Domain\Models\CarrierAwb;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services\AwbCurrencyWarningProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters\AwbNumberColumnInWcOrderGrid;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use WC_Order;

final class ShowAwbNumberColumnInWcOrderGridAction extends AbstractAction
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
     * @return string[]|null
     */
    public function getParams(): ?array
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

        if (AwbNumberColumnInWcOrderGrid::COLUMN_KEY !== $column) {
            return;
        }

        if (!$order instanceof WC_Order) {
            return;
        }

        echo $this->buildCurrencyWarningMarker($order);

        $awb = (new OrderAwbStoreServiceProvider())->getByOrderId((int) $order->get_id());

        if (null === $awb) {
            return;
        }

        echo '<span style="display: block">' . esc_html((string) $awb->getAwbNumber()) . '</span>';
        echo $this->generateShowPDFButton($awb);
        echo $this->removeAWB($awb);
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
    private function generateShowPDFButton(CarrierAwb $awb): string
    {
        return sprintf(
            '<button type="button"
                    class="sameday-show-awb-pdf button-link wp-menu-image dashicons-before dashicons-admin-page"
                    style="display: inline-block"
                    title="%s"
                    data-order-id="%s"
                    data-awb-number="%s"></button>',
            esc_attr('Show as PDF'),
            esc_attr((string) $awb->getOrderId()),
            esc_attr((string) $awb->getAwbNumber())
        );
    }

    /**
     * @param CarrierAwb $awb
     *
     * @return string
     */
    private function removeAWB(CarrierAwb $awb): string
    {
        return sprintf(
            '<button type="button"
                    class="sameday-remove-awb button-link wp-menu-image dashicons-before dashicons-trash"
                    style="display: inline-block; color: #b32d2e;"
                    title="%s"
                    data-order-id="%s"
                    data-awb-number="%s"></button>',
            esc_attr('Remove AWB'),
            esc_attr((string) $awb->getOrderId()),
            esc_attr((string) $awb->getAwbNumber())
        );
    }
}
