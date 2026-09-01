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

    var compactLockerPayload = function (locker) {
        if (locker === null || locker === undefined || locker === '') {
            return locker;
        }

        if (typeof locker !== 'object') {
            return locker;
        }

        return {
            lockerId: locker.lockerId || locker.id || '',
            name: locker.name || '',
            address: locker.address || '',
            city: locker.city || '',
            county: locker.county || '',
            postalCode: locker.postalCode || '',
            oohType: locker.oohType || ''
        };
    };

    /**
     * @param {Object} params
     * @param {boolean} reloadCheckout
     */
    SamedayCourier.doAjaxCall = function (params, reloadCheckout) {
        params = params || {};
        reloadCheckout = undefined === reloadCheckout ? true : reloadCheckout;

        if (Object.prototype.hasOwnProperty.call(params, 'locker')) {
            params.locker = compactLockerPayload(params.locker);
        }

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

    // Only the shipping rates control — broader radio-control selectors also match payment
    // methods, and querySelector would return those whenever shipping radios are briefly
    // unchecked during a Blocks re-render / place-order.
    var BLOCKS_SELECTED_RATE_SELECTOR =
        '.wc-block-components-shipping-rates-control input[type="radio"]:checked';

    /**
     * @returns {string}
     */
    SamedayCourier.getSelectedShippingRateId = function () {
        var selected = document.querySelector(BLOCKS_SELECTED_RATE_SELECTOR);

        return selected && selected.value ? selected.value : '';
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

    SamedayCourier.LOCKER_PLUGIN_CLIENT_ID = 'b8cb2ee3-41b9-4c3d-aafe-1527b453d65e';

    /**
     * Prefer shipping, then billing, then common checkout field id patterns.
     *
     * @param {string} fieldName
     * @returns {HTMLElement|undefined|null}
     */
    SamedayCourier.getCheckoutAddressField = function (fieldName) {
        var shipping = SamedayCourier.getFieldByType(fieldName, SamedayCourier.FIELD_TYPE_OF_SHIPPING);
        if (shipping) {
            return shipping;
        }

        var billing = SamedayCourier.getFieldByType(fieldName, SamedayCourier.FIELD_TYPE_OF_BILLING);
        if (billing) {
            return billing;
        }

        return document.querySelector(
            '#' + fieldName + ', #shipping-' + fieldName + ', #billing-' + fieldName
        );
    };

    /**
     * @param {string} fieldName
     * @param {string} [fallback]
     * @returns {string}
     */
    SamedayCourier.getCheckoutAddressValue = function (fieldName, fallback) {
        var field = SamedayCourier.getCheckoutAddressField(fieldName);
        if (field && field.value) {
            return field.value;
        }

        return fallback || '';
    };

    /**
     * @param {Object|null|undefined} locker
     * @returns {string}
     */
    SamedayCourier.formatLockerLabel = function (locker) {
        if (!locker) {
            return '';
        }

        var name = locker.name || '';
        var address = locker.address || '';
        if (name && address) {
            return name + ' - ' + address;
        }

        return name || address;
    };

    /**
     * Open the Sameday LockerPlugin map and invoke onSelect when a locker is chosen.
     *
     * @param {Object} options
     * @param {string} options.apiUsername
     * @param {string} [options.clientId]
     * @param {string} [options.city]
     * @param {string} [options.countryCode]
     * @param {string} [options.langCode]
     * @param {Function} onSelect function(locker, pluginInstance)
     * @returns {void}
     */
    SamedayCourier.openLockerPlugin = function (options, onSelect) {
        if (typeof window.LockerPlugin === 'undefined') {
            console.error('Sameday LockerPlugin is not loaded.');
            return;
        }

        options = options || {};
        var country = options.countryCode || '';
        var city = options.city || '';
        var lockerData = {
            apiUsername: String(options.apiUsername || '').toLowerCase(),
            clientId: options.clientId || SamedayCourier.LOCKER_PLUGIN_CLIENT_ID,
            city: city,
            countryCode: country,
            langCode: options.langCode || (country ? String(country).toLowerCase() : ''),
        };

        window.LockerPlugin.init(lockerData);

        if (
            window.LockerPlugin.options &&
            (window.LockerPlugin.options.countryCode !== country || window.LockerPlugin.options.city !== city)
        ) {
            window.LockerPlugin.reinitializePlugin(lockerData);
        }

        var pluginInstance = window.LockerPlugin.getInstance();
        pluginInstance.open();

        pluginInstance.subscribe(function (locker) {
            if (typeof onSelect === 'function') {
                onSelect(locker, pluginInstance);
            }
        });
    };

    window.SamedayCourier = SamedayCourier;
}(window, jQuery));
