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
    <?php echo $cancelLabel; ?>
</button>
<?php if (null !== $confirmLabel && '' !== $confirmLabel) : ?>
    <button type="<?php echo $confirmType; ?>"
            <?php echo '' !== $confirmFormId ? 'form="' . $confirmFormId . '"' : ''; ?>
            class="sameday_button sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--confirm">
        <?php echo (string) $confirmLabel; ?>
    </button>
<?php endif; ?>
