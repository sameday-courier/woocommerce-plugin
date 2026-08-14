jQuery(document).ready(function ($) {
    var $modal = $('[data-sameday-generate-awb-modal]');
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
            return;
        }
        $select.select2(options);
    });

    // Keep locker first/last mile rows in sync after Select2 init.
    $('#samedaycourier-service').trigger('change');
});
