(function ($) {
    'use strict';

    var BUTTON_SELECTOR = '.sameday-show-awb-pdf';
    var FORM_ID = '#showAsPdf';

    function openAwbPdf(orderId) {
        window.SamedayOrdersListAwbForm.submitOrderForm($(FORM_ID), orderId, {
            target: '_blank',
            missingFormMessage: 'Unable to open AWB PDF. Please refresh the page and try again.'
        });
    }

    $(function () {
        $(document).on('click', BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            event.stopPropagation();

            openAwbPdf(String($(this).attr('data-order-id') || '').trim());
        });
    });
}(jQuery));
