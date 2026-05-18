<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Common\ResponseStatus;

if (!defined('ABSPATH')) {
    exit;
}

final class ResponseStatus
{
    public const SUCCESS = 'success';
    public const ERROR = 'error';
}
