jQuery(document).ready(() => {
    const $ = jQuery;
    let citySelectElement;

    [FIELD_TYPE_OF_BILLING, FIELD_TYPE_OF_SHIPPING].forEach((fieldType) => {
        let formElements = {
            country: $(getFieldByType('country', fieldType)),
            state: $(getFieldByType('state', fieldType)),
            city: $(getFieldByType('city', fieldType)),
        };

        if (undefined !== formElements.state && formElements.state.length > 0) {
            formElements.state.on('change', (event) => {
                updateCities(formElements.city[0], event.target.value, formElements.country.val());
            });
        }
    });

    const updateCities = (cityField, stateCode, countryCode) => {
        let cities = samedayCourierData.cities[countryCode].filter(city => city.county_code === stateCode);
        if (cities.length > 0) {
            if (undefined !== citySelectElement && citySelectElement.length > 0) {
                populateCityField(cities, citySelectElement, cityField);
            } else {
                citySelectElement = document.createElement("select");
                citySelectElement.setAttribute("id", cityField.getAttribute('id'));
                citySelectElement.setAttribute("name", cityField.getAttribute('id'));
                citySelectElement.setAttribute("class", "form-row-wide select2-city city_select");

                populateCityField(cities, citySelectElement, cityField);
            }
        } else {
            if (undefined !== citySelectElement && citySelectElement.length > 0) {
                citySelectElement.replaceWith(cityField);
            }
        }
    }

    const createOptionElement = (value, text, cityFieldValue = null) => {
        const option = document.createElement('option');
        option.value = value;
        option.setAttribute('data-alternate-values', `[${value}]`);
        if (value === cityFieldValue) {
            option.setAttribute('selected', true);
        }
        option.textContent = text;

        return option;
    }

    const populateCityField = (cities, citySelectElement, cityField) => {
        citySelectElement.textContent = "";
        citySelectElement.appendChild(createOptionElement("", "Choose a city"));
        cities.forEach((city) => {
            citySelectElement.appendChild(createOptionElement(city.city_name, city.city_name, cityField.value));
        });

        cityField.replaceWith(citySelectElement);
    }
});