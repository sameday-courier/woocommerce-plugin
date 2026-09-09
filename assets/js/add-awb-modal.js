(function ($) {
    'use strict';

    var BUTTON_SELECTOR = '[data-sameday-add-awb-order-id]';
    var CONTAINER_SELECTOR = '#sameday-add-awb-modal-container';
    var MODAL_SELECTOR = '[data-sameday-generate-awb-modal]';
    var config = window.samedayAddAwbModal || {};
    var pendingRequest = null;
    var activeButton = null;
    var formBindingsInitialized = false;

    var modalController = window.SamedayModalCore.create({
        modalSelector: MODAL_SELECTOR,
        closeSelector: '[data-sameday-generate-awb-close]',
        openSelector: '[data-sameday-generate-awb-open]',
        openDataKey: 'samedayGenerateAwbOpen',
        bindOpen: false,
        onClose: function () {
            if (pendingRequest && typeof pendingRequest.abort === 'function') {
                pendingRequest.abort();
                pendingRequest = null;
            }

            if (activeButton) {
                setButtonBusy(activeButton, false);
                activeButton = null;
            }

            $(CONTAINER_SELECTOR).empty();
        }
    });

    function i18n(key, fallback) {
        return (config.i18n && config.i18n[key]) ? config.i18n[key] : fallback;
    }

    function getModalId() {
        return config.modalId || 'sameday-generate-awb-modal';
    }

    function showError(message) {
        if (window.SamedayAdminModalConfirm && typeof window.SamedayAdminModalConfirm.showPageNotice === 'function') {
            window.SamedayAdminModalConfirm.showPageNotice(message, 'error');
            return;
        }

        window.alert(message);
    }

    function resolveErrorMessage(response) {
        if (response && response.data && response.data.message) {
            return String(response.data.message);
        }

        return i18n('genericError', 'Unable to load AWB form. Please refresh and try again.');
    }

    function setButtonBusy($button, isBusy) {
        $button.prop('disabled', isBusy).attr('aria-busy', isBusy ? 'true' : 'false');
    }

    function getLoadingShellHtml() {
        if (config.loadingShellHtml) {
            return config.loadingShellHtml;
        }

        return (
            '<div id="' + getModalId() + '" class="sameday-bulk-awb-modal sameday-generate-awb-modal" data-sameday-generate-awb-modal hidden>' +
                '<div class="sameday-bulk-awb-modal__body">' +
                    '<div class="sameday-bulk-awb-modal__starting" role="status" aria-live="polite">' +
                        '<div class="sameday-bulk-awb-modal__spinner" aria-hidden="true"></div>' +
                        '<p class="sameday-bulk-awb-modal__starting-text">' +
                            i18n('loading', 'Loading AWB form…') +
                        '</p>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function showLoadingModal() {
        $(CONTAINER_SELECTOR).html(getLoadingShellHtml());
        modalController.openModal(getModalId());
    }

    function hideModal() {
        modalController.closeAllModals();
        $(CONTAINER_SELECTOR).empty();
    }

    function initLoadedModal() {
        var $modal = $(MODAL_SELECTOR);

        if (!$modal.length) {
            return null;
        }

        if (window.SamedayAwbForm && typeof window.SamedayAwbForm.initModal === 'function') {
            window.SamedayAwbForm.initModal($modal);
        }

        if (window.SamedayLockerAdmin && typeof window.SamedayLockerAdmin.syncModal === 'function') {
            window.SamedayLockerAdmin.syncModal($modal);
        }

        return $modal;
    }

    function wasRequestAborted(jqXHR) {
        return jqXHR && (jqXHR.statusText === 'abort' || jqXHR.status === 0);
    }

    function openAddAwbModal(orderId, $button) {
        if (!orderId || pendingRequest) {
            return;
        }

        activeButton = $button;
        setButtonBusy($button, true);
        showLoadingModal();

        pendingRequest = $.ajax({
            url: config.ajaxUrl || window.ajaxurl,
            method: 'POST',
            data: {
                action: config.action || 'render-add-awb-form',
                'order-id': orderId,
                _wpnonce: config.nonce || ''
            }
        })
            .done(function (response) {
                if (!response || !response.success || !response.data || !response.data.html) {
                    hideModal();
                    showError(resolveErrorMessage(response));
                    return;
                }

                $(CONTAINER_SELECTOR).html(response.data.html);

                var $modal = initLoadedModal();
                if (!$modal || !$modal.length) {
                    hideModal();
                    showError(i18n('genericError', 'Unable to load AWB form. Please refresh and try again.'));
                    return;
                }

                modalController.openModal(response.data.modalId || $modal.attr('id'));
            })
            .fail(function (jqXHR) {
                if (wasRequestAborted(jqXHR)) {
                    return;
                }

                hideModal();
                var response = jqXHR && jqXHR.responseJSON ? jqXHR.responseJSON : null;
                showError(resolveErrorMessage(response));
            })
            .always(function () {
                pendingRequest = null;
                if (activeButton) {
                    setButtonBusy(activeButton, false);
                    activeButton = null;
                }
            });
    }

    function getModalRoot($modal) {
        return $modal && $modal.length ? $modal.get(0) : null;
    }

    function checkPackageLength(modalRoot) {
        var packageLength = modalRoot.querySelector('.sameday-parcel-count-field');
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
        var template = i18n('calculatedWeight', 'Calculated Weight: %1$s %2$s');
        var unit = config.weightUnit || 'kg';

        return template
            .replace('%1$s', String(totalWeight))
            .replace('%2$s', unit);
    }

    function updateCalculatedWeight(modalRoot) {
        var weight = 0;
        modalRoot.querySelectorAll('.samedaycourier-package-weight-class').forEach(function (item) {
            weight += parseFloat(item.value) || 0;
        });

        var weightInput = modalRoot.querySelector('.sameday-calculated-weight-field');
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

    function bindFormInteractions() {
        if (formBindingsInitialized) {
            return;
        }

        formBindingsInitialized = true;

        $(document).on('change', MODAL_SELECTOR + ' .samedaycourier-package-weight-class', function () {
            var modalRoot = getModalRoot($(this).closest(MODAL_SELECTOR));
            if (modalRoot) {
                updateCalculatedWeight(modalRoot);
            }
        });

        $(document).on('click', MODAL_SELECTOR + ' .sameday-add-parcel-button', function (event) {
            event.preventDefault();
            var modalRoot = getModalRoot($(this).closest(MODAL_SELECTOR));
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
    }

    $(function () {
        modalController.bindEvents();
        bindFormInteractions();

        $(document).on('click', BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            event.stopPropagation();

            var $button = $(this);
            openAddAwbModal(String($button.attr('data-sameday-add-awb-order-id') || '').trim(), $button);
        });
    });
}(jQuery));
