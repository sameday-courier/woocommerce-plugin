jQuery(document).ready(function ($) {
    var MODAL_SELECTOR = '[data-sameday-generate-awb-modal]';

    function initModal($modal) {
        if (!$modal.length) {
            return;
        }

        var options = {
            dropdownParent: $modal,
            width: '100%'
        };

        $modal.find('select').each(function () {
            var $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2(options);
        });

        $modal.find('select[name="samedaycourier-service"]').trigger('change');
    }

    $(MODAL_SELECTOR).each(function () {
        initModal($(this));
    });

    window.SamedayAwbForm = {
        initModal: initModal
    };
});
