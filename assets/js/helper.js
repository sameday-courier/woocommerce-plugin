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

        var ajaxUrl = (window.samedayVars && window.samedayVars.ajaxUrl)
            ? window.samedayVars.ajaxUrl
            : (window.woocommerce_params && window.woocommerce_params.ajax_url
                ? window.woocommerce_params.ajax_url
                : null);

        if (!ajaxUrl) {
            return;
        }

        jQuery.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: params,
            success: function () {
                if (true === reloadCheckout) {
                    jQuery(document.body).trigger('update_checkout');
                }
            }
        });
    };

    var BLOCKS_SELECTED_RATE_SELECTORS = [
        '.wc-block-components-shipping-rates-control input[type="radio"]:checked',
        '.wc-block-components-radio-control input[type="radio"]:checked',
        'input[type="radio"][name*="radio-control-"]:checked'
    ];

    /**
     * @returns {string}
     */
    SamedayCourier.getSelectedShippingRateId = function () {
        for (var i = 0; i < BLOCKS_SELECTED_RATE_SELECTORS.length; i++) {
            var selected = document.querySelector(BLOCKS_SELECTED_RATE_SELECTORS[i]);
            if (selected && selected.value) {
                return selected.value;
            }
        }

        return '';
    };

    /**
     * @param {string} rateId
     * @returns {string}
     */
    SamedayCourier.getServiceCodeFromRateId = function (rateId) {
        if (!rateId || typeof rateId !== 'string') {
            return '';
        }

        var parts = rateId.split(':');

        return parts.length >= 3 ? parts[parts.length - 1] : '';
    };

    /**
     * @param {string} rateId
     * @param {string} pluginName
     * @returns {boolean}
     */
    SamedayCourier.isSamedayRateId = function (rateId, pluginName) {
        if (!rateId || typeof rateId !== 'string') {
            return false;
        }

        return rateId.indexOf(pluginName || 'samedaycourier') !== -1;
    };

    /**
     * Ordered from the option wrapper to the least specific fallback: the radio label must stay the
     * last resort, because anything appended inside it becomes a label descendant and lets clicks
     * reach the radio it controls.
     */
    var BLOCKS_OPTION_SELECTORS = [
        '.wc-block-components-radio-control-accordion-option',
        '.wc-block-components-radio-control-option',
        '.wc-block-components-shipping-rates-control__package',
        'label',
        'div'
    ];

    /**
     * Container of the shipping rate radio, used to anchor injected checkout UI.
     *
     * @param {string} rateId
     * @returns {HTMLElement|null}
     */
    SamedayCourier.findShippingMethodOption = function (rateId) {
        if (!rateId) {
            return null;
        }

        var input = Array.prototype.find.call(
            document.querySelectorAll('input[type="radio"]'),
            function (radio) {
                return radio.value === rateId;
            }
        );

        if (!input) {
            return null;
        }

        for (var i = 0; i < BLOCKS_OPTION_SELECTORS.length; i++) {
            var anchor = input.closest(BLOCKS_OPTION_SELECTORS[i]);
            if (anchor) {
                return anchor;
            }
        }

        return null;
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
