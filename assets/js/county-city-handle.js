jQuery(document).ready(() => {
    const $ = jQuery;
    let citySelectElements = {};

    [FIELD_TYPE_OF_BILLING, FIELD_TYPE_OF_SHIPPING].forEach((fieldType) => {
        let formElements = {
            country: $(getFieldByType('country', fieldType)),
            state: $(getFieldByType('state', fieldType)),
            city: $(getFieldByType('city', fieldType)),
        };

        if (undefined !== formElements.state && formElements.state.length > 0) {
            formElements.state.on('change', (event) => {
                updateCities(formElements.city[0], event.target.value, formElements.country.val(), fieldType);
            });
        }
    });

    const updateCities = (cityField, stateCode, countryCode, fieldType) => {
        let cities = samedayCourierData.cities[countryCode]?.filter(city => city.county_code === stateCode) ?? [];
        if (cities.length > 0) {
            if (undefined !== citySelectElements[fieldType] && citySelectElements[fieldType].length > 0) {
                populateCityField(cities, citySelectElements[fieldType], cityField);
            } else {
                citySelectElements[fieldType] = document.createElement("select");
                citySelectElements[fieldType].setAttribute("id", cityField.getAttribute('id'));
                citySelectElements[fieldType].setAttribute("name", cityField.getAttribute('id'));
                citySelectElements[fieldType].setAttribute("class", "form-row-wide select2-city city_select");

                populateCityField(cities, citySelectElements[fieldType], cityField);
            }
        } else {
            if (undefined !== citySelectElements[fieldType] && citySelectElements[fieldType].length > 0) {
                if ($(citySelectElements[fieldType]).data('select2')) {
                    $(citySelectElements[fieldType]).select2('destroy');
                }

                citySelectElements[fieldType].replaceWith(cityField);
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
        $(citySelectElement).select2();
    }
});