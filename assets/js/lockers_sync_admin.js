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
    changeServices();
}      


function changeServices(){
    let samedayCourierService = document.getElementById('samedaycourier-service');
    
    let optionFistMile = samedayCourierService.selectedOptions[0].getAttribute("data-fistMile");
    let optionLastMile = samedayCourierService.selectedOptions[0].getAttribute("data-lastMile");
    
    displayDetails(optionFistMile, optionLastMile);
   
    samedayCourierService.addEventListener('change', function() {
        let optionFistMile = this.selectedOptions[0].getAttribute("data-fistMile");
        let optionLastMile = this.selectedOptions[0].getAttribute("data-lastMile");
        displayDetails(optionFistMile, optionLastMile);
    });
}

function displayDetails(optionFistMile, optionLastMile){
    document.getElementById("LockerFirstMile").className = '';
    document.getElementById("LockerLastMile").className = '';
    document.getElementById("LockerFirstMile").classList.add(optionFistMile);
    document.getElementById("LockerLastMile").classList.add(optionLastMile);
}
function openLockers() {
        /* DOM node selectors. */
        const clientId="b8cb2ee3-41b9-4c3d-aafe-1527b453d65e"; // each integrator will have unique clientId
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
            document.querySelector('#locker_id').value = JSON.stringify(lockerDetails);
        })

}

setTimeout(init, 2000);