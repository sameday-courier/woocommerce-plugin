/**
 * Component: Sync and select lockers
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */


    function getLocker(e) {
        fetch(url)
        .then(response => {
            // handle the response
        })
        .catch(error => {
            // handle the error
        });
    }


    function init() {

        /**
         * DOM node selectors.
         */

        let selectors = {
            selectLocker: document.querySelector('#select_locker'),
        };

        selectors.selectLocker.addEventListener('click',getLocker);
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