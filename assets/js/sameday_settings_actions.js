document.addEventListener('DOMContentLoaded', function () {
    const actions = document.querySelector('.sameday-settings-actions');
    const submitRow = document.querySelector('#mainform p.submit');

    if (actions && submitRow) {
        submitRow.insertAdjacentElement('afterend', actions);
    }
});
