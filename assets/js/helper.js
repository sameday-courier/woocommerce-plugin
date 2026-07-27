/**
 * Constants for field types
 */
const FIELD_TYPE_OF_BILLING = 'billing';
const FIELD_TYPE_OF_SHIPPING = 'shipping';

const resolveCheckoutAjaxAction = (params) => {
    if (Object.prototype.hasOwnProperty.call(params, 'locker')) {
        return 'store_sameday_locker_in_session';
    }

    if (Object.prototype.hasOwnProperty.call(params, 'open_package')) {
        return 'store_sameday_open_package_in_session';
    }

    if (Object.prototype.hasOwnProperty.call(params, 'payment_method')) {
        return 'store_sameday_payment_method_in_session';
    }

    return null;
};

const doAjaxCall = (params = {}, reloadCheckout = true) => {
    params.action = resolveCheckoutAjaxAction(params);

    if (null === params.action) {
        return;
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