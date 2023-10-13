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

    const init = () => {
        /* DOM node selectors. */
        if (is_set( () => document.getElementById("locker_name"))) {
            document.getElementById("showLockerDetails").style.display = "none";
        }

        let selectors = {
            selectLockerMap: document.querySelector('#select_locker'),
            selectLocker: document.querySelector('#shipping-pickup-store-select'),
            lockerId: document.querySelector('#locker'),
        };

        /* Map Event. */
        if (is_set(() => selectors.selectLockerMap)) {
            selectors.selectLockerMap.addEventListener('click', openLockers);
        } else if (is_set( () => selectors.selectLocker)) {
            /* Add select2 to lockers dropdown. */
            jQuery('select#shipping-pickup-store-select').select2();

            selectors.selectLocker.onchange = (event) => {
                selectors.lockerId.value = event.target.value;
                document.getElementById("showLockerDetails").innerHTML = '';
            }
        }
    }
    
    const openLockers = () => {
        /* DOM node selectors. */
        let selectors = {
            lockerId: document.querySelector('#locker'),
            inputCounty: document.querySelector('#select2-billing_state-container'),
            selectLocker: document.querySelector('#select_locker'),
            selectCity: document.getElementById('shipping_city'),
        };

        const clientId="b8cb2ee3-41b9-4c3d-aafe-1527b453d65e";//each integrator will have unique clientId
        const countryCode= selectors.selectLocker.getAttribute('data-country'); //country for which the plugin is used
        const langCode= selectors.selectLocker.getAttribute('data-country').toLowerCase(); //language of the plugin
        const samedayUser = selectors.selectLocker.getAttribute('data-username').toLowerCase(); //sameday username

        let city = null;
        if (null !== selectors.selectCity) {
            city = selectors.selectCity.value;
        }

        const LockerPlugin = window['LockerPlugin'];

        LockerPlugin.init(
            {
                clientId: clientId,
                countryCode: countryCode,
                langCode: langCode,
                apiUsername: samedayUser,
                city: city,
            }
        );

        let pluginInstance = LockerPlugin.getInstance();

        pluginInstance.open();

        pluginInstance.subscribe((message) => {
            let lockerDetails = {};
            lockerDetails.id = message.lockerId;
            lockerDetails.name  = message.name;
            lockerDetails.address = message.address;
            lockerDetails.city = message.city;
            lockerDetails.county = message.county;
            lockerDetails.postalCode = message.postalCode;

            selectors.lockerId.value = JSON.stringify(lockerDetails);
            _setCookie("locker", JSON.stringify(lockerDetails), 30);

            document.getElementById("locker_name").value = message.name;
            document.getElementById("locker_address").value = message.address;

            document.getElementById("showLockerDetails").style.display = "block";
            document.getElementById("showLockerDetails").innerHTML = message.name + '<br/>' +message.address;

            pluginInstance.close();
        });
    }
    

    function showCookie() {

        if (is_set( () => document.getElementById("locker_name"))) {

            let lockerCookie = null;
            if ('' !== _getCookie("locker")) {
                lockerCookie = JSON.parse(_getCookie("locker"));
                if (typeof lockerCookie.city === "undefined"
                    && typeof lockerCookie.county === "undefined"
                    && typeof lockerCookie.postalCode === "undefined"
                ) {
                    lockerCookie = null;
                }
            }

            if (null !== lockerCookie) {
                document.getElementById("locker").value = JSON.stringify(lockerCookie);
                document.getElementById("locker_name").value = lockerCookie.name;
                document.getElementById("locker_address").value = lockerCookie.address;

                document.getElementById("showLockerDetails").style.display = "block";

                if (is_set( () => document.querySelector('#shipping-pickup-store-select'))) {
                    document.getElementById("showLockerDetails").innerHTML = '';
                } else {
                    document.getElementById("showLockerDetails").innerHTML = lockerCookie.name + '<br/>' + lockerCookie.address;
                }
            }
        }
    }

    /**
     * Initialise component after ajax complete
     */
    jQuery(document.body).on("updated_checkout", () => {
            const locker_map_button = document.getElementById('select_locker') || false;
            const locker_drop_down_field = document.getElementById('shipping-pickup-store-select') || false;

            if (locker_map_button || locker_drop_down_field) {
                init();
            }
            jQuery('.shipping-pickup-store [id]').each(function (i) {
                jQuery('.shipping-pickup-store [id="' + this.id + '"]').slice(1).remove();
            });

            showCookie();
        }
    );

    const _setCookie = (key, value, days) => {
        let d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        let expires = "expires=" + d.toUTCString();

        document.cookie = key + "=" + value + ";" + expires + ";path=/";
    }
      
    const _getCookie = (key) => {
        let cookie = '';
        document.cookie.split(';').forEach(function (value) {
            if (value.split('=')[0].trim() === key) {
                return cookie = value.split('=')[1];
            }
        });

        return cookie;
    }