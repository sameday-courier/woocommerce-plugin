<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses;

use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use SamedayCourier\Shipping\Domain\DTOs\OohDto;
use SamedayCourier\Shipping\Domain\DTOs\RecipientDto;

if (!defined('ABSPATH')) {
    exit;
}

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
     * @param OohDto $oohDto
     * @param RecipientDto $recipient
     * @param string $currency
     */
    public function __construct(
        OohDto $oohDto,
        RecipientDto $recipient,
        string $currency
    )
    {
        $this->oohDto = $oohDto;
        $this->recipient = $recipient;
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
