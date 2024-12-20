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
        jQuery.post({
            url: ajaxurl,
            data: {
                'action': 'send_pickup_point',
                'data': jQuery(this).serializeArray()
            },
            success: function(r){
                if(r['success'] === true){
                    window.location.reload();return true;
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

        // Check the 'default' checkbox value, if checked set to 1, otherwise set to 0
        var defaultChecked = document.getElementById('pickupPointDefault').checked ? 1 : 0;

        // Manually add the 'default' value to formData
        formData.push({ name: 'default', value: defaultChecked });

        // Send the AJAX request
        jQuery.post({
            url: ajaxurl,
            data: {
                'action': 'send_pickup_point',
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
