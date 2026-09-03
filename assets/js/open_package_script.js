(function (window, jQuery) {
    'use strict';

    jQuery(document).on('change', '#sameday_open_package', function (event) {
        window.SamedayCourier.doAjaxCall({
            open_package: event.target.checked ? 'yes' : 'no'
        });
    });

    /**
     * WooCommerce does not refresh checkout totals when the payment method changes.
     * Trigger update_checkout so WC persists chosen_payment_method and recalculates fees
     * (e.g. COD repayment tax) from the server-side session — no custom session endpoint.
     */
    var bindPaymentMethodCheckoutRefresh = function () {
        jQuery('input[name="payment_method"]')
            .off('change.samedayPaymentMethod')
            .on('change.samedayPaymentMethod', function () {
                jQuery(document.body).trigger('update_checkout');
            });
    };

    jQuery(document.body).on('updated_checkout init_checkout', bindPaymentMethodCheckoutRefresh);
}(window, jQuery));
