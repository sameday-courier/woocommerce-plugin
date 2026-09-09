/**
 * Component: Sync and select lockers Admin
 */
(function ($) {
    'use strict';

    var MODAL_SELECTOR = '[data-sameday-generate-awb-modal]';
    var adminConfig = window.samedayLockerAdmin || {};
    var SamedayCourier = window.SamedayCourier || {};

    function adminErrorMessage() {
        return (adminConfig.i18n && adminConfig.i18n.genericError)
            ? adminConfig.i18n.genericError
            : 'Something went wrong! Please try again later!';
    }

    function displayDetails($modal, optionFistMile, optionLastMile) {
        var $firstMile = $modal.find('[id^="LockerFirstMile"]');
        var $lastMile = $modal.find('[id^="LockerLastMile"]');
        var $firstMileCheckbox = $modal.find('input[name="samedaycourier-locker_first_mile"]');

        if (!$firstMile.length || !$lastMile.length) {
            return;
        }

        $firstMileCheckbox.prop('checked', false);
        $firstMile.removeClass('sameday-show-element sameday-hide-element').addClass(optionFistMile || 'sameday-hide-element');
        $lastMile.removeClass('sameday-show-element sameday-hide-element').addClass(optionLastMile || 'sameday-hide-element');
    }

    function syncServiceDetailsForModal($modal) {
        var $service = $modal.find('select[name="samedaycourier-service"]');
        if (!$service.length) {
            return;
        }

        var $selected = $service.find('option:selected');
        displayDetails(
            $modal,
            $selected.attr('data-fistMile'),
            $selected.attr('data-lastMile')
        );
    }

    function openLockers(changeLockerButton) {
        if (!changeLockerButton || typeof SamedayCourier.openLockerPlugin !== 'function') {
            return;
        }

        var $modal = $(changeLockerButton).closest(MODAL_SELECTOR);
        if (!$modal.length) {
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
                        orderId: $modal.find('input[name="samedaycourier-order-id"]').val(),
                        locker: lockerJson,
                        _wpnonce: adminConfig.nonces ? adminConfig.nonces.change_locker : ''
                    },
                    success: function () {
                        $modal.find('.sameday-locker-name-field').val(locker.name + ' - ' + locker.address);
                        $modal.find('.sameday-locker-value-field').val(lockerJson);
                    },
                    error: function () {
                        alert(adminErrorMessage());
                    }
                });
            }
        );
    }

    function init() {
        $(MODAL_SELECTOR).each(function () {
            syncServiceDetailsForModal($(this));
        });

        $(document)
            .off('change.samedayLockerService', MODAL_SELECTOR + ' select[name="samedaycourier-service"]')
            .on('change.samedayLockerService', MODAL_SELECTOR + ' select[name="samedaycourier-service"]', function () {
                syncServiceDetailsForModal($(this).closest(MODAL_SELECTOR));
            });

        $(document)
            .off('select2:select.samedayLockerService', MODAL_SELECTOR + ' select[name="samedaycourier-service"]')
            .on('select2:select.samedayLockerService', MODAL_SELECTOR + ' select[name="samedaycourier-service"]', function () {
                syncServiceDetailsForModal($(this).closest(MODAL_SELECTOR));
            });

        $(document)
            .off('click.samedayLockerMap', MODAL_SELECTOR + ' .sameday-select-locker-button')
            .on('click.samedayLockerMap', MODAL_SELECTOR + ' .sameday-select-locker-button', function (event) {
                event.preventDefault();
                openLockers(this);
            });
    }

    window.SamedayLockerAdmin = window.SamedayLockerAdmin || {};
    window.SamedayLockerAdmin.syncModal = syncServiceDetailsForModal;

    $(init);
}(jQuery));
