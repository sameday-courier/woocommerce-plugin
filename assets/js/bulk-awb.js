(function ($) {
    'use strict';

    var BUTTON_SELECTOR = '[data-sameday-bulk-awb-open]';
    var MODAL_SELECTOR = '[data-sameday-bulk-awb-modal]';
    var config = window.samedayBulkAwb || {};

    function getSelectedOrderIds() {
        var ids = [];
        var selectors = [
            '#the-list input[name="id[]"]:checked',
            '#the-list input[name="post[]"]:checked'
        ];

        $(selectors.join(',')).each(function () {
            var value = String($(this).val() || '').trim();
            if (value && ids.indexOf(value) === -1) {
                ids.push(value);
            }
        });

        return ids;
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
        if ($from.is(MODAL_SELECTOR)) {
            return $from;
        }

        return $from.closest(MODAL_SELECTOR);
    }

    function i18n(key, fallback) {
        return (config.i18n && config.i18n[key]) ? config.i18n[key] : fallback;
    }

    function updateConfirmState($modal) {
        var hasOrders = getSelectedOrderIds().length > 0;
        var agreed = $modal.find('[data-sameday-bulk-awb-agree]').is(':checked');
        $modal.find('[data-sameday-bulk-awb-confirm]').prop('disabled', !(hasOrders && agreed));
    }

    function renderOrders($modal, ids) {
        var $list = $modal.find('[data-sameday-bulk-awb-order-list]');
        var $empty = $modal.find('[data-sameday-bulk-awb-empty]');
        var orderLabel = i18n('order', 'Order');

        $list.empty();
        $modal.find('[data-sameday-bulk-awb-order-count]').text(String(ids.length));

        if (!ids.length) {
            $list.hide();
            $empty.prop('hidden', false);
            return;
        }

        $empty.prop('hidden', true);
        $list.show();

        ids.forEach(function (id) {
            $list.append($('<li/>').text(orderLabel + ' #' + id));
        });
    }

    function setHeaderIcon($modal, variant) {
        var $icon = $modal.find('[data-sameday-bulk-awb-header-icon]');
        var isSuccess = variant === 'success';

        $icon.toggleClass('is-success', isSuccess);
        $icon.find('.sameday-bulk-awb-modal__icon-svg--package').prop('hidden', isSuccess);
        $icon.find('.sameday-bulk-awb-modal__icon-svg--success').prop('hidden', !isSuccess);
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

    function updateProgress($modal, processed, total, currentOrderId) {
        var percent = total > 0 ? Math.round((processed / total) * 100) : 0;
        var text = i18n('processing', 'Processing order #%1$s (%2$s/%3$s)')
            .replace('%1$s', String(currentOrderId || '-'))
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
            var status = String($(this).data('status') || '');
            var visible = filter === 'all' || filter === status;
            $(this).prop('hidden', !visible);
        });
    }

    function renderReport($modal, data) {
        var $list = $modal.find('[data-sameday-bulk-awb-report-list]');
        var orders = data.orders || [];
        var successCount = data.successCount || 0;
        var errorCount = data.errorCount || 0;
        var total = data.total || orders.length || 0;

        $modal.find('[data-sameday-bulk-awb-stat-success]').text(String(successCount));
        $modal.find('[data-sameday-bulk-awb-stat-error]').text(String(errorCount));
        $modal.find('[data-sameday-bulk-awb-stat-total]').text(String(total));
        $modal.find('[data-sameday-bulk-awb-report-percent]').text('100%');
        $modal.find('[data-sameday-bulk-awb-log-filter]').val('all');
        $list.empty();

        orders.forEach(function (entry) {
            var status = normalizeStatus(entry.status);
            var message = entry.message || '';
            var line = '#' + entry.orderId + ' ' + message;
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

        closeAllModals();
        renderOrders($modal, getSelectedOrderIds());
        $modal.find('[data-sameday-bulk-awb-agree]').prop('checked', false);
        updateConfirmState($modal);
        setStep($modal, 'confirm');

        $modal.prop('hidden', false);
        $('body').addClass('sameday-bulk-awb-modal-open');
    }

    function closeModal($modal) {
        if (!$modal || !$modal.length) {
            return;
        }

        $modal.prop('hidden', true);
        if (!$(MODAL_SELECTOR).filter(':not([hidden])').length) {
            $('body').removeClass('sameday-bulk-awb-modal-open');
        }
    }

    function closeAllModals() {
        $(MODAL_SELECTOR).prop('hidden', true);
        $('body').removeClass('sameday-bulk-awb-modal-open');
    }

    function request(action, nonce, data) {
        return $.ajax({
            url: config.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: $.extend({
                action: action,
                _wpnonce: nonce
            }, data)
        });
    }

    function getModeName($modal) {
        return String($modal.attr('data-sameday-bulk-awb-mode') || '');
    }

    function getModeConfig($modal) {
        var modes = config.modes || {};
        return modes[getModeName($modal)] || null;
    }

    function processNext($modal, jobId, totalHint, firstOrderId) {
        var modeConfig = getModeConfig($modal);
        if (!modeConfig) {
            throw new Error(i18n('genericError', 'Something went wrong.'));
        }

        setStep($modal, 'progress');
        if (typeof totalHint === 'number') {
            updateProgress($modal, 0, totalHint, firstOrderId);
        }

        return request(modeConfig.nextAction, modeConfig.nextNonce, { jobId: jobId })
            .then(function (response) {
                var data = (response && response.data) ? response.data : {};
                return handleNextPayload($modal, jobId, data);
            }, function (xhr) {
                var data = (xhr.responseJSON && xhr.responseJSON.data) ? xhr.responseJSON.data : null;
                if (data && data.done) {
                    return handleNextPayload($modal, jobId, data);
                }

                var message = i18n('genericError', 'Something went wrong.');
                if (data && data.message) {
                    message = data.message;
                }

                throw new Error(message);
            });
    }

    function handleNextPayload($modal, jobId, data) {
        if (data.done) {
            updateProgress($modal, data.processed || data.total || 0, data.total || 0, data.currentOrderId);
            renderReport($modal, data);
            setStep($modal, 'report');
            return null;
        }

        updateProgress(
            $modal,
            data.processed || 0,
            data.total || 0,
            data.currentOrderId || (data.lastResult && data.lastResult.orderId)
        );

        return processNext($modal, jobId);
    }

    function startBulk($modal) {
        var orderIds = getSelectedOrderIds();
        var modeConfig = getModeConfig($modal);
        if (!orderIds.length || !modeConfig) {
            return;
        }

        setStep($modal, 'starting');

        request(modeConfig.startAction, modeConfig.startNonce, {
            'samedaycourier-order-ids': orderIds
        }).then(function (response) {
            var data = (response && response.data) ? response.data : {};
            if (!data.jobId) {
                throw new Error(i18n('genericError', 'Something went wrong.'));
            }

            return processNext($modal, data.jobId, data.total || orderIds.length, orderIds[0]);
        }).fail(function (xhr) {
            var message = i18n('genericError', 'Something went wrong.');
            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                message = xhr.responseJSON.data.message;
            }

            renderReport($modal, {
                successCount: 0,
                errorCount: orderIds.length,
                total: orderIds.length,
                orders: orderIds.map(function (orderId) {
                    return {
                        orderId: orderId,
                        status: 'error',
                        message: message
                    };
                })
            });
            setStep($modal, 'report');
        });
    }

    $(function () {
        placeButtons();

        $(document).on('click', BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            openModal($(this).data('sameday-bulk-awb-open'));
        });

        $(document).on('click', '[data-sameday-bulk-awb-close]', function (event) {
            event.preventDefault();
            var $modal = getModal($(this));
            if (isBusyStep($modal)) {
                return;
            }
            closeModal($modal);
        });

        $(document).on('change', '[data-sameday-bulk-awb-agree]', function () {
            updateConfirmState(getModal($(this)));
        });

        $(document).on('change', '[data-sameday-bulk-awb-log-filter]', function () {
            applyLogFilter(getModal($(this)));
        });

        $(document).on('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            var $openModal = $(MODAL_SELECTOR).filter(':not([hidden])').last();
            if (!$openModal.length) {
                return;
            }

            if (isBusyStep($openModal)) {
                return;
            }

            closeModal($openModal);
        });

        $(document).on('click', '[data-sameday-bulk-awb-confirm]', function (event) {
            event.preventDefault();
            if ($(this).is(':disabled')) {
                return;
            }

            var $modal = getModal($(this));
            if (!getModeConfig($modal)) {
                closeModal($modal);
                return;
            }

            startBulk($modal);
        });
    });
}(jQuery));
