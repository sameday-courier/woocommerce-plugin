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
    var lastKnownRateId = '';

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

    var isCheckoutBusy = function () {
        return !!document.querySelector(
            '.wc-block-components-checkout-place-order-button[disabled], ' +
            '.wc-block-components-checkout-place-order-button.is-busy, ' +
            '.wc-block-checkout__main .wc-block-components-spinner'
        );
    };

    var setPending = function (pending) {
        isPending = pending;

        var input = document.getElementById(CHECKBOX_ID);
        if (input) {
            input.disabled = pending;
        }
    };

    var persist = function (value) {
        if (isCheckoutBusy()) {
            return;
        }

        var data = {};
        data[config.cartUpdateField || 'open_package'] = value ? 'yes' : 'no';

        var blocksCheckout = window.wc && window.wc.blocksCheckout;
        if (!blocksCheckout || typeof blocksCheckout.extensionCartUpdate !== 'function') {
            if (typeof SamedayCourier.doAjaxCall === 'function') {
                SamedayCourier.doAjaxCall(data, false);
            }

            return;
        }

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

    var clearOpenPackageIfNeeded = function (rateId) {
        if (!isChecked || isSupportedRateId(rateId)) {
            return;
        }

        isChecked = false;
        persist(false);
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

    /**
     * @param {boolean} allowSessionClear
     */
    var ensureUi = function (allowSessionClear) {
        var existing = document.getElementById(UI_ROOT_ID);
        var rateId = SamedayCourier.getSelectedShippingRateId();

        if (!rateId) {
            return;
        }

        if (allowSessionClear && rateId !== lastKnownRateId) {
            clearOpenPackageIfNeeded(rateId);
            lastKnownRateId = rateId;
        }

        if (!isSupportedRateId(rateId)) {
            if (existing) {
                existing.remove();
            }

            return;
        }

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
            ensureUi(false);
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
        lastKnownRateId = SamedayCourier.getSelectedShippingRateId();

        ensureUi(false);
        startObserver();

        document.addEventListener('change', function (event) {
            var target = event.target;
            if (!target || !target.matches) {
                return;
            }

            if (target.matches('.wc-block-components-shipping-rates-control input[type="radio"]')) {
                ensureUi(true);
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}(window, document));
