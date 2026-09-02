/**
 * Orders list: AWB PDF + remove actions (same admin-post endpoints as order edit).
 */
(function ($) {
    'use strict';

    var PDF_BUTTON_SELECTOR = '.sameday-show-awb-pdf';
    var REMOVE_BUTTON_SELECTOR = '.sameday-remove-awb';
    var PDF_FORM_ID = '#showAsPdf';
    var REMOVE_FORM_ID = '#removeAwb';
    var REMOVE_MODAL_ID = 'sameday-remove-single-awb-modal';
    var REMOVE_AGREE_SELECTOR = '[data-sameday-remove-awb-agree]';
    var REMOVE_CONFIRM_SELECTOR = '#' + REMOVE_MODAL_ID + ' .sameday-bulk-awb-modal__btn--confirm';

    var pendingRemove = {
        orderId: '',
        awbNumber: ''
    };

    function submitOrderForm($form, orderId, options) {
        options = options || {};

        if (!$form.length || !orderId) {
            window.alert(options.missingFormMessage || 'Unable to complete the action. Please refresh and try again.');
            return;
        }

        $form.find('input[name="order-id"]').remove();
        $('<input>', {
            type: 'hidden',
            name: 'order-id',
            value: orderId
        }).appendTo($form);

        if (options.target) {
            $form.attr('target', options.target);
        } else {
            $form.removeAttr('target');
        }

        $form.trigger('submit');
    }

    function openAwbPdf(orderId) {
        submitOrderForm($(PDF_FORM_ID), orderId, {
            target: '_blank',
            missingFormMessage: 'Unable to open AWB PDF. Please refresh the page and try again.'
        });
    }

    function getRemoveModal() {
        return $('#' + REMOVE_MODAL_ID);
    }

    function syncRemoveConfirmState() {
        var $modal = getRemoveModal();
        var agreed = $modal.find(REMOVE_AGREE_SELECTOR).is(':checked');
        $modal.find(REMOVE_CONFIRM_SELECTOR).prop('disabled', !agreed);
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
        $modal.find(REMOVE_AGREE_SELECTOR).prop('checked', false);
        syncRemoveConfirmState();

        if (window.SamedayAdminModal && typeof window.SamedayAdminModal.open === 'function') {
            window.SamedayAdminModal.open(REMOVE_MODAL_ID);
            return;
        }

        $modal.prop('hidden', false);
        $('body').addClass('sameday-bulk-awb-modal-open');
    }

    function confirmRemoveAwb() {
        if (!pendingRemove.orderId) {
            return;
        }

        if (!getRemoveModal().find(REMOVE_AGREE_SELECTOR).is(':checked')) {
            return;
        }

        if (window.SamedayAdminModal && typeof window.SamedayAdminModal.close === 'function') {
            window.SamedayAdminModal.close(getRemoveModal());
        } else {
            getRemoveModal().prop('hidden', true);
            $('body').removeClass('sameday-bulk-awb-modal-open');
        }

        submitOrderForm($(REMOVE_FORM_ID), pendingRemove.orderId, {
            missingFormMessage: 'Unable to remove AWB. Please refresh the page and try again.'
        });
    }

    $(function () {
        $(document).on('click', PDF_BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            event.stopPropagation();

            openAwbPdf(String($(this).attr('data-order-id') || '').trim());
        });

        $(document).on('click', REMOVE_BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            event.stopPropagation();

            var $button = $(this);
            openRemoveModal(
                String($button.attr('data-order-id') || '').trim(),
                String($button.attr('data-awb-number') || '').trim()
            );
        });

        $(document).on('change', REMOVE_AGREE_SELECTOR, syncRemoveConfirmState);

        $(document).on('click', REMOVE_CONFIRM_SELECTOR, function (event) {
            event.preventDefault();
            confirmRemoveAwb();
        });
    });
}(jQuery));
