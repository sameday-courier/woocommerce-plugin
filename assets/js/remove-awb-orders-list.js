(function ($) {
    'use strict';

    var BUTTON_SELECTOR = '.sameday-remove-awb';
    var FORM_ID = '#removeAwb';
    var MODAL_ID = 'sameday-remove-single-awb-modal';
    var AGREE_SELECTOR = '[data-sameday-remove-awb-agree]';
    var CONFIRM_SELECTOR = '#' + MODAL_ID + ' .sameday-bulk-awb-modal__btn--confirm';

    var pendingRemove = {
        orderId: '',
        awbNumber: ''
    };

    function getRemoveModal() {
        return $('#' + MODAL_ID);
    }

    function syncRemoveConfirmState() {
        var $modal = getRemoveModal();
        var agreed = $modal.find(AGREE_SELECTOR).is(':checked');
        $modal.find(CONFIRM_SELECTOR).prop('disabled', !agreed);
    }

    function openRemoveModal(orderId, awbNumber) {
        var $modal = getRemoveModal();
        if (!$modal.length || !orderId) {
            window.alert('Unable to remove AWB. Please refresh the page and try again.');
            return;
        }

        pendingRemove.orderId = orderId;
        pendingRemove.awbNumber = awbNumber || '';

        $modal.find('[data-sameday-remove-order-id]').text(orderId);
        $modal.find('[data-sameday-remove-awb-number]').text(awbNumber || '—');
        $modal.find(AGREE_SELECTOR).prop('checked', false);
        syncRemoveConfirmState();

        if (window.SamedayAdminModal && typeof window.SamedayAdminModal.open === 'function') {
            window.SamedayAdminModal.open(MODAL_ID);
            return;
        }

        $modal.prop('hidden', false);
        $('body').addClass('sameday-bulk-awb-modal-open');
    }

    function closeRemoveModal() {
        var $modal = getRemoveModal();

        if (window.SamedayAdminModal && typeof window.SamedayAdminModal.close === 'function') {
            window.SamedayAdminModal.close($modal);
            return;
        }

        $modal.prop('hidden', true);
        $('body').removeClass('sameday-bulk-awb-modal-open');
    }

    function confirmRemoveAwb() {
        if (!pendingRemove.orderId) {
            return;
        }

        if (!getRemoveModal().find(AGREE_SELECTOR).is(':checked')) {
            return;
        }

        closeRemoveModal();

        window.SamedayOrdersListAwbForm.submitOrderForm($(FORM_ID), pendingRemove.orderId, {
            missingFormMessage: 'Unable to remove AWB. Please refresh the page and try again.'
        });
    }

    $(function () {
        $(document).on('click', BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            event.stopPropagation();

            var $button = $(this);
            openRemoveModal(
                String($button.attr('data-order-id') || '').trim(),
                String($button.attr('data-awb-number') || '').trim()
            );
        });

        $(document).on('change', AGREE_SELECTOR, syncRemoveConfirmState);

        $(document).on('click', CONFIRM_SELECTOR, function (event) {
            event.preventDefault();
            confirmRemoveAwb();
        });
    });
}(jQuery));
