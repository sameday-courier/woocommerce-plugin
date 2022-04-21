/**
 * Component: Sync and select lockers
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

    // Validate if element is defined and is not null
    const isset = (accessor) => {
        try {
            return accessor() !== undefined && accessor() !== null
        } catch (e) {
            return false
        }
    }

    const init = () => {
        /* DOM node selectors. */
        if (isset( () => document.getElementById("locker_name"))) {
            document.getElementById("showLockerDetails").style.display = "none";
        }

        let selectors = {
            selectLockerMap: document.querySelector('#select_locker'),
            selectLocker: document.querySelector('#shipping-pickup-store-select'),
            lockerId: document.querySelector('#locker_id')
        };

        /* Map Event. */
        if (isset(() => selectors.selectLockerMap)) {
            selectors.selectLockerMap.addEventListener('click', openLockers);
        } else if (isset( () => selectors.selectLocker)) {
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
        window.LockerPlugin.init({ clientId: clientId, countryCode: countryCode, langCode: langCode });
        var pluginInstance = window.LockerPlugin.getInstance();

        pluginInstance.open();

        pluginInstance.subscribe((message) => {
            selectors.lockerId.value = message.lockerId;
            setCookie("lockerId", message.lockerId, 30);
            document.getElementById("locker_name").value = message.name;
            setCookie("locker_name", message.name, 30);
            document.getElementById("locker_address").value = message.address;
            setCookie("locker_address", message.address, 30);
            document.getElementById("showLockerDetails").style.display = "block";
            document.getElementById("showLockerDetails").innerHTML = message.name + '<br/>' +message.address;

            pluginInstance.close();
        })
    }
    

    function showCookie() {

        if (isset( () => document.getElementById("locker_name"))) {
            let lockerIdcookie = getCookie("lockerId");
            let lockerNamedcookie = getCookie("locker_name");
            let lockerAddresscookie = getCookie("locker_address");
            if (lockerIdcookie.length > 1) {
                document.getElementById("locker_id").value = lockerIdcookie;
                document.getElementById("locker_name").value = lockerNamedcookie;
                document.getElementById("locker_address").value = lockerAddresscookie;
                document.getElementById("showLockerDetails").style.display = "block";
            }

            if (isset( () => document.querySelector('#shipping-pickup-store-select'))) {
                document.getElementById("showLockerDetails").innerHTML = '';
            } else {
                document.getElementById("showLockerDetails").innerHTML = lockerNamedcookie + '<br/>' + lockerAddresscookie;
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
                init();
                showCookie();
            })
            return send.apply(this, arguments)
        }
    })();
    

    const setCookie = (key, value, days) => {
        let d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        let expires = "expires=" + d.toUTCString();

        document.cookie = key + "=" + value + ";" + expires + ";path=/";
    }
      
    const getCookie = (key) => {
        let cookie = '';
        document.cookie.split(';').forEach(function (value) {
            if (value.split('=')[0].trim() === key) {
                return cookie = value.split('=')[1];
            }
        });

        return cookie;
    }