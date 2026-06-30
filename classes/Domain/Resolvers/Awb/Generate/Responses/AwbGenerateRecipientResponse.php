<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses;

use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use SamedayCourier\Shipping\Domain\DTOs\OohDto;

if (!defined('ABSPATH')) {
    exit;
}

class AwbGenerateRecipientResponse
{
    /**
     * @var AwbRecipientEntityObject $recipient
     */
    private AwbRecipientEntityObject $recipient;

    /**
     * @var OohDto $oohDto
     */
    private OohDto $oohDto;

    /**
     * @param OohDto $oohDto
     * @param AwbRecipientEntityObject $recipient
     */
    public function __construct(
        OohDto $oohDto,
        AwbRecipientEntityObject $recipient
    )
    {
        $this->oohDto = $oohDto;
        $this->recipient = $recipient;
    }

    /**
     * @return AwbRecipientEntityObject
     */
    public function getRecipient(): AwbRecipientEntityObject
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
}
