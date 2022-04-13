/**
 * Component: Sync and select lockers
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

    function init() {
        
        /* DOM node selectors. */

        $('#locker_name').hide();
        $('#locker_address').hide();

        let selectors = {
            selectLockerMap: document.querySelector('#select_locker'),
            selectLocker: document.querySelector('#shipping-pickup-store-select'),
            lockerId: document.querySelector('#locker_id')
        };

        /* Map Event. */
        
        if (typeof( selectors.selectLockerMap) != 'undefined' && selectors.selectLockerMap != null){
            selectors.selectLockerMap.addEventListener('click',openLockers);
        }else if (typeof( selectors.selectLocker) != 'undefined' && selectors.selectLocker != null){
            /* Add select2 to lockers dropdown. */
            jQuery('select#shipping-pickup-store-select').select2();

            selectors.selectLocker.onchange = (event) => {
                let lockerId = event.target.value;
                selectors.lockerId.value = lockerId;
            }
        }
        
    }
    
    function openLockers() {

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
            $('#locker_name').val(message.name);
            $('#locker_address').val(message.address);
            $('#locker_name').show();
            $('#locker_address').show();
            pluginInstance.close();
        })
    }
    
    /**
     * Initialise component after ajax complete
     */

    (function() {
        const send = XMLHttpRequest.prototype.send
        XMLHttpRequest.prototype.send = function() {
            this.addEventListener('load', function() {
                init();
            })
            return send.apply(this, arguments)
        }
    })()