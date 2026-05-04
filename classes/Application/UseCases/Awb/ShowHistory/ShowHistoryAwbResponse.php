<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

if (!defined('ABSPATH')) {
    exit;
}

class ShowHistoryAwbResponse
{
    private string $html;

    public function __construct(string $html = '')
    {
        $this->html = $html;
    }

    public function getHtml(): string
    {
        return $this->html;
    }
}
