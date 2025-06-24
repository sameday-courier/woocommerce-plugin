/**
 * Component: Sync and select lockers in Checkout Form
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

/**
 * CLIENT_ID
 *
 * @type {string}
 */
const CLIENT_ID="b8cb2ee3-41b9-4c3d-aafe-1527b453d65e";

// Validate if element is defined and is not null
const is_set = (accessor) => {
    try {
        return accessor() !== undefined && accessor() !== null
    } catch (e) {
        return false
    }
}

const _init = () => {
    /* DOM node selectors. */
    let selectors = {
        selectLockerMap: document.querySelector('#select_locker'),
        selectLocker: document.querySelector('#shipping-pickup-store-select'),
    };

    /* Map Event. */
    if (is_set(() => selectors.selectLockerMap)) {
        selectors.selectLockerMap.addEventListener('click', _openLockers);
    } else if (is_set( () => selectors.selectLocker)) {
        /* Add select2 to lockers dropdown. */
        jQuery('select#shipping-pickup-store-select').select2();

        selectors.selectLocker.onchange = (event) => {
            doAjaxCall({
                'locker': event.target.value,
            });
        }
    }
}

const _openLockers = () => {
    /* DOM node selectors. */
    let selectors = {
        selectLocker: document.getElementById('select_locker'),
        selectCity: getFieldByType('city', FIELD_TYPE_OF_SHIPPING),
        selectCountry: getFieldByType('country', FIELD_TYPE_OF_SHIPPING),
    };

    if (undefined === selectors.selectCity) {
        selectors.selectCity = getFieldByType('city', FIELD_TYPE_OF_BILLING);
    }

    if (undefined === selectors.selectCountry) {
        selectors.selectCountry = getFieldByType('country', FIELD_TYPE_OF_BILLING);
    }

    let samedayUser = selectors.selectLocker.getAttribute('data-username').toLowerCase();
    let city;
    if (undefined !== selectors.selectCity) {
        city = selectors.selectCity.value
    }

    let country;
    let langCode;
    if (undefined !== selectors.selectCountry) {
        country = selectors.selectCountry.value;
        langCode = country.toLowerCase();
    }

    const LockerPlugin = window['LockerPlugin'];
    const LockerData = {
        apiUsername: samedayUser,
        clientId: CLIENT_ID,
        city: city,
        countryCode: country,
        langCode: langCode,
    };

    LockerPlugin.init(LockerData);

    if (LockerPlugin.options.countryCode !== country || LockerPlugin.options.city !== city) {
        LockerPlugin.reinitializePlugin(LockerData);
    }

    let pluginInstance = LockerPlugin.getInstance();
    pluginInstance.open();

    pluginInstance.subscribe((locker) => {
        const shipping_address_span = document.querySelector('.wc-block-components-shipping-address') || false;
        if (shipping_address_span) {
            shipping_address_span.innerHTML = locker.name + ' - ' + locker.address;
            locker.address = locker.name + ' - ' + locker.address;
            shipping_address_span.innerHTML = locker.address;
        }

        doAjaxCall(
            {
                'locker': locker,
            },
        );

        pluginInstance.close();
    });

}

/**
 * Initialise component after ajax complete
 */
jQuery(document.body).on("updated_checkout", () => {
        const locker_map_button = document.getElementById('select_locker') || false;
        const locker_drop_down_field = document.getElementById('shipping-pickup-store-select') || false;

        if (locker_map_button || locker_drop_down_field) {
            _init();
        }
    }
);
