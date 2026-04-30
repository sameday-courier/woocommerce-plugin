<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

class NoticerHandler
{
    /**
     * @param string $notice
     * @param string $notice_message
     * @param string $type
     * @param bool $dismissible
     *
     * @return void
     */
    public static function addFlashNotice(
        string $notice = "",
        string $notice_message = "",
        string $type = "warning",
        bool $dismissible = false
    ): void
    {
        OptionsHandler::setOption(
            $notice,
            [
                "message" => $notice_message,
                "type" => $type,
                "dismissible" => $dismissible
            ]
        );
    }

    /**
     * @param $notice
     *
     * @return void
     */
    public static function showFlashNotice($notice): void
    {
        $notices = OptionsHandler::getOption($notice);
        if (!empty($notices)) {
            self::printFlashNotice($notices['type'], $notices['message'], $notices['dismissible']);

            // After show flash message in page, remove it from db.
            OptionsHandler::removeOption($notice);
        }
    }

    /**
     * @param $type
     * @param $dismissible
     * @param $message
     *
     * @return void
     */
    public static function printFlashNotice($type, $message, $dismissible): void
    {
        printf( '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            $type,
            ($dismissible) ? "is-dismissible" : "",
            $message
        );
    }
}
