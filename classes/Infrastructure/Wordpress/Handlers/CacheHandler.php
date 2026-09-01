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
     * @param int $timeToLive
     *
     * @return void
     */
    public function refreshCachedData(string $key, array $data, int $timeToLive = 0): void
    {
        $this->invalidateCachedData($key);
        set_transient($key, $data, $timeToLive);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function invalidateCachedData(string $key): void
    {
        delete_transient($key);
    }
}
