if (document.getElementById('pickupPointCounty')) {
    const countySelect = document.getElementById('pickupPointCounty');
    const citySelect = document.getElementById('pickupPointCity');

    const requestOptions = (action, params = {}) => ({
        url: ajaxurl,
        data: {
            action: action,
            _wpnonce: samedayPickupPointsAdmin.nonces[action],
            ...params,
        },
    });

    const populateSelectOptions = (selectElement, items, placeholder = '') => {
        selectElement.innerHTML = '';

        if ('' !== placeholder) {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        }

        items.forEach((item) => {
            selectElement.innerHTML += `<option value="${item.id}">${item.name}</option>`;
        });
    };

    const handleRequestError = () => {
        alert('Something went wrong! Please try again latter!');
    };

    jQuery.post({
        ...requestOptions('get_counties'),
        beforeSend: () => {
            countySelect.innerHTML = '<option value="">Loading...</option>';
            countySelect.disabled = true;
        },
        success: (counties) => {
            populateSelectOptions(countySelect, counties, 'Choose County');
            countySelect.disabled = false;
        },
        error: handleRequestError,
    });

    countySelect.addEventListener('change', (event) => {
        if ('' === event.target.value) {
            citySelect.innerHTML = '';
            citySelect.disabled = true;

            return;
        }

        jQuery.post({
            ...requestOptions('get_cities', { countyId: event.target.value }),
            beforeSend: () => {
                citySelect.innerHTML = '<option value="">Loading...</option>';
                citySelect.disabled = true;
            },
            success: (cities) => {
                populateSelectOptions(citySelect, cities);
                citySelect.disabled = false;
            },
            error: handleRequestError,
        });
    });
}

jQuery('body').on('click', '.delete-pickup-point', function (e) {
    e.preventDefault();
    jQuery('#form-deletePickupPoint #input-deletePickupPoint').val(jQuery(this).data('id'));
});
