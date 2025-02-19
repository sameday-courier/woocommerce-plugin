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
    document.body.insertAdjacentHTML("beforeend", "<div class='loading' id='loadingImport'>Loading&#8230;</div>");
    jQuery.post(
        ajaxurl,
        {'action': 'import_cities'},
        () => {
            document.querySelector("#loadingImport").remove();
        }
    );
}

setTimeout(init, 1000);