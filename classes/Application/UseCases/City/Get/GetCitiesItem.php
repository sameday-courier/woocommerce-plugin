<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class GetCitiesItem implements ItemInterface
{
    /**
     * @var int $countyId
     */
    private int $countyId;

    /**
     * @param int $countyId
     */
    public function __construct(int $countyId)
    {
        $this->countyId = $countyId;
    }

    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        return new self(
            (int) $inputParams['countyId'],
        );
    }

    /**
     * @return int
     */
    public function getCountyId(): int
    {
        return $this->countyId;
    }
}
