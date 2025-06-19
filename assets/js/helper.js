const doAjaxCall = (params = {}, reloadCheckout = true) => {
    // Predefined Params
    if (null !== params.action) {
        params.action = 'woo_sameday_post_ajax_data';
    }
    params.samedayNonce = samedayVars.samedayNonce; //Came from Server

    jQuery.ajax({
        'type': 'POST',
        'url': woocommerce_params.ajax_url,
        'data': params,
        success: () => {
            if (true === reloadCheckout) {
                jQuery(document.body).trigger('update_checkout');
            }
        }
    })
}
