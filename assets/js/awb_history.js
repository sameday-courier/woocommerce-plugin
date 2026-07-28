jQuery(document).ready(function ($) {
    $(document).on('click', '.showHistoryDetails', function () {
        const show = $(this).val();
        const awbNumber = $(this).data('awb-number');
        const tableId = 'history-' + awbNumber;

        if (show === '+') {
            $('#' + tableId).css('display', 'block');
            $(this).val('-');
            $(this).html('<strong> - </strong>');
        } else {
            $('#' + tableId).css('display', 'none');
            $(this).val('+');
            $(this).html('<strong> + </strong>');
        }
    });

    $('.showHistoryDetails').trigger('click');
});
