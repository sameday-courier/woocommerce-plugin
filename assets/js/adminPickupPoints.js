if(document.getElementById('pickupPointCounty')){
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
                beforeSend: function(){
                    cityHtmlElement.innerHTML = '<option value="">Loading...</option>';
                },
                error: () => {
                    alert('Something went wrong! Please try again latter!');
                },
            }
        );
    });
}

if (document.getElementById('thickbox-form')) {
    document.getElementById('thickbox-form').addEventListener('submit', (event) => {
        event.preventDefault();

        const formData = new FormData(event.target);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        jQuery.post({
            url: ajaxurl,
            data: {
                'action': 'send_pickup_point',
                'data': data,
            },
            success: (response) => {
                if (response['success'] === true) {
                    window.location.reload();
                }
            }
        });
    });
}
jQuery('body').on('click', '.delete-pickup-point', function(e){
    e.preventDefault();
    let sameday_id = jQuery(this).attr('data-id');
    jQuery('#form-deletePickupPoint #input-deletePickupPoint').attr('value', sameday_id);
});
if (document.getElementById('form-deletePickupPoint')) {
    document.getElementById('form-deletePickupPoint').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Send the AJAX request
        jQuery.post({
            url: ajaxurl,
            data: {
                'action': 'delete_pickup_point',
                'data': data
            },
            success: function (r) {
                if (r['success'] === true) {
                    window.location.reload();
                    return true;
                }
            }
        });
    });
}
