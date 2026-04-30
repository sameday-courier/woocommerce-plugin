<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

if (!defined( 'ABSPATH')) {
    exit;
}

class CacheHandler implements CacheHandlerInterface
{
    /**
     * @param string $key
     *
     * @return array
     */
    public function getCachedData(string $key): array
    {
        return get_transient($key);
    }

    /**
     * @param string $key
     * @param $data
     * @param int $timeToLive lifetime in second. 0 = lifetime
     * @return void
     */
    public function refreshCachedData(string $key, array $data, int $timeToLive = 0): void
    {
        delete_transient($key);
        set_transient($key, $data, $timeToLive);
    }
}
