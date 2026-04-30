<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

if (!defined('ABSPATH')) {
    exit;
}

class EditServiceRequest
{
    public function __construct()
    {
        // samedaycourier-service-name
        // samedaycourier-service-id
        // samedaycourier-free-delivery-price
        // samedaycourier-status
    }
}
