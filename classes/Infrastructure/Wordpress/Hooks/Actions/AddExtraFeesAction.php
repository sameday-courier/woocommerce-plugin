<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Domain\SamedaySettings;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

if (!defined('ABSPATH')) {
    exit;
}

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
        if (!defined( 'DOING_AJAX') && is_admin()) {
            return;
        }

        if ($this->hasExtraFees()) {
            $woocommerce->cart->add_fee(
                SamedaySettings::getRepaymentTaxLabel() ?? TranslatorHandler::translate('Repayment tax'),
                SamedaySettings::getRepaymentTax(),
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
        $repayment_tax = SamedaySettings::getRepaymentTax() ?? 0;
        if ($repayment_tax <= 0) {
            return false;
        }

        $chosenDeliveryMethod = (new WooSessionHandler())->get(SamedaySessionKeys::CHOSEN_PAYMENT_METHOD);
        $isCod = SamedayConstants::CASH_ON_DELIVERY;

        return $chosenDeliveryMethod === $isCod;
    }
}


