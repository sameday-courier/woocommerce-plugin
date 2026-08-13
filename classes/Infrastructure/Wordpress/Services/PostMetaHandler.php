<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

final class PostMetaHandler
{
    /**
     * @param int $postId
     * @param string $key
     * @param bool $single
     *
     * @return mixed
     */
    public static function get(int $postId, string $key, bool $single = true)
    {
        return get_post_meta($postId, $key, $single);
    }

    /**
     * @param int $postId
     * @param string $key
     * @param mixed $value
     * @param bool $unique
     *
     * @return void
     */
    public static function update(int $postId, string $key, $value, bool $unique = false): void
    {
        update_post_meta($postId, $key, $value, $unique);
    }
}
