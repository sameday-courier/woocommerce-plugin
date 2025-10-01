function waitForElement(selector, callback, intervalTime = 100, timeout = 10000) {
    const startTime = Date.now();

    const interval = setInterval(function() {
        let element = document.querySelector(selector);

        if (element) {
            clearInterval(interval); // Stop the interval when the element is found
            callback(element);
        } else if (Date.now() - startTime > timeout) {
            clearInterval(interval); // Stop the interval after a timeout to prevent infinite loops
        }
    }, intervalTime);
}

function checkShippingMethod() {
    const shippingMethod = document.querySelector("input[type='radio'][id*='samedaycourier:15:LN']");
    const shippingMethodC = document.querySelector("input[type='radio'][id*='samedaycourier15ln']");
    const lockerButton = document.getElementById('select_locker');
    const shippingAddressSpan = document.querySelector('.wc-block-components-shipping-address');
    if (!lockerButton) {
        return;
    }
    let lockerData = null;
    const lockerCookie = typeof _getCookie === 'function' ? _getCookie('locker') : null;
    if (typeof lockerCookie === 'string' && lockerCookie.trim() !== '') {
        lockerData = JSON.parse(lockerCookie);
    }
    const methodChecked = (shippingMethod && shippingMethod.checked) || (shippingMethodC && shippingMethodC.checked);
    lockerButton.style.display = methodChecked ? 'inline-block' : 'none';
    if (shippingAddressSpan) {
        if (lockerData && lockerData.address) {
            shippingAddressSpan.innerText = lockerData.address;
            shippingAddressSpan.style.display = methodChecked ? 'block' : 'none';
        } else {
            shippingAddressSpan.style.display = 'none';
        }
    }
}

// Attach event listener to all radio inputs (shipping methods)
function addShippingMethodListeners() {
    let shippingMethods = document.querySelectorAll("input[type='radio'][name*='radio-control-']");
    shippingMethods.forEach(function (radio) {
        radio.addEventListener('change', checkShippingMethod);
    });
}

// Run the function to check the state when the page loads
document.addEventListener('DOMContentLoaded', function () {
    addShippingMethodListeners();
    const button_select_locker =  document.getElementById("select_locker") || false;
    if(button_select_locker === false){
        setTimeout(function(){
            checkShippingMethod();
        }, 2000);
    }
     // Initial check to see if the specific shipping method is already selected
});

// Dynamic selector to match both patterns
const inputSelector = "input[id*='samedaycourier:15:LN'], input[id*='samedaycourier:30:XL']";
// Use waitForElement to dynamically add the locker button when the shipping method label is found
waitForElement(inputSelector, function(label) {
    let parent = label.closest('div');
    if (parent) {
        // Create the locker button dynamically
        let buttonHTML = `
            <button type="button" class="button alt sameday_select_locker"
                id="select_locker"
                style="display: none;"
                data-username="${samedayData.username}"
                data-country="${samedayData.country}">
                ${samedayData.buttonText}
            </button>
        `;
        let buttonHTMLError = '<div id="placeOrderError">Please choose an easybox</div>';

        parent.insertAdjacentHTML('beforeend', buttonHTML);
        parent.insertAdjacentHTML('beforeend', buttonHTMLError);

        if (typeof window['LockerPlugin'] !== 'undefined') {
            document.getElementById('select_locker').addEventListener('click', function() {
                _openLockers();
            });
        } else {
            console.error("LockerPlugin is undefined or not loaded yet.");
        }

        // Now that the button exists, add listeners to the radio inputs and check if the method is selected
        addShippingMethodListeners();
        checkShippingMethod(); // Check the state initially on page load
    } else {
        console.log("Parent container not found");
    }

});

waitForElement('.wc-block-components-checkout-place-order-button', function($target){
    $target.addEventListener('click', function(e){

        let lockerData = _getCookie('locker');
        if(!lockerData.length
            && (jQuery('input[id*="samedaycourier:15:LN"]:checked').length
                || jQuery('input[id*="samedaycourier:30:XL"]:checked').length)
        ) {
            e.preventDefault();
            e.stopPropagation();
            jQuery('#placeOrderError').addClass('show');
            document.getElementById('select_locker').scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });
            setTimeout(function(){
                jQuery('#placeOrderError').removeClass('show');
            }, 3000);
        }
    });
});


const _getCookie = (key) => {
    let cookie = '';
    document.cookie.split(';').forEach(function (value) {
        if (value.split('=')[0].trim() === key) {
            return cookie = value.split('=')[1];
        }
    });

    return cookie;
}