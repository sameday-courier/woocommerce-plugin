(function ($) {
    'use strict';

    if (!document.getElementById('pickupPointCounty')) {
        return;
    }

    var labels = samedayPickupPointsAdmin.labels;
    var adminLabels = samedayPickupPointsAdmin.i18n || {};
    var $modal = $('#sameday-pickup-point-modal');

    var select2Options = {
        dropdownParent: $modal,
        width: '100%',
    };

    function genericErrorMessage() {
        return adminLabels.genericError || 'Something went wrong! Please try again later!';
    }

    var requestOptions = function (action, params) {
        params = params || {};

        return {
            url: ajaxurl,
            data: Object.assign({
                action: action,
                _wpnonce: samedayPickupPointsAdmin.nonces[action],
            }, params),
        };
    };

    var populateSelectOptions = function (selectElement, items, placeholder) {
        selectElement.innerHTML = '';

        if ('' !== placeholder) {
            selectElement.innerHTML = '<option value="">' + placeholder + '</option>';
        }

        items.forEach(function (item) {
            selectElement.innerHTML += '<option value="' + item.id + '">' + item.name + '</option>';
        });
    };

    var createSelect2Field = function (selector) {
        var $select = $(selector);

        var destroy = function () {
            if ($select.data('select2')) {
                $select.select2('destroy');
            }
        };

        var init = function () {
            destroy();
            $select.select2(select2Options);
        };

        var setDisabled = function (disabled) {
            $select.prop('disabled', disabled);

            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
        };

        var refresh = function (items, placeholder, disabled) {
            destroy();
            populateSelectOptions($select[0], items, placeholder);
            init();
            setDisabled(disabled);
        };

        return {
            $select: $select,
            refresh: refresh,
        };
    };

    var countyField = createSelect2Field('#pickupPointCounty');
    var cityField = createSelect2Field('#pickupPointCity');

    var resetCitySelect = function () {
        cityField.refresh([], labels.selectCountyFirst, true);
    };

    var handleRequestError = function () {
        alert(genericErrorMessage());
    };

    resetCitySelect();
    countyField.refresh([], labels.loading, true);

    $.post(Object.assign({}, requestOptions('get_counties'), {
        beforeSend: function () {
            countyField.refresh([], labels.loading, true);
        },
        success: function (counties) {
            countyField.refresh(counties, labels.chooseCounty, false);
        },
        error: handleRequestError,
    }));

    countyField.$select.on('change', function () {
        var countyId = $(this).val();

        if ('' === countyId) {
            resetCitySelect();
            return;
        }

        $.post(Object.assign({}, requestOptions('get_cities', { countyId: countyId }), {
            beforeSend: function () {
                cityField.refresh([], labels.loading, true);
            },
            success: function (cities) {
                cityField.refresh(cities, labels.pickCity, false);
            },
            error: handleRequestError,
        }));
    });

    $(document).on('click', '.delete-pickup-point', function () {
        $('#form-deletePickupPoint #input-deletePickupPoint').val($(this).data('id'));
    });
}(jQuery));
