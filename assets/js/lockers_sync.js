/**
 * Component: Sync and select lockers in Checkout Form
 */
(function (window, jQuery) {
    'use strict';

    var SamedayCourier = window.SamedayCourier || {};

    // Guards a single sync per page load once the server-side TTL has lapsed.
    var lockerSyncDone = false;

    function initLockerDropdown(dropdown) {
        if (!dropdown || jQuery(dropdown).hasClass('select2-hidden-accessible')) {
            return;
        }

        jQuery(dropdown).select2();
    }

    function maybeSyncLockers(dropdown) {
        var config = window.samedayLockerSync || {};

        if (config.useLockerMap || !dropdown || lockerSyncDone) {
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
    }

    function openLockers(event) {
        if (event) {
            event.preventDefault();
        }

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
    }

    function initDropdownField() {
        var selectLocker = document.getElementById('shipping-pickup-store-select');
        if (!selectLocker) {
            return;
        }

        if (!maybeSyncLockers(selectLocker)) {
            initLockerDropdown(selectLocker);
        }
    }

    jQuery(function ($) {
        $(document.body).on('click.samedayLockerMap', '#select_locker', openLockers);
        $(document.body).on('change.samedayLockerDropdown', '#shipping-pickup-store-select', function (event) {
            SamedayCourier.doAjaxCall({
                locker: event.target.value,
            });
        });

        $(document.body).on('updated_checkout', function () {
            if (document.getElementById('select_locker') || document.getElementById('shipping-pickup-store-select')) {
                initDropdownField();
            }
        });

        initDropdownField();
    });
}(window, jQuery));
