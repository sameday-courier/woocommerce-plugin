if(document.getElementById('pickupPointCounty')){
    document.getElementById('pickupPointCounty').addEventListener('change', function(event){
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

if(document.getElementById('thickbox-form')){
    document.getElementById('thickbox-form').addEventListener('submit', function(e){
        e.preventDefault();


        let pickupPointFields = [];
        const formData = new FormData(document.getElementById('thickbox-form'));
        for (const [key, value] of formData) {
            pickupPointFields[key] = value;
        }

        let objectFields = Object.assign({}, pickupPointFields);

        // Send the AJAX request
        jQuery.post({
            url: ajaxurl,
            data: {
                'action': 'send_pickup_point',
                'data': objectFields
            },
            success: function (r) {
                if (r['success'] === true) {

                    window.location.reload();
                    return true;
                } else {
                    console.log(r.data);
                }
            }
        });
    });
}
jQuery('body').on('click', '.delete-pickup-point', function(e){
    e.preventDefault();
    let sameday_id = jQuery(this).attr('data-id');
    console.log(sameday_id);
    jQuery('#form-deletePickupPoint #input-deletePickupPoint').attr('value', sameday_id);
});
if (document.getElementById('form-deletePickupPoint')) {
    document.getElementById('form-deletePickupPoint').addEventListener('submit', function (e) {
        e.preventDefault();

        let formData = jQuery(this).serializeArray();  // Serialize the form data into an array
        console.log(sameday_id);

        // Send the AJAX request
        jQuery.post({
            url: ajaxurl,
            data: {
                'action': 'delete_pickup_point',
                'data': formData
            },
            success: function (r) {
                if (r['success'] === true) {

                    window.location.reload();
                    return true;
                } else {
                    console.log(r.data); // Log the response data for debugging
                }
            }
        });
    });
}
