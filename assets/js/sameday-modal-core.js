(function ($, window) {
    'use strict';

    /**
     * Factory for shared modal open/close behaviour used across admin modals.
     *
     * @param {Object} config
     * @param {string} config.modalSelector
     * @param {string} config.openSelector
     * @param {string} config.closeSelector
     * @param {string} config.openDataKey jQuery .data() key for the open trigger (e.g. "samedayModalOpen")
     * @param {string} [config.bodyOpenClass]
     * @param {function(jQuery): boolean} [config.canClose]
     * @param {function(jQuery): void} [config.onOpen]
     * @param {function(jQuery): void} [config.onClose]
     * @param {boolean} [config.bindOpen]
     * @param {boolean} [config.bindClose]
     * @returns {Object}
     */
    function create(config) {
        var modalSelector = config.modalSelector;
        var openSelector = config.openSelector;
        var closeSelector = config.closeSelector;
        var openDataKey = config.openDataKey;
        var bodyOpenClass = config.bodyOpenClass || 'sameday-bulk-awb-modal-open';
        var canClose = config.canClose;
        var onOpen = config.onOpen;
        var onClose = config.onClose;
        var bindOpen = config.bindOpen !== false;
        var bindClose = config.bindClose !== false;

        function getModal($from) {
            if ($from.is(modalSelector)) {
                return $from;
            }

            return $from.closest(modalSelector);
        }

        function openModal(modalId) {
            var $modal = modalId ? $('#' + modalId) : $(modalSelector).first();
            if (!$modal.length) {
                return null;
            }

            $modal.prop('hidden', false);
            $('body').addClass(bodyOpenClass);

            if (typeof onOpen === 'function') {
                onOpen($modal);
            }

            return $modal;
        }

        function closeModal($modal) {
            if (!$modal || !$modal.length) {
                return;
            }

            if (typeof canClose === 'function' && !canClose($modal)) {
                return;
            }

            $modal.prop('hidden', true);

            if (typeof onClose === 'function') {
                onClose($modal);
            }

            if (!$(modalSelector).filter(':not([hidden])').length) {
                $('body').removeClass(bodyOpenClass);
            }
        }

        function closeAllModals() {
            $(modalSelector).prop('hidden', true);
            $('body').removeClass(bodyOpenClass);
        }

        function bindEvents() {
            if (bindOpen) {
                $(document).on('click', openSelector, function (event) {
                    event.preventDefault();
                    openModal($(this).data(openDataKey));
                });
            }

            if (bindClose) {
                $(document).on('click', closeSelector, function (event) {
                    event.preventDefault();
                    closeModal(getModal($(this)));
                });
            }

            $(document).on('keydown', function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                var $openModal = $(modalSelector).filter(':not([hidden])').last();
                if (!$openModal.length) {
                    return;
                }

                closeModal($openModal);
            });
        }

        return {
            getModal: getModal,
            openModal: openModal,
            closeModal: closeModal,
            closeAllModals: closeAllModals,
            bindEvents: bindEvents,
            modalSelector: modalSelector
        };
    }

    window.SamedayModalCore = {
        create: create
    };
}(jQuery, window));
