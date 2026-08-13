<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

class SamedayCurrencyRules
{
    /**
     * @param float $repayment
     * @param string $destCurrency
     * @param string $storeCurrency
     *
     * @return bool
     */
    public static function hasCurrencyIssue(
        float $repayment,
        string $destCurrency,
        string $storeCurrency
    ): bool
    {
        if ($repayment === .0) {
            return false;
        }

        if ($destCurrency === $storeCurrency) {
            return false;
        }

        return true;
    }
}
