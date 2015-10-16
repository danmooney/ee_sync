$(function ($) {
    $('#syncee-page').find('.question-mark').qtip({
        style: {
            classes: 'qtip-dark qtip-shadow'
        },
        content: {
            text: function () {
                return $(this).attr('oldtitle');
            }
         }
    });
});