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
            lockerId: document.querySelector('#locker_id')
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
            lockerId: document.querySelector('#locker_id'),
            inputCounty: document.querySelector('#select2-billing_state-container'),
            selectLocker: document.querySelector('#select_locker'),
        };
        const clientId="b8cb2ee3-41b9-4c3d-aafe-1527b453d65e";//each integrator will have an unique clientId
        const countryCode= selectors.selectLocker.getAttribute('data-country'); //country for which the plugin is used
        const langCode= selectors.selectLocker.getAttribute('data-country').toLowerCase(); //language of the plugin
        const samedayUser = selectors.selectLocker.getAttribute('data-username').toLowerCase(); //sameday username
        window.LockerPlugin.init({ clientId: clientId, countryCode: countryCode, langCode: langCode, apiUsername: samedayUser });
        var pluginInstance = window.LockerPlugin.getInstance();

        pluginInstance.open();

        pluginInstance.subscribe((message) => {
            selectors.lockerId.value = message.lockerId;
            set_cookie("lockerId", message.lockerId, 30);
            document.getElementById("locker_name").value = message.name;
            set_cookie("locker_name", message.name, 30);
            document.getElementById("locker_address").value = message.address;
            set_cookie("locker_address", message.address, 30);
            document.getElementById("showLockerDetails").style.display = "block";
            document.getElementById("showLockerDetails").innerHTML = message.name + '<br/>' +message.address;

            pluginInstance.close();
        })
    }
    

    function showCookie() {

        if (is_set( () => document.getElementById("locker_name"))) {
            let lockerIdCookie = get_cookie("lockerId");
            let lockerNamesCookie = get_cookie("locker_name");
            let lockerAddressCookie = get_cookie("locker_address");
            if (parseInt(lockerIdCookie) > 0) {
                document.getElementById("locker_id").value = lockerIdCookie;
                document.getElementById("locker_name").value = lockerNamesCookie;
                document.getElementById("locker_address").value = lockerAddressCookie;
                document.getElementById("showLockerDetails").style.display = "block";
            }

            if (is_set( () => document.querySelector('#shipping-pickup-store-select'))) {
                document.getElementById("showLockerDetails").innerHTML = '';
            } else {
                document.getElementById("showLockerDetails").innerHTML = lockerNamesCookie + '<br/>' + lockerAddressCookie;
            }
        }
    }

    /**
     * Initialise component after ajax complete
     */
    (function() {
        const send = XMLHttpRequest.prototype.send

        XMLHttpRequest.prototype.send = function() {
            this.addEventListener('load', function() {    
                let selected_shipping_rate = document.querySelector("input:checked").value;
                if (undefined !== selected_shipping_rate || '' !== selected_shipping_rate) {
                    let shipping_rate_code = selected_shipping_rate.split(':')[2];
                    if (undefined !== shipping_rate_code && shipping_rate_code === 'LN') {
                        init();
                        showCookie();
                    }
                }
            })
            return send.apply(this, arguments)
        }
    })();

    const set_cookie = (key, value, days) => {
        let d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        let expires = "expires=" + d.toUTCString();

        document.cookie = key + "=" + value + ";" + expires + ";path=/";
    }
      
    const get_cookie = (key) => {
        let cookie = '';
        document.cookie.split(';').forEach(function (value) {
            if (value.split('=')[0].trim() === key) {
                return cookie = value.split('=')[1];
            }
        });

        return cookie;
    }