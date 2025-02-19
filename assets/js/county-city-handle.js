jQuery(document).ready(function(){
    jQuery('#billing_state').on('change', (element) => {
        let countyCode = element.target.value;
        jQuery.ajax({
            url: woocommerce_params.ajax_url,
            type: 'POST',
            data: {'action': 'getCities', 'countyCode': countyCode},
            success: (result) => {
                let arr = jQuery.parseJSON(result);
                let select = jQuery('#billing_city');
                select.html('');
                let html = '';
                arr.forEach(city => {
                    html += '<option value="' + `${city['city_name']}` + '">' + `${city['city_name']}` + '</option>';
                });
                select.html(html);
            }
        });
    });
});