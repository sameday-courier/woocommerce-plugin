<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

use SamedayCourier\Shipping\Domain\SamedayConstants;

if (!defined('ABSPATH')) {
    exit;
}

final class RequestSanitizer
{
    /**
     * @param array<int, string> $allowedColumns
     */
    public static function getOrderBy(array $allowedColumns): ?string
    {
        $orderBy = sanitize_text_field(wp_unslash($_REQUEST['orderby'] ?? ''));

        if ('' === $orderBy || !in_array($orderBy, $allowedColumns, true)) {
            return null;
        }

        return $orderBy;
    }

    public static function getOrder(): ?string
    {
        $order = strtoupper(sanitize_text_field(wp_unslash($_REQUEST['order'] ?? '')));

        if ('' === $order || !in_array($order, SamedayConstants::ORDER_BY_TYPES, true)) {
            return null;
        }

        return $order;
    }

    public static function getPageSlug(): string
    {
        return sanitize_text_field(wp_unslash($_GET['page'] ?? ''));
    }

    public static function getAction(): string
    {
        return sanitize_text_field(wp_unslash($_GET['action'] ?? ''));
    }

    public static function getIntId(string $key = 'id'): int
    {
        return absint($_GET[$key] ?? 0);
    }
}
