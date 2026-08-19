<?php

declare(strict_types=1);

/**
 * @var string $cancelLabel
 * @var string|null $confirmLabel
 * @var string $confirmType
 * @var string $confirmFormId
 */
?>
<button type="button"
        class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--cancel"
        data-sameday-modal-close>
    <?php echo esc_html($cancelLabel); ?>
</button>
<?php if (null !== $confirmLabel && '' !== $confirmLabel) : ?>
    <button type="<?php echo esc_attr($confirmType); ?>"
            <?php echo '' !== $confirmFormId ? 'form="' . esc_attr($confirmFormId) . '"' : ''; ?>
            class="sameday_button sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--confirm">
        <?php echo esc_html((string) $confirmLabel); ?>
    </button>
<?php endif; ?>
