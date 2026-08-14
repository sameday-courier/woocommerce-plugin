/**
 * Component: Sync and select lockers Admin
 * ------------------------------------------------------------------------------
 *
 * @namespace selectLocker
 */

(function ($) {
    'use strict';

    function displayDetails(optionFistMile, optionLastMile) {
        var $firstMile = $('#LockerFirstMile');
        var $lastMile = $('#LockerLastMile');
        var $firstMileCheckbox = $('#samedaycourier-locker_first_mile');

        if (!$firstMile.length || !$lastMile.length) {
            return;
        }

        $firstMileCheckbox.prop('checked', false);
        $firstMile.removeClass('sameday-show-element sameday-hide-element').addClass(optionFistMile || 'sameday-hide-element');
        $lastMile.removeClass('sameday-show-element sameday-hide-element').addClass(optionLastMile || 'sameday-hide-element');
    }

    function syncServiceDetails() {
        var $service = $('#samedaycourier-service');
        if (!$service.length) {
            return;
        }

        var $selected = $service.find('option:selected');
        displayDetails(
            $selected.attr('data-fistMile'),
            $selected.attr('data-lastMile')
        );
    }

    function openLockers() {
        var changeLockerButton = document.querySelector('#select_locker');
        if (!changeLockerButton || !window.LockerPlugin) {
            return;
        }

        var clientId = 'b8cb2ee3-41b9-4c3d-aafe-1527b453d65e';
        var samedayUser = changeLockerButton.getAttribute('data-username');
        var countryCode = changeLockerButton.getAttribute('data-country') || '';
        var langCode = countryCode.toLowerCase();
        var destCity = changeLockerButton.getAttribute('data-dest_city');
        var destCountry = changeLockerButton.getAttribute('data-dest_country');

        window.LockerPlugin.init({
            apiUsername: samedayUser,
            city: destCity,
            countryCode: destCountry,
            clientId: clientId,
            langCode: langCode
        });

        var pluginInstance = window.LockerPlugin.getInstance();
        pluginInstance.open();

        pluginInstance.subscribe(function (locker) {
            pluginInstance.close();

            var _locker = JSON.stringify(locker);

            $.post({
                url: ajaxurl,
                data: {
                    action: 'change_locker',
                    orderId: $('#samedaycourier-order-id').val(),
                    locker: _locker,
                    _wpnonce: (window.samedayLockerAdmin && window.samedayLockerAdmin.nonces)
                        ? window.samedayLockerAdmin.nonces.change_locker
                        : ''
                },
                success: function () {
                    $('#sameday_locker_name').val(locker.name + ' - ' + locker.address);
                    $('#locker').val(_locker);
                },
                error: function () {
                    alert('Something went wrong! Please try again latter!');
                }
            });
        });
    }

    function init() {
        syncServiceDetails();

        $(document)
            .off('change.samedayLockerService', '#samedaycourier-service')
            .on('change.samedayLockerService', '#samedaycourier-service', syncServiceDetails);

        $(document)
            .off('select2:select.samedayLockerService', '#samedaycourier-service')
            .on('select2:select.samedayLockerService', '#samedaycourier-service', syncServiceDetails);

        $(document)
            .off('click.samedayLockerMap', '#select_locker')
            .on('click.samedayLockerMap', '#select_locker', function (event) {
                event.preventDefault();
                openLockers();
            });
    }

    $(init);
}(jQuery));
