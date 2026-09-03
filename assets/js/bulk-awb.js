(function ($) {
    'use strict';

    var BUTTON_SELECTOR = '[data-sameday-bulk-awb-open]';
    var MODAL_SELECTOR = '[data-sameday-bulk-awb-modal]';
    var config = window.samedayBulkAwb || {};

    var modalController = window.SamedayModalCore.create({
        modalSelector: MODAL_SELECTOR,
        closeSelector: '[data-sameday-bulk-awb-close]',
        openSelector: BUTTON_SELECTOR,
        openDataKey: 'samedayBulkAwbOpen',
        bindOpen: false,
        canClose: function ($modal) {
            return !isBusyStep($modal);
        }
    });

    function getSelectedOrders() {
        var orders = [];
        var ids = [];
        var selectors = [
            '#the-list input[name="id[]"]:checked',
            '#the-list input[name="post[]"]:checked'
        ];

        $(selectors.join(',')).each(function () {
            var $checkbox = $(this);
            var value = String($checkbox.val() || '').trim();
            if (!value || ids.indexOf(value) !== -1) {
                return;
            }

            ids.push(value);
            orders.push({
                id: value,
                currencyWarning: String(
                    $checkbox
                        .closest('tr')
                        .find('[data-sameday-currency-warning]')
                        .attr('data-sameday-currency-warning') || ''
                )
            });
        });

        return orders;
    }

    function getSelectedOrderIds() {
        return getSelectedOrders().map(function (order) {
            return order.id;
        });
    }

    function hasCurrencyWarnings(orders) {
        return orders.some(function (order) {
            return order.currencyWarning !== '';
        });
    }

    function syncButtonLayout($button, $anchor) {
        $button.css({
            display: $anchor.css('display'),
            position: $anchor.css('position'),
            top: $anchor.css('top'),
            verticalAlign: $anchor.css('vertical-align'),
            marginTop: $anchor.css('margin-top'),
            marginBottom: $anchor.css('margin-bottom'),
            paddingTop: $anchor.css('padding-top'),
            paddingBottom: $anchor.css('padding-bottom'),
            minHeight: $anchor.css('min-height'),
            lineHeight: $anchor.css('line-height'),
            fontSize: $anchor.css('font-size'),
            fontWeight: $anchor.css('font-weight'),
            boxSizing: $anchor.css('box-sizing')
        });
    }

    function placeButtons() {
        var $buttons = $(BUTTON_SELECTOR);
        if (!$buttons.length) {
            return;
        }

        var $anchor = $('.wrap > h1.wp-heading-inline').nextAll('a.page-title-action').not(BUTTON_SELECTOR).first();
        if (!$anchor.length) {
            $anchor = $('.wrap > a.page-title-action').not(BUTTON_SELECTOR).first();
        }
        if (!$anchor.length) {
            return;
        }

        var $insertAfter = $anchor;
        $buttons.each(function () {
            var $button = $(this);
            $button.insertAfter($insertAfter);
            syncButtonLayout($button, $anchor);
            $insertAfter = $button;
        });
    }

    function getModal($from) {
        return modalController.getModal($from);
    }

    function i18n(key, fallback) {
        return (config.i18n && config.i18n[key]) ? config.i18n[key] : fallback;
    }

    function supportsCurrencyWarning($modal) {
        return $modal.find('[data-sameday-bulk-awb-currency-confirm]').length > 0;
    }

    function isCurrencyConfirmed($modal) {
        if (!supportsCurrencyWarning($modal)) {
            return true;
        }

        var $confirm = $modal.find('[data-sameday-bulk-awb-currency-confirm]');

        if ($confirm.prop('hidden')) {
            return true;
        }

        return $modal.find('[data-sameday-bulk-awb-currency-agree]').is(':checked');
    }

    function updateConfirmState($modal) {
        var hasOrders = getSelectedOrderIds().length > 0;
        var agreed = $modal.find('[data-sameday-bulk-awb-agree]').is(':checked');
        $modal
            .find('[data-sameday-bulk-awb-confirm]')
            .prop('disabled', !(hasOrders && agreed && isCurrencyConfirmed($modal)));
    }

    function toggleCurrencyConfirm($modal, orders) {
        if (!supportsCurrencyWarning($modal)) {
            return;
        }

        $modal.find('[data-sameday-bulk-awb-currency-agree]').prop('checked', false);
        $modal
            .find('[data-sameday-bulk-awb-currency-confirm]')
            .prop('hidden', !hasCurrencyWarnings(orders));
    }

    function renderOrders($modal, orders) {
        var $list = $modal.find('[data-sameday-bulk-awb-order-list]');
        var $empty = $modal.find('[data-sameday-bulk-awb-empty]');
        var orderLabel = i18n('order', 'Order');
        var showCurrencyWarning = supportsCurrencyWarning($modal);

        $list.empty();
        $modal.find('[data-sameday-bulk-awb-order-count]').text(String(orders.length));

        if (!orders.length) {
            $list.hide();
            $empty.prop('hidden', false);
            return;
        }

        $empty.prop('hidden', true);
        $list.show();

        orders.forEach(function (order) {
            var $item = $('<li/>');
            var $orderId = $('<span/>')
                .addClass('sameday-bulk-awb-modal__order-id')
                .text(orderLabel + ' #' + order.id);

            if (showCurrencyWarning && order.currencyWarning) {
                $orderId.addClass('sameday-bulk-awb-modal__order-id--warning');
                $item.append($orderId);
                $item.append(
                    $('<span/>')
                        .addClass('sameday-bulk-awb-modal__order-warning')
                        .text('! ' + order.currencyWarning)
                );
            } else {
                $item.append($orderId);
            }

            $list.append($item);
        });
    }

    function setHeaderIcon($modal, variant) {
        var $icon = $modal.find('[data-sameday-bulk-awb-header-icon]');
        $icon.toggleClass('is-success', variant === 'success');
    }

    function setStep($modal, step) {
        $modal.find('[data-sameday-bulk-awb-step]').prop('hidden', true);
        $modal.find('[data-sameday-bulk-awb-step="' + step + '"]').prop('hidden', false);

        var $footer = $modal.find('[data-sameday-bulk-awb-footer]');
        var $confirm = $modal.find('[data-sameday-bulk-awb-confirm]');
        var $cancel = $modal.find('[data-sameday-bulk-awb-cancel]');

        if (step === 'confirm') {
            $footer.prop('hidden', false);
            $confirm.prop('hidden', false);
            $cancel.prop('hidden', false);
            setHeaderIcon($modal, 'package');
            $modal.find('[data-sameday-bulk-awb-title]').text($modal.data('confirm-title') || '');
            $modal.find('[data-sameday-bulk-awb-subtitle]').text($modal.data('confirm-subtitle') || '');
            return;
        }

        $footer.prop('hidden', true);
        $confirm.prop('hidden', true);
        $cancel.prop('hidden', true);

        if (step === 'starting') {
            setHeaderIcon($modal, 'package');
            $modal.find('[data-sameday-bulk-awb-title]').text($modal.data('starting-title') || '');
            $modal.find('[data-sameday-bulk-awb-subtitle]').text('');
            return;
        }

        if (step === 'progress') {
            setHeaderIcon($modal, 'package');
            $modal.find('[data-sameday-bulk-awb-title]').text($modal.data('progress-title') || '');
            $modal.find('[data-sameday-bulk-awb-subtitle]').text('');
            return;
        }

        setHeaderIcon($modal, 'success');
        $modal.find('[data-sameday-bulk-awb-title]').text($modal.data('report-title') || '');
        $modal.find('[data-sameday-bulk-awb-subtitle]').text($modal.data('report-subtitle') || '');
    }

    function isBusyStep($modal) {
        return $modal.find('[data-sameday-bulk-awb-step="starting"]:not([hidden]), [data-sameday-bulk-awb-step="progress"]:not([hidden])').length > 0;
    }

    function updateProgress($modal, processed, total, currentItemId) {
        var percent = total > 0 ? Math.round((processed / total) * 100) : 0;
        var text = i18n('processing', 'Processing order #%1$s (%2$s/%3$s)')
            .replace('%1$s', String(currentItemId || '-'))
            .replace('%2$s', String(processed))
            .replace('%3$s', String(total));

        $modal.find('[data-sameday-bulk-awb-progress-text]').text(text);
        $modal.find('[data-sameday-bulk-awb-progress-percent]').text(percent + '%');
        $modal.find('[data-sameday-bulk-awb-progress-fill]').css('width', percent + '%');
        $modal.find('[data-sameday-bulk-awb-progress-bar]').attr('aria-valuenow', String(percent));
    }

    function statusIcon(status) {
        if (status === 'success') {
            return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }

        return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
    }

    function normalizeStatus(status) {
        return status === 'success' ? 'success' : 'error';
    }

    function applyLogFilter($modal) {
        var filter = String($modal.find('[data-sameday-bulk-awb-log-filter]').val() || 'all');
        $modal.find('[data-sameday-bulk-awb-report-list] .sameday-bulk-awb-modal__log-item').each(function () {
            var status = String(this.getAttribute('data-status') || '');
            var visible = filter === 'all' || filter === status;
            $(this).toggle(visible);
        });
    }

    function renderReport($modal, data) {
        var $list = $modal.find('[data-sameday-bulk-awb-report-list]');
        var items = data.items || data.orders || [];
        var successCount = data.successCount || 0;
        var errorCount = data.errorCount || 0;
        var total = data.total || items.length || 0;

        $modal.find('[data-sameday-bulk-awb-stat-success]').text(String(successCount));
        $modal.find('[data-sameday-bulk-awb-stat-error]').text(String(errorCount));
        $modal.find('[data-sameday-bulk-awb-stat-total]').text(String(total));
        $modal.find('[data-sameday-bulk-awb-report-percent]').text('100%');
        $modal.find('[data-sameday-bulk-awb-log-filter]').val('all');
        $list.empty();

        items.forEach(function (entry) {
            var status = normalizeStatus(entry.status);
            var message = entry.message || '';
            var itemId = entry.itemId || entry.orderId;
            var line = '#' + itemId + ' ' + message;
            var $item = $('<li/>')
                .addClass('sameday-bulk-awb-modal__log-item sameday-bulk-awb-modal__log-item--' + status)
                .attr('data-status', status);

            var $main = $('<div/>').addClass('sameday-bulk-awb-modal__log-main');
            $main.append($('<span/>').addClass('sameday-bulk-awb-modal__log-status').html(statusIcon(status)));
            $main.append($('<span/>').addClass('sameday-bulk-awb-modal__log-text').text(line));
            $item.append($main);

            if (status === 'success' && entry.awbNumber) {
                $item.append(
                    $('<span/>')
                        .addClass('sameday-bulk-awb-modal__awb-badge')
                        .attr('title', entry.awbNumber)
                        .text(entry.awbNumber)
                );
            }

            $list.append($item);
        });

        applyLogFilter($modal);
    }

    function openModal(modalId) {
        var $modal = $('#' + modalId);
        if (!$modal.length) {
            return;
        }

        var orders = getSelectedOrders();

        modalController.closeAllModals();
        renderOrders($modal, orders);
        toggleCurrencyConfirm($modal, orders);
        $modal.find('[data-sameday-bulk-awb-agree]').prop('checked', false);
        updateConfirmState($modal);
        setStep($modal, 'confirm');
        modalController.openModal(modalId);
    }

    function getModeName($modal) {
        return String($modal.attr('data-sameday-bulk-awb-mode') || '');
    }

    function getModeConfig($modal) {
        var modes = config.modes || {};
        return modes[getModeName($modal)] || null;
    }

    function resolveCurrentItemId(data) {
        return data.currentItemId
            || data.currentOrderId
            || (data.lastResult && (data.lastResult.itemId || data.lastResult.orderId));
    }

    function startBulk($modal) {
        var orderIds = getSelectedOrderIds();
        var modeConfig = getModeConfig($modal);
        if (!orderIds.length || !modeConfig) {
            return;
        }

        setStep($modal, 'starting');

        window.SamedayRecursiveJob.run({
            ajaxUrl: config.ajaxUrl,
            startAction: modeConfig.startAction,
            startNonce: modeConfig.startNonce,
            nextAction: modeConfig.nextAction,
            nextNonce: modeConfig.nextNonce,
            startData: {
                'samedaycourier-order-ids': orderIds
            },
            genericError: i18n('genericError', 'Something went wrong.'),
            onStart: function (data) {
                setStep($modal, 'progress');
                updateProgress($modal, 0, data.total || orderIds.length, orderIds[0]);
            },
            onProgress: function (data) {
                updateProgress(
                    $modal,
                    data.processed || 0,
                    data.total || 0,
                    resolveCurrentItemId(data)
                );
            },
            onComplete: function (data) {
                updateProgress(
                    $modal,
                    data.processed || data.total || 0,
                    data.total || 0,
                    resolveCurrentItemId(data)
                );
                renderReport($modal, data);
                setStep($modal, 'report');
            },
            onError: function (message) {
                renderReport($modal, {
                    successCount: 0,
                    errorCount: orderIds.length,
                    total: orderIds.length,
                    items: orderIds.map(function (orderId) {
                        return {
                            itemId: orderId,
                            status: 'error',
                            message: message
                        };
                    })
                });
                setStep($modal, 'report');
            }
        });
    }

    $(function () {
        placeButtons();
        modalController.bindEvents();

        $(document).on('click', BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            openModal($(this).data('samedayBulkAwbOpen'));
        });

        $(document).on(
            'change',
            '[data-sameday-bulk-awb-agree], [data-sameday-bulk-awb-currency-agree]',
            function () {
                updateConfirmState(getModal($(this)));
            }
        );

        $(document).on('change', '[data-sameday-bulk-awb-log-filter]', function () {
            applyLogFilter(getModal($(this)));
        });

        $(document).on('click', '[data-sameday-bulk-awb-confirm]', function (event) {
            event.preventDefault();
            if ($(this).is(':disabled')) {
                return;
            }

            var $modal = getModal($(this));
            if (!getModeConfig($modal)) {
                modalController.closeModal($modal);
                return;
            }

            startBulk($modal);
        });
    });
}(jQuery));
