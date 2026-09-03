/**
 * Component: Sync and select lockers Admin
 */
(function ($) {
    'use strict';

    var adminConfig = window.samedayLockerAdmin || {};
    var SamedayCourier = window.SamedayCourier || {};

    function adminErrorMessage() {
        return (adminConfig.i18n && adminConfig.i18n.genericError)
            ? adminConfig.i18n.genericError
            : 'Something went wrong! Please try again later!';
    }

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
        if (!changeLockerButton || typeof SamedayCourier.openLockerPlugin !== 'function') {
            return;
        }

        SamedayCourier.openLockerPlugin(
            {
                apiUsername: changeLockerButton.getAttribute('data-username') || '',
                city: changeLockerButton.getAttribute('data-dest_city') || '',
                countryCode: changeLockerButton.getAttribute('data-dest_country') || '',
            },
            function (locker, pluginInstance) {
                pluginInstance.close();

                var lockerJson = JSON.stringify(locker);

                $.post({
                    url: ajaxurl,
                    data: {
                        action: 'change_locker',
                        orderId: $('#samedaycourier-order-id').val(),
                        locker: lockerJson,
                        _wpnonce: adminConfig.nonces ? adminConfig.nonces.change_locker : ''
                    },
                    success: function () {
                        $('#sameday_locker_name').val(locker.name + ' - ' + locker.address);
                        $('#locker').val(lockerJson);
                    },
                    error: function () {
                        alert(adminErrorMessage());
                    }
                });
            }
        );
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
