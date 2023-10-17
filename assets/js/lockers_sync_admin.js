/**
 * Component: Sync and select lockers Admin
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

function init() {
    let selectors = {
        selectLockerMap: document.querySelector('#select_locker')
    };
    
    selectors.selectLockerMap.addEventListener('click', openLockers);
    changeServices();
}      


function changeServices(){
    let samedayCourierService = document.getElementById('samedaycourier-service');

    if ("undefined" !== typeof samedayCourierService.selectedOptions[0]) {
        let optionFistMile = samedayCourierService.selectedOptions[0].getAttribute("data-fistMile");
        let optionLastMile = samedayCourierService.selectedOptions[0].getAttribute("data-lastMile");

        displayDetails(optionFistMile, optionLastMile);

        samedayCourierService.addEventListener('change', function() {
            let optionFistMile = this.selectedOptions[0].getAttribute("data-fistMile");
            let optionLastMile = this.selectedOptions[0].getAttribute("data-lastMile");
            displayDetails(optionFistMile, optionLastMile);
        });
    }
}

function displayDetails(optionFistMile, optionLastMile){
    document.getElementById("samedaycourier-locker_first_mile").checked = false;
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

    pluginInstance.subscribe((locker) => {
        let lockerDetails = {};
        lockerDetails.id = locker.lockerId;
        lockerDetails.name  = locker.name;
        lockerDetails.address = locker.address;

        jQuery.post(
            {
                url: ajaxurl,
                data: {
                    'action': 'change_locker',
                    'orderId': jQuery('#samedaycourier-order-id').val(),
                    'locker': JSON.stringify({
                        'id': locker.lockerId,
                        'name': locker.name,
                        'address': locker.address,
                        'city': locker.city,
                        'county': locker.county,
                        'postalCode': locker.postalCode,
                    }),
                },
                complete: () => {
                    pluginInstance.close();
                },
                success: () => {
                    document.querySelector('#sameday_locker_name').innerHTML = locker.name + " - " + locker.address;
                    document.querySelector('#locker_id').value = JSON.stringify(lockerDetails);
                },
                error: () => {
                    alert('Something went wrong! Please try again latter!');
                },
            }
        );
    });
}

setTimeout(init, 1000);