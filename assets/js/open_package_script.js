(function (window, jQuery) {
    'use strict';

    var SamedayCourier = window.SamedayCourier || {};

    jQuery(document).on('change', '#sameday_open_package', function (ev) {
        SamedayCourier.doAjaxCall({
            open_package: ev.target.checked ? 'yes' : 'no'
        });
    });

    jQuery('body').on('updated_checkout', function () {
        jQuery('input[name="payment_method"]').change(function () {
            SamedayCourier.doAjaxCall({
                payment_method: jQuery('input[name="payment_method"]:checked').val()
            });
        });
    });
}(window, jQuery));
