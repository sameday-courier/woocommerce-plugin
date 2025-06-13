document.addEventListener('DOMContentLoaded', ()=> {
    setInterval(()=> {
        applyCheckoutFilter();
    }, 10);
});

const applyCheckoutFilter = () => {
    const shippingCityId = 'shipping-city';
    const billingCityId = 'billing-city';
    const shippingStateId = 'shipping-state';
    const billingStateId = 'billing-state';

    const shippingStateField = document.getElementById(shippingStateId);
    const shippingCityField = document.getElementById(shippingCityId);

    const billingStateField = document.getElementById(billingStateId);
    const billingCityField = document.getElementById(billingCityId);

    const samedayCityFieldBlock = 'samedaycourier-city-block-';

    [shippingStateField, billingStateField].forEach((stateField) => {
        if (null === stateField) {
            return;
        }

        let cityField;
        if (stateField.id === billingStateId) {
            cityField = billingCityField;
        } else {
            cityField = shippingCityField;
        }

        const cityFieldBlock = document.getElementById(samedayCityFieldBlock + cityField.id);
        if (null === cityFieldBlock) {
            let cityTextElement = cityField.parentNode;
            const cityDropdownElement = stateField.parentNode.parentNode.parentNode.cloneNode(true);
            cityDropdownElement.id = samedayCityFieldBlock + cityField.id;

            implementDropDownField(cityTextElement, cityDropdownElement, cityField.id, stateField.id);

            stateField.addEventListener('change', (event) => {
                populateCityField(document.getElementById(cityField.id), event.target);
            });
        }
    });
}

const implementDropDownField = (textElement, dropDownElement, cityId, stateId) => {
    Array.from(dropDownElement.querySelectorAll('div, select, label')).forEach((element) => {
        if (element.id === stateId) {
            element.id = cityId;

            populateCityField(element, document.getElementById(stateId));
        }

        if (element.tagName.toLowerCase() === 'label') {
            element.innerHTML = 'City';
            element.setAttribute('for', cityId);
        }
    });

    textElement.replaceWith(dropDownElement);
}

const populateCityField = (cityField, stateField) => {
    cityField.innerHTML = '';
    cityField.appendChild(createOptionElement('', 'Choose city ...'));
    samedayCourierData.cities.filter(city => city.county_code === stateField.value).forEach((city) => {
        cityField.appendChild(createOptionElement(city.city_name, city.city_name));
    });
}

const createOptionElement = (value, text) => {
    let option = document.createElement('option');
    option.value = value;
    option.innerHTML = text;

    return option;
}