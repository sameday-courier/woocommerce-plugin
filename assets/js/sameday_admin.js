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
                    '<div class="notice success is-dismissible"> Cities imported with success ! </div>'
                );
            },
            beforeSend: function(){
                document.body.insertAdjacentHTML("beforeend", "<div class='loading' id='loadingImport'>Loading&#8230;</div>");
            },
            complete: () => {
                document.querySelector("#loadingImport").remove();
            },
            error: (error) => {
                jQuery('#wpbody-content').prepend(
                    '<div class="notice error is-dismissible"> ' + error.responseText + ' </div>'
                );
            },
        }
    );
}

setTimeout(init, 1000);