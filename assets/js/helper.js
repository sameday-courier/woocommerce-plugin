(function (window, jQuery) {
    'use strict';

    var SamedayCourier = window.SamedayCourier || {};

    SamedayCourier.FIELD_TYPE_OF_BILLING = 'billing';
    SamedayCourier.FIELD_TYPE_OF_SHIPPING = 'shipping';

    var resolveCheckoutAjaxAction = function (params) {
        if (Object.prototype.hasOwnProperty.call(params, 'locker')) {
            return 'store_sameday_locker_in_session';
        }

        if (Object.prototype.hasOwnProperty.call(params, 'open_package')) {
            return 'store_sameday_open_package_in_session';
        }

        if (Object.prototype.hasOwnProperty.call(params, 'payment_method')) {
            return 'store_sameday_payment_method_in_session';
        }

        return null;
    };

    /**
     * @param {Object} params
     * @param {boolean} reloadCheckout
     */
    SamedayCourier.doAjaxCall = function (params, reloadCheckout) {
        params = params || {};
        reloadCheckout = undefined === reloadCheckout ? true : reloadCheckout;
        params.action = resolveCheckoutAjaxAction(params);

        if (null === params.action) {
            return;
        }

        params._wpnonce = (window.samedayVars && window.samedayVars.nonces)
            ? (window.samedayVars.nonces[params.action] || '')
            : '';

        if ('' === params._wpnonce) {
            return;
        }

        jQuery.ajax({
            type: 'POST',
            url: woocommerce_params.ajax_url,
            data: params,
            success: function () {
                if (true === reloadCheckout) {
                    jQuery(document.body).trigger('update_checkout');
                }
            }
        });
    };

    /**
     * @param {string} fieldName
     * @param {string} type
     * @returns {HTMLElement|undefined}
     */
    SamedayCourier.getFieldByType = function (fieldName, type) {
        return Array.from(document.querySelectorAll('input[id*=' + type + '], select[id*=' + type + ']'))
            .find(function (element) {
                return element.id.includes(fieldName);
            });
    };

    window.SamedayCourier = SamedayCourier;
}(window, jQuery));
