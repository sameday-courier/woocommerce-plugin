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
    if(selectors.selectLockerMap){
        selectors.selectLockerMap.addEventListener('click', openLockers);
        changeServices();
    }

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
    const clientId = "b8cb2ee3-41b9-4c3d-aafe-1527b453d65e"; // each integrator will have unique clientId
    const changeLockerButton = document.querySelector('#select_locker');

    let samedayUser = changeLockerButton.getAttribute('data-username'); // Sameday username
    let countryCode= changeLockerButton.getAttribute('data-country'); // correspond to user eAWB instance
    let langCode = countryCode.toLowerCase(); //language of the plugin
    let destCity = changeLockerButton.getAttribute('data-dest_city');
    let destCountry = changeLockerButton.getAttribute('data-dest_country');

    const LockerData = {
        apiUsername: samedayUser,
        city: destCity,
        countryCode: destCountry,
        clientId: clientId,
        langCode: langCode
    }

    window['LockerPlugin'].init(LockerData);
    let pluginInstance = window['LockerPlugin'].getInstance();

    pluginInstance.open();

    pluginInstance.subscribe((locker) => {
        pluginInstance.close();

        let _locker = JSON.stringify(locker);

        jQuery.post(
            {
                url: ajaxurl,
                data: {
                    'action': 'change_locker',
                    'orderId': jQuery('#samedaycourier-order-id').val(),
                    'locker': _locker,
                },
                success: () => {
                    document.querySelector('#sameday_locker_name').innerHTML = locker.name + " - " + locker.address;
                    document.querySelector('#locker_id').value = _locker;
                },
                error: () => {
                    alert('Something went wrong! Please try again latter!');
                },
            }
        );
    });
}

setTimeout(init, 1000);