jQuery(document).ready(function(){
    jQuery('#billing_state').on('change', (element) => {
        updateCities(jQuery('#billing_city'), element.target.value);
    });

    jQuery('#shipping_state').on('change', (element) => {
        updateCities(jQuery('#shipping_city'), element.target.value);
    });

    const updateCities = (selector, countyCode) => {
        jQuery.ajax({
            url: woocommerce_params.ajax_url,
            type: 'POST',
            data: {'action': 'getCities', 'countyCode': countyCode},
            success: (result) => {
                selector.html('');
                let html = '';
                result.forEach(city => {
                    html += '<option value="' + `${city['city_name']}` + '">' + `${city['city_name']}` + '</option>';
                });
                selector.html(html);
            }
        });
    }
});