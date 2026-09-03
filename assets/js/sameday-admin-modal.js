(function ($) {
    'use strict';

    var controller = window.SamedayModalCore.create({
        modalSelector: '[data-sameday-modal]',
        openSelector: '[data-sameday-modal-open]',
        closeSelector: '[data-sameday-modal-close]',
        openDataKey: 'samedayModalOpen'
    });

    $(function () {
        controller.bindEvents();
    });

    window.SamedayAdminModal = {
        open: controller.openModal,
        close: controller.closeModal
    };
}(jQuery));
