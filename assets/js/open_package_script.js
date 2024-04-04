jQuery(document).on('change', '#sameday_open_package', (ev) => {
    doAjaxCall({
        'open_package': ev.target.checked ? "yes" : "no"
    });
});

jQuery('body').on('updated_checkout', () => {
    jQuery('input[name="payment_method"]').change(() => {
        doAjaxCall({
            'payment_method': jQuery("input[name='payment_method']:checked").val()
        })
    });
});