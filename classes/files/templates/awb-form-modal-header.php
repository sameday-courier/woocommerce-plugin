<?php

declare(strict_types=1);

/**
 * @var string $modalId
 * @var string $iconHtml
 * @var string $title
 * @var string $subtitle
 * @var string $cancelLabel
 */
?>
<div class="sameday-bulk-awb-modal__header">
    <div class="sameday-bulk-awb-modal__heading">
        <div class="sameday-bulk-awb-modal__icon" aria-hidden="true">
            <?php echo $iconHtml; ?>
        </div>
        <div class="sameday-bulk-awb-modal__titles">
            <h2 id="<?php echo esc_attr($modalId); ?>-title"><?php echo esc_html($title); ?></h2>
            <?php if ('' !== $subtitle) : ?>
                <p><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <button type="button"
            class="sameday-bulk-awb-modal__close"
            data-sameday-generate-awb-close
            aria-label="<?php echo esc_attr($cancelLabel); ?>">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
