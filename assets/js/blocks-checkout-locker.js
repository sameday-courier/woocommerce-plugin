/**
 * WooCommerce Checkout Blocks — EasyBox / locker selection UI.
 *
 * Classic checkout uses ShowLockerFieldAction + lockers_sync.js.
 * Blocks has no PHP shipping template hooks; this script injects the UI and opens LockerPlugin.
 */
(function (window, document, jQuery) {
    'use strict';

    var CONFIG_SETTING_NAME = 'sameday-checkout-blocks_data';
    var config = {};
    var SamedayCourier = window.SamedayCourier || {};
    var UI_ROOT_ID = 'sameday-blocks-locker-root';
    var RATE_ATTRIBUTE = 'data-sameday-rate';
    var observer = null;
    var boundPlaceOrder = false;

    var readConfig = function () {
        var wcSettings = window.wc && window.wc.wcSettings;

        if (wcSettings && typeof wcSettings.getSetting === 'function') {
            return wcSettings.getSetting(CONFIG_SETTING_NAME, {}) || {};
        }

        return {};
    };

    var getCookie = function (key) {
        var cookie = '';
        document.cookie.split(';').forEach(function (value) {
            if (value.split('=')[0].trim() === key) {
                cookie = value.split('=')[1] || '';
            }
        });

        try {
            return cookie ? decodeURIComponent(cookie) : '';
        } catch (e) {
            return cookie;
        }
    };

    var setCookie = function (key, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = key + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/';
    };

    var parseLockerCookie = function () {
        var raw = getCookie('locker');
        if (!raw) {
            return null;
        }

        try {
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    };

    var getOohCodes = function () {
        return Array.isArray(config.oohServiceCodes) ? config.oohServiceCodes : ['LN', 'XL', 'PP'];
    };

    var isOohRateId = function (rateId) {
        if (!SamedayCourier.isSamedayRateId(rateId, config.pluginName)) {
            return false;
        }

        return getOohCodes().indexOf(SamedayCourier.getServiceCodeFromRateId(rateId)) !== -1;
    };

    var isOohSelected = function () {
        return isOohRateId(SamedayCourier.getSelectedShippingRateId());
    };

    var getAddressField = function (fieldName) {
        if (typeof SamedayCourier.getFieldByType === 'function') {
            var shipping = SamedayCourier.getFieldByType(fieldName, SamedayCourier.FIELD_TYPE_OF_SHIPPING);
            if (shipping) {
                return shipping;
            }

            return SamedayCourier.getFieldByType(fieldName, SamedayCourier.FIELD_TYPE_OF_BILLING);
        }

        return document.querySelector(
            '#' + fieldName + ', #shipping-' + fieldName + ', #billing-' + fieldName +
            ', input[autocomplete="address-level2"], select[autocomplete="country"]'
        );
    };

    var getCityValue = function () {
        var cityField = getAddressField('city');
        return cityField && cityField.value ? cityField.value : '';
    };

    var getCountryValue = function () {
        var countryField = getAddressField('country');
        if (countryField && countryField.value) {
            return countryField.value;
        }

        return config.country || '';
    };

    var formatLockerLabel = function (locker) {
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

    var openLockers = function () {
        if (typeof window.LockerPlugin === 'undefined') {
            console.error('Sameday LockerPlugin is not loaded.');
            return;
        }

        var country = getCountryValue();
        var city = getCityValue();
        var lockerData = {
            apiUsername: (config.username || '').toLowerCase(),
            clientId: config.clientId,
            city: city,
            countryCode: country,
            langCode: country ? String(country).toLowerCase() : '',
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
            setCookie('locker', JSON.stringify(locker), 365);

            var shipTo = document.getElementById('sameday-blocks-ship-to');
            if (shipTo) {
                shipTo.textContent = formatLockerLabel(locker);
                shipTo.style.display = 'block';
            }

            if (typeof SamedayCourier.doAjaxCall === 'function') {
                SamedayCourier.doAjaxCall({ locker: locker }, false);
            }

            pluginInstance.close();
        });
    };

    var buildMapUi = function (rateId) {
        var locker = parseLockerCookie() || config.selectedLocker;
        var shipToHtml = locker
            ? '<div id="sameday-blocks-ship-to" class="sameday-blocks-ship-to">' +
                (config.shipToText || 'Ship to') + ': ' + formatLockerLabel(locker) +
              '</div>'
            : '<div id="sameday-blocks-ship-to" class="sameday-blocks-ship-to" style="display:none;"></div>';

        return (
            '<div id="' + UI_ROOT_ID + '" class="sameday-blocks-locker" ' +
                RATE_ATTRIBUTE + '="' + rateId + '">' +
                '<button type="button" class="button alt sameday_select_locker" id="select_locker">' +
                    (config.buttonText || 'Show Locations Map') +
                '</button>' +
                '<div id="placeOrderError">' + (config.errorText || 'Please choose an easybox') + '</div>' +
                shipToHtml +
            '</div>'
        );
    };

    var buildDropdownUi = function (rateId) {
        var lockersByCity = config.lockersByCity || {};
        var options = '<option value="">' + (config.selectLockerText || 'Select easyBox') + '</option>';

        Object.keys(lockersByCity).forEach(function (city) {
            options += '<optgroup label="' + city + '">';
            (lockersByCity[city] || []).forEach(function (locker) {
                options +=
                    '<option value="' + locker.id + '"' + (locker.selected ? ' selected' : '') + '>' +
                        locker.label +
                    '</option>';
            });
            options += '</optgroup>';
        });

        return (
            '<div id="' + UI_ROOT_ID + '" class="sameday-blocks-locker" ' +
                RATE_ATTRIBUTE + '="' + rateId + '">' +
                '<select id="shipping-pickup-store-select" name="locker_id">' + options + '</select>' +
                '<div id="placeOrderError">' + (config.errorText || 'Please choose an easybox') + '</div>' +
            '</div>'
        );
    };

    var bindUiEvents = function (root) {
        // The UI lives inside a rate option rendered by React, whose listeners sit on the checkout
        // root. Keeping the events local prevents Blocks from reading them as a rate change.
        var mapButton = root.querySelector('#select_locker');
        if (mapButton) {
            mapButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                openLockers();
            });
        }

        var dropdown = root.querySelector('#shipping-pickup-store-select');
        if (dropdown) {
            dropdown.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            dropdown.addEventListener('change', function (event) {
                event.stopPropagation();

                var lockerId = event.target.value;
                if (!lockerId) {
                    return;
                }

                if (typeof SamedayCourier.doAjaxCall === 'function') {
                    SamedayCourier.doAjaxCall({ locker: lockerId }, false);
                }
            });
        }
    };

    var ensureUi = function () {
        var existing = document.getElementById(UI_ROOT_ID);
        var rateId = SamedayCourier.getSelectedShippingRateId();

        // Blocks re-renders the rate list in several passes, during which no radio is checked.
        // Tearing the UI down on that transient state makes it flicker.
        if (!rateId) {
            return;
        }

        if (!isOohRateId(rateId)) {
            if (existing) {
                existing.remove();
            }
            return;
        }

        // Blocks keeps previously rendered rate options mounted, so the UI has to be moved
        // whenever the selection changes instead of lingering under the old rate.
        if (existing && existing.getAttribute(RATE_ATTRIBUTE) !== rateId) {
            existing.remove();
            existing = null;
        }

        if (existing) {
            return;
        }

        var anchor = SamedayCourier.findShippingMethodOption(rateId);
        if (!anchor) {
            return;
        }

        var html = config.useLockerMap ? buildMapUi(rateId) : buildDropdownUi(rateId);
        anchor.insertAdjacentHTML('beforeend', html);

        var root = document.getElementById(UI_ROOT_ID);
        if (root) {
            bindUiEvents(root);
        }
    };

    var hasLockerSelection = function () {
        var cookieLocker = parseLockerCookie();
        if (cookieLocker) {
            return true;
        }

        var dropdown = document.getElementById('shipping-pickup-store-select');
        return !!(dropdown && dropdown.value);
    };

    var bindPlaceOrderGuard = function () {
        if (boundPlaceOrder) {
            return;
        }

        var button = document.querySelector('.wc-block-components-checkout-place-order-button');
        if (!button) {
            return;
        }

        boundPlaceOrder = true;
        button.addEventListener('click', function (event) {
            if (!isOohSelected() || hasLockerSelection()) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var error = document.getElementById('placeOrderError');
            if (error) {
                error.classList.add('sameday-is-visible');
                setTimeout(function () {
                    error.classList.remove('sameday-is-visible');
                }, 3000);
            }

            var target = document.getElementById('select_locker') ||
                document.getElementById('shipping-pickup-store-select') ||
                document.getElementById(UI_ROOT_ID);
            if (target && typeof target.scrollIntoView === 'function') {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, true);
    };

    var sync = function () {
        ensureUi();
        bindPlaceOrderGuard();
    };

    var startObserver = function () {
        if (observer) {
            return;
        }

        observer = new MutationObserver(function () {
            sync();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    };

    var boot = function () {
        config = readConfig();

        if (!config.username) {
            return;
        }

        sync();
        startObserver();

        document.addEventListener('change', function (event) {
            if (event.target && event.target.matches && event.target.matches('input[type="radio"]')) {
                sync();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}(window, document, window.jQuery));
