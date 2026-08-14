(function ($) {
    'use strict';

    var BUTTON_SELECTOR = '[data-sameday-generate-awb-open]';
    var MODAL_SELECTOR = '[data-sameday-generate-awb-modal]';
    var BODY_OPEN_CLASS = 'sameday-bulk-awb-modal-open';

    function getModal($from) {
        if ($from.is(MODAL_SELECTOR)) {
            return $from;
        }

        return $from.closest(MODAL_SELECTOR);
    }

    function getModalRoot($modal) {
        return $modal && $modal.length
            ? $modal.get(0)
            : document.querySelector(MODAL_SELECTOR);
    }

    function openModal(modalId) {
        var $modal = modalId ? $('#' + modalId) : $(MODAL_SELECTOR).first();
        if (!$modal.length) {
            return;
        }

        $modal.prop('hidden', false);
        $('body').addClass(BODY_OPEN_CLASS);
    }

    function closeModal($modal) {
        if (!$modal || !$modal.length) {
            return;
        }

        $modal.prop('hidden', true);

        if (!$(MODAL_SELECTOR).filter(':not([hidden])').length) {
            $('body').removeClass(BODY_OPEN_CLASS);
        }
    }

    function checkPackageLength(modalRoot) {
        var packageWeightClass = modalRoot.querySelectorAll('.samedaycourier-package-weight-class');
        var packageLength = modalRoot.querySelector('#samedaycourier-package-length');
        if (packageLength) {
            packageLength.value = packageWeightClass.length;
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

    function bindWeightRecalculation(modalRoot) {
        modalRoot.querySelectorAll('.samedaycourier-package-weight-class').forEach(function (input) {
            input.addEventListener('change', function () {
                var weight = 0;
                modalRoot.querySelectorAll('.samedaycourier-package-weight-class').forEach(function (item) {
                    weight += parseFloat(item.value) || 0;
                });
                var weightInput = modalRoot.querySelector('#sameday-package-weight');
                if (weightInput) {
                    weightInput.value = 'Calculated Weight: ' + weight + ' kg';
                }
            });
        });
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
        bindWeightRecalculation(modalRoot);
    }

    $(function () {
        $(document).on('click', BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            openModal($(this).data('sameday-generate-awb-open'));
        });

        $(document).on('click', '[data-sameday-generate-awb-close]', function (event) {
            event.preventDefault();
            closeModal(getModal($(this)));
        });

        $(document).on('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            var $openModal = $(MODAL_SELECTOR).filter(':not([hidden])').last();
            if (!$openModal.length) {
                return;
            }

            closeModal($openModal);
        });

        $(document).on('click', '#addParcelButton', function (event) {
            event.preventDefault();
            var modalRoot = getModalRoot(getModal($(this)));
            if (!modalRoot) {
                return;
            }
            addParcelRow(modalRoot);
        });

        document.addEventListener('click', function (e) {
            if (!e.target || !e.target.classList.contains('deleteParcelButton')) {
                return;
            }

            var modalRoot = e.target.closest(MODAL_SELECTOR);
            if (!modalRoot) {
                return;
            }

            if (modalRoot.querySelectorAll('.deleteParcelButton').length <= 1) {
                return;
            }

            var tableRow = e.target.closest('tr');
            if (!tableRow) {
                return;
            }

            tableRow.remove();
            renumberInputs(modalRoot);
            checkPackageLength(modalRoot);
        });
    });
}(jQuery));
