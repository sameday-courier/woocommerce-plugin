(function ($) {
    'use strict';

    var BUTTON_SELECTOR = '.sameday-remove-awb';
    var FORM_ID = '#removeAwb';
    var MODAL_ID = 'sameday-remove-single-awb-modal';
    var AGREE_SELECTOR = '[data-sameday-remove-awb-agree]';
    var DEFAULT_ERROR_MESSAGE = 'Unable to remove AWB. Please refresh the page and try again.';
    var BUSY_MESSAGE = 'Removing AWB…';

    var pendingOrderId = '';

    var modalConfirm = window.SamedayAdminModalConfirm.create({
        modalId: MODAL_ID,
        agreeSelector: AGREE_SELECTOR,
        busyMessage: BUSY_MESSAGE,
        onConfirm: function (modalApi) {
            if (!pendingOrderId) {
                return;
            }

            modalApi.setBusy(true);

            var submitted = window.SamedayOrdersListAwbForm.submitOrderForm(
                $(FORM_ID),
                pendingOrderId,
                {
                    missingFormMessage: DEFAULT_ERROR_MESSAGE,
                    onError: function (message) {
                        modalApi.setBusy(false);
                        modalApi.showNotice(message);
                    }
                }
            );

            if (!submitted) {
                modalApi.setBusy(false);
            }
        }
    });

    function openRemoveModal(orderId, awbNumber) {
        var $modal = $('#' + MODAL_ID);

        if (!$modal.length || !orderId) {
            window.SamedayAdminModalConfirm.showPageNotice(DEFAULT_ERROR_MESSAGE, 'error');
            return;
        }

        pendingOrderId = orderId;

        $modal.find('[data-sameday-remove-order-id]').text(orderId);
        $modal.find('[data-sameday-remove-awb-number]').text(awbNumber || '—');
        $modal.find(AGREE_SELECTOR).prop('checked', false);
        modalConfirm.syncConfirmState();
        modalConfirm.open();
    }

    $(function () {
        modalConfirm.bindEvents();

        $(document).on('click', BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            event.stopPropagation();

            var $button = $(this);
            openRemoveModal(
                String($button.attr('data-order-id') || '').trim(),
                String($button.attr('data-awb-number') || '').trim()
            );
        });
    });
}(jQuery));
