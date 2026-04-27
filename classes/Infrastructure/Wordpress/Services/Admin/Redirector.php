<?php

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin;

if(!defined('ABSPATH')) {
    exit;
}

class Redirector
{
    /**
     * @param string $mainPath such as admin.php, post.php, edit.php
     * @param array $queryArgs
     *
     * @return void
     */
    public static function to(string $mainPath, array $queryArgs = []): void
    {
        wp_safe_redirect(UrlBuilder::build($mainPath, $queryArgs));

        exit;
    }
}