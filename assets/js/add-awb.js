(function ($) {
    'use strict';

    var MODAL_SELECTOR = '[data-sameday-generate-awb-modal]';
    var addAwbConfig = window.samedayAddAwb || {};

    var controller = window.SamedayModalCore.create({
        modalSelector: MODAL_SELECTOR,
        openSelector: '[data-sameday-generate-awb-open]',
        closeSelector: '[data-sameday-generate-awb-close]',
        openDataKey: 'samedayGenerateAwbOpen'
    });

    function getModalRoot($modal) {
        return $modal && $modal.length
            ? $modal.get(0)
            : document.querySelector(MODAL_SELECTOR);
    }

    function checkPackageLength(modalRoot) {
        var packageLength = modalRoot.querySelector('#samedaycourier-package-length');
        if (packageLength) {
            packageLength.value = modalRoot.querySelectorAll('.samedaycourier-package-weight-class').length;
        }
    }

    function renumberInputs(modalRoot) {
        var allRows = modalRoot.querySelectorAll('.rowPackageDimension');
        allRows.forEach(function (row, index) {
            row.querySelectorAll('input').forEach(function (input) {
                var name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+]/, '[' + (index + 1) + ']'));
                }
            });
        });
    }

    function formatCalculatedWeight(totalWeight) {
        var template = (addAwbConfig.i18n && addAwbConfig.i18n.calculatedWeight)
            ? addAwbConfig.i18n.calculatedWeight
            : 'Calculated Weight: %1$s %2$s';
        var unit = addAwbConfig.weightUnit || 'kg';

        return template
            .replace('%1$s', String(totalWeight))
            .replace('%2$s', unit);
    }

    function updateCalculatedWeight(modalRoot) {
        var weight = 0;
        modalRoot.querySelectorAll('.samedaycourier-package-weight-class').forEach(function (item) {
            weight += parseFloat(item.value) || 0;
        });

        var weightInput = modalRoot.querySelector('#sameday-package-weight');
        if (weightInput) {
            weightInput.value = formatCalculatedWeight(weight);
        }
    }

    function addParcelRow(modalRoot) {
        var packageDimensionInput = modalRoot.querySelector('.rowPackageDimension');
        if (!packageDimensionInput) {
            return;
        }

        var clonedPackageDimensionInput = packageDimensionInput.cloneNode(true);
        clonedPackageDimensionInput.querySelectorAll('input').forEach(function (input) {
            input.value = '';
        });

        packageDimensionInput.parentNode.insertBefore(
            clonedPackageDimensionInput,
            packageDimensionInput.nextSibling
        );
        renumberInputs(modalRoot);
        checkPackageLength(modalRoot);
        updateCalculatedWeight(modalRoot);
    }

    $(function () {
        controller.bindEvents();

        $(document).on('change', MODAL_SELECTOR + ' .samedaycourier-package-weight-class', function () {
            var modalRoot = getModalRoot(controller.getModal($(this)));
            if (modalRoot) {
                updateCalculatedWeight(modalRoot);
            }
        });

        $(document).on('click', '#addParcelButton', function (event) {
            event.preventDefault();
            var modalRoot = getModalRoot(controller.getModal($(this)));
            if (!modalRoot) {
                return;
            }
            addParcelRow(modalRoot);
        });

        document.addEventListener('click', function (event) {
            var target = event.target;
            if (!target || !target.classList.contains('deleteParcelButton')) {
                return;
            }

            var modalRoot = target.closest(MODAL_SELECTOR);
            if (!modalRoot) {
                return;
            }

            if (modalRoot.querySelectorAll('.deleteParcelButton').length <= 1) {
                return;
            }

            var tableRow = target.closest('tr');
            if (!tableRow) {
                return;
            }

            tableRow.remove();
            renumberInputs(modalRoot);
            checkPackageLength(modalRoot);
            updateCalculatedWeight(modalRoot);
        });
    });
}(jQuery));
