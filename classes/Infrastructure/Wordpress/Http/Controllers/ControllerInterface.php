<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

if (!defined("ABSPATH")) {
    exit;
}

interface ControllerInterface
{
    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @return void
     */
    public function handle(): void;
}