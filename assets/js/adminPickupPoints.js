(function ($) {
    'use strict';

    if (!document.getElementById('pickupPointCounty')) {
        return;
    }

    const labels = samedayPickupPointsAdmin.labels;
    const $modal = $('#sameday-pickup-point-modal');

    const select2Options = {
        dropdownParent: $modal,
        width: '100%',
    };

    const requestOptions = (action, params = {}) => ({
        url: ajaxurl,
        data: {
            action: action,
            _wpnonce: samedayPickupPointsAdmin.nonces[action],
            ...params,
        },
    });

    const populateSelectOptions = (selectElement, items, placeholder = '') => {
        selectElement.innerHTML = '';

        if ('' !== placeholder) {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        }

        items.forEach((item) => {
            selectElement.innerHTML += `<option value="${item.id}">${item.name}</option>`;
        });
    };

    const createSelect2Field = (selector) => {
        const $select = $(selector);

        const destroy = () => {
            if ($select.data('select2')) {
                $select.select2('destroy');
            }
        };

        const init = () => {
            destroy();
            $select.select2(select2Options);
        };

        const setDisabled = (disabled) => {
            $select.prop('disabled', disabled);

            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
        };

        const refresh = (items, placeholder, disabled) => {
            destroy();
            populateSelectOptions($select[0], items, placeholder);
            init();
            setDisabled(disabled);
        };

        return {
            $select,
            refresh,
        };
    };

    const countyField = createSelect2Field('#pickupPointCounty');
    const cityField = createSelect2Field('#pickupPointCity');

    const resetCitySelect = () => {
        cityField.refresh([], labels.selectCountyFirst, true);
    };

    const handleRequestError = () => {
        alert('Something went wrong! Please try again latter!');
    };

    resetCitySelect();
    countyField.refresh([], labels.loading, true);

    $.post({
        ...requestOptions('get_counties'),
        beforeSend: () => {
            countyField.refresh([], labels.loading, true);
        },
        success: (counties) => {
            countyField.refresh(counties, labels.chooseCounty, false);
        },
        error: handleRequestError,
    });

    countyField.$select.on('change', function () {
        const countyId = $(this).val();

        if ('' === countyId) {
            resetCitySelect();

            return;
        }

        $.post({
            ...requestOptions('get_cities', { countyId: countyId }),
            beforeSend: () => {
                cityField.refresh([], labels.loading, true);
            },
            success: (cities) => {
                cityField.refresh(cities, labels.pickCity, false);
            },
            error: handleRequestError,
        });
    });
}(jQuery));

jQuery('body').on('click', '.delete-pickup-point', function () {
    jQuery('#form-deletePickupPoint #input-deletePickupPoint').val(jQuery(this).data('id'));
});
