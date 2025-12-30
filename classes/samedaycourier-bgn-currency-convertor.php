<?php

class BgnCurrencyConverter
{
    private string $currency;
    private float $amount;

    public function __construct(string $currency, float $amount)
    {
        $this->currency = $currency;
        $this->amount = $amount;
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function convert(): string
    {
        switch ($this->currency) {
            case SamedayCourierHelperClass::EURO_CURRENCY:
                return $this->convertBGNtoEUR($this->amount);
            case SamedayCourierHelperClass::CURRENCY_MAPPER[SamedayCourierHelperClass::API_HOST_LOCALE_BG]:
                return $this->convertEURtoBGN($this->amount);
            default:
                throw new RuntimeException('Invalid currency');
        }
    }

    /**
     * @param string $carrierName
     * @param string $price
     * @param string $storeCurrency
     * @param string $estimatedPrice
     * @param string $estimatedCurrency
     *
     * @return string
     */
    public function buildCurrencyConversionLabel(
        string $carrierName,
        string $price,
        string $storeCurrency,
        string $estimatedPrice,
        string $estimatedCurrency
    ): string
    {
        return sprintf(
            '%s: <span class="woocommerce-Price-amount amount"><bdi>%s&nbsp;<span class="woocommerce-Price-currencySymbol">%s</span> <span style="font-size: smaller"> %s </span></bdi></span>',
            $carrierName,
            number_format($price, 2, '.', ''),
            get_woocommerce_currency_symbol($storeCurrency),
            sprintf("(≈ %s %s)",
                $estimatedPrice,
                get_woocommerce_currency_symbol($estimatedCurrency)
            )
        );
    }

    /**
     * @param float $amount
     *
     * @return string
     */
    private function convertBGNtoEUR(float $amount): string
    {
        return number_format(($amount * 0.511292), 2, '.', '');
    }

    /**
     * @param float $amount
     *
     * @return string
     */
    private function convertEURtoBGN(float $amount): string
    {
        return number_format(($amount * 1.95583), 2, '.', '');
    }
}
