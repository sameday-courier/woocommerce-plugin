/**
 * Component: Sync and select lockers in Checkout Form
 */
(function (window, jQuery) {
    'use strict';

    var SamedayCourier = window.SamedayCourier || {};

    // Guards a single sync per page load once the server-side TTL has lapsed.
    var lockerSyncDone = false;

    var initLockerDropdown = function (dropdown) {
        if (!dropdown || jQuery(dropdown).hasClass('select2-hidden-accessible')) {
            return;
        }

        jQuery(dropdown).select2();
    };

    var maybeSyncLockers = function (dropdown) {
        var config = window.samedayLockerSync || {};

        // Dropdown mode only; the map picks lockers via the SDK. The field is rendered by
        // ShowLockerFieldAction only for OOH services, so its presence is the service gate.
        if (config.useLockerMap) {
            return false;
        }

        if (!dropdown || lockerSyncDone) {
            return false;
        }

        if (!SamedayCourier.isLockerSyncExpired(config.ts, config.ttl)) {
            return false;
        }

        lockerSyncDone = true;

        SamedayCourier.refreshCheckoutLockers(
            {
                ajaxUrl: config.ajaxUrl,
                action: config.action,
                nonce: config.nonce,
                selectedLockerId: dropdown.value,
                loadingContainer: dropdown.closest('td') || dropdown.parentElement,
                loadingText: config.loadingText,
                onComplete: function () {
                    initLockerDropdown(dropdown);

                    if (jQuery(dropdown).hasClass('select2-hidden-accessible')) {
                        jQuery(dropdown).trigger('change.select2');
                    }
                }
            },
            function (lockersByCity) {
                SamedayCourier.populateLockerDropdown(dropdown, lockersByCity, config.selectLockerText);
            }
        );

        return true;
    };

    var isSet = function (accessor) {
        try {
            return accessor() !== undefined && accessor() !== null;
        } catch (e) {
            return false;
        }
    };

    var openLockers = function () {
        var selectLocker = document.getElementById('select_locker');
        if (!selectLocker) {
            return;
        }

        var country = SamedayCourier.getCheckoutAddressValue(
            'country',
            selectLocker.getAttribute('data-country') || ''
        );

        SamedayCourier.openLockerPlugin(
            {
                apiUsername: selectLocker.getAttribute('data-username') || '',
                clientId: SamedayCourier.LOCKER_PLUGIN_CLIENT_ID,
                city: SamedayCourier.getCheckoutAddressValue('city'),
                countryCode: country,
            },
            function (locker, pluginInstance) {
                var shippingAddressSpan = document.querySelector('.wc-block-components-shipping-address');
                if (shippingAddressSpan) {
                    shippingAddressSpan.innerHTML = SamedayCourier.formatLockerLabel(locker);
                }

                SamedayCourier.doAjaxCall({
                    locker: locker,
                });

                pluginInstance.close();
            }
        );
    };

    var init = function () {
        var selectors = {
            selectLockerMap: document.querySelector('#select_locker'),
            selectLocker: document.querySelector('#shipping-pickup-store-select'),
        };

        if (isSet(function () { return selectors.selectLockerMap; })) {
            selectors.selectLockerMap.addEventListener('click', openLockers);
        } else if (isSet(function () { return selectors.selectLocker; })) {
            if (!maybeSyncLockers(selectors.selectLocker)) {
                initLockerDropdown(selectors.selectLocker);
            }

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
