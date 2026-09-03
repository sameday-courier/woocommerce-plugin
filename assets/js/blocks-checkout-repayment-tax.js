/**
 * WooCommerce Checkout Blocks — COD repayment tax refresh.
 *
 * Classic checkout triggers update_checkout from open_package_script.js.
 * Blocks has no equivalent hook, so the active gateway is persisted through the
 * Store API cart/extensions endpoint, which recalculates cart fees server-side.
 */
(function (window, document) {
    'use strict';

    var CONFIG_SETTING_NAME = 'sameday-repayment-tax-blocks_data';
    var PAYMENT_RADIO_SELECTOR =
        '.wc-block-checkout__payment-method input[type="radio"], ' +
        '.wc-block-components-checkout-payment-methods input[type="radio"]';

    var config = {};
    var isPending = false;
    var queuedMethod = null;
    var lastPersistedMethod = '';
    var previousActiveMethod = '';

    var readConfig = function () {
        var wcSettings = window.wc && window.wc.wcSettings;

        if (wcSettings && typeof wcSettings.getSetting === 'function') {
            return wcSettings.getSetting(CONFIG_SETTING_NAME, {}) || {};
        }

        return {};
    };

    var isCheckoutBusy = function () {
        return !!document.querySelector(
            '.wc-block-components-checkout-place-order-button[disabled], ' +
            '.wc-block-components-checkout-place-order-button.is-busy, ' +
            '.wc-block-checkout__main .wc-block-components-spinner'
        );
    };

    var getActivePaymentMethod = function () {
        var wpData = window.wp && window.wp.data;

        if (!wpData || typeof wpData.select !== 'function') {
            return '';
        }

        var paymentStore = wpData.select('wc/store/payment');

        if (!paymentStore || typeof paymentStore.getActivePaymentMethod !== 'function') {
            return '';
        }

        return paymentStore.getActivePaymentMethod() || '';
    };

    var persist = function (paymentMethod) {
        if (!paymentMethod || paymentMethod === lastPersistedMethod) {
            return;
        }

        if (isCheckoutBusy()) {
            return;
        }

        var data = {};
        data[config.cartUpdateField || 'payment_method'] = paymentMethod;

        var blocksCheckout = window.wc && window.wc.blocksCheckout;

        if (!blocksCheckout || typeof blocksCheckout.extensionCartUpdate !== 'function') {
            return;
        }

        if (isPending) {
            queuedMethod = paymentMethod;

            return;
        }

        isPending = true;

        var done = function () {
            isPending = false;
            lastPersistedMethod = paymentMethod;

            if (null !== queuedMethod && queuedMethod !== paymentMethod) {
                var next = queuedMethod;
                queuedMethod = null;
                persist(next);
            } else {
                queuedMethod = null;
            }
        };

        blocksCheckout
            .extensionCartUpdate({
                namespace: config.cartUpdateNamespace,
                data: data
            })
            .then(done, done);
    };

    var syncActivePaymentMethod = function (paymentMethod) {
        if (!paymentMethod || paymentMethod === previousActiveMethod) {
            return;
        }

        previousActiveMethod = paymentMethod;
        persist(paymentMethod);
    };

    var subscribeToPaymentStore = function () {
        var wpData = window.wp && window.wp.data;

        if (!wpData || typeof wpData.subscribe !== 'function') {
            return false;
        }

        wpData.subscribe(function () {
            syncActivePaymentMethod(getActivePaymentMethod());
        });

        syncActivePaymentMethod(getActivePaymentMethod());

        return true;
    };

    var bindPaymentRadioFallback = function () {
        document.addEventListener('change', function (event) {
            var target = event.target;

            if (!target || !target.matches || !target.matches(PAYMENT_RADIO_SELECTOR)) {
                return;
            }

            if (target.value) {
                syncActivePaymentMethod(target.value);
            }
        });
    };

    var boot = function () {
        config = readConfig();

        if (!config.cartUpdateNamespace) {
            return;
        }

        lastPersistedMethod = config.sessionPaymentMethod || '';
        previousActiveMethod = lastPersistedMethod;

        if (!subscribeToPaymentStore()) {
            bindPaymentRadioFallback();
        } else {
            bindPaymentRadioFallback();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}(window, document));
