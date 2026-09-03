<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Traits;

trait HandlesControllerAccessTrait
{
    /**
     * @param string $message
     *
     * @return void
     */
    protected function denyAccess(string $message): void
    {
        if (wp_doing_ajax()) {
            $this->sendJsonErrorResponse(['message' => $message], 403);
        }

        wp_die(
            esc_html($message),
            esc_html__('Access denied', 'sameday'),
            ['response' => 403]
        );
    }
}
