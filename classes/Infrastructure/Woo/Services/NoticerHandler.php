<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

class NoticerHandler
{
    public const NOTICE_DOMAIN = 'sameday';
    private const NOTICE_KEY = 'samedaycourier_notice_key';

    /**
     * @param string $notice_message
     * @param string $type
     * @param bool $dismissible
     *
     * @return void
     */
    public static function addFlashNotice(
        string $noticeMessage = "",
        string $noticeType = "warning",
        bool $dismissible = true
    ): void
    {
        OptionsHandler::setOption(
            self::NOTICE_KEY,
            [
                "message" => $noticeMessage,
                "type" => $noticeType,
                "dismissible" => $dismissible
            ]
        );
    }

    /**
     * @return void
     */
    public static function showFlashNotice(): void
    {
        $notices = OptionsHandler::getOption(self::NOTICE_KEY);
        if (!empty($notices)) {
            self::printFlashNotice($notices['type'], $notices['message'], $notices['dismissible']);

            // After show flash message in page, remove it from db.
            OptionsHandler::removeOption(self::NOTICE_KEY);
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
        printf(
            '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            $type,
            ($dismissible) ? "is-dismissible" : "",
            $message
        );
    }
}
