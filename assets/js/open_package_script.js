(function (window, jQuery) {
    'use strict';

    var SamedayCourier = window.SamedayCourier || {};

    jQuery(document).on('change', '#sameday_open_package', function (event) {
        SamedayCourier.doAjaxCall({
            open_package: event.target.checked ? 'yes' : 'no'
        });
    });

    jQuery(document.body).on('updated_checkout', function () {
        jQuery('input[name="payment_method"]')
            .off('change.samedayPaymentMethod')
            .on('change.samedayPaymentMethod', function () {
                SamedayCourier.doAjaxCall({
                    payment_method: jQuery('input[name="payment_method"]:checked').val()
                });
            });
    });
}(window, jQuery));
