/**
 * Constants for field types
 */
const FIELD_TYPE_OF_BILLING = 'billing';
const FIELD_TYPE_OF_SHIPPING = 'shipping';

document.addEventListener('DOMContentLoaded', ()=> {
    const formSelector = document.querySelector('form');
    if (!formSelector) {
        return;
    }

    applyCheckoutFilter();
    window.checkoutObserver = new MutationObserver(() => {
        applyCheckoutFilter();
        console.log('S-a schimbat ceva !');
    });

    window.checkoutObserver.observe(formSelector, {
        attributes: true,
        characterData: true,
        childList: true,
        characterDataOldValue: true,
        subtree: true
    });
});

const applyCheckoutFilter = () => {
    const samedayCityFieldBlock = 'samedaycourier-city-block-';

    document.querySelectorAll('[id$="state"]').forEach((stateField) => {
        if (!stateField) {
            return;
        }

        const fieldType = stateField.id.toLowerCase().includes(FIELD_TYPE_OF_BILLING)
            ? FIELD_TYPE_OF_BILLING
            : FIELD_TYPE_OF_SHIPPING;

        const cityField = getCityField(fieldType);
        if (!cityField) {
            return;
        }

        const cityFieldBlockId = samedayCityFieldBlock + cityField.id;
        const cityFieldBlock = document.getElementById(cityFieldBlockId);

        if (!cityFieldBlock) {
            const cityTextInputParent = cityField.parentNode;
            const cityDropdownInput = stateField.parentNode.parentNode.parentNode.cloneNode(true);
            cityDropdownInput.id = cityFieldBlockId;

            implementDropDownField(cityTextInputParent, cityDropdownInput, cityField.id, stateField.id, cityField.value);

            stateField.addEventListener('change', (event) => {
                populateCityField(document.getElementById('dropdown' + cityField.id), event.target);
            });
        }
    });
}

const implementDropDownField = (textElement, dropDownElement, cityId, stateId, cityValue) => {
    Array.from(dropDownElement.querySelectorAll('div, select, label')).forEach((element) => {
        if (element.id === stateId) {
            element.id = 'dropdown' + cityId;

            populateCityField(element, document.getElementById(stateId), cityValue);
        }

        if (element.tagName.toLowerCase() === 'label') {
            element.textContent = 'City';
            element.setAttribute('for', 'dropdown' + cityId);
        }
    });

    textElement.appendChild(dropDownElement);
}

const populateCityField = (cityField, stateField, cityFieldValue) => {
    cityField.textContent = '';
    cityField.appendChild(createOptionElement('', 'Choose city ...'));
    cityField.selectedIndex = cityFieldValue;

    samedayCourierData.cities.filter(city => city.county_code === stateField.value).forEach((city) => {
        cityField.appendChild(createOptionElement(city.city_name, city.city_name, cityFieldValue));
    });
}

const createOptionElement = (value, text, cityFieldValue) => {
    const option = document.createElement('option');
    option.value = value;
    option.setAttribute('data-alternate-values', `[${value}]`);
    if (value === cityFieldValue) {
        option.setAttribute('selected', true);
    }
    option.textContent = text;

    return option;
}

const getCityField = (type) => {
    return Array.from(document.querySelectorAll(`[id^=${type}]`)).find(element => element.id.includes('city'));
}

const updateReactFieldValue = (fieldName, newValue, extraData = {}) => {
    if (window.wp && window.wp.data) {
        try {
            const checkoutStore = window.wp.data.select('wc/store/checkout');
            const checkoutDispatch = window.wp.data.dispatch('wc/store/checkout');

            console.log(window.store);

            if (checkoutStore && checkoutDispatch) {
                let currentAddress = checkoutStore.getShippingAddress();
                if (fieldName.startsWith(FIELD_TYPE_OF_SHIPPING)) {
                    currentAddress = checkoutStore.getBillingAddress();
                }

                console.log(currentAddress);


                if (fieldName.startsWith('shipping')) {
                    const cleanFieldName = fieldName.replace('shipping_', '');
                    const currentAddress = checkoutStore.getShippingAddress();

                    console.log(currentAddress);

                    // Construim noua adresă
                    const newAddress = {
                        ...currentAddress,
                        [cleanFieldName]: newValue
                    };

                    // Adăugăm date extra (cum ar fi city_id)
                    if (Object.keys(extraData).length > 0) {
                        Object.keys(extraData).forEach(key => {
                            newAddress[key] = extraData[key];
                        });
                    }

                    console.log('SamedayCourier: Actualizăm shipping address în React:', newAddress);
                    checkoutDispatch.setShippingAddress(newAddress);
                    return true;
                }

                // Pentru câmpuri de billing (dacă e necesar)
                if (fieldName.startsWith('billing_')) {
                    const cleanFieldName = fieldName.replace('billing_', '');
                    const currentAddress = checkoutStore.getBillingAddress();

                    const newAddress = {
                        ...currentAddress,
                        [cleanFieldName]: newValue
                    };

                    if (Object.keys(extraData).length > 0) {
                        Object.keys(extraData).forEach(key => {
                            newAddress[key] = extraData[key];
                        });
                    }

                    console.log('SamedayCourier: Actualizăm billing address în React:', newAddress);
                    checkoutDispatch.setBillingAddress(newAddress);
                    return true;
                }
            }
        } catch (e) {
            console.warn('SamedayCourier: Eroare la actualizarea state-ului React:', e);
        }
    }
}