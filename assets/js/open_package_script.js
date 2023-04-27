jQuery(document).on('change', '#open_package', () => {
    let isChecked = 'no';
    if (jQuery(this).prop('checked')) {
        isChecked = 'yes';
    }

    doAjaxCall({
        'action': 'woo_get_ajax_data',
        'open_package': isChecked
    });
});

jQuery('body').on('updated_checkout', () => {
    jQuery('input[name="payment_method"]').change(() => {
        doAjaxCall({
            'action': 'woo_get_ajax_data',
            'payment_method': jQuery("input[name='payment_method']:checked").val()
        })
    });
});

const doAjaxCall = (params) => {
    jQuery.ajax({
        'type': 'POST',
        'url': woocommerce_params.ajax_url,
        'data': params,
        success: function () {
            jQuery(document.body).trigger('update_checkout');
        }
    })
}