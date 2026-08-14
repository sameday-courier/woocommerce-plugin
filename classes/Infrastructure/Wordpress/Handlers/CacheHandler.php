<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\CacheHandlerInterface;

final class CacheHandler implements CacheHandlerInterface
{
    /**
     * @param string $key
     *
     * @return array
     */
    public function getCachedData(string $key): array
    {
        $data = get_transient($key);

        return is_array($data) ? $data : [];
    }

    /**
     * @param string $key
     * @param array $data
     * @param int $timeToLiveInSeconds lifetime in second. 0 = lifetime
     * @return void
     */
    public function refreshCachedData(string $key, array $data, int $timeToLiveInSeconds = 0): void
    {
        delete_transient($key);
        set_transient($key, $data, $timeToLiveInSeconds);
    }
}
