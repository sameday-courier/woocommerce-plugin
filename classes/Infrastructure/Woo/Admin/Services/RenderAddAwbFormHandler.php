<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbForm;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\AwbFormFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use WC_Order;

final class RenderAddAwbFormHandler
{
    /**
     * @var OrderAwbStoreServiceProvider
     */
    private OrderAwbStoreServiceProvider $orderAwbStore;

    /**
     * @param OrderAwbStoreServiceProvider|null $orderAwbStore
     */
    public function __construct(?OrderAwbStoreServiceProvider $orderAwbStore = null)
    {
        $this->orderAwbStore = $orderAwbStore ?? new OrderAwbStoreServiceProvider();
    }

    /**
     * @param int $orderId
     *
     * @return array{html: string, modalId: string}|array{error: string}
     */
    public function renderForOrderId(int $orderId): array
    {
        if ($orderId <= 0) {
            return [
                'error' => TranslatorHandler::translate('Order id is required.'),
            ];
        }

        $order = wc_get_order($orderId);

        if (!$order instanceof WC_Order) {
            return [
                'error' => TranslatorHandler::translate('Order not found.'),
            ];
        }

        if (null !== $this->orderAwbStore->getByOrderId($orderId)) {
            return [
                'error' => TranslatorHandler::translate('This order already has an AWB.'),
            ];
        }

        return [
            'html' => AwbFormFactory::create()->samedaycourierAddAwbForm($order),
            'modalId' => AwbForm::MODAL_ID,
        ];
    }
}
