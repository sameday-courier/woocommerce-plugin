(function ($, window) {
    'use strict';

    var DEFAULT_ERROR_MESSAGE = 'Unable to complete the action. Please refresh and try again.';

    /**
     * Submit a hidden admin-post AWB form for a single order on the orders list.
     *
     * @param {jQuery} $form
     * @param {string} orderId
     * @param {Object} [options]
     * @param {string} [options.target]
     * @param {string} [options.missingFormMessage]
     * @param {function(string): void} [options.onError]
     * @returns {boolean}
     */
    function submitOrderForm($form, orderId, options) {
        options = options || {};

        if (!$form.length || !orderId) {
            var message = options.missingFormMessage || DEFAULT_ERROR_MESSAGE;

            if (typeof options.onError === 'function') {
                options.onError(message);
            } else if (window.SamedayAdminModalConfirm) {
                window.SamedayAdminModalConfirm.showPageNotice(message, 'error');
            }

            return false;
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

        return true;
    }

    window.SamedayOrdersListAwbForm = {
        submitOrderForm: submitOrderForm
    };
}(jQuery, window));
