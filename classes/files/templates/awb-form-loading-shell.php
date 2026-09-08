<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;

/**
 * @var string $modalId
 * @var string $iconHtml
 * @var string $title
 * @var string $subtitle
 * @var string $cancelLabel
 * @var string $loadingText
 */
?>
<div id="<?php echo esc_attr($modalId); ?>"
     class="sameday-bulk-awb-modal sameday-generate-awb-modal"
     data-sameday-generate-awb-modal>
    <div class="sameday-bulk-awb-modal__backdrop" data-sameday-generate-awb-close></div>
    <div class="sameday-bulk-awb-modal__dialog"
         role="dialog"
         aria-modal="true"
         aria-busy="true"
         aria-labelledby="<?php echo esc_attr($modalId); ?>-title">
        <?php echo HtmlHandler::buildHtml('awb-form-modal-header', [
            'modalId' => $modalId,
            'iconHtml' => $iconHtml,
            'title' => $title,
            'subtitle' => $subtitle,
            'cancelLabel' => $cancelLabel,
        ]); ?>
        <div class="sameday-bulk-awb-modal__body">
            <div class="sameday-bulk-awb-modal__starting" role="status" aria-live="polite">
                <div class="sameday-bulk-awb-modal__spinner" aria-hidden="true"></div>
                <p class="sameday-bulk-awb-modal__starting-text"><?php echo esc_html($loadingText); ?></p>
            </div>
        </div>
    </div>
</div>
