<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\SamedayIcon;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

/**
 * Shared Sameday admin modal markup (same shell as Bulk / Generate AWB).
 */
final class SamedayAdminModal
{
    /**
     * @param array $args
     *
     * @return string
     */
    public static function render(array $args): string
    {
        $id = (string) ($args['id'] ?? '');
        $title = (string) ($args['title'] ?? '');
        $subtitle = (string) ($args['subtitle'] ?? '');
        $body = (string) ($args['body'] ?? '');
        $extraClass = trim((string) ($args['class'] ?? ''));
        $cancelLabel = (string) ($args['cancelLabel'] ?? TranslatorHandler::translate('Cancel'));
        $confirmLabel = array_key_exists('confirmLabel', $args) ? $args['confirmLabel'] : null;
        $confirmFormId = (string) ($args['confirmFormId'] ?? '');
        $confirmType = (string) ($args['confirmType'] ?? 'submit');
        $footerHtml = array_key_exists('footerHtml', $args) ? $args['footerHtml'] : null;

        if (null === $footerHtml) {
            $footerHtml = HtmlHandler::buildHtml('admin-modal-footer', [
                'cancelLabel' => $cancelLabel,
                'confirmLabel' => $confirmLabel,
                'confirmType' => $confirmType,
                'confirmFormId' => $confirmFormId,
            ]);
        }

        return HtmlHandler::buildHtml('admin-modal', [
            'id' => $id,
            'classes' => trim('sameday-bulk-awb-modal ' . $extraClass),
            'title' => $title,
            'subtitle' => $subtitle,
            'cancelLabel' => $cancelLabel,
            'body' => $body,
            'iconHtml' => SamedayIcon::render(
                'sameday-bulk-awb-modal__icon-svg sameday-bulk-awb-modal__icon-svg--package',
                26
            ),
            'footerHtml' => $footerHtml,
        ]);
    }
}
