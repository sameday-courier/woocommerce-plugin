<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;

final class AddExtraFeesAction extends AbstractAction
{
    private const ACTION = 'woocommerce_cart_calculate_fees';

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
        return 100;
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        global $woocommerce;
        if (!defined('DOING_AJAX') && is_admin()) {
            return;
        }

        if ($this->hasExtraFees()) {
            $settings = (new CarrierSettingsServiceProvider())->get();
            $woocommerce->cart->add_fee(
                $settings->getRepaymentTaxLabel() ?? TranslatorHandler::translate('Repayment tax'),
                $settings->getRepaymentTax(),
                true,
                ''
            );
        }
    }

    /**
     * @return bool
     */
    private function hasExtraFees(): bool
    {
        $repayment_tax = (new CarrierSettingsServiceProvider())->get()->getRepaymentTax() ?? 0;
        if ($repayment_tax <= 0) {
            return false;
        }

        $chosenDeliveryMethod = (new WooSessionHandler())->get(CarrierSessionKeys::CHOSEN_PAYMENT_METHOD);
        $isCod = CarrierConstants::CASH_ON_DELIVERY;

        return $chosenDeliveryMethod === $isCod;
    }
}
