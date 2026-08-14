/**
 * Component: Sync and select lockers in Checkout Form
 */
(function (window, jQuery) {
    'use strict';

    var CLIENT_ID = 'b8cb2ee3-41b9-4c3d-aafe-1527b453d65e';
    var SamedayCourier = window.SamedayCourier || {};

    var isSet = function (accessor) {
        try {
            return accessor() !== undefined && accessor() !== null;
        } catch (e) {
            return false;
        }
    };

    var openLockers = function () {
        var selectors = {
            selectLocker: document.getElementById('select_locker'),
            selectCity: SamedayCourier.getFieldByType('city', SamedayCourier.FIELD_TYPE_OF_SHIPPING),
            selectCountry: SamedayCourier.getFieldByType('country', SamedayCourier.FIELD_TYPE_OF_SHIPPING),
        };

        if (undefined === selectors.selectCity) {
            selectors.selectCity = SamedayCourier.getFieldByType('city', SamedayCourier.FIELD_TYPE_OF_BILLING);
        }

        if (undefined === selectors.selectCountry) {
            selectors.selectCountry = SamedayCourier.getFieldByType('country', SamedayCourier.FIELD_TYPE_OF_BILLING);
        }

        var samedayUser = selectors.selectLocker.getAttribute('data-username').toLowerCase();
        var city;
        if (undefined !== selectors.selectCity) {
            city = selectors.selectCity.value;
        }

        var country;
        var langCode;
        if (undefined !== selectors.selectCountry) {
            country = selectors.selectCountry.value;
            langCode = country.toLowerCase();
        }

        var LockerPlugin = window.LockerPlugin;
        var LockerData = {
            apiUsername: samedayUser,
            clientId: CLIENT_ID,
            city: city,
            countryCode: country,
            langCode: langCode,
        };

        LockerPlugin.init(LockerData);

        if (LockerPlugin.options.countryCode !== country || LockerPlugin.options.city !== city) {
            LockerPlugin.reinitializePlugin(LockerData);
        }

        var pluginInstance = LockerPlugin.getInstance();
        pluginInstance.open();

        pluginInstance.subscribe(function (locker) {
            var shippingAddressSpan = document.querySelector('.wc-block-components-shipping-address') || false;
            if (shippingAddressSpan) {
                shippingAddressSpan.innerHTML = locker.name + ' - ' + locker.address;
            }

            var setCookie = function (key, value, days) {
                var d = new Date();
                d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
                var expires = 'expires=' + d.toUTCString();

                document.cookie = key + '=' + value + ';' + expires + ';path=/';
            };

            setCookie('locker', JSON.stringify(locker), 365);

            SamedayCourier.doAjaxCall({
                locker: locker,
            });

            pluginInstance.close();
        });
    };

    var init = function () {
        var selectors = {
            selectLockerMap: document.querySelector('#select_locker'),
            selectLocker: document.querySelector('#shipping-pickup-store-select'),
        };

        if (isSet(function () { return selectors.selectLockerMap; })) {
            selectors.selectLockerMap.addEventListener('click', openLockers);
        } else if (isSet(function () { return selectors.selectLocker; })) {
            jQuery('select#shipping-pickup-store-select').select2();

            selectors.selectLocker.onchange = function (event) {
                SamedayCourier.doAjaxCall({
                    locker: event.target.value,
                });
            };
        }
    };

    jQuery(document.body).on('updated_checkout', function () {
        var lockerMapButton = document.getElementById('select_locker') || false;
        var lockerDropDownField = document.getElementById('shipping-pickup-store-select') || false;

        if (lockerMapButton || lockerDropDownField) {
            init();
        }
    });
}(window, jQuery));
