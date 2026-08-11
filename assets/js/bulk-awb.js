(function ($) {
    'use strict';

    var BUTTON_ID = 'sameday-bulk-awb-button';
    var MODAL_ID = 'sameday-bulk-awb-modal';

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

    function placeButton() {
        var $button = $('#' + BUTTON_ID);
        var $anchor = $('.wrap > h1.wp-heading-inline').nextAll('a.page-title-action').not('#' + BUTTON_ID).first();

        if (!$anchor.length) {
            $anchor = $('.wrap > a.page-title-action').not('#' + BUTTON_ID).first();
        }

        if (!$button.length || !$anchor.length) {
            return;
        }

        $button.insertAfter($anchor).css({
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

    function updateConfirmState() {
        var hasOrders = getSelectedOrderIds().length > 0;
        var agreed = $('#sameday-bulk-awb-agree').is(':checked');
        $('#sameday-bulk-awb-confirm').prop('disabled', !(hasOrders && agreed));
    }

    function renderOrders(ids) {
        var $list = $('#sameday-bulk-awb-order-list');
        var $empty = $('#sameday-bulk-awb-empty');
        var orderLabel = (window.samedayBulkAwb && window.samedayBulkAwb.i18n && window.samedayBulkAwb.i18n.order)
            ? window.samedayBulkAwb.i18n.order
            : 'Order';

        $list.empty();
        $('#sameday-bulk-awb-order-count').text(String(ids.length));

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

    function openModal() {
        var ids = getSelectedOrderIds();
        var $modal = $('#' + MODAL_ID);

        renderOrders(ids);
        $('#sameday-bulk-awb-agree').prop('checked', false);
        updateConfirmState();

        $modal.prop('hidden', false);
        $('body').addClass('sameday-bulk-awb-modal-open');
    }

    function closeModal() {
        $('#' + MODAL_ID).prop('hidden', true);
        $('body').removeClass('sameday-bulk-awb-modal-open');
    }

    $(function () {
        placeButton();

        $(document).on('click', '#' + BUTTON_ID, function (event) {
            event.preventDefault();
            openModal();
        });

        $(document).on('click', '[data-sameday-bulk-awb-close]', function (event) {
            event.preventDefault();
            closeModal();
        });

        $(document).on('change', '#sameday-bulk-awb-agree', updateConfirmState);

        $(document).on('keydown', function (event) {
            if (event.key === 'Escape' && !$('#' + MODAL_ID).prop('hidden')) {
                closeModal();
            }
        });

        $(document).on('click', '#sameday-bulk-awb-confirm', function (event) {
            event.preventDefault();
            if ($(this).is(':disabled')) {
                return;
            }

            // Confirm handler will be wired to bulk generation in a later step.
            closeModal();
        });
    });
}(jQuery));
