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
            setCookie("lockerId", message.lockerId, 30);
            $('#locker_name').val(message.name);
            setCookie("locker_name", message.name, 30);
            $('#locker_address').val(message.address);
            setCookie("locker_address", message.address, 30);
            $('#locker_name').show();
            $('#locker_address').show();
            pluginInstance.close();
        })
    }
    

    function checkCookie() {
        let lockerIdcookie = getCookie("lockerId");
        let lockerNamedcookie = getCookie("locker_name");
        let lockerAddresscookie = getCookie("locker_address");
        $('#locker_id').val(lockerIdcookie);
        $('#locker_name').val(lockerNamedcookie);
        $('#locker_address').val(lockerAddresscookie);
        $('#locker_name').show();
        $('#locker_address').show();
       
      }
    /**
     * Initialise component after ajax complete
     */

    (function() {
        const send = XMLHttpRequest.prototype.send
        XMLHttpRequest.prototype.send = function() {
            this.addEventListener('load', function() {
                init();
                checkCookie();
            })
            return send.apply(this, arguments)
        }
    })()
    

    function setCookie(cname,cvalue,exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
      }
      
      function getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for(let i = 0; i < ca.length; i++) {
          let c = ca[i];
          while (c.charAt(0) == ' ') {
            c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
          }
        }
        return "";
      }