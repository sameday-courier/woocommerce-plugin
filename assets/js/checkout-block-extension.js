/**
 * Constants for field types
 */
const FIELD_TYPE_OF_BILLING = 'billing';
const FIELD_TYPE_OF_SHIPPING = 'shipping';

document.addEventListener('DOMContentLoaded', ()=> {
    setInterval(()=> {
        applyCheckoutFilter();
    }, 10);
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
            const cityTextElement = cityField.parentNode;
            const cityDropdownElement = stateField.parentNode.parentNode.parentNode.cloneNode(true);
            cityDropdownElement.id = cityFieldBlockId;

            implementDropDownField(cityTextElement, cityDropdownElement, cityField.id, stateField.id, cityField.value);

            stateField.addEventListener('change', (event) => {
                populateCityField(document.getElementById(cityField.id), event.target);
            });
        }
    });
}

const implementDropDownField = (textElement, dropDownElement, cityId, stateId, cityValue) => {
    Array.from(dropDownElement.querySelectorAll('div, select, label')).forEach((element) => {
        if (element.id === stateId) {
            element.id = cityId;

            const state = document.getElementById(stateId);

            populateCityField(element, state, cityValue);

            element.addEventListener('change', () => {
                const checkoutForm = document.querySelector('form.checkout');
                if (checkoutForm) {
                    checkoutForm.dispatchEvent(new Event('change', { bubbles: true }));
                    console.log('Ai modificat formularul');
                }
            });
        }

        if (element.tagName.toLowerCase() === 'label') {
            element.textContent = 'City';
            element.setAttribute('for', cityId);
        }
    });

    textElement.replaceWith(dropDownElement);
}

const populateCityField = (cityField, stateField, cityFieldValue) => {
    cityField.textContent = '';
    cityField.appendChild(createOptionElement('', 'Choose city ...'));
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