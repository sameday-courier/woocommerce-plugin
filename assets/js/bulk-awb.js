(function ($) {
    'use strict';

    var BUTTON_SELECTOR = '[data-sameday-bulk-awb-open]';
    var MODAL_SELECTOR = '[data-sameday-bulk-awb-modal]';

    function getSelectedOrderIds() {
        var ids = [];
        var selectors = [
            '#the-list input[name="id[]"]:checked',
            '#the-list input[name="post[]"]:checked'
        ];

        $(selectors.join(',')).each(function () {
            var value = String($(this).val() || '').trim();
            if (value && ids.indexOf(value) === -1) {
                ids.push(value);
            }
        });

        return ids;
    }

    function syncButtonLayout($button, $anchor) {
        $button.css({
            display: $anchor.css('display'),
            position: $anchor.css('position'),
            top: $anchor.css('top'),
            verticalAlign: $anchor.css('vertical-align'),
            marginTop: $anchor.css('margin-top'),
            marginBottom: $anchor.css('margin-bottom'),
            paddingTop: $anchor.css('padding-top'),
            paddingBottom: $anchor.css('padding-bottom'),
            minHeight: $anchor.css('min-height'),
            lineHeight: $anchor.css('line-height'),
            fontSize: $anchor.css('font-size'),
            fontWeight: $anchor.css('font-weight'),
            boxSizing: $anchor.css('box-sizing')
        });
    }

    function placeButtons() {
        var $buttons = $(BUTTON_SELECTOR);
        if (!$buttons.length) {
            return;
        }

        var $anchor = $('.wrap > h1.wp-heading-inline').nextAll('a.page-title-action').not(BUTTON_SELECTOR).first();
        if (!$anchor.length) {
            $anchor = $('.wrap > a.page-title-action').not(BUTTON_SELECTOR).first();
        }
        if (!$anchor.length) {
            return;
        }

        var $insertAfter = $anchor;
        $buttons.each(function () {
            var $button = $(this);
            $button.insertAfter($insertAfter);
            syncButtonLayout($button, $anchor);
            $insertAfter = $button;
        });
    }

    function getModal($from) {
        if ($from.is(MODAL_SELECTOR)) {
            return $from;
        }

        return $from.closest(MODAL_SELECTOR);
    }

    function updateConfirmState($modal) {
        var hasOrders = getSelectedOrderIds().length > 0;
        var agreed = $modal.find('[data-sameday-bulk-awb-agree]').is(':checked');
        $modal.find('[data-sameday-bulk-awb-confirm]').prop('disabled', !(hasOrders && agreed));
    }

    function renderOrders($modal, ids) {
        var $list = $modal.find('[data-sameday-bulk-awb-order-list]');
        var $empty = $modal.find('[data-sameday-bulk-awb-empty]');
        var orderLabel = (window.samedayBulkAwb && window.samedayBulkAwb.i18n && window.samedayBulkAwb.i18n.order)
            ? window.samedayBulkAwb.i18n.order
            : 'Order';

        $list.empty();
        $modal.find('[data-sameday-bulk-awb-order-count]').text(String(ids.length));

        if (!ids.length) {
            $list.hide();
            $empty.prop('hidden', false);
            return;
        }

        $empty.prop('hidden', true);
        $list.show();

        ids.forEach(function (id) {
            $list.append($('<li/>').text(orderLabel + ' #' + id));
        });
    }

    function openModal(modalId) {
        var $modal = $('#' + modalId);
        if (!$modal.length) {
            return;
        }

        closeAllModals();
        renderOrders($modal, getSelectedOrderIds());
        $modal.find('[data-sameday-bulk-awb-agree]').prop('checked', false);
        updateConfirmState($modal);

        $modal.prop('hidden', false);
        $('body').addClass('sameday-bulk-awb-modal-open');
    }

    function closeModal($modal) {
        if (!$modal || !$modal.length) {
            return;
        }

        $modal.prop('hidden', true);
        if (!$(MODAL_SELECTOR).filter(':not([hidden])').length) {
            $('body').removeClass('sameday-bulk-awb-modal-open');
        }
    }

    function closeAllModals() {
        $(MODAL_SELECTOR).prop('hidden', true);
        $('body').removeClass('sameday-bulk-awb-modal-open');
    }

    $(function () {
        placeButtons();

        $(document).on('click', BUTTON_SELECTOR, function (event) {
            event.preventDefault();
            openModal($(this).data('sameday-bulk-awb-open'));
        });

        $(document).on('click', '[data-sameday-bulk-awb-close]', function (event) {
            event.preventDefault();
            closeModal(getModal($(this)));
        });

        $(document).on('change', '[data-sameday-bulk-awb-agree]', function () {
            updateConfirmState(getModal($(this)));
        });

        $(document).on('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            var $openModal = $(MODAL_SELECTOR).filter(':not([hidden])').last();
            if ($openModal.length) {
                closeModal($openModal);
            }
        });

        $(document).on('click', '[data-sameday-bulk-awb-confirm]', function (event) {
            event.preventDefault();
            if ($(this).is(':disabled')) {
                return;
            }

            // Confirm handlers will be wired to bulk generate/remove in a later step.
            closeModal(getModal($(this)));
        });
    });
}(jQuery));
