$(function ($) {
    var $flashMessageContainer = $('.flash-message-container');
    $flashMessageContainer.find('.btn-close').on('click', function (e) {
        e.preventDefault();
        $flashMessageContainer.slideUp();
    });
});