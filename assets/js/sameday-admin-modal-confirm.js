(function ($, window) {
    'use strict';

    var NOTICE_SELECTOR = '[data-sameday-modal-notice]';
    var BUSY_SELECTOR = '[data-sameday-modal-busy]';
    var BUSY_TEXT_SELECTOR = '[data-sameday-modal-busy-text]';
    var CONFIRM_SELECTOR = '.sameday-bulk-awb-modal__btn--confirm';
    var INTERACTIVE_SELECTOR = [
        CONFIRM_SELECTOR,
        '.sameday-bulk-awb-modal__btn--cancel',
        '.sameday-bulk-awb-modal__close',
        '[data-sameday-modal-close]'
    ].join(', ');

    /**
     * Show a dismissible WordPress admin notice on the current page.
     *
     * @param {string} message
     * @param {string} [type]
     */
    function showPageNotice(message, type) {
        var wrap = document.querySelector('.wrap');
        if (!wrap || !message) {
            return;
        }

        wrap.querySelectorAll('.sameday-admin-page-notice').forEach(function (node) {
            node.remove();
        });

        var notice = document.createElement('div');
        notice.className = 'notice notice-' + (type || 'error') + ' is-dismissible sameday-admin-page-notice';

        var paragraph = document.createElement('p');
        paragraph.textContent = message;
        notice.appendChild(paragraph);

        var heading = wrap.querySelector('h1, h2');
        if (heading) {
            heading.insertAdjacentElement('afterend', notice);
        } else {
            wrap.insertAdjacentElement('afterbegin', notice);
        }

        notice.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Shared confirm-modal behaviour for admin dialogs backed by SamedayAdminModal.
     *
     * @param {Object} config
     * @param {string} config.modalId
     * @param {string} config.agreeSelector
     * @param {string} [config.confirmSelector]
     * @param {string} [config.busyMessage]
     * @param {function(Object): void} config.onConfirm
     * @returns {Object}
     */
    function create(config) {
        var modalId = config.modalId;
        var agreeSelector = config.agreeSelector;
        var confirmSelector = config.confirmSelector || ('#' + modalId + ' ' + CONFIRM_SELECTOR);
        var busyMessage = config.busyMessage || 'Processing…';
        var onConfirm = config.onConfirm;

        function $modal() {
            return $('#' + modalId);
        }

        function isBusy() {
            return $modal().hasClass('is-busy');
        }

        function syncConfirmState() {
            if (isBusy()) {
                return;
            }

            var agreed = $modal().find(agreeSelector).is(':checked');
            $modal().find(CONFIRM_SELECTOR).prop('disabled', !agreed);
        }

        function showNotice(message) {
            var $notice = $modal().find(NOTICE_SELECTOR);

            if (!message) {
                $notice.prop('hidden', true).text('');
                return;
            }

            $notice.text(message).prop('hidden', false);
        }

        function clearNotice() {
            showNotice('');
        }

        function setBusy(busy) {
            var $currentModal = $modal();

            $currentModal.toggleClass('is-busy', busy);
            $currentModal.find(BUSY_SELECTOR).prop('hidden', !busy);
            $currentModal.find(BUSY_TEXT_SELECTOR).text(busyMessage);
            $currentModal.find(INTERACTIVE_SELECTOR).prop('disabled', busy);

            if (!busy) {
                syncConfirmState();
            }
        }

        function open() {
            clearNotice();
            setBusy(false);

            if (window.SamedayAdminModal && typeof window.SamedayAdminModal.open === 'function') {
                window.SamedayAdminModal.open(modalId);
            }
        }

        function close() {
            if (isBusy()) {
                return;
            }

            if (window.SamedayAdminModal && typeof window.SamedayAdminModal.close === 'function') {
                window.SamedayAdminModal.close($modal());
            }
        }

        function handleConfirm(event) {
            event.preventDefault();

            var $confirm = $(event.currentTarget);
            if ($confirm.is(':disabled') || isBusy()) {
                return;
            }

            if (!$modal().find(agreeSelector).is(':checked')) {
                return;
            }

            if (typeof onConfirm === 'function') {
                onConfirm({
                    setBusy: setBusy,
                    showNotice: showNotice,
                    clearNotice: clearNotice,
                    $modal: $modal
                });
            }
        }

        function bindEvents() {
            $(document).on('change', '#' + modalId + ' ' + agreeSelector, syncConfirmState);
            $(document).on('click', confirmSelector, handleConfirm);
        }

        return {
            bindEvents: bindEvents,
            clearNotice: clearNotice,
            close: close,
            isBusy: isBusy,
            open: open,
            setBusy: setBusy,
            showNotice: showNotice,
            syncConfirmState: syncConfirmState
        };
    }

    window.SamedayAdminModalConfirm = {
        create: create,
        showPageNotice: showPageNotice
    };
}(jQuery, window));
