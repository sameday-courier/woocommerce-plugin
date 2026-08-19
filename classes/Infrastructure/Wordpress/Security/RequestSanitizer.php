<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

use SamedayCourier\Shipping\Domain\CarrierConstants;

final class RequestSanitizer
{
    /**
     * @param array<int, string> $allowedColumns
     */
    /**
     * @param array $allowedColumns
     *
     * @return ?string
     */
    public static function getOrderBy(array $allowedColumns): ?string
    {
        $orderBy = sanitize_text_field(wp_unslash($_REQUEST['orderby'] ?? ''));

        if ('' === $orderBy || !in_array($orderBy, $allowedColumns, true)) {
            return null;
        }

        return $orderBy;
    }

    /**
     * @return ?string
     */
    public static function getOrder(): ?string
    {
        $order = strtoupper(sanitize_text_field(wp_unslash($_REQUEST['order'] ?? '')));

        if ('' === $order || !in_array($order, CarrierConstants::ORDER_BY_TYPES, true)) {
            return null;
        }

        return $order;
    }

    /**
     * @return string
     */
    public static function getPageSlug(): string
    {
        return sanitize_text_field(wp_unslash($_GET['page'] ?? ''));
    }

    /**
     * @return string
     */
    public static function getAction(): string
    {
        return sanitize_text_field(wp_unslash($_GET['action'] ?? ''));
    }

    /**
     * @param string $key
     *
     * @return int
     */
    public static function getIntId(string $key = 'id'): int
    {
        return absint($_GET[$key] ?? 0);
    }
}
