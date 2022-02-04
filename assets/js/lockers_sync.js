/**
 * Component: Sync and select lockers
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

    function init() {
        
        /* DOM node selectors. */
        
        let selectors = {
            selectLocker: document.querySelector('#select_locker'),
        };

        selectors.selectLocker.addEventListener('click',openLockers);
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