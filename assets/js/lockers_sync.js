/**
 * Component: Sync and select lockers
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

    function init() {
        
        /* DOM node selectors. */
        
        let selectors = {
            selectLockerMap: document.querySelector('#select_locker'),
            selectLocker: document.querySelector('#shipping-pickup-store-select'),
            lockerId: document.querySelector('#locker_id')
        };

        /* Add select2 to lockers dropdown. */
        jQuery('select#shipping-pickup-store-select').select2();

        /* Map Event. */
        
        if (typeof( selectors.selectLockerMap) != 'undefined' && selectors.selectLockerMap != null){
            selectors.selectLockerMap.addEventListener('click',openLockers);
        }else if (typeof( selectors.selectLocker) != 'undefined' && selectors.selectLocker != null){
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
            inputCity: document.querySelector('#select_locker'),
        };

        window.LockerPlugin.init();
        let plugin = window.LockerPlugin.getInstance();
        plugin.open();

        plugin.subscribe((message) => {
            selectors.lockerId.value = message.lockerId;
            plugin.close();
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