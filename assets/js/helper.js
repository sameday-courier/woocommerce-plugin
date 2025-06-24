/**
 * Constants for field types
 */
const FIELD_TYPE_OF_BILLING = 'billing';
const FIELD_TYPE_OF_SHIPPING = 'shipping';

const doAjaxCall = (params = {}, reloadCheckout = true) => {
    // Predefined Params
    if (null !== params.action) {
        params.action = 'woo_sameday_post_ajax_data';
    }
    params.samedayNonce = samedayVars.samedayNonce;

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

/**
 * @param fieldName
 * @param type
 *
 * @returns HTML|undefined
 */
const getFieldByType = (fieldName, type) => {
    return Array.from(document.querySelectorAll(`input[id*=${type}], select[id*=${type}]`))
        .find(element => element.id.includes(fieldName)
    );
}