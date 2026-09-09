(function ($) {
    'use strict';

    var controller = window.SamedayModalCore.create({
        modalSelector: '[data-sameday-modal]',
        openSelector: '[data-sameday-modal-open]',
        closeSelector: '[data-sameday-modal-close]',
        openDataKey: 'samedayModalOpen',
        canClose: function ($modal) {
            return !$modal.hasClass('is-busy');
        }
    });

    $(function () {
        controller.bindEvents();
    });

    window.SamedayAdminModal = {
        open: controller.openModal,
        close: controller.closeModal
    };
}(jQuery));
