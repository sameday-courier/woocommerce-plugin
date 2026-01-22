jQuery(document).ready(() => {
    const $ = jQuery;
    let citySelectElements = {};
    let originalCityFields = {};
    let lastAppliedKey = {};

    const restoreCityInput = (fieldType) => {
        const currentCityField = getFieldByType('city', fieldType);
        const selectEl = citySelectElements[fieldType];

        if (selectEl && $(selectEl).data('select2')) {
            $(selectEl).select2('destroy');
        }

        if (currentCityField && currentCityField.tagName && currentCityField.tagName.toLowerCase() === 'select') {
            let restoredField = originalCityFields[fieldType];
            if (!restoredField) {
                return;
            }

            restoredField = restoredField.cloneNode(true);
            restoredField.value = currentCityField.value || restoredField.value || '';
            currentCityField.replaceWith(restoredField);
        }
    }

    const updateCities = (cityField, stateCode, countryCode, fieldType) => {
        if (!cityField || !stateCode || !countryCode) {
            restoreCityInput(fieldType);
            return;
        }

        const key = `${countryCode}:${stateCode}`;
        if (
            lastAppliedKey[fieldType] === key &&
            cityField.tagName &&
            cityField.tagName.toLowerCase() === 'select'
        ) {
            if (!$(cityField).data('select2')) {
                $(cityField).select2();
            }
            return;
        }

        let cities = samedayCourierData?.cities?.[countryCode]?.filter(city => city.county_code === stateCode) ?? [];

        if (cities.length > 0) {
            if (cityField.tagName && cityField.tagName.toLowerCase() === 'select') {
                citySelectElements[fieldType] = cityField;
            }

            if (!citySelectElements[fieldType] || !document.body.contains(citySelectElements[fieldType])) {
                citySelectElements[fieldType] = document.createElement("select");
                citySelectElements[fieldType].setAttribute("id", cityField.getAttribute('id'));
                citySelectElements[fieldType].setAttribute("name", cityField.getAttribute('name') || cityField.getAttribute('id'));
                citySelectElements[fieldType].setAttribute("class", "form-row-wide select2-city city_select");
            }

            populateCityField(cities, citySelectElements[fieldType], cityField);
            lastAppliedKey[fieldType] = key;
        } else {
            restoreCityInput(fieldType);
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
        const currentValue = cityField.value || '';

        citySelectElement.textContent = "";
        citySelectElement.appendChild(createOptionElement("", "Choose a city"));
        cities.forEach((city) => {
            citySelectElement.appendChild(createOptionElement(city.city_name, city.city_name, currentValue));
        });

        // Replace input with select (classic checkout)
        if (cityField.tagName && cityField.tagName.toLowerCase() === 'input') {
            cityField.replaceWith(citySelectElement);
        }

        if ($(citySelectElement).data('select2')) {
            $(citySelectElement).select2('destroy');
        }
        $(citySelectElement).select2();
    }

    jQuery(document.body).on('updated_checkout', () => {
        [FIELD_TYPE_OF_BILLING, FIELD_TYPE_OF_SHIPPING].forEach((fieldType) => {
            let formElements = {
                country: $(getFieldByType('country', fieldType)),
                state: $(getFieldByType('state', fieldType)),
                city: $(getFieldByType('city', fieldType)),
            };

            if (formElements.city.length === 0) {
                return;
            }

            if (!originalCityFields[fieldType]) {
                originalCityFields[fieldType] = formElements.city[0].cloneNode(true);
            }

            if (formElements.state.length > 0) {
                formElements.state.off('change.samedayCities');
                formElements.state.on('change.samedayCities', (event) => {
                    updateCities(getFieldByType('city', fieldType), event.target.value, formElements.country.val(), fieldType);
                });
            }

            if (formElements.country.length > 0) {
                formElements.country.off('change.samedayCities');
                formElements.country.on('change.samedayCities', () => {
                    const stateCode = formElements.state.length > 0 ? formElements.state.val() : '';
                    if (!stateCode) {
                        restoreCityInput(fieldType);
                        return;
                    }

                    updateCities(getFieldByType('city', fieldType), stateCode, formElements.country.val(), fieldType);
                });
            }

            // Initial (also useful after updated_checkout)
            const initialState = formElements.state.length > 0 ? formElements.state.val() : '';
            if (initialState) {
                const currentCityField = getFieldByType('city', fieldType);
                const countryCode = formElements.country.val();
                const key = `${countryCode}:${initialState}`;

                const isAlreadySelect = currentCityField && currentCityField.tagName && currentCityField.tagName.toLowerCase() === 'select';
                if (!(isAlreadySelect && lastAppliedKey[fieldType] === key)) {
                    updateCities(currentCityField, initialState, countryCode, fieldType);
                } else if (!$(currentCityField).data('select2')) {
                    $(currentCityField).select2();
                }
            } else {
                restoreCityInput(fieldType);
            }
        });
    });
});