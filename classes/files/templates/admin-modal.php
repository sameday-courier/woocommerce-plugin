<?php

declare(strict_types=1);

/**
 * @var string $id
 * @var string $classes
 * @var string $title
 * @var string $subtitle
 * @var string $cancelLabel
 * @var string $body
 * @var string $iconHtml
 * @var string $footerHtml
 */
?>
<div id="<?php echo $id; ?>"
     class="<?php echo $classes; ?>"
     hidden
     data-sameday-modal>
    <div class="sameday-bulk-awb-modal__backdrop" data-sameday-modal-close></div>
    <div class="sameday-bulk-awb-modal__dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="<?php echo $id; ?>-title">
        <div class="sameday-bulk-awb-modal__header">
            <div class="sameday-bulk-awb-modal__heading">
                <div class="sameday-bulk-awb-modal__icon" aria-hidden="true">
                    <?php echo $iconHtml; ?>
                </div>
                <div class="sameday-bulk-awb-modal__titles">
                    <h2 id="<?php echo $id; ?>-title"><?php echo $title; ?></h2>
                    <?php if ('' !== $subtitle) : ?>
                        <p><?php echo $subtitle; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <button type="button"
                    class="sameday-bulk-awb-modal__close"
                    data-sameday-modal-close
                    aria-label="<?php echo $cancelLabel; ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="sameday-bulk-awb-modal__body">
            <?php echo $body; ?>
        </div>
        <div class="sameday-bulk-awb-modal__footer">
            <?php echo $footerHtml; ?>
        </div>
    </div>
</div>
