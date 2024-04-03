jQuery(document).on('change', '#open_package', () => {
    let isChecked = 'no';
    if (jQuery(this).prop('checked')) {
        isChecked = 'yes';
    }

    doAjaxCall({
        'open_package': isChecked
    });
});

jQuery('body').on('updated_checkout', () => {
    jQuery('input[name="payment_method"]').change(() => {
        doAjaxCall({
            'payment_method': jQuery("input[name='payment_method']:checked").val()
        })
    });
});