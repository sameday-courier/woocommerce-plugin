<?php

declare(strict_types=1);

/**
 * @var string $buttonId
 * @var string $modalId
 * @var string $mode
 * @var string $buttonLabel
 * @var string $buttonIconHtml
 * @var string $headerIconHtml
 * @var string $title
 * @var string $subtitle
 * @var string $cancel
 * @var string $confirm
 * @var string $summaryHtml
 * @var string $emptySelection
 * @var string $disclaimer
 * @var string $startingHint
 * @var string $progressLabel
 * @var string $successLabel
 * @var string $successHint
 * @var string $failedLabel
 * @var string $failedHint
 * @var string $ordersLabel
 * @var string $ordersHint
 * @var string $logsTitle
 * @var string $filterAll
 * @var string $filterSuccess
 * @var string $filterFailed
 * @var string $progressTitle
 * @var string $startingTitle
 * @var string $reportTitle
 * @var string $reportSubtitle
 * @var string|null $currencyConfirm
 */
?>
<a id="<?php echo $buttonId; ?>"
   href="#"
   class="page-title-action sameday_button"
   style="display:none;"
   role="button"
   data-sameday-bulk-awb-open="<?php echo $modalId; ?>"><?php echo $buttonIconHtml; ?><?php echo $buttonLabel; ?></a>
