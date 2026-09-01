<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierCurrencyRules;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use WC_Order;

final class AwbCurrencyWarningProvider
{
    private const MESSAGE_TEMPLATE = 'Be aware that the intended currency is %s '
        . 'but the Repayment value is expressed in %s. Please consider a conversion !!';

    /**
     * @param WC_Order $order
     *
     * @return string|null
     */
    public static function forOrder(WC_Order $order): ?string
    {
        $destinationCurrency = self::resolveDestinationCurrency($order);

        if (null === $destinationCurrency) {
            return null;
        }

        return self::resolve(
            self::resolveRepayment($order),
            $destinationCurrency,
            self::resolveOrderCurrency($order)
        );
    }

    /**
     * @param WC_Order $order
     *
     * @return float
     */
    public static function resolveRepayment(WC_Order $order): float
    {
        $paymentGateway = wc_get_payment_gateway_by_order($order);

        if (false === $paymentGateway || null === $paymentGateway) {
            return 0.0;
        }

        if (CarrierConstants::CASH_ON_DELIVERY !== $paymentGateway->id) {
            return 0.0;
        }

        return (float) $order->get_total();
    }

    /**
     * @param WC_Order $order
     *
     * @return string
     */
    public static function resolveOrderCurrency(WC_Order $order): string
    {
        return $order->get_currency() ?: get_woocommerce_currency();
    }

    /**
     * @param WC_Order $order
     *
     * @return string|null
     */
    private static function resolveDestinationCurrency(WC_Order $order): ?string
    {
        $shipping = $order->get_data()['shipping'] ?? [];

        return CarrierCurrencyRules::resolveForCountry($shipping['country'] ?? null);
    }

    /**
     * @param float $repayment
     * @param string $destinationCurrency
     * @param string $orderCurrency
     *
     * @return string|null
     */
    private static function resolve(
        float $repayment,
        string $destinationCurrency,
        string $orderCurrency
    ): ?string {
        if (!CarrierCurrencyRules::hasCurrencyIssue($repayment, $destinationCurrency, $orderCurrency)) {
            return null;
        }

        return sprintf(
            TranslatorHandler::translate(self::MESSAGE_TEMPLATE),
            $destinationCurrency,
            $orderCurrency
        );
    }
}
