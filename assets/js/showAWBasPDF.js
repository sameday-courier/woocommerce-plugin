/**
 * Orders list: AWB PDF + remove actions (same admin-post endpoints as order edit).
 */
(function ($) {
    'use strict';

    var PDF_BUTTON_SELECTOR = '.sameday-show-awb-pdf';
    var REMOVE_BUTTON_SELECTOR = '.sameday-remove-awb';
    var PDF_FORM_ID = '#showAsPdf';
    var REMOVE_FORM_ID = '#removeAwb';

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

    function removeAwb(orderId, awbNumber) {
        var label = awbNumber ? ('AWB ' + awbNumber) : 'this AWB';
        if (!window.confirm('Remove ' + label + ' from this order?')) {
            return;
        }

        submitOrderForm($(REMOVE_FORM_ID), orderId, {
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
            removeAwb(
                String($button.attr('data-order-id') || '').trim(),
                String($button.attr('data-awb-number') || '').trim()
            );
        });
    });
}(jQuery));
