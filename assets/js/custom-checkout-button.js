function waitForElement(selector, callback, intervalTime = 100, timeout = 10000) {
    const startTime = Date.now();

    const interval = setInterval(function() {
        let element = document.querySelector(selector);

        if (element) {
            clearInterval(interval); // Stop the interval when the element is found
            callback(element);
        }
    }, intervalTime);
}

// Function to check if the shipping method is selected
function checkShippingMethod() {
    // Select the radio input with a partial ID 'samedaycourier:15:LN'
    let shippingMethod = document.querySelector("input[type='radio'][id*='samedaycourier\\:15\\:LN']");
    let shippingMethodC = document.querySelector("input[type='radio'][id*='samedaycourier15ln']");
    let lockerButton = document.getElementById('select_locker');

    // Ensure both the shipping method and button exist before proceeding
    if (lockerButton) {
        const shipping_address_span = document.querySelector('.wc-block-components-shipping-address') || false;
        if ((shippingMethod && shippingMethod.checked) || (shippingMethodC && shippingMethodC.checked)) {
            lockerButton.style.display = 'block';  // Show the locker button
            shipping_address_span.style.display = 'block';
        } else {
            lockerButton.style.display = 'none';   // Hide the locker button
            shipping_address_span.style.display = 'none';
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
        parent.insertAdjacentHTML('beforeend', buttonHTML);

        // Check if LockerPlugin is available before adding the event listener
        if (typeof window['LockerPlugin'] !== 'undefined') {
            document.getElementById('select_locker').addEventListener('click', function() {
                _openLockers();// Make sure LockerPlugin is initialized
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
