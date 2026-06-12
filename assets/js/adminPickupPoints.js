if (document.getElementById('pickupPointCounty')) {
    document.getElementById('pickupPointCounty').addEventListener('change', (event) => {
        let cityHtmlElement = document.getElementById('pickupPointCity');
        jQuery.post(
            {
                url: ajaxurl,
                data: {
                    'action': 'change_counties',
                    'countyId': event.target.value,
                },
                success: (result) => {
                    cityHtmlElement.innerHTML = '';
                    result.forEach((city) => {
                        cityHtmlElement.innerHTML += '<option value="' + city.id + '">' + city.name + '</option>';
                    });
                    cityHtmlElement.disabled = false;
                },
                beforeSend: function () {
                    cityHtmlElement.innerHTML = '<option value="">Loading...</option>';
                },
                error: () => {
                    alert('Something went wrong! Please try again latter!');
                },
            }
        );
    });
}

const serializeFormData = (form) => {
    const data = {};
    new FormData(form).forEach((value, key) => {
        data[key] = value;
    });

    return data;
};

const submitFormAsync = (form, action) => {
    const data = serializeFormData(form);

    jQuery.post(ajaxurl, {
        action: action,
        _wpnonce: data._wpnonce,
        data: data,
    });
};

document.querySelectorAll('form[data-url]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        submitFormAsync(form, form.dataset.url);
    });
});

jQuery('body').on('click', '.delete-pickup-point', function (e) {
    e.preventDefault();
    jQuery('#form-deletePickupPoint #input-deletePickupPoint').val(jQuery(this).data('id'));
});
