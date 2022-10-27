/**
 * Component: Sync and select lockers Admin
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

function init(){
    let selectors = {
        selectLockerMap: document.querySelector('#select_locker')
    };
    
    selectors.selectLockerMap.addEventListener('click', openLockers);
}      

function openLockers(){

        /* DOM node selectors. */
        const clientId="b8cb2ee3-41b9-4c3d-aafe-1527b453d65e";//each integrator will have unique clientId
        const countryCode= document.querySelector('#select_locker').getAttribute('data-country'); //country for which the plugin is used
        const langCode= document.querySelector('#select_locker').getAttribute('data-country').toLowerCase(); //language of the plugin
        const samedayUser = document.querySelector('#select_locker').getAttribute('data-username').toLowerCase(); //sameday username
        window['LockerPlugin'].init({ clientId: clientId, countryCode: countryCode, langCode: langCode, apiUsername: samedayUser });
        let pluginInstance = window['LockerPlugin'].getInstance();

        pluginInstance.open();

        pluginInstance.subscribe((message) => {
            let lockerDetails = {};
            lockerDetails.id = message.lockerId;
            lockerDetails.name  = message.name;
            lockerDetails.address = message.address;

            pluginInstance.close();
            document.querySelector('#sameday_locker_name').innerHTML = message.name + " - " +message.address;
            let searchParams = new URLSearchParams(window.location.search)

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: "action=updateLocker&id="+searchParams.get('post') + "&lockerId=" + message.lockerId,  
                success: function(msg){
          
                }
            });
        })

}

setTimeout(init, 2000);