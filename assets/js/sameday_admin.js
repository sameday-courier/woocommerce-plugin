/**
 * Component: Sync and select lockers Admin
 * ------------------------------------------------------------------------------
 *
 * @namespace samedayAdmin
 */

function init() {
    let selectors = {
        importAllSameday: document.querySelector('#import_all')
    };
    
    selectors.importAllSameday.addEventListener('click', importAllFct);
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

setTimeout(init, 1000);