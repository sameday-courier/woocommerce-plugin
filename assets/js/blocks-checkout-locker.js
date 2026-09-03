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
    var selectedLocker = null;
    // Guards a single sync per page load once the server-side TTL has lapsed.
    var blocksSyncDone = false;

    var readConfig = function () {
        var wcSettings = window.wc && window.wc.wcSettings;

        if (wcSettings && typeof wcSettings.getSetting === 'function') {
            return wcSettings.getSetting(CONFIG_SETTING_NAME, {}) || {};
        }

        return {};
    };

    var persistLocker = function (locker) {
        if (typeof SamedayCourier.doAjaxCall === 'function') {
            SamedayCourier.doAjaxCall({ locker: locker }, false);
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

    var SYNCING_CLASS = 'sameday-blocks-locker--syncing';

    var isSyncNeeded = function () {
        return !config.useLockerMap
            && !blocksSyncDone
            && SamedayCourier.isLockerSyncExpired(config.syncTs, config.syncTtl);
    };

    var buildLoadingUi = function (rateId) {
        var loadingClass = SamedayCourier.LOCKER_SYNC_LOADING_CLASS || 'sameday-locker-sync-loading';
        var loadingText = config.loadingText || 'Please wait for easyBox list to be populated';

        return (
            '<div id="' + UI_ROOT_ID + '" class="sameday-blocks-locker ' + SYNCING_CLASS + '" ' +
                RATE_ATTRIBUTE + '="' + rateId + '">' +
                '<div class="' + loadingClass + '" role="status" aria-live="polite">' +
                    '<span class="' + loadingClass + '__spinner" aria-hidden="true"></span>' +
                    '<span class="' + loadingClass + '__text">' + loadingText + '</span>' +
                '</div>' +
            '</div>'
        );
    };

    var startBlocksLockerSync = function (rateId) {
        blocksSyncDone = true;

        SamedayCourier.refreshCheckoutLockers(
            {
                ajaxUrl: config.ajaxUrl,
                action: config.syncAction,
                nonce: config.syncNonce,
                selectedLockerId: typeof selectedLocker === 'string' ? selectedLocker : '',
                loadingContainer: document.getElementById(UI_ROOT_ID),
                loadingText: config.loadingText,
                onComplete: function () {
                    var syncingRoot = document.getElementById(UI_ROOT_ID);

                    if (syncingRoot && syncingRoot.classList.contains(SYNCING_CLASS)) {
                        syncingRoot.remove();
                        ensureUi(true);
                    }
                }
            },
            function (lockersByCity) {
                config.lockersByCity = lockersByCity;
            }
        );
    };

    var openLockers = function () {
        SamedayCourier.openLockerPlugin(
            {
                apiUsername: config.username || '',
                clientId: config.clientId || SamedayCourier.LOCKER_PLUGIN_CLIENT_ID,
                city: SamedayCourier.getCheckoutAddressValue('city'),
                countryCode: SamedayCourier.getCheckoutAddressValue('country', config.country || ''),
            },
            function (locker, pluginInstance) {
                var shipTo = document.getElementById('sameday-blocks-ship-to');
                if (shipTo) {
                    shipTo.textContent = SamedayCourier.formatLockerLabel(locker);
                    shipTo.style.display = 'block';
                }

                selectedLocker = locker;
                persistLocker(locker);
                pluginInstance.close();
            }
        );
    };

    var buildMapUi = function (rateId) {
        var locker = selectedLocker || config.selectedLocker;
        if (locker) {
            selectedLocker = locker;
        }
        var shipToHtml = locker
            ? '<div id="sameday-blocks-ship-to" class="sameday-blocks-ship-to">' +
                (config.shipToText || 'Ship to') + ': ' + SamedayCourier.formatLockerLabel(locker) +
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
                    selectedLocker = null;
                    return;
                }

                selectedLocker = lockerId;
                persistLocker(lockerId);
            });
        }
    };

    var ensureUi = function (skipSync) {
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

        if (!skipSync && isSyncNeeded()) {
            anchor.insertAdjacentHTML('beforeend', buildLoadingUi(rateId));
            startBlocksLockerSync(rateId);
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
        if (selectedLocker) {
            return true;
        }

        if (config.selectedLocker) {
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
            if (
                event.target
                && event.target.matches
                && event.target.matches(
                    '.wc-block-components-shipping-rates-control input[type="radio"]'
                )
            ) {
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
