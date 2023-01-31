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
    
    document.getElementById('samedaycourier-service').addEventListener('change', function() {
        let samedayCodeOption = this.selectedOptions[0].getAttribute("data-samedayCode");
        let PdoEligible = this.selectedOptions[0].getAttribute("data-eligible");
        if(samedayCodeOption == 'LN'){
            document.getElementById('eligibleToLockerFirstMile').style.display = 'none';
            document.getElementById('displayLocker').style.display = 'table-row';
        }else if(samedayCodeOption == '24'){
            document.getElementById('displayLocker').style.display = 'none';
            if(PdoEligible == 'true'){
                document.getElementById('eligibleToLockerFirstMile').style.display = 'table-row';
            }
        }
    });
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