<div id="<?php echo $modalId; ?>"
     class="sameday-bulk-awb-modal"
     hidden
     data-sameday-bulk-awb-modal
     data-sameday-bulk-awb-mode="<?php echo $mode; ?>"
     data-progress-title="<?php echo $progressTitle; ?>"
     data-starting-title="<?php echo $startingTitle; ?>"
     data-report-title="<?php echo $reportTitle; ?>"
     data-report-subtitle="<?php echo $reportSubtitle; ?>"
     data-confirm-title="<?php echo $title; ?>"
     data-confirm-subtitle="<?php echo $subtitle; ?>">
    <div class="sameday-bulk-awb-modal__backdrop" data-sameday-bulk-awb-close></div>
    <div class="sameday-bulk-awb-modal__dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="<?php echo $modalId; ?>-title">
        <div class="sameday-bulk-awb-modal__header">
            <div class="sameday-bulk-awb-modal__heading">
                <div class="sameday-bulk-awb-modal__icon"
                     data-sameday-bulk-awb-header-icon
                     aria-hidden="true">
                    <?php echo $headerIconHtml; ?>
                </div>
                <div class="sameday-bulk-awb-modal__titles">
                    <h2 id="<?php echo $modalId; ?>-title" data-sameday-bulk-awb-title><?php echo $title; ?></h2>
                    <p data-sameday-bulk-awb-subtitle><?php echo $subtitle; ?></p>
                </div>
            </div>
            <button type="button"
                    class="sameday-bulk-awb-modal__close"
                    data-sameday-bulk-awb-close
                    aria-label="<?php echo $cancel; ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="sameday-bulk-awb-modal__body">
            <div data-sameday-bulk-awb-step="confirm">
                <p class="sameday-bulk-awb-modal__summary"><?php echo $summaryHtml; ?></p>
                <ul class="sameday-bulk-awb-modal__orders"
                    data-sameday-bulk-awb-order-list></ul>
                <p class="sameday-bulk-awb-modal__empty"
                   data-sameday-bulk-awb-empty
                   hidden><?php echo $emptySelection; ?></p>
                <?php if (null !== $currencyConfirm) : ?>
                    <label class="sameday-bulk-awb-modal__disclaimer sameday-bulk-awb-modal__disclaimer--currency"
                           data-sameday-bulk-awb-currency-confirm
                           hidden>
                        <input type="checkbox"
                               class="sameday-modal-checkbox"
                               data-sameday-bulk-awb-currency-agree>
                        <span><?php echo $currencyConfirm; ?></span>
                    </label>
                <?php endif; ?>
                <label class="sameday-bulk-awb-modal__disclaimer">
                    <input type="checkbox"
                           class="sameday-modal-checkbox"
                           data-sameday-bulk-awb-agree>
                    <span><?php echo $disclaimer; ?></span>
                </label>
            </div>
            <div data-sameday-bulk-awb-step="starting" hidden>
                <div class="sameday-bulk-awb-modal__starting">
                    <div class="sameday-bulk-awb-modal__spinner" aria-hidden="true"></div>
                    <p class="sameday-bulk-awb-modal__starting-text"
                       data-sameday-bulk-awb-starting-text><?php echo $startingHint; ?></p>
                </div>
            </div>
            <div data-sameday-bulk-awb-step="progress" hidden>
                <div class="sameday-bulk-awb-modal__progress-block">
                    <div class="sameday-bulk-awb-modal__progress-meta">
                        <span data-sameday-bulk-awb-progress-label><?php echo $progressLabel; ?></span>
                        <strong data-sameday-bulk-awb-progress-percent>0%</strong>
                    </div>
                    <div class="sameday-bulk-awb-modal__progress"
                         role="progressbar"
                         aria-valuemin="0"
                         aria-valuemax="100"
                         aria-valuenow="0"
                         data-sameday-bulk-awb-progress-bar>
                        <div class="sameday-bulk-awb-modal__progress-fill"
                             data-sameday-bulk-awb-progress-fill></div>
                    </div>
                    <p class="sameday-bulk-awb-modal__progress-hint"
                       data-sameday-bulk-awb-progress-text></p>
                </div>
            </div>
            <div data-sameday-bulk-awb-step="report" hidden>
                <div class="sameday-bulk-awb-modal__progress-block">
                    <div class="sameday-bulk-awb-modal__progress-meta">
                        <span><?php echo $progressLabel; ?></span>
                        <strong data-sameday-bulk-awb-report-percent>100%</strong>
                    </div>
                    <div class="sameday-bulk-awb-modal__progress"
                         role="progressbar"
                         aria-valuemin="0"
                         aria-valuemax="100"
                         aria-valuenow="100">
                        <div class="sameday-bulk-awb-modal__progress-fill" style="width:100%"></div>
                    </div>
                </div>
                <div class="sameday-bulk-awb-modal__stats">
                    <div class="sameday-bulk-awb-modal__stat sameday-bulk-awb-modal__stat--success">
                        <div class="sameday-bulk-awb-modal__stat-label">
                            <span class="sameday-bulk-awb-modal__stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
                                    <path d="M20 6 9 17l-5-5"
                                          stroke="currentColor"
                                          stroke-width="2.2"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span><?php echo $successLabel; ?></span>
                        </div>
                        <div class="sameday-bulk-awb-modal__stat-value"
                             data-sameday-bulk-awb-stat-success>0</div>
                        <div class="sameday-bulk-awb-modal__stat-hint"><?php echo $successHint; ?></div>
                    </div>
                    <div class="sameday-bulk-awb-modal__stat sameday-bulk-awb-modal__stat--error">
                        <div class="sameday-bulk-awb-modal__stat-label">
                            <span class="sameday-bulk-awb-modal__stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
                                    <path d="M18 6 6 18M6 6l12 12"
                                          stroke="currentColor"
                                          stroke-width="2.2"
                                          stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span><?php echo $failedLabel; ?></span>
                        </div>
                        <div class="sameday-bulk-awb-modal__stat-value"
                             data-sameday-bulk-awb-stat-error>0</div>
                        <div class="sameday-bulk-awb-modal__stat-hint"><?php echo $failedHint; ?></div>
                    </div>
                    <div class="sameday-bulk-awb-modal__stat sameday-bulk-awb-modal__stat--total">
                        <div class="sameday-bulk-awb-modal__stat-label">
                            <span class="sameday-bulk-awb-modal__stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
                                    <path d="M12 3.2 3.8 7.2v9.6L12 20.8l8.2-4V7.2L12 3.2Z"
                                          stroke="currentColor"
                                          stroke-width="1.7"
                                          stroke-linejoin="round"/>
                                    <path d="M12 12.4 3.8 7.2M12 12.4l8.2-5.2M12 12.4V20.8"
                                          stroke="currentColor"
                                          stroke-width="1.7"
                                          stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span><?php echo $ordersLabel; ?></span>
                        </div>
                        <div class="sameday-bulk-awb-modal__stat-value"
                             data-sameday-bulk-awb-stat-total>0</div>
                        <div class="sameday-bulk-awb-modal__stat-hint"><?php echo $ordersHint; ?></div>
                    </div>
                </div>
                <div class="sameday-bulk-awb-modal__logs">
                    <div class="sameday-bulk-awb-modal__logs-header">
                        <div class="sameday-bulk-awb-modal__logs-title">
                            <span class="sameday-bulk-awb-modal__logs-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none">
                                    <path d="M8 7h8M8 12h8M8 17h5"
                                          stroke="currentColor"
                                          stroke-width="1.8"
                                          stroke-linecap="round"/>
                                    <rect x="4" y="3" width="16" height="18" rx="2.5"
                                          stroke="currentColor"
                                          stroke-width="1.8"/>
                                </svg>
                            </span>
                            <span><?php echo $logsTitle; ?></span>
                        </div>
                        <label class="sameday-bulk-awb-modal__logs-filter">
                            <select data-sameday-bulk-awb-log-filter>
                                <option value="all"><?php echo $filterAll; ?></option>
                                <option value="success"><?php echo $filterSuccess; ?></option>
                                <option value="error"><?php echo $filterFailed; ?></option>
                            </select>
                        </label>
                    </div>
                    <ul class="sameday-bulk-awb-modal__log-list"
                        data-sameday-bulk-awb-report-list></ul>
                </div>
            </div>
        </div>
        <div class="sameday-bulk-awb-modal__footer" data-sameday-bulk-awb-footer>
            <button type="button"
                    class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--cancel"
                    data-sameday-bulk-awb-close
                    data-sameday-bulk-awb-cancel>
                <?php echo $cancel; ?>
            </button>
            <button type="button"
                    class="sameday_button sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--confirm"
                    data-sameday-bulk-awb-confirm
                    disabled>
                <?php echo $confirm; ?>
            </button>
        </div>
    </div>
</div>
