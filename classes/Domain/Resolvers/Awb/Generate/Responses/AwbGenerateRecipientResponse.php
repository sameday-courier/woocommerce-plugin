<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses;

use SamedayCourier\Shipping\Domain\DTOs\OohDto;
use SamedayCourier\Shipping\Domain\DTOs\RecipientDto;

class AwbGenerateRecipientResponse
{
    /**
     * @var OohDto $oohDto
     */
    private OohDto $oohDto;

    /**
     * @var RecipientDto $recipient
     */
    private RecipientDto $recipient;

    /**
     * @var string $currency
     */
    private string $currency;

    /**
     * @param RecipientDto $recipient
     * @param OohDto $oohDto
     * @param string $currency
     */
    public function __construct(
        RecipientDto $recipient,
        OohDto $oohDto,
        string $currency
    )
    {
        $this->recipient = $recipient;
        $this->oohDto = $oohDto;
        $this->currency = $currency;
    }

    /**
     * @return RecipientDto
     */
    public function getRecipient(): RecipientDto
    {
        return $this->recipient;
    }

    /**
     * @return OohDto
     */
    public function getOoh(): OohDto
    {
        return $this->oohDto;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
}
