jQuery(document).ready(function ($) {
    $(document).on('click', '.sameday-show-history-details', function () {
        var $toggle = $(this);
        var awbNumber = $toggle.data('awb-number');
        var $historyTable = $('#history-' + awbNumber);
        var isExpanded = String($toggle.attr('data-expanded')) === 'true';

        if (!isExpanded) {
            $historyTable.addClass('is-open');
            $toggle.attr('data-expanded', 'true');
            $toggle.html('<strong> - </strong>');
        } else {
            $historyTable.removeClass('is-open');
            $toggle.attr('data-expanded', 'false');
            $toggle.html('<strong> + </strong>');
        }
    });
});
