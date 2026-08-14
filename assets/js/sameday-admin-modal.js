(function ($) {
    'use strict';

    var MODAL_SELECTOR = '[data-sameday-modal]';
    var OPEN_SELECTOR = '[data-sameday-modal-open]';
    var CLOSE_SELECTOR = '[data-sameday-modal-close]';
    var BODY_OPEN_CLASS = 'sameday-bulk-awb-modal-open';

    function getModal($from) {
        if ($from.is(MODAL_SELECTOR)) {
            return $from;
        }

        return $from.closest(MODAL_SELECTOR);
    }

    function openModal(modalId) {
        var $modal = modalId ? $('#' + modalId) : $(MODAL_SELECTOR).first();
        if (!$modal.length) {
            return;
        }

        $modal.prop('hidden', false);
        $('body').addClass(BODY_OPEN_CLASS);
        $(document).trigger('sameday-modal:opened', [$modal]);
    }

    function closeModal($modal) {
        if (!$modal || !$modal.length) {
            return;
        }

        $modal.prop('hidden', true);
        $(document).trigger('sameday-modal:closed', [$modal]);

        if (!$(MODAL_SELECTOR).filter(':not([hidden])').length) {
            $('body').removeClass(BODY_OPEN_CLASS);
        }
    }

    $(function () {
        $(document).on('click', OPEN_SELECTOR, function (event) {
            event.preventDefault();
            openModal($(this).data('sameday-modal-open'));
        });

        $(document).on('click', CLOSE_SELECTOR, function (event) {
            event.preventDefault();
            closeModal(getModal($(this)));
        });

        $(document).on('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            var $openModal = $(MODAL_SELECTOR).filter(':not([hidden])').last();
            if (!$openModal.length) {
                return;
            }

            closeModal($openModal);
        });
    });

    window.SamedayAdminModal = {
        open: openModal,
        close: closeModal
    };
}(jQuery));
