(function ($, window) {
    'use strict';

    /**
     * Submit a hidden admin-post AWB form for a single order on the orders list.
     *
     * @param {jQuery} $form
     * @param {string} orderId
     * @param {Object} [options]
     * @param {string} [options.target]
     * @param {string} [options.missingFormMessage]
     */
    function submitOrderForm($form, orderId, options) {
        options = options || {};

        if (!$form.length || !orderId) {
            window.alert(
                options.missingFormMessage
                    || 'Unable to complete the action. Please refresh and try again.'
            );
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

    window.SamedayOrdersListAwbForm = {
        submitOrderForm: submitOrderForm
    };
}(jQuery, window));
