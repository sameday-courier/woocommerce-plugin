<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\SamedayIcon;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

/**
 * Shared Sameday admin modal markup (same shell as Bulk / Generate AWB).
 */
final class SamedayAdminModal
{
    /**
     * @param array{
     *     id: string,
     *     title: string,
     *     subtitle?: string,
     *     body: string,
     *     class?: string,
     *     cancelLabel?: string,
     *     confirmLabel?: string|null,
     *     confirmFormId?: string|null,
     *     confirmType?: string,
     *     footerHtml?: string|null,
     * } $args
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

        $classes = trim('sameday-bulk-awb-modal ' . $extraClass);

        if (null === $footerHtml) {
            $confirmButton = '';
            if (null !== $confirmLabel && '' !== $confirmLabel) {
                $confirmButton = sprintf(
                    '<button type="%1$s"%2$s class="sameday_button sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--confirm">%3$s</button>',
                    esc_attr($confirmType),
                    '' !== $confirmFormId ? ' form="' . esc_attr($confirmFormId) . '"' : '',
                    esc_html((string) $confirmLabel)
                );
            }

            $footerHtml = sprintf(
                '<button type="button" class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--cancel" data-sameday-modal-close>
                    %1$s
                </button>
                %2$s',
                esc_html($cancelLabel),
                $confirmButton
            );
        }

        return sprintf(
            '<div id="%1$s" class="%2$s" hidden data-sameday-modal>
                <div class="sameday-bulk-awb-modal__backdrop" data-sameday-modal-close></div>
                <div class="sameday-bulk-awb-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="%1$s-title">
                    <div class="sameday-bulk-awb-modal__header">
                        <div class="sameday-bulk-awb-modal__heading">
                            <div class="sameday-bulk-awb-modal__icon" aria-hidden="true">
                                %7$s
                            </div>
                            <div class="sameday-bulk-awb-modal__titles">
                                <h2 id="%1$s-title">%3$s</h2>
                                %4$s
                            </div>
                        </div>
                        <button type="button" class="sameday-bulk-awb-modal__close" data-sameday-modal-close aria-label="%5$s">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="sameday-bulk-awb-modal__body">
                        %6$s
                    </div>
                    <div class="sameday-bulk-awb-modal__footer">
                        %8$s
                    </div>
                </div>
            </div>',
            esc_attr($id),
            esc_attr($classes),
            esc_html($title),
            '' !== $subtitle ? '<p>' . esc_html($subtitle) . '</p>' : '',
            esc_attr($cancelLabel),
            $body,
            SamedayIcon::render('sameday-bulk-awb-modal__icon-svg sameday-bulk-awb-modal__icon-svg--package', 26),
            $footerHtml
        );
    }
}
