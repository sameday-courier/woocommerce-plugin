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

document.getElementById('thickbox-form').addEventListener('submit', function(e){
    e.preventDefault();
    jQuery.post({
        url: ajaxurl,
        data: {
            'action': 'send_pickup_point',
            'data': jQuery(this).serializeArray()
        },
        success: function(r){
            console.log(r);
            // if(r['success'] === true){
            //     window.location.reload();return true;
            // }
        }
    });
});

jQuery('body').on('click', '.delete-pickup-point', function(e){
    e.preventDefault();
    let sameday_id = jQuery(this).attr('data-id');
    console.log(sameday_id);
    jQuery('#form-deletePickupPoint #input-deletePickupPoint').attr('value', sameday_id);
});
document.getElementById('form-deletePickupPoint').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = jQuery(this).serializeArray();
    console.log(sameday_id);
    jQuery.post({
        url: ajaxurl,
        data: {
            'action': 'delete_pickup_point',
            'data': formData
        },
        success: function(r){
            if(r['success'] === true){
                window.location.reload();return true;
            }
        }
    });
});