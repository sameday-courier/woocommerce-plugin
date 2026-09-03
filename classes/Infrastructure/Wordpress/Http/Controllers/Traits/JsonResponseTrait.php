<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Traits;

/**
 * Shared JSON responders for admin-post / AJAX controllers.
 */
trait JsonResponseTrait
{
    /**
     * @param mixed $payload
     * @param int $statusCode
     *
     * @return void
     */
    protected function sendJsonErrorResponse(
        $payload,
        int $statusCode = 400
    ): void {
        wp_send_json_error($payload, $statusCode);
    }

    /**
     * @param mixed $payload
     * @param int $statusCode
     *
     * @return void
     */
    protected function sendJsonSuccessResponse(
        $payload,
        int $statusCode = 200
    ): void {
        wp_send_json_success($payload, $statusCode);
    }
}
