/**
 * WooCommerce Checkout Blocks — Sameday open package option.
 *
 * Classic checkout uses ShowOpenPackageFieldAction + open_package_script.js.
 * Blocks has no PHP shipping template hooks, so the checkbox is injected next to the
 * selected Sameday shipping rate and persisted through the Store API cart/extensions
 * endpoint, which reprices the cart in the same request.
 */
(function (window, document) {
    'use strict';

    var CONFIG_SETTING_NAME = 'sameday-open-package-blocks_data';
    var UI_ROOT_ID = 'sameday-blocks-open-package-root';
    var CHECKBOX_ID = 'sameday_open_package';
    var RATE_ATTRIBUTE = 'data-sameday-rate';

    var config = {};
    var SamedayCourier = window.SamedayCourier || {};
    var observer = null;
    var isChecked = false;
    var isPending = false;
    var queuedValue = null;

    var readConfig = function () {
        var wcSettings = window.wc && window.wc.wcSettings;

        if (wcSettings && typeof wcSettings.getSetting === 'function') {
            return wcSettings.getSetting(CONFIG_SETTING_NAME, {}) || {};
        }

        return {};
    };

    var getServiceCodes = function () {
        return Array.isArray(config.serviceCodes) ? config.serviceCodes : [];
    };

    var isSupportedRateId = function (rateId) {
        if (!SamedayCourier.isSamedayRateId(rateId, config.pluginName)) {
            return false;
        }

        return getServiceCodes().indexOf(SamedayCourier.getServiceCodeFromRateId(rateId)) !== -1;
    };

    var setPending = function (pending) {
        isPending = pending;

        var input = document.getElementById(CHECKBOX_ID);
        if (input) {
            input.disabled = pending;
        }
    };

    var persist = function (value) {
        var data = {};
        data[config.cartUpdateField || 'open_package'] = value ? 'yes' : 'no';

        var blocksCheckout = window.wc && window.wc.blocksCheckout;
        if (!blocksCheckout || typeof blocksCheckout.extensionCartUpdate !== 'function') {
            // The cart is not repriced without the Store API, but the choice is still stored.
            if (typeof SamedayCourier.doAjaxCall === 'function') {
                SamedayCourier.doAjaxCall(data, false);
            }

            return;
        }

        // Overlapping cart updates would land in an arbitrary order, so a change made while a
        // request is in flight is replayed once that request settles.
        if (isPending) {
            queuedValue = value;

            return;
        }

        setPending(true);

        var done = function () {
            setPending(false);

            if (null !== queuedValue) {
                var next = queuedValue;
                queuedValue = null;

                if (next !== value) {
                    persist(next);
                }
            }
        };

        blocksCheckout
            .extensionCartUpdate({
                namespace: config.cartUpdateNamespace,
                data: data
            })
            .then(done, done);
    };

    var buildUi = function (rateId) {
        var root = document.createElement('div');
        root.id = UI_ROOT_ID;
        root.className = 'sameday-blocks-open-package';
        root.setAttribute(RATE_ATTRIBUTE, rateId);

        var label = document.createElement('label');
        label.className = 'sameday-blocks-open-package__label';
        label.setAttribute('for', CHECKBOX_ID);

        var input = document.createElement('input');
        input.type = 'checkbox';
        input.id = CHECKBOX_ID;
        input.name = 'open_package';
        input.className = 'sameday-checkbox';
        input.checked = isChecked;
        input.disabled = isPending;

        // The checkbox lives inside a rate option rendered by React, whose listeners sit on the
        // checkout root. Keeping the events local prevents Blocks from reading them as a rate change.
        input.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        input.addEventListener('change', function (event) {
            event.stopPropagation();

            isChecked = input.checked;
            persist(isChecked);
        });

        var text = document.createElement('span');
        text.textContent = config.label || '';

        label.appendChild(input);
        label.appendChild(text);
        root.appendChild(label);

        return root;
    };

    var ensureUi = function () {
        var existing = document.getElementById(UI_ROOT_ID);
        var rateId = SamedayCourier.getSelectedShippingRateId();

        // Blocks re-renders the rate list in several passes, during which no radio is checked.
        // Reacting to that would clear the option the customer just ticked.
        if (!rateId) {
            return;
        }

        if (!isSupportedRateId(rateId)) {
            if (existing) {
                existing.remove();
            }

            // Switching away from a Sameday service must not leave the option set in session.
            if (isChecked) {
                isChecked = false;
                persist(false);
            }

            return;
        }

        // Blocks keeps previously rendered rate options mounted, so the checkbox has to be
        // moved whenever the selection changes instead of lingering under the old rate.
        if (existing && existing.getAttribute(RATE_ATTRIBUTE) !== rateId) {
            existing.remove();
            existing = null;
        }

        if (existing) {
            var input = existing.querySelector('#' + CHECKBOX_ID);
            if (input) {
                input.checked = isChecked;
                input.disabled = isPending;
            }

            return;
        }

        var anchor = SamedayCourier.findShippingMethodOption(rateId);
        if (!anchor) {
            return;
        }

        anchor.appendChild(buildUi(rateId));
    };

    var startObserver = function () {
        if (observer) {
            return;
        }

        observer = new MutationObserver(function () {
            ensureUi();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    };

    var boot = function () {
        config = readConfig();

        if (0 === getServiceCodes().length) {
            return;
        }

        isChecked = true === config.checked;

        ensureUi();
        startObserver();

        document.addEventListener('change', function (event) {
            var target = event.target;
            if (!target || !target.matches) {
                return;
            }

            if (target.matches('input[type="radio"]')) {
                ensureUi();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}(window, document));
