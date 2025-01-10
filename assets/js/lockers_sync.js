/**
 * Component: Sync and select lockers
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

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
        selectLocker: document.querySelector('#select_locker'),
        shipToDifferentAddress: document.querySelector('#ship-to-different-address-checkbox'),
        selectCity: document.getElementById('billing_city'),
        selectCountry: document.getElementById('billing_country'),
    };

    let useShippingAddress = false;
    if (is_set(() => selectors.shipToDifferentAddress)) {
        useShippingAddress = selectors.shipToDifferentAddress.checked;
        if (useShippingAddress) {
            selectors.selectCity = document.getElementById('shipping_city');
            selectors.selectCountry = document.getElementById('shipping_country');
        }
    }

    const clientId="b8cb2ee3-41b9-4c3d-aafe-1527b453d65e";
    const langCode= selectors.selectLocker.getAttribute('data-country').toLowerCase();
    const samedayUser = selectors.selectLocker.getAttribute('data-username').toLowerCase();

    let city = null;
    if (null !== selectors.selectCity) {
        city = selectors.selectCity.value;
    }

    let country = null;
    if (null !== selectors.selectCountry) {
        country = selectors.selectCountry.value;
    }

    const LockerPlugin = window['LockerPlugin'];
    const LockerData = {
        apiUsername: samedayUser,
        clientId: clientId,
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
