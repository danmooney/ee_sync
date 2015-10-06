$(function ($) {
    $('#syncee-page').find('.question-mark').qtip({
        content: {
             text: function () {
                 return $(this).attr('oldtitle');
             }
         }
    });
});