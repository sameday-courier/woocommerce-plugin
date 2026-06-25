<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbResolution
{
    private AwbRecipient $recipient;

    private AwbOohDelivery $oohDelivery;

    /**
     * @var string[]
     */
    private array $serviceTaxIds;

    /**
     * @param string[] $serviceTaxIds
     */
    public function __construct(
        AwbRecipient $recipient,
        AwbOohDelivery $oohDelivery,
        array $serviceTaxIds
    ) {
        $this->recipient = $recipient;
        $this->oohDelivery = $oohDelivery;
        $this->serviceTaxIds = $serviceTaxIds;
    }

    public function getRecipient(): AwbRecipient
    {
        return $this->recipient;
    }

    public function getOohDelivery(): AwbOohDelivery
    {
        return $this->oohDelivery;
    }

    /**
     * @return string[]
     */
    public function getServiceTaxIds(): array
    {
        return $this->serviceTaxIds;
    }
}
