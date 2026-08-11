jQuery(document).ready(function ($) {
    $(document).on('click', '.showHistoryDetails', function () {
        const show = $(this).val();
        const awbNumber = $(this).data('awb-number');
        const $historyTable = $('#history-' + awbNumber);

        if (show === '+') {
            $historyTable.addClass('is-open');
            $(this).val('-');
            $(this).html('<strong> - </strong>');
        } else {
            $historyTable.removeClass('is-open');
            $(this).val('+');
            $(this).html('<strong> + </strong>');
        }
    });

    $('.showHistoryDetails').trigger('click');
});
