<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\RegistryHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\OptionsHandler;

final class NoticerHandler implements RegistryHandlerInterface
{
    private const NOTICE_KEY = 'samedaycourier_notice_key';

    private const ALLOWED_TYPES = [
        ResponseNoticeType::SUCCESS,
        ResponseNoticeType::ERROR,
    ];

    /**
     * @return void
     */
    public function register(): void
    {
        add_action('admin_notices', [self::class, 'showFlashNotices']);
    }

    /**
     * @param string|null $noticeMessage
     * @param string $noticeType
     * @param bool $dismissible
     *
     * @return void
     */
    /**
     * @param ?string $noticeMessage
     * @param string $noticeType
     * @param bool $dismissible
     *
     * @return void
     */
    public static function addFlashNotice(
        ?string $noticeMessage = '',
        string $noticeType = ResponseNoticeType::ERROR,
        bool $dismissible = true
    ): void {
        $message = $noticeMessage ?? '';
        if ($message === '') {
            return;
        }

        OptionsHandler::setOption(
            self::NOTICE_KEY,
            [
                'message' => $message,
                'type' => self::sanitizeType($noticeType),
                'dismissible' => $dismissible,
            ]
        );
    }

    /**
     * @return void
     */
    /**
     * @return void
     */
    public static function showFlashNotices(): void
    {
        $notice = self::getNotice();
        if ($notice === []) {
            return;
        }

        self::printFlashNotice(
            (string) ($notice['type'] ?? ResponseNoticeType::ERROR),
            (string) ($notice['message'] ?? ''),
            (bool) ($notice['dismissible'] ?? true)
        );

        OptionsHandler::removeOption(self::NOTICE_KEY);
    }

    /**
     * @return array{message?: string, type?: string, dismissible?: bool}
     */
    /**
     * @return array
     */
    private static function getNotice(): array
    {
        $notice = OptionsHandler::getOption(self::NOTICE_KEY, []);

        return is_array($notice) ? $notice : [];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    /**
     * @param string $type
     *
     * @return string
     */
    private static function sanitizeType(string $type): string
    {
        return in_array($type, self::ALLOWED_TYPES, true)
            ? $type
            : ResponseNoticeType::ERROR;
    }

    /**
     * @param string $type
     * @param string $message
     * @param bool $dismissible
     *
     * @return void
     */
    /**
     * @param string $type
     * @param string $message
     * @param bool $dismissible
     *
     * @return void
     */
    private static function printFlashNotice(string $type, string $message, bool $dismissible): void
    {
        if ($message === '') {
            return;
        }

        printf(
            '<div class="notice notice-%1$s%2$s"><p>%3$s</p></div>',
            esc_attr(self::sanitizeType($type)),
            $dismissible ? ' is-dismissible' : '',
            wp_kses_post($message)
        );
    }
}
