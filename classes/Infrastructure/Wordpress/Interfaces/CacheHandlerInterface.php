<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces;

interface CacheHandlerInterface
{
    /**
     * @param string $key
     *
     * @return array
     */
    public function getCachedData(string $key): array;

    /**
     * @param string $key
     * @param array $data
     * @param int $timeToLive
     *
     * @return void
     */
    public function refreshCachedData(string $key, array $data, int $timeToLive): void;
}
