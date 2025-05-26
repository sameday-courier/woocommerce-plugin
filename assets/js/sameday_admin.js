/**
 * Component: Sync and select lockers Admin
 * ------------------------------------------------------------------------------
 *
 * @namespace samedayAdmin
 */

function init() {
    let selectors = {
        importAllSameday: document.querySelector('#import_all'),
        importCities: document.querySelector('#import_cities')
    };
    
    selectors.importAllSameday.addEventListener('click', importAllFct);
    selectors.importCities.addEventListener('click', function(e){
        e.preventDefault();
        importCities();
    });
}      

function importAllFct() {
    document.body.insertAdjacentHTML("beforeend", "<div class='loading' id='loadingImport'>Loading&#8230;</div>");
    jQuery.post(
        ajaxurl, 
        {
            'action': 'all_import',
        }, 
        () => {
            document.querySelector("#loadingImport").remove();
        }
    );
}
function importCities() {
    jQuery.post(
        {
            url: ajaxurl,
            data: {
                'action': 'import_cities',
            },
            success: () => {
                jQuery('#wpbody-content').prepend(
                    '<div class="notice notice-success is-dismissible" role="alert">' +
                        '<p>Cities imported with success !</p>' +
                    '</div>'
                );
            },
            beforeSend: () => {
                document.body.insertAdjacentHTML("beforeend", "<div class='loading' id='loadingImport'>Loading&#8230;</div>");
            },
            complete: () => {
                document.querySelector("#loadingImport").remove();
                jQuery(window).scrollTop(0);
            },
            error: (error) => {
                jQuery('#wpbody-content').prepend(
                    '<div class="notice error is-dismissible"><p>' + error.responseText + '</p></div>'
                );
            },
        }
    );
}

setTimeout(init, 1000);