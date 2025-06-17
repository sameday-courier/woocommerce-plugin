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
        if (null === stateField) {
            return;
        }

        let cityField;
        if (true === stateField.id.toLowerCase().includes(FIELD_TYPE_OF_BILLING)) {
            cityField = getCityField(FIELD_TYPE_OF_BILLING);
        } else {
            cityField = getCityField(FIELD_TYPE_OF_SHIPPING);
        }

        let cityFieldBlock = document.getElementById(samedayCityFieldBlock + cityField.id);
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

const getCityField = (type) => {
    return Array.from(document.querySelectorAll(`[id^=${type}]`)).find(element => element.id.includes('city'));
}