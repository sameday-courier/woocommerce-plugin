<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class EditServiceRequest
{
    /**
     * @var EditServiceItem $editServiceItem
     */
    private EditServiceItem $editServiceItem;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param EditServiceItem $editServiceItem
     * @param SamedayServiceRepository $samedayServiceRepository
     */
    public function __construct(
        EditServiceItem $editServiceItem,
        SamedayServiceRepository $samedayServiceRepository
    ) {
        $this->editServiceItem = $editServiceItem;
        $this->samedayServiceRepository = $samedayServiceRepository;
    }

    /**
     * @return EditServiceItem
     */
    public function getEditServiceItem(): EditServiceItem
    {
        return $this->editServiceItem;
    }

    /**
     * @return SamedayServiceRepository
     */
    public function getSamedayServiceRepository(): SamedayServiceRepository
    {
        return $this->samedayServiceRepository;
    }
}
